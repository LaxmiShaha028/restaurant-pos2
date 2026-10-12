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
                'domains',
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

                    $table->string(
                        'domain',
                        255
                    )->unique();

                    $table->string(
                        'type',
                        30
                    )->default('subdomain');

                    $table->boolean(
                        'is_primary'
                    )->default(false);

                    $table->boolean(
                        'is_verified'
                    )->default(false);

                    $table->string(
                        'verification_token',
                        150
                    )->nullable();

                    $table->string(
                        'ssl_status',
                        30
                    )->default('pending');

                    $table->timestamps();

                    $table->index([
                        'restaurant_id',
                        'is_primary',
                    ]);

                    $table->index([
                        'is_verified',
                        'type',
                    ]);
                }
            );
    }

    public function down(): void
    {
        Schema::connection('central')
            ->dropIfExists(
                'domains'
            );
    }
};