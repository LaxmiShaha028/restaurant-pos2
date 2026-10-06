<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class SuperAdmin extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $connection = 'central';

    protected $table = 'super_admins';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_active',
        'last_login_at',
        'last_logout_at',
        'failed_login_attempts',
        'blocked_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' =>
                'hashed',

            'is_active' =>
                'boolean',

            'last_login_at' =>
                'datetime',

            'last_logout_at' =>
                'datetime',

            'failed_login_attempts' =>
                'integer',

            'blocked_at' =>
                'datetime',

            'deleted_at' =>
                'datetime',
        ];
    }
}