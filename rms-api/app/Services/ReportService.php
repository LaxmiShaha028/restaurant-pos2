<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Order;
use App\Models\PurchaseOrder;
use App\Models\RestaurantStock;
use App\Models\WarehouseStock;
use App\Models\StockTransfer;
use App\Models\Expense;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReportService
{
    /*
    |--------------------------------------------------------------------------
    | Attendance Report
    |--------------------------------------------------------------------------
    */

    
/*
|--------------------------------------------------------------------------
| Orders Report
|--------------------------------------------------------------------------
*/

public function getOrders(
    array $filters = []
): LengthAwarePaginator {

    $dateFrom = $filters['date_from'] ?? now()->toDateString();

    $dateTo = $filters['date_to'] ?? $dateFrom;

    return Order::query()

        ->with([
            'customer',
            'items',
        ])

        ->when(
            $dateFrom,
            function ($query, $date) {
                $query->whereDate(
                    'created_at',
                    '>=',
                    $date
                );
            }
        )

        ->when(
            $dateTo,
            function ($query, $date) {
                $query->whereDate(
                    'created_at',
                    '<=',
                    $date
                );
            }
        )

        ->latest('created_at')

        ->paginate(
            (int) (
                $filters['per_page']
                ??
                10
            )
        );
}

/*
|--------------------------------------------------------------------------
| Expenses Report
|--------------------------------------------------------------------------
*/

public function getExpenses(
    array $filters = []
): LengthAwarePaginator {

    $dateFrom =
        $filters['date_from']
        ?? now()->toDateString();

    $dateTo =
        $filters['date_to']
        ?? $dateFrom;

    return Expense::query()

        ->with([
            'category',
        ])

        ->when(
            $dateFrom,
            function (
                $query,
                $date
            ) {

                $query->whereDate(
                    'expense_date',
                    '>=',
                    $date
                );

            }
        )

        ->when(
            $dateTo,
            function (
                $query,
                $date
            ) {

                $query->whereDate(
                    'expense_date',
                    '<=',
                    $date
                );

            }
        )

        ->latest(
            'expense_date'
        )

        ->latest(
            'id'
        )

        ->paginate(
            (int) (
                $filters['per_page']
                ??
                10
            )
        );
}
/*
|--------------------------------------------------------------------------
| Purchase Orders Report
|--------------------------------------------------------------------------
*/

public function getPurchaseOrders(
    array $filters = []
): LengthAwarePaginator {

    $dateFrom =
        $filters['date_from']
        ?? now()->toDateString();

    $dateTo =
        $filters['date_to']
        ?? $dateFrom;

    return PurchaseOrder::query()

        ->with([
            'supplier',
        ])

        ->when(
            $dateFrom,
            function (
                $query,
                $date
            ) {

                $query->whereDate(
                    'order_date',
                    '>=',
                    $date
                );

            }
        )

        ->when(
            $dateTo,
            function (
                $query,
                $date
            ) {

                $query->whereDate(
                    'order_date',
                    '<=',
                    $date
                );

            }
        )

        ->latest(
            'order_date'
        )

        ->latest(
            'id'
        )

        ->paginate(
            (int) (
                $filters['per_page']
                ??
                10
            )
        );
}
/*
|--------------------------------------------------------------------------
| Attendance Report
|--------------------------------------------------------------------------
*/

public function getAttendance(
    array $filters = []
): LengthAwarePaginator {

    return Attendance::query()

        ->with([
            'employee.user.role',
            'salaryDetail',
        ])

        ->when(
            $filters['date_from'] ?? null,
            function (
                $query,
                $date
            ) {

                $query->whereDate(
                    'attendance_date',
                    '>=',
                    $date
                );

            }
        )

        ->when(
            $filters['date_to'] ?? null,
            function (
                $query,
                $date
            ) {

                $query->whereDate(
                    'attendance_date',
                    '<=',
                    $date
                );

            }
        )

        ->when(
            $filters['employee_id'] ?? null,
            function (
                $query,
                $employeeId
            ) {

                $query->where(
                    'employee_id',
                    (int) $employeeId
                );

            }
        )

        ->when(
            $filters['status'] ?? null,
            function (
                $query,
                $status
            ) {

                $query->where(
                    'status',
                    $status
                );

            }
        )

        ->latest(
            'attendance_date'
        )

        ->latest(
            'id'
        )

        ->paginate(
            (int) (
                $filters['per_page']
                ??
                10
            )
        );
}


/*
|--------------------------------------------------------------------------
| Attendance Report Employees
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Attendance Report Employees
|--------------------------------------------------------------------------
*/

public function getAttendanceEmployees()
{
    return Employee::query()

        ->with([
            'user.role',
        ])

        ->whereHas(
            'user.role',
            function (
                $query
            ) {

                $query->where(
                    'name',
                    '!=',
                    'admin'
                );

            }
        )

        ->orderBy(
            'id'
        )

        ->get()

        ->map(
            function (
                Employee $employee
            ) {

                return [

                    'id' =>
                        (int)
                        $employee->id,

                    'employee_name' =>
                        $employee
                            ->user
                            ?->name
                        ??
                        'Unknown Staff',

                    'phone' =>
                        $employee->phone,

                    'email' =>
                        $employee
                            ->user
                            ?->email,

                    'role_name' =>
                        $employee
                            ->user
                            ?->role
                            ?->name,

                    'hourly_rate' =>
                        (float)
                        $employee
                            ->hourly_rate,

                ];

            }
        )

        ->values();
}

    /*
    |--------------------------------------------------------------------------
    | Export Data
    |--------------------------------------------------------------------------
    */

    public function getExportData(
        string $type,
        array $filters = []
    ): array {

        return match ($type) {


            /*
            |--------------------------------------------------------------------------
            | Orders
            |--------------------------------------------------------------------------
            */

            'orders' =>

                collect(

                    $this->getOrders(

                        array_merge(
                            $filters,
                            [
                                'per_page' => 999999
                            ]
                        )

                    )->items()

                )

                ->map(
                    function ($order) {

                        return [

                            'Order ID' =>
                                $order->id,

                            'Customer' =>
                                $order->customer?->name
                                ??
                                'Walk In',

                            'Date' =>
                                $order->created_at,

                            'Amount' =>
                                $order->total_amount,

                            'Payment' =>
                                $order->payment_method
                                ??
                                '-',

                        ];

                    }
                )

                ->values()

                ->toArray(),



            /*
            |--------------------------------------------------------------------------
            | Expenses
            |--------------------------------------------------------------------------
            */

            'expenses' =>

                collect(

                    $this->getExpenses(

                        array_merge(
                            $filters,
                            [
                                'per_page' => 999999
                            ]
                        )

                    )->items()

                )

                ->map(
                    function ($expense) {

                        return [

                            'Category' =>
                                $expense->category?->name
                                ??
                                '-',

                            'Date' =>
                                $expense->expense_date,

                            'Amount' =>
                                $expense->amount,

                            'Payment' =>
                                $expense->payment_method
                                ??
                                '-',

                            'Note' =>
                                $expense->notes
                                ??
                                '-',

                        ];

                    }
                )

                ->values()

                ->toArray(),



            /*
            |--------------------------------------------------------------------------
            | Purchase
            |--------------------------------------------------------------------------
            */

            'purchase' =>

                collect(

                    $this->getPurchaseOrders(

                        array_merge(
                            $filters,
                            [
                                'per_page' => 999999
                            ]
                        )

                    )->items()

                )

                ->map(
                    function ($purchase) {

                        return [

                            'Purchase ID' =>
                                $purchase->id,

                            'Supplier' =>
                                $purchase->supplier?->supplier_name
                                ??
                                '-',

                            'Date' =>
                                $purchase->order_date,

                            'Total Amount' =>
                                $purchase->total_amount,

                            'Paid' =>
                                $purchase->paid_amount,

                            'Due' =>
                                $purchase->due_amount,

                            'Status' =>
                                $purchase->status,

                        ];

                    }
                )

                ->values()

                ->toArray(),



            /*
            |--------------------------------------------------------------------------
            | Restaurant Stock
            |--------------------------------------------------------------------------
            */

            /*
|--------------------------------------------------------------------------
| Restaurant Stock
|--------------------------------------------------------------------------
*/

'restaurant-stock' =>

    $this->exportRestaurantStock(),


/*
|--------------------------------------------------------------------------
| Warehouse Stock
|--------------------------------------------------------------------------
*/

'warehouse-stock' =>

    $this->exportWarehouseStock(),


/*
|--------------------------------------------------------------------------
| Stock Transfer History
|--------------------------------------------------------------------------
*/

'stock-transfers' =>

    $this->exportStockTransfers(
        $filters
    ),



            /*
            |--------------------------------------------------------------------------
            | Attendance
            |--------------------------------------------------------------------------
            */

            'attendance' =>

                $this->exportAttendance(
                    $filters
                ),



            /*
            |--------------------------------------------------------------------------
            | Default
            |--------------------------------------------------------------------------
            */

            default => [],

        };
    }

/*
|--------------------------------------------------------------------------
| Attendance Monthly Matrix Export
|--------------------------------------------------------------------------
*/

private function exportAttendance(
    array $filters = []
): array {

    /*
    |--------------------------------------------------------------------------
    | Date Range
    |--------------------------------------------------------------------------
    */

    $dateFrom = Carbon::parse(
        $filters['date_from']
        ?? now()->startOfMonth()->toDateString()
    )->startOfDay();


    $dateTo = Carbon::parse(
        $filters['date_to']
        ?? $dateFrom->copy()->endOfMonth()->toDateString()
    )->startOfDay();


    /*
    |--------------------------------------------------------------------------
    | PDF Month
    |--------------------------------------------------------------------------
    |
    | Attendance sheet will represent the month of date_from.
    |
    */

    $monthStart =
        $dateFrom->copy()->startOfMonth();

    $monthEnd =
        $dateFrom->copy()->endOfMonth();


    /*
    |--------------------------------------------------------------------------
    | Employees
    |--------------------------------------------------------------------------
    */

    $employeeQuery =
        Employee::query()
            ->with([
                'user.role',
                'shiftSchedules.overrides',
            ])
            ->orderBy('id');


    /*
    |--------------------------------------------------------------------------
    | Optional Employee Filter
    |--------------------------------------------------------------------------
    */

    if (!empty($filters['employee_id'])) {

        $employeeQuery->where(
            'id',
            (int) $filters['employee_id']
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Employees
    |--------------------------------------------------------------------------
    */

    $employees =
        $employeeQuery->get();


    /*
    |--------------------------------------------------------------------------
    | Attendance Records
    |--------------------------------------------------------------------------
    */

    $attendanceQuery =
        Attendance::query()
            ->with([
                'employee.user',
            ])
            ->whereBetween(
                'attendance_date',
                [
                    $monthStart->toDateString(),
                    $monthEnd->toDateString(),
                ]
            );


    if (!empty($filters['employee_id'])) {

        $attendanceQuery->where(
            'employee_id',
            (int) $filters['employee_id']
        );
    }


    $attendanceRecords =
        $attendanceQuery
            ->get()
            ->groupBy(
                function (Attendance $attendance) {
                    return
                        $attendance->employee_id
                        . '|'
                        .
                        $attendance
                            ->attendance_date
                            ->format('Y-m-d');
                }
            );


    /*
    |--------------------------------------------------------------------------
    | Dates
    |--------------------------------------------------------------------------
    */

    $dates =
        collect(
            CarbonPeriod::create(
                $monthStart,
                $monthEnd
            )
        );


    /*
    |--------------------------------------------------------------------------
    | Matrix Rows
    |--------------------------------------------------------------------------
    */

    $rows = [];


    foreach ($employees as $index => $employee) {

        /*
        |--------------------------------------------------------------------------
        | Skip employees who joined after the month date
        |--------------------------------------------------------------------------
        */

        $joiningDate =
            $employee->joining_date
                ?->copy()
                ->startOfDay();


        $days = [];


        foreach ($dates as $date) {

            $date =
                $date->copy()->startOfDay();


            $dayNumber =
                (int) $date->format('j');


            /*
            |--------------------------------------------------------------------------
            | Default blank
            |--------------------------------------------------------------------------
            */

            $days[$dayNumber] = '';


            /*
            |--------------------------------------------------------------------------
            | Employee has not joined yet
            |--------------------------------------------------------------------------
            */

            if (
                $joiningDate
                &&
                $date->lt($joiningDate)
            ) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Do not mark future dates
            |--------------------------------------------------------------------------
            */

            $today =
                now()->startOfDay();


            if ($date->gt($today)) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Find applicable schedule
            |--------------------------------------------------------------------------
            */

            $schedule =
                $this->resolveScheduleForDate(
                    $employee,
                    $date
                );


            /*
            |--------------------------------------------------------------------------
            | No working schedule = blank
            |--------------------------------------------------------------------------
            */

            if (!$schedule) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Day Off Override = blank
            |--------------------------------------------------------------------------
            */

            $override =
                $schedule->overrideForDate(
                    $date
                );


            if (
                $override
                &&
                $override->isDayOff()
            ) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Attendance Record
            |--------------------------------------------------------------------------
            */

            $key =
                $employee->id
                . '|'
                . $date->format('Y-m-d');


            $attendance =
                $attendanceRecords
                    ->get($key)
                    ?->sortByDesc('id')
                    ?->first();


            /*
            |--------------------------------------------------------------------------
            | Existing Attendance
            |--------------------------------------------------------------------------
            */

            if ($attendance) {

                $days[$dayNumber] =
                    $this->attendanceMatrixStatus(
                        $attendance,
                        $schedule,
                        $override,
                        $date
                    );

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | No Attendance Record
            |--------------------------------------------------------------------------
            |
            | If today's shift has not started yet:
            | blank.
            |
            | If shift has started or date is in past:
            | absent.
            |
            */

            if (
                $this->hasShiftStartedForDate(
                    $schedule,
                    $override,
                    $date
                )
            ) {

                $days[$dayNumber] = 'A';

            }

        }


        $rows[] = [

            'sl' =>
                $index + 1,

            'employee_name' =>
                $employee
                    ->user
                    ?->name
                ??
                'Unknown Staff',

            'days' =>
                $days,

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Month Information
    |--------------------------------------------------------------------------
    */

    return [

        'event' =>
            config(
                'app.name',
                'Restaurant Management System'
            ),

        'month' =>
            $monthStart->format('F Y'),

        'month_start' =>
            $monthStart->toDateString(),

        'month_end' =>
            $monthEnd->toDateString(),

        'days_in_month' =>
            $monthStart->daysInMonth,

        'rows' =>
            $rows,

    ];
}

/*
|--------------------------------------------------------------------------
| Attendance CSV Export
|--------------------------------------------------------------------------
*/

public function exportAttendanceCsv(
    array $filters = []
): array {

    $data =
        $this->exportAttendance(
            $filters
        );


    $headers = [

        'SL',

        'NAME',

    ];


    /*
    |--------------------------------------------------------------------------
    | Day Headers
    |--------------------------------------------------------------------------
    */

    for (
        $day = 1;
        $day <= $data['days_in_month'];
        $day++
    ) {

        $headers[] =
            (string) $day;

    }


    /*
    |--------------------------------------------------------------------------
    | CSV Rows
    |--------------------------------------------------------------------------
    */

    $rows = [];

    $rows[] = $headers;


    /*
    |--------------------------------------------------------------------------
    | Employee Rows
    |--------------------------------------------------------------------------
    */

    foreach (
        $data['rows']
        as $row
    ) {

        $csvRow = [

            $row['sl'],

            $row['employee_name'],

        ];


        for (
            $day = 1;
            $day <= $data['days_in_month'];
            $day++
        ) {

            $csvRow[] =
                $row['days'][$day]
                ?? '';

        }


        $rows[] =
            $csvRow;

    }


    return $rows;
}
/*
|--------------------------------------------------------------------------
| Resolve Schedule For Date
|--------------------------------------------------------------------------
*/

private function resolveScheduleForDate(
    Employee $employee,
    Carbon $date
) {

    $schedules =
        $employee
            ->shiftSchedules
            ->filter(
                function ($schedule) use ($date) {

                    if (!$schedule->is_active) {
                        return false;
                    }

                    return $schedule->appliesToDate(
                        $date
                    );
                }
            )
            ->sortByDesc(
                function ($schedule) {

                    return
                        $schedule->start_date?->timestamp
                        ??
                        0;
                }
            );

    return $schedules->first();
}


/*
|--------------------------------------------------------------------------
| Attendance Status → Matrix Status
|--------------------------------------------------------------------------
*/

private function attendanceMatrixStatus(
    Attendance $attendance,
    $schedule,
    $override,
    Carbon $date
): string {

    return match ($attendance->status) {

        Attendance::STATUS_PRESENT =>
            'P',

        Attendance::STATUS_BREAK =>
            'P',

        Attendance::STATUS_COMPLETED =>
            'P',

        Attendance::STATUS_ABSENT =>
            'A',

        Attendance::STATUS_LEAVE =>
            'A',

        Attendance::STATUS_SCHEDULED =>

            $this->hasShiftStartedForDate(
                $schedule,
                $override,
                $date
            )
                ? 'A'
                : '',

        default =>
            '',

    };
}


/*
|--------------------------------------------------------------------------
| Check Shift Started
|--------------------------------------------------------------------------
*/

private function hasShiftStartedForDate(
    $schedule,
    $override,
    Carbon $date
): bool {

    $startTime =
        $override?->isModified()
            && $override->start_time
            ? $override->start_time
            : $schedule?->start_time;


    if (!$startTime) {
        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | Past date
    |--------------------------------------------------------------------------
    */

    if (
        $date->copy()->startOfDay()
            ->lt(
                now()->startOfDay()
            )
    ) {

        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | Today
    |--------------------------------------------------------------------------
    */

    if (
        $date->isSameDay(
            now()
        )
    ) {

        $time =
            Carbon::parse(
                $date->format('Y-m-d')
                . ' '
                . $startTime
            );

        return now()->gte($time);
    }


    return false;
}

    /*
    |--------------------------------------------------------------------------
    | Format Minutes
    |--------------------------------------------------------------------------
    */

    /*
|--------------------------------------------------------------------------
| Format Minutes
|--------------------------------------------------------------------------
*/

private function formatMinutes(
    mixed $minutes
): string {

    $minutes =
        max(
            0,
            (int) (
                $minutes
                ??
                0
            )
        );


    if (
        $minutes <= 0
    ) {

        return '0h';

    }


    $hours =
        intdiv(
            $minutes,
            60
        );


    $remainingMinutes =
        $minutes % 60;


    if (
        $remainingMinutes === 0
    ) {

        return "{$hours}h";

    }


    return
        $hours
        . 'h '
        . $remainingMinutes
        . 'm';
}
    private function exportRestaurantStock(): array
{
    $stocks =

        RestaurantStock::query()

            ->with([
                'rawMaterial',
            ])

            ->orderBy(
                'id'
            )

            ->get();


    return $stocks

        ->map(
            function ($stock) {

                return [

                    'Material' =>

                        $stock
                            ->rawMaterial
                            ?->material_name
                        ??
                        $stock
                            ->rawMaterial
                            ?->name
                        ??
                        '-',

                    'Unit' =>

                        $stock
                            ->rawMaterial
                            ?->base_unit
                        ??
                        '-',

                    'Quantity' =>

                        (float) (
                            $stock->quantity
                            ?? 0
                        ),

                    'Unit Cost' =>

                        (float) (
                            $stock->average_unit_cost
                            ?? 0
                        ),

                    'Total Value' =>

                        round(
                            (
                                (float) (
                                    $stock->quantity
                                    ?? 0
                                )
                            )
                            *
                            (
                                (float) (
                                    $stock->average_unit_cost
                                    ?? 0
                                )
                            ),
                            4
                        ),

                    'Last Received' =>

                        $stock
                            ->last_received_at
                            ?->format(
                                'Y-m-d H:i:s'
                            )
                        ??
                        '-',

                ];

            }
        )

        ->values()

        ->toArray();
}
/*
|--------------------------------------------------------------------------
| Warehouse Stock Export
|--------------------------------------------------------------------------
*/

private function exportWarehouseStock(): array
{
    $stocks =

        WarehouseStock::query()

            ->with([
                'rawMaterial',
            ])

            ->orderBy(
                'id'
            )

            ->get();


    return $stocks

        ->map(
            function (
                WarehouseStock $stock
            ) {

                return [

                    'Material' =>

                        $stock
                            ->rawMaterial
                            ?->material_name
                        ??
                        $stock
                            ->rawMaterial
                            ?->name
                        ??
                        '-',

                    'Unit' =>

                        $stock
                            ->rawMaterial
                            ?->base_unit
                        ??
                        '-',

                    'Quantity' =>

                        $stock
                            ->currentQuantity(),

                    'Unit Cost' =>

                        $stock
                            ->averageUnitCost(),

                    'Total Value' =>

                        $stock
                            ->stockValue(),

                    'Status' =>

                        $stock
                            ->statusLabel,

                    'Last Received' =>

                        $stock
                            ->last_received_at
                            ?->format(
                                'Y-m-d H:i:s'
                            )
                        ??
                        '-',

                ];

            }
        )

        ->values()

        ->toArray();
}
/*
|--------------------------------------------------------------------------
| Stock Transfer History Export
|--------------------------------------------------------------------------
*/

private function exportStockTransfers(
    array $filters = []
): array {

    $query =

        StockTransfer::query()

            ->with([
                'transferredBy',
                'items.rawMaterial',
            ]);


    /*
    |--------------------------------------------------------------------------
    | Date From
    |--------------------------------------------------------------------------
    */

    if (
        !empty(
            $filters['date_from']
        )
    ) {

        $query->whereDate(

            'transferred_at',

            '>=',

            $filters['date_from']

        );

    }


    /*
    |--------------------------------------------------------------------------
    | Date To
    |--------------------------------------------------------------------------
    */

    if (
        !empty(
            $filters['date_to']
        )
    ) {

        $query->whereDate(

            'transferred_at',

            '<=',

            $filters['date_to']

        );

    }


    /*
    |--------------------------------------------------------------------------
    | Latest First
    |--------------------------------------------------------------------------
    */

    $transfers =

        $query

            ->latest(
                'transferred_at'
            )

            ->latest(
                'id'
            )

            ->get();


    /*
    |--------------------------------------------------------------------------
    | Prepare Export Rows
    |--------------------------------------------------------------------------
    */

    return $transfers

        ->map(
            function (
                StockTransfer $transfer
            ) {

                $totalQuantity =

                    $transfer
                        ->items
                        ->sum(
                            function (
                                $item
                            ) {

                                return
                                    (float)
                                    (
                                        $item
                                            ->quantity
                                        ?? 0
                                    );

                            }
                        );


                $totalValue =

                    $transfer
                        ->items
                        ->sum(
                            function (
                                $item
                            ) {

                                return
                                    (
                                        (float)
                                        (
                                            $item
                                                ->quantity
                                            ?? 0
                                        )
                                    )
                                    *
                                    (
                                        (float)
                                        (
                                            $item
                                                ->unit_cost
                                            ?? 0
                                        )
                                    );

                            }
                        );


                $itemsSummary =

                    $transfer
                        ->items
                        ->map(
                            function (
                                $item
                            ) {

                                $quantity =

                                    (float)
                                    (
                                        $item
                                            ->quantity
                                        ?? 0
                                    );


                                $unit =

                                    strtoupper(
                                        (string)
                                        (
                                            $item
                                                ->unit
                                            ?? ''
                                        )
                                    );


                                return

                                    $item
                                        ->item_name
                                    .
                                    ' x'
                                    .
                                    rtrim(
                                        rtrim(
                                            number_format(
                                                $quantity,
                                                4,
                                                '.',
                                                ''
                                            ),
                                            '0'
                                        ),
                                        '.'
                                    )
                                    .
                                    (
                                        $unit !== ''
                                            ? ' '
                                              . $unit
                                            : ''
                                    );

                            }
                        )
                        ->implode(
                            ', '
                        );


                return [

                    'Transfer No' =>

                        $transfer
                            ->transfer_no,

                    'Date' =>

                        $transfer
                            ->transferred_at
                            ?->format(
                                'Y-m-d H:i:s'
                            )
                        ??
                        '-',

                    'Transferred By' =>

                        $transfer
                            ->transferredBy
                            ?->name
                        ??
                        '-',

                    'Items' =>

                        $itemsSummary
                        ?: '-',

                    'Total Quantity' =>

                        round(
                            (float)
                            $totalQuantity,
                            4
                        ),

                    'Total Value' =>

                        round(
                            (float)
                            $totalValue,
                            4
                        ),

                    'Notes' =>

                        $transfer
                            ->notes
                        ??
                        '-',

                ];

            }
        )

        ->values()

        ->toArray();
}
/*
|--------------------------------------------------------------------------
| Profit & Loss Report
|--------------------------------------------------------------------------
*/

public function getProfitLoss(
    array $filters = []
): array {


    $dateFrom =
        $filters['date_from']
        ??
        now()
            ->startOfMonth()
            ->toDateString();


    $dateTo =
        $filters['date_to']
        ??
        now()
            ->toDateString();



    /*
    |--------------------------------------------------------------------------
    | Revenue
    |--------------------------------------------------------------------------
    */


    $revenueAccounts = [

        'restaurant_sales_revenue',

        'service_charge',

    ];


    $revenueLines = \App\Models\JournalLine::query()

        ->whereIn(
            'account_name_snapshot',
            [
                'Restaurant Sales Revenue',
                'Service Charge',
            ]
        )

        ->whereHas(
            'journalEntry',
            function($query) use (
                $dateFrom,
                $dateTo
            ){

                $query
                    ->where(
                        'status',
                        'posted'
                    )
                    ->whereBetween(
                        'journal_date',
                        [
                            $dateFrom,
                            $dateTo,
                        ]
                    );

            }
        )

        ->selectRaw(
            '
            account_name_snapshot,
            SUM(credit) as total
            '
        )

        ->groupBy(
            'account_name_snapshot'
        )

        ->get();



    $totalRevenue =
        $revenueLines
            ->sum(
                'total'
            );



    /*
    |--------------------------------------------------------------------------
    | Expenses + Discount + COGS
    |--------------------------------------------------------------------------
    */


    $expenseLines =
        \App\Models\JournalLine::query()

        ->whereIn(
            'account_type_snapshot',
            [
                'expense',
                'cogs',
            ]
        )

        ->whereHas(
            'journalEntry',
            function($query) use (
                $dateFrom,
                $dateTo
            ){

                $query
                    ->where(
                        'status',
                        'posted'
                    )
                    ->whereBetween(
                        'journal_date',
                        [
                            $dateFrom,
                            $dateTo,
                        ]
                    );

            }
        )

        ->selectRaw(
            '
            account_name_snapshot,
            SUM(debit) as total
            '
        )

        ->groupBy(
            'account_name_snapshot'
        )

        ->get();



    $totalExpense =
        $expenseLines
            ->sum(
                'total'
            );



    return [

        'period' => [

            'from' =>
                $dateFrom,

            'to' =>
                $dateTo,

        ],


        'revenue' => [

            'details' =>
                $revenueLines,

            'total' =>
                round(
                    $totalRevenue,
                    2
                ),

        ],


        'expenses' => [

            'details' =>
                $expenseLines,

            'total' =>
                round(
                    $totalExpense,
                    2
                ),

        ],



        'net_profit' =>

            round(

                $totalRevenue
                -
                $totalExpense,

                2

            ),

    ];

}
}