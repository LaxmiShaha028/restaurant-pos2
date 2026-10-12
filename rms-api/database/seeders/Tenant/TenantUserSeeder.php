<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantUserSeeder extends Seeder
{
    public function run(array $restaurant = []): void
    {
        $roleId = DB::connection('tenant')
            ->table('roles')
            ->where('name', 'admin')
            ->value('id');

        if (! $roleId) {
            throw new \RuntimeException(
                'Tenant admin role was not found.'
            );
        }

        $ownerName = trim(
            (string) (
                $restaurant['owner_name']
                ?? 'Restaurant Administrator'
            )
        );

        $ownerEmail = trim(
            strtolower(
                (string) (
                    $restaurant['owner_email']
                    ?? ''
                )
            )
        );

        $username = trim(
            (string) (
                $restaurant['username']
                ?? 'admin'
            )
        );

        $password = (string) (
            $restaurant['password']
            ?? 'ChangeMe123!'
        );

        if ($ownerEmail === '') {
            throw new \InvalidArgumentException(
                'Restaurant owner email is required.'
            );
        }

        DB::connection('tenant')
            ->table('users')
            ->updateOrInsert(
                [
                    'username' => $username,
                ],
                [
                    'role_id' => $roleId,
                    'name' => $ownerName,
                    'email' => $ownerEmail,
                    'password' => Hash::make($password),
                    'is_active' => true,
                    'failed_login_attempts' => 0,
                    'blocked_at' => null,
                    'last_login_at' => null,
                    'last_logout_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
    }
}