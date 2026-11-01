<?php

namespace Database\Seeders\Central;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        DB::connection('central')
            ->table('payment_methods')
            ->upsert(
                [
                    [
                        'name' => 'bKash',
                        'code' => 'bkash',
                        'account_number' => null,
                        'instructions' => 'Send payment to the registered bKash account.',
                        'status' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],

                    [
                        'name' => 'Nagad',
                        'code' => 'nagad',
                        'account_number' => null,
                        'instructions' => 'Send payment to the registered Nagad account.',
                        'status' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],

                    [
                        'name' => 'Bank Transfer',
                        'code' => 'bank_transfer',
                        'account_number' => null,
                        'instructions' => 'Transfer the payment to the designated bank account.',
                        'status' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],

                    [
                        'name' => 'Cash',
                        'code' => 'cash',
                        'account_number' => null,
                        'instructions' => 'Collect payment manually in cash.',
                        'status' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'name' => 'Stripe (Test Mode)',
                        'code' => 'stripe',
                        'account_number' => null,
                        'instructions' => 'Pay securely using Stripe Checkout test mode.',
                        'status' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ],
                [
                    'code',
                ],
                [
                    'name',
                    'account_number',
                    'instructions',
                    'status',
                    'updated_at',
                ]
            );
    }
}
