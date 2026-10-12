<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class SupplierCreditApplicationResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        return [
            'id' =>
                (int) $this->id,

            'application_no' =>
                $this->application_no,

            'source_settlement_id' =>
                (int) $this->source_settlement_id,

            'supplier_id' =>
                (int) $this->supplier_id,

            'purchase_order_id' =>
                (int) $this->purchase_order_id,

            'amount' =>
                round(
                    (float) $this->amount,
                    2
                ),

            'amount_formatted' =>
                $this->money(
                    $this->amount
                ),

            'applied_at' =>
                $this->applied_at
                    ?->toISOString(),

            'applied_at_label' =>
                $this->applied_at
                    ?->format(
                        'd M Y, h:i A'
                    ),

            'notes' =>
                $this->notes,

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

                            'order_date' =>
                                $po->order_date
                                    ?->toISOString(),

                            'order_date_label' =>
                                $po->order_date
                                    ?->format(
                                        'd M Y, h:i A'
                                    ),

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

            'source_settlement' =>
                $this->whenLoaded(
                    'sourceSettlement',
                    function (): ?array {

                        if (!$this->sourceSettlement) {
                            return null;
                        }


                        $settlement =
                            $this->sourceSettlement;


                        return [
                            'id' =>
                                (int) $settlement->id,

                            'settlement_no' =>
                                $settlement->settlement_no,

                            'amount' =>
                                round(
                                    (float) $settlement->amount,
                                    2
                                ),

                            'purchase_return' =>
                                $settlement->relationLoaded(
                                    'purchaseReturn'
                                )
                                &&
                                $settlement->purchaseReturn

                                    ? [
                                        'id' =>
                                            (int) $settlement
                                                ->purchaseReturn
                                                ->id,

                                        'return_no' =>
                                            $settlement
                                                ->purchaseReturn
                                                ->return_no,
                                    ]

                                    : null,
                        ];
                    },
                    null
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
}
