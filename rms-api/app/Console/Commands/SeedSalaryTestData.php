<?php

namespace App\Console\Commands;

use App\Models\Central\Restaurant;
use App\Models\Employee;
use App\Models\SalaryDetail;
use App\Models\SalaryPayroll;
use App\Models\User;
use App\Services\SalaryCalculatorService;
use App\Services\SalaryPaymentService;
use App\Services\Tenant\TenantConnectionManager;
use App\Services\Tenant\TenantManager;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class SeedSalaryTestData extends Command
{
    private const TEST_MARKER =
        '[TEST DATA] Salary structure verification';


    protected $signature =
        'salary:seed-test
        {--restaurant= : Central restaurant ID}
        {--employee= : Tenant employee ID}
        {--month=2026-08 : Test payroll month in YYYY-MM format}
        {--rate=100 : Test hourly rate used only for the dummy payroll}
        {--cleanup : Remove the dummy payroll/details and reverse its linked salary expense}';


    protected $description =
        'Create or remove safe dummy salary data for Salary -> Expense structure testing.';


    public function __construct(
        private readonly TenantConnectionManager $connectionManager,
        private readonly TenantManager $tenantManager,
        private readonly SalaryCalculatorService $calculator,
        private readonly SalaryPaymentService $salaryPaymentService
    ) {
        parent::__construct();
    }


    public function handle(): int
    {
        DB::setDefaultConnection(
            'central'
        );


        /*
        |--------------------------------------------------------------------------
        | Restaurant
        |--------------------------------------------------------------------------
        */

        $restaurantId =
            (int) (
                $this->option(
                    'restaurant'
                )
                ?? 0
            );


        if ($restaurantId <= 0) {

            $this->showRestaurants();

            $this->newLine();

            $this->warn(
                'Run the command again with --restaurant=ID.'
            );

            return self::FAILURE;
        }


        $restaurant =
            Restaurant::on('central')
                ->with([
                    'tenantDatabase',
                ])
                ->find(
                    $restaurantId
                );


        if (
            ! $restaurant
            ||
            ! $restaurant->tenantDatabase
        ) {

            $this->error(
                'Restaurant or tenant database was not found.'
            );

            return self::FAILURE;
        }


        try {

            /*
            |--------------------------------------------------------------------------
            | Tenant Connection
            |--------------------------------------------------------------------------
            */

            $this->connectionManager
                ->connect(
                    $restaurant
                );


            DB::purge(
                'tenant'
            );


            DB::reconnect(
                'tenant'
            );


            DB::setDefaultConnection(
                'tenant'
            );


            $this->tenantManager
                ->set(
                    $restaurant
                );


            /*
            |--------------------------------------------------------------------------
            | Employee
            |--------------------------------------------------------------------------
            */

            $employeeId =
                (int) (
                    $this->option(
                        'employee'
                    )
                    ?? 0
                );


            if ($employeeId <= 0) {

                $this->showEmployees();

                $this->newLine();

                $this->warn(
                    'Run the command again with --employee=ID.'
                );

                return self::FAILURE;
            }


            $employee =
                Employee::query()
                    ->with([
                        'user.role',
                    ])
                    ->find(
                        $employeeId
                    );


            if (
                ! $employee
                ||
                ! $employee->user
            ) {

                $this->error(
                    'Employee was not found in the selected tenant.'
                );

                return self::FAILURE;
            }


            if (
                strtolower(
                    trim(
                        (string) (
                            $employee
                                ->user
                                ->role
                                ?->name
                            ??
                            ''
                        )
                    )
                )
                ===
                'admin'
            ) {

                $this->error(
                    'Admin cannot be used for this salary test.'
                );

                return self::FAILURE;
            }


            /*
            |--------------------------------------------------------------------------
            | Admin Actor
            |--------------------------------------------------------------------------
            */

            $admin =
                User::query()
                    ->with([
                        'role',
                    ])
                    ->where(
                        'is_active',
                        true
                    )
                    ->whereNull(
                        'blocked_at'
                    )
                    ->whereHas(
                        'role',
                        function ($query): void {

                            $query->where(
                                'name',
                                'admin'
                            );
                        }
                    )
                    ->orderBy(
                        'id'
                    )
                    ->first();


            if (! $admin) {

                $this->error(
                    'No active tenant admin account was found.'
                );

                return self::FAILURE;
            }


            /*
            |--------------------------------------------------------------------------
            | Month
            |--------------------------------------------------------------------------
            */

            $month =
                trim(
                    (string) $this->option(
                        'month'
                    )
                );


            if (
                ! preg_match(
                    '/^\d{4}-\d{2}$/',
                    $month
                )
            ) {

                $this->error(
                    'Month must use YYYY-MM format.'
                );

                return self::FAILURE;
            }


            $periodStart =
                Carbon::parse(
                    $month . '-01'
                )
                    ->startOfMonth()
                    ->startOfDay();


            $periodEnd =
                $periodStart
                    ->copy()
                    ->endOfMonth()
                    ->startOfDay();


            if (
                $periodStart
                    ->greaterThanOrEqualTo(
                        now()
                            ->startOfMonth()
                    )
            ) {

                $this->error(
                    'Use a CLOSED previous month for this payment/expense test.'
                );

                return self::FAILURE;
            }


            /*
            |--------------------------------------------------------------------------
            | Cleanup
            |--------------------------------------------------------------------------
            */

            if (
                (bool) $this->option(
                    'cleanup'
                )
            ) {

                return $this->cleanup(
                    $employee,
                    $periodStart,
                    $admin
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Test Rate
            |--------------------------------------------------------------------------
            */

            $hourlyRate =
                round(
                    max(
                        0.01,
                        (float) $this->option(
                            'rate'
                        )
                    ),
                    2
                );


            return $this->seed(
                $employee,
                $periodStart,
                $periodEnd,
                $hourlyRate,
                $admin
            );

        } catch (Throwable $exception) {

            report(
                $exception
            );


            $this->error(
                $exception->getMessage()
            );


            return self::FAILURE;

        } finally {

            try {

                DB::setDefaultConnection(
                    'central'
                );


                $this->tenantManager
                    ->clear();


                $this->connectionManager
                    ->disconnect();

            } catch (Throwable) {
                //
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Seed
    |--------------------------------------------------------------------------
    */

    private function seed(
        Employee $employee,
        Carbon $periodStart,
        Carbon $periodEnd,
        float $hourlyRate,
        User $admin
    ): int {

        return DB::transaction(
            function () use (
                $employee,
                $periodStart,
                $periodEnd,
                $hourlyRate,
                $admin
            ): int {

                $payroll =
                    SalaryPayroll::query()
                        ->where(
                            'employee_id',
                            $employee->id
                        )
                        ->whereDate(
                            'period_start',
                            $periodStart
                                ->format('Y-m-d')
                        )
                        ->orderByDesc(
                            'id'
                        )
                        ->lockForUpdate()
                        ->first();


                /*
                |--------------------------------------------------------------------------
                | Never Touch Real Payroll
                |--------------------------------------------------------------------------
                */

                if (
                    $payroll
                    &&
                    ! str_starts_with(
                        (string) $payroll->notes,
                        self::TEST_MARKER
                    )
                ) {

                    $this->error(
                        'A real payroll already exists for this employee/month. Choose another employee or test tenant.'
                    );

                    return self::FAILURE;
                }


                if (
                    $payroll
                    &&
                    $payroll->isPaid()
                ) {

                    $this->error(
                        'The existing test payroll is paid. Run --cleanup first.'
                    );

                    return self::FAILURE;
                }


                if (! $payroll) {

                    $payroll =
                        new SalaryPayroll();

                    $payroll->employee_id =
                        $employee->id;

                    $payroll->created_by =
                        $admin->id;
                }


                $payroll->employee_name =
                    $employee->user?->name
                    ??
                    'Test Employee';

                $payroll->employee_phone =
                    $employee->phone;

                $payroll->employee_email =
                    $employee->user?->email;

                $payroll->period_start =
                    $periodStart
                        ->format('Y-m-d');

                $payroll->period_end =
                    $periodEnd
                        ->format('Y-m-d');

                $payroll->hourly_rate =
                    $hourlyRate;

                $payroll->regular_salary =
                    0;

                $payroll->overtime_salary =
                    0;

                $payroll->adjustment_amount =
                    0;

                $payroll->total_amount =
                    0;

                $payroll->payment_status =
                    SalaryPayroll::STATUS_UNPAID;

                $payroll->paid_at =
                    null;

                $payroll->payment_date =
                    null;

                $payroll->payment_method =
                    null;

                $payroll->payment_reference =
                    null;

                $payroll->paid_by =
                    null;

                $payroll->notes =
                    self::TEST_MARKER;

                $payroll->updated_by =
                    $admin->id;

                $payroll->save();


                /*
                |--------------------------------------------------------------------------
                | Reset Only This Test Payroll's Details
                |--------------------------------------------------------------------------
                */

                $payroll
                    ->salaryDetails()
                    ->delete();


                /*
                |--------------------------------------------------------------------------
                | Five Dummy Days
                |--------------------------------------------------------------------------
                |
                | At default rate 100:
                |
                | Day 1: Full salary              = 800
                | Day 2: Full + 1h overtime       = 900
                | Day 3: Half salary              = 400
                | Day 4: No salary                =   0
                | Day 5: Full salary              = 800
                |
                | Expected total (default config) = 2900
                |
                */

                $scenarios = [

                    [
                        'offset' =>
                            0,

                        'worked_minutes' =>
                            480,

                        'overtime_minutes' =>
                            0,

                        'late_minutes' =>
                            0,

                        'salary_type' =>
                            SalaryDetail::TYPE_FULL,

                        'label' =>
                            'Full Salary',
                    ],

                    [
                        'offset' =>
                            1,

                        'worked_minutes' =>
                            540,

                        'overtime_minutes' =>
                            60,

                        'late_minutes' =>
                            5,

                        'salary_type' =>
                            SalaryDetail::TYPE_FULL_OVERTIME,

                        'label' =>
                            'Full Salary + Overtime',
                    ],

                    [
                        'offset' =>
                            2,

                        'worked_minutes' =>
                            480,

                        'overtime_minutes' =>
                            0,

                        'late_minutes' =>
                            30,

                        'salary_type' =>
                            SalaryDetail::TYPE_HALF,

                        'label' =>
                            'Half Salary',
                    ],

                    [
                        'offset' =>
                            3,

                        'worked_minutes' =>
                            0,

                        'overtime_minutes' =>
                            0,

                        'late_minutes' =>
                            240,

                        'salary_type' =>
                            SalaryDetail::TYPE_NO_SALARY,

                        'label' =>
                            'No Salary',
                    ],

                    [
                        'offset' =>
                            4,

                        'worked_minutes' =>
                            480,

                        'overtime_minutes' =>
                            0,

                        'late_minutes' =>
                            0,

                        'salary_type' =>
                            SalaryDetail::TYPE_FULL,

                        'label' =>
                            'Full Salary',
                    ],

                ];


                foreach (
                    $scenarios
                    as $scenario
                ) {

                    $salaryDate =
                        $periodStart
                            ->copy()
                            ->addDays(
                                $scenario[
                                    'offset'
                                ]
                            );


                    $calculation =
                        $this->calculator
                            ->calculateByType(

                                (int) $scenario[
                                    'worked_minutes'
                                ],

                                (int) $scenario[
                                    'overtime_minutes'
                                ],

                                $hourlyRate,

                                $scenario[
                                    'salary_type'
                                ]

                            );


                    SalaryDetail::create([

                        'salary_payroll_id' =>
                            $payroll->id,

                        'attendance_id' =>
                            null,

                        'employee_id' =>
                            $employee->id,

                        'salary_date' =>
                            $salaryDate
                                ->format('Y-m-d'),

                        'scheduled_minutes' =>
                            480,

                        'worked_minutes' =>
                            (int) $scenario[
                                'worked_minutes'
                            ],

                        'late_minutes' =>
                            (int) $scenario[
                                'late_minutes'
                            ],

                        'break_minutes' =>
                            0,

                        'overtime_minutes' =>
                            (int) $scenario[
                                'overtime_minutes'
                            ],

                        'hourly_rate' =>
                            $hourlyRate,

                        'salary_type' =>
                            $calculation[
                                'salary_type'
                            ],

                        'regular_salary' =>
                            $calculation[
                                'regular_salary'
                            ],

                        'overtime_salary' =>
                            $calculation[
                                'overtime_salary'
                            ],

                        'total_amount' =>
                            $calculation[
                                'total_amount'
                            ],

                        'calculation_source' =>
                            SalaryDetail::SOURCE_MANUAL,

                        'notes' =>
                            self::TEST_MARKER
                            .
                            ' - '
                            .
                            $scenario[
                                'label'
                            ],

                        'updated_by' =>
                            $admin->id,

                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Payroll Totals
                |--------------------------------------------------------------------------
                */

                $regularSalary =
                    (float) $payroll
                        ->salaryDetails()
                        ->sum(
                            'regular_salary'
                        );


                $overtimeSalary =
                    (float) $payroll
                        ->salaryDetails()
                        ->sum(
                            'overtime_salary'
                        );


                $totalAmount =
                    round(
                        $regularSalary
                        +
                        $overtimeSalary,
                        2
                    );


                $payroll->update([

                    'regular_salary' =>
                        round(
                            $regularSalary,
                            2
                        ),

                    'overtime_salary' =>
                        round(
                            $overtimeSalary,
                            2
                        ),

                    'total_amount' =>
                        $totalAmount,

                    'updated_by' =>
                        $admin->id,

                ]);


                $this->newLine();


                $this->info(
                    'Dummy salary data created successfully.'
                );


                $this->table(
                    [
                        'Field',
                        'Value',
                    ],
                    [

                        [
                            'Restaurant Tenant',
                            DB::connection(
                                'tenant'
                            )
                                ->getDatabaseName(),
                        ],

                        [
                            'Employee ID',
                            $employee->id,
                        ],

                        [
                            'Employee',
                            $employee->user?->name,
                        ],

                        [
                            'Period',
                            $periodStart
                                ->format('Y-m-d')
                            .
                            ' to '
                            .
                            $periodEnd
                                ->format('Y-m-d'),
                        ],

                        [
                            'Hourly Rate',
                            number_format(
                                $hourlyRate,
                                2
                            ),
                        ],

                        [
                            'Dummy Days',
                            5,
                        ],

                        [
                            'Regular Salary',
                            number_format(
                                $regularSalary,
                                2
                            ),
                        ],

                        [
                            'Overtime Salary',
                            number_format(
                                $overtimeSalary,
                                2
                            ),
                        ],

                        [
                            'Total Salary',
                            number_format(
                                $totalAmount,
                                2
                            ),
                        ],

                        [
                            'Payment Status',
                            'unpaid',
                        ],

                    ]
                );


                $this->newLine();


                $this->line(
                    'Now open Salary Management and select '
                    .
                    $periodStart->format('F Y')
                    .
                    '.'
                );


                return self::SUCCESS;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Cleanup
    |--------------------------------------------------------------------------
    */

    private function cleanup(
        Employee $employee,
        Carbon $periodStart,
        User $admin
    ): int {

        return DB::transaction(
            function () use (
                $employee,
                $periodStart,
                $admin
            ): int {

                $payroll =
                    SalaryPayroll::query()
                        ->where(
                            'employee_id',
                            $employee->id
                        )
                        ->whereDate(
                            'period_start',
                            $periodStart
                                ->format('Y-m-d')
                        )
                        ->orderByDesc(
                            'id'
                        )
                        ->lockForUpdate()
                        ->first();


                if (! $payroll) {

                    $this->warn(
                        'No payroll exists for that employee/month.'
                    );

                    return self::SUCCESS;
                }


                if (
                    ! str_starts_with(
                        (string) $payroll->notes,
                        self::TEST_MARKER
                    )
                ) {

                    $this->error(
                        'Refusing cleanup because this is not the generated test payroll.'
                    );

                    return self::FAILURE;
                }


                if ($payroll->isPaid()) {

                    $this->salaryPaymentService
                        ->updatePaymentStatus(

                            $payroll,

                            SalaryPayroll::STATUS_UNPAID,

                            $admin

                        );


                    $payroll->refresh();
                }


                $payroll
                    ->salaryDetails()
                    ->delete();


                $payroll->delete();


                $this->info(
                    'Dummy salary payroll/details removed. Any linked test salary expense was also reversed.'
                );


                return self::SUCCESS;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Lists
    |--------------------------------------------------------------------------
    */

    private function showRestaurants(): void
    {
        $restaurants =
            Restaurant::on('central')
                ->whereHas(
                    'tenantDatabase'
                )
                ->orderBy(
                    'id'
                )
                ->get([
                    'id',
                    'name',
                    'status',
                ]);


        $this->table(
            [
                'ID',
                'Restaurant',
                'Status',
            ],
            $restaurants
                ->map(
                    fn (
                        Restaurant $restaurant
                    ): array => [

                        $restaurant->id,

                        $restaurant->name,

                        $restaurant->status,

                    ]
                )
                ->all()
        );
    }


    private function showEmployees(): void
    {
        $employees =
            Employee::query()
                ->with([
                    'user.role',
                ])
                ->whereHas(
                    'user.role',
                    function ($query): void {

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
                ->get();


        $this->table(
            [
                'ID',
                'Employee',
                'Role',
                'Current Hourly Rate',
            ],
            $employees
                ->map(
                    fn (
                        Employee $employee
                    ): array => [

                        $employee->id,

                        $employee->user?->name
                        ??
                        'Unknown',

                        $employee->user?->role?->name
                        ??
                        '-',

                        number_format(
                            (float) $employee->hourly_rate,
                            2
                        ),

                    ]
                )
                ->all()
        );
    }
}
