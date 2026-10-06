<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInventoryAccess
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user =
            $request->user();


        if (! $user) {

            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Unauthenticated.',

                'data' =>
                    null,

                'errors' =>
                    null,
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }


        $user->loadMissing(
            'role.permissions'
        );


        /*
        |--------------------------------------------------------------------------
        | Read Requests
        |--------------------------------------------------------------------------
        |
        | inventory.view  OR  inventory.manage
        |
        */

        if (
            $request->isMethodSafe()
        ) {

            if (
                $user->hasPermission(
                    'inventory.view'
                )
                ||
                $user->hasPermission(
                    'inventory.manage'
                )
            ) {

                return $next(
                    $request
                );
            }


            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'You do not have permission to view inventory information.',

                'data' => [
                    'required_permissions' => [
                        'inventory.view',
                        'inventory.manage',
                    ],
                ],

                'errors' =>
                    null,
            ], JsonResponse::HTTP_FORBIDDEN);
        }


        /*
        |--------------------------------------------------------------------------
        | Write Requests
        |--------------------------------------------------------------------------
        |
        | POST / PUT / PATCH / DELETE require inventory.manage.
        |
        */

        if (
            ! $user->hasPermission(
                'inventory.manage'
            )
        ) {

            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'You do not have permission to modify inventory.',

                'data' => [
                    'required_permissions' => [
                        'inventory.manage',
                    ],
                ],

                'errors' =>
                    null,
            ], JsonResponse::HTTP_FORBIDDEN);
        }


        return $next(
            $request
        );
    }
}
