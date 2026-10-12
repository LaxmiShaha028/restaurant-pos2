<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class WarehouseStockLotMovementResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        return [

            'id' =>
                (int) $this->id,

            'warehouse_stock_lot_id' =>
                (int) $this->warehouse_stock_lot_id,

            'raw_material_id' =>
                (int) $this->raw_material_id,

            'movement_type' =>
                $this->movement_type,

            'movement_type_label' =>
                $this->movementTypeLabel(),

            'direction' =>
                $this->direction,

            'quantity' =>
                round(
                    (float) $this->quantity,
                    4
                ),

            'quantity_before' =>
                round(
                    (float) $this->quantity_before,
                    4
                ),

            'quantity_after' =>
                round(
                    (float) $this->quantity_after,
                    4
                ),

            'unit_cost' =>
                round(
                    (float) $this->unit_cost,
                    4
                ),

            'unit' =>
                $this->unit,

            'reference_type' =>
                $this->reference_type,

            'reference_id' =>
                $this->reference_id
                    !==
                    null
                        ? (int) $this->reference_id
                        : null,

            'occurred_at' =>
                $this->occurred_at
                    ?->toISOString(),

            'occurred_at_label' =>
                $this->occurred_at
                    ?->format(
                        'd M Y, h:i A'
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

            'created_at' =>
                $this->created_at
                    ?->toISOString(),

        ];
    }


    private function movementTypeLabel(): string
    {
        return match (
            $this->movement_type
        ) {

            'legacy_opening' =>
                'Legacy Opening',

            'purchase_receive' =>
                'Purchase Receive',

            'warehouse_adjustment_in' =>
                'Warehouse Adjustment In',

            'warehouse_adjustment_out' =>
                'Warehouse Adjustment Out',

            'transfer_out' =>
                'Restaurant Transfer Out',

            'purchase_return_out' =>
                'Purchase Return Out',

            default =>
                ucwords(
                    str_replace(
                        '_',
                        ' ',
                        (string) $this->movement_type
                    )
                ),

        };
    }
}
