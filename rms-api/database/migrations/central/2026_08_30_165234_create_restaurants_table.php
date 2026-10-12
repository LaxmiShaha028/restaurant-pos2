<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('central')
            ->create('restaurants', function (Blueprint $table): void {

                $table->id();

                /*
                |--------------------------------------------------------------------------
                | Restaurant Identity
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'name',
                    180
                );

                $table->string(
                    'slug',
                    180
                )->unique();

                $table->string(
                    'logo_path'
                )->nullable();

                /*
                |--------------------------------------------------------------------------
                | Owner Information
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'owner_name',
                    150
                );

                $table->string(
                    'owner_email',
                    150
                )->index();

                $table->string(
                    'phone',
                    30
                )->nullable();

                /*
                |--------------------------------------------------------------------------
                | Address
                |--------------------------------------------------------------------------
                */

                $table->text(
                    'address'
                )->nullable();

                $table->string(
                    'city',
                    100
                )->nullable();

                $table->string(
                    'country',
                    100
                )->nullable();

                /*
                |--------------------------------------------------------------------------
                | Tenant Identification
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'tenant_key',
                    100
                )->unique();

                /*
                |--------------------------------------------------------------------------
                | Restaurant Lifecycle
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'status',
                    40
                )->default('pending');

                /*
                |--------------------------------------------------------------------------
                | Timestamps
                |--------------------------------------------------------------------------
                */

                $table->timestamps();

                $table->softDeletes();

                /*
                |--------------------------------------------------------------------------
                | Indexes
                |--------------------------------------------------------------------------
                */

                $table->index(
                    'status'
                );

                $table->index([
                    'owner_email',
                    'status',
                ]);
            });
    }

    public function down(): void
    {
        Schema::connection('central')
            ->dropIfExists('restaurants');
    }
};