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
                'reservations',
                function (Blueprint $table): void {

                    $table->id();

                    $table->string(
                        'reservation_no'
                    )->unique();

                    $table->foreignId(
                        'customer_id'
                    )
                        ->nullable()
                        ->constrained(
                            'customers'
                        )
                        ->nullOnDelete();

                    $table->date(
                        'reservation_date'
                    );

                    $table->dateTime(
                        'start_time'
                    );

                    $table->dateTime(
                        'end_time'
                    );

                    $table->unsignedInteger(
                        'guest_count'
                    );

                    $table->decimal(
                        'advance_amount',
                        10,
                        2
                    )->default(0);

                    $table->enum(
                        'status',
                        [
                            'reserved',
                            'confirmed',
                            'seated',
                            'completed',
                            'cancelled',
                        ]
                    )->default('reserved');

                    $table->text(
                        'note'
                    )->nullable();

                    $table->foreignId(
                        'created_by'
                    )
                        ->nullable()
                        ->constrained(
                            'users'
                        )
                        ->nullOnDelete();

                    $table->timestamps();

                    $table->softDeletes();

                    $table->index([
                        'reservation_date',
                        'status',
                    ]);

                    $table->index([
                        'start_time',
                        'end_time',
                    ]);
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'reservations'
            );
    }
};