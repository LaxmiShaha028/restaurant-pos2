<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;


class InventoryWastageItem extends Model
{
    use HasFactory;


    /*
    |--------------------------------------------------------------------------
    | Reason Constants
    |--------------------------------------------------------------------------
    */

    public const REASON_SPOILED =
        'spoiled';

    public const REASON_EXPIRED =
        'expired';

    public const REASON_SPILLAGE =
        'spillage';

    public const REASON_BURNT_OVERCOOKED =
        'burnt_overcooked';

    public const REASON_CONTAMINATED =
        'contaminated';

    public const REASON_DAMAGED_PACKAGING =
        'damaged_packaging';

    public const REASON_QUALITY_REJECTED =
        'quality_rejected';

    public const REASON_STAFF_HANDLING_LOSS =
        'staff_handling_loss';

    public const REASON_OTHER =
        'other';


    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'inventory_wastage_id',

        'raw_material_id',

        'item_name',

        'unit',

        'quantity',

        'unit_cost',

        'total_loss',

        'restaurant_quantity_before',

        'restaurant_quantity_after',

        'reason',

        'notes',

    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'inventory_wastage_id' =>
            'integer',

        'raw_material_id' =>
            'integer',

        'quantity' =>
            'decimal:4',

        'unit_cost' =>
            'decimal:4',

        'total_loss' =>
            'decimal:4',

        'restaurant_quantity_before' =>
            'decimal:4',

        'restaurant_quantity_after' =>
            'decimal:4',

    ];


    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(

            function (
                InventoryWastageItem $item
            ): void {

                $itemName =
                    trim(
                        (string) (
                            $item->item_name
                            ?? ''
                        )
                    );


                if ($itemName === '') {

                    throw ValidationException::withMessages([

                        'item_name' => [
                            'Wastage item name is required.',
                        ],

                    ]);
                }


                $item->item_name =
                    $itemName;


                $unit =
                    strtolower(
                        trim(
                            (string) (
                                $item->unit
                                ?? ''
                            )
                        )
                    );


                if (
                    !in_array(
                        $unit,
                        RawMaterial::allowedUnits(),
                        true
                    )
                ) {

                    throw ValidationException::withMessages([

                        'unit' => [
                            'The wastage unit is invalid.',
                        ],

                    ]);
                }


                $item->unit =
                    $unit;


                $reason =
                    strtolower(
                        trim(
                            (string) (
                                $item->reason
                                ?? ''
                            )
                        )
                    );


                if (
                    !in_array(
                        $reason,
                        self::allowedReasons(),
                        true
                    )
                ) {

                    throw ValidationException::withMessages([

                        'reason' => [
                            'The selected wastage reason is invalid.',
                        ],

                    ]);
                }


                $item->reason =
                    $reason;


                $quantity =
                    round(
                        (float) (
                            $item->quantity
                            ?? 0
                        ),
                        4
                    );


                if ($quantity <= 0) {

                    throw ValidationException::withMessages([

                        'quantity' => [
                            'Wastage quantity must be greater than zero.',
                        ],

                    ]);
                }


                $unitCost =
                    round(
                        (float) (
                            $item->unit_cost
                            ?? 0
                        ),
                        4
                    );


                if ($unitCost < 0) {

                    throw ValidationException::withMessages([

                        'unit_cost' => [
                            'Wastage unit cost cannot be negative.',
                        ],

                    ]);
                }


                $before =
                    round(
                        (float) (
                            $item->restaurant_quantity_before
                            ?? 0
                        ),
                        4
                    );


                $after =
                    round(
                        (float) (
                            $item->restaurant_quantity_after
                            ?? 0
                        ),
                        4
                    );


                if (
                    $before < 0
                    ||
                    $after < 0
                ) {

                    throw ValidationException::withMessages([

                        'quantity' => [
                            'Restaurant stock snapshot cannot be negative.',
                        ],

                    ]);
                }


                $expectedAfter =
                    round(
                        $before
                        -
                        $quantity,
                        4
                    );


                if (
                    abs(
                        $expectedAfter
                        -
                        $after
                    )
                    >
                    0.00005
                ) {

                    throw ValidationException::withMessages([

                        'restaurant_quantity_after' => [

                            sprintf(
                                'Restaurant stock snapshot mismatch. Expected quantity after wastage is %.4f.',
                                $expectedAfter
                            ),

                        ],

                    ]);
                }


                $totalLoss =
                    round(
                        $quantity
                        *
                        $unitCost,
                        4
                    );


                $submittedLoss =
                    round(
                        (float) (
                            $item->total_loss
                            ?? $totalLoss
                        ),
                        4
                    );


                if (
                    abs(
                        $submittedLoss
                        -
                        $totalLoss
                    )
                    >
                    0.00005
                ) {

                    throw ValidationException::withMessages([

                        'total_loss' => [
                            'Wastage loss snapshot does not match quantity × unit cost.',
                        ],

                    ]);
                }


                $item->quantity =
                    $quantity;

                $item->unit_cost =
                    $unitCost;

                $item->total_loss =
                    $totalLoss;

                $item->restaurant_quantity_before =
                    $before;

                $item->restaurant_quantity_after =
                    $after;


                if (
                    $item->notes
                    !==
                    null
                ) {

                    $notes =
                        trim(
                            (string) $item->notes
                        );


                    $item->notes =
                        $notes !== ''
                            ? $notes
                            : null;
                }
            }

        );


        static::updating(

            function (): void {

                throw ValidationException::withMessages([

                    'wastage_item' => [
                        'Wastage item history cannot be modified.',
                    ],

                ]);
            }

        );


        static::deleting(

            function (): void {

                throw ValidationException::withMessages([

                    'wastage_item' => [
                        'Wastage item history cannot be deleted.',
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

    public function wastage(): BelongsTo
    {
        return $this->belongsTo(
            InventoryWastage::class,
            'inventory_wastage_id'
        );
    }


    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(
            RawMaterial::class,
            'raw_material_id'
        )->withTrashed();
    }


    /*
    |--------------------------------------------------------------------------
    | Reason Helpers
    |--------------------------------------------------------------------------
    */

    public static function allowedReasons(): array
    {
        return [

            self::REASON_SPOILED,

            self::REASON_EXPIRED,

            self::REASON_SPILLAGE,

            self::REASON_BURNT_OVERCOOKED,

            self::REASON_CONTAMINATED,

            self::REASON_DAMAGED_PACKAGING,

            self::REASON_QUALITY_REJECTED,

            self::REASON_STAFF_HANDLING_LOSS,

            self::REASON_OTHER,

        ];
    }


    public function getReasonLabelAttribute(): string
    {
        return match (
            $this->reason
        ) {

            self::REASON_SPOILED =>
                'Spoiled',

            self::REASON_EXPIRED =>
                'Expired',

            self::REASON_SPILLAGE =>
                'Spillage',

            self::REASON_BURNT_OVERCOOKED =>
                'Burnt / Overcooked',

            self::REASON_CONTAMINATED =>
                'Contaminated',

            self::REASON_DAMAGED_PACKAGING =>
                'Damaged Packaging',

            self::REASON_QUALITY_REJECTED =>
                'Quality Rejected',

            self::REASON_STAFF_HANDLING_LOSS =>
                'Staff Handling Loss',

            self::REASON_OTHER =>
                'Other',

            default =>
                ucwords(
                    str_replace(
                        '_',
                        ' ',
                        (string) $this->reason
                    )
                ),

        };
    }
}
