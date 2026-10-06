<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReservationRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

    public function authorize(): bool
    {
        return $this->user() !== null;
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Input
    |--------------------------------------------------------------------------
    */

    protected function prepareForValidation(): void
    {
        $this->merge([

            'customer_id' =>
                $this->filled(
                    'customer_id'
                )
                    ? (int)
                    $this->input(
                        'customer_id'
                    )
                    : null,

            'customer_name' =>
                $this->filled(
                    'customer_name'
                )
                    ? trim(
                        (string)
                        $this->input(
                            'customer_name'
                        )
                    )
                    : null,

            'customer_phone' =>
                $this->filled(
                    'customer_phone'
                )
                    ? trim(
                        (string)
                        $this->input(
                            'customer_phone'
                        )
                    )
                    : null,

            'customer_email' =>
                $this->filled(
                    'customer_email'
                )
                    ? strtolower(
                        trim(
                            (string)
                            $this->input(
                                'customer_email'
                            )
                        )
                    )
                    : null,

            'reservation_date' =>
                trim(
                    (string)
                    $this->input(
                        'reservation_date'
                    )
                ),

            'start_time' =>
                trim(
                    (string)
                    $this->input(
                        'start_time'
                    )
                ),

            'end_time' =>
                trim(
                    (string)
                    $this->input(
                        'end_time'
                    )
                ),

            'guest_count' =>
                $this->filled(
                    'guest_count'
                )
                    ? (int)
                    $this->input(
                        'guest_count'
                    )
                    : null,

            'table_ids' =>
                collect(
                    $this->input(
                        'table_ids',
                        []
                    )
                )
                    ->filter(
                        static fn (
                            mixed $id
                        ): bool =>
                            $id !== null
                            &&
                            $id !== ''
                    )
                    ->map(
                        static fn (
                            mixed $id
                        ): int =>
                            (int) $id
                    )
                    ->unique()
                    ->values()
                    ->all(),

            'advance_amount' =>
                $this->filled(
                    'advance_amount'
                )
                    ? round(
                        max(
                            0,
                            (float)
                            $this->input(
                                'advance_amount'
                            )
                        ),
                        2
                    )
                    : 0,

            'note' =>
                $this->filled(
                    'note'
                )
                    ? trim(
                        (string)
                        $this->input(
                            'note'
                        )
                    )
                    : null,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Existing OR New Customer
            |--------------------------------------------------------------------------
            */

            'customer_id' => [
                'nullable',
                'integer',
                'exists:customers,id',
                'required_without:customer_name',
            ],

            'customer_name' => [
                'nullable',
                'string',
                'min:2',
                'max:150',
                'required_without:customer_id',
            ],

            /*
            | Phone and email are optional for reservation booking.
            */

            'customer_phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'customer_email' => [
                'nullable',
                'email:rfc',
                'max:150',
            ],

            /*
            |--------------------------------------------------------------------------
            | Schedule
            |--------------------------------------------------------------------------
            */

            'reservation_date' => [
                'required',
                'date',
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
            ],

            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],

            'guest_count' => [
                'required',
                'integer',
                'min:1',
                'max:1000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Tables
            |--------------------------------------------------------------------------
            */

            'table_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'table_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:restaurant_tables,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Other
            |--------------------------------------------------------------------------
            */

            'advance_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Cross-field / Future-time Validation
    |--------------------------------------------------------------------------
    */

    public function after(): array
    {
        return [
            function (
                Validator $validator
            ): void {

                if (
                    $validator
                        ->errors()
                        ->isNotEmpty()
                ) {
                    return;
                }


                try {

                    $startAt =
                        \Carbon\Carbon::createFromFormat(
                            'Y-m-d H:i',
                            $this->input(
                                'reservation_date'
                            )
                            .
                            ' '
                            .
                            $this->input(
                                'start_time'
                            )
                        );


                    $endAt =
                        \Carbon\Carbon::createFromFormat(
                            'Y-m-d H:i',
                            $this->input(
                                'reservation_date'
                            )
                            .
                            ' '
                            .
                            $this->input(
                                'end_time'
                            )
                        );


                    if (
                        $startAt->lessThan(
                            now()
                        )
                    ) {

                        $validator
                            ->errors()
                            ->add(
                                'start_time',
                                'Reservation start time must be in the future.'
                            );
                    }


                    if (
                        ! $endAt->greaterThan(
                            $startAt
                        )
                    ) {

                        $validator
                            ->errors()
                            ->add(
                                'end_time',
                                'Reservation end time must be after the reservation start time.'
                            );
                    }

                }
                catch (
                    \Throwable
                ) {
                    // Field-level validation above will handle malformed values.
                }
            },
        ];
    }


    public function messages(): array
    {
        return [

            'customer_id.required_without' =>
                'Select an existing customer or enter a new customer name.',

            'customer_id.exists' =>
                'The selected customer does not exist.',

            'customer_name.required_without' =>
                'Select an existing customer or enter a new customer name.',

            'customer_name.min' =>
                'Customer name must contain at least 2 characters.',

            'customer_name.max' =>
                'Customer name may not exceed 150 characters.',

            'customer_phone.max' =>
                'Customer phone may not exceed 30 characters.',

            'customer_email.email' =>
                'Please enter a valid customer email address.',

            'reservation_date.required' =>
                'Please select a reservation date.',

            'start_time.required' =>
                'Please select a reservation start time.',

            'end_time.required' =>
                'Please select a reservation end time.',

            'end_time.after' =>
                'Reservation end time must be after the start time.',

            'guest_count.required' =>
                'Please enter the number of guests.',

            'guest_count.min' =>
                'Guest count must be at least 1.',

            'table_ids.required' =>
                'Please select at least one table.',

            'table_ids.min' =>
                'Please select at least one table.',

            'table_ids.*.exists' =>
                'One or more selected tables no longer exist.',

            'advance_amount.min' =>
                'Advance amount cannot be negative.',

            'note.max' =>
                'Reservation note cannot exceed 1000 characters.',
        ];
    }
}
