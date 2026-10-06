<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreInventoryWastageRequest;
use App\Http\Resources\InventoryWastageResource;
use App\Models\InventoryWastage;
use App\Models\InventoryWastageItem;
use App\Services\InventoryWastageService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class InventoryWastageController extends Controller
{
    public function __construct(
        private readonly InventoryWastageService
            $inventoryWastageService
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Wastage History
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

                    Rule::exists(
                        'raw_materials',
                        'id'
                    ),
                ],

                'reason' => [
                    'nullable',
                    'string',

                    Rule::in(
                        InventoryWastageItem::allowedReasons()
                    ),
                ],

                'date_from' => [
                    'nullable',
                    'date',
                ],

                'date_to' => [
                    'nullable',
                    'date',
                    'after_or_equal:date_from',
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


        $wastages =
            $this->inventoryWastageService
                ->getWastages(
                    $validated
                );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Damage / wastage history loaded successfully.',

            'data' =>
                InventoryWastageResource::collection(
                    $wastages
                ),

            'meta' =>
                $this->paginationMeta(
                    $wastages
                ),

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Record Damage / Wastage
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreInventoryWastageRequest $request
    ): JsonResponse {

        $wastage =
            $this->inventoryWastageService
                ->recordWastage(

                    data:
                        $request->validated(),

                    user:
                        $request->user()

                );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Restaurant damage / wastage recorded successfully.',

            'data' =>
                new InventoryWastageResource(
                    $wastage
                ),

        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | Single Wastage
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        InventoryWastage $wastage
    ): JsonResponse {

        $this->ensureViewAccess(
            $request
        );


        $wastage =
            $this->inventoryWastageService
                ->getWastage(
                    $wastage
                );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Damage / wastage record loaded successfully.',

            'data' =>
                new InventoryWastageResource(
                    $wastage
                ),

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    private function paginationMeta(
        LengthAwarePaginator $paginator
    ): array {

        return [

            'current_page' =>
                $paginator
                    ->currentPage(),

            'last_page' =>
                $paginator
                    ->lastPage(),

            'per_page' =>
                $paginator
                    ->perPage(),

            'total' =>
                $paginator
                    ->total(),

            'from' =>
                $paginator
                    ->firstItem(),

            'to' =>
                $paginator
                    ->lastItem(),

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | View Access
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

            'You do not have permission to view damage / wastage history.'

        );
    }
}
