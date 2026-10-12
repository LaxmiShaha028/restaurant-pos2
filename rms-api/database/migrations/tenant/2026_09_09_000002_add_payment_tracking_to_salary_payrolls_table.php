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
                'salary_payrolls',
                function (Blueprint $table): void {

                    $table->date(
                        'payment_date'
                    )
                        ->nullable()
                        ->after(
                            'paid_at'
                        );

                    $table->string(
                        'payment_method',
                        50
                    )
                        ->nullable()
                        ->after(
                            'payment_date'
                        );

                    $table->string(
                        'payment_reference',
                        150
                    )
                        ->nullable()
                        ->after(
                            'payment_method'
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Monthly Lookup Index
                    |--------------------------------------------------------------------------
                    |
                    | Application logic keeps one active payroll per employee/month.
                    | This non-unique index is safe with the current SoftDeletes design.
                    |
                    */

                    $table->index(
                        [
                            'employee_id',
                            'period_start',
                        ],
                        'salary_payroll_employee_period_idx'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->table(
                'salary_payrolls',
                function (Blueprint $table): void {

                    $table->dropIndex(
                        'salary_payroll_employee_period_idx'
                    );

                    $table->dropColumn([
                        'payment_date',
                        'payment_method',
                        'payment_reference',
                    ]);
                }
            );
    }
};
