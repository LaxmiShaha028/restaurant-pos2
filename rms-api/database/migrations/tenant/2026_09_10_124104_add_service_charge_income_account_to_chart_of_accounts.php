<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    public function up(): void
    {

        /*
        |--------------------------------------------------------------------------
        | Add Service Charge Income Account
        |--------------------------------------------------------------------------
        */

        $parent =
            DB::connection('tenant')
                ->table('chart_of_accounts')
                ->where(
                    'system_key',
                    'revenue'
                )
                ->first();


        if (! $parent) {

            throw new RuntimeException(
                'Revenue parent account not found.'
            );

        }


        $exists =
            DB::connection('tenant')
                ->table('chart_of_accounts')
                ->where(
                    'system_key',
                    'service_charge_income'
                )
                ->exists();


        if ($exists) {

            return;

        }


        DB::connection('tenant')
            ->table('chart_of_accounts')
            ->insert([

                'code' =>
                    '4020',

                'system_key' =>
                    'service_charge_income',

                'name' =>
                    'Service Charge Income',

                'account_type' =>
                    'revenue',

                'normal_balance' =>
                    'credit',

                'parent_id' =>
                    $parent->id,

                'is_system' =>
                    true,

                'is_active' =>
                    true,

                'allow_manual_posting' =>
                    true,

                'description' =>
                    'Service charge collected from restaurant customers.',

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),

            ]);

    }


    public function down(): void
    {

        DB::connection('tenant')
            ->table('chart_of_accounts')
            ->where(
                'system_key',
                'service_charge_income'
            )
            ->delete();

    }

};