<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreStockTransferTemplateRequest;
use App\Http\Requests\Api\UpdateStockTransferTemplateRequest;
use App\Http\Resources\StockTransferTemplateResource;
use App\Services\StockTransferTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class StockTransferTemplateController extends Controller
{
    public function __construct(
        private readonly StockTransferTemplateService
            $templateService
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
                    'max:120',
                ],

                'active' => [
                    'nullable',
                    'boolean',
                ],

            ]);


        $templates =
            $this->templateService
                ->getTemplates(
                    $validated
                );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Stock transfer templates loaded successfully.',

            'data' =>
                StockTransferTemplateResource::collection(
                    $templates
                ),

        ]);
    }


    public function store(
        StoreStockTransferTemplateRequest $request
    ): JsonResponse {

        $template =
            $this->templateService
                ->createTemplate(

                    data:
                        $request->validated(),

                    user:
                        $request->user()

                );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Stock transfer template created successfully.',

            'data' =>
                new StockTransferTemplateResource(
                    $template
                ),

        ], 201);
    }


    public function show(
        Request $request,
        int $template
    ): JsonResponse {

        $this->ensureViewAccess(
            $request
        );


        $resolved =
            $this->templateService
                ->getTemplate(
                    $template
                );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Stock transfer template loaded successfully.',

            'data' =>
                new StockTransferTemplateResource(
                    $resolved
                ),

        ]);
    }


    public function update(
        UpdateStockTransferTemplateRequest $request,
        int $template
    ): JsonResponse {

        $resolved =
            $this->templateService
                ->updateTemplate(

                    templateId:
                        $template,

                    data:
                        $request->validated(),

                    user:
                        $request->user()

                );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Stock transfer template updated successfully.',

            'data' =>
                new StockTransferTemplateResource(
                    $resolved
                ),

        ]);
    }


    public function updateStatus(
        Request $request,
        int $template
    ): JsonResponse {

        abort_unless(
            $request->user(),
            401,
            'Authentication is required.'
        );


        abort_unless(
            $request
                ->user()
                ->hasPermission(
                    'inventory.manage'
                ),
            403,
            'You do not have permission to manage stock transfer templates.'
        );


        $validated =
            $request->validate([

                'is_active' => [
                    'required',
                    'boolean',
                ],

            ]);


        $resolved =
            $this->templateService
                ->setStatus(

                    templateId:
                        $template,

                    isActive:
                        (bool) $validated[
                            'is_active'
                        ],

                    user:
                        $request->user()

                );


        return response()->json([

            'success' =>
                true,

            'message' =>
                $resolved->is_active
                    ? 'Stock transfer template activated successfully.'
                    : 'Stock transfer template deactivated successfully.',

            'data' =>
                new StockTransferTemplateResource(
                    $resolved
                ),

        ]);
    }


    public function destroy(
        Request $request,
        int $template
    ): JsonResponse {

        abort_unless(
            $request->user(),
            401,
            'Authentication is required.'
        );


        abort_unless(
            $request
                ->user()
                ->hasPermission(
                    'inventory.manage'
                ),
            403,
            'You do not have permission to manage stock transfer templates.'
        );


        $this->templateService
            ->deleteTemplate(
                $template
            );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Stock transfer template deleted successfully.',

            'data' =>
                null,

        ]);
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

            'You do not have permission to view stock transfer templates.'

        );
    }
}
