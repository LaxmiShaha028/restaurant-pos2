<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ReverseJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->hasAccountingAccess();
    }


    public function rules(): array
    {
        return [

            'reason' => [
                'required',
                'string',
                'min:3',
                'max:500',
            ],

            'reversal_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
        ];
    }


    protected function prepareForValidation(): void
    {
        $this->merge([

            'reason' =>
                is_string(
                    $this->reason
                )
                    ? trim(
                        $this->reason
                    )
                    : $this->reason,
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
