<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    public function run(
        array $restaurant = []
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $this->call(
            TenantPermissionSeeder::class
        );


        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $this->call(
            TenantRoleSeeder::class
        );


        /*
        |--------------------------------------------------------------------------
        | Role Permissions
        |--------------------------------------------------------------------------
        */

        $this->call(
            TenantRolePermissionSeeder::class
        );


        /*
        |--------------------------------------------------------------------------
        | Tenant Admin User
        |--------------------------------------------------------------------------
        */

        $this->callWith(
            TenantUserSeeder::class,
            [
                'restaurant' =>
                    $restaurant,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | System Settings
        |--------------------------------------------------------------------------
        */

        $this->callWith(
            TenantSystemSettingSeeder::class,
            [
                'restaurant' =>
                    $restaurant,
            ]
        );
    }
}