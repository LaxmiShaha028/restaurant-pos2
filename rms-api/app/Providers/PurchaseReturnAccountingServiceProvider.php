<?php

namespace App\Providers;

use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnSettlement;
use App\Models\SupplierCreditApplication;
use App\Observers\PurchaseReturnAccountingObserver;
use App\Observers\PurchaseReturnSettlementAccountingObserver;
use App\Observers\SupplierCreditApplicationAccountingObserver;
use Illuminate\Support\ServiceProvider;

class PurchaseReturnAccountingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }


    public function boot(): void
    {
        PurchaseReturn::observe(
            PurchaseReturnAccountingObserver::class
        );

        PurchaseReturnSettlement::observe(
            PurchaseReturnSettlementAccountingObserver::class
        );

        SupplierCreditApplication::observe(
            SupplierCreditApplicationAccountingObserver::class
        );
    }
}
