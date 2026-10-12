<?php

namespace App\Models;

use App\Services\AccountingSourcePostingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Income extends Model
{
    use HasFactory;
    use SoftDeletes;


    protected $fillable = [

        'income_category_id',

        'category_name_snapshot',

        'income_date',

        'amount',

        'payment_method',

        'reference',

        'notes',

        'source_type',

        'source_id',

        'created_by',

        'updated_by',

    ];


    protected $casts = [

        'income_category_id' =>
            'integer',

        'income_date' =>
            'date',

        'amount' =>
            'decimal:2',

        'source_id' =>
            'integer',

        'created_by' =>
            'integer',

        'updated_by' =>
            'integer',

    ];


    public function category(): BelongsTo
    {
        return $this->belongsTo(
            IncomeCategory::class,
            'income_category_id'
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
    | Automatic Accounting Journal
    |--------------------------------------------------------------------------
    |
    | The restaurant user records normal business data only.
    | Debit/Credit posting happens automatically in the background.
    |
    */

    protected static function booted(): void
    {
        static::created(
            function (
                self $income
            ): void {

                app(
                    AccountingSourcePostingService::class
                )->syncIncome(
                    $income,
                    null,
                    'Other Income transaction was created.'
                );
            }
        );


        static::updated(
            function (
                self $income
            ): void {

                if (
                    ! $income->wasChanged([
                        'income_category_id',
                        'income_date',
                        'amount',
                        'payment_method',
                    ])
                ) {
                    return;
                }


                app(
                    AccountingSourcePostingService::class
                )->syncIncome(
                    $income,
                    null,
                    'Other Income transaction was edited.'
                );
            }
        );


        static::deleted(
            function (
                self $income
            ): void {

                app(
                    AccountingSourcePostingService::class
                )->reverseIncome(
                    $income,
                    null,
                    'Other Income transaction was deleted.'
                );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {

        $search =
            trim(
                (string) $search
            );

        if ($search === '') {
            return $query;
        }

        return $query->where(
            function (
                Builder $searchQuery
            ) use ($search): void {

                $searchQuery
                    ->where(
                        'category_name_snapshot',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'reference',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'notes',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas(
                        'creator',
                        function (
                            Builder $userQuery
                        ) use ($search): void {

                            $userQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Date Range
    |--------------------------------------------------------------------------
    */

    public function scopeDateRange(
        Builder $query,
        ?string $fromDate,
        ?string $toDate
    ): Builder {

        if (
            $fromDate
            &&
            $toDate
        ) {
            return $query->whereBetween(
                'income_date',
                [
                    $fromDate,
                    $toDate,
                ]
            );
        }

        if ($fromDate) {
            return $query->whereDate(
                'income_date',
                '>=',
                $fromDate
            );
        }

        if ($toDate) {
            return $query->whereDate(
                'income_date',
                '<=',
                $toDate
            );
        }

        return $query;
    }


    public function isSystemGenerated(): bool
    {
        return (
            $this->source_type !== null
            &&
            $this->source_type !== ''
            &&
            $this->source_id !== null
        );
    }
}
