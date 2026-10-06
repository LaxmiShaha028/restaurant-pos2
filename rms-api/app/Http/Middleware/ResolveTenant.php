<?php

namespace App\Http\Middleware;

use App\Models\Central\Domain;
use App\Models\Central\Restaurant;
use App\Models\Central\Subscription;
use App\Services\Tenant\TenantConnectionManager;
use App\Services\Tenant\TenantManager;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ResolveTenant
{
    public function __construct(
        private readonly TenantConnectionManager $connectionManager,
        private readonly TenantManager $tenantManager,
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Handle
    |--------------------------------------------------------------------------
    */

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | Resolve Tenant Key / Host
        |--------------------------------------------------------------------------
        */

        $tenantKey = trim(
            (string) (
                $request->input('tenant_key')
                ??
                $request->header('X-Tenant-Key')
                ??
                ''
            )
        );

        $host = strtolower(
            trim(
                $request->getHost()
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Tenant Resolution State
        |--------------------------------------------------------------------------
        */

        $restaurant = null;
        $domain = null;


        /*
        |--------------------------------------------------------------------------
        | 1. Resolve By Tenant Key
        |--------------------------------------------------------------------------
        */

        if ($tenantKey !== '') {
            $restaurant = Restaurant::on('central')
                ->with([
                    'tenantDatabase',
                ])
                ->where(
                    'tenant_key',
                    $tenantKey
                )
                ->first();
        }


        /*
        |--------------------------------------------------------------------------
        | 2. Resolve By Domain
        |--------------------------------------------------------------------------
        */

        if (
            ! $restaurant &&
            $host !== '' &&
            ! in_array(
                $host,
                [
                    'localhost',
                    '127.0.0.1',
                ],
                true
            )
        ) {
            $domain = Domain::on('central')
                ->with([
                    'restaurant.tenantDatabase',
                ])
                ->where(
                    'domain',
                    $host
                )
                ->first();

            if ($domain) {
                $restaurant = $domain->restaurant;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Tenant Could Not Be Resolved
        |--------------------------------------------------------------------------
        */

        if (! $restaurant) {
            return response()->json([
                'success' => false,

                'message' =>
                    $tenantKey !== ''
                        ? 'Restaurant could not be resolved from the tenant key.'
                        : 'Restaurant domain was not found.',

                'data' => null,

                'errors' => null,
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Resolve Primary Domain For Tenant-Key Access
        |--------------------------------------------------------------------------
        */

        if (! $domain) {
            $domain = Domain::on('central')
                ->where(
                    'restaurant_id',
                    $restaurant->id
                )
                ->where(
                    'is_primary',
                    true
                )
                ->first();
        }


        /*
        |--------------------------------------------------------------------------
        | Domain Verification
        |--------------------------------------------------------------------------
        |
        | Only enforce verification when the tenant was resolved through
        | a real restaurant domain.
        |
        */

        if (
            $tenantKey === '' &&
            $domain &&
            ! $domain->is_verified
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Restaurant domain has not been verified.',

                'data' => null,

                'errors' => null,
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Subscription Validation
        |--------------------------------------------------------------------------
        */

        $subscriptionError =
            $this->validateRestaurantSubscription(
                $restaurant
            );

        if ($subscriptionError) {
            return $subscriptionError;
        }


        /*
        |--------------------------------------------------------------------------
        | Restaurant Status
        |--------------------------------------------------------------------------
        */

        /*
         * Re-read the restaurant status after subscription validation.
         * This is important because validateRestaurantSubscription()
         * may have changed the restaurant to inactive.
         */
        $restaurant->refresh();

        if ($restaurant->status !== 'active') {
            return response()->json([
                'success' => false,

                'message' =>
                    'This restaurant account is not active.',

                'data' => [
                    'restaurant_status' =>
                        $restaurant->status,
                ],

                'errors' => null,
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Tenant Database
        |--------------------------------------------------------------------------
        */

        if (! $restaurant->tenantDatabase) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Restaurant tenant database configuration is missing.',

                'data' => null,

                'errors' => null,
            ], 503);
        }


        /*
        |--------------------------------------------------------------------------
        | Tenant Database Status
        |--------------------------------------------------------------------------
        */

        if (
            $restaurant->tenantDatabase->status !== 'active'
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    'Restaurant tenant database is not active.',

                'data' => null,

                'errors' => null,
            ], 503);
        }


        /*
        |--------------------------------------------------------------------------
        | Connect Tenant Database
        |--------------------------------------------------------------------------
        */

        try {
            $this->connectionManager->connect(
                $restaurant
            );

            /*
            |--------------------------------------------------------------------------
            | Make Tenant Connection Default
            |--------------------------------------------------------------------------
            */

            DB::purge('tenant');

            DB::reconnect('tenant');

            DB::setDefaultConnection('tenant');


            /*
            |--------------------------------------------------------------------------
            | Tenant Connection Safety Check
            |--------------------------------------------------------------------------
            */

            DB::connection('tenant')->getPdo();

        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,

                'message' =>
                    'Restaurant database could not be connected.',

                'data' => null,

                'errors' => null,
            ], 503);
        }


        /*
        |--------------------------------------------------------------------------
        | Store Current Tenant
        |--------------------------------------------------------------------------
        */

        $this->tenantManager->set(
            $restaurant
        );


        /*
        |--------------------------------------------------------------------------
        | Request Attributes
        |--------------------------------------------------------------------------
        */

        $request->attributes->set(
            'tenant',
            $restaurant
        );

        $request->attributes->set(
            'tenant_domain',
            $domain
        );


        /*
        |--------------------------------------------------------------------------
        | Continue Request
        |--------------------------------------------------------------------------
        */

        try {
            return $next($request);

        } finally {
            /*
            |--------------------------------------------------------------------------
            | Restore Central Connection
            |--------------------------------------------------------------------------
            */

            DB::setDefaultConnection('central');

            $this->tenantManager->clear();

            $this->connectionManager->disconnect();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Restaurant Subscription
    |--------------------------------------------------------------------------
    */

    private function validateRestaurantSubscription(
        Restaurant $restaurant
    ): ?JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | Get Latest Subscription
        |--------------------------------------------------------------------------
        |
        | Always query Central DB so tenant requests use the current
        | subscription state.
        |
        */

        $subscription = Subscription::on('central')
            ->where(
                'restaurant_id',
                $restaurant->id
            )
            ->latest('id')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | No Subscription
        |--------------------------------------------------------------------------
        */

        if (! $subscription) {
            return response()->json([
                'success' => false,

                'message' =>
                    'No active subscription was found for this restaurant.',

                'data' => null,

                'errors' => null,
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Cancelled / Expired
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $subscription->status,
                [
                    'cancelled',
                    'expired',
                ],
                true
            )
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    'This restaurant subscription is no longer active.',

                'data' => [
                    'subscription_status' =>
                        $subscription->status,
                ],

                'errors' => null,
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Pending / Unknown Status
        |--------------------------------------------------------------------------
        */

        if ($subscription->status !== 'active') {
            return response()->json([
                'success' => false,

                'message' =>
                    'This restaurant subscription is not active.',

                'data' => [
                    'subscription_status' =>
                        $subscription->status,
                ],

                'errors' => null,
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Automatic Expiry Safety Check
        |--------------------------------------------------------------------------
        */

        if (
            $subscription->end_at !== null &&
            $subscription->end_at->isPast()
        ) {
            /*
            |--------------------------------------------------------------------------
            | Lock + Re-check
            |--------------------------------------------------------------------------
            |
            | Prevent multiple simultaneous requests from performing the
            | same expiration operation.
            |
            */

            DB::connection('central')->transaction(
                function () use (
                    $restaurant,
                    $subscription
                ): void {

                    $freshSubscription =
                        Subscription::on('central')
                            ->lockForUpdate()
                            ->find(
                                $subscription->id
                            );

                    if (! $freshSubscription) {
                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Another process may already have handled it.
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $freshSubscription->status !== 'active'
                    ) {
                        return;
                    }


                    if (
                        ! $freshSubscription->end_at ||
                        $freshSubscription->end_at->isFuture()
                    ) {
                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Expire Subscription
                    |--------------------------------------------------------------------------
                    */

                    $freshSubscription->forceFill([
                        'status' => 'expired',
                    ])->save();


                    /*
                    |--------------------------------------------------------------------------
                    | Lock Restaurant
                    |--------------------------------------------------------------------------
                    */

                    $freshRestaurant =
                        Restaurant::on('central')
                            ->lockForUpdate()
                            ->find(
                                $restaurant->id
                            );


                    if ($freshRestaurant) {
                        $freshRestaurant->forceFill([
                            'status' => 'inactive',
                        ])->save();
                    }
                }
            );


            return response()->json([
                'success' => false,

                'message' =>
                    'This restaurant subscription has expired.',

                'data' => [
                    'subscription_status' =>
                        'expired',

                    'restaurant_status' =>
                        'inactive',
                ],

                'errors' => null,
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Restaurant Must Be Active
        |--------------------------------------------------------------------------
        */

        $restaurant->refresh();

        if ($restaurant->status !== 'active') {
            return response()->json([
                'success' => false,

                'message' =>
                    'This restaurant is currently inactive.',

                'data' => [
                    'restaurant_status' =>
                        $restaurant->status,

                    'subscription_status' =>
                        $subscription->status,
                ],

                'errors' => null,
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Access Allowed
        |--------------------------------------------------------------------------
        */

        return null;
    }
}