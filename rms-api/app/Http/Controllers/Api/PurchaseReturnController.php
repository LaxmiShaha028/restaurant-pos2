<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePurchaseReturnRequest;
use App\Http\Resources\PurchaseReturnResource;
use App\Services\PurchaseReturnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class PurchaseReturnController extends Controller
{
    public function __construct(
        private readonly PurchaseReturnService $purchaseReturnService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->ensureViewAccess($request);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:180'],
            'supplier_id' => ['nullable', 'integer', 'min:1'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:date_from',
            ],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $returns = $this->purchaseReturnService->getReturns($validated);

        return response()->json([
            'success' => true,
            'message' => 'Purchase Return history loaded successfully.',
            'data' => PurchaseReturnResource::collection($returns),
            'meta' => [
                'current_page' => $returns->currentPage(),
                'last_page' => $returns->lastPage(),
                'per_page' => $returns->perPage(),
                'total' => $returns->total(),
                'from' => $returns->firstItem(),
                'to' => $returns->lastItem(),
            ],
        ]);
    }

    public function store(
        StorePurchaseReturnRequest $request
    ): JsonResponse {
        $purchaseReturn = $this->purchaseReturnService->createReturn(
            data: $request->validated(),
            user: $request->user()
        );

        return response()->json([
            'success' => true,
            'message' =>
                "Purchase Return {$purchaseReturn->return_no} recorded successfully.",
            'data' => new PurchaseReturnResource($purchaseReturn),
        ], 201);
    }

    public function show(
        Request $request,
        int $purchaseReturn
    ): JsonResponse {
        $this->ensureViewAccess($request);

        $resolved = $this->purchaseReturnService->getReturn($purchaseReturn);

        return response()->json([
            'success' => true,
            'message' => 'Purchase Return loaded successfully.',
            'data' => new PurchaseReturnResource($resolved),
        ]);
    }

    private function ensureViewAccess(Request $request): void
    {
        abort_unless(
            $request->user(),
            401,
            'Authentication is required.'
        );

        abort_unless(
            $request->user()->hasAnyPermission([
                'inventory.view',
                'inventory.manage',
            ]),
            403,
            'You do not have permission to view Purchase Returns.'
        );
    }
}
