<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')
            ->create(
                'reservation_tables',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'reservation_id'
                    )
                        ->constrained(
                            'reservations'
                        )
                        ->cascadeOnDelete();

                    $table->foreignId(
                        'restaurant_table_id'
                    )
                        ->constrained(
                            'restaurant_tables'
                        )
                        ->cascadeOnDelete();

                    $table->boolean(
                        'is_primary'
                    )->default(false);

                    $table->timestamps();

                    $table->unique([
                        'reservation_id',
                        'restaurant_table_id',
                    ]);

                    $table->index([
                        'reservation_id',
                        'is_primary',
                    ]);

                    $table->index(
                        'restaurant_table_id'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'reservation_tables'
            );
    }
};