<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Subscription;
use App\Models\Central\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionPlanManagementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | List Plans
    |--------------------------------------------------------------------------
    */

    public function index(): JsonResponse
    {
        $plans = SubscriptionPlan::on('central')
            ->orderBy('price')
            ->orderBy('id')
            ->get();

        $data = $plans
            ->map(
                function (SubscriptionPlan $plan): array {
                    return $this->serializePlan($plan);
                }
            )
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Subscription plans retrieved successfully.',
            'data' => $data,
            'errors' => null,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Create Plan
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): JsonResponse {
        $validated = $this->validatePlan($request);

        $slug = $this->resolveSlug(
            $validated['slug'] ?? null,
            $validated['name']
        );

        if ($this->slugExists($slug)) {
            return response()->json([
                'success' => false,
                'message' => 'A subscription plan with this slug already exists.',
                'data' => null,
                'errors' => [
                    'slug' => [
                        'The subscription plan slug has already been taken.',
                    ],
                ],
            ], 422);
        }

        $plan = SubscriptionPlan::on('central')
            ->create([
                'name' => trim($validated['name']),
                'slug' => $slug,
                'price' => $validated['price'],
                'currency' => strtoupper(
                    trim($validated['currency'])
                ),
                'billing_interval' => strtolower(
                    trim($validated['billing_interval'])
                ),
                'billing_interval_count' =>
                    (int) $validated['billing_interval_count'],
                'trial_days' =>
                    (int) ($validated['trial_days'] ?? 0),
                'is_active' =>
                    (bool) $validated['is_active'],
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan created successfully.',
            'data' => $this->serializePlan(
                $plan->fresh()
            ),
            'errors' => null,
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | Update Plan
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        int $plan
    ): JsonResponse {
        $planModel = SubscriptionPlan::on('central')
            ->find($plan);

        if (! $planModel) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription plan was not found.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $validated = $this->validatePlan($request);

        $slug = $this->resolveSlug(
            $validated['slug'] ?? null,
            $validated['name']
        );

        if (
            $this->slugExists(
                $slug,
                $planModel->id
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'A subscription plan with this slug already exists.',
                'data' => null,
                'errors' => [
                    'slug' => [
                        'The subscription plan slug has already been taken.',
                    ],
                ],
            ], 422);
        }

        $planModel->forceFill([
            'name' => trim($validated['name']),
            'slug' => $slug,
            'price' => $validated['price'],
            'currency' => strtoupper(
                trim($validated['currency'])
            ),
            'billing_interval' => strtolower(
                trim($validated['billing_interval'])
            ),
            'billing_interval_count' =>
                (int) $validated['billing_interval_count'],
            'trial_days' =>
                (int) ($validated['trial_days'] ?? 0),
            'is_active' =>
                (bool) $validated['is_active'],
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan updated successfully.',
            'data' => $this->serializePlan(
                $planModel->fresh()
            ),
            'errors' => null,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Enable / Disable Plan
    |--------------------------------------------------------------------------
    |
    | Plans are never hard-deleted from this management API because existing
    | subscriptions may reference them.
    |
    */

    public function updateStatus(
        Request $request,
        int $plan
    ): JsonResponse {
        $validated = $request->validate([
            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        $planModel = SubscriptionPlan::on('central')
            ->find($plan);

        if (! $planModel) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription plan was not found.',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        $planModel->forceFill([
            'is_active' =>
                (bool) $validated['is_active'],
        ])->save();

        return response()->json([
            'success' => true,
            'message' =>
                $planModel->is_active
                    ? 'Subscription plan enabled successfully.'
                    : 'Subscription plan disabled successfully.',
            'data' => $this->serializePlan(
                $planModel->fresh()
            ),
            'errors' => null,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function validatePlan(
        Request $request
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:150',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'currency' => [
                'required',
                'string',
                'max:10',
            ],

            'billing_interval' => [
                'required',
                'string',
                'in:day,week,month,year',
            ],

            'billing_interval_count' => [
                'required',
                'integer',
                'min:1',
                'max:120',
            ],

            'trial_days' => [
                'nullable',
                'integer',
                'min:0',
                'max:3650',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function resolveSlug(
        mixed $slug,
        string $name
    ): string {
        $resolved = Str::slug(
            trim(
                (string) (
                    $slug ?: $name
                )
            )
        );

        return $resolved !== ''
            ? $resolved
            : 'plan-' . Str::lower(
                Str::random(8)
            );
    }


    private function slugExists(
        string $slug,
        ?int $ignoreId = null
    ): bool {
        $query = SubscriptionPlan::on('central')
            ->where(
                'slug',
                $slug
            );

        if ($ignoreId) {
            $query->where(
                'id',
                '!=',
                $ignoreId
            );
        }

        return $query->exists();
    }


    private function serializePlan(
        SubscriptionPlan $plan
    ): array {
        $totalSubscriptions =
            Subscription::on('central')
                ->where(
                    'subscription_plan_id',
                    $plan->id
                )
                ->count();

        $activeSubscriptions =
            Subscription::on('central')
                ->where(
                    'subscription_plan_id',
                    $plan->id
                )
                ->where(
                    'status',
                    'active'
                )
                ->count();

        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'slug' => $plan->slug,
            'price' => $plan->price,
            'currency' => $plan->currency,
            'billing_interval' => $plan->billing_interval,
            'billing_interval_count' =>
                $plan->billing_interval_count,
            'trial_days' => $plan->trial_days,
            'is_active' => (bool) $plan->is_active,
            'created_at' => $plan->created_at,
            'updated_at' => $plan->updated_at,

            'usage' => [
                'total_subscriptions' =>
                    $totalSubscriptions,
                'active_subscriptions' =>
                    $activeSubscriptions,
            ],
        ];
    }
}
