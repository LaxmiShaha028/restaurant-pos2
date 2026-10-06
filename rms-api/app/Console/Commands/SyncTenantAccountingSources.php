<?php

namespace App\Console\Commands;

use App\Models\Central\Restaurant;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use App\Services\AccountingSourcePostingService;
use App\Services\Tenant\TenantConnectionManager;
use App\Services\Tenant\TenantManager;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncTenantAccountingSources extends Command
{
    protected $signature =
        'accounting:sync-sources
        {--restaurant= : Optional central Restaurant ID. Omit to process every active tenant.}
        {--type=all : all, income or expense}
        {--from= : Optional Y-m-d source date start.}
        {--to= : Optional Y-m-d source date end.}';


    protected $description =
        'Safely backfill/synchronize Other Income and Expense sources into the double-entry journal.';


    public function __construct(
        private readonly TenantConnectionManager $connectionManager,
        private readonly TenantManager $tenantManager,
        private readonly AccountingSourcePostingService $postingService
    ) {
        parent::__construct();
    }


    public function handle(): int
    {
        $type =
            strtolower(
                trim(
                    (string) $this->option(
                        'type'
                    )
                )
            );


        if (
            ! in_array(
                $type,
                [
                    'all',
                    'income',
                    'expense',
                ],
                true
            )
        ) {
            $this->error(
                'Invalid --type. Use all, income or expense.'
            );

            return self::FAILURE;
        }


        try {

            $from =
                $this->option(
                    'from'
                )
                    ? Carbon::parse(
                        (string) $this->option(
                            'from'
                        )
                    )->startOfDay()
                    : null;


            $to =
                $this->option(
                    'to'
                )
                    ? Carbon::parse(
                        (string) $this->option(
                            'to'
                        )
                    )->endOfDay()
                    : null;

        } catch (Throwable) {

            $this->error(
                'Invalid --from or --to date. Use Y-m-d.'
            );

            return self::FAILURE;
        }


        if (
            $from
            &&
            $to
            &&
            $from->gt(
                $to
            )
        ) {
            $this->error(
                '--from cannot be after --to.'
            );

            return self::FAILURE;
        }


        DB::setDefaultConnection(
            'central'
        );


        $restaurantsQuery =
            Restaurant::on(
                'central'
            )
                ->with([
                    'tenantDatabase',
                ])
                ->where(
                    'status',
                    'active'
                )
                ->whereHas(
                    'tenantDatabase',
                    function ($query): void {

                        $query->where(
                            'status',
                            'active'
                        );
                    }
                );


        if (
            $this->option(
                'restaurant'
            )
        ) {
            $restaurantsQuery->where(
                'id',
                (int) $this->option(
                    'restaurant'
                )
            );
        }


        $restaurants =
            $restaurantsQuery
                ->orderBy(
                    'id'
                )
                ->get();


        if ($restaurants->isEmpty()) {

            $this->error(
                'No matching active restaurant tenant was found.'
            );

            return self::FAILURE;
        }


        $tenantFailures =
            0;


        foreach (
            $restaurants
            as $restaurant
        ) {

            try {

                $this->connectionManager
                    ->connect(
                        $restaurant
                    );


                DB::purge(
                    'tenant'
                );

                DB::reconnect(
                    'tenant'
                );

                DB::setDefaultConnection(
                    'tenant'
                );


                $this->tenantManager
                    ->set(
                        $restaurant
                    );


                $admin =
                    User::query()
                        ->with(
                            'role'
                        )
                        ->where(
                            'is_active',
                            true
                        )
                        ->whereNull(
                            'blocked_at'
                        )
                        ->whereHas(
                            'role',
                            function ($query): void {

                                $query->where(
                                    'name',
                                    'admin'
                                );
                            }
                        )
                        ->orderBy(
                            'id'
                        )
                        ->first();


                if (! $admin) {
                    throw new \RuntimeException(
                        'Active tenant Admin account was not found.'
                    );
                }


                $incomeCount =
                    0;

                $expenseCount =
                    0;

                $sourceFailures =
                    0;


                /*
                |--------------------------------------------------------------------------
                | Other Income
                |--------------------------------------------------------------------------
                */

                if (
                    in_array(
                        $type,
                        [
                            'all',
                            'income',
                        ],
                        true
                    )
                ) {

                    $query =
                        Income::query()
                            ->with(
                                'category'
                            )
                            ->orderBy(
                                'id'
                            );


                    if ($from) {
                        $query->whereDate(
                            'income_date',
                            '>=',
                            $from->format(
                                'Y-m-d'
                            )
                        );
                    }


                    if ($to) {
                        $query->whereDate(
                            'income_date',
                            '<=',
                            $to->format(
                                'Y-m-d'
                            )
                        );
                    }


                    $query->chunkById(
                        100,
                        function (
                            $incomes
                        ) use (
                            &$incomeCount,
                            &$sourceFailures,
                            $admin
                        ): void {

                            foreach (
                                $incomes
                                as $income
                            ) {

                                try {

                                    $this->postingService
                                        ->syncIncome(
                                            $income,
                                            $admin,
                                            'Historical Other Income synchronization.'
                                        );

                                    $incomeCount++;

                                } catch (Throwable $exception) {

                                    $sourceFailures++;

                                    report(
                                        $exception
                                    );

                                    $this->warn(
                                        sprintf(
                                            'Income #%d failed: %s',
                                            $income->id,
                                            $exception->getMessage()
                                        )
                                    );
                                }
                            }
                        }
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Expenses
                |--------------------------------------------------------------------------
                |
                | Includes system-generated Salary & Wages Expense.
                |
                */

                if (
                    in_array(
                        $type,
                        [
                            'all',
                            'expense',
                        ],
                        true
                    )
                ) {

                    $query =
                        Expense::query()
                            ->with(
                                'category'
                            )
                            ->orderBy(
                                'id'
                            );


                    if ($from) {
                        $query->whereDate(
                            'expense_date',
                            '>=',
                            $from->format(
                                'Y-m-d'
                            )
                        );
                    }


                    if ($to) {
                        $query->whereDate(
                            'expense_date',
                            '<=',
                            $to->format(
                                'Y-m-d'
                            )
                        );
                    }


                    $query->chunkById(
                        100,
                        function (
                            $expenses
                        ) use (
                            &$expenseCount,
                            &$sourceFailures,
                            $admin
                        ): void {

                            foreach (
                                $expenses
                                as $expense
                            ) {

                                try {

                                    $this->postingService
                                        ->syncExpense(
                                            $expense,
                                            $admin,
                                            'Historical Expense synchronization.'
                                        );

                                    $expenseCount++;

                                } catch (Throwable $exception) {

                                    $sourceFailures++;

                                    report(
                                        $exception
                                    );

                                    $this->warn(
                                        sprintf(
                                            'Expense #%d failed: %s',
                                            $expense->id,
                                            $exception->getMessage()
                                        )
                                    );
                                }
                            }
                        }
                    );
                }


                $this->info(
                    sprintf(
                        '%s | income synced=%d | expense synced=%d | source failures=%d',
                        $restaurant->name,
                        $incomeCount,
                        $expenseCount,
                        $sourceFailures
                    )
                );


                if (
                    $sourceFailures > 0
                ) {
                    $tenantFailures++;
                }

            } catch (Throwable $exception) {

                $tenantFailures++;


                report(
                    $exception
                );


                $this->error(
                    sprintf(
                        '%s failed: %s',
                        $restaurant->name,
                        $exception->getMessage()
                    )
                );

            } finally {

                try {

                    DB::setDefaultConnection(
                        'central'
                    );


                    $this->tenantManager
                        ->clear();


                    $this->connectionManager
                        ->disconnect();

                } catch (Throwable) {
                    //
                }
            }
        }


        return $tenantFailures > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
