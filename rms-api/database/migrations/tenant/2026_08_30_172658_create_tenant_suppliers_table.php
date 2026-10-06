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
                'suppliers',
                function (Blueprint $table): void {

                    $table->id();

                    $table->string(
                        'supplier_name'
                    );

                    $table->string(
                        'contact_person'
                    )->nullable();

                    $table->string(
                        'email'
                    )->nullable();

                    $table->string(
                        'phone',
                        30
                    );

                    $table->text(
                        'address'
                    )->nullable();

                    $table->string(
                        'gstin',
                        50
                    )->nullable();

                    $table->foreignId(
                        'created_by'
                    )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete();

                    $table->foreignId(
                        'updated_by'
                    )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete();

                    $table->timestamps();

                    $table->softDeletes();

                    $table->index(
                        'supplier_name'
                    );

                    $table->index(
                        'phone'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists('suppliers');
    }
};