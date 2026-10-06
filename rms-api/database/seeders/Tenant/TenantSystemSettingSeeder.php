<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantSystemSettingSeeder extends Seeder
{
    public function run(array $restaurant = []): void
    {
        $existing = DB::connection('tenant')
            ->table('system_settings')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Do not overwrite restaurant-owned settings
        |--------------------------------------------------------------------------
        */

        if ($existing) {
            return;
        }

        DB::connection('tenant')
            ->table('system_settings')
            ->insert([
                'restaurant_name' =>
                    $restaurant['name']
                    ?? null,

                'restaurant_logo' =>
                    $restaurant['logo_path']
                    ?? null,

                'country' =>
                    $restaurant['country']
                    ?? null,

                'branch_address' =>
                    $restaurant['address']
                    ?? null,

                'vat_rate' =>
                    0,

                'vat_type' =>
                    'percentage',

                'service_charge_rate' =>
                    0,

                'service_charge_type' =>
                    'percentage',

                'owner_nid' =>
                    null,

                'owner_trade_license' =>
                    null,

                'invoice_subtitle' =>
                    null,

                'bin_number' =>
                    null,

                'invoice_show_logo' =>
                    true,

                'invoice_show_company_name' =>
                    true,

                'invoice_show_branch_address' =>
                    true,

                'invoice_show_token_number' =>
                    true,

                'invoice_show_table_number' =>
                    true,

                'invoice_currency' =>
                    'BDT',

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ]);
    }
}