<?php

namespace App\Services;

use App\Models\AccountingPaymentMapping;
use App\Models\ChartOfAccount;
use App\Models\PurchaseOrderPayment;
use App\Models\PurchaseOrderReceipt;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PurchaseAccountingPostingService
{
    public const SOURCE_PURCHASE_RECEIPT = 'purchase_receipt';
    public const SOURCE_PURCHASE_PAYMENT = 'purchase_payment';

    public function __construct(
        private readonly JournalService $journalService
    ) {
    }

    public function syncReceipt(
        PurchaseOrderReceipt $receipt,
        User $user
    ) {
        $receipt = PurchaseOrderReceipt::on('tenant')
            ->with([
                'items',
                'purchaseOrder.supplier',
            ])
            ->findOrFail($receipt->id);

        $amount = round(
            (float) $receipt->items->sum(
                fn ($item) => (float) $item->total_cost
            ),
            2
        );

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'purchase_receipt' => [
                    'GRN accounting value must be greater than zero.',
                ],
            ]);
        }

        $inventoryAccount = $this->systemPostingAccount(
            'inventory',
            ChartOfAccount::TYPE_ASSET
        );

        $payableAccount = $this->systemPostingAccount(
            'accounts_payable',
            ChartOfAccount::TYPE_LIABILITY
        );

        $journalDate = $receipt->received_at?->format('Y-m-d')
            ?? now()->toDateString();

        $sourceHash = $this->hash([
            'source_type' => self::SOURCE_PURCHASE_RECEIPT,
            'source_id' => (int) $receipt->id,
            'journal_date' => $journalDate,
            'amount' => number_format($amount, 2, '.', ''),
            'inventory_account_id' => (int) $inventoryAccount->id,
            'payable_account_id' => (int) $payableAccount->id,
        ]);

        $poNumber = $receipt->purchaseOrder?->purchase_order_no
            ?? $receipt->purchaseOrder?->po_number
            ?? ('PO #' . $receipt->purchase_order_id);

        return $this->journalService->syncSource(
            sourceType: self::SOURCE_PURCHASE_RECEIPT,
            sourceId: (int) $receipt->id,
            sourceHash: $sourceHash,
            journalDate: $journalDate,
            description: 'GRN ' . $receipt->receipt_no . ' — ' . $poNumber,
            lines: [
                [
                    'ledger_account_id' => (int) $inventoryAccount->id,
                    'debit' => $amount,
                    'credit' => 0,
                    'memo' => 'Inventory received via ' . $receipt->receipt_no,
                ],
                [
                    'ledger_account_id' => (int) $payableAccount->id,
                    'debit' => 0,
                    'credit' => $amount,
                    'memo' => 'Supplier liability created from ' . $receipt->receipt_no,
                ],
            ],
            user: $user,
            changeReason: 'GRN financial value changed.'
        );
    }

    public function syncPayment(
        PurchaseOrderPayment $payment,
        User $user
    ) {
        $payment = PurchaseOrderPayment::on('tenant')
            ->with([
                'purchaseOrder.supplier',
            ])
            ->findOrFail($payment->id);

        $amount = round(
            (float) $payment->amount,
            2
        );

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'purchase_payment' => [
                    'Supplier payment accounting value must be greater than zero.',
                ],
            ]);
        }

        $payableAccount = $this->systemPostingAccount(
            'accounts_payable',
            ChartOfAccount::TYPE_LIABILITY
        );

        $assetAccount = $this->paymentAssetAccount(
            (string) $payment->payment_method
        );

        $journalDate = $payment->payment_date
            ? (
                is_object($payment->payment_date)
                    ? $payment->payment_date->format('Y-m-d')
                    : (string) $payment->payment_date
            )
            : now()->toDateString();

        $sourceHash = $this->hash([
            'source_type' => self::SOURCE_PURCHASE_PAYMENT,
            'source_id' => (int) $payment->id,
            'journal_date' => $journalDate,
            'amount' => number_format($amount, 2, '.', ''),
            'payment_method' => (string) $payment->payment_method,
            'payable_account_id' => (int) $payableAccount->id,
            'asset_account_id' => (int) $assetAccount->id,
        ]);

        $poNumber = $payment->purchaseOrder?->purchase_order_no
            ?? $payment->purchaseOrder?->po_number
            ?? ('PO #' . $payment->purchase_order_id);

        $description = 'Supplier Payment — ' . $poNumber;

        if ($payment->transaction_reference) {
            $description .= ' — Ref: ' . $payment->transaction_reference;
        }

        return $this->journalService->syncSource(
            sourceType: self::SOURCE_PURCHASE_PAYMENT,
            sourceId: (int) $payment->id,
            sourceHash: $sourceHash,
            journalDate: $journalDate,
            description: $description,
            lines: [
                [
                    'ledger_account_id' => (int) $payableAccount->id,
                    'debit' => $amount,
                    'credit' => 0,
                    'memo' => 'Supplier payable reduced',
                ],
                [
                    'ledger_account_id' => (int) $assetAccount->id,
                    'debit' => 0,
                    'credit' => $amount,
                    'memo' => 'Paid via ' . $this->paymentMethodLabel(
                        (string) $payment->payment_method
                    ),
                ],
            ],
            user: $user,
            changeReason: 'Supplier payment financial data changed.'
        );
    }

    private function systemPostingAccount(
    string $systemKey,
    string $accountType
): ChartOfAccount {
    $account = ChartOfAccount::on('tenant')
        ->where('system_key', $systemKey)
        ->where('account_type', $accountType)
        ->where('is_active', true)
        ->where('allow_manual_posting', true)
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

    private function paymentAssetAccount(
        string $paymentMethod
    ): ChartOfAccount {
        $mapping = AccountingPaymentMapping::on('tenant')
            ->with('ledgerAccount')
            ->where('payment_method', $paymentMethod)
            ->first();

        $account = $mapping?->ledgerAccount;

        if (
            ! $account
            || $account->account_type !== ChartOfAccount::TYPE_ASSET
            || ! $account->is_active
            || ! $account->allow_manual_posting
        ) {
            throw ValidationException::withMessages([
                'payment_method' => [
                    'This supplier payment method does not have a valid Asset account mapping.',
                ],
            ]);
        }

        return $account;
    }

    private function hash(array $data): string
    {
        ksort($data);

        return hash(
            'sha256',
            json_encode($data, JSON_THROW_ON_ERROR)
        );
    }

    private function paymentMethodLabel(string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'cash' => 'Cash',
            'card' => 'Card',
            'bkash' => 'bKash',
            'nagad' => 'Nagad',
            'bank_transfer' => 'Bank Transfer',
            'mixed' => 'Mixed Payment',
            'other' => 'Other',
            default => ucwords(
                str_replace('_', ' ', $paymentMethod)
            ),
        };
    }
}
