<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {

        $user =
            $this->route('user');


        return [

            'name'=>[
                'sometimes',
                'required',
                'string',
                'max:150',
            ],


            'username'=>[
                'sometimes',
                'required',
                'string',
                'max:100',

                Rule::unique(
                    'users',
                    'username'
                )->ignore(
                    $user?->id
                ),
            ],


            'email'=>[
                'sometimes',
                'required',
                'email',
                'max:180',

                Rule::unique(
                    'users',
                    'email'
                )->ignore(
                    $user?->id
                ),
            ],



            'role_id'=>[

                'sometimes',

                'required',

                Rule::exists(
                    'roles',
                    'id'
                )->where(function($query){

                    $query->where(
                        'is_active',
                        true
                    );

                }),

            ],



            'is_active'=>[
                'sometimes',
                'boolean',
            ],


        ];

    }



    public function messages(): array
    {

        return [

            'role_id.exists'=>
                'Selected role is invalid or inactive.',

        ];

    }

}