<?php

namespace App\Services;

use App\Models\AccountingPaymentMapping;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SalesAccountingService
{
    public const SOURCE_ORDER_SALE = 'order_sale';

    public function __construct(
        private readonly JournalService $journalService
    ) {
    }
    
    /**
     * Post the accounting journal for a completed restaurant order.
     *
     * POS and normal restaurant orders are the same accounting sale.
     * The journal is created only after the order reaches COMPLETED status.
     */
    public function createSaleJournal(
        
        Order $order,
        ?int $userId = null
    ): JournalEntry {
        //dd('SalesAccountingService reached');
        $order = Order::query()
            ->with([
                'payments',
            ])
            ->findOrFail(
                $order->id
            );

        /*
        |--------------------------------------------------------------------------
        | Completed Order Only
        |--------------------------------------------------------------------------
        */

        if (
            $order->status !==
            Order::STATUS_COMPLETED
        ) {
            throw ValidationException::withMessages([
                'order' => [
                    'Sales accounting can only be posted for a completed order.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve Actor
        |--------------------------------------------------------------------------
        */

        $actor = $this->resolveActor(
            order: $order,
            userId: $userId
        );

        /*
        |--------------------------------------------------------------------------
        | Financial Amounts
        |--------------------------------------------------------------------------
        */

        $totalAmount = round(
            (float) $order->total_amount,
            2
        );

        $taxAmount = round(
            max(
                0,
                (float) $order->tax_amount
            ),
            2
        );

        if ($totalAmount <= 0) {
            throw ValidationException::withMessages([
                'total_amount' => [
                    'Completed order total must be greater than zero before sales accounting can be posted.',
                ],
            ]);
        }

        if ($taxAmount > $totalAmount) {
            throw ValidationException::withMessages([
                'tax_amount' => [
                    'Order tax amount cannot be greater than the completed order total.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Revenue Amount
        |--------------------------------------------------------------------------
        |
        | Revenue is the completed customer bill excluding VAT/tax.
        |
        | subtotal
        | - discount
        | + service charge
        | = restaurant sales revenue
        |
        | VAT/tax is posted separately to Tax Payable.
        |
        */

        $discountAmount = round(
            max(
                0,
                (float) $order->discount_amount
            ),
            2
        );


        $serviceCharge = round(
            max(
                0,
                (float) $order->service_charge
            ),
            2
        );


        $foodRevenueAmount = round(
            $totalAmount
            -
            $taxAmount
            -
            $serviceCharge
            +
            $discountAmount,
            2
        );


        if ($foodRevenueAmount < 0) {
            throw ValidationException::withMessages([
                'accounting' => [
                    'Calculated restaurant sales revenue cannot be negative.',
                ],
            ]);
        }

        if ($foodRevenueAmount < 0) {
            throw ValidationException::withMessages([
                'accounting' => [
                    'Calculated restaurant sales revenue cannot be negative.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Authoritative Payment Ledger
        |--------------------------------------------------------------------------
        |
        | Never use order.payment_method as the accounting source.
        | Immutable order_payments rows are the payment source of truth.
        |
        */

        $payments = $order
            ->payments
            ->sortBy('id')
            ->values();

        if ($payments->isEmpty()) {
            throw ValidationException::withMessages([
                'payments' => [
                    'A completed order must have payment history before sales accounting can be posted.',
                ],
            ]);
        }

        $paymentTotal = round(
            (float) $payments->sum(
                static fn ($payment): float =>
                    (float) $payment->amount
            ),
            2
        );

        if (
            abs(
                $paymentTotal -
                $totalAmount
            ) > 0.009
        ) {
            throw ValidationException::withMessages([
                'payments' => [
                    'Completed order payment history must exactly equal the order total before sales accounting can be posted.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve Revenue Account
        |--------------------------------------------------------------------------
        */

        $revenueAccount =
            $this->systemPostingAccount(
                systemKey:
                    'restaurant_sales_revenue',
                accountType:
                    ChartOfAccount::TYPE_REVENUE
            );
        $serviceChargeAccount = null;


if ($serviceCharge > 0) {

    $serviceChargeAccount =
        $this->systemPostingAccount(
            systemKey:
                'service_charge_income',

            accountType:
                ChartOfAccount::TYPE_REVENUE
        );

}

        /*
        |--------------------------------------------------------------------------
        | Resolve Tax Payable Account
        |--------------------------------------------------------------------------
        */

        $taxAccount = null;
        $discountAccount = null;

        if ($taxAmount > 0) {
            $taxAccount =
                $this->systemPostingAccount(
                    systemKey:
                        'tax_payable',
                    accountType:
                        ChartOfAccount::TYPE_LIABILITY
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Group Actual Payments By Method
        |--------------------------------------------------------------------------
        */

        $paymentGroups = [];

        foreach ($payments as $payment) {
            $paymentMethod = trim(
                (string) $payment->payment_method
            );

            if ($paymentMethod === '') {
                throw ValidationException::withMessages([
                    'payment_method' => [
                        'Every order payment must have a payment method before sales accounting can be posted.',
                    ],
                ]);
            }

            if (
                ! array_key_exists(
                    $paymentMethod,
                    $paymentGroups
                )
            ) {
                $paymentGroups[$paymentMethod] = 0.00;
            }

            $paymentGroups[$paymentMethod] = round(
                $paymentGroups[$paymentMethod]
                +
                (float) $payment->amount,
                2
            );
        }

        ksort($paymentGroups);

        /*
        |--------------------------------------------------------------------------
        | Build Debit Lines From Actual Payment Methods
        |--------------------------------------------------------------------------
        */

        $lines = [];
        $paymentHashRows = [];

        foreach (
            $paymentGroups
            as $paymentMethod => $amount
        ) {
            if ($amount <= 0) {
                continue;
            }

            $assetAccount =
                $this->paymentAssetAccount(
                    $paymentMethod
                );

            $lines[] = [
                'ledger_account_id' =>
                    (int) $assetAccount->id,

                'debit' =>
                    round(
                        (float) $amount,
                        2
                    ),

                'credit' =>
                    0,

                'memo' =>
                    'Customer payment received via '
                    .
                    $this->paymentMethodLabel(
                        $paymentMethod
                    ),
            ];

            $paymentHashRows[] = [
                'payment_method' =>
                    $paymentMethod,

                'amount' =>
                    number_format(
                        (float) $amount,
                        2,
                        '.',
                        ''
                    ),

                'ledger_account_id' =>
                    (int) $assetAccount->id,
            ];
        }

        if ($discountAmount > 0) {

    $discountAccount =
        $this->systemPostingAccount(
            systemKey:
                'sales_discount',

            accountType:
                ChartOfAccount::TYPE_EXPENSE
        );


    $lines[] = [

        'ledger_account_id' =>
            (int) $discountAccount->id,

        'debit' =>
            $discountAmount,

        'credit' =>
            0,

        'memo' =>
            'Sales discount for '
            .
            $order->order_number,
    ];
}

        /*
        |--------------------------------------------------------------------------
        | Credit Restaurant Sales Revenue
        |--------------------------------------------------------------------------
        */

        if ($foodRevenueAmount > 0) {

    $lines[] = [

        'ledger_account_id' =>
            (int) $revenueAccount->id,

        'debit' =>
            0,

        'credit' =>
            $foodRevenueAmount,

        'memo' =>
            'Restaurant food sales revenue for '
            .
            $order->order_number,

    ];

}



if (
    $serviceCharge > 0
    &&
    $serviceChargeAccount
) {

    $lines[] = [

        'ledger_account_id' =>
            (int) $serviceChargeAccount->id,

        'debit' =>
            0,

        'credit' =>
            $serviceCharge,

        'memo' =>
            'Service charge income for '
            .
            $order->order_number,

    ];

}

        /*
        |--------------------------------------------------------------------------
        | Credit Tax Payable
        |--------------------------------------------------------------------------
        */

        if (
            $taxAmount > 0
            &&
            $taxAccount
        ) {
            $lines[] = [
                'ledger_account_id' =>
                    (int) $taxAccount->id,

                'debit' =>
                    0,

                'credit' =>
                    $taxAmount,

                'memo' =>
                    'VAT / tax collected on '
                    .
                    $order->order_number,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Accounting Date
        |--------------------------------------------------------------------------
        */

        $journalDate =
            $order->completed_at
                ? (
                    is_object(
                        $order->completed_at
                    )
                        ? $order
                            ->completed_at
                            ->format('Y-m-d')
                        : substr(
                            (string)
                            $order->completed_at,
                            0,
                            10
                        )
                )
                : now()->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Source Hash
        |--------------------------------------------------------------------------
        |
        | JournalService uses this hash for idempotency and revisions.
        |
        */

        $paymentHistoryHashRows =
            $payments
                ->map(
                    static function (
                        $payment
                    ): array {
                        return [
                            'id' =>
                                (int) $payment->id,

                            'amount' =>
                                number_format(
                                    (float) $payment->amount,
                                    2,
                                    '.',
                                    ''
                                ),

                            'payment_method' =>
                                (string)
                                $payment->payment_method,
                        ];
                    }
                )
                ->values()
                ->all();

        

        $sourceHash =
            $this->sourceHash([
                'source_type' =>
                    self::SOURCE_ORDER_SALE,

                'source_id' =>
                    (int) $order->id,

                'order_number' =>
                    (string) $order->order_number,

                'journal_date' =>
                    $journalDate,

                'subtotal' =>
                    number_format(
                        (float) $order->subtotal,
                        2,
                        '.',
                        ''
                    ),

                'discount_amount' =>
                    number_format(
                        (float) $order->discount_amount,
                        2,
                        '.',
                        ''
                    ),

                'tax_amount' =>
                    number_format(
                        $taxAmount,
                        2,
                        '.',
                        ''
                    ),

                'service_charge' =>
                    number_format(
                        (float) $order->service_charge,
                        2,
                        '.',
                        ''
                    ),

                'total_amount' =>
                    number_format(
                        $totalAmount,
                        2,
                        '.',
                        ''
                    ),

                'food_revenue_amount' =>
    number_format(
        $foodRevenueAmount,
        2,
        '.',
        ''
    ),

'service_charge_account_id' =>
    $serviceChargeAccount
        ? (int) $serviceChargeAccount->id
        : null,

                'payment_history' =>
                    $paymentHistoryHashRows,

                'payment_posting' =>
                    $paymentHashRows,

                'revenue_account_id' =>
                    (int) $revenueAccount->id,

                'tax_account_id' =>
                    $taxAccount
                        ? (int) $taxAccount->id
                        : null,
            ]);

        /*
        |--------------------------------------------------------------------------
        | Post Through Central Journal Engine
        |--------------------------------------------------------------------------
        |
        | JournalService handles:
        | - JV number
        | - debit/credit validation
        | - account snapshots
        | - idempotency
        | - source revisions
        | - reversals
        |
        */
        return $this->journalService
            ->syncSource(
                sourceType:
                    self::SOURCE_ORDER_SALE,

                sourceId:
                    (int) $order->id,

                sourceHash:
                    $sourceHash,

                journalDate:
                    $journalDate,

                description:
                    'Restaurant Sale — '
                    .
                    $order->order_number,

                lines:
                    $lines,

                user:
                    $actor,

                changeReason:
                    'Completed restaurant sale financial data changed.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Required System Posting Account
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
                    "Required accounting account [{$systemKey}] is unavailable.",
                ],
            ]);
        }

        return $account;
    }

    /*
    |--------------------------------------------------------------------------
    | Payment Method -> Asset Account
    |--------------------------------------------------------------------------
    */

    private function paymentAssetAccount(
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
                    "Payment method [{$paymentMethod}] does not have a valid active Asset account mapping.",
                ],
            ]);
        }

        return $account;
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Posting User
    |--------------------------------------------------------------------------
    */

    private function resolveActor(
        Order $order,
        ?int $userId = null
    ): User {
        $actorId =
            $userId
            ??
            auth()->id()
            ??
            $order->created_by
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
                ->orderBy('id')
                ->first();

        if (! $admin) {
            throw ValidationException::withMessages([
                'accounting' => [
                    'An active Admin account is required to post sales accounting journals.',
                ],
            ]);
        }

        return $admin;
    }

    /*
    |--------------------------------------------------------------------------
    | Deterministic Source Hash
    |--------------------------------------------------------------------------
    */

    private function sourceHash(
        array $data
    ): string {
        ksort($data);

        return hash(
            'sha256',
            json_encode(
                $data,
                JSON_THROW_ON_ERROR
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Payment Method Label
    |--------------------------------------------------------------------------
    */

    private function paymentMethodLabel(
        string $paymentMethod
    ): string {
        return match ($paymentMethod) {
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
