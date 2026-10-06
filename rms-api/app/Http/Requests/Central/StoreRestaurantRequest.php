<?php

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

class StoreRestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'name' => [
                'required',
                'string',
                'max:180',
            ],

            'logo_path' => [
                'nullable',
                'string',
                'max:255',
            ],

            'owner_name' => [
                'required',
                'string',
                'max:150',
            ],

            'owner_email' => [
                'required',
                'email',
                'max:150',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'country' => [
                'nullable',
                'string',
                'max:100',
            ],

            'subscription_plan_id' => [
                'required',
                'integer',
                'exists:central.subscription_plans,id',
            ],

            'username' => [
                'nullable',
                'string',
                'max:100',
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'max:255',
            ],
        ];
    }
}