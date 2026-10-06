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
                'inventory_wastage_items',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'inventory_wastage_id'
                    )
                        ->constrained(
                            'inventory_wastages'
                        )
                        ->restrictOnDelete();

                    $table->foreignId(
                        'raw_material_id'
                    )
                        ->constrained(
                            'raw_materials'
                        )
                        ->restrictOnDelete();

                    /*
                    |--------------------------------------------------------------------------
                    | Historical Snapshot
                    |--------------------------------------------------------------------------
                    */

                    $table->string(
                        'item_name',
                        255
                    );

                    $table->string(
                        'unit',
                        30
                    );

                    $table->decimal(
                        'quantity',
                        18,
                        4
                    )->unsigned();

                    $table->decimal(
                        'unit_cost',
                        18,
                        4
                    )->unsigned();

                    $table->decimal(
                        'total_loss',
                        18,
                        4
                    )->unsigned();

                    /*
                    |--------------------------------------------------------------------------
                    | Stock Snapshot
                    |--------------------------------------------------------------------------
                    */

                    $table->decimal(
                        'restaurant_quantity_before',
                        18,
                        4
                    )->unsigned();

                    $table->decimal(
                        'restaurant_quantity_after',
                        18,
                        4
                    )->unsigned();

                    /*
                    |--------------------------------------------------------------------------
                    | Reason
                    |--------------------------------------------------------------------------
                    */

                    $table->string(
                        'reason',
                        60
                    );

                    $table->text(
                        'notes'
                    )->nullable();

                    $table->timestamps();

                    /*
                    |--------------------------------------------------------------------------
                    | Indexes
                    |--------------------------------------------------------------------------
                    */

                    $table->index(
                        'raw_material_id'
                    );

                    $table->index(
                        'reason'
                    );

                    $table->index(
                        [
                            'inventory_wastage_id',
                            'raw_material_id',
                        ],
                        'wastage_item_header_material_index'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'inventory_wastage_items'
            );
    }
};
