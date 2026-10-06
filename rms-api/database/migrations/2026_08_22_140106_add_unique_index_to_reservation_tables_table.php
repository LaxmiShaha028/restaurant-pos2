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
            function (
                Blueprint $table
            ): void {

                $table->unique([
                    'reservation_id',
                    'restaurant_table_id',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'reservation_tables',
            function (
                Blueprint $table
            ): void {

                $table->dropUnique([
                    'reservation_id',
                    'restaurant_table_id',
                ]);
            }
        );
    }
};