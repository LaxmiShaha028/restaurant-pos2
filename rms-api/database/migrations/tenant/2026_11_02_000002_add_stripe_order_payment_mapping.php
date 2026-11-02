<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $stripe = DB::connection('tenant')->table('accounting_payment_mappings')
            ->where('payment_method', 'stripe')->first();
        if ($stripe) {
            return;
        }

        $cardAccountId = DB::connection('tenant')->table('chart_of_accounts')
            ->where('code', '1030')->value('id');
        if (! $cardAccountId) {
            return;
        }

        DB::connection('tenant')->table('accounting_payment_mappings')->insert([
            'payment_method' => 'stripe',
            'ledger_account_id' => $cardAccountId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::connection('tenant')->table('accounting_payment_mappings')
            ->where('payment_method', 'stripe')->delete();
    }
};
