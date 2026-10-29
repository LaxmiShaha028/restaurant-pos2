<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Central\Restaurant;
use App\Models\Central\Subscription;
use App\Models\SystemSetting;
use App\Services\Tenant\TenantConnectionManager;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Http\JsonResponse;

class TenantEntryController extends Controller
{
    public function publicLandingPage(
        string $tenantKey,
        TenantConnectionManager $connectionManager
    ): JsonResponse {
        $tenantKey = strtolower(trim($tenantKey));

        $restaurant = Restaurant::on('central')
            ->with('tenantDatabase')
            ->where('tenant_key', $tenantKey)
            ->first();

        if (
            ! $restaurant
            || $restaurant->status !== 'active'
            || ! $restaurant->tenantDatabase
            || $restaurant->tenantDatabase->status !== 'active'
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant landing page was not found.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        try {
            $connectionManager->connect($restaurant);
            $settings = SystemSetting::query()->first();

            return response()->json([
                'success' => true,
                'message' => 'Restaurant landing page loaded successfully.',
                'data' => [
                    'restaurant' => [
                        'name' => $restaurant->name,
                        'tenant_key' => $restaurant->tenant_key,
                    ],
                    'landing_page_settings' =>
                        $settings?->landing_page_settings ?? [],
                ],
                'errors' => null,
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Restaurant landing page is temporarily unavailable.',
                'data' => null,
                'errors' => null,
            ], 503);
        } finally {
            $connectionManager->disconnect();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Tenant
    |--------------------------------------------------------------------------
    |
    | Public endpoint used by the frontend tenant entry page.
    |
    | IMPORTANT:
    | Subscription end_at is checked before tenant access is granted.
    |
    */

    public function resolve(
        string $tenantKey,
        SubscriptionLifecycleService $lifecycleService
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Normalize Tenant Key
        |--------------------------------------------------------------------------
        */

        $tenantKey = strtolower(
            trim($tenantKey)
        );

        if ($tenantKey === '') {
            return response()->json([
                'success' => false,
                'message' => 'Tenant key is required.',
                'data' => null,
                'errors' => null,
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Find Restaurant
        |--------------------------------------------------------------------------
        */

        $restaurant = Restaurant::on('central')
            ->where(
                'tenant_key',
                $tenantKey
            )
            ->first();

        if (! $restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant was not found.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Synchronize Subscription Lifecycle
        |--------------------------------------------------------------------------
        |
        | This makes public tenant entry safe even when the scheduler has not yet
        | executed. If end_at has passed:
        |
        | subscription -> expired
        | restaurant   -> inactive
        |
        */

        $lifecycleResult =
            $lifecycleService
                ->synchronizeRestaurant(
                    $restaurant->id
                );

        $restaurant = $lifecycleResult['restaurant']
            ?? $restaurant->fresh();

        $subscription =
            $lifecycleResult['subscription']
            ?? Subscription::on('central')
                ->with('plan')
                ->where(
                    'restaurant_id',
                    $restaurant->id
                )
                ->latest('id')
                ->first();

        /*
        |--------------------------------------------------------------------------
        | Subscription Validation
        |--------------------------------------------------------------------------
        */

        if (! $subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No subscription was found for this restaurant.',
                'data' => [
                    'restaurant' => [
                        'id' => $restaurant->id,
                        'name' => $restaurant->name,
                        'tenant_key' => $restaurant->tenant_key,
                        'status' => $restaurant->status,
                    ],
                ],
                'errors' => null,
            ], 403);
        }

        if ($subscription->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' =>
                    $subscription->status === 'expired'
                        ? 'This restaurant subscription has expired.'
                        : 'This restaurant subscription is not active.',
                'data' => [
                    'restaurant' => [
                        'id' => $restaurant->id,
                        'name' => $restaurant->name,
                        'tenant_key' => $restaurant->tenant_key,
                        'status' => $restaurant->status,
                    ],
                    'subscription' => [
                        'id' => $subscription->id,
                        'status' => $subscription->status,
                        'end_at' => $subscription->end_at,
                    ],
                ],
                'errors' => null,
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Hard Date Safety Check
        |--------------------------------------------------------------------------
        |
        | Normally synchronizeRestaurant() already handles this. This additional
        | check makes the access rule explicit in this public endpoint.
        |
        */

        if (
            $subscription->end_at !== null
            &&
            $subscription->end_at->lte(now())
        ) {
            $lifecycleService
                ->synchronizeRestaurant(
                    $restaurant->id
                );

            return response()->json([
                'success' => false,
                'message' => 'This restaurant subscription has expired.',
                'data' => [
                    'restaurant_status' => 'inactive',
                    'subscription_status' => 'expired',
                    'end_at' => $subscription->end_at,
                ],
                'errors' => null,
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Restaurant Status
        |--------------------------------------------------------------------------
        */

        $restaurant->refresh();

        if ($restaurant->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This restaurant is not active.',
                'data' => [
                    'restaurant' => [
                        'id' => $restaurant->id,
                        'name' => $restaurant->name,
                        'tenant_key' => $restaurant->tenant_key,
                        'status' => $restaurant->status,
                    ],
                ],
                'errors' => null,
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Reload Tenant Relations
        |--------------------------------------------------------------------------
        */

        $restaurant->load([
            'tenantDatabase',
            'domains',
        ]);

        $tenantDatabase =
            $restaurant->tenantDatabase;

        if (! $tenantDatabase) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant database was not found.',
                'data' => null,
                'errors' => null,
            ], 503);
        }

        if ($tenantDatabase->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Tenant database is not active.',
                'data' => [
                    'restaurant' => [
                        'id' => $restaurant->id,
                        'name' => $restaurant->name,
                        'tenant_key' => $restaurant->tenant_key,
                        'status' => $restaurant->status,
                    ],
                    'tenant_database' => [
                        'status' => $tenantDatabase->status,
                    ],
                ],
                'errors' => null,
            ], 503);
        }

        /*
        |--------------------------------------------------------------------------
        | Primary Domain
        |--------------------------------------------------------------------------
        */

        $primaryDomain =
            $restaurant->domains
                ->first(
                    fn ($domain) =>
                        (bool) $domain->is_primary
                );

        /*
        |--------------------------------------------------------------------------
        | Success Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Tenant resolved successfully.',
            'data' => [
                'restaurant' => [
                    'id' => $restaurant->id,
                    'name' => $restaurant->name,
                    'slug' => $restaurant->slug,
                    'logo_path' => $restaurant->logo_path,
                    'tenant_key' => $restaurant->tenant_key,
                    'status' => $restaurant->status,
                ],

                'tenant_database' => [
                    'status' => $tenantDatabase->status,
                ],

                'domain' => $primaryDomain
                    ? [
                        'domain' => $primaryDomain->domain,
                        'is_verified' =>
                            (bool) $primaryDomain->is_verified,
                        'is_primary' =>
                            (bool) $primaryDomain->is_primary,
                    ]
                    : null,

                'subscription' => [
                    'id' => $subscription->id,
                    'status' => $subscription->status,
                    'start_at' => $subscription->start_at,
                    'end_at' => $subscription->end_at,
                    'trial_start_at' =>
                        $subscription->trial_start_at,
                    'trial_end_at' =>
                        $subscription->trial_end_at,
                    'plan' => $subscription->plan
                        ? [
                            'id' => $subscription->plan->id,
                            'name' => $subscription->plan->name,
                            'slug' => $subscription->plan->slug,
                        ]
                        : null,
                ],
            ],
            'errors' => null,
        ]);
    }
}
