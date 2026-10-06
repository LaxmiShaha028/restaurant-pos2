<?php

namespace App\Console\Commands;

use App\Models\Central\Restaurant;
use App\Models\User;
use App\Services\SalaryService;
use App\Services\Tenant\TenantConnectionManager;
use App\Services\Tenant\TenantManager;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessTenantDailySalaries extends Command
{
    protected $signature =
        'salary:process-tenants
        {--date= : Salary date in Y-m-d format. Defaults to yesterday.}';


    protected $description =
        'Process daily salary for every active restaurant tenant.';


    public function __construct(
        private readonly TenantConnectionManager $connectionManager,
        private readonly TenantManager $tenantManager,
        private readonly SalaryService $salaryService
    ) {
        parent::__construct();
    }


    public function handle(): int
    {
        /*
        |--------------------------------------------------------------------------
        | Salary Date
        |--------------------------------------------------------------------------
        |
        | At 00:10 on 09 Sep, the system finalizes 08 Sep.
        |
        */

        try {

            $date =
                $this->option(
                    'date'
                )
                    ? Carbon::parse(
                        (string) $this->option(
                            'date'
                        )
                    )->startOfDay()
                    : today()
                        ->subDay()
                        ->startOfDay();

        } catch (Throwable $exception) {

            $this->error(
                'Invalid --date value. Use Y-m-d.'
            );

            return self::FAILURE;
        }


        if ($date->gt(today())) {

            $this->error(
                'Salary date cannot be in the future.'
            );

            return self::FAILURE;
        }


        /*
        |--------------------------------------------------------------------------
        | Active Restaurants
        |--------------------------------------------------------------------------
        */

        DB::setDefaultConnection(
            'central'
        );


        $restaurants =
            Restaurant::on('central')
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
                )
                ->orderBy(
                    'id'
                )
                ->get();


        $processed =
            0;

        $failed =
            0;


        foreach (
            $restaurants
            as $restaurant
        ) {

            try {

                /*
                |--------------------------------------------------------------------------
                | Connect Tenant
                |--------------------------------------------------------------------------
                */

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


                /*
                |--------------------------------------------------------------------------
                | Automation Actor
                |--------------------------------------------------------------------------
                |
                | Existing salary rows require created_by / updated_by.
                | Use the active tenant Admin account as the system actor.
                |
                */

                $admin =
                    User::query()
                        ->with([
                            'role',
                        ])
                        ->where(
                            'is_active',
                            true
                        )
                        ->whereNull(
                            'blocked_at'
                        )
                        ->whereHas(
                            'role',
                            function ($roleQuery): void {

                                $roleQuery->where(
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
                        'Active tenant admin account was not found.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Idempotent Daily Processing
                |--------------------------------------------------------------------------
                */

                $result =
                    $this->salaryService
                        ->processDailySalary(
                            $date,
                            $admin
                        );


                $processed++;


                $this->info(
                    sprintf(
                        '[%s] %s | employees=%d details_created=%d details_updated=%d skipped_paid=%d',
                        $date->format(
                            'Y-m-d'
                        ),
                        $restaurant->name,
                        (int) (
                            $result[
                                'employees_processed'
                            ]
                            ??
                            0
                        ),
                        (int) (
                            $result[
                                'details_created'
                            ]
                            ??
                            0
                        ),
                        (int) (
                            $result[
                                'details_updated'
                            ]
                            ??
                            0
                        ),
                        (int) (
                            $result[
                                'skipped_paid_payrolls'
                            ]
                            ??
                            0
                        )
                    )
                );

            } catch (Throwable $exception) {

                $failed++;


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

                /*
                |--------------------------------------------------------------------------
                | Always Restore Central Connection
                |--------------------------------------------------------------------------
                */

                try {

                    DB::setDefaultConnection(
                        'central'
                    );


                    $this->tenantManager
                        ->clear();


                    $this->connectionManager
                        ->disconnect();

                } catch (Throwable) {
                    // Keep processing the next tenant.
                }
            }
        }


        $this->newLine();


        $this->line(
            sprintf(
                'Daily salary complete. Processed: %d | Failed: %d',
                $processed,
                $failed
            )
        );


        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
