<?php

namespace App\Providers;

use App\Models\OrderRecipeConsumption;
use App\Observers\OrderRecipeConsumptionAccountingObserver;
use Illuminate\Support\ServiceProvider;

class InventoryConsumptionAccountingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }


    public function boot(): void
    {
        OrderRecipeConsumption::observe(
            OrderRecipeConsumptionAccountingObserver::class
        );
    }
}
