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
                'order_items',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'order_id'
                    )
                        ->constrained(
                            'orders'
                        )
                        ->cascadeOnDelete();

                    $table->foreignId(
                        'order_kitchen_batch_id'
                    )
                        ->constrained(
                            'order_kitchen_batches'
                        )
                        ->restrictOnDelete();

                    $table->foreignId(
                        'menu_item_id'
                    )
                        ->nullable()
                        ->constrained(
                            'menu_items'
                        )
                        ->nullOnDelete();

                    $table->foreignId(
                        'menu_item_variant_id'
                    )
                        ->nullable()
                        ->constrained(
                            'menu_item_variants'
                        )
                        ->nullOnDelete();

                    /*
                    |--------------------------------------------------------------------------
                    | Snapshot
                    |--------------------------------------------------------------------------
                    */

                    $table->string(
                        'item_name',
                        180
                    );

                    $table->string(
                        'variant_name',
                        150
                    )->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Pricing
                    |--------------------------------------------------------------------------
                    */

                    $table->decimal(
                        'unit_price',
                        15,
                        2
                    )->default(0);

                    $table->unsignedInteger(
                        'quantity'
                    )->default(1);

                    $table->decimal(
                        'addon_total',
                        15,
                        2
                    )->default(0);

                    $table->decimal(
                        'line_total',
                        15,
                        2
                    )->default(0);

                    /*
                    |--------------------------------------------------------------------------
                    | Kitchen
                    |--------------------------------------------------------------------------
                    */

                    $table->string(
                        'status',
                        30
                    )->default('pending');

                    $table->text(
                        'kitchen_note'
                    )->nullable();

                    $table->timestamps();

                    $table->index(
                        'status'
                    );

                    $table->index([
                        'order_id',
                        'menu_item_id',
                    ]);

                    $table->index([
                        'order_kitchen_batch_id',
                        'status',
                    ], 'order_items_batch_status_index');
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'order_items'
            );
    }
};