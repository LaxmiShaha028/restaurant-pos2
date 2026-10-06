<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SalaryPayrollResource;
use App\Models\Expense;
use App\Models\Employee;
use App\Models\SalaryPayroll;
use App\Services\SalaryService;
use App\Services\SalaryPaymentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SalaryController extends Controller
{
    public function __construct(
        private readonly SalaryService $salaryService,
        private readonly SalaryPaymentService $salaryPaymentService
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Salary List
    |--------------------------------------------------------------------------
    */

    /*
|--------------------------------------------------------------------------
| Salary List
|--------------------------------------------------------------------------
*/

public function index(
    Request $request
) {

    $this->ensureAdmin(
        $request
    );

    $validated =
        $request->validate([

            'search' => [
                'nullable',
                'string',
                'max:150',
            ],

            'employee_id' => [
                'nullable',
                'integer',
                'exists:employees,id',
            ],

            'from_date' => [
                'nullable',
                'date',
            ],

            'to_date' => [
                'nullable',
                'date',
                'after_or_equal:from_date',
                'before_or_equal:today',
            ],

            'payment_status' => [
                'nullable',
                Rule::in(
                    SalaryPayroll::allowedPaymentStatuses()
                ),
            ],

            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],

        ]);

    /*
    |--------------------------------------------------------------------------
    | Default Current Month → Today
    |--------------------------------------------------------------------------
    */

    $validated['from_date'] =
        $validated['from_date']
        ??
        now()
            ->startOfMonth()
            ->format('Y-m-d');

    $validated['to_date'] =
        $validated['to_date']
        ??
        today()
            ->format('Y-m-d');

    /*
    |--------------------------------------------------------------------------
    | Salary Payrolls
    |--------------------------------------------------------------------------
    */

    $payrolls =
        $this->salaryService
            ->getSalaries(
                $validated
            );

    /*
    |--------------------------------------------------------------------------
    | Full Summary
    |--------------------------------------------------------------------------
    |
    | Summary is calculated from the complete filtered dataset,
    | not only the current pagination page.
    |
    */

    $summary =
        $this->salaryService
            ->getSalarySummary(
                $validated
            );

    return SalaryPayrollResource::collection(
        $payrolls
    )->additional([

        'success' =>
            true,

        'message' =>
            'Salary payrolls loaded successfully.',

        'selected_period' => [

            'from_date' =>
                $summary['period_start'],

            'to_date' =>
                $summary['period_end'],

        ],

        'summary' =>
            $summary,

    ]);
}


    /*
    |--------------------------------------------------------------------------
    | Employee Options
    |--------------------------------------------------------------------------
    */

    public function employees(Request $request)
    {
        $this->ensureAdmin(
            $request
        );


        $employees =
            Employee::query()
                ->with([
                    'user.role',
                ])
                ->whereHas(
                    'user.role',
                    function ($query) {

                        $query->where(
                            'name',
                            '!=',
                            'admin'
                        );

                    }
                )
                ->orderBy('id')
                ->get()
                ->map(
                    function (Employee $employee) {

                        return [

                            'id' =>
                                (int) $employee->id,

                            'employee_name' =>
                                $employee->user
                                    ?->name
                                ??
                                'Unknown Staff',

                            'phone' =>
                                $employee->phone,

                            'email' =>
                                $employee->user
                                    ?->email,

                            'hourly_rate' =>
                                (float)
                                $employee->hourly_rate,

                            'hourly_rate_formatted' =>
                                '৳ '
                                .
                                number_format(
                                    (float)
                                    $employee
                                        ->hourly_rate,
                                    2
                                )
                                .
                                ' / hour',

                            'role_name' =>
                                $employee->user
                                    ?->role
                                    ?->name,

                        ];

                    }
                )
                ->values();


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Salary employee options loaded successfully.',

            'data' =>
                $employees,

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Salary Options
    |--------------------------------------------------------------------------
    */

    public function options(
        Request $request
    ) {
        $this->ensureAdmin(
            $request
        );

        return response()->json([

            'success' =>
                true,

            'message' =>
                'Salary options loaded successfully.',

            'data' => [

                'payment_methods' =>
                    Expense::paymentMethodOptions(),

            ],
        ]);
    }


    /*
|--------------------------------------------------------------------------
| Process Daily Salary
|--------------------------------------------------------------------------
*/

public function processDaily(
    Request $request
) {

    $this->ensureAdmin(
        $request
    );

    $validated =
        $request->validate([

            'salary_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'employee_id' => [
                'nullable',
                'integer',
                'exists:employees,id',
            ],

        ]);

    $salaryDate =
        $validated['salary_date']
        ??
        today()->format('Y-m-d');

    $result =
        $this->salaryService
            ->processDailySalary(
                $salaryDate,
                $request->user(),
                isset(
                    $validated['employee_id']
                )
                    ? (int)
                        $validated['employee_id']
                    : null
            );

    return response()->json([

        'success' =>
            true,

        'message' =>
            'Daily salary processing completed successfully.',

        'data' =>
            $result,

    ]);
}
    /*
    |--------------------------------------------------------------------------
    | Generate or Recalculate
    |--------------------------------------------------------------------------
    */

    public function generate(Request $request)
    {
        $this->ensureAdmin(
            $request
        );


        $validated =
            $request->validate([

                'from_date' => [
                    'required',
                    'date',
                    'before_or_equal:today',
                ],

                'to_date' => [
                    'required',
                    'date',
                    'after_or_equal:from_date',
                    'before_or_equal:today',
                ],

                'employee_id' => [
                    'nullable',
                    'integer',
                    'exists:employees,id',
                ],

            ]);


        $result =
            $this->salaryService
                ->generatePayrolls(

                    $validated['from_date'],

                    $validated['to_date'],

                    isset(
                        $validated['employee_id']
                    )
                        ? (int)
                            $validated['employee_id']
                        : null,

                    $request->user()

                );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Salary generation and recalculation completed successfully.',

            'data' =>
                $result,

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Update Payroll
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        SalaryPayroll $salary
    ) {
        $this->ensureAdmin(
            $request
        );


        $validated =
            $request->validate([

                'adjustment_amount' => [
                    'nullable',
                    'numeric',
                    'between:-99999999,99999999',
                ],

                'notes' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

            ]);


        $salary =
            $this->salaryService
                ->updatePayroll(

                    $salary,

                    $validated,

                    $request->user()

                );


        return (
            new SalaryPayrollResource(
                $salary
            )
        )->additional([

            'success' =>
                true,

            'message' =>
                'Salary payroll updated successfully.',

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Payment Status
    |--------------------------------------------------------------------------
    */

    public function paymentStatus(
        Request $request,
        SalaryPayroll $salary
    ) {
        $this->ensureAdmin(
            $request
        );


        $validated =
            $request->validate([

                'payment_status' => [
                    'required',

                    Rule::in(
                        SalaryPayroll::allowedPaymentStatuses()
                    ),
                ],

                'payment_method' => [
                    'nullable',
                    'required_if:payment_status,paid',

                    Rule::in(
                        Expense::allowedPaymentMethods()
                    ),
                ],

                'payment_date' => [
                    'nullable',
                    'required_if:payment_status,paid',
                    'date',
                    'before_or_equal:today',
                ],

                'payment_reference' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

            ]);


        $salary =
            $this->salaryPaymentService
                ->updatePaymentStatus(

                    $salary,

                    $validated[
                        'payment_status'
                    ],

                    $request->user(),

                    [
                        'payment_method' =>
                            $validated[
                                'payment_method'
                            ]
                            ??
                            null,

                        'payment_date' =>
                            $validated[
                                'payment_date'
                            ]
                            ??
                            null,

                        'payment_reference' =>
                            $validated[
                                'payment_reference'
                            ]
                            ??
                            null,
                    ]

                );


        return (
            new SalaryPayrollResource(
                $salary
            )
        )->additional([

            'success' =>
                true,

            'message' =>
                $validated[
                    'payment_status'
                ] ===
                SalaryPayroll::STATUS_PAID
                    ? 'Salary marked as paid and salary expense created successfully.'
                    : 'Salary payment was reversed and linked salary expense was removed.',

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Payroll
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        SalaryPayroll $salary
    ) {
        $this->ensureAdmin(
            $request
        );


        $this->salaryService
            ->deletePayroll(
                $salary
            );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Salary payroll deleted successfully.',

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Admin Protection
    |--------------------------------------------------------------------------
    */

    private function ensureAdmin(
        Request $request
    ): void {

        $user =
            $request->user();


        $user?->loadMissing(
            'role'
        );


        $roleName =
            strtolower(
                trim(
                    (string) (
                        $user
                            ?->role
                            ?->name
                        ??
                        ''
                    )
                )
            );


        abort_unless(
            $user
            &&
            $roleName === 'admin',
            403,
            'Only administrators can manage employee salaries.'
        );
    }
}