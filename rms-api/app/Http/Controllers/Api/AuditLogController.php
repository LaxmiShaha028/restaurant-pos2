<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;



class AuditLogController extends Controller
{


    public function __construct(

        private readonly AuditLogService $service

    )
    {

    }






    /*
    |--------------------------------------------------------------------------
    | List Logs
    |--------------------------------------------------------------------------
    */


    public function index(
        Request $request
    ): JsonResponse {


        $logs =

            $this->service->getLogs(

                $request->all()

            );



        return response()->json([


            'success'=>true,


            'message'=>
                'Audit logs retrieved successfully.',



            'data'=>

                AuditLogResource::collection(
                    $logs
                ),



            'meta'=>[


                'current_page'=>
                    $logs->currentPage(),


                'last_page'=>
                    $logs->lastPage(),


                'per_page'=>
                    $logs->perPage(),


                'total'=>
                    $logs->total(),


            ]


        ]);


    }








    /*
    |--------------------------------------------------------------------------
    | Single Log
    |--------------------------------------------------------------------------
    */


    public function show(
        AuditLog $auditLog
    ): JsonResponse {


        return response()->json([


            'success'=>true,


            'data'=>

                new AuditLogResource(

                    $auditLog->load(
                        'user'
                    )

                )


        ]);


    }



}