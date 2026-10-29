<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Offer;
use App\Models\Order;
use Carbon\CarbonImmutable;

class OfferService
{
    public function customerEligibilityHint(float $subtotal, ?Customer $customer = null): ?string
    {
        $localNow = CarbonImmutable::now('Asia/Dhaka');
        $today = $localNow->toDateString();
        $time = $localNow->format('H:i:s');
        $weekday = strtolower($localNow->format('D'));

        $offers = Offer::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('valid_from')->orWhereDate('valid_from', '<=', $today))
            ->where(fn ($query) => $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', $today))
            ->orderByDesc('discount_value')
            ->get();

        foreach ($offers as $offer) {
            if ((float) $offer->minimum_order_amount > $subtotal) {
                continue;
            }
            if ($offer->days_of_week && ! in_array($weekday, $offer->days_of_week, true)) {
                continue;
            }
            if ($offer->start_time && $offer->end_time) {
                $start = (string) $offer->start_time;
                $end = (string) $offer->end_time;
                $withinTime = $end < $start
                    ? ($time >= $start || $time <= $end)
                    : ($time >= $start && $time <= $end);
                if (! $withinTime) {
                    continue;
                }
            }

            $usageQuery = Order::query()
                ->where('offer_id', $offer->id)
                ->where('status', '!=', Order::STATUS_CANCELED);
            if ($offer->usage_limit !== null && $usageQuery->count() >= (int) $offer->usage_limit) {
                continue;
            }

            if ($offer->type === 'birthday') {
                if (! $customer) {
                    return "Select a customer with a saved birthday to use {$offer->name}.";
                }
                if (! $customer->date_of_birth) {
                    return "Add {$customer->name}'s birthday to their customer profile to check {$offer->name} eligibility. Birthday is optional.";
                }
                if (! $this->isBirthdayWindow($customer, (int) $offer->birthday_window_days)) {
                    return "{$customer->name} is not within the birthday window for {$offer->name}.";
                }

                $perCustomerLimit = $offer->per_customer_limit ?? 1;
                $customerUses = $customer->exists
                    ? (clone $usageQuery)->where('customer_id', $customer->id)->count()
                    : 0;
                if ($customerUses >= (int) $perCustomerLimit) {
                    return "{$customer->name} has already used {$offer->name} the allowed number of times.";
                }
            }
            if ($offer->type === 'loyalty') {
                if (! $customer) {
                    return "Select a loyalty customer to check eligibility for {$offer->name}.";
                }
                if ($customer->visitCount() < (int) $offer->minimum_customer_orders) {
                    return "{$customer->name} does not yet meet the visit requirement for {$offer->name}.";
                }
            }
        }

        return null;
    }

    /** @return array{offer: Offer, discount_amount: float}|null */
    public function bestForOrder(
        float $subtotal,
        ?Customer $customer,
        ?int $excludeOrderId = null
    ): ?array {
        $localNow = CarbonImmutable::now('Asia/Dhaka');
        $today = $localNow->toDateString();
        $time = $localNow->format('H:i:s');
        $weekday = strtolower($localNow->format('D'));

        $offers = Offer::query()
            ->where('is_active', true)
            ->where(function ($query) use ($today): void {
                $query->whereNull('valid_from')->orWhereDate('valid_from', '<=', $today);
            })
            ->where(function ($query) use ($today): void {
                $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', $today);
            })
            ->orderByDesc('discount_value')
            ->lockForUpdate()
            ->get();

        $eligible = $offers->filter(function (Offer $offer) use (
            $subtotal,
            $customer,
            $today,
            $time,
            $weekday,
            $excludeOrderId
        ): bool {
            if ((float) $offer->minimum_order_amount > $subtotal) {
                return false;
            }

            if ($offer->days_of_week && ! in_array($weekday, $offer->days_of_week, true)) {
                return false;
            }

            if ($offer->start_time && $offer->end_time) {
                $start = (string) $offer->start_time;
                $end = (string) $offer->end_time;
                $withinTime = $end < $start
                    ? ($time >= $start || $time <= $end)
                    : ($time >= $start && $time <= $end);
                if (! $withinTime) {
                    return false;
                }
            }

            if ($offer->type === 'birthday' && ! $this->isBirthdayWindow($customer, (int) $offer->birthday_window_days)) {
                return false;
            }

            if ($offer->type === 'loyalty') {
                if (! $customer || ! $customer->exists || $customer->visitCount() < (int) $offer->minimum_customer_orders) {
                    return false;
                }
            }

            $usageQuery = Order::query()
                ->where('offer_id', $offer->id)
                ->where('status', '!=', Order::STATUS_CANCELED);

            if ($excludeOrderId) {
                $usageQuery->where('id', '!=', $excludeOrderId);
            }

            if ($offer->usage_limit !== null && (clone $usageQuery)->count() >= (int) $offer->usage_limit) {
                return false;
            }

            $perCustomerLimit = $offer->type === 'birthday'
                ? ($offer->per_customer_limit ?? 1)
                : $offer->per_customer_limit;
            if ($perCustomerLimit !== null && $customer?->exists) {
                $customerUses = (clone $usageQuery)
                    ->where('customer_id', $customer->id)
                    ->count();
                if ($customerUses >= (int) $perCustomerLimit) {
                    return false;
                }
            }

            return true;
        });

        return $eligible
            ->map(function (Offer $offer) use ($subtotal): array {
                $discount = $offer->discount_type === 'percentage'
                    ? $subtotal * (float) $offer->discount_value / 100
                    : (float) $offer->discount_value;

                return [
                    'offer' => $offer,
                    'discount_amount' => round(min($subtotal, max(0, $discount)), 2),
                ];
            })
            ->sortByDesc('discount_amount')
            ->first();
    }

    private function isBirthdayWindow(?Customer $customer, int $windowDays): bool
    {
        if (! $customer?->date_of_birth) {
            return false;
        }

        $today = CarbonImmutable::today('Asia/Dhaka');
        $birth = CarbonImmutable::parse($customer->date_of_birth);
        $birthdayMonthEnd = CarbonImmutable::create($today->year, $birth->month, 1)->endOfMonth()->day;
        $birthdayThisYear = $today->setDate($today->year, $birth->month, min($birth->day, $birthdayMonthEnd));
        $dates = [
            $birthdayThisYear,
            $birthdayThisYear->subYear(),
            $birthdayThisYear->addYear(),
        ];

        return collect($dates)->contains(
            fn (CarbonImmutable $date): bool => $date->diffInDays($today) <= $windowDays
        );
    }
}
