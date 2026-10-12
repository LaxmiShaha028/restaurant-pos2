<?php


namespace App\Helpers;


use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;



class AuditHelper
{


    public static function log(

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