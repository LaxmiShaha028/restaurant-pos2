<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionExpiringMail;
use App\Models\Central\Restaurant;
use App\Models\Central\Subscription;
use App\Models\Central\SubscriptionExpiryReminder;
use App\Models\Central\SubscriptionPlan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendSubscriptionExpiryReminders extends Command
{
    protected $signature =
        'subscriptions:send-expiry-reminders
        {--dry-run : Show eligible reminders without sending email}
        {--restaurant= : Limit to one restaurant ID}';

    protected $description =
        'Send 48-hour and 24-hour subscription expiry reminder emails to restaurant owners.';

    private const WINDOW_MINUTES = 15;

    public function handle(): int
    {
        $now = now();
        $restaurantFilter = $this->option('restaurant');
        $dryRun = (bool) $this->option('dry-run');

        $query = Subscription::on('central')
            ->whereIn(
                'id',
                function ($subQuery): void {
                    $subQuery
                        ->selectRaw('MAX(id)')
                        ->from('subscriptions')
                        ->groupBy('restaurant_id');
                }
            )
            ->where('status', 'active')
            ->whereNotNull('end_at')
            ->where('end_at', '>', $now)
            ->where('end_at', '<=', $now->copy()->addHours(48))
            ->orderBy('end_at');

        if ($restaurantFilter !== null && $restaurantFilter !== '') {
            $query->where('restaurant_id', (int) $restaurantFilter);
        }

        $subscriptions = $query->get();

        if ($subscriptions->isEmpty()) {
            $this->info('No subscriptions are currently inside the 48-hour reminder window.');
            return self::SUCCESS;
        }

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($subscriptions as $subscription) {
            $restaurant = Restaurant::on('central')
                ->find($subscription->restaurant_id);

            if (! $restaurant) {
                $skipped++;
                $this->warn("Subscription {$subscription->id}: restaurant not found.");
                continue;
            }

            $ownerEmail = strtolower(
                trim((string) ($restaurant->owner_email ?? ''))
            );

            if (
                $ownerEmail === ''
                || ! filter_var($ownerEmail, FILTER_VALIDATE_EMAIL)
            ) {
                $skipped++;
                $this->warn("Restaurant {$restaurant->id}: valid owner email is missing.");
                continue;
            }

            /*
            |------------------------------------------------------------------
            | Re-read current subscription before sending.
            | If plan was updated/renewed, old end_at is ignored automatically.
            |------------------------------------------------------------------
            */
            $currentSubscription = Subscription::on('central')
                ->where('restaurant_id', $restaurant->id)
                ->latest('id')
                ->first();

            if (
                ! $currentSubscription
                || (int) $currentSubscription->id !== (int) $subscription->id
                || $currentSubscription->status !== 'active'
                || ! $currentSubscription->end_at
            ) {
                $skipped++;
                continue;
            }

            $endAt = Carbon::parse($currentSubscription->end_at);

            if ($endAt->lte(now())) {
                $skipped++;
                continue;
            }

            $reminderHours = $this->resolveReminderHours(now(), $endAt);

            if ($reminderHours === null) {
                $skipped++;
                continue;
            }

            $reminderType = $reminderHours . '_hours';

            $alreadySent = SubscriptionExpiryReminder::on('central')
                ->where('subscription_id', $currentSubscription->id)
                ->where('reminder_type', $reminderType)
                ->where('subscription_end_at', $endAt->toDateTimeString())
                ->exists();

            if ($alreadySent) {
                $skipped++;
                continue;
            }

            $plan = SubscriptionPlan::on('central')
                ->find($currentSubscription->subscription_plan_id);

            $mailData = [
                'owner_name' => $restaurant->owner_name ?: 'Restaurant Owner',
                'owner_email' => $ownerEmail,
                'restaurant_name' => $restaurant->name ?: 'Restaurant',
                'plan_name' => $plan?->name ?: 'Current Plan',
                'reminder_hours' => $reminderHours,
                'expires_at' => $endAt
                    ->copy()
                    ->timezone(config('app.timezone'))
                    ->format('d M Y, h:i A'),
                'subscription_end_at' => $endAt->toDateTimeString(),
                'subscription_id' => $currentSubscription->id,
                'restaurant_id' => $restaurant->id,
                'app_name' => config('app.name', 'RMS'),
            ];

            if ($dryRun) {
                $this->line(
                    "[DRY RUN] {$reminderHours}h reminder -> "
                    . "{$restaurant->name} <{$ownerEmail}> | expires {$mailData['expires_at']}"
                );
                continue;
            }

            try {
                Mail::to($ownerEmail)->send(
                    new SubscriptionExpiringMail($mailData)
                );

                /*
                |--------------------------------------------------------------
                | Re-check after send before marking reminder as completed.
                |--------------------------------------------------------------
                */
                $freshSubscription = Subscription::on('central')
                    ->find($currentSubscription->id);

                if (
                    ! $freshSubscription
                    || $freshSubscription->status !== 'active'
                    || ! $freshSubscription->end_at
                    || Carbon::parse($freshSubscription->end_at)->ne($endAt)
                ) {
                    $this->warn(
                        "Restaurant {$restaurant->id}: subscription changed during reminder processing."
                    );
                    $skipped++;
                    continue;
                }

                SubscriptionExpiryReminder::on('central')
                    ->firstOrCreate(
                        [
                            'subscription_id' => $currentSubscription->id,
                            'reminder_type' => $reminderType,
                            'subscription_end_at' => $endAt->toDateTimeString(),
                        ],
                        [
                            'restaurant_id' => $restaurant->id,
                            'recipient_email' => $ownerEmail,
                            'sent_at' => now(),
                        ]
                    );

                $sent++;
                $this->info(
                    "{$reminderHours}h reminder sent -> {$restaurant->name} <{$ownerEmail}>"
                );
            } catch (Throwable $exception) {
                report($exception);
                $failed++;
                $this->error(
                    "Restaurant {$restaurant->id}: email failed - {$exception->getMessage()}"
                );
            }
        }

        $this->newLine();
        $this->info(
            "Reminder run completed. Sent: {$sent}; Skipped: {$skipped}; Failed: {$failed}."
        );

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }

    private function resolveReminderHours(
        Carbon $now,
        Carbon $endAt
    ): ?int {
        $remainingSeconds =
            $endAt->getTimestamp() - $now->getTimestamp();

        if ($remainingSeconds <= 0) {
            return null;
        }

        $toleranceSeconds = self::WINDOW_MINUTES * 60;

        foreach ([48, 24] as $hours) {
            $thresholdSeconds = $hours * 60 * 60;

            if (
                $remainingSeconds <= $thresholdSeconds
                && $remainingSeconds > ($thresholdSeconds - $toleranceSeconds)
            ) {
                return $hours;
            }
        }

        return null;
    }
}
