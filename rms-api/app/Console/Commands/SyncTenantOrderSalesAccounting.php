<?php

namespace App\Console\Commands;

use App\Models\Central\Restaurant;
use App\Models\Order;
use App\Services\SalesAccountingService;
use App\Services\Tenant\TenantConnectionManager;
use App\Services\Tenant\TenantManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncTenantOrderSalesAccounting extends Command
{
    /**
     * Run for all tenant restaurants with an active tenant database,
     * or limit the command to one CENTRAL restaurant ID.
     */
    protected $signature =
        'accounting:sync-order-sales
        {--restaurant= : Central restaurant ID}';

    protected $description =
        'Create or repair accounting journals for historical completed restaurant orders.';

    public function handle(
        TenantConnectionManager $connectionManager,
        TenantManager $tenantManager
    ): int {
        $restaurantOption =
            $this->option('restaurant');

        /*
        |--------------------------------------------------------------------------
        | Central Restaurant Query
        |--------------------------------------------------------------------------
        */

        $restaurants =
            Restaurant::on('central')
                ->with([
                    'tenantDatabase',
                ])
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
            $restaurantOption !== null
            &&
            $restaurantOption !== ''
        ) {
            if (
                ! ctype_digit(
                    (string) $restaurantOption
                )
            ) {
                $this->error(
                    '--restaurant must be a valid CENTRAL restaurant ID.'
                );

                return self::FAILURE;
            }

            $restaurants->whereKey(
                (int) $restaurantOption
            );
        }

        $restaurants =
            $restaurants
                ->orderBy('id')
                ->get();

        if ($restaurants->isEmpty()) {
            $this->warn(
                'No restaurant with an active tenant database was found.'
            );

            return self::SUCCESS;
        }

        /*
        |--------------------------------------------------------------------------
        | Overall Counters
        |--------------------------------------------------------------------------
        */

        $overallProcessed = 0;
        $overallSucceeded = 0;
        $overallFailed = 0;

        /*
        |--------------------------------------------------------------------------
        | Process Each Tenant
        |--------------------------------------------------------------------------
        */

        foreach (
            $restaurants
            as $restaurant
        ) {
            $restaurantProcessed = 0;
            $restaurantSucceeded = 0;
            $restaurantFailed = 0;

            $this->newLine();

            $this->info(
                sprintf(
                    'Restaurant #%d — %s',
                    (int) $restaurant->id,
                    (string) $restaurant->name
                )
            );

            try {
                /*
                |--------------------------------------------------------------------------
                | Connect Tenant Database
                |--------------------------------------------------------------------------
                */

                $connectionManager->connect(
                    $restaurant
                );

                DB::purge('tenant');

                DB::reconnect('tenant');

                DB::setDefaultConnection(
                    'tenant'
                );

                DB::connection('tenant')
                    ->getPdo();

                $tenantManager->set(
                    $restaurant
                );

                /*
                |--------------------------------------------------------------------------
                | Resolve Historical Posting Actor
                |--------------------------------------------------------------------------
                |
                | Prefer the tenant Admin user for backfill.
                | If an individual order has a creator, that creator is passed first.
                |
                */

                $adminRoleId =
                    DB::connection('tenant')
                        ->table('roles')
                        ->where(
                            'name',
                            'admin'
                        )
                        ->value('id');

                $adminUserId = null;

                if ($adminRoleId) {
                    $adminUserId =
                        DB::connection('tenant')
                            ->table('users')
                            ->where(
                                'role_id',
                                $adminRoleId
                            )
                            ->orderBy('id')
                            ->value('id');
                }

                /*
                |--------------------------------------------------------------------------
                | Resolve Service After Tenant Connection
                |--------------------------------------------------------------------------
                */

                /** @var SalesAccountingService $salesAccountingService */
                $salesAccountingService =
                    app(
                        SalesAccountingService::class
                    );

                /*
                |--------------------------------------------------------------------------
                | Historical Completed Orders
                |--------------------------------------------------------------------------
                */

                Order::query()
                    ->where(
                        'status',
                        Order::STATUS_COMPLETED
                    )
                    ->orderBy('id')
                    ->chunkById(
                        100,
                        function (
                            $orders
                        ) use (
                            $salesAccountingService,
                            $adminUserId,
                            &$restaurantProcessed,
                            &$restaurantSucceeded,
                            &$restaurantFailed,
                            &$overallProcessed,
                            &$overallSucceeded,
                            &$overallFailed
                        ): void {
                            foreach (
                                $orders
                                as $order
                            ) {
                                $restaurantProcessed++;
                                $overallProcessed++;

                                try {
                                    /*
                                    |--------------------------------------------------------------------------
                                    | Actor
                                    |--------------------------------------------------------------------------
                                    */

                                    $actorUserId =
                                        $order->created_by
                                        ??
                                        $adminUserId;

                                    if (! $actorUserId) {
                                        throw new \RuntimeException(
                                            'No tenant user is available as the accounting posting actor.'
                                        );
                                    }

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Safe Idempotent Sync
                                    |--------------------------------------------------------------------------
                                    |
                                    | SalesAccountingService ->
                                    | JournalService::syncSource()
                                    |
                                    | Same source + same hash:
                                    | NO duplicate journal.
                                    |
                                    */

                                    $salesAccountingService
                                        ->createSaleJournal(
                                            order:
                                                $order,

                                            userId:
                                                (int) $actorUserId
                                        );

                                    $restaurantSucceeded++;
                                    $overallSucceeded++;

                                } catch (
                                    Throwable $exception
                                ) {
                                    $restaurantFailed++;
                                    $overallFailed++;

                                    $this->error(
                                        sprintf(
                                            'Order #%d (%s) failed: %s',
                                            (int) $order->id,
                                            (string) $order->order_number,
                                            $exception->getMessage()
                                        )
                                    );

                                    report(
                                        $exception
                                    );
                                }
                            }
                        }
                    );

                /*
                |--------------------------------------------------------------------------
                | Restaurant Summary
                |--------------------------------------------------------------------------
                */

                $this->line(
                    sprintf(
                        '%s | processed=%d | synced=%d | failures=%d',
                        (string) $restaurant->name,
                        $restaurantProcessed,
                        $restaurantSucceeded,
                        $restaurantFailed
                    )
                );

            } catch (
                Throwable $exception
            ) {
                $overallFailed++;

                $this->error(
                    sprintf(
                        'Restaurant #%d (%s) could not be processed: %s',
                        (int) $restaurant->id,
                        (string) $restaurant->name,
                        $exception->getMessage()
                    )
                );

                report(
                    $exception
                );

            } finally {
                /*
                |--------------------------------------------------------------------------
                | Restore Central Connection
                |--------------------------------------------------------------------------
                */

                try {
                    DB::setDefaultConnection(
                        'central'
                    );

                    $tenantManager->clear();

                    $connectionManager->disconnect();

                } catch (
                    Throwable $cleanupException
                ) {
                    report(
                        $cleanupException
                    );
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Final Summary
        |--------------------------------------------------------------------------
        */

        $this->newLine();

        $this->info(
            sprintf(
                'Order sales accounting sync complete | processed=%d | synced=%d | failures=%d',
                $overallProcessed,
                $overallSucceeded,
                $overallFailed
            )
        );

        return $overallFailed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
