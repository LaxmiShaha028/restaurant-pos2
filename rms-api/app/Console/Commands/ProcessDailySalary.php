<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SalaryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessDailySalary extends Command
{
    /*
    |--------------------------------------------------------------------------
    | Command Signature
    |--------------------------------------------------------------------------
    */

    protected $signature =
        'salary:process-daily
        {--date= : Process a specific date (Y-m-d)}';


    /*
    |--------------------------------------------------------------------------
    | Command Description
    |--------------------------------------------------------------------------
    */

    protected $description =
        'Process daily employee salary and update monthly payroll.';


    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        private readonly SalaryService $salaryService
    ) {
        parent::__construct();
    }


    /*
    |--------------------------------------------------------------------------
    | Execute
    |--------------------------------------------------------------------------
    */

    public function handle(): int
    {
        /*
        |--------------------------------------------------------------------------
        | Resolve Salary Date
        |--------------------------------------------------------------------------
        */

        $salaryDate =
            $this->option('date')
            ??
            today()->format('Y-m-d');


        $this->info(
            "Starting salary processing for {$salaryDate}..."
        );


        /*
        |--------------------------------------------------------------------------
        | Find System User
        |--------------------------------------------------------------------------
        |
        | SalaryService requires an authenticated/admin User for
        | audit fields such as created_by / updated_by.
        |
        */

        $systemUser =
            User::query()
                ->whereHas(
                    'role',
                    function ($query) {

                        $query->where(
                            'name',
                            'admin'
                        );

                    }
                )
                ->orderBy('id')
                ->first();


        /*
        |--------------------------------------------------------------------------
        | Admin Account Required
        |--------------------------------------------------------------------------
        */

        if (! $systemUser) {

            $this->error(
                'No admin user was found. Daily salary processing stopped.'
            );


            Log::error(
                'Automatic salary processing failed: no admin user found.'
            );


            return self::FAILURE;
        }


        /*
        |--------------------------------------------------------------------------
        | Process Salary
        |--------------------------------------------------------------------------
        */

        try {

            $result =
                $this->salaryService
                    ->processDailySalary(
                        $salaryDate,
                        $systemUser
                    );


            /*
            |--------------------------------------------------------------------------
            | Console Result
            |--------------------------------------------------------------------------
            */

            $this->info(
                'Daily salary processing completed successfully.'
            );


            $this->line(
                'Salary Date: '
                .
                ($result['salary_date'] ?? $salaryDate)
            );


            $this->line(
                'Employees Processed: '
                .
                ($result['employees_processed'] ?? 0)
            );


            $this->line(
                'Payroll Created: '
                .
                ($result['payroll_created'] ?? 0)
            );


            $this->line(
                'Payroll Updated: '
                .
                ($result['payroll_updated'] ?? 0)
            );


            $this->line(
                'Skipped Paid Payrolls: '
                .
                ($result['skipped_paid_payrolls'] ?? 0)
            );


            $this->line(
                'Salary Details Created: '
                .
                ($result['details_created'] ?? 0)
            );


            $this->line(
                'Salary Details Updated: '
                .
                ($result['details_updated'] ?? 0)
            );


            /*
            |--------------------------------------------------------------------------
            | Attendance Preparation Result
            |--------------------------------------------------------------------------
            */

            if (
                isset(
                    $result['attendance']
                )
                &&
                is_array(
                    $result['attendance']
                )
            ) {

                $attendance =
                    $result['attendance'];


                $this->line(
                    'Attendance Auto Checkout: '
                    .
                    (
                        $attendance[
                            'auto_checked_out'
                        ]
                        ??
                        0
                    )
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Success Log
            |--------------------------------------------------------------------------
            */

            Log::info(
                'Automatic daily salary processing completed.',
                [
                    'salary_date' =>
                        $salaryDate,

                    'result' =>
                        $result,
                ]
            );


            return self::SUCCESS;

        }

        catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Error Output
            |--------------------------------------------------------------------------
            */

            $this->error(
                'Daily salary processing failed.'
            );


            $this->error(
                $e->getMessage()
            );


            /*
            |--------------------------------------------------------------------------
            | Error Log
            |--------------------------------------------------------------------------
            */

            Log::error(
                'Automatic daily salary processing failed.',
                [
                    'salary_date' =>
                        $salaryDate,

                    'message' =>
                        $e->getMessage(),

                    'exception' =>
                        $e,
                ]
            );


            return self::FAILURE;
        }
    }
}