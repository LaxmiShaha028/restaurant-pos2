<?php

namespace App\Services;

use App\Models\Central\Restaurant;
use App\Models\Central\Subscription;
use Illuminate\Support\Facades\DB;
use Throwable;

class SubscriptionLifecycleService
{
    /*
    |--------------------------------------------------------------------------
    | Expire Due Subscriptions
    |--------------------------------------------------------------------------
    |
    | Proactively expires every ACTIVE subscription whose end_at has passed.
    | This method is intended for the scheduler.
    |
    */

    public function expireDueSubscriptions(): array
    {
        $expiredCount = 0;
        $restaurantIds = [];

        $subscriptionIds = Subscription::on('central')
            ->where('status', 'active')
            ->whereNotNull('end_at')
            ->where('end_at', '<=', now())
            ->orderBy('id')
            ->pluck('id');

        foreach ($subscriptionIds as $subscriptionId) {
            try {
                $result = $this->expireSubscriptionIfDue(
                    (int) $subscriptionId
                );

                if (! $result['expired']) {
                    continue;
                }

                $expiredCount++;

                if ($result['restaurant_id']) {
                    $restaurantIds[] =
                        $result['restaurant_id'];
                }
            } catch (Throwable $exception) {
                /*
                |------------------------------------------------------------------
                | One failed subscription must not stop the whole scheduler run.
                |------------------------------------------------------------------
                */

                report($exception);
            }
        }

        return [
            'expired_count' => $expiredCount,

            'restaurant_ids' => array_values(
                array_unique($restaurantIds)
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Synchronize One Restaurant Subscription
    |--------------------------------------------------------------------------
    |
    | Request-time safety net used by:
    |
    | - Super Admin restaurant details
    | - Public tenant entry
    | - Tenant middleware
    |
    | The latest subscription controls the current restaurant lifecycle.
    |
    */

    public function synchronizeRestaurant(
        int $restaurantId
    ): array {
        return DB::connection('central')->transaction(
            function () use ($restaurantId): array {

                /*
                |------------------------------------------------------------------
                | Lock Latest Subscription First
                |------------------------------------------------------------------
                |
                | Keep the lock order consistent with scheduler processing:
                | subscription -> restaurant.
                |
                */

                $subscription = Subscription::on('central')
                    ->where(
                        'restaurant_id',
                        $restaurantId
                    )
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();

                $restaurant = Restaurant::on('central')
                    ->lockForUpdate()
                    ->find($restaurantId);

                if (! $restaurant) {
                    return [
                        'restaurant' => null,
                        'subscription' => $subscription,
                        'changed' => false,
                    ];
                }

                if (! $subscription) {
                    return [
                        'restaurant' => $restaurant,
                        'subscription' => null,
                        'changed' => false,
                    ];
                }

                $changed = false;

                /*
                |------------------------------------------------------------------
                | Active + Due => Expired
                |------------------------------------------------------------------
                */

                if (
                    $subscription->status === 'active'
                    &&
                    $subscription->end_at !== null
                    &&
                    $subscription->end_at->lte(now())
                ) {
                    $subscription->forceFill([
                        'status' => 'expired',
                    ])->save();

                    if ($restaurant->status !== 'inactive') {
                        $restaurant->forceFill([
                            'status' => 'inactive',
                        ])->save();
                    }

                    $changed = true;
                }

                /*
                |------------------------------------------------------------------
                | Defensive Restaurant Synchronization
                |------------------------------------------------------------------
                |
                | If the latest subscription is already expired/cancelled,
                | an ACTIVE restaurant must not remain accessible.
                |
                */

                if (
                    in_array(
                        $subscription->status,
                        [
                            'expired',
                            'cancelled',
                        ],
                        true
                    )
                    &&
                    $restaurant->status === 'active'
                ) {
                    $restaurant->forceFill([
                        'status' => 'inactive',
                    ])->save();

                    $changed = true;
                }

                return [
                    'restaurant' =>
                        $restaurant->fresh(),

                    'subscription' =>
                        $subscription
                            ->fresh()
                            ->load('plan'),

                    'changed' => $changed,
                ];
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Expire One Subscription If Due
    |--------------------------------------------------------------------------
    */

    private function expireSubscriptionIfDue(
        int $subscriptionId
    ): array {
        return DB::connection('central')->transaction(
            function () use ($subscriptionId): array {
                $subscription = Subscription::on('central')
                    ->lockForUpdate()
                    ->find($subscriptionId);

                if (! $subscription) {
                    return [
                        'expired' => false,
                        'restaurant_id' => null,
                    ];
                }

                if ($subscription->status !== 'active') {
                    return [
                        'expired' => false,
                        'restaurant_id' =>
                            $subscription->restaurant_id,
                    ];
                }

                if (
                    ! $subscription->end_at
                    ||
                    $subscription->end_at->isFuture()
                ) {
                    return [
                        'expired' => false,
                        'restaurant_id' =>
                            $subscription->restaurant_id,
                    ];
                }

                $subscription->forceFill([
                    'status' => 'expired',
                ])->save();

                $restaurant = Restaurant::on('central')
                    ->lockForUpdate()
                    ->find(
                        $subscription->restaurant_id
                    );

                if ($restaurant) {
                    /*
                    |--------------------------------------------------------------
                    | Do not deactivate a restaurant because of an OLD historical
                    | subscription if a newer valid active subscription exists.
                    |--------------------------------------------------------------
                    */

                    $latestSubscription =
                        Subscription::on('central')
                            ->where(
                                'restaurant_id',
                                $restaurant->id
                            )
                            ->latest('id')
                            ->first();

                    $latestIsValidActive =
                        $latestSubscription
                        &&
                        $latestSubscription->status === 'active'
                        &&
                        (
                            $latestSubscription->end_at === null
                            ||
                            $latestSubscription->end_at->isFuture()
                        );

                    if (! $latestIsValidActive) {
                        $restaurant->forceFill([
                            'status' => 'inactive',
                        ])->save();
                    }
                }

                return [
                    'expired' => true,
                    'restaurant_id' =>
                        $subscription->restaurant_id,
                ];
            }
        );
    }
}
