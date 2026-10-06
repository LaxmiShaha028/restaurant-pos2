<?php

namespace App\Services;

use App\Models\AccountingPaymentMapping;
use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountingSourcePostingService
{
    public const SOURCE_INCOME =
        'income';

    public const SOURCE_EXPENSE =
        'expense';


    public function __construct(
        private readonly JournalService $journalService
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Income
    |--------------------------------------------------------------------------
    */

    public function syncIncome(
        Income $income,
        ?User $actor = null,
        string $changeReason =
            'Other Income transaction changed.'
    ) {

        $income->loadMissing(
            'category'
        );


        $actor =
            $actor
            ??
            $this->resolveActor(
                $income
            );


        $amount =
            round(
                (float) $income->amount,
                2
            );


        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => [
                    'Other Income amount must be greater than zero before accounting can be posted.',
                ],
            ]);
        }


        $revenueAccount =
            $this->resolveIncomeAccount(
                $income->category
            );


        $assetAccount =
            $this->resolvePaymentAssetAccount(
                $income->payment_method
            );


        $journalDate =
            Carbon::parse(
                $income->income_date
            )->format(
                'Y-m-d'
            );


        $sourceHash =
            $this->sourceHash([

                'type' =>
                    self::SOURCE_INCOME,

                'source_id' =>
                    (int) $income->id,

                'journal_date' =>
                    $journalDate,

                'amount' =>
                    number_format(
                        $amount,
                        2,
                        '.',
                        ''
                    ),

                'payment_method' =>
                    (string) $income->payment_method,

                'debit_account_id' =>
                    (int) $assetAccount->id,

                'credit_account_id' =>
                    (int) $revenueAccount->id,
            ]);


        $description =
            'Other Income — '
            .
            (
                $income->category_name_snapshot
                ??
                $income->category?->name
                ??
                'Other Income'
            );


        if (
            $income->reference
        ) {
            $description .=
                ' — Ref: '
                .
                $income->reference;
        }


        return $this->journalService
            ->syncSource(

                sourceType:
                    self::SOURCE_INCOME,

                sourceId:
                    (int) $income->id,

                sourceHash:
                    $sourceHash,

                journalDate:
                    $journalDate,

                description:
                    $description,

                lines: [
                    [
                        'ledger_account_id' =>
                            (int) $assetAccount->id,

                        'debit' =>
                            $amount,

                        'credit' =>
                            0,

                        'memo' =>
                            'Amount received via '
                            .
                            $this->paymentMethodLabel(
                                $income->payment_method
                            ),
                    ],
                    [
                        'ledger_account_id' =>
                            (int) $revenueAccount->id,

                        'debit' =>
                            0,

                        'credit' =>
                            $amount,

                        'memo' =>
                            $income->category_name_snapshot
                            ??
                            $income->category?->name
                            ??
                            'Other Income',
                    ],
                ],

                user:
                    $actor,

                changeReason:
                    $changeReason
            );
    }


    public function reverseIncome(
        Income $income,
        ?User $actor = null,
        string $reason =
            'Other Income transaction was deleted.'
    ) {

        $actor =
            $actor
            ??
            $this->resolveActor(
                $income
            );


        $journalDate =
            Carbon::parse(
                $income->income_date
            )->format(
                'Y-m-d'
            );


        return $this->journalService
            ->reverseCurrentSource(

                sourceType:
                    self::SOURCE_INCOME,

                sourceId:
                    (int) $income->id,

                reason:
                    $reason,

                reversalDate:
                    $journalDate,

                user:
                    $actor
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Expense
    |--------------------------------------------------------------------------
    */

    public function syncExpense(
        Expense $expense,
        ?User $actor = null,
        string $changeReason =
            'Expense transaction changed.'
    ) {

        $expense->loadMissing(
            'category'
        );


        $actor =
            $actor
            ??
            $this->resolveActor(
                $expense
            );


        $amount =
            round(
                (float) $expense->amount,
                2
            );


        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => [
                    'Expense amount must be greater than zero before accounting can be posted.',
                ],
            ]);
        }


        $expenseAccount =
            $this->resolveExpenseAccount(
                $expense->category
            );


        $assetAccount =
            $this->resolvePaymentAssetAccount(
                $expense->payment_method
            );


        $journalDate =
            Carbon::parse(
                $expense->expense_date
            )->format(
                'Y-m-d'
            );


        $sourceHash =
            $this->sourceHash([

                'type' =>
                    self::SOURCE_EXPENSE,

                'source_id' =>
                    (int) $expense->id,

                'journal_date' =>
                    $journalDate,

                'amount' =>
                    number_format(
                        $amount,
                        2,
                        '.',
                        ''
                    ),

                'payment_method' =>
                    (string) $expense->payment_method,

                'debit_account_id' =>
                    (int) $expenseAccount->id,

                'credit_account_id' =>
                    (int) $assetAccount->id,
            ]);


        $categoryName =
            $expense->category?->name
            ??
            'Expense';


        $description =
            $categoryName;


        if (
            $expense->source_type
            &&
            $expense->source_id
        ) {
            $description .=
                ' — '
                .
                ucwords(
                    str_replace(
                        [
                            '_',
                            '-',
                        ],
                        ' ',
                        (string) $expense->source_type
                    )
                )
                .
                ' #'
                .
                $expense->source_id;
        }


        return $this->journalService
            ->syncSource(

                sourceType:
                    self::SOURCE_EXPENSE,

                sourceId:
                    (int) $expense->id,

                sourceHash:
                    $sourceHash,

                journalDate:
                    $journalDate,

                description:
                    $description,

                lines: [
                    [
                        'ledger_account_id' =>
                            (int) $expenseAccount->id,

                        'debit' =>
                            $amount,

                        'credit' =>
                            0,

                        'memo' =>
                            $categoryName,
                    ],
                    [
                        'ledger_account_id' =>
                            (int) $assetAccount->id,

                        'debit' =>
                            0,

                        'credit' =>
                            $amount,

                        'memo' =>
                            'Paid via '
                            .
                            $this->paymentMethodLabel(
                                $expense->payment_method
                            ),
                    ],
                ],

                user:
                    $actor,

                changeReason:
                    $changeReason
            );
    }


    public function reverseExpense(
        Expense $expense,
        ?User $actor = null,
        string $reason =
            'Expense transaction was deleted or reversed.'
    ) {

        $actor =
            $actor
            ??
            $this->resolveActor(
                $expense
            );


        $journalDate =
            Carbon::parse(
                $expense->expense_date
            )->format(
                'Y-m-d'
            );


        return $this->journalService
            ->reverseCurrentSource(

                sourceType:
                    self::SOURCE_EXPENSE,

                sourceId:
                    (int) $expense->id,

                reason:
                    $reason,

                reversalDate:
                    $journalDate,

                user:
                    $actor
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Income Category Account
    |--------------------------------------------------------------------------
    |
    | Owner-friendly fallback:
    | an unmapped new income category automatically uses
    | 4190 Miscellaneous Other Income.
    |
    */

    private function resolveIncomeAccount(
        ?IncomeCategory $category
    ): ChartOfAccount {

        if (! $category) {
            throw ValidationException::withMessages([
                'income_category_id' => [
                    'Income category was not found.',
                ],
            ]);
        }


        $account =
            $category->ledger_account_id
                ? ChartOfAccount::query()
                    ->whereKey(
                        $category->ledger_account_id
                    )
                    ->where(
                        'account_type',
                        ChartOfAccount::TYPE_REVENUE
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->where(
                        'allow_manual_posting',
                        true
                    )
                    ->first()
                : null;


        if ($account) {
            return $account;
        }


        $fallback =
            ChartOfAccount::query()
                ->where(
                    'system_key',
                    'misc_other_income'
                )
                ->where(
                    'account_type',
                    ChartOfAccount::TYPE_REVENUE
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


        if (! $fallback) {
            throw ValidationException::withMessages([
                'income_category_id' => [
                    'No valid Revenue account is available for this Other Income category.',
                ],
            ]);
        }


        DB::table(
            'income_categories'
        )
            ->where(
                'id',
                $category->id
            )
            ->update([

                'ledger_account_id' =>
                    $fallback->id,

                'updated_at' =>
                    now(),
            ]);


        $category->ledger_account_id =
            $fallback->id;


        return $fallback;
    }


    /*
    |--------------------------------------------------------------------------
    | Expense Category Account
    |--------------------------------------------------------------------------
    |
    | Owner-friendly fallback:
    | an unmapped new expense category automatically uses
    | 6900 Miscellaneous Operating Expense.
    |
    */

    private function resolveExpenseAccount(
        ?ExpenseCategory $category
    ): ChartOfAccount {

        if (! $category) {
            throw ValidationException::withMessages([
                'expense_category_id' => [
                    'Expense category was not found.',
                ],
            ]);
        }


        $account =
            $category->ledger_account_id
                ? ChartOfAccount::query()
                    ->whereKey(
                        $category->ledger_account_id
                    )
                    ->whereIn(
                        'account_type',
                        [
                            ChartOfAccount::TYPE_EXPENSE,
                            ChartOfAccount::TYPE_COGS,
                        ]
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->where(
                        'allow_manual_posting',
                        true
                    )
                    ->first()
                : null;


        if ($account) {
            return $account;
        }


        $fallback =
            ChartOfAccount::query()
                ->where(
                    'system_key',
                    'misc_operating_expense'
                )
                ->where(
                    'account_type',
                    ChartOfAccount::TYPE_EXPENSE
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


        if (! $fallback) {
            throw ValidationException::withMessages([
                'expense_category_id' => [
                    'No valid Expense account is available for this Expense category.',
                ],
            ]);
        }


        DB::table(
            'expense_categories'
        )
            ->where(
                'id',
                $category->id
            )
            ->update([

                'ledger_account_id' =>
                    $fallback->id,

                'updated_at' =>
                    now(),
            ]);


        $category->ledger_account_id =
            $fallback->id;


        return $fallback;
    }


    /*
    |--------------------------------------------------------------------------
    | Payment Method Asset Account
    |--------------------------------------------------------------------------
    */

    private function resolvePaymentAssetAccount(
        string $paymentMethod
    ): ChartOfAccount {

        $mapping =
            AccountingPaymentMapping::query()
                ->with(
                    'ledgerAccount'
                )
                ->where(
                    'payment_method',
                    $paymentMethod
                )
                ->first();


        $account =
            $mapping?->ledgerAccount;


        if (
            ! $account
            ||
            $account->account_type !==
                ChartOfAccount::TYPE_ASSET
            ||
            ! $account->is_active
            ||
            ! $account->allow_manual_posting
        ) {
            throw ValidationException::withMessages([
                'payment_method' => [
                    'The selected payment method does not have a valid Asset account mapping.',
                ],
            ]);
        }


        return $account;
    }


    /*
    |--------------------------------------------------------------------------
    | Actor
    |--------------------------------------------------------------------------
    */

    private function resolveActor(
        Model $source
    ): User {

        $actorId =
            auth()->id()
            ??
            $source->updated_by
            ??
            $source->created_by
            ??
            null;


        if ($actorId) {

            $actor =
                User::query()
                    ->find(
                        $actorId
                    );


            if ($actor) {
                return $actor;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | System / Backfill Fallback Actor
        |--------------------------------------------------------------------------
        */

        $admin =
            User::query()
                ->where(
                    'is_active',
                    true
                )
                ->whereNull(
                    'blocked_at'
                )
                ->whereHas(
                    'role',
                    function ($query): void {

                        $query->where(
                            'name',
                            'admin'
                        );
                    }
                )
                ->orderBy(
                    'id'
                )
                ->first();


        if (! $admin) {
            throw ValidationException::withMessages([
                'accounting' => [
                    'An active Admin account is required to post accounting journals.',
                ],
            ]);
        }


        return $admin;
    }


    private function sourceHash(
        array $data
    ): string {

        ksort(
            $data
        );


        return hash(
            'sha256',
            json_encode(
                $data,
                JSON_THROW_ON_ERROR
            )
        );
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
