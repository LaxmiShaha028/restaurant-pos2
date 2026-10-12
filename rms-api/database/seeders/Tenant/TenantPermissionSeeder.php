<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $permissions = [

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'dashboard.view',
                'display_name' => 'View Dashboard',
                'module' => 'dashboard',
                'description' => 'Access restaurant dashboard.',
            ],


            /*
            |--------------------------------------------------------------------------
            | Users
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'users.manage',
                'display_name' => 'Manage Users',
                'module' => 'users',
                'description' => 'Manage restaurant users.',
            ],


            /*
            |--------------------------------------------------------------------------
            | Settings
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'settings.manage',
                'display_name' => 'Manage Settings',
                'module' => 'settings',
                'description' => 'Manage restaurant settings and branding.',
            ],


            /*
            |--------------------------------------------------------------------------
            | Customers
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'customers.manage',
                'display_name' => 'Manage Customers',
                'module' => 'customers',
                'description' => 'Manage restaurant customers.',
            ],


            /*
            |--------------------------------------------------------------------------
            | Orders
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'orders.create',
                'display_name' => 'Create Orders',
                'module' => 'orders',
                'description' => 'Create restaurant orders.',
            ],

            [
                'name' => 'orders.view',
                'display_name' => 'View Orders',
                'module' => 'orders',
                'description' => 'View restaurant orders.',
            ],


            /*
            |--------------------------------------------------------------------------
            | Kitchen
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'kitchen.view',
                'display_name' => 'View Kitchen',
                'module' => 'kitchen',
                'description' => 'Access kitchen operations.',
            ],


            /*
            |--------------------------------------------------------------------------
            | Tables
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'tables.manage',
                'display_name' => 'Manage Tables',
                'module' => 'tables',
                'description' => 'Manage restaurant tables and table availability.',
            ],


            /*
            |--------------------------------------------------------------------------
            | Billing
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'billing.view',
                'display_name' => 'View Billing',
                'module' => 'billing',
                'description' => 'Access billing and settlement information.',
            ],


            /*
            |--------------------------------------------------------------------------
            | Reservations
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'reservations.manage',
                'display_name' => 'Manage Reservations',
                'module' => 'reservations',
                'description' => 'Manage restaurant reservations.',
            ],


            /*
            |--------------------------------------------------------------------------
            | Menu
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'menu.manage',
                'display_name' => 'Manage Menu',
                'module' => 'menu',
                'description' => 'Manage restaurant menu items and categories.',
            ],


            /*
            |--------------------------------------------------------------------------
            | Inventory
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'inventory.view',
                'display_name' => 'View Inventory',
                'module' => 'inventory',
                'description' => 'View warehouse and restaurant inventory.',
            ],

            [
                'name' => 'inventory.manage',
                'display_name' => 'Manage Inventory',
                'module' => 'inventory',
                'description' => 'Manage stock and inventory transactions.',
            ],


            /*
            |--------------------------------------------------------------------------
            | Suppliers
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'suppliers.manage',
                'display_name' => 'Manage Suppliers',
                'module' => 'suppliers',
                'description' => 'Manage restaurant suppliers.',
            ],


            /*
            |--------------------------------------------------------------------------
            | Staff
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'staff.manage',
                'display_name' => 'Manage Staff',
                'module' => 'staff',
                'description' => 'Manage restaurant staff.',
            ],


            /*
            |--------------------------------------------------------------------------
            | Salary
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'salary.manage',
                'display_name' => 'Manage Salary',
                'module' => 'salary',
                'description' => 'Manage employee salary and payroll.',
            ],


            /*
            |--------------------------------------------------------------------------
            | Expenses
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'expenses.manage',
                'display_name' => 'Manage Expenses',
                'module' => 'expenses',
                'description' => 'Manage restaurant expenses.',
            ],


            /*
            |--------------------------------------------------------------------------
            | Reports
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'reports.view',
                'display_name' => 'View Reports',
                'module' => 'reports',
                'description' => 'View restaurant reports.',
            ],


            /*
            |--------------------------------------------------------------------------
            | Audit Logs
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'audit_logs.view',
                'display_name' => 'View Audit Log',
                'module' => 'audit_logs',
                'description' => 'View restaurant audit logs.',
            ],
        ];


        foreach ($permissions as $permission) {

            DB::connection('tenant')
                ->table('permissions')
                ->updateOrInsert(
                    [
                        'name' =>
                            $permission['name'],
                    ],
                    [
                        'display_name' =>
                            $permission['display_name'],

                        'module' =>
                            $permission['module'],

                        'description' =>
                            $permission['description'],

                        'updated_at' =>
                            $now,

                        'created_at' =>
                            $now,
                    ]
                );
        }
    }
}