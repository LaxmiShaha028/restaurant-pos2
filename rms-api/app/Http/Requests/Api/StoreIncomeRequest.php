<?php

namespace App\Http\Requests\Api;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->hasIncomeAccess();
    }


    public function rules(): array
    {
        return [

            'income_category_id' => [
                'required',
                'integer',

                Rule::exists(
                    'income_categories',
                    'id'
                )
                    ->where(
                        'is_active',
                        true
                    )
                    ->whereNull(
                        'deleted_at'
                    ),
            ],

            'income_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'max:999999999999.99',
            ],

            'payment_method' => [
                'required',

                Rule::in(
                    Expense::allowedPaymentMethods()
                ),
            ],

            'reference' => [
                'nullable',
                'string',
                'max:150',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

        ];
    }


    protected function prepareForValidation(): void
    {
        $this->merge([

            'income_category_id' =>
                $this->income_category_id !== null
                    ? (int) $this->income_category_id
                    : null,

            'reference' =>
                is_string(
                    $this->reference
                )
                    ? trim(
                        $this->reference
                    )
                    : $this->reference,

            'notes' =>
                is_string(
                    $this->notes
                )
                    ? trim(
                        $this->notes
                    )
                    : $this->notes,

        ]);
    }


    private function hasIncomeAccess(): bool
    {
        $user =
            $this->user();

        $user?->loadMissing(
            'role'
        );

        $roleName =
            strtolower(
                trim(
                    (string) (
                        $user
                            ?->role
                            ?->name
                        ??
                        ''
                    )
                )
            );

        return (
            $user !== null
            &&
            in_array(
                $roleName,
                [
                    'admin',
                    'manager',
                ],
                true
            )
        );
    }
}
