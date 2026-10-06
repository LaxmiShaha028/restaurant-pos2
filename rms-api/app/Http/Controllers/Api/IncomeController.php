<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreIncomeCategoryRequest;
use App\Http\Requests\Api\StoreIncomeRequest;
use App\Http\Requests\Api\UpdateIncomeRequest;
use App\Http\Resources\IncomeResource;
use App\Models\Expense;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Services\IncomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IncomeController extends Controller
{
    public function __construct(
        private readonly IncomeService $incomeService
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | List
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {

        $this->ensureAdminOrManager(
            $request
        );


        $validated =
            $this->validateFilters(
                $request
            );


        $incomes =
            $this->incomeService
                ->getIncomes(
                    $validated
                );


        return IncomeResource::collection(
            $incomes
        )->additional([

            'success' =>
                true,

            'message' =>
                'Other income records loaded successfully.',

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    public function summary(
        Request $request
    ): JsonResponse {

        $this->ensureAdminOrManager(
            $request
        );


        $validated =
            $this->validateFilters(
                $request,
                includePagination: false
            );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Other income summary loaded successfully.',

            'data' =>
                $this->incomeService
                    ->getSummary(
                        $validated
                    ),

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Options
    |--------------------------------------------------------------------------
    */

    public function options(
        Request $request
    ): JsonResponse {

        $this->ensureAdminOrManager(
            $request
        );


        $categories =
            IncomeCategory::query()
                ->where(
                    'is_active',
                    true
                )
                ->orderBy(
                    'name'
                )
                ->get([
                    'id',
                    'name',
                    'description',
                ])
                ->map(
                    fn (
                        IncomeCategory $category
                    ): array => [

                        'id' =>
                            (int) $category->id,

                        'name' =>
                            $category->name,

                        'description' =>
                            $category->description,

                    ]
                )
                ->values();


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Other income options loaded successfully.',

            'data' => [

                'categories' =>
                    $categories,

                'payment_methods' =>
                    Expense::paymentMethodOptions(),

            ],

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreIncomeRequest $request
    ) {

        $this->ensureAdminOrManager(
            $request
        );


        $income =
            $this->incomeService
                ->createIncome(
                    $request->validated(),
                    $request->user()
                );


        return (
            new IncomeResource(
                $income
            )
        )
            ->additional([

                'success' =>
                    true,

                'message' =>
                    'Other income recorded successfully.',

            ])
            ->response()
            ->setStatusCode(201);
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateIncomeRequest $request,
        Income $income
    ) {

        $this->ensureAdminOrManager(
            $request
        );


        $income =
            $this->incomeService
                ->updateIncome(
                    $income,
                    $request->validated(),
                    $request->user()
                );


        return (
            new IncomeResource(
                $income
            )
        )->additional([

            'success' =>
                true,

            'message' =>
                'Other income updated successfully.',

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        Income $income
    ): JsonResponse {

        $this->ensureAdminOrManager(
            $request
        );


        $this->incomeService
            ->deleteIncome(
                $income,
                $request->user()
            );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Other income deleted successfully.',

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Quick Add Category
    |--------------------------------------------------------------------------
    */

    public function storeCategory(
        StoreIncomeCategoryRequest $request
    ): JsonResponse {

        $this->ensureAdminOrManager(
            $request
        );


        $result =
            $this->incomeService
                ->createOrReactivateCategory(
                    $request->validated(),
                    $request->user()
                );


        $category =
            $result[
                'category'
            ];


        return response()->json([

            'success' =>
                true,

            'message' =>
                $result[
                    'created'
                ]
                    ? 'Income category created successfully.'
                    : (
                        $result[
                            'reactivated'
                        ]
                            ? 'Existing income category restored and activated.'
                            : 'Income category already existed and was updated.'
                    ),

            'data' => [

                'id' =>
                    (int) $category->id,

                'name' =>
                    $category->name,

                'description' =>
                    $category->description,

                'is_active' =>
                    (bool) $category->is_active,

            ],

        ], $result['created'] ? 201 : 200);
    }


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    private function validateFilters(
        Request $request,
        bool $includePagination = true
    ): array {

        $rules = [

            'search' => [
                'nullable',
                'string',
                'max:150',
            ],

            'income_category_id' => [
                'nullable',
                'integer',
                'exists:income_categories,id',
            ],

            'payment_method' => [
                'nullable',

                Rule::in(
                    Expense::allowedPaymentMethods()
                ),
            ],

            'from_date' => [
                'nullable',
                'date',
            ],

            'to_date' => [
                'nullable',
                'date',
                'after_or_equal:from_date',
                'before_or_equal:today',
            ],

        ];


        if ($includePagination) {

            $rules[
                'page'
            ] = [
                'nullable',
                'integer',
                'min:1',
            ];

            $rules[
                'per_page'
            ] = [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ];
        }


        return $request->validate(
            $rules
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Admin / Manager Protection
    |--------------------------------------------------------------------------
    */

    private function ensureAdminOrManager(
        Request $request
    ): void {

        $user =
            $request->user();


        $user?->loadMissing(
            'role'
        );


        $roleName =
            strtolower(
                trim(
                    (string) (
                        $user
                            ?->role
                            ?->name
                        ??
                        ''
                    )
                )
            );


        abort_unless(
            $user
            &&
            in_array(
                $roleName,
                [
                    'admin',
                    'manager',
                ],
                true
            ),
            403,
            'Only administrators and managers can manage other income.'
        );


        abort_unless(
            $user->is_active
            &&
            $user->blocked_at === null,
            403,
            'Your account is inactive or blocked.'
        );
    }
}
