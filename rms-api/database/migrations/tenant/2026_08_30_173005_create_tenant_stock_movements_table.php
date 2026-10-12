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
                'stock_movements',
                function (Blueprint $table): void {

                    $table->id();

                    /*
                    |--------------------------------------------------------------------------
                    | Raw Material
                    |--------------------------------------------------------------------------
                    */

                    $table->foreignId(
                        'raw_material_id'
                    )
                        ->constrained(
                            'raw_materials'
                        )
                        ->restrictOnDelete();

                    /*
                    |--------------------------------------------------------------------------
                    | Location
                    |--------------------------------------------------------------------------
                    */

                    $table->string(
                        'location',
                        30
                    )->index();

                    /*
                    |--------------------------------------------------------------------------
                    | Movement Type
                    |--------------------------------------------------------------------------
                    */

                    $table->string(
                        'movement_type',
                        50
                    )->index();

                    /*
                    |--------------------------------------------------------------------------
                    | Quantity
                    |--------------------------------------------------------------------------
                    */

                    $table->decimal(
                        'quantity',
                        18,
                        4
                    )->unsigned();

                    $table->decimal(
                        'quantity_before',
                        18,
                        4
                    )->unsigned()->default(0);

                    $table->decimal(
                        'quantity_after',
                        18,
                        4
                    )->unsigned()->default(0);

                    /*
                    |--------------------------------------------------------------------------
                    | Cost Snapshot
                    |--------------------------------------------------------------------------
                    */

                    $table->decimal(
                        'unit_cost',
                        18,
                        4
                    )->unsigned()->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Polymorphic Reference
                    |--------------------------------------------------------------------------
                    */

                    $table->string(
                        'reference_type',
                        100
                    )->nullable()->index();

                    $table->unsignedBigInteger(
                        'reference_id'
                    )->nullable()->index();

                    /*
                    |--------------------------------------------------------------------------
                    | Unit
                    |--------------------------------------------------------------------------
                    */

                    $table->string(
                        'unit',
                        30
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Notes
                    |--------------------------------------------------------------------------
                    */

                    $table->text(
                        'notes'
                    )->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Audit
                    |--------------------------------------------------------------------------
                    */

                    $table->foreignId(
                        'created_by'
                    )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete();

                    $table->timestamps();

                    /*
                    |--------------------------------------------------------------------------
                    | Indexes
                    |--------------------------------------------------------------------------
                    */

                    $table->index(
                        [
                            'raw_material_id',
                            'location',
                            'movement_type',
                        ],
                        'stock_movements_material_location_type_index'
                    );

                    $table->index(
                        [
                            'reference_type',
                            'reference_id',
                        ],
                        'stock_movements_reference_index'
                    );

                    $table->index(
                        [
                            'raw_material_id',
                            'created_at',
                        ],
                        'stock_movements_material_date_index'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'stock_movements'
            );
    }
};