<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends CentralModel
{
    use HasFactory;
    use SoftDeletes;

     protected $connection = 'central';
    protected $table = 'subscription_plans';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'billing_interval',
        'billing_interval_count',
        'trial_days',
        'features',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' =>
                'decimal:2',

            'billing_interval_count' =>
                'integer',

            'trial_days' =>
                'integer',

            'features' =>
                'array',

            'is_active' =>
                'boolean',

            'sort_order' =>
                'integer',

            'deleted_at' =>
                'datetime',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(
            Subscription::class,
            'subscription_plan_id'
        );
    }
}