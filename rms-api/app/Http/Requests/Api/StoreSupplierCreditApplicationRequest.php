<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreSupplierCreditApplicationRequest extends FormRequest
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
                'notes',
            ]
            as
            $field
        ) {

            if (!$this->exists($field)) {
                continue;
            }


            $value =
                trim(
                    (string) (
                        $this->input($field)
                        ??
                        ''
                    )
                );


            $data[$field] =
                $value !== ''
                    ? $value
                    : null;
        }


        if ($data !== []) {
            $this->merge($data);
        }
    }


    public function rules(): array
    {
        return [
            'source_settlement_id' => [
                'required',
                'integer',
                Rule::exists(
                    'purchase_return_settlements',
                    'id'
                ),
            ],

            'purchase_order_id' => [
                'required',
                'integer',
                Rule::exists(
                    'purchase_orders',
                    'id'
                ),
            ],

            'amount' => [
                'required',
                'numeric',
                'decimal:0,2',
                'gt:0',
                'max:999999999999.99',
            ],

            'applied_at' => [
                'nullable',
                'date',
                'before_or_equal:now',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
