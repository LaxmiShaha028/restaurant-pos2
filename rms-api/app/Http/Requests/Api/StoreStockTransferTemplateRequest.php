<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreStockTransferTemplateRequest extends FormRequest
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


        if (
            $this->exists(
                'name'
            )
        ) {

            $data['name'] =
                trim(
                    (string) $this->input(
                        'name'
                    )
                );
        }


        if (
            $this->exists(
                'description'
            )
        ) {

            $description =
                trim(
                    (string) (
                        $this->input(
                            'description'
                        )
                        ??
                        ''
                    )
                );


            $data['description'] =
                $description !== ''
                    ? $description
                    : null;
        }


        $items =
            $this->input(
                'items'
            );


        if (
            is_array(
                $items
            )
        ) {

            $data['items'] =
                collect(
                    $items
                )
                    ->map(
                        static function (
                            mixed $item
                        ): mixed {

                            if (
                                !is_array(
                                    $item
                                )
                            ) {
                                return $item;
                            }


                            if (
                                array_key_exists(
                                    'notes',
                                    $item
                                )
                            ) {

                                $notes =
                                    trim(
                                        (string) (
                                            $item[
                                                'notes'
                                            ]
                                            ??
                                            ''
                                        )
                                    );


                                $item['notes'] =
                                    $notes !== ''
                                        ? $notes
                                        : null;
                            }


                            return $item;
                        }
                    )
                    ->values()
                    ->all();
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

            'name' => [
                'bail',
                'required',
                'string',
                'max:120',

                Rule::unique(
                    'stock_transfer_templates',
                    'name'
                )
                    ->whereNull(
                        'deleted_at'
                    ),
            ],


            'description' => [
                'bail',
                'nullable',
                'string',
                'max:500',
            ],


            'is_active' => [
                'sometimes',
                'boolean',
            ],


            'items' => [
                'bail',
                'required',
                'array',
                'min:1',
                'max:200',
            ],


            'items.*.raw_material_id' => [
                'bail',
                'required',
                'integer',
                'distinct',

                Rule::exists(
                    'raw_materials',
                    'id'
                )
                    ->where(
                        static function (
                            $query
                        ): void {

                            $query
                                ->whereNull(
                                    'deleted_at'
                                )
                                ->where(
                                    'is_active',
                                    true
                                );
                        }
                    ),
            ],


            'items.*.quantity' => [
                'bail',
                'required',
                'numeric',
                'decimal:0,4',
                'gt:0',
                'max:9999999999.9999',
            ],


            'items.*.notes' => [
                'bail',
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }


    public function messages(): array
    {
        return [

            'name.required' =>
                'Template name is required.',

            'name.unique' =>
                'A transfer template with this name already exists.',


            'items.required' =>
                'Add at least one raw material to the transfer template.',

            'items.min' =>
                'Add at least one raw material to the transfer template.',

            'items.max' =>
                'A transfer template can contain a maximum of 200 raw materials.',


            'items.*.raw_material_id.required' =>
                'Select a raw material for every template item.',

            'items.*.raw_material_id.distinct' =>
                'The same raw material cannot appear more than once in a template.',

            'items.*.raw_material_id.exists' =>
                'The selected raw material was not found, is inactive, or has been deleted.',


            'items.*.quantity.required' =>
                'Enter a default transfer quantity for every item.',

            'items.*.quantity.gt' =>
                'Default transfer quantity must be greater than zero.',

            'items.*.quantity.decimal' =>
                'Default transfer quantity may contain up to 4 decimal places.',
        ];
    }
}
