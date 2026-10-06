<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;


class InventoryWastage extends Model
{
    use HasFactory;


    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'wastage_no',

        'wasted_at',

        'total_loss',

        'notes',

        'recorded_by',

        'created_by',

    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'wasted_at' =>
            'datetime',

        'total_loss' =>
            'decimal:4',

        'recorded_by' =>
            'integer',

        'created_by' =>
            'integer',

    ];


    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    |
    | Wastage is an immutable inventory/accounting source document.
    |
    | Wrong entries should later be reversed through a separate reversal
    | workflow instead of editing/deleting historical records.
    |
    */

    protected static function booted(): void
    {
        static::creating(

            function (
                InventoryWastage $wastage
            ): void {

                $wastageNo =
                    trim(
                        (string) (
                            $wastage->wastage_no
                            ?? ''
                        )
                    );


                if ($wastageNo === '') {

                    throw ValidationException::withMessages([

                        'wastage_no' => [
                            'Wastage number is required.',
                        ],

                    ]);
                }


                $wastage->wastage_no =
                    $wastageNo;


                if (
                    $wastage->wasted_at
                    ===
                    null
                ) {

                    throw ValidationException::withMessages([

                        'wasted_at' => [
                            'Wastage date and time are required.',
                        ],

                    ]);
                }


                $totalLoss =
                    round(
                        (float) (
                            $wastage->total_loss
                            ?? 0
                        ),
                        4
                    );


                if ($totalLoss < 0) {

                    throw ValidationException::withMessages([

                        'total_loss' => [
                            'Wastage loss cannot be negative.',
                        ],

                    ]);
                }


                $wastage->total_loss =
                    $totalLoss;


                if (
                    $wastage->notes
                    !==
                    null
                ) {

                    $notes =
                        trim(
                            (string) $wastage->notes
                        );


                    $wastage->notes =
                        $notes !== ''
                            ? $notes
                            : null;
                }
            }

        );


        static::updating(

            function (): void {

                throw ValidationException::withMessages([

                    'wastage' => [
                        'Wastage history cannot be modified.',
                    ],

                ]);
            }

        );


        static::deleting(

            function (): void {

                throw ValidationException::withMessages([

                    'wastage' => [
                        'Wastage history cannot be deleted.',
                    ],

                ]);
            }

        );
    }


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function items(): HasMany
    {
        return $this->hasMany(
            InventoryWastageItem::class,
            'inventory_wastage_id'
        );
    }


    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'recorded_by'
        );
    }


    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Accounting Helpers
    |--------------------------------------------------------------------------
    */

    public function totalLoss(): float
    {
        return round(
            (float) (
                $this->total_loss
                ?? 0
            ),
            4
        );
    }


    public function accountingClassification(): string
    {
        return 'inventory_wastage_loss';
    }


    public function hasCashEffect(): bool
    {
        return false;
    }
}
