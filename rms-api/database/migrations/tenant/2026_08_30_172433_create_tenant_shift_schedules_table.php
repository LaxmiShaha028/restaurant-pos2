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
                'shift_schedules',
                function (Blueprint $table): void {

                    $table->id();

                    /*
                    |--------------------------------------------------------------------------
                    | Employee
                    |--------------------------------------------------------------------------
                    */

                    $table->foreignId(
                        'employee_id'
                    )
                        ->constrained(
                            'employees'
                        )
                        ->cascadeOnUpdate()
                        ->restrictOnDelete();

                    /*
                    |--------------------------------------------------------------------------
                    | Schedule Date Range
                    |--------------------------------------------------------------------------
                    */

                    $table->date(
                        'start_date'
                    );

                    $table->date(
                        'end_date'
                    )->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Working Days
                    |--------------------------------------------------------------------------
                    |
                    | Example:
                    | ["sunday","monday","tuesday"]
                    |
                    */

                    $table->json(
                        'working_days'
                    )->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Shift Time
                    |--------------------------------------------------------------------------
                    */

                    $table->time(
                        'start_time'
                    );

                    $table->time(
                        'end_time'
                    );

                    $table->unsignedSmallInteger(
                        'grace_minutes'
                    )->default(0);

                    /*
                    |--------------------------------------------------------------------------
                    | Status
                    |--------------------------------------------------------------------------
                    */

                    $table->boolean(
                        'is_active'
                    )->default(true);

                    $table->text(
                        'notes'
                    )->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Audit
                    |--------------------------------------------------------------------------
                    */

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

                    /*
                    |--------------------------------------------------------------------------
                    | Indexes
                    |--------------------------------------------------------------------------
                    */

                    $table->index([
                        'employee_id',
                        'start_date',
                        'end_date',
                    ]);

                    $table->index([
                        'start_date',
                        'end_date',
                        'is_active',
                    ]);
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists('shift_schedules');
    }
};