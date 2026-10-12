<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class WarehouseStockLotResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        $rawMaterial =
            $this->relationLoaded(
                'rawMaterial'
            )
                ? $this->rawMaterial
                : null;


        $supplier =
            $this->relationLoaded(
                'supplier'
            )
                ? $this->supplier
                : null;


        $purchaseOrder =
            $this->relationLoaded(
                'purchaseOrder'
            )
                ? $this->purchaseOrder
                : null;


        $receipt =
            $this->relationLoaded(
                'purchaseOrderReceipt'
            )
                ? $this->purchaseOrderReceipt
                : null;


        $receivedQuantity =
            round(
                (float) $this->received_quantity,
                4
            );


        $remainingQuantity =
            round(
                (float) $this->remaining_quantity,
                4
            );


        $unitCost =
            round(
                (float) $this->unit_cost,
                4
            );


        $remainingValue =
            round(
                $remainingQuantity
                *
                $unitCost,
                4
            );


        return [

            'id' =>
                (int) $this->id,

            'lot_no' =>
                $this->lot_no,

            'source_type' =>
                $this->source_type,

            'source_type_label' =>
                $this->sourceTypeLabel(),

            'raw_material_id' =>
                (int) $this->raw_material_id,

            'raw_material' =>
                $rawMaterial
                    ? [
                        'id' =>
                            (int) $rawMaterial->id,

                        'material_name' =>
                            $rawMaterial->material_name,

                        'category' =>
                            $rawMaterial->category,

                        'base_unit' =>
                            $rawMaterial->base_unit,

                        'is_active' =>
                            (bool) $rawMaterial->is_active,
                    ]
                    : null,

            'material_name' =>
                $rawMaterial
                    ?->material_name
                ??
                'Unknown Material',

            'category' =>
                $rawMaterial
                    ?->category,

            'unit' =>
                $this->unit,

            'supplier_id' =>
                $this->supplier_id
                    !==
                    null
                        ? (int) $this->supplier_id
                        : null,

            'supplier' =>
                $supplier
                    ? [
                        'id' =>
                            (int) $supplier->id,

                        'supplier_name' =>
                            $supplier->supplier_name,

                        'name' =>
                            $supplier->supplier_name,
                    ]
                    : null,

            'supplier_name' =>
                $supplier
                    ?->supplier_name,

            'purchase_order_id' =>
                $this->purchase_order_id
                    !==
                    null
                        ? (int) $this->purchase_order_id
                        : null,

            'purchase_order' =>
                $purchaseOrder
                    ? [
                        'id' =>
                            (int) $purchaseOrder->id,

                        /*
                        |--------------------------------------------------------------------------
                        | Human label fallback
                        |--------------------------------------------------------------------------
                        |
                        | The current RMS PurchaseOrder model does not expose a
                        | guaranteed PO-number property in the uploaded resource.
                        | Keep ID authoritative and use any known number if present.
                        |
                        */

                        'number' =>
                            $purchaseOrder->purchase_order_no
                            ??
                            $purchaseOrder->po_number
                            ??
                            $purchaseOrder->order_no
                            ??
                            null,

                        'label' =>
                            (
                                $purchaseOrder->purchase_order_no
                                ??
                                $purchaseOrder->po_number
                                ??
                                $purchaseOrder->order_no
                            )
                                ? (
                                    $purchaseOrder->purchase_order_no
                                    ??
                                    $purchaseOrder->po_number
                                    ??
                                    $purchaseOrder->order_no
                                )
                                : "PO #{$purchaseOrder->id}",
                    ]
                    : null,

            'purchase_order_receipt_id' =>
                $this->purchase_order_receipt_id
                    !==
                    null
                        ? (int) $this->purchase_order_receipt_id
                        : null,

            'purchase_order_receipt' =>
                $receipt
                    ? [
                        'id' =>
                            (int) $receipt->id,

                        'receipt_no' =>
                            $receipt->receipt_no,

                        'label' =>
                            $receipt->receipt_no
                            ??
                            "GRN #{$receipt->id}",
                    ]
                    : null,

            'purchase_order_receipt_item_id' =>
                $this->purchase_order_receipt_item_id
                    !==
                    null
                        ? (int) $this->purchase_order_receipt_item_id
                        : null,

            'supplier_batch_no' =>
                $this->supplier_batch_no,

            'manufactured_at' =>
                $this->manufactured_at
                    ?->format(
                        'Y-m-d'
                    ),

            'manufactured_at_label' =>
                $this->manufactured_at
                    ?->format(
                        'd M Y'
                    ),

            'expiry_date' =>
                $this->expiry_date
                    ?->format(
                        'Y-m-d'
                    ),

            'expiry_date_label' =>
                $this->expiry_date
                    ?->format(
                        'd M Y'
                    ),

            'days_until_expiry' =>
                $this->days_until_expiry,

            'status' =>
                $this->status,

            'status_label' =>
                $this->status_label,

            'received_quantity' =>
                $receivedQuantity,

            'remaining_quantity' =>
                $remainingQuantity,

            'used_quantity' =>
                round(
                    max(
                        0,
                        $receivedQuantity
                        -
                        $remainingQuantity
                    ),
                    4
                ),

            'quantity_formatted' =>
                $this->formatQuantity(
                    $remainingQuantity,
                    $this->unit
                ),

            'received_quantity_formatted' =>
                $this->formatQuantity(
                    $receivedQuantity,
                    $this->unit
                ),

            'unit_cost' =>
                $unitCost,

            'unit_cost_formatted' =>
                $this->formatMoney(
                    $unitCost
                ),

            'remaining_value' =>
                $remainingValue,

            'remaining_value_formatted' =>
                $this->formatMoney(
                    $remainingValue
                ),

            'received_at' =>
                $this->received_at
                    ?->toISOString(),

            'received_at_label' =>
                $this->received_at
                    ?->format(
                        'd M Y, h:i A'
                    ),

            /*
            |--------------------------------------------------------------------------
            | Notes
            |--------------------------------------------------------------------------
            |
            | notes        = system/source audit text
            | manual_notes = user-maintained lot metadata note
            |
            */

            'notes' =>
                $this->notes,

            'manual_notes' =>
                $this->manual_notes,

            'can_edit_supplier' =>
                $this->source_type
                !==
                'purchase_receive',

            'return_eligible' =>
                $this->supplier_id !==
                    null
                &&
                $remainingQuantity
                >
                0,

            'return_eligibility_message' =>
                $remainingQuantity
                <=
                0
                    ? 'This lot has no remaining warehouse quantity.'
                    : (
                        $this->supplier_id ===
                        null
                            ? 'Assign a supplier before returning this lot.'
                            : 'Eligible for Purchase Return.'
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

            'updated_by' =>
                $this->whenLoaded(
                    'updater',
                    fn (): ?array =>
                        $this->updater
                            ? [
                                'id' =>
                                    (int) $this->updater->id,

                                'name' =>
                                    $this->updater->name,
                            ]
                            : null,
                    null
                ),

            'movements' =>
                WarehouseStockLotMovementResource::collection(
                    $this->whenLoaded(
                        'movements'
                    )
                ),

            'created_at' =>
                $this->created_at
                    ?->toISOString(),

            'updated_at' =>
                $this->updated_at
                    ?->toISOString(),

        ];
    }


    private function sourceTypeLabel(): string
    {
        return match (
            $this->source_type
        ) {

            'legacy_opening' =>
                'Legacy Opening',

            'purchase_receive' =>
                'Purchase Receive',

            'warehouse_adjustment_in' =>
                'Warehouse Adjustment In',

            default =>
                ucwords(
                    str_replace(
                        '_',
                        ' ',
                        (string) $this->source_type
                    )
                ),

        };
    }


    private function formatQuantity(
        float $quantity,
        ?string $unit
    ): string {

        $formatted =
            number_format(
                $quantity,
                4,
                '.',
                ''
            );


        $formatted =
            rtrim(
                rtrim(
                    $formatted,
                    '0'
                ),
                '.'
            );


        return trim(
            "{$formatted} {$unit}"
        );
    }


    private function formatMoney(
        float $amount
    ): string {

        return '৳ '
            .
            number_format(
                $amount,
                2
            );
    }
}
