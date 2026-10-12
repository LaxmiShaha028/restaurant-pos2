<?php

namespace App\Http\Requests\Api;

use App\Models\PurchaseReturnSettlement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StorePurchaseReturnRefundCollectionRequest extends FormRequest
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


            if ($field === 'payment_method') {
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
            'receivable_settlement_id' => [
                'required',
                'integer',
                Rule::exists(
                    'purchase_return_settlements',
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

            'payment_method' => [
                'required',
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
