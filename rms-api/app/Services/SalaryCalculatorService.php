<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\SalaryDetail;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

class SalaryCalculatorService
{
    /*
    |--------------------------------------------------------------------------
    | Automatic Attendance Salary
    |--------------------------------------------------------------------------
    */

    public function calculateAutomatic(
        Attendance $attendance,
        float $hourlyRate
    ): array {

        $fullLateLimit =
            max(
                0,
                (int) config(
                    'salary.full_salary_late_limit_minutes',
                    10
                )
            );

        $halfLateLimit =
            max(
                $fullLateLimit,
                (int) config(
                    'salary.half_salary_late_limit_minutes',
                    180
                )
            );

        $actualLateMinutes =
            $this->calculateActualLateMinutes(
                $attendance
            );

        $isCompleted =
            $attendance->status ===
                Attendance::STATUS_COMPLETED
            &&
            $attendance->check_in_at !== null
            &&
            $attendance->check_out_at !== null;

        if (! $isCompleted) {
            return [
                'late_minutes' =>
                    $actualLateMinutes,

                ...$this->calculateByType(
                    (int) $attendance->worked_minutes,
                    (int) $attendance->overtime_minutes,
                    $hourlyRate,
                    SalaryDetail::TYPE_NO_SALARY
                ),
            ];
        }

        if (
            $actualLateMinutes <=
            $fullLateLimit
        ) {
            $salaryType =
                (int) $attendance->overtime_minutes > 0
                    ? SalaryDetail::TYPE_FULL_OVERTIME
                    : SalaryDetail::TYPE_FULL;
        }
        elseif (
            $actualLateMinutes <=
            $halfLateLimit
        ) {
            $salaryType =
                (int) $attendance->overtime_minutes > 0
                    ? SalaryDetail::TYPE_HALF_OVERTIME
                    : SalaryDetail::TYPE_HALF;
        }
        else {
            $salaryType =
                (int) $attendance->overtime_minutes > 0
                    ? SalaryDetail::TYPE_OVERTIME_ONLY
                    : SalaryDetail::TYPE_NO_SALARY;
        }

        return [
            'late_minutes' =>
                $actualLateMinutes,

            ...$this->calculateByType(
                (int) $attendance->worked_minutes,
                (int) $attendance->overtime_minutes,
                $hourlyRate,
                $salaryType
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Salary Calculation by Selected Type
    |--------------------------------------------------------------------------
    */

    public function calculateByType(
    int $workedMinutes,
    int $overtimeMinutes,
    float $hourlyRate,
    string $salaryType
): array {

    /*
    |--------------------------------------------------------------------------
    | Validate Salary Type
    |--------------------------------------------------------------------------
    */

    if (
        ! in_array(
            $salaryType,
            SalaryDetail::allowedSalaryTypes(),
            true
        )
    ) {
        throw ValidationException::withMessages([
            'salary_type' =>
                'The selected salary type is invalid.',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Values
    |--------------------------------------------------------------------------
    */

    $workedMinutes =
        max(
            0,
            $workedMinutes
        );

    $overtimeMinutes =
        max(
            0,
            $overtimeMinutes
        );

    $hourlyRate =
        max(
            0,
            $hourlyRate
        );


    /*
    |--------------------------------------------------------------------------
    | Half Salary Percentage
    |--------------------------------------------------------------------------
    */

    $halfPercentage =
        max(
            0,
            min(
                100,
                (float) config(
                    'salary.half_salary_percentage',
                    50
                )
            )
        );


    /*
    |--------------------------------------------------------------------------
    | Overtime Multiplier
    |--------------------------------------------------------------------------
    */

    $overtimeMultiplier =
        max(
            0,
            (float) config(
                'salary.overtime_multiplier',
                1
            )
        );


    /*
    |--------------------------------------------------------------------------
    | Regular Worked Minutes
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | worked_minutes includes overtime.
    |
    | তাই regular salary calculate করার সময় overtime বাদ দিতে হবে।
    |
    */

    $regularWorkedMinutes =
        max(
            0,
            $workedMinutes
            -
            $overtimeMinutes
        );


    /*
    |--------------------------------------------------------------------------
    | Full Regular Salary
    |--------------------------------------------------------------------------
    |
    | Full Salary means:
    |
    | Actual worked NORMAL hours × hourly rate
    |
    */

    $fullSalary =
        round(
            (
                $regularWorkedMinutes
                /
                60
            )
            *
            $hourlyRate,
            2
        );


    /*
    |--------------------------------------------------------------------------
    | Half Regular Salary
    |--------------------------------------------------------------------------
    */

    $halfSalary =
        round(
            $fullSalary
            *
            (
                $halfPercentage
                /
                100
            ),
            2
        );


    /*
    |--------------------------------------------------------------------------
    | Overtime Salary
    |--------------------------------------------------------------------------
    */

    $calculatedOvertime =
        round(
            (
                $overtimeMinutes
                /
                60
            )
            *
            $hourlyRate
            *
            $overtimeMultiplier,
            2
        );


    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */

    $regularSalary = 0;

    $overtimeSalary = 0;


    /*
    |--------------------------------------------------------------------------
    | Salary Type
    |--------------------------------------------------------------------------
    */

    switch ($salaryType) {

        /*
        |----------------------------------------------------------------------
        | Full Salary
        |----------------------------------------------------------------------
        */

        case SalaryDetail::TYPE_FULL:

            $regularSalary =
                $fullSalary;

            break;


        /*
        |----------------------------------------------------------------------
        | Half Salary
        |----------------------------------------------------------------------
        */

        case SalaryDetail::TYPE_HALF:

            $regularSalary =
                $halfSalary;

            break;


        /*
        |----------------------------------------------------------------------
        | Full Salary + Overtime
        |----------------------------------------------------------------------
        */

        case SalaryDetail::TYPE_FULL_OVERTIME:

            $regularSalary =
                $fullSalary;

            $overtimeSalary =
                $calculatedOvertime;

            break;


        /*
        |----------------------------------------------------------------------
        | Half Salary + Overtime
        |----------------------------------------------------------------------
        */

        case SalaryDetail::TYPE_HALF_OVERTIME:

            $regularSalary =
                $halfSalary;

            $overtimeSalary =
                $calculatedOvertime;

            break;


        /*
        |----------------------------------------------------------------------
        | Overtime Only
        |----------------------------------------------------------------------
        */

        case SalaryDetail::TYPE_OVERTIME_ONLY:

            $overtimeSalary =
                $calculatedOvertime;

            break;


        /*
        |----------------------------------------------------------------------
        | No Salary
        |----------------------------------------------------------------------
        */

        case SalaryDetail::TYPE_NO_SALARY:

        default:

            $regularSalary =
                0;

            $overtimeSalary =
                0;

            break;
    }


    /*
    |--------------------------------------------------------------------------
    | Final Result
    |--------------------------------------------------------------------------
    */

    return [

        'salary_type' =>
            $salaryType,

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
            round(
                $regularSalary
                +
                $overtimeSalary,
                2
            ),
    ];
}


    /*
    |--------------------------------------------------------------------------
    | Actual Late Minutes
    |--------------------------------------------------------------------------
    */

    private function calculateActualLateMinutes(
        Attendance $attendance
    ): int {

        if (
            ! $attendance->check_in_at
            ||
            ! $attendance->scheduled_start_at
            ||
            $attendance->check_in_at
                ->lessThanOrEqualTo(
                    $attendance->scheduled_start_at
                )
        ) {
            return 0;
        }

        return $this->minutesBetween(
            $attendance->scheduled_start_at,
            $attendance->check_in_at
        );
    }


    private function minutesBetween(
        CarbonInterface $start,
        CarbonInterface $end
    ): int {

        return max(
            0,
            intdiv(
                $end->getTimestamp()
                -
                $start->getTimestamp(),
                60
            )
        );
    }
}