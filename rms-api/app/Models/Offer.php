<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offer extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'discount_type',
        'discount_value',
        'days_of_week',
        'start_time',
        'end_time',
        'valid_from',
        'valid_until',
        'minimum_order_amount',
        'usage_limit',
        'per_customer_limit',
        'minimum_customer_orders',
        'birthday_window_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'days_of_week' => 'array',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'minimum_order_amount' => 'decimal:2',
            'usage_limit' => 'integer',
            'per_customer_limit' => 'integer',
            'minimum_customer_orders' => 'integer',
            'birthday_window_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
