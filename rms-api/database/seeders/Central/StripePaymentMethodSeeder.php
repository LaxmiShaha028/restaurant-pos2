<?php

namespace Database\Seeders\Central;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StripePaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        DB::connection('central')->table('payment_methods')->upsert(
            [[
                'name' => 'Stripe (Test Mode)',
                'code' => 'stripe',
                'account_number' => null,
                'instructions' => 'Pay securely using Stripe Checkout test mode.',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]],
            ['code'],
            ['name', 'instructions', 'status', 'updated_at']
        );
    }
}
