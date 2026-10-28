<?php

namespace App\Console\Commands;

use App\Models\Central\Restaurant;
use App\Services\Tenant\TenantConnectionManager;
use Database\Seeders\Tenant\TenantDemoDataSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class SeedTenantDemoData extends Command
{
    protected $signature = 'tenants:seed-demo-data {tenant_key : Restaurant tenant key}';

    protected $description = 'Add 100 clearly marked demo records to each visible business module for one tenant.';

    public function __construct(private readonly TenantConnectionManager $connectionManager)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        DB::setDefaultConnection('central');
        $tenantKey = trim((string) $this->argument('tenant_key'));

        try {
            $restaurant = Restaurant::on('central')->with('tenantDatabase')->where('tenant_key', $tenantKey)->first();
            if (!$restaurant || !$restaurant->tenantDatabase) {
                $this->error("Restaurant tenant not found or has no tenant database: {$tenantKey}");

                return self::FAILURE;
            }

            $this->connectionManager->connect($restaurant);
            $expectedDatabase = trim((string) $restaurant->tenantDatabase->database_name);
            $actualDatabase = (string) DB::connection('tenant')->getDatabaseName();
            if ($expectedDatabase === '' || $actualDatabase !== $expectedDatabase) {
                throw new RuntimeException("Tenant database safety check failed. Expected [{$expectedDatabase}], connected to [{$actualDatabase}].");
            }

            $existingDemoOrders = DB::connection('tenant')
                ->table('orders')
                ->where('order_number', 'like', 'DEMO100-ORD-%')
                ->count();

            if ($existingDemoOrders > 0) {
                $updatedMenuItems = app(TenantDemoDataSeeder::class)->refreshExistingMenuItems();
                $this->info("DEMO100 demo data is already present for {$restaurant->name} in {$actualDatabase}.");
                $this->line("- orders preserved: {$existingDemoOrders}");
                $this->line("- demo menu items refreshed: {$updatedMenuItems}");

                return self::SUCCESS;
            }

            $counts = app(TenantDemoDataSeeder::class)->run();
            $this->info("Seeded demo records for {$restaurant->name} in {$actualDatabase}:");
            foreach ($counts as $module => $count) {
                $this->line("- {$module}: {$count}");
            }
            $this->line('All demo rows use the DEMO100- marker. Existing tenant data was preserved.');

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
