<?php

namespace App\Http\Requests\Api;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->hasIncomeAccess();
    }


    public function rules(): array
    {
        return [

            'income_category_id' => [
                'sometimes',
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
                'sometimes',
                'required',
                'date',
                'before_or_equal:today',
            ],

            'amount' => [
                'sometimes',
                'required',
                'numeric',
                'gt:0',
                'max:999999999999.99',
            ],

            'payment_method' => [
                'sometimes',
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
        $data = [];


        if (
            $this->has(
                'income_category_id'
            )
        ) {
            $data[
                'income_category_id'
            ] =
                $this->income_category_id !== null
                    ? (int) $this->income_category_id
                    : null;
        }


        if (
            $this->has(
                'reference'
            )
        ) {
            $data[
                'reference'
            ] =
                is_string(
                    $this->reference
                )
                    ? trim(
                        $this->reference
                    )
                    : $this->reference;
        }


        if (
            $this->has(
                'notes'
            )
        ) {
            $data[
                'notes'
            ] =
                is_string(
                    $this->notes
                )
                    ? trim(
                        $this->notes
                    )
                    : $this->notes;
        }


        if ($data !== []) {
            $this->merge(
                $data
            );
        }
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
