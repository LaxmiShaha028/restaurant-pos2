<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {

        return [

            'name'=>[
                'required',
                'string',
                'max:150',
            ],



            'username'=>[
                'required',
                'string',
                'max:100',

                Rule::unique(
                    'users',
                    'username'
                ),

            ],




            'email'=>[
                'required',
                'email',
                'max:180',

                Rule::unique(
                    'users',
                    'email'
                ),

            ],




            'password'=>[
                'required',
                'string',
                'min:8',
                'confirmed',
            ],




            'role_id'=>[

                'required',

                'integer',

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
                'nullable',
                'boolean',
            ],






            /*
            |--------------------------------------------------------------------------
            | Employee Optional Data
            |--------------------------------------------------------------------------
            */


            'phone'=>[
                'nullable',
                'string',
                'max:30',
            ],



            'joining_date'=>[
                'nullable',
                'date',
            ],



            'hourly_rate'=>[
                'nullable',
                'numeric',
                'min:0',
            ],



            'profile_image'=>[
                'nullable',
                'image',
                'max:2048',
            ],


        ];

    }




    public function messages(): array
    {

        return [

            'name.required'=>
                'User name is required.',


            'username.unique'=>
                'Username already exists.',


            'email.unique'=>
                'Email already exists.',


            'password.confirmed'=>
                'Password confirmation does not match.',


            'role_id.exists'=>
                'Invalid role selected.',


            'profile_image.image'=>
                'Profile file must be an image.',


        ];

    }


}