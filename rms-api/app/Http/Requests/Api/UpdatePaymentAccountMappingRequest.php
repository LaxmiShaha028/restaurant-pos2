<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentAccountMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->hasAccountingAccess();
    }


    public function rules(): array
    {
        return [

            'ledger_account_id' => [
                'required',
                'integer',

                Rule::exists(
                    'chart_of_accounts',
                    'id'
                )
                    ->where(
                        'account_type',
                        'asset'
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->where(
                        'allow_manual_posting',
                        true
                    )
                    ->whereNull(
                        'deleted_at'
                    ),
            ],
        ];
    }


    protected function prepareForValidation(): void
    {
        $this->merge([

            'ledger_account_id' =>
                (int) $this->ledger_account_id,
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
