<?php

namespace Database\Seeders\Central;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $plans = [
            [
                'name' =>
                    'Trial',

                'slug' =>
                    'trial',

                'description' =>
                    'Free trial plan for new restaurants.',

                'price' =>
                    0,

                'currency' =>
                    'BDT',

                'billing_interval' =>
                    'month',

                'billing_interval_count' =>
                    1,

                'trial_days' =>
                    14,

                'features' => json_encode([
                    'Dashboard',
                    'Menu Management',
                    'Order Management',
                    'Kitchen Management',
                    'Basic Reports',
                ]),

                'is_active' =>
                    true,

                'sort_order' =>
                    1,
            ],

            [
                'name' =>
                    'Enterprise',

                'slug' =>
                    'enterprise',

                'description' =>
                    'Full restaurant management system with advanced features.',

                'price' =>
                    5000,

                'currency' =>
                    'BDT',

                'billing_interval' =>
                    'month',

                'billing_interval_count' =>
                    1,

                'trial_days' =>
                    0,

                'features' => json_encode([
                    'All Modules',
                    'Inventory',
                    'Warehouse',
                    'Salary Management',
                    'Advanced Reports',
                    'Kitchen Management',
                    'Billing',
                    'Staff Management',
                    'Priority Support',
                ]),

                'is_active' =>
                    true,

                'sort_order' =>
                    2,
            ],
        ];

        foreach ($plans as $plan) {

            DB::connection('central')
                ->table('subscription_plans')
                ->updateOrInsert(
                    [
                        'slug' =>
                            $plan['slug'],
                    ],
                    [
                        'name' =>
                            $plan['name'],

                        'description' =>
                            $plan['description'],

                        'price' =>
                            $plan['price'],

                        'currency' =>
                            $plan['currency'],

                        'billing_interval' =>
                            $plan['billing_interval'],

                        'billing_interval_count' =>
                            $plan[
                                'billing_interval_count'
                            ],

                        'trial_days' =>
                            $plan['trial_days'],

                        'features' =>
                            $plan['features'],

                        'is_active' =>
                            $plan['is_active'],

                        'sort_order' =>
                            $plan['sort_order'],

                        'created_at' =>
                            $now,

                        'updated_at' =>
                            $now,
                    ]
                );
        }
    }
}