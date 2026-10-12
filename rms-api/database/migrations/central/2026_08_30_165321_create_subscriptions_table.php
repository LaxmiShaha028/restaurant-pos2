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
                'subscriptions',
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
                        'subscription_plan_id'
                    )
                        ->constrained(
                            'subscription_plans'
                        )
                        ->cascadeOnUpdate()
                        ->restrictOnDelete();

                    $table->string(
                        'status',
                        30
                    )->default('pending');

                    $table->timestamp(
                        'start_at'
                    )->nullable();

                    $table->timestamp(
                        'end_at'
                    )->nullable();

                    $table->timestamp(
                        'trial_start_at'
                    )->nullable();

                    $table->timestamp(
                        'trial_end_at'
                    )->nullable();

                    $table->boolean(
                        'auto_renew'
                    )->default(false);

                    $table->timestamp(
                        'cancelled_at'
                    )->nullable();

                    $table->text(
                        'cancel_reason'
                    )->nullable();

                    $table->timestamps();

                    $table->index(
                        [
                            'restaurant_id',
                            'status',
                        ]
                    );

                    $table->index(
                        [
                            'status',
                            'end_at',
                        ]
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('central')
            ->dropIfExists(
                'subscriptions'
            );
    }
};