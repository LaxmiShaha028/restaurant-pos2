<?php

namespace App\Http\Resources;

use App\Models\PurchaseReturnSettlement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class PurchaseReturnResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items =
            $this->relationLoaded('items')
                ? $this->items
                : collect();

        $settlements =
            $this->relationLoaded('settlements')
                ? $this->settlements
                : collect();

        $topLevelSettlements =
            $settlements
                ->whereNull('parent_settlement_id')
                ->values();

        $quantityByUnit =
            $items
                ->groupBy(
                    static fn ($item): string =>
                        (string) ($item->unit ?? 'unknown')
                )
                ->map(
                    static function ($group, string $unit): array {
                        return [
                            'unit' => $unit,
                            'quantity' =>
                                round(
                                    (float) $group->sum('quantity'),
                                    4
                                ),
                        ];
                    }
                )
                ->values()
                ->all();

        $targetAmount =
            round(
                (float) $this->expected_supplier_credit,
                2
            );

        $allocatedAmount =
            round(
                (float) $settlements
                    ->where('allocation_effect', true)
                    ->sum('amount'),
                2
            );

        $unallocatedAmount =
            round(
                max(
                    0,
                    $targetAmount - $allocatedAmount
                ),
                2
            );

        $payableOffsetAmount =
            round(
                (float) $settlements
                    ->where(
                        'settlement_type',
                        PurchaseReturnSettlement::TYPE_PAYABLE_OFFSET
                    )
                    ->sum('amount'),
                2
            );

        $supplierCreditSettlements =
            $settlements
                ->where(
                    'settlement_type',
                    PurchaseReturnSettlement::TYPE_SUPPLIER_CREDIT
                );


        $supplierCreditAmount =
            round(
                (float) $supplierCreditSettlements
                    ->sum('amount'),
                2
            );


        $supplierCreditAppliedAmount =
            round(
                (float) $supplierCreditSettlements
                    ->sum(
                        static function ($settlement): float {

                            if (
                                !$settlement->relationLoaded(
                                    'creditApplications'
                                )
                            ) {
                                return 0.0;
                            }


                            return (float) $settlement
                                ->creditApplications
                                ->sum('amount');
                        }
                    ),
                2
            );


        $supplierCreditBalance =
            round(
                max(
                    0,
                    $supplierCreditAmount
                    -
                    $supplierCreditAppliedAmount
                ),
                2
            );

        $refundReceivableCreated =
            round(
                (float) $settlements
                    ->where(
                        'settlement_type',
                        PurchaseReturnSettlement::TYPE_REFUND_RECEIVABLE
                    )
                    ->sum('amount'),
                2
            );

        $refundCollected =
            round(
                (float) $settlements
                    ->where(
                        'settlement_type',
                        PurchaseReturnSettlement::TYPE_RECEIVABLE_COLLECTION
                    )
                    ->sum('amount'),
                2
            );

        $directRefundAmount =
            round(
                (float) $settlements
                    ->where(
                        'settlement_type',
                        PurchaseReturnSettlement::TYPE_DIRECT_REFUND
                    )
                    ->sum('amount'),
                2
            );

        $refundReceivableBalance =
            round(
                max(
                    0,
                    $refundReceivableCreated
                    -
                    $refundCollected
                ),
                2
            );

        $cashRefundTotal =
            round(
                $directRefundAmount
                +
                $refundCollected,
                2
            );

        $settlementStatus =
            $this->resolveSettlementStatus(
                targetAmount:
                    $targetAmount,

                allocatedAmount:
                    $allocatedAmount,

                refundReceivableBalance:
                    $refundReceivableBalance
            );

        $linkedPurchaseOrders =
            $items
                ->map(
                    static fn ($item) =>
                        $item->relationLoaded('purchaseOrder')
                            ? $item->purchaseOrder
                            : null
                )
                ->filter()
                ->unique('id')
                ->map(
                    function ($po): array {
                        return [
                            'id' => (int) $po->id,

                            'total_amount' =>
                                round(
                                    (float) $po->total_amount,
                                    2
                                ),

                            'paid_amount' =>
                                round(
                                    (float) $po->paid_amount,
                                    2
                                ),

                            'return_credit_applied' =>
                                round(
                                    (float) (
                                        $po->return_credit_applied
                                        ??
                                        0
                                    ),
                                    2
                                ),

                            'due_amount' =>
                                round(
                                    max(
                                        0,
                                        (float) $po->due_amount
                                    ),
                                    2
                                ),

                            'due_amount_formatted' =>
                                $this->money(
                                    $po->due_amount
                                ),
                        ];
                    }
                )
                ->values()
                ->all();

        return [
            'id' =>
                (int) $this->id,

            'return_no' =>
                $this->return_no,

            'supplier_id' =>
                $this->supplier_id !== null
                    ? (int) $this->supplier_id
                    : null,

            'supplier' => [
                'id' =>
                    $this->supplier_id !== null
                        ? (int) $this->supplier_id
                        : null,

                'name' =>
                    $this->supplier_name,

                'supplier_name' =>
                    $this->supplier_name,

                'phone' =>
                    $this->supplier_phone,

                'email' =>
                    $this->supplier_email,

                'address' =>
                    $this->supplier_address,
            ],

            'supplier_name' =>
                $this->supplier_name,

            'returned_at' =>
                $this->returned_at?->toISOString(),

            'returned_at_label' =>
                $this->returned_at?->format(
                    'd M Y, h:i A'
                ),

            'total_items' =>
                (int) $this->total_items,

            'quantity_by_unit' =>
                $quantityByUnit,

            'expected_supplier_credit' =>
                round(
                    (float) $this->expected_supplier_credit,
                    4
                ),

            'expected_supplier_credit_formatted' =>
                $this->money(
                    $this->expected_supplier_credit
                ),

            'settlement_target_amount' =>
                $targetAmount,

            'settlement_target_amount_formatted' =>
                $this->money($targetAmount),

            'allocated_amount' =>
                $allocatedAmount,

            'allocated_amount_formatted' =>
                $this->money($allocatedAmount),

            'unallocated_amount' =>
                $unallocatedAmount,

            'unallocated_amount_formatted' =>
                $this->money($unallocatedAmount),

            'payable_offset_amount' =>
                $payableOffsetAmount,

            'supplier_credit_amount' =>
                $supplierCreditAmount,

            'supplier_credit_applied_amount' =>
                $supplierCreditAppliedAmount,

            'supplier_credit_applied_amount_formatted' =>
                $this->money(
                    $supplierCreditAppliedAmount
                ),

            'supplier_credit_balance' =>
                $supplierCreditBalance,

            'supplier_credit_balance_formatted' =>
                $this->money(
                    $supplierCreditBalance
                ),

            'refund_receivable_created' =>
                $refundReceivableCreated,

            'refund_receivable_collected' =>
                $refundCollected,

            'refund_receivable_balance' =>
                $refundReceivableBalance,

            'refund_receivable_balance_formatted' =>
                $this->money(
                    $refundReceivableBalance
                ),

            'direct_refund_amount' =>
                $directRefundAmount,

            'cash_refund_total' =>
                $cashRefundTotal,

            'cash_refund_total_formatted' =>
                $this->money(
                    $cashRefundTotal
                ),

            'inventory_value_reduction' =>
                round(
                    (float) $this->inventory_value_reduction,
                    4
                ),

            'inventory_value_reduction_formatted' =>
                $this->money(
                    $this->inventory_value_reduction
                ),

            'valuation_variance' =>
                round(
                    (float) $this->valuation_variance,
                    4
                ),

            'valuation_variance_formatted' =>
                $this->signedMoney(
                    $this->valuation_variance
                ),

            'supplier_financial_posted' =>
                $allocatedAmount > 0,

            'settlement_status' =>
                $settlementStatus,

            'settlement_status_label' =>
                $this->settlementStatusLabel(
                    $settlementStatus
                ),

            'cash_effect' =>
                $cashRefundTotal > 0,

            'linked_purchase_orders' =>
                $linkedPurchaseOrders,

            'accounting_note' =>
                $this->accountingNote(
                    settlementStatus:
                        $settlementStatus,

                    supplierCreditIssuedAmount:
                        $supplierCreditAmount,

                    supplierCreditBalance:
                        $supplierCreditBalance,

                    refundReceivableBalance:
                        $refundReceivableBalance
                ),

            'notes' =>
                $this->notes,

            'items' =>
                PurchaseReturnItemResource::collection(
                    $this->whenLoaded('items')
                ),

            'settlements' =>
                PurchaseReturnSettlementResource::collection(
                    $this->when(
                        $this->relationLoaded('settlements'),
                        $topLevelSettlements,
                        collect()
                    )
                ),

            'created_by' =>
                $this->whenLoaded(
                    'creator',
                    fn (): ?array =>
                        $this->creator
                            ? [
                                'id' =>
                                    (int) $this->creator->id,

                                'name' =>
                                    $this->creator->name,
                            ]
                            : null,
                    null
                ),

            'created_at' =>
                $this->created_at?->toISOString(),
        ];
    }


    private function resolveSettlementStatus(
        float $targetAmount,
        float $allocatedAmount,
        float $refundReceivableBalance
    ): string {

        if ($allocatedAmount <= 0) {
            return 'pending';
        }

        if (
            $allocatedAmount
            +
            0.005
            <
            $targetAmount
        ) {
            return 'partially_settled';
        }

        if ($refundReceivableBalance > 0.005) {
            return 'awaiting_refund';
        }

        return 'settled';
    }


    private function settlementStatusLabel(
        string $status
    ): string {

        return match ($status) {
            'pending' =>
                'Pending Settlement',

            'partially_settled' =>
                'Partially Settled',

            'awaiting_refund' =>
                'Awaiting Refund',

            'settled' =>
                'Settled',

            default =>
                'Unknown',
        };
    }


    private function accountingNote(
        string $settlementStatus,
        float $supplierCreditIssuedAmount,
        float $supplierCreditBalance,
        float $refundReceivableBalance
    ): string {

        if ($settlementStatus === 'pending') {
            return 'Physical Purchase Return is recorded, but supplier financial allocation is still pending.';
        }

        if ($refundReceivableBalance > 0) {
            return 'Supplier refund receivable remains outstanding until money is actually collected.';
        }

        if ($supplierCreditBalance > 0) {
            return 'Supplier credit has been recognized and remains available for another Purchase Order.';
        }

        if ($supplierCreditIssuedAmount > 0) {
            return 'Supplier credit from this Purchase Return has been fully consumed against Purchase Orders.';
        }

        return 'Purchase Return supplier settlement has been allocated.';
    }


    private function money(
        mixed $value
    ): string {

        return '৳ '
            .
            number_format(
                (float) $value,
                2
            );
    }


    private function signedMoney(
        mixed $value
    ): string {

        $amount =
            (float) $value;

        $prefix =
            $amount > 0
                ? '+'
                : '';

        return $prefix
            .
            '৳ '
            .
            number_format(
                $amount,
                2
            );
    }
}
