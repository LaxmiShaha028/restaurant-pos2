<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AvailableTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'date' =>
                trim(
                    (string) $this->input('date')
                ),

            'start_time' =>
                trim(
                    (string) $this->input('start_time')
                ),

            'end_time' =>
                trim(
                    (string) $this->input('end_time')
                ),

            'guest_count' =>
                $this->input('guest_count') !== null &&
                $this->input('guest_count') !== ''
                    ? (int) $this->input('guest_count')
                    : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'date' => [
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
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' =>
                'Reservation date is required.',

            'date.date' =>
                'Please enter a valid reservation date.',

            'start_time.required' =>
                'Reservation start time is required.',

            'start_time.date_format' =>
                'Reservation start time must use HH:MM format.',

            'end_time.required' =>
                'Reservation end time is required.',

            'end_time.date_format' =>
                'Reservation end time must use HH:MM format.',

            'end_time.after' =>
                'Reservation end time must be later than the start time.',

            'guest_count.required' =>
                'Guest count is required.',

            'guest_count.integer' =>
                'Guest count must be a whole number.',

            'guest_count.min' =>
                'Guest count must be at least 1.',

            'guest_count.max' =>
                'Guest count is too large.',
        ];
    }
}