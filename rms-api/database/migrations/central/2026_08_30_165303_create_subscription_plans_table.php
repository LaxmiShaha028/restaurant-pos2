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
                'subscription_plans',
                function (Blueprint $table): void {

                    $table->id();

                    $table->string(
                        'name',
                        100
                    );

                    $table->string(
                        'slug',
                        100
                    )->unique();

                    $table->text(
                        'description'
                    )->nullable();

                    $table->decimal(
                        'price',
                        14,
                        2
                    )->default(0);

                    $table->string(
                        'currency',
                        10
                    )->default('BDT');

                    $table->string(
                        'billing_interval',
                        20
                    )->default('month');

                    $table->unsignedInteger(
                        'billing_interval_count'
                    )->default(1);

                    $table->unsignedInteger(
                        'trial_days'
                    )->default(0);

                    $table->json(
                        'features'
                    )->nullable();

                    $table->boolean(
                        'is_active'
                    )->default(true);

                    $table->unsignedInteger(
                        'sort_order'
                    )->default(0);

                    $table->timestamps();

                    $table->softDeletes();

                    $table->index(
                        'is_active'
                    );

                    $table->index([
                        'is_active',
                        'sort_order',
                    ]);
                }
            );
    }

    public function down(): void
    {
        Schema::connection('central')
            ->dropIfExists(
                'subscription_plans'
            );
    }
};