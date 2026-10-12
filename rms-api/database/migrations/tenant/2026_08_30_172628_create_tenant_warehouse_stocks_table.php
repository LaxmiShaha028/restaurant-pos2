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
                'warehouse_stocks',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'raw_material_id'
                    )
                        ->constrained(
                            'raw_materials'
                        )
                        ->restrictOnDelete();

                    $table->decimal(
                        'quantity',
                        18,
                        4
                    )
                        ->unsigned()
                        ->default(0);

                    $table->decimal(
                        'average_unit_cost',
                        18,
                        4
                    )
                        ->unsigned()
                        ->default(0);

                    $table->timestamp(
                        'last_received_at'
                    )->nullable();

                    $table->foreignId(
                        'created_by'
                    )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete();

                    $table->foreignId(
                        'updated_by'
                    )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete();

                    $table->timestamps();

                    $table->softDeletes();

                    $table->unique(
                        'raw_material_id',
                        'warehouse_stocks_raw_material_unique'
                    );

                    $table->index([
                        'quantity',
                        'last_received_at',
                    ]);
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists('warehouse_stocks');
    }
};