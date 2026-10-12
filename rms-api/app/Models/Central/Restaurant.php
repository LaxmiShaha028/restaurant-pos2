<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restaurant extends CentralModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'restaurants';

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'owner_name',
        'owner_email',
        'phone',
        'address',
        'city',
        'country',
        'tenant_key',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' =>
                'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Tenant Database
    |--------------------------------------------------------------------------
    */

    public function tenantDatabase(): HasOne
    {
        return $this->hasOne(
            TenantDatabase::class,
            'restaurant_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Subscriptions
    |--------------------------------------------------------------------------
    */

    public function subscriptions(): HasMany
    {
        return $this->hasMany(
            Subscription::class,
            'restaurant_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Domains
    |--------------------------------------------------------------------------
    */

    public function domains(): HasMany
    {
        return $this->hasMany(
            Domain::class,
            'restaurant_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */

    public function payments(): HasMany
    {
        return $this->hasMany(
            Payment::class,
            'restaurant_id'
        );
    }
}