<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Subscription;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Http\JsonResponse;

class CentralNotificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Subscription Expiry Notifications
    |--------------------------------------------------------------------------
    |
    | Returns only the latest ACTIVE subscription for each restaurant when
    | its end_at falls inside the next 24 hours.
    |
    | This endpoint is READ ONLY apart from lifecycle synchronization.
    |
    */

    public function subscriptionExpiring(
        SubscriptionLifecycleService $lifecycleService
    ): JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | Keep Expired Records Synchronized
        |--------------------------------------------------------------------------
        */

        $lifecycleService
            ->expireDueSubscriptions();


        /*
        |--------------------------------------------------------------------------
        | Notification Window
        |--------------------------------------------------------------------------
        */

        $now =
            now();

        $windowEnd =
            $now
                ->copy()
                ->addHours(24);


        /*
        |--------------------------------------------------------------------------
        | Latest Subscription Per Restaurant
        |--------------------------------------------------------------------------
        */

        $subscriptions =
            Subscription::on('central')
                ->with([
                    'restaurant:id,name,owner_name,owner_email,phone,status,tenant_key',
                    'plan:id,name,price,currency,billing_interval,billing_interval_count',
                ])
                ->whereIn(
                    'id',
                    function ($subQuery): void {

                        $subQuery
                            ->selectRaw(
                                'MAX(id)'
                            )
                            ->from(
                                'subscriptions'
                            )
                            ->groupBy(
                                'restaurant_id'
                            );
                    }
                )
                ->where(
                    'status',
                    'active'
                )
                ->whereNotNull(
                    'end_at'
                )
                ->where(
                    'end_at',
                    '>',
                    $now
                )
                ->where(
                    'end_at',
                    '<=',
                    $windowEnd
                )
                ->orderBy(
                    'end_at'
                )
                ->get();


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        $data =
            $subscriptions
                ->map(
                    function (
                        Subscription $subscription
                    ) use (
                        $now
                    ): array {

                        $restaurant =
                            $subscription->restaurant;

                        $plan =
                            $subscription->plan;

                        $endAt =
                            $subscription->end_at;

                        $secondsRemaining =
                            $endAt
                                ? max(
                                    0,
                                    (int)
                                        $now->diffInSeconds(
                                            $endAt,
                                            false
                                        )
                                )
                                : 0;


                        return [
                            'subscription_id' =>
                                $subscription->id,

                            'restaurant_id' =>
                                $subscription->restaurant_id,

                            'status' =>
                                $subscription->status,

                            'end_at' =>
                                $endAt,

                            'seconds_remaining' =>
                                $secondsRemaining,

                            'subscription_type' =>
                                (
                                    $plan
                                    &&
                                    (float) $plan->price <= 0
                                    &&
                                    $subscription->trial_end_at
                                )
                                    ? 'trial'
                                    : 'paid',

                            'restaurant' =>
                                $restaurant
                                    ? [
                                        'id' =>
                                            $restaurant->id,

                                        'name' =>
                                            $restaurant->name,

                                        'owner_name' =>
                                            $restaurant->owner_name,

                                        'owner_email' =>
                                            $restaurant->owner_email,

                                        'phone' =>
                                            $restaurant->phone,

                                        'status' =>
                                            $restaurant->status,

                                        'tenant_key' =>
                                            $restaurant->tenant_key,
                                    ]
                                    : null,

                            'plan' =>
                                $plan
                                    ? [
                                        'id' =>
                                            $plan->id,

                                        'name' =>
                                            $plan->name,

                                        'price' =>
                                            $plan->price,

                                        'currency' =>
                                            $plan->currency,

                                        'billing_interval' =>
                                            $plan->billing_interval,

                                        'billing_interval_count' =>
                                            $plan->billing_interval_count,
                                    ]
                                    : null,
                        ];
                    }
                )
                ->values();


        return response()->json([
            'success' =>
                true,

            'message' =>
                'Subscription expiry notifications retrieved successfully.',

            'data' =>
                $data,

            'meta' => [
                'count' =>
                    $data->count(),

                'window_hours' =>
                    24,

                'generated_at' =>
                    $now->toISOString(),

                'window_ends_at' =>
                    $windowEnd->toISOString(),
            ],

            'errors' =>
                null,
        ]);
    }
}
