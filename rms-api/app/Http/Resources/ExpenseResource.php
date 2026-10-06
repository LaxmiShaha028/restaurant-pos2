<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        return [

            'id' =>
                (int) $this->id,

            'expense_date' =>
                $this->expense_date
                    ?->toISOString(),

            'expense_date_label' =>
                $this->expense_date
                    ?->format(
                        'd M Y, h:i A'
                    ),

            'expense_day' =>
                $this->expense_date
                    ?->format(
                        'l'
                    ),

            'category' =>
                $this->whenLoaded(
                    'category',
                    function (): ?array {

                        if (! $this->category) {
                            return null;
                        }

                        return [
                            'id' =>
                                (int) $this
                                    ->category
                                    ->id,

                            'name' =>
                                $this
                                    ->category
                                    ->name,
                        ];
                    }
                ),

            'category_name' =>
                $this->category
                    ?->name
                ??
                'Uncategorized',

            'amount' =>
                (float) $this->amount,

            'amount_formatted' =>
                $this->money(
                    $this->amount
                ),

            'payment_method' =>
                $this->payment_method,

            'payment_method_label' =>
                $this->paymentMethodLabel(),

            'paid_by' =>
                $this->whenLoaded(
                    'creator',
                    function (): ?array {

                        if (! $this->creator) {
                            return null;
                        }

                        return [
                            'id' =>
                                (int) $this
                                    ->creator
                                    ->id,

                            'name' =>
                                $this
                                    ->creator
                                    ->name,

                            'email' =>
                                $this
                                    ->creator
                                    ->email,
                        ];
                    }
                ),

            'paid_by_name' =>
                $this->creator
                    ?->name
                ??
                'System',

            'notes' =>
                $this->notes,

            /*
            |--------------------------------------------------------------------------
            | System Source
            |--------------------------------------------------------------------------
            */

            'source_type' =>
                $this->source_type,

            'source_id' =>
                $this->source_id !== null
                    ? (int) $this->source_id
                    : null,

            'source_label' =>
                $this->sourceLabel(),

            'is_system_generated' =>
                $this->isSystemGenerated(),

            'can_edit' =>
                ! $this->isSystemGenerated(),

            'can_delete' =>
                ! $this->isSystemGenerated(),

            'created_at' =>
                $this->created_at
                    ?->toISOString(),

            'updated_at' =>
                $this->updated_at
                    ?->toISOString(),

            'updated_by_name' =>
                $this->updater
                    ?->name,
        ];
    }


    private function money(
        mixed $amount
    ): string {

        return '৳ '
            .
            number_format(
                (float) $amount,
                2
            );
    }
}
