<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PurchaseReturnAccountingReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class PurchaseReturnAccountingController extends Controller
{
    public function __construct(
        private readonly PurchaseReturnAccountingReportService $reportService
    ) {
    }


    public function summary(
        Request $request
    ): JsonResponse {

        $this->ensureViewAccess(
            $request
        );


        $validated =
            $this->validateFilters(
                $request
            );


        return response()->json([
            'success' =>
                true,

            'message' =>
                'Purchase Return accounting summary loaded successfully.',

            'data' =>
                $this->reportService
                    ->summary(
                        $validated
                    ),
        ]);
    }


    public function ledger(
        Request $request
    ): JsonResponse {

        $this->ensureViewAccess(
            $request
        );


        $validated =
            $this->validateFilters(
                $request,
                includeLimit:
                    true
            );


        return response()->json([
            'success' =>
                true,

            'message' =>
                'Purchase Return accounting ledger loaded successfully.',

            'data' =>
                $this->reportService
                    ->ledger(
                        $validated
                    ),
        ]);
    }


    private function validateFilters(
        Request $request,
        bool $includeLimit = false
    ): array {

        $rules = [
            'date_from' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'date_to' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:date_from',
            ],

            'supplier_id' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];


        if ($includeLimit) {
            $rules['limit'] = [
                'nullable',
                'integer',
                'min:1',
                'max:500',
            ];
        }


        return $request->validate(
            $rules
        );
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

            'You do not have permission to view Purchase Return accounting data.'
        );
    }
}
