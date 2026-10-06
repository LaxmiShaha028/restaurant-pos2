<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Validation\ValidationException;


class WarehouseStockLotMovement extends Model
{
    use HasFactory;


    /*
    |--------------------------------------------------------------------------
    | Directions
    |--------------------------------------------------------------------------
    */

    public const DIRECTION_IN =
        'in';

    public const DIRECTION_OUT =
        'out';


    /*
    |--------------------------------------------------------------------------
    | Prepared Movement Types
    |--------------------------------------------------------------------------
    */

    public const TYPE_LEGACY_OPENING =
        'legacy_opening';

    public const TYPE_PURCHASE_RECEIVE =
        'purchase_receive';

    public const TYPE_WAREHOUSE_ADJUSTMENT_IN =
        'warehouse_adjustment_in';

    public const TYPE_WAREHOUSE_ADJUSTMENT_OUT =
        'warehouse_adjustment_out';

    public const TYPE_TRANSFER_OUT =
        'transfer_out';

    public const TYPE_PURCHASE_RETURN_OUT =
        'purchase_return_out';


    protected $fillable = [

        'warehouse_stock_lot_id',

        'raw_material_id',

        'movement_type',

        'direction',

        'quantity',

        'quantity_before',

        'quantity_after',

        'unit_cost',

        'unit',

        'reference_type',

        'reference_id',

        'occurred_at',

        'notes',

        'created_by',

    ];


    protected $casts = [

        'warehouse_stock_lot_id' =>
            'integer',

        'raw_material_id' =>
            'integer',

        'quantity' =>
            'decimal:4',

        'quantity_before' =>
            'decimal:4',

        'quantity_after' =>
            'decimal:4',

        'unit_cost' =>
            'decimal:4',

        'reference_id' =>
            'integer',

        'occurred_at' =>
            'datetime',

        'created_by' =>
            'integer',

    ];


    /*
    |--------------------------------------------------------------------------
    | Immutable Audit
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(

            function (
                WarehouseStockLotMovement $movement
            ): void {

                $direction =
                    strtolower(
                        trim(
                            (string) (
                                $movement->direction
                                ??
                                ''
                            )
                        )
                    );


                if (
                    !in_array(
                        $direction,
                        [
                            self::DIRECTION_IN,
                            self::DIRECTION_OUT,
                        ],
                        true
                    )
                ) {

                    throw ValidationException::withMessages([

                        'direction' => [
                            'Warehouse lot movement direction is invalid.',
                        ],

                    ]);
                }


                $quantity =
                    round(
                        (float) (
                            $movement->quantity
                            ??
                            0
                        ),
                        4
                    );


                $before =
                    round(
                        (float) (
                            $movement->quantity_before
                            ??
                            0
                        ),
                        4
                    );


                $after =
                    round(
                        (float) (
                            $movement->quantity_after
                            ??
                            0
                        ),
                        4
                    );


                if (
                    $quantity
                    <=
                    0
                ) {

                    throw ValidationException::withMessages([

                        'quantity' => [
                            'Warehouse lot movement quantity must be greater than zero.',
                        ],

                    ]);
                }


                if (
                    $before
                    <
                    0
                    ||
                    $after
                    <
                    0
                ) {

                    throw ValidationException::withMessages([

                        'quantity' => [
                            'Warehouse lot quantity snapshot cannot be negative.',
                        ],

                    ]);
                }


                $expectedAfter =
                    $direction
                    ===
                    self::DIRECTION_IN

                        ? round(
                            $before
                            +
                            $quantity,
                            4
                        )

                        : round(
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

                        'quantity_after' => [
                            'Warehouse lot movement quantity snapshot is inconsistent.',
                        ],

                    ]);
                }


                if (
                    $after
                    <
                    0
                ) {

                    throw ValidationException::withMessages([

                        'quantity_after' => [
                            'Warehouse lot movement cannot result in negative stock.',
                        ],

                    ]);
                }


                $movement->direction =
                    $direction;

                $movement->quantity =
                    $quantity;

                $movement->quantity_before =
                    $before;

                $movement->quantity_after =
                    $after;
            }

        );


        static::updating(

            function (): void {

                throw ValidationException::withMessages([

                    'warehouse_lot_movement' => [
                        'Warehouse lot movement history cannot be modified.',
                    ],

                ]);
            }

        );


        static::deleting(

            function (): void {

                throw ValidationException::withMessages([

                    'warehouse_lot_movement' => [
                        'Warehouse lot movement history cannot be deleted.',
                    ],

                ]);
            }

        );
    }


    public function lot(): BelongsTo
    {
        return $this->belongsTo(
            WarehouseStockLot::class,
            'warehouse_stock_lot_id'
        );
    }


    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(
            RawMaterial::class,
            'raw_material_id'
        )->withTrashed();
    }


    public function reference(): MorphTo
    {
        return $this->morphTo(
            'reference'
        );
    }


    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }


    public static function allowedMovementTypes(): array
    {
        return [

            self::TYPE_LEGACY_OPENING,

            self::TYPE_PURCHASE_RECEIVE,

            self::TYPE_WAREHOUSE_ADJUSTMENT_IN,

            self::TYPE_WAREHOUSE_ADJUSTMENT_OUT,

            self::TYPE_TRANSFER_OUT,

            self::TYPE_PURCHASE_RETURN_OUT,

        ];
    }
}
