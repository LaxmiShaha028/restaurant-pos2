<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'system_settings',
            function (Blueprint $table): void {

                $table->id();

                /*
                |--------------------------------------------------------------------------
                | Restaurant Identity
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'restaurant_name'
                )->nullable();

                $table->string(
                    'restaurant_logo'
                )->nullable();

                /*
                |--------------------------------------------------------------------------
                | Business / Location
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'country'
                )->nullable();

                $table->string(
                    'branch_address'
                )->nullable();

                /*
                |--------------------------------------------------------------------------
                | Tax & Service Charge
                |--------------------------------------------------------------------------
                */

                $table->decimal(
                    'vat_rate',
                    5,
                    2
                )->default(0);

                $table->decimal(
                    'service_charge_rate',
                    5,
                    2
                )->default(0);

                /*
                |--------------------------------------------------------------------------
                | Business Verification
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'owner_nid'
                )->nullable();

                $table->string(
                    'owner_trade_license'
                )->nullable();

                /*
                |--------------------------------------------------------------------------
                | Invoice General
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'invoice_subtitle'
                )->nullable();

                $table->string(
                    'bin_number'
                )->nullable();

                /*
                |--------------------------------------------------------------------------
                | Invoice Print Settings
                |--------------------------------------------------------------------------
                */

                $table->boolean(
                    'invoice_show_logo'
                )->default(true);

                $table->boolean(
                    'invoice_show_company_name'
                )->default(true);

                $table->boolean(
                    'invoice_show_branch_address'
                )->default(true);

                $table->boolean(
                    'invoice_show_token_number'
                )->default(true);

                $table->boolean(
                    'invoice_show_table_number'
                )->default(true);

                /*
                |--------------------------------------------------------------------------
                | Invoice Currency
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'invoice_currency'
                )->default('BDT');

                /*
                |--------------------------------------------------------------------------
                | Timestamps
                |--------------------------------------------------------------------------
                */

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'system_settings'
        );
    }
};