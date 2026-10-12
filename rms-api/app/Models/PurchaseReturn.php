<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;


class PurchaseReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_no',
        'supplier_id',
        'supplier_name',
        'supplier_phone',
        'supplier_email',
        'supplier_address',
        'returned_at',
        'total_items',
        'expected_supplier_credit',
        'inventory_value_reduction',
        'valuation_variance',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'supplier_id' => 'integer',
        'returned_at' => 'datetime',
        'total_items' => 'integer',
        'expected_supplier_credit' => 'decimal:4',
        'inventory_value_reduction' => 'decimal:4',
        'valuation_variance' => 'decimal:4',
        'created_by' => 'integer',
    ];

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw ValidationException::withMessages([
                'purchase_return' => [
                    'Purchase return history cannot be modified.',
                ],
            ]);
        });

        static::deleting(function (): void {
            throw ValidationException::withMessages([
                'purchase_return' => [
                    'Purchase return history cannot be deleted.',
                ],
            ]);
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id')->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class, 'purchase_return_id')
            ->orderBy('id');
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(
            PurchaseReturnSettlement::class,
            'purchase_return_id'
        )
            ->orderBy('settled_at')
            ->orderBy('id');
    }


    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
