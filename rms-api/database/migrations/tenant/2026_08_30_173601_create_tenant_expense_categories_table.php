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
                'expense_categories',
                function (Blueprint $table): void {

                    $table->id();

                    $table->string(
                        'name',
                        150
                    )->unique();

                    $table->text(
                        'description'
                    )->nullable();

                    $table->boolean(
                        'is_active'
                    )
                        ->default(true)
                        ->index();

                    $table->timestamps();

                    $table->softDeletes();
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'expense_categories'
            );
    }
};