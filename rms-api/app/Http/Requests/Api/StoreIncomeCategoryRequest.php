<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncomeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->hasIncomeAccess();
    }


    public function rules(): array
    {
        return [

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

        ];
    }


    protected function prepareForValidation(): void
    {
        $this->merge([

            'name' =>
                is_string(
                    $this->name
                )
                    ? trim(
                        $this->name
                    )
                    : $this->name,

            'description' =>
                is_string(
                    $this->description
                )
                    ? trim(
                        $this->description
                    )
                    : $this->description,

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
