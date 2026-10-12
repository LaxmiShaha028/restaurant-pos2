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
                'order_kitchen_batches',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'order_id'
                    )
                        ->constrained(
                            'orders'
                        )
                        ->cascadeOnDelete();

                    $table->unsignedInteger(
                        'batch_no'
                    );

                    $table->string(
                        'status',
                        30
                    )->default('pending');

                    $table->foreignId(
                        'chef_id'
                    )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete();

                    $table->timestamp(
                        'sent_to_kitchen_at'
                    )->nullable();

                    $table->timestamp(
                        'preparing_at'
                    )->nullable();

                    $table->timestamp(
                        'ready_at'
                    )->nullable();

                    $table->timestamp(
                        'served_at'
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

                    $table->unique(
                        [
                            'order_id',
                            'batch_no',
                        ],
                        'order_kitchen_batches_order_batch_unique'
                    );

                    $table->index(
                        [
                            'status',
                            'chef_id',
                        ],
                        'order_kitchen_batches_status_chef_index'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'order_kitchen_batches'
            );
    }
};