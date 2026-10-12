<?php

namespace App\Console\Commands;

use App\Models\Central\Restaurant;
use App\Services\Tenant\TenantConnectionManager;
use Database\Seeders\Tenant\ReservationsPaginationTestSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class SeedReservationPaginationTestData extends Command
{
    protected $signature = 'tenants:seed-reservation-test-data {tenant_key : Restaurant tenant key}';

    protected $description = 'Replace the reservation pagination test data with ten test reservations for one tenant.';

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

            if (! Schema::connection('tenant')->hasTable('reservations')) {
                throw new RuntimeException(
                    "Required table [reservations] is missing in tenant database [{$actualDatabase}]. Run tenant migrations first."
                );
            }

            $count = app(ReservationsPaginationTestSeeder::class)->run();
            $this->info("Seeded {$count} reservation pagination test records for {$restaurant->name} in {$actualDatabase}.");
            $this->line('Running this command again replaces only reservations whose number starts with TEST-RES-.');

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
