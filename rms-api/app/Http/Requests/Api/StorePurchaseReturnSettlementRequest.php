<?php

namespace App\Http\Requests\Api;

use App\Models\PurchaseReturnSettlement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StorePurchaseReturnSettlementRequest extends FormRequest
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
                'settlement_type',
                'payment_method',
                'transaction_reference',
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


            if (
                in_array(
                    $field,
                    [
                        'settlement_type',
                        'payment_method',
                    ],
                    true
                )
            ) {
                $value = strtolower($value);
            }


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
            'settlement_type' => [
                'required',
                'string',
                Rule::in(
                    PurchaseReturnSettlement::allocationTypes()
                ),
            ],

            'amount' => [
                'required',
                'numeric',
                'decimal:0,2',
                'gt:0',
                'max:999999999999.99',
            ],

            'purchase_order_id' => [
                'nullable',
                'integer',
                Rule::exists(
                    'purchase_orders',
                    'id'
                ),
            ],

            'payment_method' => [
                'nullable',
                'string',
                Rule::in(
                    PurchaseReturnSettlement::refundMethods()
                ),
            ],

            'settled_at' => [
                'nullable',
                'date',
                'before_or_equal:now',
            ],

            'transaction_reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
