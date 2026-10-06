<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;


class SupplierCreditApplication extends Model
{
    use HasFactory;


    protected $fillable = [
        'application_no',
        'source_settlement_id',
        'supplier_id',
        'purchase_order_id',
        'amount',
        'applied_at',
        'notes',
        'created_by',
    ];


    protected $casts = [
        'source_settlement_id' => 'integer',
        'supplier_id' => 'integer',
        'purchase_order_id' => 'integer',
        'amount' => 'decimal:2',
        'applied_at' => 'datetime',
        'created_by' => 'integer',
    ];


    protected static function booted(): void
    {
        static::updating(
            function (): void {

                throw ValidationException::withMessages([
                    'supplier_credit_application' => [
                        'Supplier credit application history cannot be modified.',
                    ],
                ]);
            }
        );


        static::deleting(
            function (): void {

                throw ValidationException::withMessages([
                    'supplier_credit_application' => [
                        'Supplier credit application history cannot be deleted.',
                    ],
                ]);
            }
        );
    }


    public function sourceSettlement(): BelongsTo
    {
        return $this->belongsTo(
            PurchaseReturnSettlement::class,
            'source_settlement_id'
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


    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}
