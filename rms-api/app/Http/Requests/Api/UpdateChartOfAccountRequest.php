<?php

namespace App\Http\Requests\Api;

use App\Models\ChartOfAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChartOfAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->hasAccountingAccess();
    }


    public function rules(): array
    {
        $account =
            $this->route(
                'chartOfAccount'
            );

        return [

            'code' => [
                'sometimes',
                'required',
                'string',
                'max:20',

                Rule::unique(
                    'chart_of_accounts',
                    'code'
                )
                    ->ignore(
                        $account?->id
                    ),
            ],

            'name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],

            'account_type' => [
                'sometimes',
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
                'sometimes',
                'boolean',
            ],

            'is_active' => [
                'sometimes',
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
        $data = [];


        foreach (
            [
                'code',
                'name',
                'description',
            ]
            as $field
        ) {
            if (
                $this->has(
                    $field
                )
                &&
                is_string(
                    $this->{$field}
                )
            ) {
                $data[
                    $field
                ] =
                    trim(
                        $this->{$field}
                    );
            }
        }


        if (
            $this->has(
                'parent_id'
            )
        ) {
            $data[
                'parent_id'
            ] =
                $this->parent_id === ''
                ||
                $this->parent_id === null
                    ? null
                    : (int) $this->parent_id;
        }


        if (
            $this->has(
                'allow_manual_posting'
            )
        ) {
            $data[
                'allow_manual_posting'
            ] =
                filter_var(
                    $this->allow_manual_posting,
                    FILTER_VALIDATE_BOOLEAN
                );
        }


        if (
            $this->has(
                'is_active'
            )
        ) {
            $data[
                'is_active'
            ] =
                filter_var(
                    $this->is_active,
                    FILTER_VALIDATE_BOOLEAN
                );
        }


        if ($data !== []) {
            $this->merge(
                $data
            );
        }
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
