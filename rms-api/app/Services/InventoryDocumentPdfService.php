<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderReceipt;
use App\Models\PurchaseReturn;
use Barryvdh\DomPDF\Facade\Pdf;


class InventoryDocumentPdfService
{
    /*
    |--------------------------------------------------------------------------
    | Purchase Return PDF
    |--------------------------------------------------------------------------
    */

    public function purchaseReturn(
        int $purchaseReturnId
    ): mixed {

        $this->ensurePdfDriver();


        $purchaseReturn =
            PurchaseReturn::query()
                ->with([
                    'items.rawMaterial',
                    'items.lot',
                    'items.purchaseOrder',
                    'items.purchaseOrderReceipt',
                    'settlements.purchaseOrder',
                    'settlements.creator',
                    'settlements.children.creator',
                    'settlements.creditApplications.purchaseOrder',
                    'settlements.creditApplications.creator',
                    'creator',
                ])
                ->findOrFail(
                    $purchaseReturnId
                );


        return Pdf::loadView(
            'pdf.inventory.purchase-return',
            [
                'purchaseReturn' =>
                    $purchaseReturn,
            ]
        )
            ->setPaper(
                'a4',
                'portrait'
            )
            ->setOption(
                'defaultFont',
                'DejaVu Sans'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Purchase Order PDF
    |--------------------------------------------------------------------------
    */

    public function purchaseOrder(
        int $purchaseOrderId
    ): mixed {

        $this->ensurePdfDriver();


        $purchaseOrder =
            PurchaseOrder::query()
                ->with([
                    'supplier',
                    'items.rawMaterial',
                    'orderedBy',
                    'payments.creator',
                    'receipts.items.rawMaterial',
                    'receipts.receivedBy',
                    'receipts.creator',
                ])
                ->findOrFail(
                    $purchaseOrderId
                );


        return Pdf::loadView(
            'pdf.inventory.purchase-order',
            [
                'purchaseOrder' =>
                    $purchaseOrder,
            ]
        )
            ->setPaper(
                'a4',
                'portrait'
            )
            ->setOption(
                'defaultFont',
                'DejaVu Sans'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | GRN / Purchase Receive PDF
    |--------------------------------------------------------------------------
    */

    public function purchaseReceipt(
        int $purchaseOrderId,
        int $receiptId
    ): mixed {

        $this->ensurePdfDriver();


        $purchaseOrder =
            PurchaseOrder::query()
                ->with([
                    'supplier',
                ])
                ->findOrFail(
                    $purchaseOrderId
                );


        $receipt =
            PurchaseOrderReceipt::query()
                ->with([
                    'items.rawMaterial',
                    'receivedBy',
                    'creator',
                ])
                ->where(
                    'purchase_order_id',
                    $purchaseOrder->id
                )
                ->findOrFail(
                    $receiptId
                );


        return Pdf::loadView(
            'pdf.inventory.purchase-receipt',
            [
                'purchaseOrder' =>
                    $purchaseOrder,

                'receipt' =>
                    $receipt,
            ]
        )
            ->setPaper(
                'a4',
                'portrait'
            )
            ->setOption(
                'defaultFont',
                'DejaVu Sans'
            );
    }


    private function ensurePdfDriver(): void
    {
        if (
            !class_exists(
                Pdf::class
            )
        ) {

            throw new \RuntimeException(
                'PDF driver is not installed. Run: composer require barryvdh/laravel-dompdf'
            );
        }
    }
}
