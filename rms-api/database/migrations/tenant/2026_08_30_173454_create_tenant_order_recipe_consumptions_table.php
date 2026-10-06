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
                'order_recipe_consumptions',
                function (Blueprint $table): void {

                    $table->id();

                    /*
                    |--------------------------------------------------------------------------
                    | Order
                    |--------------------------------------------------------------------------
                    */

                    $table->foreignId(
                        'order_id'
                    )
                        ->constrained(
                            'orders'
                        )
                        ->restrictOnDelete();

                    /*
                    |--------------------------------------------------------------------------
                    | Kitchen Batch
                    |--------------------------------------------------------------------------
                    */

                    $table->foreignId(
                        'order_kitchen_batch_id'
                    )
                        ->constrained(
                            'order_kitchen_batches'
                        )
                        ->restrictOnDelete();

                    /*
                    |--------------------------------------------------------------------------
                    | Order Snapshot
                    |--------------------------------------------------------------------------
                    */

                    $table->string(
                        'order_number',
                        100
                    );

                    $table->string(
                        'trigger',
                        50
                    )
                        ->default(
                            'start_preparing'
                        );

                    $table->string(
                        'order_status_snapshot',
                        30
                    );

                    $table->timestamp(
                        'consumed_at'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Creator
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
                    | One Consumption Per Order + Batch
                    |--------------------------------------------------------------------------
                    */

                    $table->unique(
                        [
                            'order_id',
                            'order_kitchen_batch_id',
                        ],
                        'order_recipe_consumptions_order_batch_unique'
                    );

                    $table->index(
                        'order_kitchen_batch_id',
                        'order_recipe_consumptions_batch_index'
                    );

                    $table->index(
                        'consumed_at',
                        'orc_consumed_at_idx'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'order_recipe_consumptions'
            );
    }
};