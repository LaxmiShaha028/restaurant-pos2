<?php

namespace App\Console\Commands;

use App\Models\Central\Restaurant;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnSettlement;
use App\Models\SupplierCreditApplication;
use App\Models\User;
use App\Services\PurchaseReturnAccountingPostingService;
use App\Services\Tenant\TenantConnectionManager;
use App\Services\Tenant\TenantManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncTenantPurchaseReturnAccounting extends Command
{
    protected $signature =
        'accounting:sync-purchase-returns
        {--restaurant= : Optional central Restaurant ID. Omit to process all active tenants.}';


    protected $description =
        'Safely synchronize Purchase Returns, settlements and Supplier Credit applications into the double-entry journal.';


    public function __construct(
        private readonly TenantConnectionManager $connectionManager,
        private readonly TenantManager $tenantManager,
        private readonly PurchaseReturnAccountingPostingService $postingService
    ) {
        parent::__construct();
    }


    public function handle(): int
    {
        DB::setDefaultConnection(
            'central'
        );

        $query =
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
                    function ($tenantQuery): void {
                        $tenantQuery->where(
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
            $query->where(
                'id',
                (int) $this->option(
                    'restaurant'
                )
            );
        }

        $restaurants =
            $query
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

        $failedTenants =
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
                        'Active tenant Admin account was not found.'
                    );
                }

                $returnCount =
                    0;

                $settlementCount =
                    0;

                $applicationCount =
                    0;

                $failures =
                    0;

                PurchaseReturn::query()
                    ->orderBy(
                        'id'
                    )
                    ->chunkById(
                        100,
                        function (
                            $returns
                        ) use (
                            $admin,
                            &$returnCount,
                            &$failures
                        ): void {
                            foreach (
                                $returns
                                as $purchaseReturn
                            ) {
                                try {
                                    $this->postingService
                                        ->syncReturn(
                                            $purchaseReturn,
                                            $admin
                                        );

                                    $returnCount++;

                                } catch (Throwable $exception) {
                                    $failures++;

                                    report(
                                        $exception
                                    );

                                    $this->warn(
                                        sprintf(
                                            'Purchase Return #%d failed: %s',
                                            $purchaseReturn->id,
                                            $exception->getMessage()
                                        )
                                    );
                                }
                            }
                        }
                    );

                PurchaseReturnSettlement::query()
                    ->orderBy(
                        'id'
                    )
                    ->chunkById(
                        100,
                        function (
                            $settlements
                        ) use (
                            $admin,
                            &$settlementCount,
                            &$failures
                        ): void {
                            foreach (
                                $settlements
                                as $settlement
                            ) {
                                try {
                                    $this->postingService
                                        ->syncSettlement(
                                            $settlement,
                                            $admin
                                        );

                                    $settlementCount++;

                                } catch (Throwable $exception) {
                                    $failures++;

                                    report(
                                        $exception
                                    );

                                    $this->warn(
                                        sprintf(
                                            'Settlement #%d [%s] failed: %s',
                                            $settlement->id,
                                            $settlement->settlement_type
                                                ?? 'unknown',
                                            $exception->getMessage()
                                        )
                                    );
                                }
                            }
                        }
                    );

                SupplierCreditApplication::query()
                    ->orderBy(
                        'id'
                    )
                    ->chunkById(
                        100,
                        function (
                            $applications
                        ) use (
                            $admin,
                            &$applicationCount,
                            &$failures
                        ): void {
                            foreach (
                                $applications
                                as $application
                            ) {
                                try {
                                    $this->postingService
                                        ->syncSupplierCreditApplication(
                                            $application,
                                            $admin
                                        );

                                    $applicationCount++;

                                } catch (Throwable $exception) {
                                    $failures++;

                                    report(
                                        $exception
                                    );

                                    $this->warn(
                                        sprintf(
                                            'Supplier Credit Application #%d failed: %s',
                                            $application->id,
                                            $exception->getMessage()
                                        )
                                    );
                                }
                            }
                        }
                    );

                $this->info(
                    sprintf(
                        '%s | returns=%d | settlements=%d | credit applications=%d | failures=%d',
                        $restaurant->name,
                        $returnCount,
                        $settlementCount,
                        $applicationCount,
                        $failures
                    )
                );

                if (
                    $failures >
                    0
                ) {
                    $failedTenants++;
                }

            } catch (Throwable $exception) {
                $failedTenants++;

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

        return $failedTenants > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
