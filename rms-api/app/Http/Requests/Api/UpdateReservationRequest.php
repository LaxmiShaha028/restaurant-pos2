<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_id' =>
                $this->filled('customer_id')
                    ? (int) $this->input('customer_id')
                    : null,

            'customer_name' =>
                $this->filled('customer_name')
                    ? trim(
                        (string)
                        $this->input('customer_name')
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
                $this->filled('guest_count')
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
                            $id !== null &&
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

            'status' =>
                $this->filled('status')
                    ? trim(
                        (string)
                        $this->input('status')
                    )
                    : null,

            'note' =>
                $this->filled('note')
                    ? trim(
                        (string)
                        $this->input('note')
                    )
                    : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'customer_id' => [
                'nullable',
                'integer',
                'exists:customers,id',
                'required_without:customer_name',
            ],

            'customer_name' => [
                'nullable',
                'string',
                'max:150',
                'required_without:customer_id',
            ],

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

            'advance_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'reserved',
                    'confirmed',
                    'seated',
                ]),
            ],

            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

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

                $date =
                    $this->input(
                        'reservation_date'
                    );

                $start =
                    $this->input(
                        'start_time'
                    );

                $end =
                    $this->input(
                        'end_time'
                    );

                try {

                    $startAt =
                        \Carbon\Carbon::createFromFormat(
                            'Y-m-d H:i',
                            "{$date} {$start}"
                        );

                    $endAt =
                        \Carbon\Carbon::createFromFormat(
                            'Y-m-d H:i',
                            "{$date} {$end}"
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
                        !$endAt->greaterThan(
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

                } catch (
                    \Throwable
                ) {
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.exists' =>
                'The selected customer does not exist.',

            'customer_id.required_without' =>
                'Please select an existing customer or enter a new customer name.',

            'customer_name.required_without' =>
                'Please select an existing customer or enter a new customer name.',

            'reservation_date.required' =>
                'Please select a reservation date.',

            'reservation_date.date' =>
                'The reservation date is invalid.',

            'start_time.required' =>
                'Please select a reservation start time.',

            'start_time.date_format' =>
                'Reservation start time must use HH:MM format.',

            'end_time.required' =>
                'Please select a reservation end time.',

            'end_time.date_format' =>
                'Reservation end time must use HH:MM format.',

            'end_time.after' =>
                'Reservation end time must be after the start time.',

            'guest_count.required' =>
                'Please enter the number of guests.',

            'guest_count.integer' =>
                'Guest count must be a whole number.',

            'guest_count.min' =>
                'Guest count must be at least 1.',

            'table_ids.required' =>
                'Please select at least one table.',

            'table_ids.array' =>
                'The selected tables are invalid.',

            'table_ids.min' =>
                'Please select at least one table.',

            'table_ids.*.exists' =>
                'One or more selected tables no longer exist.',

            'table_ids.*.distinct' =>
                'The same table cannot be selected more than once.',

            'advance_amount.numeric' =>
                'Advance amount must be a valid number.',

            'advance_amount.min' =>
                'Advance amount cannot be negative.',

            'note.max' =>
                'Reservation note cannot exceed 1000 characters.',
        ];
    }
}