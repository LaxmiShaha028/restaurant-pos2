<?php

namespace App\Services;

use App\Models\AccountingPaymentMapping;
use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChartOfAccountService
{
    /*
    |--------------------------------------------------------------------------
    | List
    |--------------------------------------------------------------------------
    */

    public function getAccounts(
        array $filters = []
    ): LengthAwarePaginator {

        $query =
            ChartOfAccount::query()
                ->with([
                    'parent',
                    'creator',
                    'updater',
                ])
                ->search(
                    $filters[
                        'search'
                    ]
                    ??
                    null
                );


        if (
            ! empty(
                $filters[
                    'account_type'
                ]
                ??
                null
            )
        ) {
            $query->where(
                'account_type',
                $filters[
                    'account_type'
                ]
            );
        }


        if (
            array_key_exists(
                'is_active',
                $filters
            )
            &&
            $filters[
                'is_active'
            ] !== null
            &&
            $filters[
                'is_active'
            ] !== ''
        ) {
            $query->where(
                'is_active',
                (bool) $filters[
                    'is_active'
                ]
            );
        }


        return $query
            ->orderBy(
                'code'
            )
            ->paginate(
                max(
                    1,
                    min(
                        100,
                        (int) (
                            $filters[
                                'per_page'
                            ]
                            ??
                            50
                        )
                    )
                )
            )
            ->withQueryString();
    }


    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    public function summary(): array
    {
        $accounts =
            ChartOfAccount::query();


        return [

            'total_accounts' =>
                (clone $accounts)
                    ->count(),

            'active_accounts' =>
                (clone $accounts)
                    ->where(
                        'is_active',
                        true
                    )
                    ->count(),

            'asset_accounts' =>
                (clone $accounts)
                    ->where(
                        'account_type',
                        ChartOfAccount::TYPE_ASSET
                    )
                    ->count(),

            'revenue_accounts' =>
                (clone $accounts)
                    ->where(
                        'account_type',
                        ChartOfAccount::TYPE_REVENUE
                    )
                    ->count(),

            'expense_accounts' =>
                (clone $accounts)
                    ->whereIn(
                        'account_type',
                        [
                            ChartOfAccount::TYPE_EXPENSE,
                            ChartOfAccount::TYPE_COGS,
                        ]
                    )
                    ->count(),

            'unmapped_income_categories' =>
                IncomeCategory::query()
                    ->where(
                        'is_active',
                        true
                    )
                    ->whereNull(
                        'ledger_account_id'
                    )
                    ->count(),

            'unmapped_expense_categories' =>
                ExpenseCategory::query()
                    ->where(
                        'is_active',
                        true
                    )
                    ->whereNull(
                        'ledger_account_id'
                    )
                    ->count(),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function createAccount(
        array $data,
        User $user
    ): ChartOfAccount {

        return DB::transaction(
            function () use (
                $data,
                $user
            ): ChartOfAccount {

                $type =
                    $data[
                        'account_type'
                    ];


                $parent =
                    $this->validatedParent(
                        $data[
                            'parent_id'
                        ]
                        ??
                        null,
                        $type
                    );


                $account =
                    ChartOfAccount::create([

                        'code' =>
                            $data[
                                'code'
                            ],

                        'system_key' =>
                            null,

                        'name' =>
                            $data[
                                'name'
                            ],

                        'account_type' =>
                            $type,

                        'normal_balance' =>
                            ChartOfAccount::
                                normalBalanceForType(
                                    $type
                                ),

                        'parent_id' =>
                            $parent?->id,

                        'is_system' =>
                            false,

                        'is_active' =>
                            (bool) (
                                $data[
                                    'is_active'
                                ]
                                ??
                                true
                            ),

                        'allow_manual_posting' =>
                            (bool) (
                                $data[
                                    'allow_manual_posting'
                                ]
                                ??
                                true
                            ),

                        'description' =>
                            $data[
                                'description'
                            ]
                            ??
                            null,

                        'created_by' =>
                            $user->id,

                        'updated_by' =>
                            $user->id,
                    ]);


                return $account->fresh([
                    'parent',
                    'creator',
                    'updater',
                ]);
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function updateAccount(
        ChartOfAccount $account,
        array $data,
        User $user
    ): ChartOfAccount {

        if (
            $account->is_system
        ) {
            throw ValidationException::withMessages([
                'account' => [
                    'System accounts are locked because future journal rules depend on their accounting identity.',
                ],
            ]);
        }


        return DB::transaction(
            function () use (
                $account,
                $data,
                $user
            ): ChartOfAccount {

                $type =
                    $data[
                        'account_type'
                    ]
                    ??
                    $account->account_type;


                $parentId =
                    array_key_exists(
                        'parent_id',
                        $data
                    )
                        ? $data[
                            'parent_id'
                        ]
                        : $account->parent_id;


                if (
                    $parentId !== null
                    &&
                    (int) $parentId ===
                    (int) $account->id
                ) {
                    throw ValidationException::withMessages([
                        'parent_id' => [
                            'An account cannot be its own parent.',
                        ],
                    ]);
                }


                $parent =
                    $this->validatedParent(
                        $parentId,
                        $type,
                        $account->id
                    );


                if (
                    $account->children()
                        ->exists()
                    &&
                    $type !==
                    $account->account_type
                ) {
                    throw ValidationException::withMessages([
                        'account_type' => [
                            'An account with child accounts cannot change its account type.',
                        ],
                    ]);
                }


                $update = [

                    'account_type' =>
                        $type,

                    'normal_balance' =>
                        ChartOfAccount::
                            normalBalanceForType(
                                $type
                            ),

                    'parent_id' =>
                        $parent?->id,

                    'updated_by' =>
                        $user->id,
                ];


                foreach (
                    [
                        'code',
                        'name',
                        'is_active',
                        'allow_manual_posting',
                        'description',
                    ]
                    as $field
                ) {
                    if (
                        array_key_exists(
                            $field,
                            $data
                        )
                    ) {
                        $update[
                            $field
                        ] =
                            $data[
                                $field
                            ];
                    }
                }


                $account->update(
                    $update
                );


                return $account->fresh([
                    'parent',
                    'creator',
                    'updater',
                ]);
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function deleteAccount(
        ChartOfAccount $account,
        User $user
    ): void {

        if (
            $account->is_system
        ) {
            throw ValidationException::withMessages([
                'account' => [
                    'System accounts cannot be deleted.',
                ],
            ]);
        }


        if (
            $account->children()
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'account' => [
                    'Move or delete child accounts before deleting this account.',
                ],
            ]);
        }


        $usedByIncome =
            IncomeCategory::query()
                ->where(
                    'ledger_account_id',
                    $account->id
                )
                ->exists();


        $usedByExpense =
            ExpenseCategory::query()
                ->where(
                    'ledger_account_id',
                    $account->id
                )
                ->exists();


        $usedByPayment =
            AccountingPaymentMapping::query()
                ->where(
                    'ledger_account_id',
                    $account->id
                )
                ->exists();


        if (
            $usedByIncome
            ||
            $usedByExpense
            ||
            $usedByPayment
        ) {
            throw ValidationException::withMessages([
                'account' => [
                    'This account is currently mapped to an income category, expense category or payment method.',
                ],
            ]);
        }


        DB::transaction(
            function () use (
                $account,
                $user
            ): void {

                $account->updated_by =
                    $user->id;

                $account->save();

                $account->delete();
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Category Mappings
    |--------------------------------------------------------------------------
    */

    public function updateIncomeCategoryMapping(
        IncomeCategory $category,
        ?int $ledgerAccountId,
        User $user
    ): void {

        $account =
            $this->validateCategoryAccount(
                $ledgerAccountId,
                [
                    ChartOfAccount::TYPE_REVENUE,
                ]
            );


        IncomeCategory::query()
            ->whereKey(
                $category->id
            )
            ->update([

                'ledger_account_id' =>
                    $account?->id,

                'updated_by' =>
                    $user->id,

                'updated_at' =>
                    now(),
            ]);
    }


    public function updateExpenseCategoryMapping(
        ExpenseCategory $category,
        ?int $ledgerAccountId,
        User $user
    ): void {

        $account =
            $this->validateCategoryAccount(
                $ledgerAccountId,
                [
                    ChartOfAccount::TYPE_EXPENSE,
                    ChartOfAccount::TYPE_COGS,
                ]
            );


        ExpenseCategory::query()
            ->whereKey(
                $category->id
            )
            ->update([

                'ledger_account_id' =>
                    $account?->id,

                'updated_at' =>
                    now(),
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Payment Mapping
    |--------------------------------------------------------------------------
    */

    public function updatePaymentMapping(
        string $paymentMethod,
        int $ledgerAccountId,
        User $user
    ): AccountingPaymentMapping {

        $allowedMethods =
            Expense::allowedPaymentMethods();


        if (
            ! in_array(
                $paymentMethod,
                $allowedMethods,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'payment_method' => [
                    'Invalid payment method.',
                ],
            ]);
        }


        $account =
            ChartOfAccount::query()
                ->whereKey(
                    $ledgerAccountId
                )
                ->where(
                    'account_type',
                    ChartOfAccount::TYPE_ASSET
                )
                ->where(
                    'is_active',
                    true
                )
                ->where(
                    'allow_manual_posting',
                    true
                )
                ->first();


        if (! $account) {
            throw ValidationException::withMessages([
                'ledger_account_id' => [
                    'Payment methods must map to an active posting Asset account.',
                ],
            ]);
        }


        $mapping =
            AccountingPaymentMapping::query()
                ->updateOrCreate(
                    [
                        'payment_method' =>
                            $paymentMethod,
                    ],
                    [
                        'ledger_account_id' =>
                            $account->id,

                        'updated_by' =>
                            $user->id,
                    ]
                );


        return $mapping->fresh([
            'ledgerAccount',
            'updater',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Mapping Data
    |--------------------------------------------------------------------------
    */

    public function mappings(): array
    {
        $incomeCategories =
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
                    'ledger_account_id',
                ])
                ->map(
                    function (
                        IncomeCategory $category
                    ): array {

                        return [
                            'id' =>
                                (int) $category->id,

                            'name' =>
                                $category->name,

                            'ledger_account_id' =>
                                $category
                                    ->ledger_account_id !==
                                    null
                                    ? (int) $category
                                        ->ledger_account_id
                                    : null,
                        ];
                    }
                )
                ->values();


        $expenseCategories =
            ExpenseCategory::query()
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
                    'ledger_account_id',
                ])
                ->map(
                    function (
                        ExpenseCategory $category
                    ): array {

                        return [
                            'id' =>
                                (int) $category->id,

                            'name' =>
                                $category->name,

                            'ledger_account_id' =>
                                $category
                                    ->ledger_account_id !==
                                    null
                                    ? (int) $category
                                        ->ledger_account_id
                                    : null,
                        ];
                    }
                )
                ->values();


        $paymentMappings =
            AccountingPaymentMapping::query()
                ->with(
                    'ledgerAccount'
                )
                ->orderBy(
                    'payment_method'
                )
                ->get()
                ->map(
                    function (
                        AccountingPaymentMapping $mapping
                    ): array {

                        return [
                            'payment_method' =>
                                $mapping
                                    ->payment_method,

                            'payment_method_label' =>
                                $this
                                    ->paymentMethodLabel(
                                        $mapping
                                            ->payment_method
                                    ),

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
                        ];
                    }
                )
                ->values();


        return [

            'income_categories' =>
                $incomeCategories,

            'expense_categories' =>
                $expenseCategories,

            'payment_mappings' =>
                $paymentMappings,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Options
    |--------------------------------------------------------------------------
    */

    public function options(): array
    {
        $accounts =
            ChartOfAccount::query()
                ->where(
                    'is_active',
                    true
                )
                ->orderBy(
                    'code'
                )
                ->get([
                    'id',
                    'code',
                    'name',
                    'account_type',
                    'normal_balance',
                    'parent_id',
                    'allow_manual_posting',
                ]);


        return [

            'account_types' =>
                ChartOfAccount::typeOptions(),

            'parent_accounts' =>
                $accounts
                    ->map(
                        fn (
                            ChartOfAccount $account
                        ): array => [

                            'id' =>
                                (int) $account->id,

                            'code' =>
                                $account->code,

                            'name' =>
                                $account->name,

                            'account_type' =>
                                $account
                                    ->account_type,
                        ]
                    )
                    ->values(),

            'revenue_accounts' =>
                $accounts
                    ->where(
                        'account_type',
                        ChartOfAccount::TYPE_REVENUE
                    )
                    ->where(
                        'allow_manual_posting',
                        true
                    )
                    ->values(),

            'expense_accounts' =>
                $accounts
                    ->whereIn(
                        'account_type',
                        [
                            ChartOfAccount::TYPE_EXPENSE,
                            ChartOfAccount::TYPE_COGS,
                        ]
                    )
                    ->where(
                        'allow_manual_posting',
                        true
                    )
                    ->values(),

            'asset_accounts' =>
                $accounts
                    ->where(
                        'account_type',
                        ChartOfAccount::TYPE_ASSET
                    )
                    ->where(
                        'allow_manual_posting',
                        true
                    )
                    ->values(),

            'payment_methods' =>
                Expense::paymentMethodOptions(),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function validatedParent(
        mixed $parentId,
        string $type,
        ?int $ignoreAccountId = null
    ): ?ChartOfAccount {

        if (
            $parentId === null
            ||
            $parentId === ''
        ) {
            return null;
        }


        $parent =
            ChartOfAccount::query()
                ->whereKey(
                    (int) $parentId
                )
                ->where(
                    'is_active',
                    true
                )
                ->first();


        if (! $parent) {
            throw ValidationException::withMessages([
                'parent_id' => [
                    'Selected parent account was not found or is inactive.',
                ],
            ]);
        }


        if (
            $ignoreAccountId !== null
            &&
            (int) $parent->id ===
            $ignoreAccountId
        ) {
            throw ValidationException::withMessages([
                'parent_id' => [
                    'An account cannot be its own parent.',
                ],
            ]);
        }


        if (
            $parent->account_type !==
            $type
        ) {
            throw ValidationException::withMessages([
                'parent_id' => [
                    'Parent and child account types must match.',
                ],
            ]);
        }


        if (
            $ignoreAccountId !== null
            &&
            $this->isDescendant(
                $parent,
                $ignoreAccountId
            )
        ) {
            throw ValidationException::withMessages([
                'parent_id' => [
                    'A child account cannot become the parent of its own ancestor.',
                ],
            ]);
        }


        return $parent;
    }


    private function isDescendant(
        ChartOfAccount $candidateParent,
        int $accountId
    ): bool {

        $current =
            $candidateParent;


        while (
            $current->parent_id !==
            null
        ) {

            if (
                (int) $current
                    ->parent_id ===
                $accountId
            ) {
                return true;
            }


            $current =
                ChartOfAccount::query()
                    ->find(
                        $current
                            ->parent_id
                    );


            if (! $current) {
                break;
            }
        }


        return false;
    }


    private function validateCategoryAccount(
        ?int $ledgerAccountId,
        array $allowedTypes
    ): ?ChartOfAccount {

        if ($ledgerAccountId === null) {
            return null;
        }


        $account =
            ChartOfAccount::query()
                ->whereKey(
                    $ledgerAccountId
                )
                ->whereIn(
                    'account_type',
                    $allowedTypes
                )
                ->where(
                    'is_active',
                    true
                )
                ->where(
                    'allow_manual_posting',
                    true
                )
                ->first();


        if (! $account) {
            throw ValidationException::withMessages([
                'ledger_account_id' => [
                    'Select an active posting account with the correct accounting type.',
                ],
            ]);
        }


        return $account;
    }


    private function paymentMethodLabel(
        string $paymentMethod
    ): string {

        return match (
            $paymentMethod
        ) {

            'cash' =>
                'Cash',

            'card' =>
                'Card',

            'bkash' =>
                'bKash',

            'nagad' =>
                'Nagad',

            'bank_transfer' =>
                'Bank Transfer',

            'mixed' =>
                'Mixed Payment',

            'other' =>
                'Other',

            default =>
                ucwords(
                    str_replace(
                        '_',
                        ' ',
                        $paymentMethod
                    )
                ),
        };
    }
}
