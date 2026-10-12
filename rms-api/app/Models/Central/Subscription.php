<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends CentralModel
{
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'subscriptions';

    protected $fillable = [
        'restaurant_id',
        'subscription_plan_id',
        'status',
        'start_at',
        'end_at',
        'trial_start_at',
        'trial_end_at',
        'auto_renew',
        'cancelled_at',
        'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'trial_start_at' => 'datetime',
            'trial_end_at' => 'datetime',
            'auto_renew' => 'boolean',
            'cancelled_at' => 'datetime',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(
            Restaurant::class,
            'restaurant_id'
        );
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(
            SubscriptionPlan::class,
            'subscription_plan_id'
        );
    }

    public function isActive(): bool
    {
        return (
            $this->status === 'active'
            &&
            ! $this->isExpired()
        );
    }

    public function isTrial(): bool
    {
        return (
            $this->status === 'active'
            &&
            $this->trial_end_at !== null
            &&
            $this->trial_end_at->isFuture()
        );
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isExpired(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        if ($this->end_at === null) {
            return false;
        }

        return $this->end_at->lte(now());
    }
}
