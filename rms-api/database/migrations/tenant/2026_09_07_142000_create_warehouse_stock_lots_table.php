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
                'warehouse_stock_lots',
                function (Blueprint $table): void {

                    $table->id();

                    /*
                    |--------------------------------------------------------------------------
                    | Human Readable Lot Number
                    |--------------------------------------------------------------------------
                    */

                    $table->string(
                        'lot_no',
                        80
                    )->unique();


                    /*
                    |--------------------------------------------------------------------------
                    | Material
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
                    | Source Classification
                    |--------------------------------------------------------------------------
                    |
                    | legacy_opening
                    | purchase_receive
                    | warehouse_adjustment_in
                    |
                    | More source types can be added later without schema change.
                    |
                    */

                    $table->string(
                        'source_type',
                        60
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Original Supplier / Purchase Source
                    |--------------------------------------------------------------------------
                    |
                    | These nullable links are intentionally included from day one.
                    | They will later power:
                    |
                    | - Purchase Return
                    | - Supplier Ledger
                    | - Original PO / GRN traceability
                    | - Printable Purchase Return PDF
                    |
                    */

                    $table->foreignId(
                        'supplier_id'
                    )
                        ->nullable()
                        ->constrained(
                            'suppliers'
                        )
                        ->nullOnDelete();

                    $table->foreignId(
                        'purchase_order_id'
                    )
                        ->nullable()
                        ->constrained(
                            'purchase_orders'
                        )
                        ->nullOnDelete();

                    $table->foreignId(
                        'purchase_order_receipt_id'
                    )
                        ->nullable()
                        ->constrained(
                            'purchase_order_receipts'
                        )
                        ->nullOnDelete();

                    $table->foreignId(
                        'purchase_order_receipt_item_id'
                    )
                        ->nullable()
                        ->constrained(
                            'purchase_order_receipt_items'
                        )
                        ->nullOnDelete();


                    /*
                    |--------------------------------------------------------------------------
                    | Supplier Batch / Expiry
                    |--------------------------------------------------------------------------
                    */

                    $table->string(
                        'supplier_batch_no',
                        120
                    )->nullable();

                    $table->date(
                        'manufactured_at'
                    )->nullable();

                    $table->date(
                        'expiry_date'
                    )->nullable();


                    /*
                    |--------------------------------------------------------------------------
                    | Quantity Snapshot
                    |--------------------------------------------------------------------------
                    */

                    $table->decimal(
                        'received_quantity',
                        18,
                        4
                    )->unsigned();

                    $table->decimal(
                        'remaining_quantity',
                        18,
                        4
                    )->unsigned();


                    /*
                    |--------------------------------------------------------------------------
                    | Original Unit Cost Snapshot
                    |--------------------------------------------------------------------------
                    |
                    | Purchase Return must use the original lot cost,
                    | not the current weighted average warehouse cost.
                    |
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
                    | Dates / Notes
                    |--------------------------------------------------------------------------
                    */

                    $table->timestamp(
                        'received_at'
                    )->nullable();

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

                    $table->foreignId(
                        'updated_by'
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
                            'expiry_date',
                        ],
                        'warehouse_lot_material_expiry_index'
                    );

                    $table->index(
                        [
                            'raw_material_id',
                            'remaining_quantity',
                        ],
                        'warehouse_lot_material_remaining_index'
                    );

                    $table->index(
                        [
                            'supplier_id',
                            'raw_material_id',
                        ],
                        'warehouse_lot_supplier_material_index'
                    );

                    $table->index(
                        'purchase_order_id'
                    );

                    $table->index(
                        'purchase_order_receipt_id'
                    );

                    $table->index(
                        'purchase_order_receipt_item_id'
                    );

                    $table->index(
                        'source_type'
                    );
                }
            );
    }


    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'warehouse_stock_lots'
            );
    }
};
