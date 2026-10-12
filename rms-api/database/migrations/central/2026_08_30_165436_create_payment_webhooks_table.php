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
                'payment_webhooks',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'payment_id'
                    )
                        ->nullable()
                        ->constrained(
                            'payments'
                        )
                        ->nullOnDelete();

                    $table->string(
                        'gateway',
                        50
                    );

                    $table->string(
                        'event_id',
                        180
                    );

                    $table->string(
                        'event_type',
                        100
                    );

                    $table->json(
                        'payload'
                    )->nullable();

                    $table->boolean(
                        'signature_verified'
                    )->default(false);

                    $table->string(
                        'status',
                        30
                    )->default('pending');

                    $table->timestamp(
                        'processed_at'
                    )->nullable();

                    $table->text(
                        'error_message'
                    )->nullable();

                    $table->timestamps();

                    $table->unique([
                        'gateway',
                        'event_id',
                    ]);

                    $table->index([
                        'gateway',
                        'event_type',
                    ]);

                    $table->index(
                        'status'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('central')
            ->dropIfExists(
                'payment_webhooks'
            );
    }
};