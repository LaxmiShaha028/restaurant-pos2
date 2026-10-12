<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantRoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $roles = [

            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' =>
                    'Full access to the restaurant management system.',
                'is_active' => true,
            ],

            [
                'name' => 'manager',
                'display_name' => 'Manager',
                'description' =>
                    'Manage restaurant operations, staff, inventory and reports.',
                'is_active' => true,
            ],

            [
                'name' => 'chef',
                'display_name' => 'Chef',
                'description' =>
                    'Kitchen and food preparation management access.',
                'is_active' => true,
            ],

            [
                'name' => 'waiter',
                'display_name' => 'Waiter',
                'description' =>
                    'Order taking and front desk operational access.',
                'is_active' => true,
            ],
        ];


        foreach ($roles as $role) {

            DB::connection('tenant')
                ->table('roles')
                ->updateOrInsert(
                    [
                        'name' =>
                            $role['name'],
                    ],
                    [
                        'display_name' =>
                            $role['display_name'],

                        'description' =>
                            $role['description'],

                        'is_active' =>
                            $role['is_active'],

                        'updated_at' =>
                            $now,

                        'created_at' =>
                            $now,
                    ]
                );
        }
    }
}