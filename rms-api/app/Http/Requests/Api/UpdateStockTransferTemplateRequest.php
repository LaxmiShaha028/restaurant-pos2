<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;


class UpdateStockTransferTemplateRequest extends StoreStockTransferTemplateRequest
{
    public function rules(): array
    {
        $templateId =
            (int) $this->route(
                'template'
            );


        $rules =
            parent::rules();


        $rules['name'] = [

            'bail',

            'required',

            'string',

            'max:120',

            Rule::unique(
                'stock_transfer_templates',
                'name'
            )
                ->ignore(
                    $templateId
                )
                ->whereNull(
                    'deleted_at'
                ),
        ];


        return $rules;
    }
}
