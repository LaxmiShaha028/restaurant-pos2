<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateSystemSettingRequest;
use App\Http\Resources\SystemSettingResource;
use App\Services\SystemSettingService;
use Illuminate\Http\JsonResponse;

class SystemSettingController
    extends Controller
{
    public function __construct(
        protected SystemSettingService $service
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Settings
    |--------------------------------------------------------------------------
    |
    | Used by /settings.
    | Route remains protected by settings.manage.
    |
    */

    public function show(): JsonResponse
    {
        $settings =
            $this->service->get();

        return response()->json([
            'success' =>
                true,

            'message' =>
                'System settings retrieved successfully.',

            'data' =>
                new SystemSettingResource(
                    $settings
                ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Settings
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateSystemSettingRequest $request
    ): JsonResponse {
        $settings =
            $this->service->update(
                request: $request
            );

        return response()->json([
            'success' =>
                true,

            'message' =>
                'System settings updated successfully.',

            'data' =>
                new SystemSettingResource(
                    $settings
                ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Operational Restaurant Settings
    |--------------------------------------------------------------------------
    |
    | This is NOT the admin settings editor.
    |
    | It exposes only read-only operational values needed by authenticated
    | restaurant screens such as Create Order:
    |
    | - Restaurant branding
    | - VAT
    | - Service Charge
    |
    | The route /system-settings already sits inside the protected tenant
    | authentication group, but it does not require settings.manage.
    |
    */

    public function publicShow(): JsonResponse
    {
        $settings =
            $this->service->get();

        return response()->json([

            'success' =>
                true,

            'message' =>
                'Restaurant operational settings retrieved successfully.',

            'data' => [

                /*
                |--------------------------------------------------------------------------
                | Branding
                |--------------------------------------------------------------------------
                */

                'restaurant_name' =>
                    $settings->restaurant_name,

                'restaurant_logo' =>
                    $this->fileUrl(
                        $settings->restaurant_logo
                    ),

                /*
                |--------------------------------------------------------------------------
                | VAT
                |--------------------------------------------------------------------------
                */

                'vat_type' =>
                    (string) (
                        $settings->vat_type
                        ??
                        'percentage'
                    ),

                'vat_rate' =>
                    (float) (
                        $settings->vat_rate
                        ??
                        0
                    ),

                /*
                |--------------------------------------------------------------------------
                | Service Charge
                |--------------------------------------------------------------------------
                */

                'service_charge_type' =>
                    (string) (
                        $settings->service_charge_type
                        ??
                        'percentage'
                    ),

                'service_charge_rate' =>
                    (float) (
                        $settings->service_charge_rate
                        ??
                        0
                    ),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | File URL Helper
    |--------------------------------------------------------------------------
    */

    protected function fileUrl(
        ?string $path
    ): ?string {

        if (!$path) {
            return null;
        }

        return asset(
            'storage/' .
            ltrim(
                $path,
                '/'
            )
        );
    }
}
