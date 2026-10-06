<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreRestaurantRequest;
use App\Models\Central\SubscriptionPlan;
use App\Services\Tenant\TenantProvisioningService;
use App\Services\SubscriptionLifecycleService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;
use App\Models\Central\Restaurant;
use App\Models\Central\Subscription;



class RestaurantController extends Controller
{
    public function plans(): JsonResponse
{
    $plans =
        \App\Models\Central\SubscriptionPlan::on('central')
            ->where(
                'is_active',
                true
            )
            ->orderBy(
                'sort_order'
            )
            ->get([
                'id',
                'name',
                'slug',
                'description',
                'price',
                'currency',
                'billing_interval',
                'billing_interval_count',
                'trial_days',
                'features',
                'is_active',
                'sort_order',
            ]);

    return response()->json([
        'success' =>
            true,

        'message' =>
            'Subscription plans retrieved successfully.',

        'data' =>
            $plans,

        'errors' =>
            null,
    ]);
}

public function store(
    StoreRestaurantRequest $request,
    TenantProvisioningService $provisioningService
): JsonResponse {
    /*
    |--------------------------------------------------------------------------
    | Validated Data
    |--------------------------------------------------------------------------
    */

    $data =
        $request->validated();
/*
|--------------------------------------------------------------------------
| Resolve Tenant Admin Username
|--------------------------------------------------------------------------
|
| Frontend sends `admin_username`.
| Tenant provisioning uses `username`.
|
*/

$adminUsername =
    trim(
        (string) (
            $data['admin_username']
            ?? ''
        )
    );

if (
    $adminUsername !== ''
) {

    $data['username'] =
        $adminUsername;

} elseif (
    empty(
        $data['username']
    )
) {

    $data['username'] =
        $this->generateUsername(
            $data['owner_email']
        );
}

    /*
    |--------------------------------------------------------------------------
    | Resolve Subscription Plan
    |--------------------------------------------------------------------------
    */

    $plan =
        SubscriptionPlan::on('central')
            ->where(
                'id',
                $data['subscription_plan_id']
            )
            ->where(
                'is_active',
                true
            )
            ->first();

    if (
        ! $plan
    ) {

        return response()->json([
            'success' =>
                false,

            'message' =>
                'The selected subscription plan is unavailable.',

            'data' =>
                null,

            'errors' => [
                'subscription_plan_id' => [
                    'Selected subscription plan is not available.',
                ],
            ],

        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | Detect Trial
    |--------------------------------------------------------------------------
    */

    $isTrial =
        (float) $plan->price <= 0
        &&
        (int) $plan->trial_days > 0;

    /*
    |--------------------------------------------------------------------------
    | Trial Restaurant
    |--------------------------------------------------------------------------
    */

    if (
        $isTrial
    ) {

        $data['trial_days'] =
            (int) $plan->trial_days;

        $data['subscription_status'] =
            'active';

        try {

            $result =
                $provisioningService->provision(
                    $data
                );

            $result['plan'] = [
                'id' =>
                    $plan->id,

                'name' =>
                    $plan->name,

                'slug' =>
                    $plan->slug,

                'price' =>
                    $plan->price,

                'currency' =>
                    $plan->currency,

                'trial_days' =>
                    $plan->trial_days,
            ];

            $result['payment_required'] =
                false;

            return response()->json([
                'success' =>
                    true,

                'message' =>
                    'Trial restaurant created successfully.',

                'data' =>
                    $result,

                'errors' =>
                    null,

            ], 201);

        } catch (
            Throwable $exception
        ) {

            report(
                $exception
            );

            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Restaurant creation failed.',

                'data' =>
                    null,

                'errors' => [
                    'provisioning' => [
                        $exception->getMessage(),
                    ],
                ],

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Paid / Enterprise Restaurant
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Do NOT provision the tenant yet.
    | Restaurant remains pending until manual payment is completed.
    |
    */

    $data['trial_days'] =
        0;

    $data['subscription_status'] =
        'pending';

    try {

        $result =
            $provisioningService
                ->createPendingRestaurant(
                    $data
                );

        $result['plan'] = [
            'id' =>
                $plan->id,

            'name' =>
                $plan->name,

            'slug' =>
                $plan->slug,

            'price' =>
                $plan->price,

            'currency' =>
                $plan->currency,

            'trial_days' =>
                $plan->trial_days,
        ];

        $result['payment_required'] =
            true;

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Restaurant created and is awaiting payment.',

            'data' =>
                $result,

            'errors' =>
                null,

        ], 201);

    } catch (
        Throwable $exception
    ) {

        report(
            $exception
        );

        return response()->json([
            'success' =>
                false,

            'message' =>
                'Restaurant creation failed.',

            'data' =>
                null,

            'errors' => [
                'creation' => [
                    $exception->getMessage(),
                ],
            ],

        ], 500);
    }
}

    public function show(
    int $restaurant,
    SubscriptionLifecycleService $lifecycleService
): JsonResponse {
    /*
    |--------------------------------------------------------------------------
    | Synchronize Subscription Before Returning Details
    |--------------------------------------------------------------------------
    */

    $lifecycleService
        ->synchronizeRestaurant(
            $restaurant
        );

    $restaurantModel =
        \App\Models\Central\Restaurant::on('central')
            ->with([
                'tenantDatabase',
                'domains',
                'subscriptions.plan',
            ])
            ->find(
                $restaurant
            );

    if (! $restaurantModel) {

        return response()->json([
            'success' =>
                false,

            'message' =>
                'Restaurant was not found.',

            'data' =>
                null,

            'errors' =>
                null,

        ], 404);
    }

    return response()->json([
        'success' =>
            true,

        'message' =>
            'Restaurant details retrieved successfully.',

        'data' => [
            'restaurant' => [
                'id' =>
                    $restaurantModel->id,

                'name' =>
                    $restaurantModel->name,

                'slug' =>
                    $restaurantModel->slug,

                'logo_path' =>
                    $restaurantModel->logo_path,

                'owner_name' =>
                    $restaurantModel->owner_name,

                'owner_email' =>
                    $restaurantModel->owner_email,

                'phone' =>
                    $restaurantModel->phone,

                'address' =>
                    $restaurantModel->address,

                'city' =>
                    $restaurantModel->city,

                'country' =>
                    $restaurantModel->country,

                'tenant_key' =>
                    $restaurantModel->tenant_key,

                'status' =>
                    $restaurantModel->status,
            ],

            'tenant_database' =>
                $restaurantModel->tenantDatabase,

            'domains' =>
                $restaurantModel->domains,

            'subscriptions' =>
                $restaurantModel->subscriptions,

        ],

        'errors' =>
            null,
    ]);
}

    public function index(): JsonResponse
{
    /*
    |--------------------------------------------------------------------------
    | Latest Subscription Per Restaurant
    |--------------------------------------------------------------------------
    */

    $latestSubscriptions =
        DB::connection('central')
            ->table('subscriptions')
            ->selectRaw(
                'MAX(id) as id, restaurant_id'
            )
            ->groupBy(
                'restaurant_id'
            );


    /*
    |--------------------------------------------------------------------------
    | Restaurant Directory
    |--------------------------------------------------------------------------
    |
    | Only return the information needed by the Super Admin restaurant list:
    |
    | - Restaurant / owner
    | - Restaurant status
    | - Current plan
    | - Tenant key (frontend builds login URL)
    | - Current subscription status
    | - Current subscription end date
    |
    */

    $restaurants =
        DB::connection('central')
            ->table('restaurants')

            ->leftJoinSub(
                $latestSubscriptions,
                'latest_subscriptions',
                function ($join): void {
                    $join->on(
                        'restaurants.id',
                        '=',
                        'latest_subscriptions.restaurant_id'
                    );
                }
            )

            ->leftJoin(
                'subscriptions as current_subscription',
                'latest_subscriptions.id',
                '=',
                'current_subscription.id'
            )

            ->leftJoin(
                'subscription_plans as current_plan',
                'current_subscription.subscription_plan_id',
                '=',
                'current_plan.id'
            )

            ->select([
                'restaurants.id',
                'restaurants.name',
                'restaurants.owner_name',
                'restaurants.owner_email',
                'restaurants.phone',
                'restaurants.city',
                'restaurants.country',
                'restaurants.status',
                'restaurants.tenant_key',

                'current_plan.id as plan_id',
                'current_plan.name as plan_name',
                'current_plan.slug as plan_slug',

                'current_subscription.id as subscription_id',
                'current_subscription.status as subscription_status',
                'current_subscription.end_at as subscription_end_at',
            ])

            ->orderByDesc(
                'restaurants.id'
            )

            ->get();


    return response()->json([
        'success' =>
            true,

        'message' =>
            'Restaurants retrieved successfully.',

        'data' =>
            $restaurants,

        'errors' =>
            null,
    ]);
}
/*
|--------------------------------------------------------------------------
| Update Restaurant Status
|--------------------------------------------------------------------------
|
| Lifecycle rules:
|
| 1. Restaurant can become ACTIVE only when the latest subscription
|    is ACTIVE and not expired.
|
| 2. EXPIRED / CANCELLED / PENDING subscription cannot be activated.
|
| 3. If an active subscription has already passed its end_at,
|    synchronize:
|
|       subscription -> expired
|       restaurant   -> inactive
|
| 4. Manually setting INACTIVE / SUSPENDED / PENDING does not modify
|    the subscription. This allows Super Admin to temporarily disable
|    restaurant access without destroying subscription history.
|
*/

public function updateStatus(
    Request $request,
    int $restaurant
): JsonResponse {

    /*
    |--------------------------------------------------------------------------
    | Validate
    |--------------------------------------------------------------------------
    */

    $validated =
        $request->validate([
            'status' => [
                'required',
                'string',
                'in:active,inactive,suspended,pending',
            ],
        ]);


    /*
    |--------------------------------------------------------------------------
    | Central Transaction
    |--------------------------------------------------------------------------
    */

    return DB::connection('central')->transaction(
        function () use (
            $validated,
            $restaurant
        ): JsonResponse {

            /*
            |--------------------------------------------------------------------------
            | Lock Restaurant
            |--------------------------------------------------------------------------
            */

            $restaurantModel =
                Restaurant::on('central')
                    ->lockForUpdate()
                    ->find($restaurant);

            if (! $restaurantModel) {

                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Restaurant was not found.',

                    'data' =>
                        null,

                    'errors' =>
                        null,

                ], 404);
            }


            /*
            |--------------------------------------------------------------------------
            | No Change
            |--------------------------------------------------------------------------
            */

            if (
                $restaurantModel->status ===
                $validated['status']
            ) {

                return response()->json([
                    'success' =>
                        true,

                    'message' =>
                        'Restaurant status is already set to the requested value.',

                    'data' => [
                        'restaurant' => [
                            'id' =>
                                $restaurantModel->id,

                            'name' =>
                                $restaurantModel->name,

                            'status' =>
                                $restaurantModel->status,
                        ],
                    ],

                    'errors' =>
                        null,

                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | ACTIVE Protection
            |--------------------------------------------------------------------------
            |
            | A restaurant may become active only when its latest
            | subscription is active and still valid.
            |
            */

            if (
                $validated['status'] === 'active'
            ) {

                $subscription =
                    Subscription::on('central')
                        ->lockForUpdate()
                        ->where(
                            'restaurant_id',
                            $restaurantModel->id
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
                        'success' =>
                            false,

                        'message' =>
                            'Restaurant cannot be activated because no subscription was found.',

                        'data' => [
                            'restaurant_status' =>
                                $restaurantModel->status,

                        ],

                        'errors' =>
                            null,

                    ], 422);
                }


                /*
                |--------------------------------------------------------------------------
                | Automatically Expired
                |--------------------------------------------------------------------------
                |
                | If the subscription is marked active but the end date
                | has already passed, synchronize both states immediately.
                |
                */

                if (
                    $subscription->status === 'active'
                    &&
                    $subscription->end_at !== null
                    &&
                    $subscription->end_at->isPast()
                ) {

                    $subscription->forceFill([
                        'status' =>
                            'expired',
                    ])->save();


                    $restaurantModel->forceFill([
                        'status' =>
                            'inactive',
                    ])->save();


                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'Restaurant cannot be activated because its subscription has expired.',

                        'data' => [
                            'restaurant_status' =>
                                'inactive',

                            'subscription_status' =>
                                'expired',
                        ],

                        'errors' =>
                            null,

                    ], 422);
                }


                /*
                |--------------------------------------------------------------------------
                | Subscription Must Be Active
                |--------------------------------------------------------------------------
                */

                if (
                    $subscription->status !== 'active'
                ) {

                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'Restaurant cannot be activated while its subscription is not active.',

                        'data' => [
                            'restaurant_status' =>
                                $restaurantModel->status,

                            'subscription_status' =>
                                $subscription->status,
                        ],

                        'errors' =>
                            null,

                    ], 422);
                }


                /*
                |--------------------------------------------------------------------------
                | Subscription Must Not Be Expired
                |--------------------------------------------------------------------------
                */

                if (
                    $subscription->end_at !== null
                    &&
                    $subscription->end_at->isPast()
                ) {

                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'Restaurant cannot be activated because the subscription has expired.',

                        'data' => [
                            'restaurant_status' =>
                                $restaurantModel->status,

                            'subscription_status' =>
                                $subscription->status,
                        ],

                        'errors' =>
                            null,

                    ], 422);
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Apply Restaurant Status
            |--------------------------------------------------------------------------
            */

            $restaurantModel->forceFill([
                'status' =>
                    $validated['status'],
            ])->save();


            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' =>
                    true,

                'message' =>
                    'Restaurant status updated successfully.',

                'data' => [
                    'restaurant' => [
                        'id' =>
                            $restaurantModel->id,

                        'name' =>
                            $restaurantModel->name,

                        'status' =>
                            $restaurantModel->status,
                    ],
                ],

                'errors' =>
                    null,

            ]);
        }
    );
}
/*
|--------------------------------------------------------------------------
| Sync Tenant Roles & Permissions
|--------------------------------------------------------------------------
|
| Used by Super Admin to sync newly added tenant roles and permissions
| into an already existing tenant database.
|
*/

public function syncTenantPermissions(
    int $restaurant,
    TenantProvisioningService $provisioningService
): JsonResponse {

    /*
    |--------------------------------------------------------------------------
    | Find Restaurant
    |--------------------------------------------------------------------------
    */

    $restaurantModel =
        Restaurant::on('central')
            ->find($restaurant);

    if (
        ! $restaurantModel
    ) {

        return response()->json([
            'success' =>
                false,

            'message' =>
                'Restaurant was not found.',

            'data' =>
                null,

            'errors' =>
                null,

        ], 404);
    }


    /*
    |--------------------------------------------------------------------------
    | Active Tenant Protection
    |--------------------------------------------------------------------------
    */

    if (
        $restaurantModel->status !== 'active'
    ) {

        return response()->json([
            'success' =>
                false,

            'message' =>
                'Roles and permissions can only be synced for an active restaurant.',

            'data' => [
                'status' =>
                    $restaurantModel->status,
            ],

            'errors' =>
                null,

        ], 422);
    }


    /*
    |--------------------------------------------------------------------------
    | Sync Roles & Permissions
    |--------------------------------------------------------------------------
    */

    try {

        $result =
            $provisioningService
                ->syncTenantRolesAndPermissions(
                    $restaurantModel
                );


        /*
        |--------------------------------------------------------------------------
        | Success Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Tenant roles and permissions synced successfully.',

            'data' =>
                $result,

            'errors' =>
                null,

        ]);

    } catch (
        Throwable $exception
    ) {

        report(
            $exception
        );

        return response()->json([
            'success' =>
                false,

            'message' =>
                'Failed to sync tenant roles and permissions.',

            'data' =>
                null,

            'errors' => [
                'sync' => [
                    $exception->getMessage(),
                ],
            ],

        ], 500);
    }
}
/*
|--------------------------------------------------------------------------
| Sync All Tenant Roles & Permissions
|--------------------------------------------------------------------------
*/

public function syncAllTenantPermissions(
    TenantProvisioningService $provisioningService
): JsonResponse {

    try {

        $result =
            $provisioningService
                ->syncAllTenantRolesAndPermissions();


        return response()->json([
            'success' =>
                true,

            'message' =>
                'All active tenant roles and permissions synced successfully.',

            'data' =>
                $result,

            'errors' =>
                null,

        ]);

    } catch (
        Throwable $exception
    ) {

        report(
            $exception
        );


        return response()->json([
            'success' =>
                false,

            'message' =>
                'Failed to sync tenant roles and permissions.',

            'data' =>
                null,

            'errors' => [
                'sync' => [
                    $exception->getMessage(),
                ],
            ],

        ], 500);
    }
}
/*
|--------------------------------------------------------------------------
| Reset Tenant Admin Credentials
|--------------------------------------------------------------------------
*/

public function resetAdminCredentials(
    int $restaurant,
    TenantProvisioningService $provisioningService
): JsonResponse {

    /*
    |--------------------------------------------------------------------------
    | Find Restaurant
    |--------------------------------------------------------------------------
    */

    $restaurantModel =
        Restaurant::on('central')
            ->find($restaurant);

    if (
        ! $restaurantModel
    ) {

        return response()->json([
            'success' =>
                false,

            'message' =>
                'Restaurant was not found.',

            'data' =>
                null,

            'errors' =>
                null,

        ], 404);
    }


    /*
    |--------------------------------------------------------------------------
    | Active Restaurant Protection
    |--------------------------------------------------------------------------
    */

    if (
        $restaurantModel->status !== 'active'
    ) {

        return response()->json([
            'success' =>
                false,

            'message' =>
                'Admin credentials can only be reset for an active restaurant.',

            'data' => [
                'status' =>
                    $restaurantModel->status,
            ],

            'errors' =>
                null,

        ], 422);
    }


    /*
    |--------------------------------------------------------------------------
    | Reset Credentials
    |--------------------------------------------------------------------------
    */

    try {

        $credentials =
            $provisioningService
                ->resetTenantAdminCredentials(
                    $restaurantModel
                );


        /*
        |--------------------------------------------------------------------------
        | Success Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Tenant admin credentials reset successfully.',

            'data' => [

                'restaurant' => [
                    'id' =>
                        $restaurantModel->id,

                    'name' =>
                        $restaurantModel->name,
                ],

                'admin' =>
                    $credentials,
            ],

            'errors' =>
                null,

        ]);

    } catch (
        Throwable $exception
    ) {

        report(
            $exception
        );

        return response()->json([
            'success' =>
                false,

            'message' =>
                'Failed to reset tenant admin credentials.',

            'data' =>
                null,

            'errors' => [
                'credentials' => [
                    $exception->getMessage(),
                ],
            ],

        ], 500);
    }
}


    private function generateUsername(
        string $email
    ): string {
        $prefix =
            Str::before(
                strtolower(
                    trim($email)
                ),
                '@'
            );

        $prefix =
            preg_replace(
                '/[^a-z0-9_]/',
                '_',
                $prefix
            );

        $prefix =
            trim(
                (string) $prefix,
                '_'
            );

        if (
            $prefix === ''
        ) {
            $prefix = 'admin';
        }

        return substr(
            $prefix,
            0,
            100
        );
    }
}