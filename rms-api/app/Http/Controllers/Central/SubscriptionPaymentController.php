<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Payment;
use App\Models\Central\PaymentMethod;
use App\Models\Central\Restaurant;
use App\Models\Central\Subscription;
use App\Models\Central\SubscriptionPlan;
use App\Services\Tenant\TenantProvisioningService;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class SubscriptionPaymentController extends Controller
{
        /*
    |--------------------------------------------------------------------------
    | Trial To Paid Activation / Upgrade
    |--------------------------------------------------------------------------
    |
    | Converts the CURRENT trial subscription into a paid subscription.
    |
    | Supports:
    | - Active trial -> paid upgrade.
    | - Expired trial -> paid activation and restaurant reactivation.
    |
    | IMPORTANT:
    |
    | - No new restaurant is created.
    | - No new tenant database is created.
    | - No tenant provisioning is performed.
    | - Existing tenant users/data remain untouched.
    | - The same subscription record is upgraded.
    |
    */

    public function upgradeTrialToPaid(
        Request $request,
        int $restaurant
    ): JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'subscription_id' => [
                    'required',
                    'integer',
                ],

                'subscription_plan_id' => [
                    'required',
                    'integer',
                ],

                'payment_method' => [
                    'required',
                    'string',
                    'max:50',
                ],

                'transaction_id' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'reference' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'notes' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],
            ]);

        abort_if(strtolower($validated['payment_method']) === 'stripe', 422, 'Stripe payments must use Stripe Checkout.');


        /*
        |--------------------------------------------------------------------------
        | Payment Method Specific Validation
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                strtolower(
                    $validated['payment_method']
                ),
                [
                    'bkash',
                    'nagad',
                ],
                true
            )
            &&
            blank(
                $validated['transaction_id']
                ??
                null
            )
        ) {

            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Transaction ID is required for bKash or Nagad payments.',

                'data' =>
                    null,

                'errors' => [
                    'transaction_id' => [
                        'Transaction ID is required for this payment method.',
                    ],
                ],

            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Central Transaction
        |--------------------------------------------------------------------------
        */

        return DB::connection('central')->transaction(
            function () use (
                $request,
                $restaurant,
                $validated
            ): JsonResponse {

                /*
                |--------------------------------------------------------------------------
                | Lock Restaurant
                |--------------------------------------------------------------------------
                */

                $restaurantModel =
                    Restaurant::on('central')
                        ->lockForUpdate()
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


                /*
                |--------------------------------------------------------------------------
                | Lock Current Subscription
                |--------------------------------------------------------------------------
                */

                $subscription =
                    Subscription::on('central')
                        ->where(
                            'id',
                            $validated['subscription_id']
                        )
                        ->where(
                            'restaurant_id',
                            $restaurantModel->id
                        )
                        ->lockForUpdate()
                        ->first();


                if (! $subscription) {

                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'Subscription was not found for this restaurant.',

                        'data' =>
                            null,

                        'errors' =>
                            null,

                    ], 404);
                }


                /*
                |--------------------------------------------------------------------------
                | Current Subscription Protection
                |--------------------------------------------------------------------------
                |
                | Only the latest subscription for this restaurant may be
                | converted from trial to paid.
                |
                */

                $latestSubscriptionId =
                    Subscription::on('central')
                        ->where(
                            'restaurant_id',
                            $restaurantModel->id
                        )
                        ->max(
                            'id'
                        );


                if (
                    (int) $latestSubscriptionId
                    !==
                    (int) $subscription->id
                ) {

                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'Only the current subscription can be activated or upgraded.',

                        'data' => [
                            'current_subscription_id' =>
                                $latestSubscriptionId,
                        ],

                        'errors' =>
                            null,

                    ], 409);
                }


                /*
                |--------------------------------------------------------------------------
                | Trial Subscription Status
                |--------------------------------------------------------------------------
                |
                | Active trial:
                |     can upgrade before expiry.
                |
                | Expired trial:
                |     can choose a paid plan, complete payment, and reactivate
                |     the existing restaurant and tenant access.
                |
                */

                if (
                    ! in_array(
                        $subscription->status,
                        [
                            'active',
                            'expired',
                        ],
                        true
                    )
                ) {

                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'Only an active or expired trial subscription can use this flow.',

                        'data' => [
                            'subscription_status' =>
                                $subscription->status,
                        ],

                        'errors' =>
                            null,

                    ], 422);
                }


                /*
                |--------------------------------------------------------------------------
                | Trial Validation
                |--------------------------------------------------------------------------
                |
                | Your current trial subscriptions are stored as ACTIVE,
                | therefore trial_end_at is used to identify a live trial.
                |
                */

                if (
                    ! $subscription->trial_end_at
                ) {

                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'This subscription is not currently a trial subscription.',

                        'data' =>
                            null,

                        'errors' =>
                            null,

                    ], 422);
                }


                /*
                |--------------------------------------------------------------------------
                | Active Trial vs Expired Trial
                |--------------------------------------------------------------------------
                */

                $trialHasEnded =
                    $subscription
                        ->trial_end_at
                        ->lte(
                            now()
                        )
                    ||
                    (
                        $subscription->end_at
                        &&
                        $subscription
                            ->end_at
                            ->lte(
                                now()
                            )
                    );


                $isExpiredTrial =
                    $subscription->status === 'expired'
                    ||
                    $trialHasEnded;


                /*
                |--------------------------------------------------------------------------
                | Restaurant Status Protection
                |--------------------------------------------------------------------------
                |
                | An expired trial is expected to make the restaurant inactive.
                | We also tolerate an ACTIVE restaurant if lifecycle sync has not
                | run yet, but we never bypass suspended/pending/failed states.
                |
                */

                $allowedRestaurantStatuses =
                    $isExpiredTrial
                        ? [
                            'active',
                            'inactive',
                        ]
                        : [
                            'active',
                        ];


                if (
                    ! in_array(
                        $restaurantModel->status,
                        $allowedRestaurantStatuses,
                        true
                    )
                ) {

                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            $isExpiredTrial
                                ? 'This expired trial cannot be reactivated while the restaurant is in its current status.'
                                : 'This restaurant must be active to upgrade its running trial.',

                        'data' => [
                            'restaurant_status' =>
                                $restaurantModel->status,

                            'expired_trial' =>
                                $isExpiredTrial,
                        ],

                        'errors' =>
                            null,

                    ], 422);
                }


                /*
                |--------------------------------------------------------------------------
                | Current Trial Plan
                |--------------------------------------------------------------------------
                */

                $currentPlan =
                    SubscriptionPlan::on('central')
                        ->find(
                            $subscription->subscription_plan_id
                        );


                if (! $currentPlan) {

                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'Current subscription plan was not found.',

                        'data' =>
                            null,

                        'errors' =>
                            null,

                    ], 404);
                }


                /*
                |--------------------------------------------------------------------------
                | Confirm Current Plan Is Actually Free/Trial
                |--------------------------------------------------------------------------
                */

                if (
                    (float) $currentPlan->price > 0
                ) {

                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'This subscription is already associated with a paid plan.',

                        'data' => [
                            'current_plan' =>
                                $currentPlan->name,
                        ],

                        'errors' =>
                            null,

                    ], 422);
                }


                /*
                |--------------------------------------------------------------------------
                | Resolve Paid Upgrade Plan
                |--------------------------------------------------------------------------
                */

                $paidPlan =
                    SubscriptionPlan::on('central')
                        ->where(
                            'id',
                            $validated['subscription_plan_id']
                        )
                        ->where(
                            'is_active',
                            true
                        )
                        ->first();


                if (! $paidPlan) {

                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'The selected paid subscription plan is unavailable.',

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
                | Paid Plan Protection
                |--------------------------------------------------------------------------
                */

                if (
                    (float) $paidPlan->price <= 0
                ) {

                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'A paid plan is required for trial upgrade.',

                        'data' =>
                            null,

                        'errors' => [
                            'subscription_plan_id' => [
                                'The selected plan is not a paid plan.',
                            ],
                        ],

                    ], 422);
                }


                /*
                |--------------------------------------------------------------------------
                | Same Plan Protection
                |--------------------------------------------------------------------------
                */

                if (
                    (int) $paidPlan->id ===
                    (int) $currentPlan->id
                ) {

                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'The selected plan is already assigned to this subscription.',

                        'data' =>
                            null,

                        'errors' =>
                            null,

                    ], 422);
                }


                /*
                |--------------------------------------------------------------------------
                | Payment Method
                |--------------------------------------------------------------------------
                */

                $paymentMethod =
                    PaymentMethod::on('central')
                        ->where(
                            'code',
                            $validated['payment_method']
                        )
                        ->where(
                            'status',
                            true
                        )
                        ->first();


                if (! $paymentMethod) {

                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'Selected payment method is not available.',

                        'data' =>
                            null,

                        'errors' => [
                            'payment_method' => [
                                'The selected payment method is invalid or inactive.',
                            ],
                        ],

                    ], 422);
                }


                /*
                |--------------------------------------------------------------------------
                | Duplicate Upgrade Protection
                |--------------------------------------------------------------------------
                */

                $alreadyUpgraded =
                    Payment::on('central')
                        ->where(
                            'subscription_id',
                            $subscription->id
                        )
                        ->where(
                            'status',
                            'paid'
                        )
                        ->get()
                        ->first(
                            function ($payment) {

                                $gatewayResponse =
                                    is_array(
                                        $payment->gateway_response
                                    )
                                        ? $payment->gateway_response
                                        : [];

                                return in_array(
                                    (
                                        $gatewayResponse['payment_type']
                                        ??
                                        null
                                    ),
                                    [
                                        'upgrade',
                                        'expired_trial_activation',
                                    ],
                                    true
                                );
                            }
                        );


                if (
                    $alreadyUpgraded
                ) {

                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            'A paid activation has already been completed for this trial subscription.',

                        'data' => [
                            'payment_id' =>
                                $alreadyUpgraded->id,
                        ],

                        'errors' =>
                            null,

                    ], 409);
                }


                /*
                |--------------------------------------------------------------------------
                | Calculate Paid Subscription Period
                |--------------------------------------------------------------------------
                */

                $startAt =
                    now();

                $endAt =
                    $startAt->copy();


                $interval =
                    strtolower(
                        (string) (
                            $paidPlan->billing_interval
                            ??
                            'month'
                        )
                    );


                $intervalCount =
                    max(
                        1,
                        (int) (
                            $paidPlan->billing_interval_count
                            ??
                            1
                        )
                    );


                if (
                    in_array(
                        $interval,
                        [
                            'day',
                            'days',
                        ],
                        true
                    )
                ) {

                    $endAt->addDays(
                        $intervalCount
                    );

                } elseif (
                    in_array(
                        $interval,
                        [
                            'week',
                            'weeks',
                        ],
                        true
                    )
                ) {

                    $endAt->addWeeks(
                        $intervalCount
                    );

                } elseif (
                    in_array(
                        $interval,
                        [
                            'year',
                            'years',
                        ],
                        true
                    )
                ) {

                    $endAt->addYears(
                        $intervalCount
                    );

                } else {

                    $endAt->addMonths(
                        $intervalCount
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Create Upgrade Payment
                |--------------------------------------------------------------------------
                |
                | Amount always comes from the selected paid plan.
                |
                */

                $payment =
                    Payment::on('central')
                        ->create([
                            'restaurant_id' =>
                                $restaurantModel->id,

                            'subscription_id' =>
                                $subscription->id,

                            'gateway' =>
                                'manual',

                            'transaction_id' =>
                                $validated['transaction_id']
                                ??
                                null,

                            'reference' =>
                                $validated['reference']
                                ??
                                null,

                            'amount' =>
                                $paidPlan->price,

                            'currency' =>
                                $paidPlan->currency,

                            'payment_method' =>
                                $paymentMethod->code,

                            'status' =>
                                'paid',

                            'paid_at' =>
                                now(),

                            'gateway_response' => [
                                'type' =>
                                    'manual',

                                'payment_type' =>
                                    $isExpiredTrial
                                        ? 'expired_trial_activation'
                                        : 'upgrade',

                                'from_plan_id' =>
                                    $currentPlan->id,

                                'from_plan_name' =>
                                    $currentPlan->name,

                                'to_plan_id' =>
                                    $paidPlan->id,

                                'to_plan_name' =>
                                    $paidPlan->name,

                                'payment_method_name' =>
                                    $paymentMethod->name,

                                'previous_subscription_status' =>
                                    $isExpiredTrial
                                        ? 'expired'
                                        : 'active',

                                'expired_trial_reactivation' =>
                                    $isExpiredTrial,

                                'notes' =>
                                    $validated['notes']
                                    ??
                                    null,

                                'recorded_by' =>
                                    $request
                                        ->user()
                                        ?->id,
                            ],
                        ]);


                /*
                |--------------------------------------------------------------------------
                | Convert Trial To Paid
                |--------------------------------------------------------------------------
                |
                | Same subscription row is reused.
                |
                */

                $subscription->forceFill([
                    'subscription_plan_id' =>
                        $paidPlan->id,

                    'status' =>
                        'active',

                    'start_at' =>
                        $startAt,

                    'end_at' =>
                        $endAt,

                    /*
                     * Keep trial_start_at / trial_end_at as historical
                     * information. They are not deleted.
                     */

                    'cancelled_at' =>
                        null,

                    'cancel_reason' =>
                        null,

                    'auto_renew' =>
                        false,
                ])->save();


                /*
                |--------------------------------------------------------------------------
                | Ensure Restaurant Is Active
                |--------------------------------------------------------------------------
                */

                $restaurantModel->forceFill([
                    'status' =>
                        'active',
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
                        $isExpiredTrial
                            ? 'Expired trial activated successfully with the paid subscription.'
                            : 'Trial successfully upgraded to the paid subscription.',

                    'data' => [
                        'payment' =>
                            $payment->fresh(),

                        'subscription' =>
                            $subscription
                                ->fresh()
                                ->load('plan'),

                        'restaurant' => [
                            'id' =>
                                $restaurantModel->id,

                            'status' =>
                                $restaurantModel
                                    ->fresh()
                                    ->status,
                        ],

                        'activation' => [
                            'expired_trial' =>
                                $isExpiredTrial,

                            'tenant_data_preserved' =>
                                true,

                            'tenant_reactivated' =>
                                true,

                            'new_start_at' =>
                                $startAt,

                            'new_end_at' =>
                                $endAt,
                        ],

                        'previous_plan' => [
                            'id' =>
                                $currentPlan->id,

                            'name' =>
                                $currentPlan->name,
                        ],

                        'new_plan' => [
                            'id' =>
                                $paidPlan->id,

                            'name' =>
                                $paidPlan->name,

                            'price' =>
                                $paidPlan->price,

                            'currency' =>
                                $paidPlan->currency,

                            'billing_interval' =>
                                $paidPlan->billing_interval,

                            'billing_interval_count' =>
                                $paidPlan->billing_interval_count,
                        ],
                    ],

                    'errors' =>
                        null,

                ], 200);
            }
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Update Active Paid Subscription Plan
    |--------------------------------------------------------------------------
    |
    | Used for changing an active paid subscription to another paid plan.
    |
    | IMPORTANT:
    |
    | - Existing subscription row is reused.
    | - Existing tenant database is preserved.
    | - No tenant provisioning occurs.
    | - Payment amount always comes from the selected plan.
    |
    */

    public function updatePaidPlan(
        Request $request,
        int $restaurant
    ): JsonResponse {

        $validated =
            $request->validate([
                'subscription_id' => [
                    'required',
                    'integer',
                ],

                'subscription_plan_id' => [
                    'required',
                    'integer',
                ],

                'payment_method' => [
                    'required',
                    'string',
                    'max:50',
                ],

                'transaction_id' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'reference' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'notes' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],
            ]);

        abort_if(strtolower($validated['payment_method']) === 'stripe', 422, 'Stripe payments must use Stripe Checkout.');

        if (
            in_array(
                strtolower(
                    $validated['payment_method']
                ),
                [
                    'bkash',
                    'nagad',
                ],
                true
            )
            &&
            blank(
                $validated['transaction_id']
                ?? null
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction ID is required for bKash or Nagad payments.',
                'data' => null,
                'errors' => [
                    'transaction_id' => [
                        'Transaction ID is required for this payment method.',
                    ],
                ],
            ], 422);
        }

        return DB::connection('central')->transaction(
            function () use (
                $request,
                $restaurant,
                $validated
            ): JsonResponse {

                $restaurantModel =
                    Restaurant::on('central')
                        ->lockForUpdate()
                        ->find(
                            $restaurant
                        );

                if (! $restaurantModel) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Restaurant was not found.',
                        'data' => null,
                        'errors' => null,
                    ], 404);
                }

                $subscription =
                    Subscription::on('central')
                        ->where(
                            'id',
                            $validated['subscription_id']
                        )
                        ->where(
                            'restaurant_id',
                            $restaurantModel->id
                        )
                        ->lockForUpdate()
                        ->first();

                if (! $subscription) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Subscription was not found for this restaurant.',
                        'data' => null,
                        'errors' => null,
                    ], 404);
                }

                if (
                    $subscription->status !== 'active'
                ) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only an active paid subscription can be updated.',
                        'data' => [
                            'subscription_status' => $subscription->status,
                        ],
                        'errors' => null,
                    ], 422);
                }

                $currentPlan =
                    SubscriptionPlan::on('central')
                        ->find(
                            $subscription->subscription_plan_id
                        );

                if (! $currentPlan) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Current subscription plan was not found.',
                        'data' => null,
                        'errors' => null,
                    ], 404);
                }

                if (
                    (float) $currentPlan->price <= 0
                ) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Trial subscriptions must use the trial upgrade flow.',
                        'data' => null,
                        'errors' => null,
                    ], 422);
                }

                $newPlan =
                    SubscriptionPlan::on('central')
                        ->where(
                            'id',
                            $validated['subscription_plan_id']
                        )
                        ->where(
                            'is_active',
                            true
                        )
                        ->first();

                if (! $newPlan) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The selected subscription plan is unavailable.',
                        'data' => null,
                        'errors' => [
                            'subscription_plan_id' => [
                                'Selected subscription plan is not available.',
                            ],
                        ],
                    ], 422);
                }

                if (
                    (float) $newPlan->price <= 0
                ) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A paid plan is required.',
                        'data' => null,
                        'errors' => null,
                    ], 422);
                }

                if (
                    (int) $newPlan->id ===
                    (int) $currentPlan->id
                ) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The selected plan is already active.',
                        'data' => null,
                        'errors' => null,
                    ], 422);
                }

                $paymentMethod =
                    PaymentMethod::on('central')
                        ->where(
                            'code',
                            $validated['payment_method']
                        )
                        ->where(
                            'status',
                            true
                        )
                        ->first();

                if (! $paymentMethod) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected payment method is not available.',
                        'data' => null,
                        'errors' => [
                            'payment_method' => [
                                'The selected payment method is invalid or inactive.',
                            ],
                        ],
                    ], 422);
                }

                $startAt = now();
                $endAt = $startAt->copy();

                $interval =
                    strtolower(
                        (string) (
                            $newPlan->billing_interval
                            ?? 'month'
                        )
                    );

                $intervalCount =
                    max(
                        1,
                        (int) (
                            $newPlan->billing_interval_count
                            ?? 1
                        )
                    );

                if (
                    in_array(
                        $interval,
                        [
                            'day',
                            'days',
                        ],
                        true
                    )
                ) {
                    $endAt->addDays(
                        $intervalCount
                    );
                } elseif (
                    in_array(
                        $interval,
                        [
                            'week',
                            'weeks',
                        ],
                        true
                    )
                ) {
                    $endAt->addWeeks(
                        $intervalCount
                    );
                } elseif (
                    in_array(
                        $interval,
                        [
                            'year',
                            'years',
                        ],
                        true
                    )
                ) {
                    $endAt->addYears(
                        $intervalCount
                    );
                } else {
                    $endAt->addMonths(
                        $intervalCount
                    );
                }

                $payment =
                    Payment::on('central')
                        ->create([
                            'restaurant_id' => $restaurantModel->id,
                            'subscription_id' => $subscription->id,
                            'gateway' => 'manual',
                            'transaction_id' => $validated['transaction_id'] ?? null,
                            'reference' => $validated['reference'] ?? null,
                            'amount' => $newPlan->price,
                            'currency' => $newPlan->currency,
                            'payment_method' => $paymentMethod->code,
                            'status' => 'paid',
                            'paid_at' => now(),
                            'gateway_response' => [
                                'type' => 'manual',
                                'payment_type' => 'plan_change',
                                'from_plan_id' => $currentPlan->id,
                                'from_plan_name' => $currentPlan->name,
                                'to_plan_id' => $newPlan->id,
                                'to_plan_name' => $newPlan->name,
                                'payment_method_name' => $paymentMethod->name,
                                'notes' => $validated['notes'] ?? null,
                                'recorded_by' => $request->user()?->id,
                            ],
                        ]);

                $subscription->forceFill([
                    'subscription_plan_id' => $newPlan->id,
                    'status' => 'active',
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'cancelled_at' => null,
                    'cancel_reason' => null,

                    /*
                     * Preserve historical trial timestamps.
                     */
                    'trial_start_at' => $subscription->trial_start_at,
                    'trial_end_at' => $subscription->trial_end_at,
                ])->save();

                $restaurantModel->forceFill([
                    'status' => 'active',
                ])->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription plan updated successfully.',
                    'data' => [
                        'payment' => $payment->fresh(),
                        'subscription' => $subscription
                            ->fresh()
                            ->load('plan'),
                        'restaurant' => [
                            'id' => $restaurantModel->id,
                            'status' => $restaurantModel
                                ->fresh()
                                ->status,
                        ],
                        'previous_plan' => [
                            'id' => $currentPlan->id,
                            'name' => $currentPlan->name,
                        ],
                        'new_plan' => [
                            'id' => $newPlan->id,
                            'name' => $newPlan->name,
                            'price' => $newPlan->price,
                            'currency' => $newPlan->currency,
                            'billing_interval' => $newPlan->billing_interval,
                            'billing_interval_count' => $newPlan->billing_interval_count,
                        ],
                    ],
                    'errors' => null,
                ]);
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Complete Initial Manual Payment
    |--------------------------------------------------------------------------
    |
    | This endpoint is used only when a newly created restaurant is pending
    | and needs its first paid subscription completed.
    |
    */

    public function complete(
        Request $request,
        int $restaurant,
        TenantProvisioningService $provisioningService
    ): JsonResponse {
        $validated = $request->validate([
            'subscription_id' => [
                'required',
                'integer',
            ],

            'payment_method' => [
                'required',
                'string',
                'max:50',
            ],

            'reference' => [
                'nullable',
                'string',
                'max:150',
            ],

            'transaction_id' => [
                'nullable',
                'string',
                'max:150',
            ],

            'username' => [
                'nullable',
                'string',
                'max:100',
            ],

            'password' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        abort_if(strtolower($validated['payment_method']) === 'stripe', 422, 'Stripe payments must use Stripe Checkout.');

        /*
        |--------------------------------------------------------------------------
        | Payment Method Specific Validation
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                strtolower($validated['payment_method']),
                [
                    'bkash',
                    'nagad',
                ],
                true
            )
            &&
            blank($validated['transaction_id'] ?? null)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction ID is required for bKash or Nagad payments.',
                'data' => null,
                'errors' => [
                    'transaction_id' => [
                        'Transaction ID is required for this payment method.',
                    ],
                ],
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve Payment Method
        |--------------------------------------------------------------------------
        */

        $paymentMethod = PaymentMethod::on('central')
            ->where(
                'code',
                $validated['payment_method']
            )
            ->where(
                'status',
                true
            )
            ->first();

        if (! $paymentMethod) {
            return response()->json([
                'success' => false,
                'message' => 'Selected payment method is not available.',
                'data' => null,
                'errors' => [
                    'payment_method' => [
                        'The selected payment method is invalid or inactive.',
                    ],
                ],
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve Restaurant
        |--------------------------------------------------------------------------
        */

        $restaurantModel = Restaurant::on('central')
            ->find($restaurant);

        if (! $restaurantModel) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant was not found.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Restaurant Must Await Initial Payment
        |--------------------------------------------------------------------------
        */

        if ($restaurantModel->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This restaurant is not awaiting payment.',
                'data' => [
                    'status' => $restaurantModel->status,
                ],
                'errors' => null,
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Pending Subscription
        |--------------------------------------------------------------------------
        */

        $subscription = Subscription::on('central')
            ->with('plan')
            ->where(
                'id',
                $validated['subscription_id']
            )
            ->where(
                'restaurant_id',
                $restaurantModel->id
            )
            ->where(
                'status',
                'pending'
            )
            ->first();

        if (! $subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Pending subscription was not found for this restaurant.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Subscription Plan
        |--------------------------------------------------------------------------
        */

        $plan = $subscription->plan;

        if (! $plan) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription plan was not found.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Trial Protection
        |--------------------------------------------------------------------------
        */

        if ((float) $plan->price <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Trial subscriptions do not require payment.',
                'data' => null,
                'errors' => null,
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Duplicate Initial Payment Protection
        |--------------------------------------------------------------------------
        */

        $alreadyPaid = Payment::on('central')
            ->where(
                'subscription_id',
                $subscription->id
            )
            ->where(
                'status',
                'paid'
            )
            ->exists();

        if ($alreadyPaid) {
            return response()->json([
                'success' => false,
                'message' => 'This subscription has already been paid.',
                'data' => null,
                'errors' => null,
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Tenant Admin Credentials
        |--------------------------------------------------------------------------
        */

        $username = trim(
            (string) (
                $validated['username']
                ??
                ''
            )
        );

        if ($username === '') {
            $username = strtolower(
                Str::before(
                    trim((string) $restaurantModel->owner_email),
                    '@'
                )
            );
        }

        if ($username === '') {
            $username = 'admin';
        }

        $password = trim(
            (string) (
                $validated['password']
                ??
                ''
            )
        );

        $provisioningData = [
            'username' => $username,
            'password' => $password !== ''
                ? $password
                : null,
        ];

        /*
        |--------------------------------------------------------------------------
        | Create Initial Payment Record
        |--------------------------------------------------------------------------
        */

        $payment = Payment::on('central')
            ->create([
                'restaurant_id' => $restaurantModel->id,
                'subscription_id' => $subscription->id,
                'gateway' => 'manual',
                'transaction_id' => $validated['transaction_id'] ?? null,
                'reference' => $validated['reference'] ?? null,

                /*
                 * Never trust frontend amount.
                 */
                'amount' => $plan->price,

                'currency' => $plan->currency,

                'payment_method' => $paymentMethod->code,
                'status' => 'paid',
                'paid_at' => now(),

                'gateway_response' => [
                    'type' => 'manual',
                    'payment_type' => 'initial',
                    'payment_method_name' => $paymentMethod->name,
                    'notes' => $validated['notes'] ?? null,
                    'recorded_by' => $request->user()?->id,
                ],
            ]);

        /*
        |--------------------------------------------------------------------------
        | Provision Restaurant
        |--------------------------------------------------------------------------
        */

        try {
            $result = $provisioningService
                ->provisionExistingRestaurant(
                    $restaurantModel,
                    $provisioningData
                );

            return response()->json([
                'success' => true,
                'message' => 'Payment completed and restaurant activated successfully.',
                'data' => [
                    'payment' => $payment->fresh(),
                    'restaurant' => $result['restaurant'],
                    'subscription' => $result['subscription'],
                    'tenant_database' => $result['tenant_database'],
                    'domain' => $result['domain'],
                    'admin' => $result['admin'],
                ],
                'errors' => null,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            $payment->forceFill([
                'gateway_response' => [
                    'type' => 'manual',
                    'payment_type' => 'initial',
                    'payment_method_name' => $paymentMethod->name,
                    'notes' => $validated['notes'] ?? null,
                    'recorded_by' => $request->user()?->id,
                    'provisioning_error' => $exception->getMessage(),
                ],
            ])->saveQuietly();

            return response()->json([
                'success' => false,
                'message' => 'Payment was recorded, but restaurant provisioning failed.',
                'data' => [
                    'payment_id' => $payment->id,
                    'restaurant_id' => $restaurantModel->id,
                    'subscription_id' => $subscription->id,
                ],
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
    | Retry Initial Provisioning
    |--------------------------------------------------------------------------
    */

    public function retryProvisioning(
        int $restaurant,
        TenantProvisioningService $provisioningService
    ): JsonResponse {
        $restaurantModel = Restaurant::on('central')
            ->find($restaurant);

        if (! $restaurantModel) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant was not found.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $subscription = Subscription::on('central')
            ->with('plan')
            ->where(
                'restaurant_id',
                $restaurantModel->id
            )
            ->where(
                'status',
                'pending'
            )
            ->latest('id')
            ->first();

        if (! $subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No pending subscription was found for retry.',
                'data' => null,
                'errors' => null,
            ], 422);
        }

        $payment = Payment::on('central')
            ->where(
                'subscription_id',
                $subscription->id
            )
            ->where(
                'status',
                'paid'
            )
            ->latest('id')
            ->first();

        if (! $payment) {
            return response()->json([
                'success' => false,
                'message' => 'A completed payment was not found for this subscription.',
                'data' => null,
                'errors' => null,
            ], 422);
        }

        try {
            $result = $provisioningService
                ->provisionExistingRestaurant(
                    $restaurantModel,
                    []
                );

            return response()->json([
                'success' => true,
                'message' => 'Restaurant provisioning completed successfully.',
                'data' => [
                    'payment' => $payment->fresh(),
                    'restaurant' => $result['restaurant'],
                    'subscription' => $result['subscription'],
                    'tenant_database' => $result['tenant_database'],
                    'domain' => $result['domain'],
                ],
                'errors' => null,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Restaurant provisioning failed again.',
                'data' => [
                    'payment_id' => $payment->id,
                    'restaurant_id' => $restaurantModel->id,
                    'subscription_id' => $subscription->id,
                ],
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
    | Subscription Details
    |--------------------------------------------------------------------------
    */

    public function subscription(
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

        $subscription = Subscription::on('central')
            ->with('plan')
            ->where(
                'restaurant_id',
                $restaurant
            )
            ->latest('id')
            ->first();

        if (! $subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription was not found.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Subscription details retrieved successfully.',
            'data' => $subscription,
            'errors' => null,
        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Create Renewal Subscription
    |--------------------------------------------------------------------------
    |
    | Kept for compatibility with the existing route/flow.
    | This creates a NEW pending renewal subscription.
    |
    */

    public function renew(
        Request $request,
        int $restaurant
    ): JsonResponse {
        $validated = $request->validate([
            'subscription_plan_id' => [
                'required',
                'integer',
            ],
        ]);

        $restaurantModel = Restaurant::on('central')
            ->find($restaurant);

        if (! $restaurantModel) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant was not found.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $currentSubscription = Subscription::on('central')
            ->with('plan')
            ->where(
                'restaurant_id',
                $restaurantModel->id
            )
            ->latest('id')
            ->first();

        if (
            $currentSubscription &&
            $currentSubscription->status === 'active' &&
            (
                ! $currentSubscription->end_at ||
                now()->lt($currentSubscription->end_at)
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'This restaurant already has an active subscription.',
                'data' => [
                    'subscription' => $currentSubscription,
                ],
                'errors' => null,
            ], 422);
        }

        $plan = SubscriptionPlan::on('central')
            ->where(
                'id',
                $validated['subscription_plan_id']
            )
            ->where(
                'is_active',
                true
            )
            ->first();

        if (! $plan) {
            return response()->json([
                'success' => false,
                'message' => 'The selected subscription plan is unavailable.',
                'data' => null,
                'errors' => [
                    'subscription_plan_id' => [
                        'Selected subscription plan is not available.',
                    ],
                ],
            ], 422);
        }

        if ((float) $plan->price <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Trial plans cannot be used for subscription renewal.',
                'data' => null,
                'errors' => null,
            ], 422);
        }

        $subscription = Subscription::on('central')
            ->create([
                'restaurant_id' => $restaurantModel->id,
                'subscription_plan_id' => $plan->id,
                'status' => 'pending',
                'start_at' => null,
                'end_at' => null,
                'trial_start_at' => null,
                'trial_end_at' => null,
                'auto_renew' => false,
                'cancelled_at' => null,
                'cancel_reason' => null,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Renewal subscription created successfully. Payment is required.',
            'data' => [
                'subscription' => $subscription
                    ->fresh()
                    ->load('plan'),

                'payment_required' => true,
            ],
            'errors' => null,
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | Complete Renewal Payment
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | - Does NOT provision a tenant database.
    | - Does NOT run tenant migrations.
    | - Does NOT create another restaurant.
    | - Reuses the existing subscription.
    | - Extends the current subscription period.
    |
    */

    public function completeRenewalPayment(
        Request $request,
        int $restaurant
    ): JsonResponse {
        $validated = $request->validate([
            'subscription_id' => [
                'required',
                'integer',
            ],

            'payment_method' => [
                'required',
                'string',
                'max:50',
            ],

            'transaction_id' => [
                'nullable',
                'string',
                'max:150',
            ],

            'reference' => [
                'nullable',
                'string',
                'max:150',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        abort_if(strtolower($validated['payment_method']) === 'stripe', 422, 'Stripe payments must use Stripe Checkout.');

        /*
        |--------------------------------------------------------------------------
        | Payment Method Specific Validation
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                strtolower($validated['payment_method']),
                [
                    'bkash',
                    'nagad',
                ],
                true
            )
            &&
            blank($validated['transaction_id'] ?? null)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction ID is required for bKash or Nagad payments.',
                'data' => null,
                'errors' => [
                    'transaction_id' => [
                        'Transaction ID is required for this payment method.',
                    ],
                ],
            ], 422);
        }

        return DB::connection('central')->transaction(
            function () use (
                $request,
                $restaurant,
                $validated
            ): JsonResponse {

                /*
                |--------------------------------------------------------------------------
                | Lock Restaurant
                |--------------------------------------------------------------------------
                */

                $restaurantModel = Restaurant::on('central')
                    ->lockForUpdate()
                    ->find($restaurant);

                if (! $restaurantModel) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Restaurant was not found.',
                        'data' => null,
                        'errors' => null,
                    ], 404);
                }

                /*
                |--------------------------------------------------------------------------
                | Resolve Payment Method
                |--------------------------------------------------------------------------
                */

                $paymentMethod = PaymentMethod::on('central')
                    ->where(
                        'code',
                        $validated['payment_method']
                    )
                    ->where(
                        'status',
                        true
                    )
                    ->first();

                if (! $paymentMethod) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected payment method is not available.',
                        'data' => null,
                        'errors' => [
                            'payment_method' => [
                                'The selected payment method is invalid or inactive.',
                            ],
                        ],
                    ], 422);
                }

                /*
                |--------------------------------------------------------------------------
                | Lock Subscription
                |--------------------------------------------------------------------------
                */

                $subscription = Subscription::on('central')
                    ->with('plan')
                    ->where(
                        'id',
                        $validated['subscription_id']
                    )
                    ->where(
                        'restaurant_id',
                        $restaurantModel->id
                    )
                    ->lockForUpdate()
                    ->first();

                if (! $subscription) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Subscription was not found for this restaurant.',
                        'data' => null,
                        'errors' => null,
                    ], 404);
                }

                /*
                |--------------------------------------------------------------------------
                | Renewal Eligibility
                |--------------------------------------------------------------------------
                |
                | Renewal is allowed for:
                |     expired
                |     cancelled
                |
                | A still-active subscription cannot be renewed early
                | from this endpoint.
                |
                */

                if (
                    $subscription->status === 'active' &&
                    $subscription->end_at &&
                    now()->lt($subscription->end_at)
                ) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This subscription is still active and does not require renewal.',
                        'data' => [
                            'subscription' => $subscription,
                        ],
                        'errors' => null,
                    ], 422);
                }

                if (
                    ! in_array(
                        $subscription->status,
                        [
                            'expired',
                            'cancelled',
                            'active',
                        ],
                        true
                    )
                ) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This subscription is not eligible for renewal.',
                        'data' => [
                            'status' => $subscription->status,
                        ],
                        'errors' => null,
                    ], 422);
                }

                /*
                |--------------------------------------------------------------------------
                | Plan
                |--------------------------------------------------------------------------
                */

                $plan = $subscription->plan;

                if (! $plan) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Subscription plan was not found.',
                        'data' => null,
                        'errors' => null,
                    ], 404);
                }

                if ((float) $plan->price <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Free plans do not require renewal payment.',
                        'data' => null,
                        'errors' => null,
                    ], 422);
                }

                /*
                |--------------------------------------------------------------------------
                | Duplicate Renewal Protection
                |--------------------------------------------------------------------------
                |
                | No permanent "has any renewal payment" check is used here.
                | The subscription row is locked above and, after a successful renewal,
                | becomes active with a future end_at. A repeated immediate request is
                | therefore rejected by the active/future eligibility check. This still
                | allows the same subscription row to be renewed again in a future cycle.
                |
                */

                /*
                |--------------------------------------------------------------------------
                | Calculate Renewal Period
                |--------------------------------------------------------------------------
                */

                $startAt = now();

                if (
                    $subscription->end_at &&
                    $subscription->status === 'active'
                ) {
                    $currentEnd =
                        \Carbon\Carbon::parse(
                            $subscription->end_at
                        );

                    if ($currentEnd->isFuture()) {
                        $startAt = $currentEnd;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Billing Interval
                |--------------------------------------------------------------------------
                */

                $interval =
                    strtolower(
                        (string) (
                            $plan->billing_interval ??
                            'month'
                        )
                    );

                $count =
                    max(
                        1,
                        (int) (
                            $plan->billing_interval_count ??
                            1
                        )
                    );

                $endAt = $startAt->copy();

                if (
                    in_array(
                        $interval,
                        [
                            'day',
                            'days',
                        ],
                        true
                    )
                ) {
                    $endAt->addDays($count);
                } elseif (
                    in_array(
                        $interval,
                        [
                            'week',
                            'weeks',
                        ],
                        true
                    )
                ) {
                    $endAt->addWeeks($count);
                } elseif (
                    in_array(
                        $interval,
                        [
                            'year',
                            'years',
                        ],
                        true
                    )
                ) {
                    $endAt->addYears($count);
                } else {
                    $endAt->addMonths($count);
                }

                /*
                |--------------------------------------------------------------------------
                | Create Renewal Payment
                |--------------------------------------------------------------------------
                */

                $payment = Payment::on('central')
                    ->create([
                        'restaurant_id' => $restaurantModel->id,
                        'subscription_id' => $subscription->id,
                        'gateway' => 'manual',
                        'transaction_id' =>
                            $validated['transaction_id'] ?? null,
                        'reference' =>
                            $validated['reference'] ?? null,
                        'amount' => $plan->price,
                        'currency' => $plan->currency,
                        'payment_method' => $paymentMethod->code,
                        'status' => 'paid',
                        'paid_at' => now(),

                        'gateway_response' => [
                            'type' => 'manual',
                            'payment_type' => 'renewal',
                            'payment_method_name' => $paymentMethod->name,
                            'notes' =>
                                $validated['notes'] ?? null,
                            'recorded_by' =>
                                $request->user()?->id,
                        ],
                    ]);

                /*
                |--------------------------------------------------------------------------
                | Renew Subscription
                |--------------------------------------------------------------------------
                */

                $subscription->forceFill([
                    'status' => 'active',
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'cancelled_at' => null,
                    'cancel_reason' => null,
                ])->save();

                /*
                |--------------------------------------------------------------------------
                | Reactivate Restaurant
                |--------------------------------------------------------------------------
                */

                $restaurantModel->forceFill([
                    'status' => 'active',
                ])->save();

                /*
                |--------------------------------------------------------------------------
                | Response
                |--------------------------------------------------------------------------
                */

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription renewal payment completed successfully.',
                    'data' => [
                        'payment' =>
                            $payment->fresh(),

                        'subscription' =>
                            $subscription
                                ->fresh()
                                ->load('plan'),

                        'restaurant' => [
                            'id' =>
                                $restaurantModel->id,

                            'status' =>
                                $restaurantModel
                                    ->fresh()
                                    ->status,
                        ],
                    ],
                    'errors' => null,
                ]);
            }
        );
    }

                    
    public function listSubscriptions(
        Request $request,
        SubscriptionLifecycleService $lifecycleService
    ): JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | Synchronize Due Expirations First
        |--------------------------------------------------------------------------
        |
        | This makes the list accurate even when the scheduler has not run yet.
        |
        */

        $lifecycleService->expireDueSubscriptions();


        /*
        |--------------------------------------------------------------------------
        | Resolve List Type
        |--------------------------------------------------------------------------
        */

        $type =
            strtolower(
                trim(
                    (string) (
                        $request->query(
                            'type',
                            'active'
                        )
                    )
                )
            );


        $allowedTypes = [
            'active',
            'trial',
            'expired',
            'cancelled',
        ];


        if (
            ! in_array(
                $type,
                $allowedTypes,
                true
            )
        ) {

            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Invalid subscription list type.',

                'data' =>
                    null,

                'errors' => [
                    'type' => [
                        'Allowed values are active, trial, expired, and cancelled.',
                    ],
                ],

            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Current Subscription Per Restaurant
        |--------------------------------------------------------------------------
        */

        $query =
            Subscription::on('central')
                ->with([
                    'restaurant:id,name,owner_name,owner_email,status,tenant_key',
                    'plan:id,name,slug,price,currency,billing_interval,billing_interval_count,trial_days',
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
                );


        /*
        |--------------------------------------------------------------------------
        | Type Filters
        |--------------------------------------------------------------------------
        */

        if (
            $type === 'active'
        ) {

            $query
                ->where(
                    'status',
                    'active'
                )
                ->whereHas(
                    'plan',
                    function ($planQuery): void {

                        $planQuery->where(
                            'price',
                            '>',
                            0
                        );
                    }
                );

        } elseif (
            $type === 'trial'
        ) {

            $query
                ->where(
                    'status',
                    'active'
                )
                ->whereNotNull(
                    'trial_end_at'
                )
                ->where(
                    'trial_end_at',
                    '>',
                    now()
                )
                ->whereHas(
                    'plan',
                    function ($planQuery): void {

                        $planQuery->where(
                            'price',
                            '<=',
                            0
                        );
                    }
                );

        } elseif (
            $type === 'expired'
        ) {

            $query->where(
                'status',
                'expired'
            );

        } elseif (
            $type === 'cancelled'
        ) {

            $query->where(
                'status',
                'cancelled'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Fetch
        |--------------------------------------------------------------------------
        */

        $subscriptions =
            $query
                ->orderByDesc(
                    'id'
                )
                ->get();


        /*
        |--------------------------------------------------------------------------
        | Response Shape
        |--------------------------------------------------------------------------
        */

        $data =
            $subscriptions
                ->map(
                    function (
                        Subscription $subscription
                    ): array {

                        $plan =
                            $subscription->plan;

                        $restaurant =
                            $subscription->restaurant;


                        return [
                            'id' =>
                                $subscription->id,

                            'restaurant_id' =>
                                $subscription->restaurant_id,

                            'subscription_plan_id' =>
                                $subscription->subscription_plan_id,

                            'status' =>
                                $subscription->status,

                            'start_at' =>
                                $subscription->start_at,

                            'end_at' =>
                                $subscription->end_at,

                            'trial_start_at' =>
                                $subscription->trial_start_at,

                            'trial_end_at' =>
                                $subscription->trial_end_at,

                            'auto_renew' =>
                                (bool)
                                    $subscription->auto_renew,

                            'cancelled_at' =>
                                $subscription->cancelled_at,

                            'cancel_reason' =>
                                $subscription->cancel_reason,

                            'created_at' =>
                                $subscription->created_at,

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

                                        'slug' =>
                                            $plan->slug,

                                        'price' =>
                                            $plan->price,

                                        'currency' =>
                                            $plan->currency,

                                        'billing_interval' =>
                                            $plan->billing_interval,

                                        'billing_interval_count' =>
                                            $plan->billing_interval_count,

                                        'trial_days' =>
                                            $plan->trial_days,
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
                'Subscriptions retrieved successfully.',

            'data' =>
                $data,

            'meta' => [
                'type' =>
                    $type,

                'count' =>
                    $data->count(),
            ],

            'errors' =>
                null,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Payment History
    |--------------------------------------------------------------------------
    */

    public function history(
        int $restaurant
    ): JsonResponse {
        $payments = Payment::on('central')
            ->where(
                'restaurant_id',
                $restaurant
            )
            ->latest('id')
            ->get([
                'id',
                'subscription_id',
                'gateway',
                'transaction_id',
                'reference',
                'amount',
                'currency',
                'payment_method',
                'status',
                'paid_at',
                'created_at',
                'gateway_response',
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment history retrieved successfully.',
            'data' => $payments,
            'errors' => null,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Update Subscription Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        Request $request,
        int $restaurant
    ): JsonResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                'in:active,cancelled,expired',
            ],

            'cancel_reason' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $restaurantModel = Restaurant::on('central')
            ->find($restaurant);

        if (! $restaurantModel) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant was not found.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $subscription = Subscription::on('central')
            ->with('plan')
            ->where(
                'restaurant_id',
                $restaurantModel->id
            )
            ->latest('id')
            ->first();

        if (! $subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription was not found.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Pending Subscription Protection
        |--------------------------------------------------------------------------
        */

        if (
            $subscription->status === 'pending' &&
            $validated['status'] !== 'active'
        ) {
            return response()->json([
                'success' => false,
                'message' => 'A pending subscription must be paid before it can be cancelled or expired.',
                'data' => [
                    'status' => $subscription->status,
                ],
                'errors' => null,
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Activate
        |--------------------------------------------------------------------------
        */

        if ($validated['status'] === 'active') {
            $subscription->forceFill([
                'status' => 'active',
                'cancelled_at' => null,
                'cancel_reason' => null,
            ])->save();

            $restaurantModel->forceFill([
                'status' => 'active',
            ])->save();
        }

        /*
        |--------------------------------------------------------------------------
        | Cancel / Expire
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $validated['status'],
                [
                    'cancelled',
                    'expired',
                ],
                true
            )
        ) {
            $subscription->forceFill([
                'status' => $validated['status'],

                'cancelled_at' =>
                    $validated['status'] === 'cancelled'
                        ? now()
                        : null,

                'cancel_reason' =>
                    $validated['cancel_reason'] ?? null,
            ])->save();

            $restaurantModel->forceFill([
                'status' => 'inactive',
            ])->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Subscription status updated successfully.',
            'data' => [
                'subscription' =>
                    $subscription
                        ->fresh()
                        ->load('plan'),

                'restaurant' => [
                    'id' =>
                        $restaurantModel->id,

                    'status' =>
                        $restaurantModel
                            ->fresh()
                            ->status,
                ],
            ],
            'errors' => null,
        ]);
    }
}
