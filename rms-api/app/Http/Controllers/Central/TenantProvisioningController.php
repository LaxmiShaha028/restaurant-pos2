<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Services\Tenant\TenantProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class TenantProvisioningController extends Controller
{
    public function store(
        Request $request,
        TenantProvisioningService $provisioningService
    ): JsonResponse {

        $validated =
            $request->validate([

                'name' => [
                    'required',
                    'string',
                    'max:180',
                ],

                'owner_name' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'owner_email' => [
                    'required',
                    'email',
                    'max:150',
                ],

                'phone' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'address' => [
                    'nullable',
                    'string',
                ],

                'city' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'country' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'username' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'password' => [
                    'nullable',
                    'string',
                    'min:8',
                    'max:255',
                ],

                'logo_path' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'subscription_plan_id' => [
                    'nullable',
                    'integer',
                    'exists:central.subscription_plans,id',
                ],

                'trial_days' => [
                    'nullable',
                    'integer',
                    'min:0',
                ],

                'auto_renew' => [
                    'nullable',
                    'boolean',
                ],
            ]);

        try {

            $result =
                $provisioningService->provision(
                    $validated
                );

            return response()->json([

                'success' =>
                    true,

                'message' =>
                    'Restaurant tenant provisioned successfully.',

                'data' =>
                    $result,

                'errors' =>
                    null,

            ], 201);

        } catch (Throwable $exception) {

            report($exception);

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Restaurant tenant provisioning failed.',

                'data' =>
                    null,

                'errors' => [
                    'provisioning' => [
                        $exception->getMessage(),
                    ],
                ],

            ], 500);
        }
    }
}