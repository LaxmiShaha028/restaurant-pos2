<?php

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\JournalLine;
use Carbon\Carbon;

class OwnerLedgerService
{
    public function __construct(
        private readonly ProfitLossService $profitLossService
    ) {
    }


    /**
     * Owner friendly accounting summary.
     *
     * Important:
     * - Revenue + Profit come from canonical Profit & Loss service.
     * - Purchase means goods actually received (GRN / purchase_receipt).
     * - Purchase is NOT deducted from profit because it first becomes Inventory.
     * - Food Cost represents consumed inventory / COGS.
     */
    public function getOwnerLedger(array $filters = []): array
    {
        $fromDate =
            $filters['date_from']
            ??
            $filters['from_date']
            ??
            Carbon::now()
                ->startOfMonth()
                ->format('Y-m-d');


        $toDate =
            $filters['date_to']
            ??
            $filters['to_date']
            ??
            Carbon::now()
                ->format('Y-m-d');


        /*
        |--------------------------------------------------------------------------
        | Canonical Profit & Loss
        |--------------------------------------------------------------------------
        |
        | This is the single source of truth for:
        |
        | Revenue
        | Net Profit
        |
        | It correctly nets debit / credit adjustments and reversals.
        |
        */

        $profitLoss =
            $this->profitLossService
                ->getProfitLoss([
                    'from_date' => $fromDate,
                    'to_date'   => $toDate,
                ]);


        $revenue =
            round(
                (float) (
                    $profitLoss['revenue']['total']
                    ??
                    0
                ),
                2
            );


        $profit =
            round(
                (float) (
                    $profitLoss['net_profit']
                    ??
                    0
                ),
                2
            );


        /*
        |--------------------------------------------------------------------------
        | Food Cost / COGS
        |--------------------------------------------------------------------------
        |
        | Net COGS:
        |
        | Debit - Credit
        |
        | This means reversals are automatically deducted.
        |
        */

        $foodCost =
            $this->getNetAccountTypeTotal(
                ['cogs'],
                $fromDate,
                $toDate
            );


        /*
        |--------------------------------------------------------------------------
        | Operating Expenses
        |--------------------------------------------------------------------------
        |
        | Net Expense:
        |
        | Debit - Credit
        |
        | COGS is intentionally excluded because Food Cost is shown separately.
        |
        */

        $expense =
            $this->getNetAccountTypeTotal(
                ['expense'],
                $fromDate,
                $toDate
            );


        /*
        |--------------------------------------------------------------------------
        | Purchases
        |--------------------------------------------------------------------------
        |
        | Purchase Order itself is only a commitment.
        |
        | Owner Ledger purchase means actual received goods / GRN.
        |
        | PurchaseAccountingPostingService source_type:
        |
        | purchase_receipt
        |
        */

        $purchase =
            $this->getPurchaseTotal(
                $fromDate,
                $toDate
            );


        /*
        |--------------------------------------------------------------------------
        | Purchase Returns
        |--------------------------------------------------------------------------
        */

        $purchaseReturn =
            $this->getPurchaseReturnTotal(
                $fromDate,
                $toDate
            );


        return [

            'period' => [

                'from' =>
                    $fromDate,

                'to' =>
                    $toDate,

            ],


            'summary' => [

                'revenue' =>
                    $revenue,

                'purchase' =>
                    $purchase,

                'purchase_return' =>
                    $purchaseReturn,

                'food_cost' =>
                    $foodCost,

                'expense' =>
                    $expense,

                'profit' =>
                    $profit,

            ],

        ];
    }


    /**
     * Calculate a NET account-type amount.
     *
     * Revenue normally:
     *     Credit - Debit
     *
     * Expense / COGS normally:
     *     Debit - Credit
     */
    private function getNetAccountTypeTotal(
        array $types,
        string $fromDate,
        string $toDate
    ): float {

        $totals =
            JournalLine::query()

                ->whereHas(
                    'journalEntry',
                    function ($query) use (
                        $fromDate,
                        $toDate
                    ) {

                        $query
                            ->whereDate(
                                'journal_date',
                                '>=',
                                $fromDate
                            )
                            ->whereDate(
                                'journal_date',
                                '<=',
                                $toDate
                            );

                    }
                )

                ->whereHas(
                    'ledgerAccount',
                    function ($query) use ($types) {

                        $query->whereIn(
                            'account_type',
                            $types
                        );

                    }
                )

                ->selectRaw(
                    '
                    COALESCE(SUM(debit), 0) AS total_debit,
                    COALESCE(SUM(credit), 0) AS total_credit
                    '
                )

                ->first();


        $debit =
            (float) (
                $totals->total_debit
                ??
                0
            );


        $credit =
            (float) (
                $totals->total_credit
                ??
                0
            );


        /*
        |--------------------------------------------------------------------------
        | Revenue
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                'revenue',
                $types,
                true
            )
        ) {

            return round(
                $credit - $debit,
                2
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Expense / COGS
        |--------------------------------------------------------------------------
        */

        return round(
            $debit - $credit,
            2
        );
    }

    /**
 * Owner-friendly Purchase total.
 *
 * Only Inventory debit from purchase_receipt journals is counted.
 * This represents goods actually received into inventory.
 */
private function getPurchaseTotal(
    string $fromDate,
    string $toDate
): float {

    $total =
        JournalLine::query()

            ->whereHas(
                'journalEntry',
                function ($query) use (
                    $fromDate,
                    $toDate
                ) {

                    $query
                        ->where(
                            'source_type',
                            'purchase_receipt'
                        )
                        ->whereDate(
                            'journal_date',
                            '>=',
                            $fromDate
                        )
                        ->whereDate(
                            'journal_date',
                            '<=',
                            $toDate
                        );

                }
            )

            ->whereHas(
                'ledgerAccount',
                function ($query) {

                    $query->where(
                        'system_key',
                        'inventory'
                    );

                }
            )

            ->sum('debit');


    return round(
        (float) $total,
        2
    );
}

    /**
 * Owner-friendly Purchase Return total.
 *
 * Only Purchase Return Clearing debit is counted.
 *
 * We intentionally do NOT sum every debit line from the
 * purchase_return journal because valuation loss may create
 * an additional debit line.
 */
private function getPurchaseReturnTotal(
    string $fromDate,
    string $toDate
): float {

    $total =
        JournalLine::query()

            ->whereHas(
                'journalEntry',
                function ($query) use (
                    $fromDate,
                    $toDate
                ) {

                    $query
                        ->where(
                            'source_type',
                            'purchase_return'
                        )
                        ->whereDate(
                            'journal_date',
                            '>=',
                            $fromDate
                        )
                        ->whereDate(
                            'journal_date',
                            '<=',
                            $toDate
                        );

                }
            )

            ->whereHas(
                'ledgerAccount',
                function ($query) {

                    $query->where(
                        'system_key',
                        'purchase_return_clearing'
                    );

                }
            )

            ->sum('debit');


    return round(
        (float) $total,
        2
    );
}

    /**
     * Sum debit side of journal entries for a specific source.
     *
     * Used for business-information metrics such as:
     *
     * purchase_receipt
     * purchase_return
     */
    private function getSourceDebitTotal(
        string $sourceType,
        string $fromDate,
        string $toDate
    ): float {

        $total =
            JournalLine::query()

                ->whereHas(
                    'journalEntry',
                    function ($query) use (
                        $sourceType,
                        $fromDate,
                        $toDate
                    ) {

                        $query
                            ->where(
                                'source_type',
                                $sourceType
                            )
                            ->whereDate(
                                'journal_date',
                                '>=',
                                $fromDate
                            )
                            ->whereDate(
                                'journal_date',
                                '<=',
                                $toDate
                            );

                    }
                )

                ->sum('debit');


        return round(
            (float) $total,
            2
        );
    }
}