<?php

namespace App\Models\Central;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class CentralPersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $connection = 'central';

    protected $table = 'personal_access_tokens';

    protected $guarded = [];
}