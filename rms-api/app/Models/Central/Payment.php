<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends CentralModel
{
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'payments';

    protected $fillable = [
        'restaurant_id',
        'subscription_id',
        'gateway',
        'transaction_id',
        'reference',
        'amount',
        'currency',
        'payment_method',
        'status',
        'paid_at',
        'gateway_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' =>
                'decimal:2',

            'paid_at' =>
                'datetime',

            'gateway_response' =>
                'array',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(
            Restaurant::class,
            'restaurant_id'
        );
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(
            Subscription::class,
            'subscription_id'
        );
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'paid';
    }
}