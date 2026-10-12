<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnSettlement;
use App\Models\SupplierCreditApplication;


class PurchaseReturnAccountingReconciliationService
{
    private const MONEY_EPSILON =
        0.005;


    public function checkCurrentTenant(): array
    {
        $rows = [];


        /*
        |--------------------------------------------------------------------------
        | 1. Purchase Order Effective Due
        |--------------------------------------------------------------------------
        */

        PurchaseOrder::query()
            ->orderBy('id')
            ->chunkById(
                200,
                function ($orders) use (&$rows): void {

                    foreach ($orders as $order) {

                        $expectedDue =
                            round(
                                max(
                                    0,
                                    (float) $order->total_amount
                                    -
                                    (float) $order->paid_amount
                                    -
                                    (float) (
                                        $order->return_credit_applied
                                        ??
                                        0
                                    )
                                    -
                                    (float) (
                                        $order->supplier_credit_applied
                                        ??
                                        0
                                    )
                                ),
                                2
                            );


                        $storedDue =
                            round(
                                max(
                                    0,
                                    (float) $order->due_amount
                                ),
                                2
                            );


                        $this->addCheck(
                            rows:
                                $rows,

                            check:
                                'purchase_order_due',

                            reference:
                                "PO #{$order->id}",

                            expected:
                                $expectedDue,

                            actual:
                                $storedDue,

                            details:
                                'total - paid - return_credit_applied - supplier_credit_applied'
                        );
                    }
                }
            );


        /*
        |--------------------------------------------------------------------------
        | 2. Direct Purchase Return Payable Offset -> PO Summary
        |--------------------------------------------------------------------------
        */

        $returnCreditByPo =
            PurchaseReturnSettlement::query()
                ->where(
                    'settlement_type',
                    PurchaseReturnSettlement::TYPE_PAYABLE_OFFSET
                )
                ->whereNotNull(
                    'purchase_order_id'
                )
                ->selectRaw(
                    'purchase_order_id, SUM(amount) AS total_amount'
                )
                ->groupBy(
                    'purchase_order_id'
                )
                ->pluck(
                    'total_amount',
                    'purchase_order_id'
                );


        PurchaseOrder::query()
            ->where(
                'return_credit_applied',
                '>',
                0
            )
            ->orWhereIn(
                'id',
                $returnCreditByPo
                    ->keys()
                    ->all()
            )
            ->orderBy('id')
            ->get()
            ->each(
                function ($order) use (
                    &$rows,
                    $returnCreditByPo
                ): void {

                    $expected =
                        round(
                            (float) (
                                $returnCreditByPo[
                                    $order->id
                                ]
                                ??
                                0
                            ),
                            2
                        );


                    $actual =
                        round(
                            (float) (
                                $order->return_credit_applied
                                ??
                                0
                            ),
                            2
                        );


                    $this->addCheck(
                        rows:
                            $rows,

                        check:
                            'purchase_order_return_credit',

                        reference:
                            "PO #{$order->id}",

                        expected:
                            $expected,

                        actual:
                            $actual,

                        details:
                            'Payable-offset settlement sum must equal purchase_orders.return_credit_applied.'
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | 3. General Supplier Credit Applications -> PO Summary
        |--------------------------------------------------------------------------
        */

        $supplierCreditByPo =
            SupplierCreditApplication::query()
                ->selectRaw(
                    'purchase_order_id, SUM(amount) AS total_amount'
                )
                ->groupBy(
                    'purchase_order_id'
                )
                ->pluck(
                    'total_amount',
                    'purchase_order_id'
                );


        PurchaseOrder::query()
            ->where(
                'supplier_credit_applied',
                '>',
                0
            )
            ->orWhereIn(
                'id',
                $supplierCreditByPo
                    ->keys()
                    ->all()
            )
            ->orderBy('id')
            ->get()
            ->each(
                function ($order) use (
                    &$rows,
                    $supplierCreditByPo
                ): void {

                    $expected =
                        round(
                            (float) (
                                $supplierCreditByPo[
                                    $order->id
                                ]
                                ??
                                0
                            ),
                            2
                        );


                    $actual =
                        round(
                            (float) (
                                $order->supplier_credit_applied
                                ??
                                0
                            ),
                            2
                        );


                    $this->addCheck(
                        rows:
                            $rows,

                        check:
                            'purchase_order_supplier_credit',

                        reference:
                            "PO #{$order->id}",

                        expected:
                            $expected,

                        actual:
                            $actual,

                        details:
                            'Supplier Credit Application sum must equal purchase_orders.supplier_credit_applied.'
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | 4. Purchase Return Allocation Must Never Exceed Return Credit
        |--------------------------------------------------------------------------
        */

        PurchaseReturn::query()
            ->with([
                'settlements',
            ])
            ->orderBy('id')
            ->chunkById(
                100,
                function ($returns) use (&$rows): void {

                    foreach ($returns as $return) {

                        $target =
                            round(
                                (float) $return
                                    ->expected_supplier_credit,
                                2
                            );


                        $allocated =
                            round(
                                (float) $return
                                    ->settlements
                                    ->where(
                                        'allocation_effect',
                                        true
                                    )
                                    ->sum(
                                        'amount'
                                    ),
                                2
                            );


                        $matches =
                            $allocated
                            <=
                            $target
                            +
                            self::MONEY_EPSILON;


                        $rows[] = [
                            'check' =>
                                'purchase_return_allocation',

                            'reference' =>
                                $return->return_no,

                            'expected' =>
                                $target,

                            'actual' =>
                                $allocated,

                            'difference' =>
                                round(
                                    $allocated
                                    -
                                    $target,
                                    2
                                ),

                            'matches' =>
                                $matches,

                            'details' =>
                                'Allocated supplier settlement cannot exceed expected supplier credit.',
                        ];
                    }
                }
            );


        /*
        |--------------------------------------------------------------------------
        | 5. Refund Receivable Collection Must Never Exceed Receivable
        |--------------------------------------------------------------------------
        */

        PurchaseReturnSettlement::query()
            ->with([
                'children',
            ])
            ->where(
                'settlement_type',
                PurchaseReturnSettlement::TYPE_REFUND_RECEIVABLE
            )
            ->orderBy('id')
            ->get()
            ->each(
                function ($receivable) use (&$rows): void {

                    $receivableAmount =
                        round(
                            (float) $receivable->amount,
                            2
                        );


                    $collected =
                        round(
                            (float) $receivable
                                ->children
                                ->where(
                                    'settlement_type',
                                    PurchaseReturnSettlement::TYPE_RECEIVABLE_COLLECTION
                                )
                                ->sum(
                                    'amount'
                                ),
                            2
                        );


                    $rows[] = [
                        'check' =>
                            'refund_receivable_collection',

                        'reference' =>
                            $receivable->settlement_no,

                        'expected' =>
                            $receivableAmount,

                        'actual' =>
                            $collected,

                        'difference' =>
                            round(
                                $collected
                                -
                                $receivableAmount,
                                2
                            ),

                        'matches' =>
                            $collected
                            <=
                            $receivableAmount
                            +
                            self::MONEY_EPSILON,

                        'details' =>
                            'Refund collections cannot exceed the original receivable.',
                    ];
                }
            );


        /*
        |--------------------------------------------------------------------------
        | 6. Supplier Credit Consumption Must Never Exceed Credit Issued
        |--------------------------------------------------------------------------
        */

        PurchaseReturnSettlement::query()
            ->with([
                'creditApplications.purchaseOrder',
            ])
            ->where(
                'settlement_type',
                PurchaseReturnSettlement::TYPE_SUPPLIER_CREDIT
            )
            ->orderBy('id')
            ->get()
            ->each(
                function ($credit) use (&$rows): void {

                    $issued =
                        round(
                            (float) $credit->amount,
                            2
                        );


                    $applied =
                        round(
                            (float) $credit
                                ->creditApplications
                                ->sum(
                                    'amount'
                                ),
                            2
                        );


                    $rows[] = [
                        'check' =>
                            'supplier_credit_consumption',

                        'reference' =>
                            $credit->settlement_no,

                        'expected' =>
                            $issued,

                        'actual' =>
                            $applied,

                        'difference' =>
                            round(
                                $applied
                                -
                                $issued,
                                2
                            ),

                        'matches' =>
                            $applied
                            <=
                            $issued
                            +
                            self::MONEY_EPSILON,

                        'details' =>
                            'Supplier Credit applications cannot exceed credit issued.',
                    ];


                    foreach (
                        $credit->creditApplications
                        as
                        $application
                    ) {

                        $po =
                            $application
                                ->purchaseOrder;


                        $sameSupplier =
                            $po
                            &&
                            (int) $po->supplier_id
                            ===
                            (int) $credit->supplier_id;


                        $rows[] = [
                            'check' =>
                                'supplier_credit_supplier_match',

                            'reference' =>
                                $application->application_no,

                            'expected' =>
                                (int) $credit->supplier_id,

                            'actual' =>
                                $po
                                    ? (int) $po->supplier_id
                                    : null,

                            'difference' =>
                                null,

                            'matches' =>
                                $sameSupplier,

                            'details' =>
                                'Supplier Credit source and target Purchase Order must belong to the same supplier.',
                        ];
                    }
                }
            );


        /*
        |--------------------------------------------------------------------------
        | 7. Every Supplier Credit Application Must Have A Valid Credit Source
        |--------------------------------------------------------------------------
        */

        SupplierCreditApplication::query()
            ->with([
                'sourceSettlement.purchaseReturn.items',
                'purchaseOrder',
            ])
            ->orderBy('id')
            ->get()
            ->each(
                function ($application) use (&$rows): void {

                    $source =
                        $application
                            ->sourceSettlement;


                    $sourceValid =
                        $source
                        &&
                        $source->settlement_type
                        ===
                        PurchaseReturnSettlement::TYPE_SUPPLIER_CREDIT
                        &&
                        (int) $source->supplier_id
                        ===
                        (int) $application->supplier_id;


                    $rows[] = [
                        'check' =>
                            'supplier_credit_source_type',

                        'reference' =>
                            $application->application_no,

                        'expected' =>
                            PurchaseReturnSettlement::TYPE_SUPPLIER_CREDIT,

                        'actual' =>
                            $source?->settlement_type,

                        'difference' =>
                            null,

                        'matches' =>
                            $sourceValid,

                        'details' =>
                            'Every Supplier Credit Application must originate from a supplier_credit settlement for the same supplier.',
                    ];


                    $originalPoIds =
                        $source
                            ?->purchaseReturn
                            ?->items
                            ?->pluck('purchase_order_id')
                            ?->filter()
                            ?->map(
                                static fn ($id): int =>
                                    (int) $id
                            )
                            ?->unique()
                            ?->values()
                            ?->all()
                        ??
                        [];


                    $targetIsDifferent =
                        !in_array(
                            (int) $application->purchase_order_id,
                            $originalPoIds,
                            true
                        );


                    $rows[] = [
                        'check' =>
                            'supplier_credit_original_po_protection',

                        'reference' =>
                            $application->application_no,

                        'expected' =>
                            'different_purchase_order',

                        'actual' =>
                            $targetIsDifferent
                                ? 'different_purchase_order'
                                : 'original_purchase_order',

                        'difference' =>
                            null,

                        'matches' =>
                            $targetIsDifferent,

                        'details' =>
                            'Reusable Supplier Credit must not be applied back to the Purchase Return source PO.',
                    ];
                }
            );


        $mismatches =
            collect(
                $rows
            )
                ->where(
                    'matches',
                    false
                )
                ->values();


        return [
            'matches' =>
                $mismatches->isEmpty(),

            'checks_performed' =>
                count($rows),

            'mismatch_count' =>
                $mismatches->count(),

            'rows' =>
                $rows,
        ];
    }


    private function addCheck(
        array &$rows,
        string $check,
        string $reference,
        float $expected,
        float $actual,
        string $details
    ): void {

        $difference =
            round(
                $actual
                -
                $expected,
                2
            );


        $rows[] = [
            'check' =>
                $check,

            'reference' =>
                $reference,

            'expected' =>
                $expected,

            'actual' =>
                $actual,

            'difference' =>
                $difference,

            'matches' =>
                abs(
                    $difference
                )
                <=
                self::MONEY_EPSILON,

            'details' =>
                $details,
        ];
    }
}
