<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    App\Providers\PurchaseReturnAccountingServiceProvider::class,
    App\Providers\InventoryConsumptionAccountingServiceProvider::class,
];
