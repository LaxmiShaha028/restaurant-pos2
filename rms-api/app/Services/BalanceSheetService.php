<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalLine;
use App\Services\ProfitLossService;


class BalanceSheetService
{

    public function __construct(
        private readonly ProfitLossService $profitLossService
    ) {
    }

    public function getBalanceSheet(
        array $filters = []
    ): array {


        $accounts =
            ChartOfAccount::on('tenant')
                ->whereIn(
                    'account_type',
                    [
                        ChartOfAccount::TYPE_ASSET,
                        ChartOfAccount::TYPE_LIABILITY,
                        ChartOfAccount::TYPE_EQUITY,
                    ]
                )
                ->where(
                    'is_active',
                    true
                )
                ->orderBy(
                    'code'
                )
                ->get();



        $assets = [];

        $liabilities = [];

        $equity = [];


        $currentProfit =
            $this->profitLossService
                ->getProfitLoss($filters)
                ['net_profit'];



        foreach (
            $accounts
            as $account
        ) {


            $balance =
                $this->calculateAccountBalance(
                    $account->id
                );


            if (
                round(
                    $balance,
                    2
                ) == 0
            ) {

                continue;

            }



            $row = [

                'account_id' =>
                    $account->id,

                'code' =>
                    $account->code,

                'account_name' =>
                    $account->name,

                'account_type' =>
                    $account->account_type,

                'balance' =>
                    $balance,

            ];



            switch (
                $account->account_type
            ) {


                case ChartOfAccount::TYPE_ASSET:

                    $assets[] =
                        $row;

                    break;



                case ChartOfAccount::TYPE_LIABILITY:

                    $liabilities[] =
                        $row;

                    break;



                case ChartOfAccount::TYPE_EQUITY:

                    $equity[] =
                        $row;

                    break;

            }

        }



        return [

            'assets' => [

                'details' =>
                    $assets,

                'total' =>
                    round(
                        collect($assets)
                            ->sum('balance'),
                        2
                    ),

            ],



            'liabilities' => [

                'details' =>
                    $liabilities,

                'total' =>
                    round(
                        collect($liabilities)
                            ->sum('balance'),
                        2
                    ),

            ],

            

            'equity' => [

    'details' =>
        collect($equity)
            ->push([

                'account_id' => null,

                'code' => 'CURRENT',

                'account_name' =>
                    'Current Period Profit',

                'account_type' =>
                    'equity',

                'balance' =>
                    $currentProfit,

            ])
            ->values()
            ->toArray(),


    'total' =>
        round(

            collect($equity)
                ->sum('balance')
            +
            $currentProfit,

            2

        ),

],



            'balance_check' => [

    'assets' =>
        round(
            collect($assets)
                ->sum('balance'),
            2
        ),

    'liabilities_equity' =>
        round(
            collect($liabilities)
                ->sum('balance')
            +
            collect($equity)
                ->sum('balance')
            +
            $currentProfit,
            2
        ),

],


        ];

    }





    private function calculateAccountBalance(
        int $accountId
    ): float {


        $debit =
            JournalLine::on('tenant')
                ->where(
                    'ledger_account_id',
                    $accountId
                )
                ->sum(
                    'debit'
                );


        $credit =
            JournalLine::on('tenant')
                ->where(
                    'ledger_account_id',
                    $accountId
                )
                ->sum(
                    'credit'
                );



        $account =
            ChartOfAccount::on('tenant')
                ->find(
                    $accountId
                );



        switch(
    $account->account_type
){

    case ChartOfAccount::TYPE_ASSET:

        return round(
            $debit - $credit,
            2
        );


    case ChartOfAccount::TYPE_LIABILITY:

    case ChartOfAccount::TYPE_EQUITY:

        return round(
            $credit - $debit,
            2
        );


    default:

        return 0;

}

    }

}