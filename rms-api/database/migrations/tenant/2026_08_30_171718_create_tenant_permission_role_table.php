<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')
            ->create('permission_role', function (Blueprint $table): void {

                $table->id();

                $table->foreignId(
                    'role_id'
                )
                    ->constrained('roles')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreignId(
                    'permission_id'
                )
                    ->constrained('permissions')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->timestamps();

                $table->unique(
                    [
                        'role_id',
                        'permission_id',
                    ],
                    'permission_role_unique'
                );
            });
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists('permission_role');
    }
};