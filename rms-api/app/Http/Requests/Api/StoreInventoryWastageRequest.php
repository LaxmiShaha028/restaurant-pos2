<?php

namespace App\Http\Requests\Api;

use App\Models\InventoryWastageItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreInventoryWastageRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

    public function authorize(): bool
    {
        return $this->user()?->hasPermission(
            'inventory.manage'
        ) ?? false;
    }


    /*
    |--------------------------------------------------------------------------
    | Prepare Input
    |--------------------------------------------------------------------------
    */

    protected function prepareForValidation(): void
    {
        $data = [];


        if (
            $this->exists(
                'reason'
            )
        ) {

            $reason =
                strtolower(
                    trim(
                        (string) $this->input(
                            'reason'
                        )
                    )
                );


            $data['reason'] =
                $reason !== ''
                    ? $reason
                    : null;
        }


        if (
            $this->exists(
                'wasted_at'
            )
        ) {

            $wastedAt =
                trim(
                    (string) $this->input(
                        'wasted_at'
                    )
                );


            $data['wasted_at'] =
                $wastedAt !== ''
                    ? $wastedAt
                    : null;
        }


        if (
            $this->exists(
                'notes'
            )
        ) {

            $notes =
                trim(
                    (string) $this->input(
                        'notes'
                    )
                );


            $data['notes'] =
                $notes !== ''
                    ? $notes
                    : null;
        }


        if ($data !== []) {

            $this->merge(
                $data
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Rules
    |--------------------------------------------------------------------------
    */

    public function rules(): array
    {
        return [

            'raw_material_id' => [
                'bail',
                'required',
                'integer',

                /*
                |--------------------------------------------------------------------------
                | Historical / Cleanup Friendly
                |--------------------------------------------------------------------------
                |
                | Do not require is_active=true here. Existing physical restaurant
                | stock may still need to be written off even if a material has
                | later been deactivated.
                |
                */

                Rule::exists(
                    'raw_materials',
                    'id'
                ),
            ],


            'quantity' => [
                'bail',
                'required',
                'numeric',
                'decimal:0,4',
                'gt:0',
                'max:99999999999999.9999',
            ],


            'reason' => [
                'bail',
                'required',
                'string',

                Rule::in(
                    InventoryWastageItem::allowedReasons()
                ),
            ],


            'wasted_at' => [
                'bail',
                'nullable',
                'date',
            ],


            'notes' => [
                'bail',
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */

    public function messages(): array
    {
        return [

            'raw_material_id.required' =>
                'Please select a raw material.',

            'raw_material_id.exists' =>
                'The selected raw material was not found.',


            'quantity.required' =>
                'Damage / wastage quantity is required.',

            'quantity.numeric' =>
                'Damage / wastage quantity must be numeric.',

            'quantity.decimal' =>
                'Damage / wastage quantity may contain up to 4 decimal places.',

            'quantity.gt' =>
                'Damage / wastage quantity must be greater than zero.',


            'reason.required' =>
                'Please select a damage / wastage reason.',

            'reason.in' =>
                'Please select a valid damage / wastage reason.',


            'wasted_at.date' =>
                'Damage / wastage date must be valid.',


            'notes.max' =>
                'Notes cannot exceed 2000 characters.',
        ];
    }
}
