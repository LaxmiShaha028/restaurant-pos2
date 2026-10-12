<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;


class PurchaseReturnSettlement extends Model
{
    use HasFactory;


    public const TYPE_PAYABLE_OFFSET =
        'payable_offset';

    public const TYPE_SUPPLIER_CREDIT =
        'supplier_credit';

    public const TYPE_REFUND_RECEIVABLE =
        'refund_receivable';

    public const TYPE_DIRECT_REFUND =
        'direct_refund';

    public const TYPE_RECEIVABLE_COLLECTION =
        'receivable_collection';


    protected $fillable = [
        'settlement_no',
        'purchase_return_id',
        'supplier_id',
        'settlement_type',
        'amount',
        'purchase_order_id',
        'parent_settlement_id',
        'payment_method',
        'transaction_reference',
        'settled_at',
        'allocation_effect',
        'cash_effect',
        'notes',
        'created_by',
    ];


    protected $casts = [
        'purchase_return_id' => 'integer',
        'supplier_id' => 'integer',
        'amount' => 'decimal:2',
        'purchase_order_id' => 'integer',
        'parent_settlement_id' => 'integer',
        'settled_at' => 'datetime',
        'allocation_effect' => 'boolean',
        'cash_effect' => 'boolean',
        'created_by' => 'integer',
    ];


    protected static function booted(): void
    {
        static::updating(
            function (): void {

                throw ValidationException::withMessages([
                    'purchase_return_settlement' => [
                        'Purchase Return settlement history cannot be modified.',
                    ],
                ]);
            }
        );


        static::deleting(
            function (): void {

                throw ValidationException::withMessages([
                    'purchase_return_settlement' => [
                        'Purchase Return settlement history cannot be deleted.',
                    ],
                ]);
            }
        );
    }


    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(
            PurchaseReturn::class,
            'purchase_return_id'
        );
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


    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'parent_settlement_id'
        );
    }


    public function children(): HasMany
    {
        return $this->hasMany(
            self::class,
            'parent_settlement_id'
        )
            ->orderBy('settled_at')
            ->orderBy('id');
    }


    public function creditApplications(): HasMany
    {
        return $this->hasMany(
            SupplierCreditApplication::class,
            'source_settlement_id'
        )
            ->orderBy('applied_at')
            ->orderBy('id');
    }


    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }


    public static function allocationTypes(): array
    {
        return [
            self::TYPE_PAYABLE_OFFSET,
            self::TYPE_SUPPLIER_CREDIT,
            self::TYPE_REFUND_RECEIVABLE,
            self::TYPE_DIRECT_REFUND,
        ];
    }


    public static function refundMethods(): array
    {
        return [
            'cash',
            'bank_transfer',
            'bkash',
            'nagad',
            'card',
            'other',
        ];
    }


    public static function typeLabel(
        ?string $type
    ): string {

        return match ($type) {
            self::TYPE_PAYABLE_OFFSET =>
                'Reduce Supplier Payable',

            self::TYPE_SUPPLIER_CREDIT =>
                'Supplier Credit',

            self::TYPE_REFUND_RECEIVABLE =>
                'Refund Receivable',

            self::TYPE_DIRECT_REFUND =>
                'Refund Received Now',

            self::TYPE_RECEIVABLE_COLLECTION =>
                'Refund Collection',

            default =>
                'Unknown',
        };
    }
}
