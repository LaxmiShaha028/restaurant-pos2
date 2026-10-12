<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name =
            trim(
                (string) $this->input(
                    'name',
                    ''
                )
            );

        $description =
            trim(
                (string) $this->input(
                    'description',
                    ''
                )
            );

        $this->merge([
            'name' =>
                $name,

            'description' =>
                $description !== ''
                    ? $description
                    : null,
        ]);
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

    public function messages(): array
    {
        return [

            'name.required' =>
                'Category name is required.',

            'name.max' =>
                'Category name may not be greater than 150 characters.',

            'description.max' =>
                'Category description may not be greater than 2000 characters.',

        ];
    }
}
