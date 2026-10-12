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
                'employees',
                function (Blueprint $table): void {

                    $table->id();

                    /*
                    |--------------------------------------------------------------------------
                    | User Account
                    |--------------------------------------------------------------------------
                    */

                    $table->foreignId(
                        'user_id'
                    )
                        ->unique()
                        ->constrained(
                            'users'
                        )
                        ->restrictOnDelete()
                        ->cascadeOnUpdate();

                    /*
                    |--------------------------------------------------------------------------
                    | Employee Information
                    |--------------------------------------------------------------------------
                    */

                    $table->string(
                        'phone',
                        30
                    )
                        ->nullable()
                        ->index();

                    $table->string(
                        'profile_image'
                    )
                        ->nullable();

                    $table->date(
                        'joining_date'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Salary
                    |--------------------------------------------------------------------------
                    */

                    $table->decimal(
                        'hourly_rate',
                        12,
                        2
                    )->default(0);

                    /*
                    |--------------------------------------------------------------------------
                    | Current Working Status
                    |--------------------------------------------------------------------------
                    */

                    $table->string(
                        'current_status',
                        30
                    )
                        ->default('none')
                        ->index();

                    $table->dateTime(
                        'status_updated_at'
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
                        'joining_date',
                        'current_status',
                    ]);

                    $table->index([
                        'current_status',
                        'status_updated_at',
                    ]);
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists('employees');
    }
};