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
                'expenses',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'expense_category_id'
                    )
                        ->constrained(
                            'expense_categories'
                        )
                        ->restrictOnDelete()
                        ->cascadeOnUpdate();

                    $table->dateTime(
                        'expense_date'
                    )->index();

                    $table->decimal(
                        'amount',
                        14,
                        2
                    )->default(0);

                    $table->string(
                        'payment_method',
                        50
                    )->index();

                    $table->text(
                        'notes'
                    )->nullable();

                    $table->foreignId(
                        'created_by'
                    )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete()
                        ->cascadeOnUpdate();

                    $table->foreignId(
                        'updated_by'
                    )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete()
                        ->cascadeOnUpdate();

                    $table->timestamps();

                    $table->softDeletes();

                    $table->index([
                        'expense_date',
                        'expense_category_id',
                    ]);

                    $table->index([
                        'expense_date',
                        'payment_method',
                    ]);

                    $table->index([
                        'created_by',
                        'expense_date',
                    ]);
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'expenses'
            );
    }
};