<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncomeResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        return [

            'id' =>
                (int) $this->id,

            'income_category_id' =>
                (int) $this->income_category_id,

            /*
            |--------------------------------------------------------------------------
            | Historical Category
            |--------------------------------------------------------------------------
            */

            'category_name' =>
                $this->category_name_snapshot
                ??
                $this->category?->name
                ??
                'Uncategorized',

            'current_category_name' =>
                $this->category?->name,

            /*
            |--------------------------------------------------------------------------
            | Date / Amount
            |--------------------------------------------------------------------------
            */

            'income_date' =>
                $this->income_date
                    ?->format('Y-m-d'),

            'income_date_label' =>
                $this->income_date
                    ?->format('d M Y'),

            'day_label' =>
                $this->income_date
                    ?->format('l'),

            'amount' =>
                (float) $this->amount,

            'amount_formatted' =>
                $this->money(
                    $this->amount
                ),

            /*
            |--------------------------------------------------------------------------
            | Payment
            |--------------------------------------------------------------------------
            */

            'payment_method' =>
                $this->payment_method,

            'payment_method_label' =>
                $this->paymentMethodLabel(),

            'reference' =>
                $this->reference,

            'notes' =>
                $this->notes,

            /*
            |--------------------------------------------------------------------------
            | Source / Future Ledger
            |--------------------------------------------------------------------------
            */

            'source_type' =>
                $this->source_type,

            'source_id' =>
                $this->source_id !== null
                    ? (int) $this->source_id
                    : null,

            'is_system_generated' =>
                $this->isSystemGenerated(),

            'can_edit' =>
                ! $this->isSystemGenerated(),

            'can_delete' =>
                ! $this->isSystemGenerated(),

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            'created_by_name' =>
                $this->creator?->name
                ??
                'System',

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


    private function paymentMethodLabel(): string
    {
        return match (
            $this->payment_method
        ) {

            'cash' =>
                'Cash',

            'card' =>
                'Card',

            'bkash' =>
                'bKash',

            'nagad' =>
                'Nagad',

            'bank_transfer' =>
                'Bank Transfer',

            'mixed' =>
                'Mixed Payment',

            'other' =>
                'Other',

            default =>
                ucwords(
                    str_replace(
                        '_',
                        ' ',
                        (string) $this->payment_method
                    )
                ),
        };
    }
}
