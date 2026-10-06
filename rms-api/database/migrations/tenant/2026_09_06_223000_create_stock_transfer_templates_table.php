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
                'stock_transfer_templates',
                function (Blueprint $table): void {

                    $table->id();

                    $table->string(
                        'name',
                        120
                    );

                    $table->string(
                        'description',
                        500
                    )->nullable();

                    $table->boolean(
                        'is_active'
                    )->default(true);

                    $table->foreignId(
                        'created_by'
                    )
                        ->nullable()
                        ->constrained('users')
                        ->nullOnDelete();

                    $table->foreignId(
                        'updated_by'
                    )
                        ->nullable()
                        ->constrained('users')
                        ->nullOnDelete();

                    $table->timestamps();

                    /*
                    |--------------------------------------------------------------------------
                    | Configuration Record
                    |--------------------------------------------------------------------------
                    |
                    | Templates are reusable configuration, not accounting history.
                    | Soft delete lets users remove a template without destroying
                    | its old configuration row immediately.
                    |
                    */

                    $table->softDeletes();

                    $table->index(
                        'name'
                    );

                    $table->index(
                        'is_active'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'stock_transfer_templates'
            );
    }
};
