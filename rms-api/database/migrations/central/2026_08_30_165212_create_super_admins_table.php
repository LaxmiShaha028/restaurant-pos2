<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('central')->hasTable('super_admins')) {
            return;
        }

        Schema::connection('central')
            ->create('super_admins', function (Blueprint $table): void {

                $table->id();

                $table->string(
                    'name',
                    150
                );

                $table->string(
                    'username',
                    100
                )->unique();

                $table->string(
                    'email',
                    150
                )->unique();

                $table->timestamp(
                    'email_verified_at'
                )->nullable();

                $table->string(
                    'password'
                );

                $table->rememberToken();

                $table->boolean(
                    'is_active'
                )->default(true);

                $table->timestamp(
                    'last_login_at'
                )->nullable();

                $table->timestamp(
                    'last_logout_at'
                )->nullable();

                $table->unsignedSmallInteger(
                    'failed_login_attempts'
                )->default(0);

                $table->timestamp(
                    'blocked_at'
                )->nullable();

                $table->timestamps();

                $table->softDeletes();

                $table->index(
                    'is_active'
                );

                $table->index(
                    'blocked_at'
                );
            });
    }

    public function down(): void
    {
        Schema::connection('central')
            ->dropIfExists('super_admins');
    }
};
