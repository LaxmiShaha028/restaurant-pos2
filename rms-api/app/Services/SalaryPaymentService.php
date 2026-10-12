<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\SalaryPayroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalaryPaymentService
{
    private const SALARY_EXPENSE_CATEGORY =
        'Salary & Wages';


    public function __construct(
        private readonly AccountingSourcePostingService $accountingSourcePostingService
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Update Salary Payment Status
    |--------------------------------------------------------------------------
    */

    public function updatePaymentStatus(
        SalaryPayroll $payroll,
        string $status,
        User $admin,
        array $paymentData = []
    ): SalaryPayroll {

        if (
            ! in_array(
                $status,
                SalaryPayroll::allowedPaymentStatuses(),
                true
            )
        ) {
            throw ValidationException::withMessages([
                'payment_status' => [
                    'The selected payment status is invalid.',
                ],
            ]);
        }


        return DB::transaction(
            function () use (
                $payroll,
                $status,
                $admin,
                $paymentData
            ): SalaryPayroll {

                /*
                |--------------------------------------------------------------------------
                | Lock Payroll
                |--------------------------------------------------------------------------
                */

                $lockedPayroll =
                    SalaryPayroll::query()
                        ->with([
                            'expense',
                        ])
                        ->lockForUpdate()
                        ->findOrFail(
                            $payroll->id
                        );


                if (
                    $status ===
                    SalaryPayroll::STATUS_PAID
                ) {

                    $this->markPaid(
                        $lockedPayroll,
                        $admin,
                        $paymentData
                    );

                } else {

                    $this->markUnpaid(
                        $lockedPayroll,
                        $admin
                    );
                }


                return $lockedPayroll
                    ->fresh([
                        'payer',
                        'employee.user.role',
                        'employee.shiftSchedules',
                        'salaryDetails',
                        'expense.category',
                    ]);
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Mark Paid
    |--------------------------------------------------------------------------
    */

    private function markPaid(
        SalaryPayroll $payroll,
        User $admin,
        array $paymentData
    ): void {

        if ($payroll->isPaid()) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Closed Month Protection
        |--------------------------------------------------------------------------
        |
        | Current month payroll is still accumulating.
        | Paying it early would freeze/skip future daily salary processing.
        |
        */

        if (! $payroll->isClosedPeriod()) {
            throw ValidationException::withMessages([
                'payment_status' => [
                    'Only a closed previous-month payroll can be marked as paid. Complete/recalculate the full month first.',
                ],
            ]);
        }


        if (
            (float) $payroll->total_amount <= 0
        ) {
            throw ValidationException::withMessages([
                'payment_status' => [
                    'A zero or negative salary payroll cannot be marked as paid.',
                ],
            ]);
        }


        $paymentMethod =
            trim(
                (string) (
                    $paymentData[
                        'payment_method'
                    ]
                    ??
                    ''
                )
            );


        if (
            ! in_array(
                $paymentMethod,
                Expense::allowedPaymentMethods(),
                true
            )
        ) {
            throw ValidationException::withMessages([
                'payment_method' => [
                    'Please select a valid salary payment method.',
                ],
            ]);
        }


        $paymentDate =
            Carbon::parse(
                $paymentData[
                    'payment_date'
                ]
                ??
                today()
            )
                ->startOfDay();


        if ($paymentDate->gt(today())) {
            throw ValidationException::withMessages([
                'payment_date' => [
                    'Salary payment date cannot be in the future.',
                ],
            ]);
        }


        $paymentReference =
            trim(
                (string) (
                    $paymentData[
                        'payment_reference'
                    ]
                    ??
                    ''
                )
            );


        $paidAt =
            now();


        /*
        |--------------------------------------------------------------------------
        | Payroll Payment Snapshot
        |--------------------------------------------------------------------------
        */

        $payroll->update([

            'payment_status' =>
                SalaryPayroll::STATUS_PAID,

            'paid_at' =>
                $paidAt,

            'payment_date' =>
                $paymentDate
                    ->format('Y-m-d'),

            'payment_method' =>
                $paymentMethod,

            'payment_reference' =>
                $paymentReference !== ''
                    ? $paymentReference
                    : null,

            'paid_by' =>
                $admin->id,

            'updated_by' =>
                $admin->id,

        ]);


        /*
        |--------------------------------------------------------------------------
        | System Salary Expense
        |--------------------------------------------------------------------------
        */

        $category =
            $this->salaryExpenseCategory();


        $expense =
            Expense::query()
                ->withTrashed()
                ->where(
                    'source_type',
                    Expense::SOURCE_SALARY_PAYROLL
                )
                ->where(
                    'source_id',
                    $payroll->id
                )
                ->lockForUpdate()
                ->first();


        if (! $expense) {
            $expense =
                new Expense();

            $expense->source_type =
                Expense::SOURCE_SALARY_PAYROLL;

            $expense->source_id =
                $payroll->id;

            $expense->created_by =
                $admin->id;
        }


        if ($expense->trashed()) {
            $expense->restore();
        }


        $expense->expense_category_id =
            $category->id;

        $expense->expense_date =
            $paymentDate
                ->copy()
                ->setTime(
                    $paidAt->hour,
                    $paidAt->minute,
                    $paidAt->second
                );

        $expense->amount =
            round(
                (float) $payroll->total_amount,
                2
            );

        $expense->payment_method =
            $paymentMethod;

        $expense->notes =
            $this->buildExpenseNotes(
                $payroll,
                $paymentReference
            );

        $expense->updated_by =
            $admin->id;

        $expense->save();


        /*
        |--------------------------------------------------------------------------
        | Ensure Salary Expense Journal
        |--------------------------------------------------------------------------
        |
        | New expenses are already handled by the Expense model event.
        | This explicit idempotent sync is still required for a restored
        | salary expense whose financial fields happen to be unchanged.
        |
        */

        $this->accountingSourcePostingService
            ->syncExpense(
                $expense->fresh([
                    'category',
                ]),
                $admin,
                'Salary payment was posted or restored.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Mark Unpaid / Reverse Expense
    |--------------------------------------------------------------------------
    |
    | The linked Expense is soft-deleted instead of physically removed.
    | Later, when the Accounting Ledger is implemented, this action can be
    | upgraded to create an explicit reversal journal instead.
    |
    */

    private function markUnpaid(
        SalaryPayroll $payroll,
        User $admin
    ): void {

        if (! $payroll->isPaid()) {
            return;
        }


        $expense =
            Expense::query()
                ->where(
                    'source_type',
                    Expense::SOURCE_SALARY_PAYROLL
                )
                ->where(
                    'source_id',
                    $payroll->id
                )
                ->lockForUpdate()
                ->first();


        if ($expense) {

            $expense->updated_by =
                $admin->id;

            $expense->save();

            $expense->delete();
        }


        $payroll->update([

            'payment_status' =>
                SalaryPayroll::STATUS_UNPAID,

            'paid_at' =>
                null,

            'payment_date' =>
                null,

            'payment_method' =>
                null,

            'payment_reference' =>
                null,

            'paid_by' =>
                null,

            'updated_by' =>
                $admin->id,

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Salary Expense Category
    |--------------------------------------------------------------------------
    */

    private function salaryExpenseCategory(): ExpenseCategory
    {
        $category =
            ExpenseCategory::query()
                ->withTrashed()
                ->whereRaw(
                    'LOWER(name) = LOWER(?)',
                    [
                        self::SALARY_EXPENSE_CATEGORY,
                    ]
                )
                ->lockForUpdate()
                ->first();


        if (! $category) {

            return ExpenseCategory::create([

                'name' =>
                    self::SALARY_EXPENSE_CATEGORY,

                'description' =>
                    'System category for employee salary and wage payments.',

                'is_active' =>
                    true,

            ]);
        }


        if ($category->trashed()) {
            $category->restore();
        }


        if (! $category->is_active) {

            $category->is_active =
                true;

            $category->save();
        }


        return $category;
    }


    /*
    |--------------------------------------------------------------------------
    | Expense Notes
    |--------------------------------------------------------------------------
    */

    private function buildExpenseNotes(
        SalaryPayroll $payroll,
        string $paymentReference
    ): string {

        $period =
            (
                $payroll->period_start
                    ?->format('d M Y')
                ??
                'Unknown'
            )
            .
            ' - '
            .
            (
                $payroll->period_end
                    ?->format('d M Y')
                ??
                'Unknown'
            );


        $parts = [

            'System generated salary payment.',

            'Employee: '
            .
            (
                $payroll->employee_name
                ??
                'Unknown Staff'
            )
            .
            '.',

            "Payroll period: {$period}.",

            "Payroll ID: {$payroll->id}.",

        ];


        if ($paymentReference !== '') {
            $parts[] =
                "Reference: {$paymentReference}.";
        }


        return implode(
            ' ',
            $parts
        );
    }
}
