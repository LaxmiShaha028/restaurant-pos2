<?php

namespace App\Console\Commands;

use App\Models\Central\Restaurant;
use App\Services\Tenant\TenantConnectionManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class MigrateTenants extends Command
{
    protected $signature = 'tenants:migrate
        {--tenant= : Run only for one tenant key}
        {--path=database/migrations/tenant : Tenant migration directory}';

    protected $description =
        'Run pending tenant migrations against every existing restaurant tenant database.';

    public function __construct(
        private readonly TenantConnectionManager $connectionManager
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        DB::setDefaultConnection('central');

        $tenantKey = trim((string) $this->option('tenant'));
        $path = trim((string) $this->option('path'));

        if ($path === '') {
            $path = 'database/migrations/tenant';
        }

        $query = Restaurant::on('central')
            ->with(['tenantDatabase'])
            ->whereHas('tenantDatabase')
            ->orderBy('id');

        if ($tenantKey !== '') {
            $query->where('tenant_key', $tenantKey);
        }

        $restaurants = $query->get();

        if ($restaurants->isEmpty()) {
            $this->error(
                $tenantKey !== ''
                    ? "No restaurant with a tenant database was found for tenant key: {$tenantKey}"
                    : 'No restaurant tenant databases were found.'
            );

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Tenant migration started.');
        $this->line("Migration path: {$path}");
        $this->line('Tenant databases found: ' . $restaurants->count());
        $this->newLine();

        $successCount = 0;
        $failedCount = 0;

        foreach ($restaurants as $restaurant) {
            $tenantDatabase = $restaurant->tenantDatabase;

            if (!$tenantDatabase) {
                $failedCount++;
                $this->error(
                    "[SKIPPED] {$restaurant->name} ({$restaurant->tenant_key}) — tenant database configuration missing."
                );
                continue;
            }

            $expectedDatabase = trim((string) $tenantDatabase->database_name);

            $this->line(
                "→ {$restaurant->name} | {$restaurant->tenant_key} | {$expectedDatabase}"
            );

            try {
                $this->connectionManager->connect($restaurant);

                DB::purge('tenant');
                DB::reconnect('tenant');
                DB::setDefaultConnection('tenant');

                $actualDatabase = (string) DB::connection('tenant')->getDatabaseName();

                if ($actualDatabase !== $expectedDatabase) {
                    throw new RuntimeException(
                        "Tenant database safety check failed. Expected [{$expectedDatabase}] but connected to [{$actualDatabase}]."
                    );
                }

                $exitCode = Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path' => $path,
                    '--force' => true,
                ]);

                $migrationOutput = trim(Artisan::output());

                if ($exitCode !== 0) {
                    throw new RuntimeException(
                        $migrationOutput !== ''
                            ? $migrationOutput
                            : 'Tenant migration command failed.'
                    );
                }

                DB::setDefaultConnection('central');

                $tenantDatabase->forceFill([
                    'migrated_at' => now(),
                    'last_error' => null,
                ])->save();

                $successCount++;

                $this->info("  DONE — {$expectedDatabase}");

                if ($migrationOutput !== '') {
                    foreach (preg_split('/\r\n|\r|\n/', $migrationOutput) as $line) {
                        $line = trim((string) $line);

                        if ($line !== '') {
                            $this->line("       {$line}");
                        }
                    }
                }
            } catch (Throwable $exception) {
                $failedCount++;

                try {
                    DB::setDefaultConnection('central');

                    $tenantDatabase->forceFill([
                        'last_error' => $exception->getMessage(),
                    ])->save();
                } catch (Throwable) {
                }

                $this->error("  FAILED — {$expectedDatabase}");
                $this->line('       ' . $exception->getMessage());
            } finally {
                try {
                    DB::setDefaultConnection('central');
                    $this->connectionManager->disconnect();
                } catch (Throwable) {
                }
            }

            $this->newLine();
        }

        $this->info('Tenant migration finished.');
        $this->line("Successful: {$successCount}");
        $this->line("Failed: {$failedCount}");

        return $failedCount === 0
            ? self::SUCCESS
            : self::FAILURE;
    }
}
