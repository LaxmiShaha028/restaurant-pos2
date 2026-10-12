<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class TestAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'employee_id' => [
                'required',
                'integer',
                'exists:employees,id',
            ],

            'attendance_date' => [
                'required',
                'date',
            ],

            'check_in_at' => [
                'nullable',
                'date',
            ],

            'check_out_at' => [
                'nullable',
                'date',
                'after_or_equal:check_in_at',
            ],

        ];
    }
}