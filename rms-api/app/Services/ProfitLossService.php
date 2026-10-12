<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalLine;

class ProfitLossService
{

    public function getProfitLoss(
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
                ->whereIn(
                    'account_type',
                    [
                        ChartOfAccount::TYPE_REVENUE,
                        ChartOfAccount::TYPE_EXPENSE,
                        ChartOfAccount::TYPE_COGS,
                    ]
                )
                ->where(
                    'is_active',
                    true
                )
                ->orderBy('code')
                ->get();



        $revenue = [];

        $expenses = [];



        foreach($accounts as $account){


            $balance =
                $this->accountBalance(
                    $account->id,
                    $from,
                    $to
                );


            if(round($balance,2)==0){
                continue;
            }



            $row = [

    'account_id'=>$account->id,

    'code'=>$account->code,

    'account_name'=>$account->name,

    'account_type'=>$account->account_type,

    'balance'=>$balance,

];



            if(
                $account->account_type
                ===
                ChartOfAccount::TYPE_REVENUE
            ){

                $revenue[]=$row;

            }
            else{

                $expenses[]=$row;

            }

        }



        $totalRevenue =
            collect($revenue)
                ->sum('balance');


        $totalExpense =
            collect($expenses)
                ->sum('balance');



        return [

            'period'=>[

                'from'=>$from,

                'to'=>$to,

            ],


            'revenue'=>[

                'details'=>$revenue,

                'total'=>round(
                    $totalRevenue,
                    2
                ),

            ],


            'expenses'=>[

                'details'=>$expenses,

                'total'=>round(
                    $totalExpense,
                    2
                ),

            ],


            'net_profit'=>round(
                $totalRevenue-$totalExpense,
                2
            ),

        ];

    }
    private function accountBalance(
    int $accountId,
    string $from,
    string $to
): float {


    $line =
        JournalLine::on('tenant')
            ->where(
                'ledger_account_id',
                $accountId
            )
            ->whereHas(
                'journalEntry',
                function($q) use($from,$to){

                    $q->whereBetween(
                        'journal_date',
                        [
                            $from,
                            $to
                        ]
                    );

                }
            )
            ->selectRaw(
                'SUM(debit) as debit,
                 SUM(credit) as credit'
            )
            ->first();



    $account =
        ChartOfAccount::on('tenant')
            ->find($accountId);



    if (!$account) {
        return 0;
    }



    if (
        $account->account_type
        ===
        ChartOfAccount::TYPE_REVENUE
    ) {

        return round(
            ($line->credit ?? 0)
            -
            ($line->debit ?? 0),
            2
        );

    }



    return round(
        ($line->debit ?? 0)
        -
        ($line->credit ?? 0),
        2
    );

}

}