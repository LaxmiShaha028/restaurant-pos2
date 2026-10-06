<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;


class PurchaseReturnItem extends Model
{
    use HasFactory;

    public const REASON_NEAR_EXPIRY = 'near_expiry';
    public const REASON_EXPIRED = 'expired';
    public const REASON_QUALITY_REJECTED = 'quality_rejected';
    public const REASON_DAMAGED_PACKAGING = 'damaged_packaging';
    public const REASON_EXCESS_STOCK = 'excess_stock';
    public const REASON_WRONG_ITEM = 'wrong_item';
    public const REASON_SUPPLIER_RECALL = 'supplier_recall';
    public const REASON_OTHER = 'other';

    protected $fillable = [
        'purchase_return_id',
        'warehouse_stock_lot_id',
        'raw_material_id',
        'purchase_order_id',
        'purchase_order_receipt_id',
        'item_name',
        'unit',
        'lot_no',
        'supplier_batch_no',
        'manufactured_at',
        'expiry_date',
        'quantity',
        'lot_quantity_before',
        'lot_quantity_after',
        'warehouse_quantity_before',
        'warehouse_quantity_after',
        'return_unit_cost',
        'expected_supplier_credit',
        'inventory_average_unit_cost',
        'inventory_value_reduction',
        'valuation_variance',
        'reason',
        'reason_notes',
    ];

    protected $casts = [
        'purchase_return_id' => 'integer',
        'warehouse_stock_lot_id' => 'integer',
        'raw_material_id' => 'integer',
        'purchase_order_id' => 'integer',
        'purchase_order_receipt_id' => 'integer',
        'manufactured_at' => 'date',
        'expiry_date' => 'date',
        'quantity' => 'decimal:4',
        'lot_quantity_before' => 'decimal:4',
        'lot_quantity_after' => 'decimal:4',
        'warehouse_quantity_before' => 'decimal:4',
        'warehouse_quantity_after' => 'decimal:4',
        'return_unit_cost' => 'decimal:4',
        'expected_supplier_credit' => 'decimal:4',
        'inventory_average_unit_cost' => 'decimal:4',
        'inventory_value_reduction' => 'decimal:4',
        'valuation_variance' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw ValidationException::withMessages([
                'purchase_return_item' => [
                    'Purchase return item history cannot be modified.',
                ],
            ]);
        });

        static::deleting(function (): void {
            throw ValidationException::withMessages([
                'purchase_return_item' => [
                    'Purchase return item history cannot be deleted.',
                ],
            ]);
        });
    }

    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class, 'purchase_return_id');
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(WarehouseStockLot::class, 'warehouse_stock_lot_id');
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id')->withTrashed();
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function purchaseOrderReceipt(): BelongsTo
    {
        return $this->belongsTo(
            PurchaseOrderReceipt::class,
            'purchase_order_receipt_id'
        );
    }

    public static function allowedReasons(): array
    {
        return [
            self::REASON_NEAR_EXPIRY,
            self::REASON_EXPIRED,
            self::REASON_QUALITY_REJECTED,
            self::REASON_DAMAGED_PACKAGING,
            self::REASON_EXCESS_STOCK,
            self::REASON_WRONG_ITEM,
            self::REASON_SUPPLIER_RECALL,
            self::REASON_OTHER,
        ];
    }

    public static function reasonLabel(?string $reason): string
    {
        return match ($reason) {
            self::REASON_NEAR_EXPIRY => 'Near Expiry',
            self::REASON_EXPIRED => 'Expired',
            self::REASON_QUALITY_REJECTED => 'Quality Rejected',
            self::REASON_DAMAGED_PACKAGING => 'Damaged Packaging',
            self::REASON_EXCESS_STOCK => 'Excess Stock',
            self::REASON_WRONG_ITEM => 'Wrong Item',
            self::REASON_SUPPLIER_RECALL => 'Supplier Recall',
            self::REASON_OTHER => 'Other',
            default => 'Unknown',
        };
    }
}
