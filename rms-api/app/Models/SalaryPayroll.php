<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryPayroll extends Model
{
    use HasFactory;
    use SoftDeletes;


    public const STATUS_UNPAID =
        'unpaid';

    public const STATUS_PAID =
        'paid';


    protected $fillable = [

        'employee_id',

        'employee_name',

        'employee_phone',

        'employee_email',

        'period_start',

        'period_end',

        'hourly_rate',

        'regular_salary',

        'overtime_salary',

        'adjustment_amount',

        'total_amount',

        'payment_status',

        'paid_at',

        'payment_date',

        'payment_method',

        'payment_reference',

        'paid_by',

        'notes',

        'created_by',

        'updated_by',

    ];


    protected $casts = [

        'period_start' =>
            'date',

        'period_end' =>
            'date',

        'hourly_rate' =>
            'decimal:2',

        'regular_salary' =>
            'decimal:2',

        'overtime_salary' =>
            'decimal:2',

        'adjustment_amount' =>
            'decimal:2',

        'total_amount' =>
            'decimal:2',

        'paid_at' =>
            'datetime',

        'payment_date' =>
            'date',

    ];


    public static function allowedPaymentStatuses(): array
    {
        return [

            self::STATUS_UNPAID,

            self::STATUS_PAID,

        ];
    }


    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class
        );
    }


    public function salaryDetails(): HasMany
    {
        return $this->hasMany(
            SalaryDetail::class
        );
    }


    public function payer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'paid_by'
        );
    }


    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }


    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Linked Salary Expense
    |--------------------------------------------------------------------------
    |
    | Expense.source_type + source_id keeps this relationship generic so the
    | Expense module can later accept Purchase, Salary and other accounting
    | sources without adding a new FK column for every module.
    |
    */

    public function expense(): HasOne
    {
        return $this->hasOne(
            Expense::class,
            'source_id',
            'id'
        )
            ->where(
                'source_type',
                Expense::SOURCE_SALARY_PAYROLL
            );
    }


    public function isPaid(): bool
    {
        return (
            $this->payment_status ===
            self::STATUS_PAID
        );
    }


    public function paymentStatusLabel(): string
    {
        return $this->isPaid()
            ? 'Paid'
            : 'Unpaid';
    }


    /*
    |--------------------------------------------------------------------------
    | Period State
    |--------------------------------------------------------------------------
    */

    public function isCurrentMonth(): bool
    {
        if (! $this->period_start) {
            return false;
        }

        return $this->period_start
            ->isSameMonth(
                now()
            )
            &&
            $this->period_start
                ->isSameYear(
                    now()
                );
    }


    public function isClosedPeriod(): bool
    {
        if (
            ! $this->period_start
            ||
            ! $this->period_end
        ) {
            return false;
        }

        $expectedEnd =
            $this->period_start
                ->copy()
                ->endOfMonth()
                ->startOfDay();

        return (
            $this->period_end
                ->copy()
                ->startOfDay()
                ->equalTo(
                    $expectedEnd
                )
            &&
            $this->period_start
                ->copy()
                ->startOfMonth()
                ->lt(
                    now()
                        ->startOfMonth()
                )
        );
    }


    public function canBeMarkedPaid(): bool
    {
        return (
            ! $this->isPaid()
            &&
            $this->isClosedPeriod()
            &&
            (float) $this->total_amount > 0
        );
    }
}
