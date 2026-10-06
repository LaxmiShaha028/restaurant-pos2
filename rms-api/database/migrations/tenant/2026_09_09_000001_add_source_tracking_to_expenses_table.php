<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')
            ->table(
                'expenses',
                function (Blueprint $table): void {

                    $table->string(
                        'source_type',
                        50
                    )
                        ->nullable()
                        ->after(
                            'notes'
                        );

                    $table->unsignedBigInteger(
                        'source_id'
                    )
                        ->nullable()
                        ->after(
                            'source_type'
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | One System Expense Per Source
                    |--------------------------------------------------------------------------
                    |
                    | Example:
                    | source_type = salary_payroll
                    | source_id   = 25
                    |
                    | Multiple manual expenses are still allowed because both values are null.
                    |
                    */

                    $table->unique(
                        [
                            'source_type',
                            'source_id',
                        ],
                        'expenses_source_unique'
                    );

                    $table->index(
                        'source_type',
                        'expenses_source_type_idx'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->table(
                'expenses',
                function (Blueprint $table): void {

                    $table->dropUnique(
                        'expenses_source_unique'
                    );

                    $table->dropIndex(
                        'expenses_source_type_idx'
                    );

                    $table->dropColumn([
                        'source_type',
                        'source_id',
                    ]);
                }
            );
    }
};
