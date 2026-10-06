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
                'platform_audit_logs',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'super_admin_id'
                    )
                        ->nullable()
                        ->constrained(
                            'super_admins'
                        )
                        ->nullOnDelete();

                    $table->foreignId(
                        'restaurant_id'
                    )
                        ->nullable()
                        ->constrained(
                            'restaurants'
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
                        'restaurant_id'
                    );

                    $table->index(
                        'created_at'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('central')
            ->dropIfExists(
                'platform_audit_logs'
            );
    }
};