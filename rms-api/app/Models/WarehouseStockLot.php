<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;


class WarehouseStockLot extends Model
{
    use HasFactory;


    /*
    |--------------------------------------------------------------------------
    | Source Types
    |--------------------------------------------------------------------------
    */

    public const SOURCE_LEGACY_OPENING =
        'legacy_opening';

    public const SOURCE_PURCHASE_RECEIVE =
        'purchase_receive';

    public const SOURCE_WAREHOUSE_ADJUSTMENT_IN =
        'warehouse_adjustment_in';


    /*
    |--------------------------------------------------------------------------
    | Expiry Statuses
    |--------------------------------------------------------------------------
    */

    public const STATUS_HEALTHY =
        'healthy';

    public const STATUS_EXPIRING_SOON =
        'expiring_soon';

    public const STATUS_CRITICAL =
        'critical';

    public const STATUS_EXPIRED =
        'expired';

    public const STATUS_DEPLETED =
        'depleted';

    public const STATUS_UNKNOWN_EXPIRY =
        'unknown_expiry';


    public const DEFAULT_EXPIRING_SOON_DAYS =
        30;

    public const DEFAULT_CRITICAL_DAYS =
        7;


    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'lot_no',

        'raw_material_id',

        'source_type',

        'supplier_id',

        'purchase_order_id',

        'purchase_order_receipt_id',

        'purchase_order_receipt_item_id',

        'supplier_batch_no',

        'manufactured_at',

        'expiry_date',

        'received_quantity',

        'remaining_quantity',

        'unit_cost',

        'unit',

        'received_at',

        'notes',

        'created_by',

        'updated_by',

    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'raw_material_id' =>
            'integer',

        'supplier_id' =>
            'integer',

        'purchase_order_id' =>
            'integer',

        'purchase_order_receipt_id' =>
            'integer',

        'purchase_order_receipt_item_id' =>
            'integer',

        'manufactured_at' =>
            'date',

        'expiry_date' =>
            'date',

        'received_quantity' =>
            'decimal:4',

        'remaining_quantity' =>
            'decimal:4',

        'unit_cost' =>
            'decimal:4',

        'received_at' =>
            'datetime',

        'created_by' =>
            'integer',

        'updated_by' =>
            'integer',

    ];


    /*
    |--------------------------------------------------------------------------
    | Appends
    |--------------------------------------------------------------------------
    */

    protected $appends = [

        'status',

        'status_label',

        'days_until_expiry',

        'remaining_value',

        'quantity_formatted',

    ];


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saving(

            function (
                WarehouseStockLot $lot
            ): void {

                $receivedQuantity =
                    round(
                        (float) (
                            $lot->received_quantity
                            ??
                            0
                        ),
                        4
                    );


                $remainingQuantity =
                    round(
                        (float) (
                            $lot->remaining_quantity
                            ??
                            0
                        ),
                        4
                    );


                $unitCost =
                    round(
                        (float) (
                            $lot->unit_cost
                            ??
                            0
                        ),
                        4
                    );


                if (
                    $receivedQuantity
                    <=
                    0
                ) {

                    throw ValidationException::withMessages([

                        'received_quantity' => [
                            'Warehouse lot received quantity must be greater than zero.',
                        ],

                    ]);
                }


                if (
                    $remainingQuantity
                    <
                    0
                ) {

                    throw ValidationException::withMessages([

                        'remaining_quantity' => [
                            'Warehouse lot remaining quantity cannot be negative.',
                        ],

                    ]);
                }


                if (
                    $remainingQuantity
                    >
                    $receivedQuantity
                ) {

                    throw ValidationException::withMessages([

                        'remaining_quantity' => [
                            'Warehouse lot remaining quantity cannot exceed received quantity.',
                        ],

                    ]);
                }


                if (
                    $unitCost
                    <
                    0
                ) {

                    throw ValidationException::withMessages([

                        'unit_cost' => [
                            'Warehouse lot unit cost cannot be negative.',
                        ],

                    ]);
                }


                $unit =
                    strtolower(
                        trim(
                            (string) (
                                $lot->unit
                                ??
                                ''
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
                            'Warehouse lot unit is invalid.',
                        ],

                    ]);
                }


                if (
                    $lot->manufactured_at
                    &&
                    $lot->expiry_date
                    &&
                    $lot->expiry_date
                        ->lt(
                            $lot->manufactured_at
                        )
                ) {

                    throw ValidationException::withMessages([

                        'expiry_date' => [
                            'Expiry date cannot be earlier than manufacturing date.',
                        ],

                    ]);
                }


                $lot->received_quantity =
                    $receivedQuantity;

                $lot->remaining_quantity =
                    $remainingQuantity;

                $lot->unit_cost =
                    $unitCost;

                $lot->unit =
                    $unit;


                if (
                    $lot->supplier_batch_no
                    !==
                    null
                ) {

                    $batchNo =
                        trim(
                            (string) $lot
                                ->supplier_batch_no
                        );


                    $lot->supplier_batch_no =
                        $batchNo !== ''
                            ? $batchNo
                            : null;
                }


                if (
                    $lot->notes
                    !==
                    null
                ) {

                    $notes =
                        trim(
                            (string) $lot
                                ->notes
                        );


                    $lot->notes =
                        $notes !== ''
                            ? $notes
                            : null;
                }
            }

        );
    }


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(
            RawMaterial::class,
            'raw_material_id'
        )->withTrashed();
    }


    public function supplier(): BelongsTo
    {
        return $this->belongsTo(
            Supplier::class,
            'supplier_id'
        )->withTrashed();
    }


    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(
            PurchaseOrder::class,
            'purchase_order_id'
        );
    }


    public function purchaseOrderReceipt(): BelongsTo
    {
        return $this->belongsTo(
            PurchaseOrderReceipt::class,
            'purchase_order_receipt_id'
        );
    }


    public function purchaseOrderReceiptItem(): BelongsTo
    {
        return $this->belongsTo(
            PurchaseOrderReceiptItem::class,
            'purchase_order_receipt_item_id'
        );
    }


    public function movements(): HasMany
    {
        return $this->hasMany(
            WarehouseStockLotMovement::class,
            'warehouse_stock_lot_id'
        )
            ->orderBy(
                'occurred_at'
            )
            ->orderBy(
                'id'
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
    | Quantity / Value
    |--------------------------------------------------------------------------
    */

    public function remainingQuantity(): float
    {
        return round(
            (float) (
                $this->remaining_quantity
                ??
                0
            ),
            4
        );
    }


    public function remainingValue(): float
    {
        return round(
            $this->remainingQuantity()
            *
            (float) $this->unit_cost,
            4
        );
    }


    public function getRemainingValueAttribute(): float
    {
        return $this->remainingValue();
    }


    public function getQuantityFormattedAttribute(): string
    {
        $quantity =
            number_format(
                $this->remainingQuantity(),
                4,
                '.',
                ''
            );


        $quantity =
            rtrim(
                rtrim(
                    $quantity,
                    '0'
                ),
                '.'
            );


        return trim(
            "{$quantity} {$this->unit}"
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Expiry
    |--------------------------------------------------------------------------
    */

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (
            !$this->expiry_date
        ) {
            return null;
        }


        $today =
            now()
                ->startOfDay();


        $expiry =
            $this->expiry_date
                ->copy()
                ->startOfDay();


        if (
            $expiry
                ->lt(
                    $today
                )
        ) {

            return -1
                *
                $expiry
                    ->diffInDays(
                        $today
                    );
        }


        return $today
            ->diffInDays(
                $expiry
            );
    }


    public function getStatusAttribute(): string
    {
        if (
            $this->remainingQuantity()
            <=
            0
        ) {

            return self::STATUS_DEPLETED;
        }


        if (
            !$this->expiry_date
        ) {

            return self::STATUS_UNKNOWN_EXPIRY;
        }


        $days =
            $this->days_until_expiry;


        if (
            $days
            <
            0
        ) {

            return self::STATUS_EXPIRED;
        }


        if (
            $days
            <=
            self::DEFAULT_CRITICAL_DAYS
        ) {

            return self::STATUS_CRITICAL;
        }


        if (
            $days
            <=
            self::DEFAULT_EXPIRING_SOON_DAYS
        ) {

            return self::STATUS_EXPIRING_SOON;
        }


        return self::STATUS_HEALTHY;
    }


    public function getStatusLabelAttribute(): string
    {
        return match (
            $this->status
        ) {

            self::STATUS_HEALTHY =>
                'Healthy',

            self::STATUS_EXPIRING_SOON =>
                'Expiring Soon',

            self::STATUS_CRITICAL =>
                'Critical',

            self::STATUS_EXPIRED =>
                'Expired',

            self::STATUS_DEPLETED =>
                'Depleted',

            self::STATUS_UNKNOWN_EXPIRY =>
                'Expiry Unknown',

            default =>
                'Unknown',

        };
    }


    /*
    |--------------------------------------------------------------------------
    | FEFO Helpers
    |--------------------------------------------------------------------------
    */

    public function isExpired(): bool
    {
        return
            $this->status
            ===
            self::STATUS_EXPIRED;
    }


    public function isDepleted(): bool
    {
        return
            $this->status
            ===
            self::STATUS_DEPLETED;
    }


    public function isUsableForFefo(): bool
    {
        return
            !$this->isExpired()
            &&
            !$this->isDepleted();
    }


    public static function allowedSourceTypes(): array
    {
        return [

            self::SOURCE_LEGACY_OPENING,

            self::SOURCE_PURCHASE_RECEIVE,

            self::SOURCE_WAREHOUSE_ADJUSTMENT_IN,

        ];
    }
}
