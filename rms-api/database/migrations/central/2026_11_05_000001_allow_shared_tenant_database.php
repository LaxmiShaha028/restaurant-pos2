<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = Schema::connection('central')->getIndexes('tenant_databases');

        foreach ($indexes as $index) {
            if (($index['unique'] ?? false) && ($index['columns'] ?? []) === ['database_name']) {
                Schema::connection('central')->table('tenant_databases', function (Blueprint $table) use ($index): void {
                    $table->dropUnique($index['name']);
                });
            }
        }

        $hasDatabaseNameIndex = collect($indexes)->contains(
            fn (array $index): bool => ! ($index['unique'] ?? false)
                && ($index['columns'] ?? []) === ['database_name']
        );

        if (! $hasDatabaseNameIndex) {
            Schema::connection('central')->table('tenant_databases', function (Blueprint $table): void {
                $table->index('database_name');
            });
        }
    }

    public function down(): void
    {
        $databaseNames = Schema::connection('central')->getConnection()
            ->table('tenant_databases')
            ->select('database_name')
            ->groupBy('database_name')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($databaseNames) {
            return;
        }

        $indexes = Schema::connection('central')->getIndexes('tenant_databases');
        foreach ($indexes as $index) {
            if (! ($index['unique'] ?? false) && ($index['columns'] ?? []) === ['database_name']) {
                Schema::connection('central')->table('tenant_databases', function (Blueprint $table) use ($index): void {
                    $table->dropIndex($index['name']);
                });
            }
        }

        Schema::connection('central')->table('tenant_databases', function (Blueprint $table): void {
            $table->unique('database_name');
        });
    }
};
