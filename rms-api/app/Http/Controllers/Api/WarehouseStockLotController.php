<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateWarehouseStockLotMetadataRequest;
use App\Http\Resources\WarehouseStockLotMetadataChangeResource;
use App\Http\Resources\WarehouseStockLotResource;
use App\Models\WarehouseStockLot;
use App\Services\WarehouseStockLotMetadataService;
use App\Services\WarehouseStockLotQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class WarehouseStockLotController extends Controller
{
    public function __construct(
        private readonly WarehouseStockLotQueryService $lotQueryService,
        private readonly WarehouseStockLotMetadataService $metadataService
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Lot List
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): JsonResponse {

        $this->ensureViewAccess(
            $request
        );


        $validated =
            $request->validate([

                'search' => [
                    'nullable',
                    'string',
                    'max:180',
                ],

                'raw_material_id' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],

                'source_type' => [
                    'nullable',
                    'string',
                    Rule::in(
                        WarehouseStockLot::allowedSourceTypes()
                    ),
                ],

                'status' => [
                    'nullable',
                    'string',
                    Rule::in([
                        WarehouseStockLot::STATUS_HEALTHY,
                        WarehouseStockLot::STATUS_EXPIRING_SOON,
                        WarehouseStockLot::STATUS_CRITICAL,
                        WarehouseStockLot::STATUS_EXPIRED,
                        WarehouseStockLot::STATUS_DEPLETED,
                        WarehouseStockLot::STATUS_UNKNOWN_EXPIRY,
                    ]),
                ],

                'scope' => [
                    'nullable',
                    'string',
                    Rule::in([
                        'expiry_watch',
                    ]),
                ],

                'has_stock' => [
                    'nullable',
                    'boolean',
                ],

                'expiry_from' => [
                    'nullable',
                    'date_format:Y-m-d',
                ],

                'expiry_to' => [
                    'nullable',
                    'date_format:Y-m-d',
                    'after_or_equal:expiry_from',
                ],

                'sort_by' => [
                    'nullable',
                    'string',
                    Rule::in([
                        'expiry_date',
                        'received_at',
                        'remaining_quantity',
                        'received_quantity',
                        'unit_cost',
                        'created_at',
                        'updated_at',
                    ]),
                ],

                'sort_direction' => [
                    'nullable',
                    'string',
                    Rule::in([
                        'asc',
                        'desc',
                    ]),
                ],

                'page' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],

                'per_page' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:100',
                ],

            ]);


        $lots =
            $this->lotQueryService
                ->getLots(
                    $validated
                );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Warehouse lots loaded successfully.',

            'data' =>
                WarehouseStockLotResource::collection(
                    $lots
                ),

            'meta' => [
                'current_page' =>
                    $lots->currentPage(),

                'last_page' =>
                    $lots->lastPage(),

                'per_page' =>
                    $lots->perPage(),

                'total' =>
                    $lots->total(),

                'from' =>
                    $lots->firstItem(),

                'to' =>
                    $lots->lastItem(),
            ],

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    public function summary(
        Request $request
    ): JsonResponse {

        $this->ensureViewAccess(
            $request
        );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Warehouse lot expiry summary loaded successfully.',

            'data' =>
                $this->lotQueryService
                    ->getSummary(),

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Lot Details
    |--------------------------------------------------------------------------
    |
    | Integer parameter avoids tenant model-binding order problems.
    |
    */

    public function show(
        Request $request,
        int $lot
    ): JsonResponse {

        $this->ensureViewAccess(
            $request
        );


        $resolved =
            $this->lotQueryService
                ->getLot(
                    $lot
                );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Warehouse lot loaded successfully.',

            'data' =>
                new WarehouseStockLotResource(
                    $resolved
                ),

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Update Manual Lot Metadata
    |--------------------------------------------------------------------------
    |
    | inventory.manage only.
    |
    | Quantities, cost, material, source, PO and GRN are intentionally NOT
    | editable here.
    |
    */

    public function updateMetadata(
        UpdateWarehouseStockLotMetadataRequest $request,
        int $lot
    ): JsonResponse {

        $updated =
            $this->metadataService
                ->update(

                    lotId:
                        $lot,

                    data:
                        $request->validated(),

                    user:
                        $request->user()

                );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Warehouse lot information updated successfully.',

            'data' =>
                new WarehouseStockLotResource(
                    $updated
                ),

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Manual Metadata Change History
    |--------------------------------------------------------------------------
    */

    public function metadataHistory(
        Request $request,
        int $lot
    ): JsonResponse {

        $this->ensureViewAccess(
            $request
        );


        $changes =
            $this->metadataService
                ->history(
                    $lot
                );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Warehouse lot metadata history loaded successfully.',

            'data' =>
                WarehouseStockLotMetadataChangeResource::collection(
                    $changes
                ),

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Permission
    |--------------------------------------------------------------------------
    */

    private function ensureViewAccess(
        Request $request
    ): void {

        abort_unless(
            $request->user(),
            401,
            'Authentication is required.'
        );


        abort_unless(

            $request
                ->user()
                ->hasAnyPermission([
                    'inventory.view',
                    'inventory.manage',
                ]),

            403,

            'You do not have permission to view warehouse lots.'

        );
    }
}
