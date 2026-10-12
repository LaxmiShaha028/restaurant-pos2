<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Tenant Sanctum Personal Access Token
        |--------------------------------------------------------------------------
        |
        | Tenant API authentication must always use the tenant database
        | personal_access_tokens table.
        |
        */

        Sanctum::usePersonalAccessTokenModel(
            PersonalAccessToken::class
        );
    }
}