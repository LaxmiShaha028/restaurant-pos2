<?php

namespace App\Http\Requests\Api;

use App\Models\ChartOfAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryLedgerAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->hasAccountingAccess();
    }


    public function rules(): array
    {
        return [

            'ledger_account_id' => [
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
        ];
    }


    protected function prepareForValidation(): void
    {
        $this->merge([

            'ledger_account_id' =>
                $this->ledger_account_id === ''
                ||
                $this->ledger_account_id === null
                    ? null
                    : (int) $this->ledger_account_id,
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
