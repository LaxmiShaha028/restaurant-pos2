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
                'customers',
                function (Blueprint $table): void {

                    $table->id();

                    $table->string(
                        'name',
                        150
                    );

                    $table->string(
                        'phone',
                        30
                    )->unique();

                    $table->string(
                        'email',
                        150
                    )->nullable()->unique();

                    $table->dateTime(
                        'last_visit_at'
                    )->nullable();

                    $table->unsignedInteger(
                        'total_orders'
                    )->default(0);

                    $table->decimal(
                        'total_spent',
                        15,
                        2
                    )->default(0);

                    $table->boolean(
                        'is_active'
                    )->default(true);

                    $table->text(
                        'notes'
                    )->nullable();

                    $table->timestamps();

                    $table->softDeletes();

                    $table->index(
                        'name'
                    );

                    $table->index(
                        'last_visit_at'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('tenant')
            ->dropIfExists(
                'customers'
            );
    }
};