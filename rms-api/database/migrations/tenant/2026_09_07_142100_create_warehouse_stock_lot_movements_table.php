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
                'warehouse_stock_lot_movements',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'warehouse_stock_lot_id'
                    )
                        ->constrained(
                            'warehouse_stock_lots'
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
                    | Movement
                    |--------------------------------------------------------------------------
                    |
                    | Prepared movement types:
                    |
                    | legacy_opening
                    | purchase_receive
                    | warehouse_adjustment_in
                    | warehouse_adjustment_out
                    | transfer_out
                    | purchase_return_out
                    |
                    */

                    $table->string(
                        'movement_type',
                        60
                    );

                    $table->string(
                        'direction',
                        10
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Quantity Audit
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
                    )->unsigned();

                    $table->decimal(
                        'quantity_after',
                        18,
                        4
                    )->unsigned();


                    /*
                    |--------------------------------------------------------------------------
                    | Historical Cost Snapshot
                    |--------------------------------------------------------------------------
                    */

                    $table->decimal(
                        'unit_cost',
                        18,
                        4
                    )->unsigned();

                    $table->string(
                        'unit',
                        30
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Polymorphic Business Reference
                    |--------------------------------------------------------------------------
                    |
                    | Example references:
                    |
                    | PurchaseOrderReceipt
                    | StockTransfer
                    | PurchaseReturn
                    | WarehouseStock
                    |
                    */

                    $table->string(
                        'reference_type',
                        255
                    )->nullable();

                    $table->unsignedBigInteger(
                        'reference_id'
                    )->nullable();


                    /*
                    |--------------------------------------------------------------------------
                    | Audit
                    |--------------------------------------------------------------------------
                    */

                    $table->timestamp(
                        'occurred_at'
                    );

                    $table->text(
                        'notes'
                    )->nullable();

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
                            'warehouse_stock_lot_id',
                            'occurred_at',
                        ],
                        'warehouse_lot_movement_lot_date_index'
                    );

                    $table->index(
                        [
                            'raw_material_id',
                            'occurred_at',
                        ],
                        'warehouse_lot_movement_material_date_index'
                    );

                    $table->index(
                        'movement_type'
                    );

                    $table->index(
                        [
                            'reference_type',
                            'reference_id',
                        ],
                        'warehouse_lot_movement_reference_index'
                    );
                }
            );
    }


    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'warehouse_stock_lot_movements'
            );
    }
};
