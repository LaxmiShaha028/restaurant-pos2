<?php

namespace App\Console\Commands;

use App\Models\Central\Restaurant;
use App\Services\Tenant\TenantConnectionManager;
use Database\Seeders\MenuManagementSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class SeedTenantMenu extends Command
{
    protected $signature = 'tenants:seed-menu {tenant_key : Restaurant tenant key}';

    protected $description = 'Seed the starter restaurant menu into one tenant database.';

    public function __construct(
        private readonly TenantConnectionManager $connectionManager
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

            if (!$restaurant) {
                $this->error("Restaurant tenant not found: {$tenantKey}");

                return self::FAILURE;
            }

            $this->connectionManager->connect($restaurant);

            $expectedDatabase = trim(
                (string) $restaurant->tenantDatabase?->database_name
            );
            $actualDatabase = (string) DB::connection('tenant')->getDatabaseName();

            if ($expectedDatabase === '' || $actualDatabase !== $expectedDatabase) {
                throw new RuntimeException(
                    "Tenant database safety check failed. Expected [{$expectedDatabase}], connected to [{$actualDatabase}]."
                );
            }

            foreach ([
                'menu_categories',
                'menu_items',
                'menu_item_variants',
                'add_ons',
            ] as $table) {
                if (!Schema::connection('tenant')->hasTable($table)) {
                    throw new RuntimeException(
                        "Required table [{$table}] is missing in tenant database [{$actualDatabase}]. Run tenant migrations first."
                    );
                }
            }

            app(MenuManagementSeeder::class)->run();

            $this->info("Starter menu seeded for {$restaurant->name} in {$actualDatabase}.");
            $this->line('Menu categories and non-beef items are ready; beef items, variants, and add-ons are hidden.');

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
