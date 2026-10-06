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
                'raw_materials',
                function (Blueprint $table): void {

                    $table->id();

                    $table->string(
                        'material_name',
                        180
                    )->index();

                    $table->string(
                        'category',
                        100
                    )
                        ->nullable()
                        ->index();

                    $table->string(
                        'base_unit',
                        30
                    );

                    $table->decimal(
                        'warehouse_minimum_quantity',
                        18,
                        4
                    )
                        ->unsigned()
                        ->default(0);

                    $table->decimal(
                        'restaurant_minimum_quantity',
                        18,
                        4
                    )
                        ->unsigned()
                        ->default(0);

                    $table->boolean(
                        'is_active'
                    )
                        ->default(true)
                        ->index();

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

                    $table->index([
                        'material_name',
                        'base_unit',
                        'is_active',
                    ]);
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists('raw_materials');
    }
};