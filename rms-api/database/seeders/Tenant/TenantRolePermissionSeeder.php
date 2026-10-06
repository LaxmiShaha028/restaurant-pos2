<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $connection = DB::connection('tenant');

        /*
        |--------------------------------------------------------------------------
        | Role IDs
        |--------------------------------------------------------------------------
        */

        $adminRoleId =
            $connection
                ->table('roles')
                ->where('name', 'admin')
                ->value('id');

        $managerRoleId =
            $connection
                ->table('roles')
                ->where('name', 'manager')
                ->value('id');

        $chefRoleId =
            $connection
                ->table('roles')
                ->where('name', 'chef')
                ->value('id');

        $waiterRoleId =
            $connection
                ->table('roles')
                ->where('name', 'waiter')
                ->value('id');


        /*
        |--------------------------------------------------------------------------
        | All Permissions
        |--------------------------------------------------------------------------
        */

        $permissions =
            $connection
                ->table('permissions')
                ->pluck('id', 'name');


        /*
        |--------------------------------------------------------------------------
        | Helper
        |--------------------------------------------------------------------------
        */

        $assignPermissions = function (
            ?int $roleId,
            array $permissionNames
        ) use (
            $connection,
            $permissions
        ): void {

            if (! $roleId) {
                return;
            }

            foreach ($permissionNames as $permissionName) {

                if (! isset($permissions[$permissionName])) {
                    continue;
                }

                $connection
                    ->table('permission_role')
                    ->updateOrInsert(
                        [
                            'permission_id' =>
                                $permissions[$permissionName],

                            'role_id' =>
                                $roleId,
                        ],
                        [
                            'updated_at' =>
                                now(),

                            'created_at' =>
                                now(),
                        ]
                    );
            }
        };


        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        |
        | Admin receives every available permission.
        |
        */

        if ($adminRoleId) {

            foreach ($permissions as $permissionId) {

                $connection
                    ->table('permission_role')
                    ->updateOrInsert(
                        [
                            'permission_id' =>
                                $permissionId,

                            'role_id' =>
                                $adminRoleId,
                        ],
                        [
                            'updated_at' =>
                                now(),

                            'created_at' =>
                                now(),
                        ]
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | MANAGER
        |--------------------------------------------------------------------------
        */

        $assignPermissions(
            $managerRoleId,
            [

                'dashboard.view',

                'customers.manage',

                'orders.create',
                'orders.view',

                'kitchen.view',

                'tables.manage',

                'billing.view',

                'reservations.manage',

                'menu.manage',

                'inventory.view',
                'inventory.manage',

                'suppliers.manage',

                'staff.manage',

                'salary.manage',

                'expenses.manage',

                'reports.view',

            ]
        );


        /*
        |--------------------------------------------------------------------------
        | CHEF
        |--------------------------------------------------------------------------
        */

        $assignPermissions(
            $chefRoleId,
            [

                'dashboard.view',

                'orders.view',

                'kitchen.view',

                'tables.manage',

            ]
        );


        /*
        |--------------------------------------------------------------------------
        | WAITER
        |--------------------------------------------------------------------------
        */

        $assignPermissions(
            $waiterRoleId,
            [

                'dashboard.view',

                'customers.manage',

                'orders.create',
                'orders.view',

                'tables.manage',

                'reservations.manage',

            ]
        );
    }
}