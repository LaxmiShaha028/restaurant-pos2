<?php

namespace App\Http\Requests\Api;

use App\Models\ChartOfAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChartOfAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->hasAccountingAccess();
    }


    public function rules(): array
    {
        return [

            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique(
                    'chart_of_accounts',
                    'code'
                ),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'account_type' => [
                'required',
                Rule::in(
                    ChartOfAccount::allowedTypes()
                ),
            ],

            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists(
                    'chart_of_accounts',
                    'id'
                )
                    ->where(
                        'is_active',
                        true
                    )
                    ->whereNull(
                        'deleted_at'
                    ),
            ],

            'allow_manual_posting' => [
                'nullable',
                'boolean',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }


    protected function prepareForValidation(): void
    {
        $this->merge([

            'code' =>
                is_string(
                    $this->code
                )
                    ? trim(
                        $this->code
                    )
                    : $this->code,

            'name' =>
                is_string(
                    $this->name
                )
                    ? trim(
                        $this->name
                    )
                    : $this->name,

            'parent_id' =>
                $this->parent_id === ''
                ||
                $this->parent_id === null
                    ? null
                    : (int) $this->parent_id,

            'allow_manual_posting' =>
                $this->has(
                    'allow_manual_posting'
                )
                    ? filter_var(
                        $this->allow_manual_posting,
                        FILTER_VALIDATE_BOOLEAN
                    )
                    : true,

            'is_active' =>
                $this->has(
                    'is_active'
                )
                    ? filter_var(
                        $this->is_active,
                        FILTER_VALIDATE_BOOLEAN
                    )
                    : true,

            'description' =>
                is_string(
                    $this->description
                )
                    ? trim(
                        $this->description
                    )
                    : $this->description,
        ]);
    }


    private function hasAccountingAccess(): bool
    {
        $user =
            $this->user();

        $user?->loadMissing(
            'role'
        );

        $roleName =
            strtolower(
                trim(
                    (string) (
                        $user
                            ?->role
                            ?->name
                        ??
                        ''
                    )
                )
            );

        return (
            $user !== null
            &&
            $user->is_active
            &&
            $user->blocked_at === null
            &&
            in_array(
                $roleName,
                [
                    'admin',
                    'manager',
                ],
                true
            )
        );
    }
}
