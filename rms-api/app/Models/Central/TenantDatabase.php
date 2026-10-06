<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantDatabase extends CentralModel
{
    use HasFactory;

    protected $table = 'tenant_databases';

    protected $fillable = [
        'restaurant_id',
        'connection_name',
        'database_host',
        'database_port',
        'database_name',
        'database_username',
        'database_password',
        'status',
        'migrated_at',
        'provisioned_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'database_port' =>
                'integer',

            'database_password' =>
                'encrypted',

            'migrated_at' =>
                'datetime',

            'provisioned_at' =>
                'datetime',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(
            Restaurant::class,
            'restaurant_id'
        );
    }

    public function isReady(): bool
    {
        return $this->status === 'active';
    }
}