<?php
use App\Http\Controllers\Central\TenantProvisioningController;
use App\Http\Controllers\Central\RestaurantController;
use App\Http\Controllers\Central\SubscriptionPaymentController;
use App\Http\Controllers\Api\AddOnController;
use App\Http\Controllers\Api\SupplierCreditController;
use App\Http\Controllers\Api\PurchaseReturnAccountingController;
use App\Http\Controllers\Api\InventoryDocumentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExpenseCategoryController;
use App\Http\Controllers\Api\PurchaseReturnSettlementController;
use App\Http\Controllers\Api\PurchaseReturnController;
use App\Http\Controllers\Api\WarehouseStockLotController;
use App\Http\Controllers\Central\CentralDashboardController;
use App\Http\Controllers\Central\CentralPaymentController;
use App\Http\Controllers\Central\CentralNotificationController;
use App\Http\Controllers\Central\PaymentMethodManagementController;
use App\Http\Controllers\Central\SubscriptionPlanManagementController;
use App\Http\Controllers\Central\SuperAdminAuthController;
use App\Http\Middleware\AuthenticateSuperAdmin;
use App\Http\Controllers\Auth\TenantAuthController;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\AuthenticateTenantApiToken;
use App\Http\Middleware\EnsureInventoryAccess;
use App\Http\Middleware\EnsureOrderCanBeModified;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MenuCategoryController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\MenuItemVariantController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RestaurantTableController;
use App\Http\Controllers\Api\OrderOptionsController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderManagement\PaymentController;
use App\Http\Controllers\ReservationManagement\ReservationController;
use App\Http\Controllers\Api\KitchenOrderController;
use App\Http\Controllers\Api\OrderInvoiceController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\ChartOfAccountController;
use App\Http\Controllers\Api\JournalEntryController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ShiftScheduleController;
use App\Http\Controllers\Api\ShiftScheduleOverrideController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\SalaryController;
use App\Http\Controllers\Api\SalaryDetailController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\PurchaseOrderPaymentController;
use App\Http\Controllers\Api\StockTransferController;
use App\Http\Controllers\Api\InventoryWastageController;
use App\Http\Controllers\Api\StockTransferTemplateController;
use App\Http\Controllers\Api\RecipeMappingController;
use App\Http\Controllers\Api\OrderRecipeConsumptionController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReportExportController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\SystemSettingController;
use App\Http\Controllers\Api\TestAttendanceController;
use App\Http\Controllers\Central\PaymentMethodController;
use App\Http\Controllers\Tenant\TenantEntryController;
use App\Http\Controllers\Api\BusinessOverviewController;
use App\Http\Controllers\Api\PickupController;



/*
|--------------------------------------------------------------------------
| Public Tenant Entry
|--------------------------------------------------------------------------
*/

Route::get(
    '/tenant/resolve/{tenantKey}',
    [
        TenantEntryController::class,
        'resolve',
    ]
);

/*
|--------------------------------------------------------------------------
| API Health
|--------------------------------------------------------------------------
*/

Route::get('/health', function (): JsonResponse {
    try {
        DB::connection()->getPdo();

        $databaseStatus = 'connected';
    } catch (\Throwable) {
        $databaseStatus = 'disconnected';
    }

    return response()->json(
        [
            'success' =>
                $databaseStatus === 'connected',

            'message' =>
                'Restaurant Management System API health status.',

            'data' => [
                'application' =>
                    config('app.name'),

                'environment' =>
                    app()->environment(),

                'timezone' =>
                    config('app.timezone'),

                'database' =>
                    $databaseStatus,

                'timestamp' =>
                    now()->toISOString(),
            ],

            'errors' => null,
        ],
        $databaseStatus === 'connected'
            ? 200
            : 503
    );
});
// /*
// |--------------------------------------------------------------------------
// | SaaS Tenant Provisioning Test
// |--------------------------------------------------------------------------
// |
// | Temporary development endpoint.
// |
// */

// Route::post(
//     '/central/tenant-provision',
//     [
//         TenantProvisioningController::class,
//         'store',
//     ]
// )->middleware(
//     AuthenticateSuperAdmin::class
// );
/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::prefix('auth')
    ->group(function (): void {
        Route::post(
            '/login',
            [
                AuthController::class,
                'login',
            ]
        );

        Route::middleware('auth:sanctum')
            ->group(function (): void {
                Route::get(
                    '/me',
                    [
                        AuthController::class,
                        'me',
                    ]
                );

                Route::post(
                    '/logout',
                    [
                        AuthController::class,
                        'logout',
                    ]
                );

                Route::post(
                    '/logout-all',
                    [
                        AuthController::class,
                        'logoutAll',
                    ]
                );
                
            });
    });



/*
|--------------------------------------------------------------------------
| Super Admin Authentication
|--------------------------------------------------------------------------
*/

Route::prefix('central/auth')
    ->group(function (): void {

        /*
        |--------------------------------------------------------------------------
        | Login
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/login',
            [
                SuperAdminAuthController::class,
                'login',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Protected Central Authentication
        |--------------------------------------------------------------------------
        */

        Route::middleware(
            AuthenticateSuperAdmin::class
        )->group(function (): void {

            Route::get(
                '/me',
                [
                    SuperAdminAuthController::class,
                    'me',
                ]
            );

            Route::post(
                '/logout',
                [
                    SuperAdminAuthController::class,
                    'logout',
                ]
            );

            
        });
    });

    /*
|--------------------------------------------------------------------------
| Super Admin Central Management
|--------------------------------------------------------------------------
*/

Route::prefix('central')
    ->middleware(
        AuthenticateSuperAdmin::class
    )
    ->group(function (): void {


    Route::get(
        '/dashboard',
        [
            CentralDashboardController::class,
            'overview',
        ]
    );

    /*
    |--------------------------------------------------------------------------
    | Subscription Expiry Notifications
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/notifications/subscription-expiring',
        [
            CentralNotificationController::class,
            'subscriptionExpiring',
        ]
    );

    /*
    |--------------------------------------------------------------------------
    | Central Management — Subscription Plans / Payment Methods
    |--------------------------------------------------------------------------
    |
    | These endpoints are separate from the existing list endpoints used by
    | restaurant creation and payment forms.
    |
    */

    Route::prefix('management')
        ->group(function (): void {

            /*
            |--------------------------------------------------------------------------
            | Subscription Plans
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/subscription-plans',
                [
                    SubscriptionPlanManagementController::class,
                    'index',
                ]
            );

            Route::post(
                '/subscription-plans',
                [
                    SubscriptionPlanManagementController::class,
                    'store',
                ]
            );

            Route::put(
                '/subscription-plans/{plan}',
                [
                    SubscriptionPlanManagementController::class,
                    'update',
                ]
            );

            Route::patch(
                '/subscription-plans/{plan}/status',
                [
                    SubscriptionPlanManagementController::class,
                    'updateStatus',
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Payment Methods
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/payment-methods',
                [
                    PaymentMethodManagementController::class,
                    'index',
                ]
            );

            Route::post(
                '/payment-methods',
                [
                    PaymentMethodManagementController::class,
                    'store',
                ]
            );

            Route::put(
                '/payment-methods/{method}',
                [
                    PaymentMethodManagementController::class,
                    'update',
                ]
            );

            Route::patch(
                '/payment-methods/{method}/status',
                [
                    PaymentMethodManagementController::class,
                    'updateStatus',
                ]
            );
        });


    /*
    |--------------------------------------------------------------------------
    | Central Subscription Lists
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/subscriptions',
        [
            SubscriptionPaymentController::class,
            'listSubscriptions',
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Central Payments
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/payments/transactions',
        [
            CentralPaymentController::class,
            'transactions',
        ]
    );

    Route::get(
        '/payments/revenue',
        [
            CentralPaymentController::class,
            'revenue',
        ]
    );

    Route::post(
        '/restaurants/{restaurant}/subscriptions/retry-provisioning',
        [
            SubscriptionPaymentController::class,
            'retryProvisioning',
        ]
    );
    Route::post(
        '/restaurants/{restaurant}/sync-permissions',
        [RestaurantController::class, 'syncTenantPermissions']
    );
    Route::post(
    '/restaurants/sync-all-permissions',
    [RestaurantController::class, 'syncAllTenantPermissions']
);
Route::post(
    '/restaurants/{restaurant}/subscriptions/upgrade',
    [SubscriptionPaymentController::class, 'upgradeTrialToPaid']
);

/*
|--------------------------------------------------------------------------
| Active Paid Plan Update
|--------------------------------------------------------------------------
*/

Route::post(
    '/restaurants/{restaurant}/subscriptions/plan',
    [
        SubscriptionPaymentController::class,
        'updatePaidPlan',
    ]
);

/*
|--------------------------------------------------------------------------
| Subscription Renewal Payment
|--------------------------------------------------------------------------
|
| Expired / cancelled paid subscriptions renew directly through payment.
| Keep only ONE POST route for this URI.
|
*/

Route::post(
    '/restaurants/{restaurant}/subscriptions/renew',
    [
        SubscriptionPaymentController::class,
        'completeRenewalPayment',
    ]
);

/*
|--------------------------------------------------------------------------
| Subscription Details
|--------------------------------------------------------------------------
*/

Route::get(
    '/restaurants/{restaurant}/subscription',
    [
        SubscriptionPaymentController::class,
        'subscription',
    ]
);

/*
|--------------------------------------------------------------------------
| Update Subscription Status
|--------------------------------------------------------------------------
*/

Route::patch(
    '/restaurants/{restaurant}/subscription/status',
    [
        SubscriptionPaymentController::class,
        'updateStatus',
    ]
);

/*
|--------------------------------------------------------------------------
| Payment History
|--------------------------------------------------------------------------
*/

Route::get(
    '/restaurants/{restaurant}/payments',
    [
        SubscriptionPaymentController::class,
        'history',
    ]
);
Route::patch(
    '/restaurants/{restaurant}/status',
    [
        RestaurantController::class,
        'updateStatus',
    ]
);


    /*
|--------------------------------------------------------------------------
| Subscription Payments
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Payment Methods
|--------------------------------------------------------------------------
*/

Route::get(
    '/payment-methods',
    [
        PaymentMethodController::class,
        'index',
    ]
);

/*
|--------------------------------------------------------------------------
| Subscription Payments
|--------------------------------------------------------------------------
*/

Route::post(
    '/restaurants/{restaurant}/subscriptions/payment',
    [
        SubscriptionPaymentController::class,
        'complete',
    ]
);
    /*
    |--------------------------------------------------------------------------
    | Restaurants
    |--------------------------------------------------------------------------
    */

    Route::prefix('restaurants')
        ->group(function (): void {

        Route::get(
            '/',
            [
                RestaurantController::class,
                'index',
            ]
        );

        Route::get(
            '/plans',
            [
                RestaurantController::class,
                'plans',
            ]
        );

        Route::post(
            '/',
            [
                RestaurantController::class,
                'store',
            ]
        );
        Route::post(
            '/{restaurant}/reset-admin-credentials',
            [RestaurantController::class, 'resetAdminCredentials']
        );

        Route::get(
            '/{restaurant}',
            [
                RestaurantController::class,
                'show',
            ]
        );
    });
});

/*
|--------------------------------------------------------------------------
| Tenant Authentication
|--------------------------------------------------------------------------
|
| Tenant login:
|     ResolveTenant only
|
| Tenant protected authentication:
|     ResolveTenant
|     +
|     AuthenticateTenantApiToken
|
*/

Route::middleware([
    ResolveTenant::class,
])->group(function (): void {

    /*
    |--------------------------------------------------------------------------
    | Tenant Login
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/tenant/login',
        [
            TenantAuthController::class,
            'login',
        ]
    );

    /*
    |--------------------------------------------------------------------------
    | Tenant Protected Authentication
    |--------------------------------------------------------------------------
    */

    Route::middleware(
        AuthenticateTenantApiToken::class
    )->group(function (): void {

        Route::get(
            '/tenant/me',
            [
                TenantAuthController::class,
                'me',
            ]
        );

        Route::post(
            '/tenant/logout',
            [
                TenantAuthController::class,
                'logout',
            ]
        );
    });
});

/*
|--------------------------------------------------------------------------
| Protected Application Routes
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Protected Tenant Application Routes
|--------------------------------------------------------------------------
|
| Every restaurant-specific API request must:
|
| 1. Resolve the tenant from the request domain.
| 2. Connect to the correct tenant database.
| 3. Authenticate the bearer token against the current tenant database.
|
*/

Route::middleware([
    ResolveTenant::class,
    AuthenticateTenantApiToken::class,
])
    ->group(function (): void {
        
        Route::prefix('test/attendance')
    ->controller(TestAttendanceController::class)
    ->middleware(
        'permission:staff.manage'
    )
    ->group(function (): void {

        Route::post(
            '/',
            'create'
        );

    });
        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/dashboard',
            [
                DashboardController::class,
                'overview',
            ]
        )->middleware(
            'permission:dashboard.view'
        );

        Route::get(
            '/admin/overview',
            [
                DashboardController::class,
                'adminOverview',
            ]
        )->middleware(
            'permission:users.manage'
        );



        Route::get(
            '/manager/overview',
            [
                DashboardController::class,
                'managerOverview',
            ]
        )->middleware(
            'permission:orders.create'
        );

        Route::get(
            '/kitchen/overview',
            [
                DashboardController::class,
                'kitchenOverview',
            ]
        )->middleware(
            'permission:kitchen.view'
        );


        /*
|--------------------------------------------------------------------------
| Admin User Management
|--------------------------------------------------------------------------
*/

Route::prefix('admin/users')
    ->controller(
        AdminUserController::class
    )
    ->middleware(
        'permission:users.manage'
    )
    ->group(function () {


        Route::get(
            '/',
            'index'
        );
        Route::get(
            '/roles',
            'roles'
        );
        Route::post(
            '/',
            'store'
        );

        Route::get(
            '/{user}',
            'show'
        );


        Route::put(
            '/{user}',
            'update'
        );


        Route::delete(
            '/{user}',
            'destroy'
        );


        Route::patch(
            '/{user}/status',
            'toggleStatus'
        );


    });
/*
|--------------------------------------------------------------------------
| System Settings
|--------------------------------------------------------------------------
*/

Route::prefix('settings')
    ->name('settings.')
    ->middleware(
        'permission:settings.manage'
    )
    ->group(function (): void {

        Route::get(
            '/',
            [
                SystemSettingController::class,
                'show',
            ]
        )->name('show');

        Route::put(
            '/',
            [
                SystemSettingController::class,
                'update',
            ]
        )->name('update');

    });
    // /*
    // |--------------------------------------------------------------------------
    // | Restaurant Branding
    // |--------------------------------------------------------------------------
    // */

    Route::get(
        '/system-settings',
        [
            SystemSettingController::class,
            'publicShow',
        ]
    )->middleware(
        'permission:orders.create,billing.view,settings.manage'
    );

/*
|--------------------------------------------------------------------------
| Audit Logs
|--------------------------------------------------------------------------
*/

Route::prefix('admin/audit-logs')
    ->controller(
        AuditLogController::class
    )
    ->middleware(
        'permission:audit_logs.view'
    )
    ->group(function () {


        Route::get(
            '/',
            'index'
        );


        Route::get(
            '/{auditLog}',
            'show'
        );


    });

        /*
        |--------------------------------------------------------------------------
        | Menu Management
        |--------------------------------------------------------------------------
        */

        Route::prefix('menu-management')
            ->name('menu-management.')
            ->middleware(
                'permission:menu.manage'
            )
            ->group(function (): void {
                /*
                |--------------------------------------------------------------------------
                | Menu Categories
                |--------------------------------------------------------------------------
                */

                Route::patch(
                    '/menu-categories/{menuCategory}/status',
                    [
                        MenuCategoryController::class,
                        'toggleStatus',
                    ]
                )->name(
                    'menu-categories.toggle-status'
                );

                Route::apiResource(
                    'menu-categories',
                    MenuCategoryController::class
                )->parameters([
                    'menu-categories' =>
                        'menuCategory',
                ]);

                /*
                |--------------------------------------------------------------------------
                | Menu Items
                |--------------------------------------------------------------------------
                */

                Route::patch(
                    '/menu-items/{menuItem}/status',
                    [
                        MenuItemController::class,
                        'toggleStatus',
                    ]
                )->name(
                    'menu-items.toggle-status'
                );

                Route::patch(
                    '/menu-items/{menuItem}/featured',
                    [
                        MenuItemController::class,
                        'toggleFeatured',
                    ]
                )->name(
                    'menu-items.toggle-featured'
                );

                Route::apiResource(
                    'menu-items',
                    MenuItemController::class
                )->parameters([
                    'menu-items' =>
                        'menuItem',
                ]);

                /*
                |--------------------------------------------------------------------------
                | Menu Item Variants
                |--------------------------------------------------------------------------
                */

                Route::patch(
                    '/menu-variants/{menuVariant}/status',
                    [
                        MenuItemVariantController::class,
                        'toggleStatus',
                    ]
                )->name(
                    'menu-variants.toggle-status'
                );

                Route::apiResource(
                    'menu-variants',
                    MenuItemVariantController::class
                )->parameters([
                    'menu-variants' =>
                        'menuVariant',
                ]);

                /*
                |--------------------------------------------------------------------------
                | Add-ons
                |--------------------------------------------------------------------------
                */

                Route::patch(
                    '/add-ons/{addOn}/status',
                    [
                        AddOnController::class,
                        'toggleStatus',
                    ]
                )->name(
                    'add-ons.toggle-status'
                );

                Route::apiResource(
                    'add-ons',
                    AddOnController::class
                )->parameters([
                    'add-ons' => 'addOn',
                ]);
            });

            /*
|--------------------------------------------------------------------------
| Reservation Management
|--------------------------------------------------------------------------
*/

Route::prefix('reservations')
    ->name('reservations.')
    ->middleware(
        'permission:reservations.manage'
    )
    ->group(function (): void {


        /*
        |--------------------------------------------------------------------------
        | Available Tables
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/available-tables',
            [
                ReservationController::class,
                'availableTables',
            ]
        )->name(
            'available-tables'
        );



        /*
        |--------------------------------------------------------------------------
        | Reservation List
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/',
            [
                ReservationController::class,
                'index',
            ]
        )->name(
            'index'
        );



        /*
        |--------------------------------------------------------------------------
        | Create Reservation
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/',
            [
                ReservationController::class,
                'store',
            ]
        )->name(
            'store'
        );



        /*
        |--------------------------------------------------------------------------
        | Edit Options
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/{reservation}/edit-options',
            [
                ReservationController::class,
                'editOptions',
            ]
        )->name(
            'edit-options'
        );



        /*
        |--------------------------------------------------------------------------
        | Seat Reservation
        |--------------------------------------------------------------------------
        |
        | Change reservation status:
        | reserved → seated
        |
        */

        Route::post(
            '/{reservation}/seat',
            [
                ReservationController::class,
                'seat',
            ]
        )->name(
            'seat'
        );



        /*
        |--------------------------------------------------------------------------
        | Cancel Reservation
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/{reservation}/cancel',
            [
                ReservationController::class,
                'cancel',
            ]
        )->name(
            'cancel'
        );



        /*
        |--------------------------------------------------------------------------
        | Show Single Reservation
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/{reservation}',
            [
                ReservationController::class,
                'show',
            ]
        )->name(
            'show'
        );



        /*
        |--------------------------------------------------------------------------
        | Update Reservation
        |--------------------------------------------------------------------------
        */

        Route::put(
            '/{reservation}',
            [
                ReservationController::class,
                'update',
            ]
        )->name(
            'update'
        );


    });

        /*
        |--------------------------------------------------------------------------
        | Table Management
        |--------------------------------------------------------------------------
        */

        Route::prefix('table-management')
            ->name('table-management.')
            ->middleware(
                'permission:tables.manage'
            )
            ->group(function (): void {
                /*
                |--------------------------------------------------------------------------
                | Restaurant table list and create
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/tables',
                    [
                        RestaurantTableController::class,
                        'index',
                    ]
                )->name('tables.index');

                Route::post(
                    '/tables',
                    [
                        RestaurantTableController::class,
                        'store',
                    ]
                )->name('tables.store');

                /*
                |--------------------------------------------------------------------------
                | Merge and split
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/tables/{restaurantTable}/merge-options',
                    [
                        RestaurantTableController::class,
                        'mergeOptions',
                    ]
                )->name(
                    'tables.merge-options'
                );

                Route::post(
                    '/tables/{restaurantTable}/merge',
                    [
                        RestaurantTableController::class,
                        'merge',
                    ]
                )->name('tables.merge');

                Route::post(
                    '/tables/{restaurantTable}/split',
                    [
                        RestaurantTableController::class,
                        'split',
                    ]
                )->name('tables.split');

                /*
                |--------------------------------------------------------------------------
                | Edit options
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/tables/{restaurantTable}/edit-options',
                    [
                        RestaurantTableController::class,
                        'editOptions',
                    ]
                )->name(
                    'tables.edit-options'
                );

                /*
                |--------------------------------------------------------------------------
                | Single table CRUD
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/tables/{restaurantTable}',
                    [
                        RestaurantTableController::class,
                        'show',
                    ]
                )->name('tables.show');

                Route::put(
                    '/tables/{restaurantTable}',
                    [
                        RestaurantTableController::class,
                        'update',
                    ]
                )->name('tables.update');

                Route::delete(
                    '/tables/{restaurantTable}',
                    [
                        RestaurantTableController::class,
                        'destroy',
                    ]
                )->name('tables.destroy');
            });

        /*
        |--------------------------------------------------------------------------
        | Order Management
        |--------------------------------------------------------------------------
        */

        Route::prefix('order-management')
            ->name('order-management.')
            ->group(function (): void {

                Route::get('/pickup-board', [PickupController::class, 'board'])
                    ->middleware('permission:orders.view')
                    ->name('pickup-board');

                Route::post('/orders/{order}/pickup-collected', [PickupController::class, 'markCollected'])
                    ->middleware('permission:orders.update,orders.create')
                    ->name('orders.pickup-collected');

                /*
                |--------------------------------------------------------------------------
                | POS / Order Customer Search
                |--------------------------------------------------------------------------
                |
                | Read-only lookup for users who can create orders.
                | This does not grant full Customer Management access.
                |
                */

                Route::get(
                    '/customer-search',
                    [
                        CustomerController::class,
                        'search',
                    ]
                )
                    ->middleware(
                        'permission:orders.create'
                    )
                    ->name(
                        'customer-search'
                    );


                Route::get(
                    '/orders/{order}/edit-options',
                    [
                        OrderController::class,
                        'editOptions',
                    ]
                )
                    ->middleware(
                        EnsureOrderCanBeModified::class
                    )
                    ->name(
                        'orders.edit-options'
                    );

                Route::get(
                    '/payments',
                    [
                        PaymentController::class,
                        'index',
                    ]
                )->middleware(
                    'permission:orders.view,billing.view'
                );

                Route::post(
                    '/payments',
                    [
                        PaymentController::class,
                        'store',
                    ]
                )->middleware(
                    'permission:orders.create'
                );
                /*
                |--------------------------------------------------------------------------
                | Download Order Invoice
                |--------------------------------------------------------------------------
                |
                | একটি নির্দিষ্ট order-এর customer invoice PDF generate এবং download করবে।
                |
                */

                Route::get(
                    '/orders/{order}/invoice',
                    [
                        OrderInvoiceController::class,
                        'download',
                    ]
                )
                    ->middleware(
                        'permission:orders.view'
                    )
                    ->name(
                        'orders.invoice.download'
                    );

                /*
                |--------------------------------------------------------------------------
                | Create Order Options
                |--------------------------------------------------------------------------
                */
                /*
                |--------------------------------------------------------------------------
                | Update Order
                |--------------------------------------------------------------------------
                */

                Route::put(
                    '/orders/{order}',
                    [
                        OrderController::class,
                        'update',
                    ]
                )
                    ->middleware(
                        EnsureOrderCanBeModified::class
                    )
                    ->name(
                        'orders.update'
                    );

                Route::get(
                    '/create-options',
                    [
                        OrderController::class,
                        'createOptions',
                    ]
                )
                    ->middleware(
                        'permission:orders.create'
                    )
                    ->name(
                        'create-options'
                    );


                /*
                |--------------------------------------------------------------------------
                | Order list and create
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/orders',
                    [
                        OrderController::class,
                        'index',
                    ]
                )
                    ->middleware(
                        'permission:orders.view'
                    )
                    ->name(
                        'orders.index'
                    );


                Route::post(
                    '/orders',
                    [
                        OrderController::class,
                        'store',
                    ]
                )
                    ->middleware(
                        'permission:orders.create'
                    )
                    ->name(
                        'orders.store'
                    );


                /*
                |--------------------------------------------------------------------------
                | Order actions
                |--------------------------------------------------------------------------
                */


                Route::patch(
                    '/orders/{order}/status',
                    [
                        OrderController::class,
                        'updateStatus',
                    ]
                )
                    ->middleware(
                        'permission:orders.create,orders.update'
                    )
                    ->name(
                        'orders.update-status'
                    );


                Route::post(
                    '/orders/{order}/cancel',
                    [
                        OrderController::class,
                        'cancel',
                    ]
                )
                    ->middleware(
                        'permission:orders.create,orders.update'
                    )
                    ->name(
                        'orders.cancel'
                    );


                Route::post(
                    '/orders/{order}/complete',
                    [
                        OrderController::class,
                        'complete',
                    ]
                )
                    ->middleware(
                        'permission:orders.create'
                    )
                    ->name(
                        'orders.complete'
                    );


                /*
                |--------------------------------------------------------------------------
                | Single Order
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/orders/{order}',
                    [
                        OrderController::class,
                        'show',
                    ]
                )
                    ->middleware(
                        'permission:orders.view'
                    )
                    ->name(
                        'orders.show'
                    );
            });
            /*
        |--------------------------------------------------------------------------
        | Supplier Management
        |--------------------------------------------------------------------------
        */


        Route::apiResource(
            'suppliers',
            SupplierController::class
        )->middleware(
            'permission:suppliers.manage'
        );


        /*
        |--------------------------------------------------------------------------
        | Other Income — Admin + Manager Only
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | - These routes remain inside the authenticated tenant boundary.
        | - IncomeController + FormRequests enforce Admin/Manager authorization.
        | - Waiter/Chef direct API access receives HTTP 403.
        |
        */

        Route::prefix(
            'other-incomes'
        )
            ->controller(
                IncomeController::class
            )
            ->group(function (): void {

                /*
                |--------------------------------------------------------------------------
                | Static Endpoints
                |--------------------------------------------------------------------------
                |
                | Keep these BEFORE /{income}.
                |
                */

                Route::get(
                    '/summary',
                    'summary'
                );

                Route::get(
                    '/options',
                    'options'
                );

                Route::post(
                    '/categories',
                    'storeCategory'
                );


                /*
                |--------------------------------------------------------------------------
                | List / Create
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/',
                    'index'
                );

                Route::post(
                    '/',
                    'store'
                );


                /*
                |--------------------------------------------------------------------------
                | Update / Delete
                |--------------------------------------------------------------------------
                */

                Route::put(
                    '/{income}',
                    'update'
                );

                Route::delete(
                    '/{income}',
                    'destroy'
                );
            });


        
        /*
        |--------------------------------------------------------------------------
        | Chart of Accounts — Admin + Manager Only
        |--------------------------------------------------------------------------
        */

        Route::prefix(
            'accounting/chart-of-accounts'
        )
            ->controller(
                ChartOfAccountController::class
            )
            ->group(function (): void {

                /*
                |--------------------------------------------------------------------------
                | Static Endpoints
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/summary',
                    'summary'
                );

                Route::get(
                    '/options',
                    'options'
                );

                Route::get(
                    '/mappings',
                    'mappings'
                );


                /*
                |--------------------------------------------------------------------------
                | Mapping Updates
                |--------------------------------------------------------------------------
                */

                Route::put(
                    '/mappings/income-categories/{incomeCategory}',
                    'updateIncomeCategoryMapping'
                );

                Route::put(
                    '/mappings/expense-categories/{expenseCategory}',
                    'updateExpenseCategoryMapping'
                );

                Route::put(
                    '/mappings/payment-methods/{paymentMethod}',
                    'updatePaymentMapping'
                );


                /*
                |--------------------------------------------------------------------------
                | Account CRUD
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/',
                    'index'
                );

                Route::post(
                    '/',
                    'store'
                );

                Route::put(
                    '/{chartOfAccount}',
                    'update'
                );

                Route::delete(
                    '/{chartOfAccount}',
                    'destroy'
                );
            });


        /*
        |--------------------------------------------------------------------------
        | Double-Entry Journal Engine — Admin + Manager Only
        |--------------------------------------------------------------------------
        */

        Route::prefix(
            'accounting/journals'
        )
            ->controller(
                JournalEntryController::class
            )
            ->group(function (): void {

                Route::get(
                    '/options',
                    'options'
                );

                Route::get(
                    '/',
                    'index'
                );

                Route::post(
                    '/',
                    'store'
                );

                Route::post(
                    '/{journalEntry}/reverse',
                    'reverse'
                );

                Route::get(
                    '/{journalEntry}',
                    'show'
                );
            });

/*
    |--------------------------------------------------------------------------
    | Expense Management
    |--------------------------------------------------------------------------
    */


            Route::post(
    '/expense-categories',
    [
        ExpenseCategoryController::class,
        'store',
    ]
);
    Route::middleware(
        'permission:expenses.manage'
    )
    ->prefix('expenses')
    ->group(function () {

    


        /*
        |--------------------------------------------------------------------------
        | List
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/',
            [
                ExpenseController::class,
                'index'
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Summary Cards
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/summary',
            [
                ExpenseController::class,
                'summary'
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Dropdown Options
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/options',
            [
                ExpenseController::class,
                'options'
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Create
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/',
            [
                ExpenseController::class,
                'store'
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | View Single Expense
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/{expense}',
            [
                ExpenseController::class,
                'show'
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        Route::put(
            '/{expense}',
            [
                ExpenseController::class,
                'update'
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Soft Delete
        |--------------------------------------------------------------------------
        */

        Route::delete(
            '/{expense}',
            [
                ExpenseController::class,
                'destroy'
            ]
        );


    });
    /*
    |--------------------------------------------------------------------------
    | Purchase Order Management
    |--------------------------------------------------------------------------
    */

    Route::patch(
        'purchase-orders/{purchaseOrder}/status',
        [
            PurchaseOrderController::class,
            'updateStatus',
        ]
    )
        ->middleware(
            'permission:suppliers.manage,inventory.manage'
        )
        ->name(
            'purchase-orders.update-status'
        );


    Route::post(
        'purchase-orders/{purchaseOrder}/receive',
        [
            PurchaseOrderController::class,
            'receive',
        ]
    )
        ->middleware(
            'permission:suppliers.manage,inventory.manage'
        )
        ->name(
            'purchase-orders.receive'
        );


    /*
    |--------------------------------------------------------------------------
    | Purchase Order Payment History
    |--------------------------------------------------------------------------
    |
    | Payment history is intentionally immutable from the API.
    |
    | GET  = view payment history
    | POST = record a new payment
    |
    */

    Route::prefix(
        'purchase-orders'
    )
        ->name(
            'purchase-orders.'
        )
        ->controller(
            PurchaseOrderPaymentController::class
        )
        ->middleware(
            'permission:suppliers.manage,inventory.manage'
        )
        ->group(function (): void {

            Route::get(
                '/{purchaseOrder}/payments',
                'index'
            )->name(
                'payments.index'
            );


            Route::post(
                '/{purchaseOrder}/payments',
                'store'
            )->name(
                'payments.store'
            );
        });

    /*
|--------------------------------------------------------------------------
| Purchase Order GRN / Receipt History
|--------------------------------------------------------------------------
*/

Route::get(
    'purchase-orders/{purchaseOrder}/receipts',
    [
        PurchaseOrderController::class,
        'receipts',
    ]
)
    ->middleware(
        'permission:suppliers.manage,inventory.manage'
    )
    ->name(
        'purchase-orders.receipts.index'
    );
    /*
    |--------------------------------------------------------------------------
    | Purchase Order CRUD
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'purchase-orders',
        PurchaseOrderController::class
    )
        ->parameters([
            'purchase-orders' =>
                'purchaseOrder',
        ])
        ->middleware(
            'permission:suppliers.manage,inventory.manage'
        );

    /*
    |--------------------------------------------------------------------------
    | Inventory Management
    |--------------------------------------------------------------------------
    |
    | Permission rules are enforced by InventoryController,
    | StockTransferController and their FormRequest classes.
    |
    | Keeping permission checks there allows:
    |
    | inventory.view   -> read-only inventory access
    | inventory.manage -> read + management access
    |
    | without forcing a manage-only user to also possess inventory.view.
    |
    */

    Route::prefix('inventory')
        ->name('inventory.')
        ->middleware(
            EnsureInventoryAccess::class
        )
        ->group(function (): void {

        /*
|--------------------------------------------------------------------------
| Supplier Credit
|--------------------------------------------------------------------------
*/

Route::controller(
    SupplierCreditController::class
)
    ->prefix(
        'supplier-credits'
    )
    ->name(
        'supplier-credits.'
    )
    ->group(function (): void {

        Route::get(
            '/',
            'index'
        )->name(
            'index'
        );


        Route::get(
            '/summary',
            'summary'
        )->name(
            'summary'
        );


        Route::get(
            '/applications',
            'applications'
        )->name(
            'applications'
        );


        Route::get(
            '/{supplierCredit}/eligible-purchase-orders',
            'eligiblePurchaseOrders'
        )
            ->whereNumber(
                'supplierCredit'
            )
            ->name(
                'eligible-purchase-orders'
            );


        Route::post(
            '/apply',
            'apply'
        )->name(
            'apply'
        );
    });

        /*
        |--------------------------------------------------------------------------
        | Purchase Return Accounting / Reporting
        |--------------------------------------------------------------------------
        |
        | Final Phase 9 read-only accounting bridge.
        |
        | Final endpoints:
        |
        | GET /api/inventory/purchase-return-accounting/summary
        | GET /api/inventory/purchase-return-accounting/ledger
        |
        */

        Route::controller(
            PurchaseReturnAccountingController::class
        )
            ->prefix(
                'purchase-return-accounting'
            )
            ->name(
                'purchase-return-accounting.'
            )
            ->group(function (): void {

                Route::get(
                    '/summary',
                    'summary'
                )->name(
                    'summary'
                );


                Route::get(
                    '/ledger',
                    'ledger'
                )->name(
                    'ledger'
                );
            });


        /*
        |--------------------------------------------------------------------------
        | Printable Inventory Documents
        |--------------------------------------------------------------------------
        |
        | Phase 8 PDF / Print routes.
        |
        | Final endpoints:
        |
        | GET /api/inventory/documents/purchase-returns/{purchaseReturn}/pdf
        | GET /api/inventory/documents/purchase-orders/{purchaseOrder}/pdf
        | GET /api/inventory/documents/purchase-orders/{purchaseOrder}/receipts/{receipt}/pdf
        |
        */

        Route::controller(
            InventoryDocumentController::class
        )
            ->prefix(
                'documents'
            )
            ->name(
                'documents.'
            )
            ->group(function (): void {

                Route::get(
                    '/purchase-returns/{purchaseReturn}/pdf',
                    'purchaseReturn'
                )
                    ->whereNumber(
                        'purchaseReturn'
                    )
                    ->name(
                        'purchase-returns.pdf'
                    );


                Route::get(
                    '/purchase-orders/{purchaseOrder}/pdf',
                    'purchaseOrder'
                )
                    ->whereNumber(
                        'purchaseOrder'
                    )
                    ->name(
                        'purchase-orders.pdf'
                    );


                Route::get(
                    '/purchase-orders/{purchaseOrder}/receipts/{receipt}/pdf',
                    'purchaseReceipt'
                )
                    ->whereNumber(
                        'purchaseOrder'
                    )
                    ->whereNumber(
                        'receipt'
                    )
                    ->name(
                        'purchase-orders.receipts.pdf'
                    );
            });


        Route::controller(
    PurchaseReturnSettlementController::class
)
    ->prefix(
        'purchase-returns/{purchaseReturn}/settlements'
    )
    ->name(
        'purchase-returns.settlements.'
    )
    ->group(function (): void {

        Route::get(
            '/',
            'index'
        )
            ->whereNumber(
                'purchaseReturn'
            )
            ->name(
                'index'
            );


        Route::post(
            '/',
            'store'
        )
            ->whereNumber(
                'purchaseReturn'
            )
            ->name(
                'store'
            );


        Route::post(
            '/collect-refund',
            'collectRefund'
        )
            ->whereNumber(
                'purchaseReturn'
            )
            ->name(
                'collect-refund'
            );
    });


        Route::controller(
    PurchaseReturnController::class
)
    ->prefix(
        'purchase-returns'
    )
    ->name(
        'purchase-returns.'
    )
    ->group(function (): void {

        Route::get(
            '/',
            'index'
        )->name(
            'index'
        );

        Route::post(
            '/',
            'store'
        )->name(
            'store'
        );

        Route::get(
            '/{purchaseReturn}',
            'show'
        )
            ->whereNumber(
                'purchaseReturn'
            )
            ->name(
                'show'
            );
    });



            /*
|--------------------------------------------------------------------------
| Warehouse Lot / Batch + Expiry
|--------------------------------------------------------------------------
|
| Read-only Phase 5 visibility layer.
| inventory.view OR inventory.manage is enforced by controller.
|
*/

Route::controller(
    WarehouseStockLotController::class
)
    ->prefix(
        'warehouse-lots'
    )
    ->name(
        'warehouse-lots.'
    )
    ->group(function (): void {

        Route::get(
            '/summary',
            'summary'
        )->name(
            'summary'
        );

        Route::get(
    '/{lot}/metadata-history',
    'metadataHistory'
)
    ->whereNumber(
        'lot'
    )
    ->name(
        'metadata-history'
    );


Route::patch(
    '/{lot}/metadata',
    'updateMetadata'
)
    ->whereNumber(
        'lot'
    )
    ->name(
        'update-metadata'
    );


        Route::get(
            '/',
            'index'
        )->name(
            'index'
        );


        Route::get(
            '/{lot}',
            'show'
        )
            ->whereNumber(
                'lot'
            )
            ->name(
                'show'
            );
    });

            /*
            |--------------------------------------------------------------------------
            | Inventory / Warehouse
            |--------------------------------------------------------------------------
            */

            Route::controller(
                InventoryController::class
            )
                ->group(function (): void {

                    /*
                    |--------------------------------------------------------------------------
                    | Summary and Options
                    |--------------------------------------------------------------------------
                    */

                    Route::get(
                        '/summary',
                        'summary'
                    )->name(
                        'summary'
                    );


                    Route::get(
                        '/options',
                        'options'
                    )->name(
                        'options'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Warehouse Stocks
                    |--------------------------------------------------------------------------
                    */

                    Route::get(
                        '/warehouse-stocks',
                        'warehouseStocks'
                    )->name(
                        'warehouse-stocks.index'
                    );


                    Route::get(
                        '/warehouse-stocks/{rawMaterial}',
                        'showWarehouseStock'
                    )->name(
                        'warehouse-stocks.show'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Stock Movement History
                    |--------------------------------------------------------------------------
                    */

                    Route::get(
                        '/stock-movements',
                        'stockMovements'
                    )->name(
                        'stock-movements.index'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Raw Material List and Create
                    |--------------------------------------------------------------------------
                    */

                    Route::get(
                        '/raw-materials',
                        'rawMaterials'
                    )->name(
                        'raw-materials.index'
                    );


                    Route::post(
                        '/raw-materials',
                        'storeRawMaterial'
                    )->name(
                        'raw-materials.store'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Raw Material Actions
                    |--------------------------------------------------------------------------
                    |
                    | Static action routes stay before the dynamic show route.
                    |
                    */

                    Route::patch(
                        '/raw-materials/{rawMaterial}/status',
                        'toggleRawMaterialStatus'
                    )->name(
                        'raw-materials.toggle-status'
                    );


                    Route::post(
                        '/raw-materials/{rawMaterial}/warehouse-adjustment',
                        'adjustWarehouseStock'
                    )->name(
                        'raw-materials.warehouse-adjustment'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Raw Material Single CRUD
                    |--------------------------------------------------------------------------
                    */

                    Route::get(
                        '/raw-materials/{rawMaterial}',
                        'showRawMaterial'
                    )->name(
                        'raw-materials.show'
                    );


                    Route::put(
                        '/raw-materials/{rawMaterial}',
                        'updateRawMaterial'
                    )->name(
                        'raw-materials.update'
                    );


                    Route::delete(
                        '/raw-materials/{rawMaterial}',
                        'destroyRawMaterial'
                    )->name(
                        'raw-materials.destroy'
                    );
                });

                        /*
            |--------------------------------------------------------------------------
            | Recipe Mapping
            |--------------------------------------------------------------------------
            |
            | GET:
            |     inventory.view OR inventory.manage
            |
            | PUT / DELETE:
            |     inventory.manage
            |
            | Supports both Menu Items and Add-ons.
            |
            | Legacy Menu Item routes are preserved for backward compatibility.
            |
            */

            Route::controller(
                RecipeMappingController::class
            )
                ->group(function (): void {

                    /*
                    |--------------------------------------------------------------------------
                    | Recipe Mapping List
                    |--------------------------------------------------------------------------
                    */

                    Route::get(
                        '/recipe-mappings',
                        'index'
                    )->name(
                        'recipe-mappings.index'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Unified Save / Replace
                    |--------------------------------------------------------------------------
                    |
                    | Body:
                    |
                    | target_type = menu_item | add_on
                    | target_id
                    | ingredients[]
                    |
                    */

                    Route::put(
                        '/recipe-mappings',
                        'saveTarget'
                    )->name(
                        'recipe-mappings.save'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Unified Target Show
                    |--------------------------------------------------------------------------
                    */

                    Route::get(
                        '/recipe-mappings/{targetType}/{targetId}',
                        'showTarget'
                    )
                        ->where(
                            'targetType',
                            'menu_item|add_on'
                        )
                        ->whereNumber(
                            'targetId'
                        )
                        ->name(
                            'recipe-mappings.target.show'
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Unified Target Delete
                    |--------------------------------------------------------------------------
                    |
                    | Deletes only recipe definition rows.
                    | Menu Item / Add-on master records remain untouched.
                    |
                    */

                    Route::delete(
                        '/recipe-mappings/{targetType}/{targetId}',
                        'destroyTarget'
                    )
                        ->where(
                            'targetType',
                            'menu_item|add_on'
                        )
                        ->whereNumber(
                            'targetId'
                        )
                        ->name(
                            'recipe-mappings.target.destroy'
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Legacy Menu Item Show
                    |--------------------------------------------------------------------------
                    */

                    Route::get(
                        '/recipe-mappings/{menuItem}',
                        'show'
                    )
                        ->whereNumber(
                            'menuItem'
                        )
                        ->name(
                            'recipe-mappings.show'
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Legacy Menu Item Save / Replace
                    |--------------------------------------------------------------------------
                    */

                    Route::put(
                        '/recipe-mappings/{menuItem}',
                        'update'
                    )
                        ->whereNumber(
                            'menuItem'
                        )
                        ->name(
                            'recipe-mappings.update'
                        );
                });
                /*
            |--------------------------------------------------------------------------
            | Order Recipe Consumption Audit
            |--------------------------------------------------------------------------
            |
            | Read-only immutable recipe consumption history for a specific order.
            |
            | GET:
            |     inventory.view OR inventory.manage
            |
            | Permission is enforced by OrderRecipeConsumptionController.
            |
            */

            Route::get(
                '/orders/{order}/recipe-consumption',
                [
                    OrderRecipeConsumptionController::class,
                    'showForOrder',
                ]
            )->name(
                'orders.recipe-consumption.show'
            );

            /*
            |--------------------------------------------------------------------------
            | Warehouse → Restaurant Stock Transfer
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            | This is already inside /inventory.
            | Do not add another inventory prefix here.
            |
            */

            Route::controller(
                StockTransferController::class
            )
                ->group(function (): void {

                    /*
                    |--------------------------------------------------------------------------
                    | Restaurant Stocks
                    |--------------------------------------------------------------------------
                    */

                    Route::get(
                        '/restaurant-stocks',
                        'restaurantStocks'
                    )->name(
                        'restaurant-stocks.index'
                    );


                    Route::get(
                        '/restaurant-stocks/{rawMaterial}',
                        'showRestaurantStock'
                    )->name(
                        'restaurant-stocks.show'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Stock Transfer History
                    |--------------------------------------------------------------------------
                    */

                    Route::get(
                        '/stock-transfers',
                        'index'
                    )->name(
                        'stock-transfers.index'
                    );


                    Route::get(
                        '/stock-transfers/{stockTransfer}',
                        'show'
                    )->name(
                        'stock-transfers.show'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Create Stock Transfer
                    |--------------------------------------------------------------------------
                    |
                    | StoreStockTransferRequest requires inventory.manage.
                    |
                    */

                    Route::post(
                        '/stock-transfers',
                        'store'
                    )->name(
                        'stock-transfers.store'
                    );
                });


            /*
            |--------------------------------------------------------------------------
            | Daily Stock Transfer Templates
            |--------------------------------------------------------------------------
            |
            | Templates are editable configuration only.
            | Actual inventory movement continues to use StockTransferService.
            |
            */

            Route::controller(
                StockTransferTemplateController::class
            )
                ->prefix(
                    'transfer-templates'
                )
                ->name(
                    'transfer-templates.'
                )
                ->group(function (): void {

                    Route::get(
                        '/',
                        'index'
                    )->name(
                        'index'
                    );


                    Route::post(
                        '/',
                        'store'
                    )->name(
                        'store'
                    );


                    Route::get(
                        '/{template}',
                        'show'
                    )
                        ->whereNumber(
                            'template'
                        )
                        ->name(
                            'show'
                        );


                    Route::put(
                        '/{template}',
                        'update'
                    )
                        ->whereNumber(
                            'template'
                        )
                        ->name(
                            'update'
                        );


                    Route::patch(
                        '/{template}/status',
                        'updateStatus'
                    )
                        ->whereNumber(
                            'template'
                        )
                        ->name(
                            'status'
                        );


                    Route::delete(
                        '/{template}',
                        'destroy'
                    )
                        ->whereNumber(
                            'template'
                        )
                        ->name(
                            'destroy'
                        );
                });


            /*
            |--------------------------------------------------------------------------
            | Restaurant Damage / Wastage
            |--------------------------------------------------------------------------
            |
            | GET:
            |     inventory.view OR inventory.manage
            |
            | POST:
            |     inventory.manage
            |
            | Wastage history is immutable.
            | No update/delete routes are intentionally exposed.
            |
            */

            Route::controller(
                InventoryWastageController::class
            )
                ->prefix(
                    'wastages'
                )
                ->name(
                    'wastages.'
                )
                ->group(function (): void {

                    Route::get(
                        '/',
                        'index'
                    )->name(
                        'index'
                    );


                    Route::post(
                        '/',
                        'store'
                    )->name(
                        'store'
                    );


                    Route::get(
                        '/{wastage}',
                        'show'
                    )
                        ->whereNumber(
                            'wastage'
                        )
                        ->name(
                            'show'
                        );
                });
        });


        /*
|--------------------------------------------------------------------------
| Customer Management
|--------------------------------------------------------------------------
|
| Customer list, summary, search, create, details, update,
| status toggle এবং soft delete manage করবে।
|
*/
Route::prefix('customers')
    ->name('customers.')
    ->middleware(
        'permission:customers.manage'
    )
    ->group(function (): void {
        /*
        |--------------------------------------------------------------------------
        | Customer Summary
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/summary',
            [
                CustomerController::class,
                'summary',
            ]
        )->name(
            'summary'
        );

        /*
        |--------------------------------------------------------------------------
        | Customer Search Suggestions
        |--------------------------------------------------------------------------
        |
        | Order create/edit form customer lookup-এর জন্য।
        |
        */

        Route::get(
            '/search',
            [
                CustomerController::class,
                'search',
            ]
        )->name(
            'search'
        );

        /*
        |--------------------------------------------------------------------------
        | Customer List
        |--------------------------------------------------------------------------
        |
        | Query parameters:
        |
        | - search
        | - status
        | - sort
        | - page
        | - per_page
        |
        */

        Route::get(
            '/',
            [
                CustomerController::class,
                'index',
            ]
        )->name(
            'index'
        );

        /*
        |--------------------------------------------------------------------------
        | Create Customer
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/',
            [
                CustomerController::class,
                'store',
            ]
        )->name(
            'store'
        );

        /*
        |--------------------------------------------------------------------------
        | Toggle Customer Status
        |--------------------------------------------------------------------------
        |
        | Dynamic customer route-এর আগে থাকবে।
        |
        */

        Route::patch(
            '/{customer}/status',
            [
                CustomerController::class,
                'toggleStatus',
            ]
        )->name(
            'toggle-status'
        );

        /*
        |--------------------------------------------------------------------------
        | Customer Details
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/{customer}',
            [
                CustomerController::class,
                'show',
            ]
        )->name(
            'show'
        );

        /*
        |--------------------------------------------------------------------------
        | Update Customer
        |--------------------------------------------------------------------------
        */

        Route::put(
            '/{customer}',
            [
                CustomerController::class,
                'update',
            ]
        )->name(
            'update'
        );

        /*
        |--------------------------------------------------------------------------
        | Soft Delete Customer
        |--------------------------------------------------------------------------
        */

        Route::delete(
            '/{customer}',
            [
                CustomerController::class,
                'destroy',
            ]
        )->name(
            'destroy'
        );
    });

        /*
    |--------------------------------------------------------------------------
    | Kitchen Order Management
    |--------------------------------------------------------------------------
    |
    | Kitchen display, chef assignment এবং kitchen status progression:
    |
    | Pending
    |   → Accept Order
    |   → Start Preparing
    |   → Mark Ready
    |
    */

    Route::prefix('kitchen')
        ->name('kitchen.')
        ->controller(KitchenOrderController::class)
        ->middleware(
            'permission:kitchen.view'
        )
        ->group(function (): void {
            /*
            |--------------------------------------------------------------------------
            | Kitchen Order List
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/orders',
                'index'
            )->name('orders.index');

            /*
            |--------------------------------------------------------------------------
            | Kitchen Order Details
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/orders/{order}',
                'show'
            )->name('orders.show');

            /*
            |--------------------------------------------------------------------------
            | Accept Kitchen Order
            |--------------------------------------------------------------------------
            |
            | Authenticated chef-এর user ID order-এর chef_id field-এ save হবে।
            | sent_to_kitchen_at timestamp update হবে।
            |
            */

            Route::post(
                '/orders/{order}/accept',
                'accept'
            )->name('orders.accept');

            /*
            |--------------------------------------------------------------------------
            | Start Preparing
            |--------------------------------------------------------------------------
            |
            | Order status preparing হবে এবং preparing_at update হবে।
            |
            */

            Route::post(
                '/orders/{order}/start-preparing',
                'startPreparing'
            )->name(
                'orders.start-preparing'
            );

            /*
            |--------------------------------------------------------------------------
            | Mark Order Ready
            |--------------------------------------------------------------------------
            |
            | Order status ready হবে এবং ready_at update হবে।
            |
            */

            Route::post(
                '/orders/{order}/ready',
                'markReady'
            )->name('orders.ready');
        });
        /*
        |--------------------------------------------------------------------------
        | Billing and Statement Module
        |--------------------------------------------------------------------------
        |
        | Billing summary, settlement orders, payment methods এবং staff activity
        | report এই route group থেকে manage করা হবে।
        |
        */

        Route::prefix('billing')
            ->name('billing.')
            ->middleware('permission:billing.view')
            ->group(function (): void {
            /*
            |--------------------------------------------------------------------------
            | Billing Summary
            |--------------------------------------------------------------------------
            |
            | Selected date বা date range অনুযায়ী net sales, collected amount,
            | tax, service charge, cash collection, due এবং total order summary
            | return করবে।
            |
            | Query parameters:
            | - date
            | - date_from
            | - date_to
            |
            */

            Route::get(
                '/summary',
                [
                    BillingController::class,
                    'summary',
                ]
            )->name('summary');

            /*
            |--------------------------------------------------------------------------
            | Settlement Orders
            |--------------------------------------------------------------------------
            |
            | Order status বা payment status অনুযায়ী settlement order list return
            | করবে। প্রতিটি page-এ default 5টি order দেখানো হবে।
            |
            | Query parameters:
            | - date
            | - date_from
            | - date_to
            | - status
            | - page
            | - per_page
            |
            */

            Route::get(
                '/settlements',
                [
                    BillingController::class,
                    'settlements',
                ]
            )->name('settlements');

            /*
            |--------------------------------------------------------------------------
            | Payment Mode Report
            |--------------------------------------------------------------------------
            |
            | order_payments table থেকে payment method অনুযায়ী payment list
            | return করবে।
            |
            | Query parameters:
            | - date
            | - date_from
            | - date_to
            | - payment_method
            | - page
            | - per_page
            |
            */

            Route::get(
                '/payment-modes',
                [
                    BillingController::class,
                    'paymentModes',
                ]
            )->name('payment-modes');

            /*
            |--------------------------------------------------------------------------
            | Payment and Staff Activity
            |--------------------------------------------------------------------------
            |
            | Order creator, assigned chef এবং payment receiver informationসহ
            | payment activity list return করবে।
            |
            | orders.created_by user-কে waiter/order creator হিসেবে দেখানো হবে।
            |
            | Query parameters:
            | - date
            | - date_from
            | - date_to
            | - user_id
            | - user_type
            | - page
            | - per_page
            |
            */

            Route::get(
                '/payment-activities',
                [
                    BillingController::class,
                    'paymentActivities',
                ]
            )->name('payment-activities');

            /*
            |--------------------------------------------------------------------------
            | Billing Users
            |--------------------------------------------------------------------------
            |
            | User filter dropdown-এর জন্য creator, chef এবং payment receiver
            | হিসেবে ব্যবহৃত users return করবে।
            |
            */

            Route::get(
                '/users',
                [
                    BillingController::class,
                    'users',
                ]
            )->name('users');
        });

        /*
        |--------------------------------------------------------------------------
        | Staff Management
        |--------------------------------------------------------------------------
        |
        | Employee, Attendance and Shift Schedule routes use the /api/staff prefix.
        |
        */

        Route::prefix('staff')
            ->controller(EmployeeController::class)
            ->middleware(
                'permission:staff.manage'
            )
            ->group(function (): void {

                /*
                |--------------------------------------------------------------------------
                | Roles
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/roles',
                    'roles'
                );

                /*
                |--------------------------------------------------------------------------
                | Employee CRUD
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/employees',
                    'index'
                );

                Route::post(
                    '/employees',
                    'store'
                );

                Route::get(
                    '/employees/{employee}',
                    'show'
                );

                Route::put(
                    '/employees/{employee}',
                    'update'
                );

                Route::patch(
                    '/employees/{employee}',
                    'update'
                );

                Route::delete(
                    '/employees/{employee}',
                    'destroy'
                );

                /*
                |--------------------------------------------------------------------------
                | Employee Working Status
                |--------------------------------------------------------------------------
                */

                Route::patch(
                    '/employees/{employee}/status',
                    'updateStatus'
                );

                /*
                |--------------------------------------------------------------------------
                | Block / Unblock Employee Account
                |--------------------------------------------------------------------------
                */

                Route::patch(
                    '/employees/{employee}/account-status',
                    'updateAccountStatus'
                );

                /*
                |--------------------------------------------------------------------------
                | Temporary Old Frontend Compatibility
                |--------------------------------------------------------------------------
                */

                Route::patch(
                    '/employees/{employee}/toggle-active',
                    'toggleActive'
                );

                /*
                |--------------------------------------------------------------------------
                | Shift Schedules
                |--------------------------------------------------------------------------
                */

                Route::prefix('shift-schedules')
                    ->controller(ShiftScheduleController::class)
                    ->group(function (): void {

                        Route::get(
                            '/employees',
                            'employees'
                        );

                        Route::get(
                            '/',
                            'index'
                        );

                        Route::post(
                            '/',
                            'store'
                        );

                        Route::get(
                            '/{shiftSchedule}',
                            'show'
                        );

                        Route::put(
                            '/{shiftSchedule}',
                            'update'
                        );

                        Route::patch(
                            '/{shiftSchedule}',
                            'update'
                        );

                        Route::patch(
                            '/{shiftSchedule}/status',
                            'updateStatus'
                        );

                        Route::delete(
                            '/{shiftSchedule}',
                            'destroy'
                        );
                    });

                /*
                |--------------------------------------------------------------------------
                | One-Day Schedule Overrides
                |--------------------------------------------------------------------------
                */

                Route::controller(
                    ShiftScheduleOverrideController::class
                )->group(function (): void {

                    Route::get(
                        '/shift-schedules/{shiftSchedule}/overrides',
                        'index'
                    );

                    Route::post(
                        '/shift-schedules/{shiftSchedule}/overrides',
                        'store'
                    );

                    Route::put(
                        '/shift-schedule-overrides/{shiftScheduleOverride}',
                        'update'
                    );

                    Route::patch(
                        '/shift-schedule-overrides/{shiftScheduleOverride}',
                        'update'
                    );

                    Route::delete(
                        '/shift-schedule-overrides/{shiftScheduleOverride}',
                        'destroy'
                    );
                });

                /*
                |--------------------------------------------------------------------------
                | Attendance
                |--------------------------------------------------------------------------
                */

                Route::prefix('attendances')
                    ->controller(AttendanceController::class)
                    ->group(function (): void {

                        /*
                        | Static route must remain before /{attendance}.
                        */

                        Route::post(
                            '/sync',
                            'sync'
                        );

                        Route::get(
                            '/',
                            'index'
                        );

                        Route::get(
                            '/{attendance}',
                            'show'
                        );
                    });
            });

        /*
        |--------------------------------------------------------------------------
        | Admin Salary Management
        |--------------------------------------------------------------------------
        |
        | Important:
        | This group must remain OUTSIDE Route::prefix('staff').
        | Final endpoint: /api/admin/salaries
        |
        */

        Route::prefix('admin/salaries')
            ->controller(SalaryController::class)
            ->middleware(
                'permission:users.manage'
            )
            ->group(function (): void {

                /*
                | Static routes must remain before /{salary}.
                */

                Route::get(
                    '/employees',
                    'employees'
                );

                Route::post(
                    '/generate',
                    'generate'
                );

                Route::get(
                    '/',
                    'index'
                );
                Route::get(
                    '/options',
                    'options'
                );

                Route::post(
                    '/process-daily',
                    'processDaily'
                );

                Route::put(
                    '/{salary}',
                    'update'
                );

                Route::patch(
                    '/{salary}/payment-status',
                    'paymentStatus'
                );

                Route::delete(
                    '/{salary}',
                    'destroy'
                );
            });
            /*
            |--------------------------------------------------------------------------
            | Admin Salary Details
            |--------------------------------------------------------------------------
            */

            Route::prefix('admin/salary-details')
                ->controller(SalaryDetailController::class)
                ->middleware(
                    'permission:users.manage'
                )
                ->group(function (): void {

                    Route::get(
                        '/',
                        'index'
                    );

                    Route::patch(
                        '/{salaryDetail}',
                        'update'
                    );

                });
            /*
/*
|--------------------------------------------------------------------------
| Reports Module
|--------------------------------------------------------------------------
*/

Route::prefix('reports')
    ->name('reports.')
    ->middleware('permission:reports.view')
    ->group(function (): void {

        /*
        |--------------------------------------------------------------------------
        | Accounting Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/accounting-dashboard',
            [
                ReportController::class,
                'accountingDashboard',
            ]
        )->name(
            'accounting-dashboard'
        );

        Route::get(
    '/owner-ledger',
    [
        ReportController::class,
        'ownerLedger'
    ]
);
Route::get(
    '/owner-ledger/pdf',
    [
        ReportController::class,
        'ownerLedgerPdf'
    ]
);

Route::get(
    '/profit-loss/pdf',
    [
        ReportController::class,
        'profitLossPdf'
    ]
);
Route::get(
    '/business-overview',
    [
        BusinessOverviewController::class,
        'index'
    ]
);

Route::get(

    '/balance-sheet/pdf',

    [

        ReportController::class,

        'balanceSheetPdf'

    ]

);
Route::get(

    '/trial-balance/pdf',

    [

        ReportController::class,

        'trialBalancePdf'

    ]

);


        /*
        |--------------------------------------------------------------------------
        | Ledger Accounts
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/ledger/accounts',
            [
                ReportController::class,
                'ledgerAccounts',
            ]
        )->name(
            'ledger.accounts'
        );


        /*
        |--------------------------------------------------------------------------
        | Trial Balance
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/trial-balance',
            [
                ReportController::class,
                'trialBalance',
            ]
        )->name(
            'trial-balance'
        );


        /*
        |--------------------------------------------------------------------------
        | General Ledger
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/general-ledger',
            [
                ReportController::class,
                'generalLedger',
            ]
        )->name(
            'general-ledger'
        );


        /*
        |--------------------------------------------------------------------------
        | Profit & Loss
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/profit-loss',
            [
                ReportController::class,
                'profitLoss',
            ]
        )->name(
            'profit-loss'
        );


        /*
        |--------------------------------------------------------------------------
        | Balance Sheet
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/balance-sheet',
            [
                ReportController::class,
                'balanceSheet',
            ]
        )->name(
            'balance-sheet'
        );


        /*
        |--------------------------------------------------------------------------
        | Existing Business Reports
        |--------------------------------------------------------------------------
        */

        Route::controller(
            ReportController::class
        )
        ->group(function (): void {

            Route::get(
                '/demand-forecast',
                'demandForecast'
            )->name(
                'demand-forecast'
            );

            Route::get(
                '/orders',
                'orders'
            )->name(
                'orders'
            );


            Route::get(
                '/expenses',
                'expenses'
            )->name(
                'expenses'
            );


            Route::get(
                '/expenses/summary',
                'expenseSummary'
            )->name(
                'expenses.summary'
            );


            Route::get(
                '/purchase-orders',
                'purchaseOrders'
            )->name(
                'purchase-orders'
            );


            Route::get(
                '/restaurant-stock',
                'restaurantStock'
            )->name(
                'restaurant-stock'
            );


            Route::get(
                '/warehouse-stock',
                'warehouseStock'
            )->name(
                'warehouse-stock'
            );


            Route::get(
                '/stock-transfers',
                'stockTransfers'
            )->name(
                'stock-transfers'
            );


            Route::get(
                '/attendance/employees',
                'attendanceEmployees'
            )->name(
                'attendance.employees'
            );


            Route::get(
                '/attendance',
                'attendance'
            )->name(
                'attendance'
            );

        });


        /*
        |--------------------------------------------------------------------------
        | CSV Export
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/{type}/export/csv',
            [
                ReportExportController::class,
                'csv',
            ]
        )->name(
            'export.csv'
        );


        /*
        |--------------------------------------------------------------------------
        | PDF Export
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/{type}/export/pdf',
            [
                ReportExportController::class,
                'pdf',
            ]
        )->name(
            'export.pdf'
        );

    });
    });
