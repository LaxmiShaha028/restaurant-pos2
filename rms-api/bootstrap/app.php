<?php

use App\Http\Middleware\EnsureUserHasPermission;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\AuthenticateTenantApiToken;
use App\Http\Middleware\ResolveTenant;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
        |--------------------------------------------------------------------------
        | Tenant Middleware Priority
        |--------------------------------------------------------------------------
        |
        | Tenant database MUST be resolved before:
        |
        | 1. tenant authentication
        | 2. Laravel implicit route model binding
        |
        | Otherwise routes such as:
        |
        | /purchase-orders/{purchaseOrder}
        | /purchase-orders/{purchaseOrder}/receive
        |
        | may try to resolve PurchaseOrder from the wrong/default database.
        |
        */

        $middleware->prependToPriorityList(

            before: [

                AuthenticateTenantApiToken::class,

                SubstituteBindings::class,

            ],

            prepend:
                ResolveTenant::class

        );


        $middleware->prependToPriorityList(

            before:
                SubstituteBindings::class,

            prepend:
                AuthenticateTenantApiToken::class

        );
        $middleware->alias([
            'permission' => EnsureUserHasPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (
            AuthenticationException $exception,
            Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'data' => null,
                    'errors' => null,
                ], 401);
            }

            return null;
        });
    })
    ->create();