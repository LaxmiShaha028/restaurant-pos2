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
                'purchase_order_receipts',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'purchase_order_id'
                    )
                        ->constrained(
                            'purchase_orders'
                        )
                        ->restrictOnDelete();

                    $table->string(
                        'receipt_no',
                        80
                    )->unique();

                    $table->timestamp(
                        'received_at'
                    );

                    $table->text(
                        'notes'
                    )->nullable();

                    $table->foreignId(
                        'received_by'
                    )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete();

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

                    $table->index([
                        'purchase_order_id',
                        'received_at',
                    ]);
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'purchase_order_receipts'
            );
    }
};