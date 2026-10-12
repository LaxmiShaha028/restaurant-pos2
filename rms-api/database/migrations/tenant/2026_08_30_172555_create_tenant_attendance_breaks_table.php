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
                'attendance_breaks',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'attendance_id'
                    )
                        ->constrained(
                            'attendances'
                        )
                        ->cascadeOnUpdate()
                        ->cascadeOnDelete();

                    $table->dateTime(
                        'break_start_at'
                    );

                    $table->dateTime(
                        'break_end_at'
                    )->nullable();

                    $table->unsignedInteger(
                        'duration_minutes'
                    )->default(0);

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

                    $table->index([
                        'attendance_id',
                        'break_start_at',
                    ]);

                    $table->index([
                        'attendance_id',
                        'break_end_at',
                    ]);
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists('attendance_breaks');
    }
};