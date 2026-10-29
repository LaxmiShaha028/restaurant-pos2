<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', Rule::in(['automatic', 'weekday_weekend', 'birthday', 'loyalty'])],
            'discount_type' => ['required', Rule::in(['percentage', 'fixed'])],
            'discount_value' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'days_of_week' => ['nullable', 'array'],
            'days_of_week.*' => [Rule::in(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])],
            'start_time' => ['nullable', 'date_format:H:i', 'required_with:end_time'],
            'end_time' => ['nullable', 'date_format:H:i', 'required_with:start_time'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'minimum_order_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_customer_limit' => ['nullable', 'integer', 'min:1'],
            'minimum_customer_orders' => ['nullable', 'integer', 'min:0'],
            'birthday_window_days' => ['nullable', 'integer', 'min:0', 'max:30'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
