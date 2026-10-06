<?php

namespace App\Http\Resources;

use App\Models\PurchaseReturnSettlement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class PurchaseReturnSettlementResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        $children =
            $this->relationLoaded('children')
                ? $this->children
                : collect();


        $collectedAmount =
            $this->settlement_type
            ===
            PurchaseReturnSettlement::TYPE_REFUND_RECEIVABLE

                ? round(
                    (float) $children
                        ->where(
                            'settlement_type',
                            PurchaseReturnSettlement::TYPE_RECEIVABLE_COLLECTION
                        )
                        ->sum('amount'),
                    2
                )

                : 0.0;


        $receivableBalance =
            $this->settlement_type
            ===
            PurchaseReturnSettlement::TYPE_REFUND_RECEIVABLE

                ? round(
                    max(
                        0,
                        (float) $this->amount
                        -
                        $collectedAmount
                    ),
                    2
                )

                : 0.0;


        $creditApplications =
            $this->relationLoaded(
                'creditApplications'
            )
                ? $this->creditApplications
                : collect();


        $creditAppliedAmount =
            $this->settlement_type
            ===
            PurchaseReturnSettlement::TYPE_SUPPLIER_CREDIT

                ? round(
                    (float) $creditApplications
                        ->sum('amount'),
                    2
                )

                : 0.0;


        $creditBalance =
            $this->settlement_type
            ===
            PurchaseReturnSettlement::TYPE_SUPPLIER_CREDIT

                ? round(
                    max(
                        0,
                        (float) $this->amount
                        -
                        $creditAppliedAmount
                    ),
                    2
                )

                : 0.0;


        return [
            'id' =>
                (int) $this->id,

            'settlement_no' =>
                $this->settlement_no,

            'purchase_return_id' =>
                (int) $this->purchase_return_id,

            'supplier_id' =>
                (int) $this->supplier_id,

            'settlement_type' =>
                $this->settlement_type,

            'settlement_type_label' =>
                PurchaseReturnSettlement::typeLabel(
                    $this->settlement_type
                ),

            'amount' =>
                round(
                    (float) $this->amount,
                    2
                ),

            'amount_formatted' =>
                $this->money(
                    $this->amount
                ),

            'purchase_order_id' =>
                $this->purchase_order_id !== null
                    ? (int) $this->purchase_order_id
                    : null,

            'purchase_order' =>
                $this->whenLoaded(
                    'purchaseOrder',
                    function (): ?array {

                        if (!$this->purchaseOrder) {
                            return null;
                        }


                        $po =
                            $this->purchaseOrder;


                        return [
                            'id' =>
                                (int) $po->id,

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

                            'supplier_credit_applied' =>
                                round(
                                    (float) (
                                        $po->supplier_credit_applied
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
                        ];
                    },
                    null
                ),

            'parent_settlement_id' =>
                $this->parent_settlement_id !== null
                    ? (int) $this->parent_settlement_id
                    : null,

            'payment_method' =>
                $this->payment_method,

            'payment_method_label' =>
                $this->paymentMethodLabel(
                    $this->payment_method
                ),

            'transaction_reference' =>
                $this->transaction_reference,

            'settled_at' =>
                $this->settled_at
                    ?->toISOString(),

            'settled_at_label' =>
                $this->settled_at
                    ?->format(
                        'd M Y, h:i A'
                    ),

            'allocation_effect' =>
                (bool) $this->allocation_effect,

            'cash_effect' =>
                (bool) $this->cash_effect,

            'collected_amount' =>
                $collectedAmount,

            'collected_amount_formatted' =>
                $this->money(
                    $collectedAmount
                ),

            'receivable_balance' =>
                $receivableBalance,

            'receivable_balance_formatted' =>
                $this->money(
                    $receivableBalance
                ),

            'credit_applied_amount' =>
                $creditAppliedAmount,

            'credit_applied_amount_formatted' =>
                $this->money(
                    $creditAppliedAmount
                ),

            'credit_balance' =>
                $creditBalance,

            'credit_balance_formatted' =>
                $this->money(
                    $creditBalance
                ),

            'credit_applications' =>
                SupplierCreditApplicationResource::collection(
                    $this->whenLoaded(
                        'creditApplications'
                    )
                ),

            'notes' =>
                $this->notes,

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

            'children' =>
                self::collection(
                    $this->whenLoaded(
                        'children'
                    )
                ),

            'created_at' =>
                $this->created_at
                    ?->toISOString(),
        ];
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


    private function paymentMethodLabel(
        ?string $method
    ): ?string {

        if (!$method) {
            return null;
        }


        return match ($method) {
            'cash' =>
                'Cash',

            'bank_transfer' =>
                'Bank Transfer',

            'bkash' =>
                'bKash',

            'nagad' =>
                'Nagad',

            'card' =>
                'Card',

            'other' =>
                'Other',

            default =>
                ucwords(
                    str_replace(
                        '_',
                        ' ',
                        $method
                    )
                ),
        };
    }
}
