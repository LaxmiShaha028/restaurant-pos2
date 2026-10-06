<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class UpdateWarehouseStockLotMetadataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(
            'inventory.manage'
        ) ?? false;
    }


    protected function prepareForValidation(): void
    {
        $data = [];


        foreach (
            [
                'supplier_batch_no',
                'manual_notes',
                'change_reason',
            ]
            as
            $field
        ) {

            if (
                !$this->exists(
                    $field
                )
            ) {
                continue;
            }


            $value =
                trim(
                    (string) (
                        $this->input(
                            $field
                        )
                        ??
                        ''
                    )
                );


            $data[
                $field
            ] =
                $value !== ''
                    ? $value
                    : null;
        }


        if (
            $data !== []
        ) {
            $this->merge(
                $data
            );
        }
    }


    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Legacy / Adjustment Supplier Enrichment
            |--------------------------------------------------------------------------
            |
            | The service prevents changing supplier on purchase_receive lots.
            |
            */

            'supplier_id' => [
                'present',
                'nullable',
                'integer',

                Rule::exists(
                    'suppliers',
                    'id'
                ),
            ],


            'supplier_batch_no' => [
                'present',
                'nullable',
                'string',
                'max:120',
            ],


            'manufactured_at' => [
                'present',
                'nullable',
                'date_format:Y-m-d',
            ],


            'expiry_date' => [
                'present',
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:manufactured_at',
            ],


            /*
            |--------------------------------------------------------------------------
            | User Notes
            |--------------------------------------------------------------------------
            |
            | Does not overwrite system/source notes.
            |
            */

            'manual_notes' => [
                'present',
                'nullable',
                'string',
                'max:2000',
            ],


            /*
            |--------------------------------------------------------------------------
            | Mandatory Audit Reason
            |--------------------------------------------------------------------------
            */

            'change_reason' => [
                'required',
                'string',
                'max:1000',
            ],

        ];
    }


    public function messages(): array
    {
        return [

            'supplier_id.exists' =>
                'The selected supplier was not found.',

            'supplier_batch_no.max' =>
                'Supplier batch number cannot exceed 120 characters.',

            'manufactured_at.date_format' =>
                'Manufacturing date must use YYYY-MM-DD format.',

            'expiry_date.date_format' =>
                'Expiry date must use YYYY-MM-DD format.',

            'expiry_date.after_or_equal' =>
                'Expiry date cannot be earlier than manufacturing date.',

            'manual_notes.max' =>
                'Manual notes cannot exceed 2000 characters.',

            'change_reason.required' =>
                'Please explain why this lot information is being changed.',

            'change_reason.max' =>
                'Change reason cannot exceed 1000 characters.',

        ];
    }
}
