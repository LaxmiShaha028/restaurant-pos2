<?php

namespace App\Console\Commands;

use App\Models\Central\Restaurant;
use App\Services\Tenant\TenantConnectionManager;
use Database\Seeders\Tenant\RealMenuCatalogSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class SeedRealMenuCatalogs extends Command
{
    protected $signature = 'tenants:seed-real-menus';
    protected $description = 'Idempotently add real dishes to provisioned restaurant tenants.';

    public function __construct(private readonly TenantConnectionManager $connectionManager)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        DB::setDefaultConnection('central');
        $restaurants = Restaurant::on('central')->with('tenantDatabase')->whereHas('tenantDatabase')->orderBy('id')->get();
        $failures = 0;

        foreach ($restaurants as $restaurant) {
            try {
                $this->connectionManager->connect($restaurant);
                $expected = config('tenancy.mode') === 'single_database'
                    ? (string) config('database.connections.central.database')
                    : (string) $restaurant->tenantDatabase->database_name;
                $actual = (string) DB::connection('tenant')->getDatabaseName();
                if ($expected === '' || $actual !== $expected) {
                    throw new RuntimeException("Tenant database safety check failed for {$restaurant->tenant_key}.");
                }
                foreach (['menu_categories', 'menu_items'] as $table) {
                    if (!Schema::connection('tenant')->hasTable($table)) {
                        throw new RuntimeException("Tenant menu table [{$table}] is missing.");
                    }
                }
                $count = app(RealMenuCatalogSeeder::class)->run();
                $this->info("{$restaurant->name}: {$count} new dishes added.");
            } catch (Throwable $exception) {
                $failures++;
                $this->error("{$restaurant->name}: {$exception->getMessage()}");
            } finally {
                DB::setDefaultConnection('central');
                $this->connectionManager->disconnect();
            }
        }

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }
}
