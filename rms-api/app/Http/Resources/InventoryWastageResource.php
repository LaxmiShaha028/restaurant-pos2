<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class InventoryWastageResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        $totalLoss =
            round(
                (float) $this->total_loss,
                4
            );


        return [

            'id' =>
                (int) $this->id,


            'wastage_no' =>
                $this->wastage_no,


            'wasted_at' =>
                $this->wasted_at
                    ?->toISOString(),


            'wasted_at_label' =>
                $this->wasted_at
                    ?->format(
                        'd M Y, h:i A'
                    ),


            'items' =>
                $this->whenLoaded(

                    'items',

                    fn () =>
                        InventoryWastageItemResource::collection(
                            $this->items
                        ),

                    []
                ),


            'total_items' =>
                $this->relationLoaded(
                    'items'
                )
                    ? $this->items->count()
                    : 0,


            'total_loss' =>
                $totalLoss,


            'total_loss_formatted' =>
                '৳ '
                .
                number_format(
                    $totalLoss,
                    2
                ),


            /*
            |--------------------------------------------------------------------------
            | Future Accounting Metadata
            |--------------------------------------------------------------------------
            */

            'accounting_classification' =>
                'inventory_wastage_loss',


            'cash_effect' =>
                false,


            'notes' =>
                $this->notes,


            'recorded_by' =>
                $this->whenLoaded(

                    'recordedBy',

                    fn (): ?array =>
                        $this->userSummary(
                            $this->recordedBy
                        ),

                    null
                ),


            'created_by' =>
                $this->whenLoaded(

                    'creator',

                    fn (): ?array =>
                        $this->userSummary(
                            $this->creator
                        ),

                    null
                ),


            'created_at' =>
                $this->created_at
                    ?->toISOString(),

        ];
    }


    private function userSummary(
        mixed $user
    ): ?array {

        if (!$user) {
            return null;
        }


        return [

            'id' =>
                (int) $user->id,

            'name' =>
                $user->name,

        ];
    }
}
