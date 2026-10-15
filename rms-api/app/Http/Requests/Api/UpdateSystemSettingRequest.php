<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSystemSettingRequest
    extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Restaurant Name
            |--------------------------------------------------------------------------
            */

            'restaurant_name' => [
                'nullable',
                'string',
                'max:190',
            ],

            /*
            |--------------------------------------------------------------------------
            | Restaurant Logo
            |--------------------------------------------------------------------------
            */

            'restaurant_logo' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            /*
            |--------------------------------------------------------------------------
            | Country
            |--------------------------------------------------------------------------
            */

            'country' => [
                'nullable',
                'string',
                'max:100',
            ],

            /*
            |--------------------------------------------------------------------------
            | Branch
            |--------------------------------------------------------------------------
            */

            'branch_address' => [
                'nullable',
                'string',
                'max:500',
            ],

            /*
            |--------------------------------------------------------------------------
            | VAT
            |--------------------------------------------------------------------------
            */

            'vat_type' => [
                'nullable',
                'string',
                Rule::in([
                    'percentage',
                    'fixed',
                ]),
            ],

            'vat_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99',
            ],

            /*
            |--------------------------------------------------------------------------
            | Service Charge
            |--------------------------------------------------------------------------
            */

            'service_charge_type' => [
                'nullable',
                'string',
                Rule::in([
                    'percentage',
                    'fixed',
                ]),
            ],

            'service_charge_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99',
            ],

            /*
            |--------------------------------------------------------------------------
            | Owner NID
            |--------------------------------------------------------------------------
            */

            'owner_nid' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],

            /*
            |--------------------------------------------------------------------------
            | Owner Trade License
            |--------------------------------------------------------------------------
            */

            'owner_trade_license' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
        ];
    }
}
