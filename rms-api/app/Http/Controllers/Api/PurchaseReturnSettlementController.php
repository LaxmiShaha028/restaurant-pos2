<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePurchaseReturnRefundCollectionRequest;
use App\Http\Requests\Api\StorePurchaseReturnSettlementRequest;
use App\Http\Resources\PurchaseReturnSettlementResource;
use App\Services\PurchaseReturnSettlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class PurchaseReturnSettlementController extends Controller
{
    public function __construct(
        private readonly PurchaseReturnSettlementService $settlementService
    ) {
    }


    public function index(
        Request $request,
        int $purchaseReturn
    ): JsonResponse {

        $this->ensureViewAccess(
            $request
        );


        $settlements =
            $this->settlementService
                ->getSettlements(
                    $purchaseReturn
                );


        return response()->json([
            'success' =>
                true,

            'message' =>
                'Purchase Return settlements loaded successfully.',

            'data' =>
                PurchaseReturnSettlementResource::collection(
                    $settlements
                ),
        ]);
    }


    public function store(
        StorePurchaseReturnSettlementRequest $request,
        int $purchaseReturn
    ): JsonResponse {

        $settlement =
            $this->settlementService
                ->allocate(
                    purchaseReturnId:
                        $purchaseReturn,

                    data:
                        $request->validated(),

                    user:
                        $request->user()
                );


        return response()->json([
            'success' =>
                true,

            'message' =>
                "{$settlement->settlement_no} recorded successfully.",

            'data' =>
                new PurchaseReturnSettlementResource(
                    $settlement
                ),
        ], 201);
    }


    public function collectRefund(
        StorePurchaseReturnRefundCollectionRequest $request,
        int $purchaseReturn
    ): JsonResponse {

        $collection =
            $this->settlementService
                ->collectReceivable(
                    purchaseReturnId:
                        $purchaseReturn,

                    data:
                        $request->validated(),

                    user:
                        $request->user()
                );


        return response()->json([
            'success' =>
                true,

            'message' =>
                "{$collection->settlement_no} refund collection recorded successfully.",

            'data' =>
                new PurchaseReturnSettlementResource(
                    $collection
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

            'You do not have permission to view Purchase Return settlements.'
        );
    }
}
