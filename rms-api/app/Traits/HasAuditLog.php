<?php

namespace App\Traits;


use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;



trait HasAuditLog
{


    /**
     * Create Audit Log
     */


    public function audit(

        string $module,

        string $action,

        ?Model $model = null,

        ?array $old = null,

        ?array $new = null,

        ?string $description = null

    )
    {


        return app(
            AuditLogService::class
        )
        ->log(

            $module,

            $action,

            $model,

            $old,

            $new,

            $description

        );


    }



}