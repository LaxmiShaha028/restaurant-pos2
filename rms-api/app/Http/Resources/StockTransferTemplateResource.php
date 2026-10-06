<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class StockTransferTemplateResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        return [

            'id' =>
                (int) $this->id,

            'name' =>
                $this->name,

            'description' =>
                $this->description,

            'is_active' =>
                (bool) $this->is_active,

            'items' =>
                StockTransferTemplateItemResource::collection(
                    $this->whenLoaded(
                        'items'
                    )
                ),

            'total_items' =>
                $this->relationLoaded(
                    'items'
                )
                    ? $this->items->count()
                    : 0,

            'created_by' =>
                $this->whenLoaded(
                    'creator',
                    fn (): ?array =>
                        $this->userSummary(
                            $this->creator
                        ),
                    null
                ),

            'updated_by' =>
                $this->whenLoaded(
                    'updater',
                    fn (): ?array =>
                        $this->userSummary(
                            $this->updater
                        ),
                    null
                ),

            'created_at' =>
                $this->created_at
                    ?->toISOString(),

            'updated_at' =>
                $this->updated_at
                    ?->toISOString(),

        ];
    }


    private function userSummary(
        mixed $user
    ): ?array {

        if (
            !$user
        ) {
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
