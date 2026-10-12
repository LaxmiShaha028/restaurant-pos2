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
                'purchase_order_items',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'purchase_order_id'
                    )
                        ->constrained(
                            'purchase_orders'
                        )
                        ->cascadeOnDelete();

                    $table->foreignId(
                        'raw_material_id'
                    )
                        ->nullable()
                        ->constrained(
                            'raw_materials'
                        )
                        ->restrictOnDelete();

                    $table->string(
                        'item_name'
                    );

                    $table->string(
                        'unit',
                        30
                    );

                    $table->decimal(
                        'quantity',
                        12,
                        2
                    )->default(0);

                    $table->decimal(
                        'received_quantity',
                        12,
                        2
                    )->default(0);

                    $table->decimal(
                        'unit_price',
                        12,
                        2
                    )->default(0);

                    $table->decimal(
                        'total_price',
                        14,
                        2
                    )->default(0);

                    $table->timestamps();

                    $table->index([
                        'raw_material_id',
                        'purchase_order_id',
                    ]);
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'purchase_order_items'
            );
    }
};