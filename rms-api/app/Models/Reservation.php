<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    use HasFactory;
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Statuses
    |--------------------------------------------------------------------------
    */

    public const STATUS_RESERVED = 'reserved';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_SEATED = 'seated';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'reservation_no',
        'customer_id',
        'reservation_date',
        'start_time',
        'end_time',
        'guest_count',
        'advance_amount',
        'status',
        'note',
        'created_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'reservation_date' => 'date',

            'start_time' => 'datetime',

            'end_time' => 'datetime',

            'advance_amount' => 'decimal:2',

            'guest_count' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            Customer::class
        );
    }

    public function tables(): BelongsToMany
    {
        return $this->belongsToMany(
            RestaurantTable::class,
            'reservation_tables'
        )->withTimestamps();
    }
    public function orders(): HasMany
{
    return $this->hasMany(
        Order::class,
        'reservation_id'
    );
}

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public static function activeStatuses(): array
    {
        return [
            self::STATUS_RESERVED,
            self::STATUS_CONFIRMED,
            self::STATUS_SEATED,
        ];
    }
    public function isSeated(): bool
{
    return $this->status ===
        self::STATUS_SEATED;
}
public function isFinalized(): bool
{
    return in_array(
        $this->status,
        [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ],
        true
    );
}

    public function isCancelled(): bool
    {
        return $this->status ===
            self::STATUS_CANCELLED;
    }

    public function isCompleted(): bool
    {
        return $this->status ===
            self::STATUS_COMPLETED;
    }

    public function isActive(): bool
    {
        return in_array(
            $this->status,
            self::activeStatuses(),
            true
        );
    }
    public function canBeCompleted(): bool
{
    return $this->status ===
        self::STATUS_SEATED;
}
public function markCompleted(): bool
{
    return $this->update([
        'status' =>
            self::STATUS_COMPLETED,
    ]);
}
}