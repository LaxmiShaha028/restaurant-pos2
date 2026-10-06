<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InventoryDocumentPdfService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class InventoryDocumentController extends Controller
{
    public function __construct(
        private readonly InventoryDocumentPdfService $pdfService
    ) {
    }


    public function purchaseReturn(
        Request $request,
        int $purchaseReturn
    ): Response {

        $this->ensureViewAccess(
            $request
        );


        $pdf =
            $this->pdfService
                ->purchaseReturn(
                    $purchaseReturn
                );


        $filename =
            sprintf(
                'purchase-return-%d.pdf',
                $purchaseReturn
            );


        return $request->boolean(
            'download'
        )
            ? $pdf->download(
                $filename
            )
            : $pdf->stream(
                $filename
            );
    }


    public function purchaseOrder(
        Request $request,
        int $purchaseOrder
    ): Response {

        $this->ensureViewAccess(
            $request
        );


        $pdf =
            $this->pdfService
                ->purchaseOrder(
                    $purchaseOrder
                );


        $filename =
            sprintf(
                'purchase-order-%d.pdf',
                $purchaseOrder
            );


        return $request->boolean(
            'download'
        )
            ? $pdf->download(
                $filename
            )
            : $pdf->stream(
                $filename
            );
    }


    public function purchaseReceipt(
        Request $request,
        int $purchaseOrder,
        int $receipt
    ): Response {

        $this->ensureViewAccess(
            $request
        );


        $pdf =
            $this->pdfService
                ->purchaseReceipt(
                    purchaseOrderId:
                        $purchaseOrder,

                    receiptId:
                        $receipt
                );


        $filename =
            sprintf(
                'grn-%d-%d.pdf',
                $purchaseOrder,
                $receipt
            );


        return $request->boolean(
            'download'
        )
            ? $pdf->download(
                $filename
            )
            : $pdf->stream(
                $filename
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

            'You do not have permission to view inventory documents.'
        );
    }
}
