<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreChartOfAccountRequest;
use App\Http\Requests\Api\UpdateCategoryLedgerAccountRequest;
use App\Http\Requests\Api\UpdateChartOfAccountRequest;
use App\Http\Requests\Api\UpdatePaymentAccountMappingRequest;
use App\Http\Resources\ChartOfAccountResource;
use App\Models\ChartOfAccount;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Services\ChartOfAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChartOfAccountController extends Controller
{
    public function __construct(
        private readonly ChartOfAccountService $service
    ) {
    }


    public function index(
        Request $request
    ) {
        $this->ensureAdminOrManager(
            $request
        );


        $validated =
            $request->validate([

                'search' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'account_type' => [
                    'nullable',
                    Rule::in(
                        ChartOfAccount::allowedTypes()
                    ),
                ],

                'is_active' => [
                    'nullable',
                    'boolean',
                ],

                'page' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],

                'per_page' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:100',
                ],
            ]);


        $accounts =
            $this->service
                ->getAccounts(
                    $validated
                );


        return ChartOfAccountResource::collection(
            $accounts
        )->additional([

            'success' =>
                true,

            'message' =>
                'Chart of accounts loaded successfully.',
        ]);
    }


    public function summary(
        Request $request
    ): JsonResponse {

        $this->ensureAdminOrManager(
            $request
        );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Chart of accounts summary loaded successfully.',

            'data' =>
                $this->service
                    ->summary(),
        ]);
    }


    public function options(
        Request $request
    ): JsonResponse {

        $this->ensureAdminOrManager(
            $request
        );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Accounting options loaded successfully.',

            'data' =>
                $this->service
                    ->options(),
        ]);
    }


    public function mappings(
        Request $request
    ): JsonResponse {

        $this->ensureAdminOrManager(
            $request
        );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Accounting mappings loaded successfully.',

            'data' =>
                $this->service
                    ->mappings(),
        ]);
    }


    public function store(
        StoreChartOfAccountRequest $request
    ) {

        $account =
            $this->service
                ->createAccount(
                    $request->validated(),
                    $request->user()
                );


        return (
            new ChartOfAccountResource(
                $account
            )
        )
            ->additional([

                'success' =>
                    true,

                'message' =>
                    'Ledger account created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }


    public function update(
        UpdateChartOfAccountRequest $request,
        ChartOfAccount $chartOfAccount
    ) {

        $account =
            $this->service
                ->updateAccount(
                    $chartOfAccount,
                    $request->validated(),
                    $request->user()
                );


        return (
            new ChartOfAccountResource(
                $account
            )
        )->additional([

            'success' =>
                true,

            'message' =>
                'Ledger account updated successfully.',
        ]);
    }


    public function destroy(
        Request $request,
        ChartOfAccount $chartOfAccount
    ): JsonResponse {

        $this->ensureAdminOrManager(
            $request
        );


        $this->service
            ->deleteAccount(
                $chartOfAccount,
                $request->user()
            );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Ledger account deleted successfully.',
        ]);
    }


    public function updateIncomeCategoryMapping(
        UpdateCategoryLedgerAccountRequest $request,
        IncomeCategory $incomeCategory
    ): JsonResponse {

        $this->service
            ->updateIncomeCategoryMapping(
                $incomeCategory,
                $request->validated()[
                    'ledger_account_id'
                ],
                $request->user()
            );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Income category ledger mapping updated successfully.',
        ]);
    }


    public function updateExpenseCategoryMapping(
        UpdateCategoryLedgerAccountRequest $request,
        ExpenseCategory $expenseCategory
    ): JsonResponse {

        $this->service
            ->updateExpenseCategoryMapping(
                $expenseCategory,
                $request->validated()[
                    'ledger_account_id'
                ],
                $request->user()
            );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Expense category ledger mapping updated successfully.',
        ]);
    }


    public function updatePaymentMapping(
        UpdatePaymentAccountMappingRequest $request,
        string $paymentMethod
    ): JsonResponse {

        $mapping =
            $this->service
                ->updatePaymentMapping(
                    $paymentMethod,
                    $request->validated()[
                        'ledger_account_id'
                    ],
                    $request->user()
                );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Payment method ledger mapping updated successfully.',

            'data' => [

                'payment_method' =>
                    $mapping
                        ->payment_method,

                'ledger_account_id' =>
                    (int) $mapping
                        ->ledger_account_id,

                'ledger_account' =>
                    $mapping
                        ->ledgerAccount
                        ? [
                            'id' =>
                                (int) $mapping
                                    ->ledgerAccount
                                    ->id,

                            'code' =>
                                $mapping
                                    ->ledgerAccount
                                    ->code,

                            'name' =>
                                $mapping
                                    ->ledgerAccount
                                    ->name,
                        ]
                        : null,
            ],
        ]);
    }


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
            $user->is_active
            &&
            $user->blocked_at === null
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
            'Only administrators and managers can manage accounting.'
        );
    }
}
