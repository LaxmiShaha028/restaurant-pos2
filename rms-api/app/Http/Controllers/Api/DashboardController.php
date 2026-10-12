<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Permission;
use App\Models\RestaurantTable;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Restaurant Dashboard
    |--------------------------------------------------------------------------
    */

    public function overview(Request $request): JsonResponse
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Date Ranges
        |--------------------------------------------------------------------------
        */

        $now = now();

        $currentMonthStart = $now
            ->copy()
            ->startOfMonth();

        $currentMonthEnd = $now
            ->copy()
            ->endOfMonth();

        $previousMonthStart = $now
            ->copy()
            ->subMonthNoOverflow()
            ->startOfMonth();

        $previousMonthEnd = $now
            ->copy()
            ->subMonthNoOverflow()
            ->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | Order Statuses
        |--------------------------------------------------------------------------
        */

        $activeOrderStatuses = [
            Order::STATUS_PENDING,
            Order::STATUS_PREPARING,
            Order::STATUS_READY,
            Order::STATUS_SERVED,
        ];

        /*
        |--------------------------------------------------------------------------
        | Current Month Orders
        |--------------------------------------------------------------------------
        */

        $currentMonthOrdersQuery =
            Order::query()
                ->whereBetween(
                    'created_at',
                    [
                        $currentMonthStart,
                        $currentMonthEnd,
                    ]
                );

        /*
        |--------------------------------------------------------------------------
        | Sales
        |--------------------------------------------------------------------------
        |
        | Canceled orders are excluded from sales.
        |
        */

        $currentMonthSales =
            (clone $currentMonthOrdersQuery)
                ->where(
                    'status',
                    '!=',
                    Order::STATUS_CANCELED
                )
                ->sum('total_amount');

        $previousMonthSales =
            Order::query()
                ->whereBetween(
                    'created_at',
                    [
                        $previousMonthStart,
                        $previousMonthEnd,
                    ]
                )
                ->where(
                    'status',
                    '!=',
                    Order::STATUS_CANCELED
                )
                ->sum('total_amount');

        /*
        |--------------------------------------------------------------------------
        | Sales Growth
        |--------------------------------------------------------------------------
        */

        $salesChangePercent = 0;

        if ((float) $previousMonthSales > 0) {
            $salesChangePercent =
                round(
                    (
                        (
                            (float) $currentMonthSales
                            -
                            (float) $previousMonthSales
                        )
                        /
                        (float) $previousMonthSales
                    )
                    * 100,
                    2
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Orders
        |--------------------------------------------------------------------------
        */

        $totalOrders =
            (clone $currentMonthOrdersQuery)
                ->count();

        $completedOrders =
            (clone $currentMonthOrdersQuery)
                ->where(
                    'status',
                    Order::STATUS_COMPLETED
                )
                ->count();

        $activeOrders =
            (clone $currentMonthOrdersQuery)
                ->whereIn(
                    'status',
                    $activeOrderStatuses
                )
                ->count();

        $pendingKitchenOrders =
            (clone $currentMonthOrdersQuery)
                ->where(
                    'status',
                    Order::STATUS_PENDING
                )
                ->count();

        /*
        |--------------------------------------------------------------------------
        | Payment Summary
        |--------------------------------------------------------------------------
        */

        $paidAmount =
            (clone $currentMonthOrdersQuery)
                ->where(
                    'status',
                    '!=',
                    Order::STATUS_CANCELED
                )
                ->sum('paid_amount');

        $dueAmount =
            (clone $currentMonthOrdersQuery)
                ->where(
                    'status',
                    '!=',
                    Order::STATUS_CANCELED
                )
                ->sum('due_amount');

        /*
        |--------------------------------------------------------------------------
        | Tables
        |--------------------------------------------------------------------------
        */

        $occupiedTables =
            RestaurantTable::query()
                ->where(
                    'status',
                    RestaurantTable::STATUS_OCCUPIED
                )
                ->count();

        $availableTables =
            RestaurantTable::query()
                ->where(
                    'status',
                    RestaurantTable::STATUS_AVAILABLE
                )
                ->count();

        /*
        |--------------------------------------------------------------------------
        | Sales Chart
        |--------------------------------------------------------------------------
        */

        $salesPeriod = $request->query('sales_period', 'month');

        if (! in_array($salesPeriod, ['day', 'month', 'year', 'last_year'], true)) {
            $salesPeriod = 'month';
        }

        if ($salesPeriod === 'day') {
            $chartStart = $now->copy()->startOfDay();
            $chartEnd = $now->copy()->endOfDay();
            $chartSelect = 'HOUR(created_at) as period_key';
            $chartGroup = 'HOUR(created_at)';
        } elseif ($salesPeriod === 'year') {
            $chartStart = $now->copy()->startOfYear();
            $chartEnd = $now->copy()->endOfYear();
            $chartSelect = 'MONTH(created_at) as period_key';
            $chartGroup = 'MONTH(created_at)';
        } elseif ($salesPeriod === 'last_year') {
            $chartStart = $now->copy()->subYearNoOverflow()->startOfYear();
            $chartEnd = $now->copy()->subYearNoOverflow()->endOfYear();
            $chartSelect = 'MONTH(created_at) as period_key';
            $chartGroup = 'MONTH(created_at)';
        } else {
            $chartStart = $currentMonthStart;
            $chartEnd = $currentMonthEnd;
            $chartSelect = 'DATE(created_at) as period_key';
            $chartGroup = 'DATE(created_at)';
        }

        $salesChartRows = Order::query()
            ->selectRaw($chartSelect)
            ->selectRaw('SUM(total_amount) as total_sales')
            ->whereBetween('created_at', [$chartStart, $chartEnd])
            ->where('status', '!=', Order::STATUS_CANCELED)
            ->groupBy(DB::raw($chartGroup))
            ->orderBy('period_key')
            ->get();

        $salesChartLookup = $salesChartRows->mapWithKeys(function ($row) use ($salesPeriod): array {
            $key = $salesPeriod === 'day'
                ? (int) $row->period_key
                : ($salesPeriod === 'month'
                    ? Carbon::parse($row->period_key)->format('Y-m-d')
                    : (int) $row->period_key);

            return [$key => round((float) $row->total_sales, 2)];
        });

        $salesChart = [];

        if ($salesPeriod === 'day') {
            for ($hour = 0; $hour < 24; $hour++) {
                $salesChart[] = [
                    'date' => sprintf('%02d:00', $hour),
                    'label' => Carbon::createFromTime($hour)->format('g A'),
                    'sales' => (float) ($salesChartLookup[$hour] ?? 0),
                ];
            }
        } elseif ($salesPeriod === 'month') {
            $chartDate = $chartStart->copy();

            while ($chartDate->lessThanOrEqualTo($chartEnd)) {
                $dateKey = $chartDate->format('Y-m-d');
                $salesChart[] = [
                    'date' => $dateKey,
                    'label' => $chartDate->format('d M'),
                    'sales' => (float) ($salesChartLookup[$dateKey] ?? 0),
                ];
                $chartDate->addDay();
            }
        } else {
            for ($month = 1; $month <= 12; $month++) {
                $chartDate = $chartStart->copy()->month($month)->startOfMonth();
                $salesChart[] = [
                    'date' => $chartDate->format('Y-m'),
                    'label' => $chartDate->format('M'),
                    'sales' => (float) ($salesChartLookup[$month] ?? 0),
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Top Selling Items
        |--------------------------------------------------------------------------
        */

        $topItems =
            DB::table('order_items')
                ->join(
                    'orders',
                    'orders.id',
                    '=',
                    'order_items.order_id'
                )
                ->whereBetween(
                    'orders.created_at',
                    [
                        $currentMonthStart,
                        $currentMonthEnd,
                    ]
                )
                ->where(
                    'orders.status',
                    '!=',
                    Order::STATUS_CANCELED
                )
                ->select(
                    'order_items.menu_item_id'
                )
                ->selectRaw(
                    'MAX(order_items.item_name) as item_name'
                )
                ->selectRaw(
                    'SUM(order_items.quantity) as quantity_sold'
                )
                ->selectRaw(
                    'SUM(order_items.line_total) as sales_amount'
                )
                ->groupBy(
                    'order_items.menu_item_id'
                )
                ->orderByDesc(
                    'quantity_sold'
                )
                ->limit(5)
                ->get()
                ->map(
                    function ($item): array {
                        return [
                            'menu_item_id' =>
                                (int) $item->menu_item_id,

                            'item_name' =>
                                $item->item_name,

                            'quantity_sold' =>
                                (int) $item->quantity_sold,

                            'sales_amount' =>
                                round(
                                    (float) $item->sales_amount,
                                    2
                                ),
                        ];
                    }
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Recent Orders
        |--------------------------------------------------------------------------
        */

        $recentOrders =
            Order::query()
                ->latest('created_at')
                ->limit(8)
                ->get([
                    'id',
                    'order_number',
                    'customer_name',
                    'status',
                    'total_amount',
                    'paid_amount',
                    'due_amount',
                    'payment_status',
                    'payment_method',
                    'created_at',
                ])
                ->map(
                    function (Order $order): array {
                        return [
                            'id' =>
                                (int) $order->id,

                            'order_number' =>
                                $order->order_number,

                            'customer_name' =>
                                $order->customer_name
                                ?: 'Walk-in Customer',

                            'status' =>
                                $order->status,

                            'total_amount' =>
                                round(
                                    (float) $order->total_amount,
                                    2
                                ),

                            'paid_amount' =>
                                round(
                                    (float) $order->paid_amount,
                                    2
                                ),

                            'due_amount' =>
                                round(
                                    (float) $order->due_amount,
                                    2
                                ),

                            'payment_status' =>
                                $order->payment_status,

                            'payment_method' =>
                                $order->payment_method,

                            'created_at' =>
                                $order->created_at?->toISOString(),
                        ];
                    }
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Order Status Summary
        |--------------------------------------------------------------------------
        */

        /*
|--------------------------------------------------------------------------
| Order Status Summary
|--------------------------------------------------------------------------
|
| Always return every dashboard status.
| If a status has no order, return 0.
|
*/

$orderStatusRows =
    (clone $currentMonthOrdersQuery)
        ->select('status')
        ->selectRaw('COUNT(*) as total')
        ->groupBy('status')
        ->get()
        ->mapWithKeys(
            function ($row): array {
                return [
                    strtolower(
                        trim(
                            (string) $row->status
                        )
                    ) =>
                    (int) $row->total,
                ];
            }
        );


$orderStatusDefinitions = [

    Order::STATUS_PENDING => 'Pending',

    Order::STATUS_PREPARING => 'Preparing',

    Order::STATUS_READY => 'Ready',

    Order::STATUS_SERVED => 'Served',

    Order::STATUS_COMPLETED => 'Completed',

    Order::STATUS_CANCELED => 'Canceled',

];


$orderStatus =
    collect(
        $orderStatusDefinitions
    )
    ->map(
        function (
            string $label,
            string $status
        ) use ($orderStatusRows): array {

            $normalizedStatus =
                strtolower(
                    trim($status)
                );

            return [
                'status' =>
                    $normalizedStatus,

                'label' =>
                    $label,

                'total' =>
                    (int) (
                        $orderStatusRows[
                            $normalizedStatus
                        ]
                        ?? 0
                    ),
            ];
        }
    )
    ->values()
    ->all();

        /*
        |--------------------------------------------------------------------------
        | Payment Method Summary
        |--------------------------------------------------------------------------
        */

        $paymentMethodRows =
            (clone $currentMonthOrdersQuery)
                ->where(
                    'status',
                    '!=',
                    Order::STATUS_CANCELED
                )
                ->select(
                    'payment_method'
                )
                ->selectRaw(
                    'SUM(paid_amount) as total_paid'
                )
                ->groupBy(
                    'payment_method'
                )
                ->orderByDesc(
                    'total_paid'
                )
                ->get();

        $paymentMethods =
            $paymentMethodRows
                ->map(
                    function ($row): array {
                        return [
                            'payment_method' =>
                                $row->payment_method
                                ?: 'unknown',

                            'total_paid' =>
                                round(
                                    (float) $row->total_paid,
                                    2
                                ),
                        ];
                    }
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'message' =>
                'Dashboard overview retrieved successfully.',

            'data' => [

                /*
                |--------------------------------------------------------------------------
                | User
                |--------------------------------------------------------------------------
                */

                'welcome_message' =>
                    'Welcome to the Restaurant Management System.',

                'user' => [
                    'id' =>
                        $user->id,

                    'name' =>
                        $user->name,

                    'username' =>
                        $user->username,

                    'role' =>
                        $user->role?->display_name,
                ],

                /*
                |--------------------------------------------------------------------------
                | Main Summary
                |--------------------------------------------------------------------------
                */

                'summary' => [

                    'total_sales' =>
                        round(
                            (float) $currentMonthSales,
                            2
                        ),

                    'total_orders' =>
                        $totalOrders,

                    'completed_orders' =>
                        $completedOrders,

                    'active_orders' =>
                        $activeOrders,

                    'pending_kitchen_orders' =>
                        $pendingKitchenOrders,

                    'paid_amount' =>
                        round(
                            (float) $paidAmount,
                            2
                        ),

                    'due_amount' =>
                        round(
                            (float) $dueAmount,
                            2
                        ),

                    'occupied_tables' =>
                        $occupiedTables,

                    'available_tables' =>
                        $availableTables,
                ],

                /*
                |--------------------------------------------------------------------------
                | Sales Comparison
                |--------------------------------------------------------------------------
                */

                'sales_comparison' => [

                    'current_month' =>
                        round(
                            (float) $currentMonthSales,
                            2
                        ),

                    'previous_month' =>
                        round(
                            (float) $previousMonthSales,
                            2
                        ),

                    'change_percent' =>
                        $salesChangePercent,
                ],

                /*
                |--------------------------------------------------------------------------
                | Chart
                |--------------------------------------------------------------------------
                */

                'sales_chart' =>
                    $salesChart,

                /*
                |--------------------------------------------------------------------------
                | Top Items
                |--------------------------------------------------------------------------
                */

                'top_items' =>
                    $topItems,

                /*
                |--------------------------------------------------------------------------
                | Recent Orders
                |--------------------------------------------------------------------------
                */

                'recent_orders' =>
                    $recentOrders,

                /*
                |--------------------------------------------------------------------------
                | Order Status
                |--------------------------------------------------------------------------
                */

                'order_status' =>
                    $orderStatus,

                /*
                |--------------------------------------------------------------------------
                | Payment Methods
                |--------------------------------------------------------------------------
                */

                'payment_methods' =>
                    $paymentMethods,
            ],

            'errors' => null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Overview
    |--------------------------------------------------------------------------
    */

    public function adminOverview(
        Request $request
    ): JsonResponse {
        return response()->json([
            'success' => true,

            'message' =>
                'Admin overview retrieved successfully.',

            'data' => [
                'current_user' =>
                    $request->user()->name,

                'total_users' =>
                    User::query()->count(),

                'total_roles' =>
                    Role::query()->count(),

                'total_permissions' =>
                    Permission::query()->count(),
            ],

            'errors' => null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Manager Overview
    |--------------------------------------------------------------------------
    */

    public function managerOverview(
        Request $request
    ): JsonResponse {
        return response()->json([
            'success' => true,

            'message' =>
                'Manager operational overview retrieved successfully.',

            'data' => [
                'current_user' =>
                    $request->user()->name,

                'active_orders' =>
                    Order::query()
                        ->whereIn(
                            'status',
                            [
                                Order::STATUS_PENDING,
                                Order::STATUS_PREPARING,
                                Order::STATUS_READY,
                                Order::STATUS_SERVED,
                            ]
                        )
                        ->count(),

                'occupied_tables' =>
                    RestaurantTable::query()
                        ->where(
                            'status',
                            RestaurantTable::STATUS_OCCUPIED
                        )
                        ->count(),

                'pending_payments' =>
                    Order::query()
                        ->where(
                            'due_amount',
                            '>',
                            0
                        )
                        ->where(
                            'status',
                            '!=',
                            Order::STATUS_CANCELED
                        )
                        ->count(),

                'pending_kitchen_orders' =>
                    Order::query()
                        ->where(
                            'status',
                            Order::STATUS_PENDING
                        )
                        ->count(),
            ],

            'errors' => null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Kitchen Overview
    |--------------------------------------------------------------------------
    */

    public function kitchenOverview(
        Request $request
    ): JsonResponse {
        return response()->json([
            'success' => true,

            'message' =>
                'Kitchen overview retrieved successfully.',

            'data' => [
                'current_user' =>
                    $request->user()->name,

                'new_orders' =>
                    Order::query()
                        ->where(
                            'status',
                            Order::STATUS_PENDING
                        )
                        ->count(),

                'preparing_orders' =>
                    Order::query()
                        ->where(
                            'status',
                            Order::STATUS_PREPARING
                        )
                        ->count(),

                'ready_orders' =>
                    Order::query()
                        ->where(
                            'status',
                            Order::STATUS_READY
                        )
                        ->count(),

                'delayed_orders' =>
                    0,
            ],

            'errors' => null,
        ]);
    }
}
