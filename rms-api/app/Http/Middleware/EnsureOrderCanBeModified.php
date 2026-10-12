<?php

namespace App\Http\Middleware;

use App\Models\Order;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrderCanBeModified
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
        | Full Order Edit Permission
        |--------------------------------------------------------------------------
        |
        | orders.update may edit an active non-finalized order through the normal
        | Edit Order workflow.
        |
        */

        if (
            $user->hasPermission(
                'orders.update'
            )
        ) {

            return $next(
                $request
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Operational Served-Order Permission
        |--------------------------------------------------------------------------
        |
        | Existing RMS behavior intentionally allows an orders.create user to:
        |
        | - Add More Items to a SERVED order
        | - add/settle payment on a SERVED order
        |
        | Those flows use the same edit-options / update endpoints.
        |
        | orders.create must NOT become a general edit permission for pending,
        | preparing or ready orders.
        |
        */

        if (
            ! $user->hasPermission(
                'orders.create'
            )
        ) {

            return $this->forbidden();
        }


        $routeOrder =
            $request->route(
                'order'
            );


        if (
            $routeOrder instanceof Order
        ) {

            $order =
                $routeOrder;

        }
        else {

            $orderId =
                (int) $routeOrder;


            if (
                $orderId <= 0
            ) {

                return $this->forbidden();
            }


            $order =
                Order::query()
                    ->find(
                        $orderId
                    );
        }


        if (
            ! $order
            ||
            $order->status !==
                Order::STATUS_SERVED
        ) {

            return $this->forbidden(
                'You may only use the operational order update flow for a served order.'
            );
        }


        return $next(
            $request
        );
    }


    private function forbidden(
        string $message =
            'You do not have permission to modify this order.'
    ): Response {

        return response()->json([
            'success' =>
                false,

            'message' =>
                $message,

            'data' => [
                'required_permissions' => [
                    'orders.update',
                    'orders.create (served-order operational flow only)',
                ],
            ],

            'errors' =>
                null,
        ], JsonResponse::HTTP_FORBIDDEN);
    }
}
