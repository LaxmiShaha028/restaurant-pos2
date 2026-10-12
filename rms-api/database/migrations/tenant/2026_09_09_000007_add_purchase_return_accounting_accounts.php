<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now =
            now();


        /*
        |--------------------------------------------------------------------------
        | Required Parent Accounts
        |--------------------------------------------------------------------------
        */

        $assetRootId =
            DB::connection('tenant')
                ->table('chart_of_accounts')
                ->where(
                    'system_key',
                    'assets'
                )
                ->value('id');


        $revenueRootId =
            DB::connection('tenant')
                ->table('chart_of_accounts')
                ->where(
                    'system_key',
                    'revenue'
                )
                ->value('id');


        $expenseRootId =
            DB::connection('tenant')
                ->table('chart_of_accounts')
                ->where(
                    'system_key',
                    'operating_expenses'
                )
                ->value('id');


        /*
        |--------------------------------------------------------------------------
        | Compatibility Fallback For Expense Root
        |--------------------------------------------------------------------------
        */

        if (! $expenseRootId) {
            $expenseRootId =
                DB::connection('tenant')
                    ->table('chart_of_accounts')
                    ->where(
                        'code',
                        '6000'
                    )
                    ->value('id');
        }


        if (
            ! $assetRootId
            ||
            ! $revenueRootId
            ||
            ! $expenseRootId
        ) {
            throw new RuntimeException(
                'Phase 5B requires the Phase 2 Chart of Accounts system roots.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Phase 5B Accounts
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | system_key is the stable accounting identity.
        |
        | code is presentation/order metadata only.
        |
        | Different tenant databases may already use one of our preferred codes,
        | so this migration safely chooses the first unused code from a candidate
        | list instead of modifying any existing account.
        |
        */

        $accounts = [
            [
                'preferred_codes' => [
                    '1250',
                    '1251',
                    '1252',
                    '1253',
                    '1254',
                    '1255',
                    '1256',
                    '1257',
                    '1258',
                    '1259',
                ],

                'system_key' =>
                    'purchase_return_clearing',

                'name' =>
                    'Purchase Return Clearing',

                'account_type' =>
                    'asset',

                'normal_balance' =>
                    'debit',

                'parent_id' =>
                    $assetRootId,

                'is_system' =>
                    true,

                'is_active' =>
                    true,

                'allow_manual_posting' =>
                    true,

                'description' =>
                    'Temporary supplier recovery asset created when inventory is physically returned, before the return is fully settled.',
            ],

            [
                'preferred_codes' => [
                    '1260',
                    '1261',
                    '1262',
                    '1263',
                    '1264',
                    '1265',
                    '1266',
                    '1267',
                    '1268',
                    '1269',
                ],

                'system_key' =>
                    'supplier_credit_receivable',

                'name' =>
                    'Supplier Credit Receivable',

                'account_type' =>
                    'asset',

                'normal_balance' =>
                    'debit',

                'parent_id' =>
                    $assetRootId,

                'is_system' =>
                    true,

                'is_active' =>
                    true,

                'allow_manual_posting' =>
                    true,

                'description' =>
                    'Reusable supplier credit available for application against another eligible purchase order.',
            ],

            [
                'preferred_codes' => [
                    '1270',
                    '1271',
                    '1272',
                    '1273',
                    '1274',
                    '1275',
                    '1276',
                    '1277',
                    '1278',
                    '1279',
                ],

                'system_key' =>
                    'supplier_refund_receivable',

                'name' =>
                    'Supplier Refund Receivable',

                'account_type' =>
                    'asset',

                'normal_balance' =>
                    'debit',

                'parent_id' =>
                    $assetRootId,

                'is_system' =>
                    true,

                'is_active' =>
                    true,

                'allow_manual_posting' =>
                    true,

                'description' =>
                    'Cash/bank refund due from a supplier but not yet collected.',
            ],

            [
                'preferred_codes' => [
                    '4200',
                    '4201',
                    '4202',
                    '4203',
                    '4204',
                    '4205',
                    '4206',
                    '4207',
                    '4208',
                    '4209',
                ],

                'system_key' =>
                    'purchase_return_valuation_gain',

                'name' =>
                    'Purchase Return Valuation Gain',

                'account_type' =>
                    'revenue',

                'normal_balance' =>
                    'credit',

                'parent_id' =>
                    $revenueRootId,

                'is_system' =>
                    true,

                'is_active' =>
                    true,

                'allow_manual_posting' =>
                    true,

                'description' =>
                    'Gain when supplier return credit exceeds the inventory carrying value removed.',
            ],

            [
                /*
                |--------------------------------------------------------------------------
                | 6800 May Already Exist
                |--------------------------------------------------------------------------
                |
                | The user's current tenant already has code 6800.
                |
                | We preserve that account unchanged and select the first free code.
                |
                */

                'preferred_codes' => [
                    '6800',
                    '6810',
                    '6820',
                    '6830',
                    '6840',
                    '6850',
                    '6860',
                    '6870',
                    '6880',
                    '6890',
                    '6910',
                    '6920',
                    '6930',
                    '6940',
                    '6950',
                    '6960',
                    '6970',
                    '6980',
                    '6990',
                ],

                'system_key' =>
                    'purchase_return_valuation_loss',

                'name' =>
                    'Purchase Return Valuation Loss',

                'account_type' =>
                    'expense',

                'normal_balance' =>
                    'debit',

                'parent_id' =>
                    $expenseRootId,

                'is_system' =>
                    true,

                'is_active' =>
                    true,

                'allow_manual_posting' =>
                    true,

                'description' =>
                    'Loss when inventory carrying value removed exceeds the supplier return credit.',
            ],
        ];


        foreach ($accounts as $account) {

            /*
            |--------------------------------------------------------------------------
            | Already Installed
            |--------------------------------------------------------------------------
            |
            | A previous failed migration may already have inserted some Phase 5B
            | accounts before reaching a later code collision.
            |
            | Never create duplicates. system_key is authoritative.
            |
            */

            $existingBySystemKey =
                DB::connection('tenant')
                    ->table(
                        'chart_of_accounts'
                    )
                    ->where(
                        'system_key',
                        $account[
                            'system_key'
                        ]
                    )
                    ->first();


            if ($existingBySystemKey) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Find First Safe Unused Code
            |--------------------------------------------------------------------------
            */

            $code =
                $this->firstAvailableCode(
                    $account[
                        'preferred_codes'
                    ]
                );


            if (! $code) {
                throw new RuntimeException(
                    'No free Chart of Accounts code is available for '
                    .
                    $account[
                        'name'
                    ]
                    .
                    '.'
                );
            }


            unset(
                $account[
                    'preferred_codes'
                ]
            );


            DB::connection('tenant')
                ->table(
                    'chart_of_accounts'
                )
                ->insert([
                    ...$account,

                    'code' =>
                        $code,

                    'created_by' =>
                        null,

                    'updated_by' =>
                        null,

                    'created_at' =>
                        $now,

                    'updated_at' =>
                        $now,

                    'deleted_at' =>
                        null,
                ]);
        }
    }


    public function down(): void
    {
        DB::connection('tenant')
            ->table(
                'chart_of_accounts'
            )
            ->whereIn(
                'system_key',
                [
                    'purchase_return_clearing',
                    'supplier_credit_receivable',
                    'supplier_refund_receivable',
                    'purchase_return_valuation_gain',
                    'purchase_return_valuation_loss',
                ]
            )
            ->delete();
    }


    /*
    |--------------------------------------------------------------------------
    | First Available COA Code
    |--------------------------------------------------------------------------
    */

    private function firstAvailableCode(
        array $candidateCodes
    ): ?string {

        foreach ($candidateCodes as $candidateCode) {

            $alreadyUsed =
                DB::connection('tenant')
                    ->table(
                        'chart_of_accounts'
                    )
                    ->where(
                        'code',
                        $candidateCode
                    )
                    ->exists();


            if (! $alreadyUsed) {
                return $candidateCode;
            }
        }


        return null;
    }
};
