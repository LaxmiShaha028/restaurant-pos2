<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'orders',
            function (Blueprint $table): void {

                $table->foreignId(
                    'reservation_id'
                )
                    ->nullable()
                    ->after('restaurant_table_id')
                    ->constrained(
                        'reservations'
                    )
                    ->nullOnDelete();

                $table->index(
                    'reservation_id'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'orders',
            function (Blueprint $table): void {

                $table->dropForeign([
                    'reservation_id',
                ]);

                $table->dropIndex([
                    'reservation_id',
                ]);

                $table->dropColumn(
                    'reservation_id'
                );
            }
        );
    }
};