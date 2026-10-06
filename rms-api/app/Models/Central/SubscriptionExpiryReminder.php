<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class SubscriptionExpiryReminder extends Model
{
    protected $connection = 'central';

    protected $table = 'subscription_expiry_reminders';

    protected $fillable = [
        'subscription_id',
        'restaurant_id',
        'reminder_type',
        'subscription_end_at',
        'recipient_email',
        'sent_at',
    ];

    protected $casts = [
        'subscription_end_at' => 'datetime',
        'sent_at' => 'datetime',
    ];
}
