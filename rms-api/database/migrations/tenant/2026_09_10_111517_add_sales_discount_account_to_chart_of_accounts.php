<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    public function up(): void
    {

        /*
        |--------------------------------------------------------------------------
        | Add Sales Discount Account
        |--------------------------------------------------------------------------
        */

        $parent =
            DB::connection('tenant')
                ->table('chart_of_accounts')
                ->where(
                    'code',
                    '6000'
                )
                ->first();


        if (! $parent) {

            throw new RuntimeException(
                'Operating Expenses parent account not found.'
            );

        }


        $exists =
            DB::connection('tenant')
                ->table('chart_of_accounts')
                ->where(
                    'system_key',
                    'sales_discount'
                )
                ->exists();


        if ($exists) {

            return;

        }



        DB::connection('tenant')
            ->table('chart_of_accounts')
            ->insert([

                'code' =>
                    '6050',

                'system_key' =>
                    'sales_discount',

                'name' =>
                    'Sales Discount',

                'account_type' =>
                    'expense',

                'normal_balance' =>
                    'debit',

                'parent_id' =>
                    $parent->id,

                'is_system' =>
                    true,

                'is_active' =>
                    true,

                'allow_manual_posting' =>
                    true,

                'description' =>
                    'Discount given to customers on restaurant sales.',

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
                'sales_discount'
            )
            ->delete();

    }

};