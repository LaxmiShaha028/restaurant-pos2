<?php

namespace App\Console\Commands;

use App\Models\Central\Restaurant;
use App\Services\PurchaseReturnAccountingReconciliationService;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;


class CheckTenantPurchaseReturnAccounting extends Command
{
    protected $signature =
        'tenants:purchase-return-check
        {--tenant= : Check only one tenant key}';


    protected $description =
        'Verify Purchase Return settlement, Supplier Credit and Purchase Order financial invariants for tenant databases.';


    public function __construct(
        private readonly TenantConnectionManager $connectionManager,
        private readonly PurchaseReturnAccountingReconciliationService $reconciliationService
    ) {
        parent::__construct();
    }


    public function handle(): int
    {
        DB::setDefaultConnection(
            'central'
        );


        $tenantKey =
            trim(
                (string) $this->option(
                    'tenant'
                )
            );


        $query =
            Restaurant::on('central')
                ->with([
                    'tenantDatabase',
                ])
                ->whereHas(
                    'tenantDatabase'
                )
                ->orderBy(
                    'id'
                );


        if ($tenantKey !== '') {
            $query->where(
                'tenant_key',
                $tenantKey
            );
        }


        $restaurants =
            $query->get();


        if ($restaurants->isEmpty()) {
            $this->error(
                $tenantKey !== ''
                    ? "No tenant database found for tenant key: {$tenantKey}"
                    : 'No tenant databases were found.'
            );

            return self::FAILURE;
        }


        $failedTenants =
            0;


        foreach (
            $restaurants
            as
            $restaurant
        ) {

            $tenantDatabase =
                $restaurant->tenantDatabase;


            $databaseName =
                $tenantDatabase?->database_name
                ??
                'unknown';


            $this->line(
                "→ {$restaurant->name} | {$restaurant->tenant_key} | {$databaseName}"
            );


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


                $actualDatabase =
                    DB::connection(
                        'tenant'
                    )
                        ->getDatabaseName();


                if ($actualDatabase !== $databaseName) {
                    throw new \RuntimeException(
                        "Tenant database safety check failed. Expected [{$databaseName}] but connected to [{$actualDatabase}]."
                    );
                }


                $result =
                    $this->reconciliationService
                        ->checkCurrentTenant();


                if ($result['matches']) {
                    $this->info(
                        "  OK — {$result['checks_performed']} financial invariant check(s) passed."
                    );
                } else {
                    $failedTenants++;

                    $this->error(
                        "  MISMATCH — {$result['mismatch_count']} financial invariant(s) require attention."
                    );


                    foreach (
                        $result['rows']
                        as
                        $row
                    ) {

                        if ($row['matches']) {
                            continue;
                        }


                        $expected =
                            $row['expected'] ===
                            null
                                ? 'null'
                                : (string) $row['expected'];


                        $actual =
                            $row['actual'] ===
                            null
                                ? 'null'
                                : (string) $row['actual'];


                        $this->line(
                            "       {$row['check']} | {$row['reference']} | Expected {$expected} | Actual {$actual}"
                        );
                    }
                }
            } catch (Throwable $exception) {
                $failedTenants++;

                $this->error(
                    '  FAILED — '
                    .
                    $exception->getMessage()
                );
            } finally {
                try {
                    DB::setDefaultConnection(
                        'central'
                    );

                    $this->connectionManager
                        ->disconnect();
                } catch (Throwable) {
                }
            }


            $this->newLine();
        }


        if ($failedTenants > 0) {
            $this->error(
                "Purchase Return accounting check finished with {$failedTenants} tenant(s) requiring attention."
            );

            return self::FAILURE;
        }


        $this->info(
            'Purchase Return accounting check completed successfully for all selected tenants.'
        );


        return self::SUCCESS;
    }
}
