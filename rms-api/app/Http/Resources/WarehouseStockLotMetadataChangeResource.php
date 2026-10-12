<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class WarehouseStockLotMetadataChangeResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        return [

            'id' =>
                (int) $this->id,

            'warehouse_stock_lot_id' =>
                (int) $this->warehouse_stock_lot_id,

            'changed_fields' =>
                array_values(
                    $this->changed_fields
                    ??
                    []
                ),

            'before_snapshot' =>
                $this->before_snapshot
                ??
                [],

            'after_snapshot' =>
                $this->after_snapshot
                ??
                [],

            'change_reason' =>
                $this->change_reason,

            'changed_by' =>
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

            'created_at_label' =>
                $this->created_at
                    ?->format(
                        'd M Y, h:i A'
                    ),

        ];
    }
}
