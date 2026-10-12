<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChartOfAccountResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        return [

            'id' =>
                (int) $this->id,

            'code' =>
                $this->code,

            'system_key' =>
                $this->system_key,

            'name' =>
                $this->name,

            'account_type' =>
                $this->account_type,

            'account_type_label' =>
                $this->accountTypeLabel(),

            'normal_balance' =>
                $this->normal_balance,

            'normal_balance_label' =>
                $this->normalBalanceLabel(),

            'parent_id' =>
                $this->parent_id !== null
                    ? (int) $this->parent_id
                    : null,

            'parent' =>
                $this->whenLoaded(
                    'parent',
                    function (): ?array {

                        if (! $this->parent) {
                            return null;
                        }

                        return [
                            'id' =>
                                (int) $this->parent->id,

                            'code' =>
                                $this->parent->code,

                            'name' =>
                                $this->parent->name,
                        ];
                    }
                ),

            'is_system' =>
                (bool) $this->is_system,

            'is_active' =>
                (bool) $this->is_active,

            'allow_manual_posting' =>
                (bool) $this->allow_manual_posting,

            'description' =>
                $this->description,

            'can_edit_structure' =>
                ! $this->is_system,

            'can_delete' =>
                ! $this->is_system
                &&
                ! $this->children()
                    ->exists(),

            'created_by_name' =>
                $this->creator?->name,

            'updated_by_name' =>
                $this->updater?->name,

            'created_at' =>
                $this->created_at
                    ?->toISOString(),

            'updated_at' =>
                $this->updated_at
                    ?->toISOString(),
        ];
    }
}
