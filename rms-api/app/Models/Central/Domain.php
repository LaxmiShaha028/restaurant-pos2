<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Domain extends CentralModel
{
    use HasFactory;

    protected $table = 'domains';

    protected $fillable = [
        'restaurant_id',
        'domain',
        'type',
        'is_primary',
        'is_verified',
        'verification_token',
        'ssl_status',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' =>
                'boolean',

            'is_verified' =>
                'boolean',

        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(
            Restaurant::class,
            'restaurant_id'
        );
    }

    public function isCustom(): bool
    {
        return $this->type === 'custom';
    }

    public function isSubdomain(): bool
    {
        return $this->type === 'subdomain';
    }

    public function isVerified(): bool
    {
        return (bool) $this->is_verified;
    }
}