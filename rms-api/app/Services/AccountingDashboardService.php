<?php

namespace App\Services;


use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\ChartOfAccount;



class AccountingDashboardService
{


    public function __construct(

        private readonly ProfitLossService $profitLossService,

        private readonly BalanceSheetService $balanceSheetService,

        private readonly OwnerLedgerService $ownerLedgerService,

    ){

    }






    public function getDashboard(
        array $filters = []
    ): array
    {


        /*
        |--------------------------------------------------------------------------
        | Profit Loss
        |--------------------------------------------------------------------------
        */


        $profitLoss =

            $this->profitLossService

                ->getProfitLoss(
                    $filters
                );






        /*
        |--------------------------------------------------------------------------
        | Balance Sheet
        |--------------------------------------------------------------------------
        */


        $balanceSheet =

            $this->balanceSheetService

                ->getBalanceSheet(
                    $filters
                );


        /*
        |--------------------------------------------------------------------------
        | Owner Ledger Summary
        |--------------------------------------------------------------------------
        */

        $ownerLedger =

            $this->ownerLedgerService

                ->getOwnerLedger(
                    $filters
                );




        /*
        |--------------------------------------------------------------------------
        | Payment Position
        |--------------------------------------------------------------------------
        */


        $paymentAccounts =

            ChartOfAccount::on('tenant')

                ->whereIn(

                    'system_key',

                    [

                        'cash',

                        'bank',

                        'bkash_wallet',

                        'nagad_wallet',

                        'card_settlement'

                    ]

                )

                ->get();




        $payments = [];




        foreach(
            $paymentAccounts as $account
        ){

            $debit =

                JournalLine::on('tenant')

                ->where(
                    'ledger_account_id',
                    $account->id
                )

                ->sum(
                    'debit'
                );



            $credit =

                JournalLine::on('tenant')

                ->where(
                    'ledger_account_id',
                    $account->id
                )

                ->sum(
                    'credit'
                );




            $payments[] = [

                'account_name'=>
                    $account->name,


                'balance'=>
                    round(
                        $debit-$credit,
                        2
                    )

            ];


        }









        /*
        |--------------------------------------------------------------------------
        | Expense Breakdown
        |--------------------------------------------------------------------------
        */


        $expenseAccounts =

            ChartOfAccount::on('tenant')

                ->whereIn(

                    'account_type',

                    [

                        ChartOfAccount::TYPE_EXPENSE,

                        ChartOfAccount::TYPE_COGS

                    ]

                )

                ->get();




        $expenseBreakdown=[];




        foreach(
            $expenseAccounts as $account
        ){


            $amount =

    JournalLine::on('tenant')

    ->where(

        'ledger_account_id',

        $account->id

    )

    ->whereHas(

        'journalEntry',

        function ($query) use ($filters) {

            if (!empty($filters['from_date'])) {

                $query->whereDate(
                    'journal_date',
                    '>=',
                    $filters['from_date']
                );

            }

            if (!empty($filters['to_date'])) {

                $query->whereDate(
                    'journal_date',
                    '<=',
                    $filters['to_date']
                );

            }

        }

    )

    ->sum(

        'debit'

    );



            if(
                $amount > 0
            ){

                $expenseBreakdown[]=[


                    'account_name'=>
                        $account->name,


                    'amount'=>
                        round(
                            $amount,
                            2
                        )

                ];

            }


        }









        /*
        |--------------------------------------------------------------------------
        | Recent Journals
        |--------------------------------------------------------------------------
        */


        $recentJournals =

    JournalEntry::on('tenant')

        ->when(
            !empty($filters['from_date']),
            function ($query) use ($filters) {

                $query->whereDate(
                    'journal_date',
                    '>=',
                    $filters['from_date']
                );

            }
        )

        ->when(
            !empty($filters['to_date']),
            function ($query) use ($filters) {

                $query->whereDate(
                    'journal_date',
                    '<=',
                    $filters['to_date']
                );

            }
        )

        ->latest('journal_date')

        ->latest('id')

        ->limit(10)

        ->get([
            'id',
            'journal_number',
            'journal_date',
            'description',
            'total_debit',
            'total_credit'
        ])

        ->map(function ($journal) {

            return [
                'id' =>
                    $journal->id,

                'journal_number' =>
                    $journal->journal_number,

                'journal_date' =>
                    $journal->journal_date
                        ? \Carbon\Carbon::parse(
                            $journal->journal_date
                        )->format('Y-m-d')
                        : null,

                'description' =>
                    $journal->description,

                'total_debit' =>
                    $journal->total_debit,

                'total_credit' =>
                    $journal->total_credit,
            ];

        })

        ->values();

        return [


            'period'=>[

                'from'=>
                    $filters['from_date'] ?? null,


                'to'=>
                    $filters['to_date'] ?? null,

            ],





            'summary'=>[


    'revenue'=>

        $ownerLedger['summary']['revenue'],


    'purchase'=>

        $ownerLedger['summary']['purchase'],


    'purchase_return'=>

        $ownerLedger['summary']['purchase_return'],


    'food_cost'=>

        $ownerLedger['summary']['food_cost'],


    'expense'=>

        $ownerLedger['summary']['expense'],


    'profit'=>

        $ownerLedger['summary']['profit'],


    'assets'=>

        $balanceSheet['assets']['total'],


],







            'payment_position'=>

                $payments,






            'expense_breakdown'=>

                $expenseBreakdown,






            'recent_journals'=>

                $recentJournals,



        ];


    }



}