<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('central')
            ->create(
                'payments',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'restaurant_id'
                    )
                        ->constrained(
                            'restaurants'
                        )
                        ->cascadeOnUpdate()
                        ->restrictOnDelete();

                    $table->foreignId(
                        'subscription_id'
                    )
                        ->nullable()
                        ->constrained(
                            'subscriptions'
                        )
                        ->cascadeOnUpdate()
                        ->nullOnDelete();

                    $table->string(
                        'gateway',
                        50
                    );

                    $table->string(
                        'transaction_id',
                        150
                    )->nullable();

                    $table->string(
                        'reference',
                        150
                    )->nullable();

                    $table->decimal(
                        'amount',
                        14,
                        2
                    );

                    $table->string(
                        'currency',
                        10
                    )->default('BDT');

                    $table->string(
                        'payment_method',
                        50
                    )->nullable();

                    $table->string(
                        'status',
                        30
                    )->default('pending');

                    $table->timestamp(
                        'paid_at'
                    )->nullable();

                    $table->json(
                        'gateway_response'
                    )->nullable();

                    $table->timestamps();

                    $table->index([
                        'restaurant_id',
                        'status',
                    ]);

                    $table->index(
                        'transaction_id'
                    );

                    $table->index(
                        'gateway'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('central')
            ->dropIfExists(
                'payments'
            );
    }
};