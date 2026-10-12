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
                'shift_schedule_overrides',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'shift_schedule_id'
                    )
                        ->constrained(
                            'shift_schedules'
                        )
                        ->cascadeOnUpdate()
                        ->restrictOnDelete();

                    $table->date(
                        'override_date'
                    );

                    $table->string(
                        'override_type',
                        30
                    )->default('modified');

                    $table->time(
                        'start_time'
                    )->nullable();

                    $table->time(
                        'end_time'
                    )->nullable();

                    $table->unsignedSmallInteger(
                        'grace_minutes'
                    )->nullable();

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

                    $table->index([
                        'shift_schedule_id',
                        'override_date',
                    ]);

                    $table->index([
                        'override_date',
                        'override_type',
                    ]);
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'shift_schedule_overrides'
            );
    }
};