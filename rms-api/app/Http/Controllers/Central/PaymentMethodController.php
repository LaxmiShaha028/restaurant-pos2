<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\PaymentMethod;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    |
    | Return all active payment methods available for Super Admin
    | restaurant subscription payments.
    |
    */

    public function index(): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Fetch Active Payment Methods
        |--------------------------------------------------------------------------
        */

        $paymentMethods =
            PaymentMethod::query()
                ->where(
                    'status',
                    true
                )
                ->orderBy(
                    'name'
                )
                ->get([
                    'id',
                    'name',
                    'code',
                    'account_number',
                    'instructions',
                    'status',
                ]);

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Payment methods retrieved successfully.',

            'data' =>
                $paymentMethods,

            'errors' =>
                null,
        ]);
    }
}