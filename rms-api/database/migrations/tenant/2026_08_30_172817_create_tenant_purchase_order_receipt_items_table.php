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
                'purchase_order_receipt_items',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'purchase_order_receipt_id'
                    )
                        ->constrained(
                            'purchase_order_receipts',
                            indexName: 'pori_receipt_fk'
                        )
                        ->restrictOnDelete();

                    $table->foreignId(
                        'purchase_order_item_id'
                    )
                        ->constrained(
                            'purchase_order_items',
                            indexName: 'pori_po_item_fk'
                        )
                        ->restrictOnDelete();

                    $table->foreignId(
                        'raw_material_id'
                    )
                        ->constrained(
                            'raw_materials',
                            indexName: 'pori_material_fk'
                        )
                        ->restrictOnDelete();

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
                        14,
                        4
                    );

                    $table->decimal(
                        'unit_cost',
                        14,
                        4
                    );

                    $table->decimal(
                        'total_cost',
                        18,
                        4
                    );

                    $table->text(
                        'notes'
                    )->nullable();

                    $table->timestamps();

                    $table->unique(
                        [
                            'purchase_order_receipt_id',
                            'purchase_order_item_id',
                        ],
                        'po_receipt_item_unique'
                    );

                    $table->index(
                        'raw_material_id'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'purchase_order_receipt_items'
            );
    }
};
