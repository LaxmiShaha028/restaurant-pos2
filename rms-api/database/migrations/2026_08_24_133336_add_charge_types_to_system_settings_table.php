<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'system_settings',
            function (Blueprint $table): void {

                /*
                |--------------------------------------------------------------------------
                | VAT Type
                |--------------------------------------------------------------------------
                |
                | Supported:
                |
                | percentage
                | fixed
                |
                */

                $table->string(
                    'vat_type'
                )
                    ->default('percentage')
                    ->after('vat_rate');


                /*
                |--------------------------------------------------------------------------
                | Service Charge Type
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'service_charge_type'
                )
                    ->default('percentage')
                    ->after(
                        'service_charge_rate'
                    );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'system_settings',
            function (Blueprint $table): void {

                $table->dropColumn([
                    'vat_type',
                    'service_charge_type',
                ]);
            }
        );
    }
};