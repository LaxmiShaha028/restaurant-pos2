<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalLine;


class GeneralLedgerService
{

    /*
    |--------------------------------------------------------------------------
    | Get Ledger Accounts
    |--------------------------------------------------------------------------
    |
    | Used by General Ledger account dropdown.
    |
    */

    public function getAccounts(): array
    {

        return ChartOfAccount::on('tenant')
            ->orderBy('code')
            ->get([
                'id',
                'code',
                'name',
                'account_type',
            ])
            ->map(
                function ($account) {

                    return [

                        'id' =>
                            $account->id,

                        'code' =>
                            $account->code,

                        'name' =>
                            $account->name,

                        'account_type' =>
                            $account->account_type,

                    ];

                }
            )
            ->values()
            ->all();

    }


    /*
    |--------------------------------------------------------------------------
    | Get General Ledger
    |--------------------------------------------------------------------------
    */

    public function getLedger(
        int $accountId,
        array $filters = []
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Date Filters
        |--------------------------------------------------------------------------
        |
        | Frontend uses:
        |
        | date_from
        | date_to
        |
        | Also support from_date / to_date for backward compatibility.
        |
        */

        $from =
            $filters['date_from']
            ??
            $filters['from_date']
            ??
            now()
                ->startOfMonth()
                ->format('Y-m-d');


        $to =
            $filters['date_to']
            ??
            $filters['to_date']
            ??
            now()
                ->format('Y-m-d');


        /*
        |--------------------------------------------------------------------------
        | Account
        |--------------------------------------------------------------------------
        */

        $account =
            ChartOfAccount::on('tenant')
                ->findOrFail(
                    $accountId
                );


        /*
        |--------------------------------------------------------------------------
        | Journal Lines
        |--------------------------------------------------------------------------
        */

        $lines =
            JournalLine::on('tenant')
                ->with(
                    'journalEntry'
                )
                ->where(
                    'ledger_account_id',
                    $accountId
                )
                ->whereHas(
                    'journalEntry',
                    function ($query) use (
                        $from,
                        $to
                    ) {

                        $query->whereBetween(
                            'journal_date',
                            [
                                $from,
                                $to,
                            ]
                        );

                    }
                )
                ->join(
                    'journal_entries',
                    'journal_lines.journal_entry_id',
                    '=',
                    'journal_entries.id'
                )
                ->orderBy(
                    'journal_entries.journal_date'
                )
                ->orderBy(
                    'journal_lines.id'
                )
                ->select(
                    'journal_lines.*'
                )
                ->get();


        /*
        |--------------------------------------------------------------------------
        | Running Balance
        |--------------------------------------------------------------------------
        */

        $balance =
            0;


        $transactions =
            [];


        /*
        |--------------------------------------------------------------------------
        | Calculate Running Balance
        |--------------------------------------------------------------------------
        */

        foreach (
            $lines as $line
        ) {

            $debit =
                (float)
                $line->debit;


            $credit =
                (float)
                $line->credit;


            /*
            |--------------------------------------------------------------------------
            | Normal Balance
            |--------------------------------------------------------------------------
            |
            | Asset / Expense / COGS:
            |
            | Debit - Credit
            |
            | Liability / Equity / Revenue:
            |
            | Credit - Debit
            |
            */

            if (
                in_array(
                    $account->account_type,
                    [
                        ChartOfAccount::TYPE_ASSET,
                        ChartOfAccount::TYPE_EXPENSE,
                        ChartOfAccount::TYPE_COGS,
                    ],
                    true
                )
            ) {

                $balance +=
                    $debit
                    -
                    $credit;

            }
            else {

                $balance +=
                    $credit
                    -
                    $debit;

            }


            /*
            |--------------------------------------------------------------------------
            | Transaction
            |--------------------------------------------------------------------------
            */

            $transactions[] = [

                'date' =>
                    $line
                        ->journalEntry
                        ->journal_date
                        ->format(
                            'Y-m-d'
                        ),


                'journal_number' =>
                    $line
                        ->journalEntry
                        ->journal_number,


                'description' =>
                    $line
                        ->journalEntry
                        ->description,


                'debit' =>
                    $debit,


                'credit' =>
                    $credit,


                'balance' =>
                    round(
                        $balance,
                        2
                    ),

            ];

        }


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return [

            'account' => [

                'id' =>
                    $account->id,

                'code' =>
                    $account->code,

                'name' =>
                    $account->name,

                'type' =>
                    $account->account_type,

            ],


            'period' => [

                'from' =>
                    $from,

                'to' =>
                    $to,

            ],


            'transactions' =>
                $transactions,


            'closing_balance' =>
                round(
                    $balance,
                    2
                ),

        ];

    }

}