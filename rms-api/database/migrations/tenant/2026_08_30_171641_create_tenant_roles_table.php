<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')
            ->create('roles', function (Blueprint $table): void {

                $table->id();

                $table->string(
                    'name',
                    50
                )->unique();

                $table->string(
                    'display_name',
                    100
                );

                $table->text(
                    'description'
                )->nullable();

                $table->boolean(
                    'is_active'
                )->default(true);

                $table->timestamps();

                $table->index(
                    'is_active'
                );
            });
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists('roles');
    }
};