<?php

namespace App\Console\Commands;

use App\Models\Central\Restaurant;
use App\Services\Tenant\TenantConnectionManager;
use Database\Seeders\Tenant\KitchenDisplayTestSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class SeedKitchenDisplayTestOrders extends Command
{
    protected $signature = 'tenants:seed-kitchen-test-orders {tenant_key : Restaurant tenant key}';

    protected $description = 'Replace the Kitchen Display test orders with twelve test orders for one tenant.';

    public function __construct(
        private readonly TenantConnectionManager $connectionManager,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        DB::setDefaultConnection('central');
        $tenantKey = trim((string) $this->argument('tenant_key'));

        try {
            $restaurant = Restaurant::on('central')
                ->with('tenantDatabase')
                ->where('tenant_key', $tenantKey)
                ->first();

            if (! $restaurant) {
                $this->error("Restaurant tenant not found: {$tenantKey}");

                return self::FAILURE;
            }

            $this->connectionManager->connect($restaurant);

            $expectedDatabase = trim((string) $restaurant->tenantDatabase?->database_name);
            $actualDatabase = (string) DB::connection('tenant')->getDatabaseName();

            if ($expectedDatabase === '' || $actualDatabase !== $expectedDatabase) {
                throw new RuntimeException(
                    "Tenant database safety check failed. Expected [{$expectedDatabase}], connected to [{$actualDatabase}]."
                );
            }

            foreach (['orders', 'order_items', 'order_kitchen_batches'] as $table) {
                if (! Schema::connection('tenant')->hasTable($table)) {
                    throw new RuntimeException(
                        "Required table [{$table}] is missing in tenant database [{$actualDatabase}]. Run tenant migrations first."
                    );
                }
            }

            $count = app(KitchenDisplayTestSeeder::class)->run();
            $this->info("Seeded {$count} Kitchen Display test orders for {$restaurant->name} in {$actualDatabase}.");
            $this->line('Running this command again replaces only orders whose number starts with TEST-KITCHEN-.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            DB::setDefaultConnection('central');
            $this->connectionManager->disconnect();
        }
    }
}
