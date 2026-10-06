<?php

namespace App\Console\Commands;

use App\Models\Central\Restaurant;
use App\Models\OrderRecipeConsumption;
use App\Models\User;
use App\Services\InventoryConsumptionAccountingPostingService;
use App\Services\Tenant\TenantConnectionManager;
use App\Services\Tenant\TenantManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncTenantRecipeCogsAccounting extends Command
{
    protected $signature =
        'accounting:sync-recipe-cogs
        {--restaurant= : Optional central Restaurant ID. Omit to process all active tenants.}';


    protected $description =
        'Safely synchronize immutable recipe consumption cost into Inventory and COGS journals.';


    public function __construct(
        private readonly TenantConnectionManager $connectionManager,
        private readonly TenantManager $tenantManager,
        private readonly InventoryConsumptionAccountingPostingService $postingService
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


                $synced =
                    0;

                $zeroCost =
                    0;

                $failures =
                    0;


                OrderRecipeConsumption::query()
                    ->orderBy(
                        'id'
                    )
                    ->chunkById(
                        100,
                        function (
                            $consumptions
                        ) use (
                            $admin,
                            &$synced,
                            &$zeroCost,
                            &$failures
                        ): void {

                            foreach (
                                $consumptions
                                as $consumption
                            ) {

                                try {

                                    $journal =
                                        $this->postingService
                                            ->syncRecipeConsumption(
                                                $consumption,
                                                $admin
                                            );


                                    if ($journal) {
                                        $synced++;
                                    } else {
                                        $zeroCost++;
                                    }

                                } catch (Throwable $exception) {

                                    $failures++;


                                    report(
                                        $exception
                                    );


                                    $this->warn(
                                        sprintf(
                                            'Recipe Consumption #%d failed: %s',
                                            $consumption->id,
                                            $exception->getMessage()
                                        )
                                    );
                                }
                            }
                        }
                    );


                $this->info(
                    sprintf(
                        '%s | COGS synced=%d | zero-cost/no-journal=%d | failures=%d',
                        $restaurant->name,
                        $synced,
                        $zeroCost,
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
