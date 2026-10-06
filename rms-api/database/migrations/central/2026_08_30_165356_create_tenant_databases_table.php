<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('central')
            ->create(
                'tenant_databases',
                function (Blueprint $table): void {

                    $table->id();

                    $table->foreignId(
                        'restaurant_id'
                    )
                        ->unique()
                        ->constrained(
                            'restaurants'
                        )
                        ->cascadeOnUpdate()
                        ->restrictOnDelete();

                    $table->string(
                        'connection_name',
                        100
                    )->default('tenant');

                    $table->string(
                        'database_host',
                        255
                    );

                    $table->unsignedSmallInteger(
                        'database_port'
                    )->default(3306);

                    $table->string(
                        'database_name',
                        180
                    )->unique();

                    $table->string(
                        'database_username',
                        150
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Encrypted at application level
                    |--------------------------------------------------------------------------
                    */

                    $table->text(
                        'database_password'
                    )->nullable();

                    $table->string(
                        'status',
                        40
                    )->default('pending');

                    $table->timestamp(
                        'migrated_at'
                    )->nullable();

                    $table->timestamp(
                        'provisioned_at'
                    )->nullable();

                    $table->text(
                        'last_error'
                    )->nullable();

                    $table->timestamps();

                    $table->index(
                        'status'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('central')
            ->dropIfExists(
                'tenant_databases'
            );
    }
};