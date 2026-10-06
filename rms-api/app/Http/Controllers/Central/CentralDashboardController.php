<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
class CentralDashboardController extends Controller
{
    public function overview(): JsonResponse
    {
        $totalRestaurants =
            DB::connection('central')
                ->table('restaurants')
                ->count();

        $activeRestaurants =
            DB::connection('central')
                ->table('restaurants')
                ->where(
                    'status',
                    'active'
                )
                ->count();

        $pendingRestaurants =
            DB::connection('central')
                ->table('restaurants')
                ->where(
                    'status',
                    'pending'
                )
                ->count();

        $totalPlans =
            DB::connection('central')
                ->table('subscription_plans')
                ->where(
                    'is_active',
                    true
                )
                ->count();

        $activeSubscriptions =
            DB::connection('central')
                ->table('subscriptions')
                ->where(
                    'status',
                    'active'
                )
                ->count();

        $pendingSubscriptions =
            DB::connection('central')
                ->table('subscriptions')
                ->where(
                    'status',
                    'pending'
                )
                ->count();

        $activeTenantDatabases =
            DB::connection('central')
                ->table('tenant_databases')
                ->where(
                    'status',
                    'active'
                )
                ->count();

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Central dashboard overview retrieved successfully.',

            'data' => [
                'restaurants' => [
                    'total' =>
                        $totalRestaurants,

                    'active' =>
                        $activeRestaurants,

                    'pending' =>
                        $pendingRestaurants,
                ],

                'subscriptions' => [
                    'active' =>
                        $activeSubscriptions,

                    'pending' =>
                        $pendingSubscriptions,
                ],

                'subscription_plans' => [
                    'active' =>
                        $totalPlans,
                ],

                'tenant_databases' => [
                    'active' =>
                        $activeTenantDatabases,
                ],
            ],

            'errors' =>
                null,
        ]);
    }
}