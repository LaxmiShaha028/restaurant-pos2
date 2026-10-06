<?php

namespace App\Services;

use App\Models\AccountingPaymentMapping;
use App\Models\ChartOfAccount;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnSettlement;
use App\Models\SupplierCreditApplication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseReturnAccountingPostingService
{
    public const SOURCE_RETURN =
        'purchase_return';

    public const SOURCE_SETTLEMENT =
        'purchase_return_settlement';

    public const SOURCE_CREDIT_APPLICATION =
        'supplier_credit_application';


    public function __construct(
        private readonly JournalService $journalService
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Physical Purchase Return
    |--------------------------------------------------------------------------
    |
    | The stock leaves the restaurant now.
    |
    | Dr Purchase Return Clearing        expected supplier credit
    | Dr Valuation Loss                  if carrying value > supplier credit
    |    Cr Inventory                    carrying value removed
    |    Cr Valuation Gain               if supplier credit > carrying value
    |
    */

    public function syncReturn(
        PurchaseReturn $purchaseReturn,
        ?User $actor = null
    ) {
        $expectedCredit =
            $this->money(
                $purchaseReturn
                    ->expected_supplier_credit
                ??
                0
            );

        $inventoryReduction =
            $this->money(
                $purchaseReturn
                    ->inventory_value_reduction
                ??
                0
            );

        if (
            $expectedCredit <= 0
            &&
            $inventoryReduction <= 0
        ) {
            return null;
        }

        $actor =
            $actor
            ??
            $this->resolveActor(
                $purchaseReturn
            );

        $clearing =
            $this->systemPostingAccount(
                'purchase_return_clearing',
                ChartOfAccount::TYPE_ASSET
            );

        $inventory =
            $this->systemPostingAccount(
                'inventory',
                ChartOfAccount::TYPE_ASSET
            );

        $gain =
            $this->systemPostingAccount(
                'purchase_return_valuation_gain',
                ChartOfAccount::TYPE_REVENUE
            );

        $loss =
            $this->systemPostingAccount(
                'purchase_return_valuation_loss',
                ChartOfAccount::TYPE_EXPENSE
            );

        $difference =
            $this->money(
                $expectedCredit
                -
                $inventoryReduction
            );

        $lines =
            [];

        if ($expectedCredit > 0) {
            $lines[] = [
                'ledger_account_id' =>
                    (int) $clearing->id,

                'debit' =>
                    $expectedCredit,

                'credit' =>
                    0,

                'memo' =>
                    'Supplier recovery expected from Purchase Return.',
            ];
        }

        if ($difference < 0) {
            $lines[] = [
                'ledger_account_id' =>
                    (int) $loss->id,

                'debit' =>
                    abs($difference),

                'credit' =>
                    0,

                'memo' =>
                    'Purchase Return valuation loss.',
            ];
        }

        if ($inventoryReduction > 0) {
            $lines[] = [
                'ledger_account_id' =>
                    (int) $inventory->id,

                'debit' =>
                    0,

                'credit' =>
                    $inventoryReduction,

                'memo' =>
                    'Inventory carrying value removed by Purchase Return.',
            ];
        }

        if ($difference > 0) {
            $lines[] = [
                'ledger_account_id' =>
                    (int) $gain->id,

                'debit' =>
                    0,

                'credit' =>
                    $difference,

                'memo' =>
                    'Purchase Return valuation gain.',
            ];
        }

        $journalDate =
            $this->date(
                $purchaseReturn
                    ->returned_at
                ??
                $purchaseReturn
                    ->created_at
                ??
                now()
            );

        $sourceHash =
            $this->hash([
                'source_type' =>
                    self::SOURCE_RETURN,

                'source_id' =>
                    (int) $purchaseReturn->id,

                'journal_date' =>
                    $journalDate,

                'expected_supplier_credit' =>
                    $this->moneyString(
                        $expectedCredit
                    ),

                'inventory_value_reduction' =>
                    $this->moneyString(
                        $inventoryReduction
                    ),

                'clearing_account_id' =>
                    (int) $clearing->id,

                'inventory_account_id' =>
                    (int) $inventory->id,

                'gain_account_id' =>
                    (int) $gain->id,

                'loss_account_id' =>
                    (int) $loss->id,
            ]);

        return $this->journalService
            ->syncSource(
                sourceType:
                    self::SOURCE_RETURN,

                sourceId:
                    (int) $purchaseReturn->id,

                sourceHash:
                    $sourceHash,

                journalDate:
                    $journalDate,

                description:
                    'Purchase Return '
                    .
                    $this->returnReference(
                        $purchaseReturn
                    ),

                lines:
                    $lines,

                user:
                    $actor,

                changeReason:
                    'Purchase Return financial value changed.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Purchase Return Settlement
    |--------------------------------------------------------------------------
    */

    public function syncSettlement(
        PurchaseReturnSettlement $settlement,
        ?User $actor = null
    ) {
        $amount =
            $this->money(
                $settlement->amount
                ??
                0
            );

        if ($amount <= 0) {
            return null;
        }

        $actor =
            $actor
            ??
            $this->resolveActor(
                $settlement
            );

        $settlementType =
            $this->normalizeSettlementType(
                (string) (
                    $settlement
                        ->settlement_type
                    ??
                    ''
                )
            );

        $clearing =
            $this->systemPostingAccount(
                'purchase_return_clearing',
                ChartOfAccount::TYPE_ASSET
            );

        $payable =
            $this->systemPostingAccount(
                'accounts_payable',
                ChartOfAccount::TYPE_LIABILITY
            );

        $supplierCredit =
            $this->systemPostingAccount(
                'supplier_credit_receivable',
                ChartOfAccount::TYPE_ASSET
            );

        $refundReceivable =
            $this->systemPostingAccount(
                'supplier_refund_receivable',
                ChartOfAccount::TYPE_ASSET
            );

        $debitAccount =
            null;

        $creditAccount =
            null;

        $description =
            null;

        switch ($settlementType) {
            case 'payable_offset':
                $debitAccount =
                    $payable;

                $creditAccount =
                    $clearing;

                $description =
                    'Purchase Return — Reduce Supplier Payable';

                break;

            case 'supplier_credit':
                $debitAccount =
                    $supplierCredit;

                $creditAccount =
                    $clearing;

                $description =
                    'Purchase Return — Supplier Credit Issued';

                break;

            case 'refund_receivable':
                $debitAccount =
                    $refundReceivable;

                $creditAccount =
                    $clearing;

                $description =
                    'Purchase Return — Refund Receivable';

                break;

            case 'direct_refund':
                $debitAccount =
                    $this->paymentAssetAccount(
                        (string) (
                            $settlement
                                ->payment_method
                            ??
                            ''
                        )
                    );

                $creditAccount =
                    $clearing;

                $description =
                    'Purchase Return — Direct Refund Received';

                break;

            case 'receivable_collection':
                $debitAccount =
                    $this->paymentAssetAccount(
                        (string) (
                            $settlement
                                ->payment_method
                            ??
                            ''
                        )
                    );

                $creditAccount =
                    $refundReceivable;

                $description =
                    'Purchase Return — Refund Receivable Collected';

                break;

            default:
                throw ValidationException::withMessages([
                    'settlement_type' => [
                        'Unsupported Purchase Return settlement type for accounting: '
                        .
                        (
                            $settlement
                                ->settlement_type
                            ??
                            '(empty)'
                        ),
                    ],
                ]);
        }

        $journalDate =
            $this->date(
                $settlement
                    ->settled_at
                ??
                $settlement
                    ->created_at
                ??
                now()
            );

        $sourceHash =
            $this->hash([
                'source_type' =>
                    self::SOURCE_SETTLEMENT,

                'source_id' =>
                    (int) $settlement->id,

                'journal_date' =>
                    $journalDate,

                'settlement_type' =>
                    $settlementType,

                'amount' =>
                    $this->moneyString(
                        $amount
                    ),

                'payment_method' =>
                    (string) (
                        $settlement
                            ->payment_method
                        ??
                        ''
                    ),

                'debit_account_id' =>
                    (int) $debitAccount->id,

                'credit_account_id' =>
                    (int) $creditAccount->id,
            ]);

        return $this->journalService
            ->syncSource(
                sourceType:
                    self::SOURCE_SETTLEMENT,

                sourceId:
                    (int) $settlement->id,

                sourceHash:
                    $sourceHash,

                journalDate:
                    $journalDate,

                description:
                    $description
                    .
                    ' — '
                    .
                    (
                        $settlement
                            ->settlement_no
                        ??
                        (
                            'Settlement #'
                            .
                            $settlement->id
                        )
                    ),

                lines: [
                    [
                        'ledger_account_id' =>
                            (int) $debitAccount->id,

                        'debit' =>
                            $amount,

                        'credit' =>
                            0,

                        'memo' =>
                            $description,
                    ],
                    [
                        'ledger_account_id' =>
                            (int) $creditAccount->id,

                        'debit' =>
                            0,

                        'credit' =>
                            $amount,

                        'memo' =>
                            $description,
                    ],
                ],

                user:
                    $actor,

                changeReason:
                    'Purchase Return settlement financial data changed.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Supplier Credit Application
    |--------------------------------------------------------------------------
    |
    | Reusable supplier credit is consumed against another/future PO.
    |
    | Dr Accounts Payable
    |    Cr Supplier Credit Receivable
    |
    */

    public function syncSupplierCreditApplication(
        SupplierCreditApplication $application,
        ?User $actor = null
    ) {
        $amount =
            $this->money(
                $application->amount
                ??
                0
            );

        if ($amount <= 0) {
            return null;
        }

        $actor =
            $actor
            ??
            $this->resolveActor(
                $application
            );

        $payable =
            $this->systemPostingAccount(
                'accounts_payable',
                ChartOfAccount::TYPE_LIABILITY
            );

        $supplierCredit =
            $this->systemPostingAccount(
                'supplier_credit_receivable',
                ChartOfAccount::TYPE_ASSET
            );

        $journalDate =
            $this->date(
                $application
                    ->applied_at
                ??
                $application
                    ->created_at
                ??
                now()
            );

        $sourceHash =
            $this->hash([
                'source_type' =>
                    self::SOURCE_CREDIT_APPLICATION,

                'source_id' =>
                    (int) $application->id,

                'journal_date' =>
                    $journalDate,

                'amount' =>
                    $this->moneyString(
                        $amount
                    ),

                'purchase_order_id' =>
                    (int) (
                        $application
                            ->purchase_order_id
                        ??
                        0
                    ),

                'source_settlement_id' =>
                    (int) (
                        $application
                            ->source_settlement_id
                        ??
                        0
                    ),

                'payable_account_id' =>
                    (int) $payable->id,

                'supplier_credit_account_id' =>
                    (int) $supplierCredit->id,
            ]);

        return $this->journalService
            ->syncSource(
                sourceType:
                    self::SOURCE_CREDIT_APPLICATION,

                sourceId:
                    (int) $application->id,

                sourceHash:
                    $sourceHash,

                journalDate:
                    $journalDate,

                description:
                    'Supplier Credit Applied to PO #'
                    .
                    (
                        $application
                            ->purchase_order_id
                        ??
                        '-'
                    )
                    .
                    ' — '
                    .
                    (
                        $application
                            ->application_no
                        ??
                        (
                            'Application #'
                            .
                            $application->id
                        )
                    ),

                lines: [
                    [
                        'ledger_account_id' =>
                            (int) $payable->id,

                        'debit' =>
                            $amount,

                        'credit' =>
                            0,

                        'memo' =>
                            'Supplier payable reduced using existing Supplier Credit.',
                    ],
                    [
                        'ledger_account_id' =>
                            (int) $supplierCredit->id,

                        'debit' =>
                            0,

                        'credit' =>
                            $amount,

                        'memo' =>
                            'Supplier Credit Receivable consumed.',
                    ],
                ],

                user:
                    $actor,

                changeReason:
                    'Supplier Credit application financial data changed.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Settlement Type Normalization
    |--------------------------------------------------------------------------
    */

    private function normalizeSettlementType(
        string $type
    ): string {
        $type =
            strtolower(
                trim(
                    $type
                )
            );

        return match ($type) {
            'payable_offset',
            'reduce_supplier_payable',
            'payable_reduction' =>
                'payable_offset',

            'supplier_credit' =>
                'supplier_credit',

            'refund_receivable' =>
                'refund_receivable',

            'direct_refund',
            'cash_refund' =>
                'direct_refund',

            'receivable_collection',
            'refund_collection',
            'collect_refund' =>
                'receivable_collection',

            default =>
                $type,
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Accounts
    |--------------------------------------------------------------------------
    */

    private function systemPostingAccount(
        string $systemKey,
        string $accountType
    ): ChartOfAccount {
        $account =
            ChartOfAccount::query()
                ->where(
                    'system_key',
                    $systemKey
                )
                ->where(
                    'account_type',
                    $accountType
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
                'accounting' => [
                    "Required Purchase Return account [{$systemKey}] is unavailable.",
                ],
            ]);
        }

        return $account;
    }


    private function paymentAssetAccount(
        string $paymentMethod
    ): ChartOfAccount {
        $paymentMethod =
            strtolower(
                trim(
                    $paymentMethod
                )
            );

        if ($paymentMethod === '') {
            throw ValidationException::withMessages([
                'payment_method' => [
                    'A payment method is required for a cash/bank Purchase Return refund event.',
                ],
            ]);
        }

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
                    'The Purchase Return refund payment method does not have a valid Asset account mapping.',
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
                    'An active Admin account is required to post Purchase Return accounting journals.',
                ],
            ]);
        }

        return $admin;
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function returnReference(
        PurchaseReturn $purchaseReturn
    ): string {
        return (string) (
            $purchaseReturn
                ->return_no
            ??
            $purchaseReturn
                ->purchase_return_no
            ??
            (
                '#'
                .
                $purchaseReturn->id
            )
        );
    }


    private function money(
        mixed $value
    ): float {
        return round(
            (float) $value,
            2
        );
    }


    private function moneyString(
        float $value
    ): string {
        return number_format(
            $value,
            2,
            '.',
            ''
        );
    }


    private function date(
        mixed $value
    ): string {
        return Carbon::parse(
            $value
        )->format(
            'Y-m-d'
        );
    }


    private function hash(
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
}
