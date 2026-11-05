<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->file(public_path('index.html'));
});

use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

Route::middleware([
    \App\Http\Middleware\ResolveTenant::class,
])->get(
    '/tenant-test',
    function (Request $request) {

        $tenant =
            $request->attributes->get(
                'tenant'
            );

        return response()->json([

            'tenant_id' =>
                $tenant?->id,

            'tenant_name' =>
                $tenant?->name,

            'tenant_key' =>
                $tenant?->tenant_key,

            'database_default' =>
                DB::getDefaultConnection(),

            'database_name' =>
                DB::connection()
                    ->getDatabaseName(),

            'users_count' =>
                User::query()
                    ->count(),

            'menu_items_count' =>
                MenuItem::query()
                    ->count(),

            'customers_count' =>
                Customer::query()
                    ->count(),

            'orders_count' =>
                Order::query()
                    ->count(),
            'central_restaurants_count' =>
                \App\Models\Central\Restaurant::query()
                    ->count(),
        ]);
    }
);

Route::get('/{path?}', function () {
    return response()->file(public_path('index.html'));
})->where('path', '^(?!api(?:/|$)|up$).*$');
