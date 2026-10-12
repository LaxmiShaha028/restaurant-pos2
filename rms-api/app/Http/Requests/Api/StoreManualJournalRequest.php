<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreManualJournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->hasAccountingAccess();
    }


    public function rules(): array
    {
        return [

            'journal_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'description' => [
                'required',
                'string',
                'max:2000',
            ],

            'lines' => [
                'required',
                'array',
                'min:2',
                'max:50',
            ],

            'lines.*.ledger_account_id' => [
                'required',
                'integer',
                'exists:chart_of_accounts,id',
            ],

            'lines.*.debit' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99999999999999.99',
            ],

            'lines.*.credit' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99999999999999.99',
            ],

            'lines.*.memo' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }


    protected function prepareForValidation(): void
    {
        $lines =
            collect(
                $this->input(
                    'lines',
                    []
                )
            )
                ->map(
                    function (
                        mixed $line
                    ): array {

                        $line =
                            is_array($line)
                                ? $line
                                : [];


                        return [

                            'ledger_account_id' =>
                                isset(
                                    $line[
                                        'ledger_account_id'
                                    ]
                                )
                                    ? (int) $line[
                                        'ledger_account_id'
                                    ]
                                    : null,

                            'debit' =>
                                round(
                                    (float) (
                                        $line[
                                            'debit'
                                        ]
                                        ??
                                        0
                                    ),
                                    2
                                ),

                            'credit' =>
                                round(
                                    (float) (
                                        $line[
                                            'credit'
                                        ]
                                        ??
                                        0
                                    ),
                                    2
                                ),

                            'memo' =>
                                isset(
                                    $line[
                                        'memo'
                                    ]
                                )
                                &&
                                is_string(
                                    $line[
                                        'memo'
                                    ]
                                )
                                    ? trim(
                                        $line[
                                            'memo'
                                        ]
                                    )
                                    : null,
                        ];
                    }
                )
                ->values()
                ->all();


        $this->merge([

            'description' =>
                is_string(
                    $this->description
                )
                    ? trim(
                        $this->description
                    )
                    : $this->description,

            'lines' =>
                $lines,
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
