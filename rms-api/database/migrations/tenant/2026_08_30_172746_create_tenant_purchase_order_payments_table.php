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
                'purchase_order_payments',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'purchase_order_id'
                    )
                        ->constrained(
                            'purchase_orders'
                        )
                        ->cascadeOnDelete();

                    $table->date(
                        'payment_date'
                    );

                    $table->decimal(
                        'amount',
                        14,
                        2
                    );

                    $table->string(
                        'payment_method',
                        50
                    )->nullable();

                    $table->string(
                        'transaction_reference'
                    )->nullable();

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
                        'payment_date',
                    ]);
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'purchase_order_payments'
            );
    }
};