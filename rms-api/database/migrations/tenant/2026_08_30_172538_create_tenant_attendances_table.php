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
                'attendances',
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
                    | Schedule References
                    |--------------------------------------------------------------------------
                    */

                    $table->foreignId(
                        'shift_schedule_id'
                    )
                        ->nullable()
                        ->constrained(
                            'shift_schedules'
                        )
                        ->cascadeOnUpdate()
                        ->nullOnDelete();

                    $table->foreignId(
                        'shift_schedule_override_id'
                    )
                        ->nullable()
                        ->constrained(
                            'shift_schedule_overrides'
                        )
                        ->cascadeOnUpdate()
                        ->nullOnDelete();

                    /*
                    |--------------------------------------------------------------------------
                    | Attendance Date
                    |--------------------------------------------------------------------------
                    */

                    $table->date(
                        'attendance_date'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Scheduled Snapshot
                    |--------------------------------------------------------------------------
                    */

                    $table->dateTime(
                        'scheduled_start_at'
                    );

                    $table->dateTime(
                        'scheduled_end_at'
                    );

                    $table->unsignedSmallInteger(
                        'grace_minutes'
                    )->default(0);

                    $table->unsignedInteger(
                        'scheduled_minutes'
                    )->default(0);

                    /*
                    |--------------------------------------------------------------------------
                    | Actual Attendance
                    |--------------------------------------------------------------------------
                    */

                    $table->dateTime(
                        'check_in_at'
                    )->nullable();

                    $table->dateTime(
                        'check_out_at'
                    )->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Auto Checkout
                    |--------------------------------------------------------------------------
                    */

                    $table->boolean(
                        'auto_checked_out'
                    )->default(false);

                    $table->string(
                        'auto_checkout_reason',
                        255
                    )->nullable();

                    /*
                    |--------------------------------------------------------------------------
                    | Status
                    |--------------------------------------------------------------------------
                    */

                    $table->string(
                        'status',
                        30
                    )->default('scheduled');

                    /*
                    |--------------------------------------------------------------------------
                    | Calculated Minutes
                    |--------------------------------------------------------------------------
                    */

                    $table->unsignedInteger(
                        'late_minutes'
                    )->default(0);

                    $table->unsignedInteger(
                        'worked_minutes'
                    )->default(0);

                    $table->unsignedInteger(
                        'break_minutes'
                    )->default(0);

                    $table->unsignedInteger(
                        'overtime_minutes'
                    )->default(0);

                    $table->unsignedInteger(
                        'early_leave_minutes'
                    )->default(0);

                    /*
                    |--------------------------------------------------------------------------
                    | Manual / Notes
                    |--------------------------------------------------------------------------
                    */

                    $table->boolean(
                        'is_manual'
                    )->default(false);

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

                    /*
                    |--------------------------------------------------------------------------
                    | Constraints / Indexes
                    |--------------------------------------------------------------------------
                    */

                    $table->unique(
                        [
                            'employee_id',
                            'attendance_date',
                        ],
                        'attendances_employee_date_unique'
                    );

                    $table->index([
                        'attendance_date',
                        'status',
                    ]);

                    $table->index([
                        'scheduled_start_at',
                        'scheduled_end_at',
                    ]);
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists('attendances');
    }
};