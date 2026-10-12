<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSupplierCreditApplicationRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Http\Resources\SupplierCreditApplicationResource;
use App\Http\Resources\SupplierCreditResource;
use App\Services\SupplierCreditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class SupplierCreditController extends Controller
{
    public function __construct(
        private readonly SupplierCreditService $supplierCreditService
    ) {
    }


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

                'supplier_id' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],

                'available_only' => [
                    'nullable',
                    'boolean',
                ],

                'per_page' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:100',
                ],
            ]);


        $credits =
            $this->supplierCreditService
                ->getCredits(
                    $validated
                );


        return response()->json([
            'success' =>
                true,

            'message' =>
                'Supplier credits loaded successfully.',

            'data' =>
                SupplierCreditResource::collection(
                    $credits
                ),

            'meta' => [
                'current_page' =>
                    $credits->currentPage(),

                'last_page' =>
                    $credits->lastPage(),

                'per_page' =>
                    $credits->perPage(),

                'total' =>
                    $credits->total(),

                'from' =>
                    $credits->firstItem(),

                'to' =>
                    $credits->lastItem(),
            ],
        ]);
    }


    public function applications(
        Request $request
    ): JsonResponse {

        $this->ensureViewAccess(
            $request
        );


        $validated =
            $request->validate([
                'supplier_id' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],

                'purchase_order_id' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],

                'source_settlement_id' => [
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


        $applications =
            $this->supplierCreditService
                ->getApplications(
                    $validated
                );


        return response()->json([
            'success' =>
                true,

            'message' =>
                'Supplier credit application history loaded successfully.',

            'data' =>
                SupplierCreditApplicationResource::collection(
                    $applications
                ),

            'meta' => [
                'current_page' =>
                    $applications->currentPage(),

                'last_page' =>
                    $applications->lastPage(),

                'per_page' =>
                    $applications->perPage(),

                'total' =>
                    $applications->total(),

                'from' =>
                    $applications->firstItem(),

                'to' =>
                    $applications->lastItem(),
            ],
        ]);
    }


    public function summary(
        Request $request
    ): JsonResponse {

        $this->ensureViewAccess(
            $request
        );


        $validated =
            $request->validate([
                'supplier_id' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
            ]);


        return response()->json([
            'success' =>
                true,

            'message' =>
                'Supplier credit summary loaded successfully.',

            'data' =>
                $this->supplierCreditService
                    ->getSummary(
                        !empty(
                            $validated['supplier_id']
                        )
                            ? (int) $validated['supplier_id']
                            : null
                    ),
        ]);
    }


    public function eligiblePurchaseOrders(
        Request $request,
        int $supplierCredit
    ): JsonResponse {

        $this->ensureViewAccess(
            $request
        );


        $orders =
            $this->supplierCreditService
                ->getEligiblePurchaseOrders(
                    $supplierCredit
                );


        return response()->json([
            'success' =>
                true,

            'message' =>
                'Eligible Purchase Orders loaded successfully.',

            'data' =>
                PurchaseOrderResource::collection(
                    $orders
                ),
        ]);
    }


    public function apply(
        StoreSupplierCreditApplicationRequest $request
    ): JsonResponse {

        $application =
            $this->supplierCreditService
                ->applyCredit(
                    data:
                        $request->validated(),

                    user:
                        $request->user()
                );


        return response()->json([
            'success' =>
                true,

            'message' =>
                "{$application->application_no} applied successfully.",

            'data' =>
                new SupplierCreditApplicationResource(
                    $application
                ),
        ], 201);
    }


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

            'You do not have permission to view supplier credits.'
        );
    }
}
