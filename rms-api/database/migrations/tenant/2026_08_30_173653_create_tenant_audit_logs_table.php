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
                'audit_logs',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'user_id'
                    )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete();

                    $table->string(
                        'module',
                        100
                    );

                    $table->string(
                        'action',
                        100
                    );

                    $table->text(
                        'description'
                    )->nullable();

                    $table->string(
                        'model_type'
                    )->nullable();

                    $table->unsignedBigInteger(
                        'model_id'
                    )->nullable();

                    $table->json(
                        'old_values'
                    )->nullable();

                    $table->json(
                        'new_values'
                    )->nullable();

                    $table->ipAddress(
                        'ip_address'
                    )->nullable();

                    $table->text(
                        'user_agent'
                    )->nullable();

                    $table->timestamps();

                    $table->index([
                        'module',
                        'action',
                    ]);

                    $table->index(
                        'created_at'
                    );

                    $table->index(
                        [
                            'model_type',
                            'model_id',
                        ]
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'audit_logs'
            );
    }
};