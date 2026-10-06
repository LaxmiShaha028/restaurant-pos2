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
                'order_payments',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'order_id'
                    )
                        ->constrained(
                            'orders'
                        )
                        ->cascadeOnDelete();

                    $table->decimal(
                        'amount',
                        12,
                        2
                    );

                    $table->string(
                        'payment_method',
                        50
                    );

                    $table->string(
                        'reference'
                    )->nullable();

                    $table->text(
                        'note'
                    )->nullable();

                    $table->foreignId(
                        'received_by'
                    )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete();

                    $table->timestamps();

                    $table->index([
                        'order_id',
                        'created_at',
                    ]);

                    $table->index(
                        'payment_method'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'order_payments'
            );
    }
};