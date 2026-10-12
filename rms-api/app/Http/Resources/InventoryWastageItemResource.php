<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class InventoryWastageItemResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        $quantity =
            round(
                (float) $this->quantity,
                4
            );


        $unitCost =
            round(
                (float) $this->unit_cost,
                4
            );


        $totalLoss =
            round(
                (float) $this->total_loss,
                4
            );


        return [

            'id' =>
                (int) $this->id,


            'inventory_wastage_id' =>
                (int) $this->inventory_wastage_id,


            'raw_material_id' =>
                (int) $this->raw_material_id,


            'raw_material' =>
                $this->whenLoaded(

                    'rawMaterial',

                    function (): ?array {

                        if (
                            !$this->rawMaterial
                        ) {
                            return null;
                        }


                        return [

                            'id' =>
                                (int) $this->rawMaterial->id,

                            'material_name' =>
                                $this->rawMaterial->material_name,

                            'category' =>
                                $this->rawMaterial->category,

                            'base_unit' =>
                                $this->rawMaterial->base_unit,

                        ];
                    },

                    null
                ),


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


            'unit_cost' =>
                $unitCost,


            'unit_cost_formatted' =>
                $this->formatMoney(
                    $unitCost,
                    4
                ),


            'total_loss' =>
                $totalLoss,


            'total_loss_formatted' =>
                $this->formatMoney(
                    $totalLoss,
                    2
                ),


            'restaurant_quantity_before' =>
                round(
                    (float) $this
                        ->restaurant_quantity_before,
                    4
                ),


            'restaurant_quantity_after' =>
                round(
                    (float) $this
                        ->restaurant_quantity_after,
                    4
                ),


            'reason' =>
                $this->reason,


            'reason_label' =>
                $this->reason_label,


            'notes' =>
                $this->notes,


            'created_at' =>
                $this->created_at
                    ?->toISOString(),

        ];
    }


    private function formatQuantity(
        float $value,
        ?string $unit
    ): string {

        $formatted =
            number_format(
                $value,
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
        float $value,
        int $maximumDecimals
    ): string {

        $formatted =
            number_format(
                $value,
                $maximumDecimals,
                '.',
                ''
            );


        if (
            $maximumDecimals > 2
        ) {

            $parts =
                explode(
                    '.',
                    $formatted,
                    2
                );


            if (
                count(
                    $parts
                )
                ===
                2
            ) {

                $decimalPart =
                    rtrim(
                        $parts[1],
                        '0'
                    );


                if (
                    strlen(
                        $decimalPart
                    )
                    <
                    2
                ) {

                    $decimalPart =
                        str_pad(
                            $decimalPart,
                            2,
                            '0'
                        );
                }


                $formatted =
                    "{$parts[0]}.{$decimalPart}";
            }
        }


        return "৳ {$formatted}";
    }
}
