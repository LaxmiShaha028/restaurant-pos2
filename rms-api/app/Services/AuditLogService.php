<?php

namespace App\Services;


use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;



class AuditLogService
{


    public function log(

        string $module,

        string $action,

        ?Model $model = null,

        ?array $oldValues = null,

        ?array $newValues = null,

        ?string $description = null

    ): AuditLog {


        return AuditLog::create([


            'user_id'=>

Auth::check()

?
Auth::id()

:
null,



            'module'=>

                $module,



            'action'=>

                $action,



            'description'=>

                $description,



            'model_type'=>

                $model
                ?
                get_class($model)
                :
                null,



            'model_id'=>

                $model?->id,



            'old_values'=>

                $oldValues,



            'new_values'=>

                $newValues,



            'ip_address'=>

                request()->ip(),



            'user_agent'=>

                request()->userAgent(),



        ]);

    }

    /*
|--------------------------------------------------------------------------
| Get Audit Logs
|--------------------------------------------------------------------------
*/

public function getLogs(
    array $filters = []
)
{

    return AuditLog::query()

        ->with([
            'user:id,name,username,email'
        ])



        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        ->when(

            $filters['search'] ?? null,

            function($query,$search){

                $query->where(function($q) use ($search){


                    $q->where(
                        'description',
                        'like',
                        "%{$search}%"
                    )


                    ->orWhere(
                        'module',
                        'like',
                        "%{$search}%"
                    )


                    ->orWhere(
                        'action',
                        'like',
                        "%{$search}%"
                    )



                    ->orWhereHas(
                        'user',
                        function($userQuery) use ($search){

                            $userQuery->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )

                            ->orWhere(
                                'username',
                                'like',
                                "%{$search}%"
                            );

                        }
                    );


                });


            }

        )





        /*
        |--------------------------------------------------------------------------
        | Module Filter
        |--------------------------------------------------------------------------
        */

        ->when(

            $filters['module'] ?? null,

            function($query,$module){

                $query->where(
                    'module',
                    $module
                );

            }

        )






        /*
        |--------------------------------------------------------------------------
        | Action Filter
        |--------------------------------------------------------------------------
        */

        ->when(

            $filters['action'] ?? null,

            function($query,$action){

                $query->where(
                    'action',
                    $action
                );

            }

        )







        /*
        |--------------------------------------------------------------------------
        | Date Filter
        |--------------------------------------------------------------------------
        */

        ->when(

            $filters['date_from'] ?? null,

            function($query,$date){

                $query->whereDate(
                    'created_at',
                    '>=',
                    $date
                );

            }

        )



        ->when(

            $filters['date_to'] ?? null,

            function($query,$date){

                $query->whereDate(
                    'created_at',
                    '<=',
                    $date
                );

            }

        )





        ->latest()



        ->paginate(

            $filters['per_page']
            ??
            20

        );

}



}