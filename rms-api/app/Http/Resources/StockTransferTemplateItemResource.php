<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class StockTransferTemplateItemResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        $quantity =
            round(
                (float) $this->quantity,
                4
            );


        $rawMaterial =
            $this->relationLoaded(
                'rawMaterial'
            )
                ? $this->rawMaterial
                : null;


        $materialAvailable =
            $rawMaterial
            &&
            !$rawMaterial->trashed()
            &&
            (bool) $rawMaterial->is_active;


        return [

            'id' =>
                (int) $this->id,

            'raw_material_id' =>
                (int) $this->raw_material_id,

            'item_name' =>
                $this->item_name,

            'unit' =>
                $this->unit,

            'quantity' =>
                $quantity,

            'quantity_formatted' =>
                $this->formatQuantity(
                    $quantity,
                    $this->unit
                ),

            'notes' =>
                $this->notes,

            'sort_order' =>
                (int) $this->sort_order,

            'material_available' =>
                (bool) $materialAvailable,

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

                        'deleted_at' =>
                            $rawMaterial->deleted_at
                                ?->toISOString(),
                    ]
                    : null,

        ];
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
}
