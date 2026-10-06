<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'reservation_tables',
            function (Blueprint $table): void {

                $table->boolean(
                    'is_primary'
                )
                    ->default(false)
                    ->after(
                        'restaurant_table_id'
                    );

                $table->index([
                    'reservation_id',
                    'is_primary',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'reservation_tables',
            function (Blueprint $table): void {

                $table->dropIndex([
                    'reservation_id',
                    'is_primary',
                ]);

                $table->dropColumn(
                    'is_primary'
                );
            }
        );
    }
};