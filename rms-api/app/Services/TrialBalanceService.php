<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalLine;


class TrialBalanceService
{


    public function getTrialBalance(
        array $filters = []
    ): array {


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



        $accounts =
            ChartOfAccount::on('tenant')
                ->where(
                    'is_active',
                    true
                )
                ->orderBy(
                    'code'
                )
                ->get();



        $rows = [];

        $totalDebit = 0;

        $totalCredit = 0;



        foreach($accounts as $account)
        {


            $line =
                JournalLine::on('tenant')
                    ->where(
                        'ledger_account_id',
                        $account->id
                    )
                    ->whereHas(
                        'journalEntry',
                        function($query) use($from,$to){

                            $query->whereBetween(
                                'journal_date',
                                [
                                    $from,
                                    $to
                                ]
                            );

                        }
                    )
                    ->selectRaw(
                        '
                        SUM(debit) as debit,
                        SUM(credit) as credit
                        '
                    )
                    ->first();



            $debit =
                round(
                    $line->debit ?? 0,
                    2
                );


            $credit =
                round(
                    $line->credit ?? 0,
                    2
                );



            if(
                $debit == 0
                &&
                $credit == 0
            ){

                continue;

            }



            $rows[] = [

                'account_id' =>
                    $account->id,

                'code' =>
                    $account->code,

                'account_name' =>
                    $account->name,

                'account_type' =>
                    $account->account_type,

                'debit' =>
                    $debit,

                'credit' =>
                    $credit,

            ];



            $totalDebit += $debit;

            $totalCredit += $credit;


        }



        return [

            'period'=>[

                'from'=>$from,

                'to'=>$to,

            ],


            'accounts'=>$rows,


            'total_debit'=>
                round(
                    $totalDebit,
                    2
                ),


            'total_credit'=>
                round(
                    $totalCredit,
                    2
                ),



            'is_balanced'=>
                round(
                    $totalDebit,
                    2
                )
                ===
                round(
                    $totalCredit,
                    2
                ),

        ];

    }


}