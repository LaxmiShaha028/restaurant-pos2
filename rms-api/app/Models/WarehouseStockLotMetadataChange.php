<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;


class WarehouseStockLotMetadataChange extends Model
{
    use HasFactory;


    protected $fillable = [

        'warehouse_stock_lot_id',

        'changed_fields',

        'before_snapshot',

        'after_snapshot',

        'change_reason',

        'created_by',

    ];


    protected $casts = [

        'warehouse_stock_lot_id' =>
            'integer',

        'changed_fields' =>
            'array',

        'before_snapshot' =>
            'array',

        'after_snapshot' =>
            'array',

        'created_by' =>
            'integer',

    ];


    protected static function booted(): void
    {
        static::updating(
            function (): void {

                throw ValidationException::withMessages([

                    'metadata_change' => [
                        'Warehouse lot metadata change history cannot be modified.',
                    ],

                ]);
            }
        );


        static::deleting(
            function (): void {

                throw ValidationException::withMessages([

                    'metadata_change' => [
                        'Warehouse lot metadata change history cannot be deleted.',
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


    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}
