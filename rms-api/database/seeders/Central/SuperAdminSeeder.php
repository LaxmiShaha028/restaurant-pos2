<?php

namespace Database\Seeders\Central;

use App\Models\Central\SuperAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $name = trim((string) env('SUPER_ADMIN_NAME'));
        $username = strtolower(trim((string) env('SUPER_ADMIN_USERNAME')));
        $email = strtolower(trim((string) env('SUPER_ADMIN_EMAIL')));
        $password = (string) env('SUPER_ADMIN_PASSWORD');

        if ($name === '' || $username === '' || $email === '' || $password === '') {
            throw new RuntimeException('Set the SUPER_ADMIN_NAME, SUPER_ADMIN_USERNAME, SUPER_ADMIN_EMAIL, and SUPER_ADMIN_PASSWORD Render variables.');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('SUPER_ADMIN_EMAIL must be a valid email address.');
        }

        SuperAdmin::query()->firstOrCreate(
            ['username' => $username],
            [
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'is_active' => true,
            ]
        );
    }
}
