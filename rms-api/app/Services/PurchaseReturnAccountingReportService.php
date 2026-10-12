<?php

namespace App\Services;

use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnSettlement;
use App\Models\SupplierCreditApplication;
use Illuminate\Database\Eloquent\Builder;


class PurchaseReturnAccountingReportService
{
    public function summary(
        array $filters = []
    ): array {

        $supplierId =
            !empty(
                $filters['supplier_id']
            )
                ? (int) $filters['supplier_id']
                : null;


        /*
        |--------------------------------------------------------------------------
        | Physical Return Activity
        |--------------------------------------------------------------------------
        |
        | Filtered by Purchase Return date.
        |
        */

        $returnQuery =
            PurchaseReturn::query();


        $this->applyDateFilter(
            query:
                $returnQuery,

            field:
                'returned_at',

            filters:
                $filters
        );


        if ($supplierId) {
            $returnQuery->where(
                'supplier_id',
                $supplierId
            );
        }


        $returnIds =
            (clone $returnQuery)
                ->pluck('id');


        $returnCount =
            (clone $returnQuery)
                ->count();


        $expectedSupplierCredit =
            round(
                (float) (clone $returnQuery)
                    ->sum(
                        'expected_supplier_credit'
                    ),
                2
            );


        $inventoryValueReduction =
            round(
                (float) (clone $returnQuery)
                    ->sum(
                        'inventory_value_reduction'
                    ),
                2
            );


        $valuationVariance =
            round(
                (float) (clone $returnQuery)
                    ->sum(
                        'valuation_variance'
                    ),
                2
            );


        $allocatedForSelectedReturns =
            $returnIds->isEmpty()
                ? 0.0
                : round(
                    (float) PurchaseReturnSettlement::query()
                        ->whereIn(
                            'purchase_return_id',
                            $returnIds->all()
                        )
                        ->where(
                            'allocation_effect',
                            true
                        )
                        ->sum('amount'),
                    2
                );


        $unallocatedForSelectedReturns =
            round(
                max(
                    0,
                    $expectedSupplierCredit
                    -
                    $allocatedForSelectedReturns
                ),
                2
            );


        /*
        |--------------------------------------------------------------------------
        | Financial Activity
        |--------------------------------------------------------------------------
        |
        | Filtered by actual settlement/application event date.
        |
        */

        $settlementQuery =
            PurchaseReturnSettlement::query();


        $this->applyDateFilter(
            query:
                $settlementQuery,

            field:
                'settled_at',

            filters:
                $filters
        );


        if ($supplierId) {
            $settlementQuery->where(
                'supplier_id',
                $supplierId
            );
        }


        $payableOffset =
            $this->sumSettlementType(
                $settlementQuery,
                PurchaseReturnSettlement::TYPE_PAYABLE_OFFSET
            );


        $supplierCreditIssued =
            $this->sumSettlementType(
                $settlementQuery,
                PurchaseReturnSettlement::TYPE_SUPPLIER_CREDIT
            );


        $refundReceivableCreated =
            $this->sumSettlementType(
                $settlementQuery,
                PurchaseReturnSettlement::TYPE_REFUND_RECEIVABLE
            );


        $directRefund =
            $this->sumSettlementType(
                $settlementQuery,
                PurchaseReturnSettlement::TYPE_DIRECT_REFUND
            );


        $refundCollected =
            $this->sumSettlementType(
                $settlementQuery,
                PurchaseReturnSettlement::TYPE_RECEIVABLE_COLLECTION
            );


        $applicationQuery =
            SupplierCreditApplication::query();


        $this->applyDateFilter(
            query:
                $applicationQuery,

            field:
                'applied_at',

            filters:
                $filters
        );


        if ($supplierId) {
            $applicationQuery->where(
                'supplier_id',
                $supplierId
            );
        }


        $supplierCreditApplied =
            round(
                (float) $applicationQuery
                    ->sum('amount'),
                2
            );


        /*
        |--------------------------------------------------------------------------
        | Current Open Balances
        |--------------------------------------------------------------------------
        |
        | Current balances are intentionally NOT limited by date range.
        | They answer "what is still open now?".
        |
        */

        $allSupplierCreditIssuedQuery =
            PurchaseReturnSettlement::query()
                ->where(
                    'settlement_type',
                    PurchaseReturnSettlement::TYPE_SUPPLIER_CREDIT
                )
                ->whereNull(
                    'parent_settlement_id'
                );


        $allSupplierCreditAppliedQuery =
            SupplierCreditApplication::query();


        $allRefundReceivableQuery =
            PurchaseReturnSettlement::query()
                ->where(
                    'settlement_type',
                    PurchaseReturnSettlement::TYPE_REFUND_RECEIVABLE
                );


        $allRefundCollectionQuery =
            PurchaseReturnSettlement::query()
                ->where(
                    'settlement_type',
                    PurchaseReturnSettlement::TYPE_RECEIVABLE_COLLECTION
                );


        if ($supplierId) {
            $allSupplierCreditIssuedQuery->where(
                'supplier_id',
                $supplierId
            );

            $allSupplierCreditAppliedQuery->where(
                'supplier_id',
                $supplierId
            );

            $allRefundReceivableQuery->where(
                'supplier_id',
                $supplierId
            );

            $allRefundCollectionQuery->where(
                'supplier_id',
                $supplierId
            );
        }


        $allSupplierCreditIssued =
            round(
                (float) $allSupplierCreditIssuedQuery
                    ->sum('amount'),
                2
            );


        $allSupplierCreditApplied =
            round(
                (float) $allSupplierCreditAppliedQuery
                    ->sum('amount'),
                2
            );


        $openSupplierCredit =
            round(
                max(
                    0,
                    $allSupplierCreditIssued
                    -
                    $allSupplierCreditApplied
                ),
                2
            );


        $allRefundReceivable =
            round(
                (float) $allRefundReceivableQuery
                    ->sum('amount'),
                2
            );


        $allRefundCollected =
            round(
                (float) $allRefundCollectionQuery
                    ->sum('amount'),
                2
            );


        $openRefundReceivable =
            round(
                max(
                    0,
                    $allRefundReceivable
                    -
                    $allRefundCollected
                ),
                2
            );


        return [
            'filters' => [
                'date_from' =>
                    $filters['date_from']
                    ??
                    null,

                'date_to' =>
                    $filters['date_to']
                    ??
                    null,

                'supplier_id' =>
                    $supplierId,
            ],

            'classification' => [
                'purchase_return' =>
                    'inventory_and_supplier_liability_reversal',

                'supplier_credit' =>
                    'supplier_account_credit',

                'refund_receivable' =>
                    'supplier_receivable',

                'refund_cash' =>
                    'cash_or_bank_inflow',

                'pnl_note' =>
                    'Purchase Return is not sales revenue and is not an operating expense. Inventory valuation variance is exposed separately for accounting policy/reporting.',
            ],

            'return_activity' => [
                'return_count' =>
                    $returnCount,

                'expected_supplier_credit' =>
                    $expectedSupplierCredit,

                'inventory_value_reduction' =>
                    $inventoryValueReduction,

                'valuation_variance' =>
                    $valuationVariance,

                'allocated_amount' =>
                    $allocatedForSelectedReturns,

                'unallocated_amount' =>
                    $unallocatedForSelectedReturns,
            ],

            'financial_activity' => [
                'payable_offset' =>
                    $payableOffset,

                'supplier_credit_issued' =>
                    $supplierCreditIssued,

                'supplier_credit_applied_to_po' =>
                    $supplierCreditApplied,

                'refund_receivable_created' =>
                    $refundReceivableCreated,

                'direct_refund_received' =>
                    $directRefund,

                'refund_receivable_collected' =>
                    $refundCollected,

                'cash_refund_total' =>
                    round(
                        $directRefund
                        +
                        $refundCollected,
                        2
                    ),
            ],

            'open_balances' => [
                'supplier_credit' =>
                    $openSupplierCredit,

                'refund_receivable' =>
                    $openRefundReceivable,
            ],
        ];
    }


    public function ledger(
        array $filters = []
    ): array {

        $supplierId =
            !empty(
                $filters['supplier_id']
            )
                ? (int) $filters['supplier_id']
                : null;


        $limit =
            min(
                max(
                    (int) (
                        $filters['limit']
                        ??
                        200
                    ),
                    1
                ),
                500
            );


        $settlementQuery =
            PurchaseReturnSettlement::query()
                ->with([
                    'purchaseReturn',
                    'purchaseOrder',
                ]);


        $this->applyDateFilter(
            query:
                $settlementQuery,

            field:
                'settled_at',

            filters:
                $filters
        );


        if ($supplierId) {
            $settlementQuery->where(
                'supplier_id',
                $supplierId
            );
        }


        $settlementRows =
            $settlementQuery
                ->orderByDesc('settled_at')
                ->orderByDesc('id')
                ->limit($limit)
                ->get()
                ->map(
                    static function (
                        PurchaseReturnSettlement $row
                    ): array {

                        return [
                            'event_id' =>
                                "settlement:{$row->id}",

                            'event_type' =>
                                $row->settlement_type,

                            'event_type_label' =>
                                PurchaseReturnSettlement::typeLabel(
                                    $row->settlement_type
                                ),

                            'occurred_at' =>
                                $row->settled_at
                                    ?->toISOString(),

                            'reference_no' =>
                                $row->settlement_no,

                            'supplier_id' =>
                                (int) $row->supplier_id,

                            'supplier_name' =>
                                $row->purchaseReturn
                                    ?->supplier_name,

                            'purchase_return_id' =>
                                (int) $row->purchase_return_id,

                            'return_no' =>
                                $row->purchaseReturn
                                    ?->return_no,

                            'purchase_order_id' =>
                                $row->purchase_order_id !==
                                null
                                    ? (int) $row->purchase_order_id
                                    : null,

                            'amount' =>
                                round(
                                    (float) $row->amount,
                                    2
                                ),

                            'cash_effect' =>
                                (bool) $row->cash_effect,

                            'allocation_effect' =>
                                (bool) $row->allocation_effect,

                            'notes' =>
                                $row->notes,
                        ];
                    }
                );


        $applicationQuery =
            SupplierCreditApplication::query()
                ->with([
                    'sourceSettlement.purchaseReturn',
                    'purchaseOrder',
                ]);


        $this->applyDateFilter(
            query:
                $applicationQuery,

            field:
                'applied_at',

            filters:
                $filters
        );


        if ($supplierId) {
            $applicationQuery->where(
                'supplier_id',
                $supplierId
            );
        }


        $applicationRows =
            $applicationQuery
                ->orderByDesc('applied_at')
                ->orderByDesc('id')
                ->limit($limit)
                ->get()
                ->map(
                    static function (
                        SupplierCreditApplication $row
                    ): array {

                        return [
                            'event_id' =>
                                "credit_application:{$row->id}",

                            'event_type' =>
                                'supplier_credit_application',

                            'event_type_label' =>
                                'Supplier Credit Applied to PO',

                            'occurred_at' =>
                                $row->applied_at
                                    ?->toISOString(),

                            'reference_no' =>
                                $row->application_no,

                            'supplier_id' =>
                                (int) $row->supplier_id,

                            'supplier_name' =>
                                $row->sourceSettlement
                                    ?->purchaseReturn
                                    ?->supplier_name,

                            'purchase_return_id' =>
                                $row->sourceSettlement
                                    ?->purchase_return_id
                                    ? (int) $row
                                        ->sourceSettlement
                                        ->purchase_return_id
                                    : null,

                            'return_no' =>
                                $row->sourceSettlement
                                    ?->purchaseReturn
                                    ?->return_no,

                            'purchase_order_id' =>
                                (int) $row->purchase_order_id,

                            'amount' =>
                                round(
                                    (float) $row->amount,
                                    2
                                ),

                            'cash_effect' =>
                                false,

                            'allocation_effect' =>
                                false,

                            'notes' =>
                                $row->notes,
                        ];
                    }
                );


        return $settlementRows
            ->concat(
                $applicationRows
            )
            ->sortByDesc(
                'occurred_at'
            )
            ->take(
                $limit
            )
            ->values()
            ->all();
    }


    private function sumSettlementType(
        Builder $baseQuery,
        string $type
    ): float {

        return round(
            (float) (clone $baseQuery)
                ->where(
                    'settlement_type',
                    $type
                )
                ->sum('amount'),
            2
        );
    }


    private function applyDateFilter(
        Builder $query,
        string $field,
        array $filters
    ): void {

        if (
            !empty(
                $filters['date_from']
            )
        ) {
            $query->whereDate(
                $field,
                '>=',
                $filters['date_from']
            );
        }


        if (
            !empty(
                $filters['date_to']
            )
        ) {
            $query->whereDate(
                $field,
                '<=',
                $filters['date_to']
            );
        }
    }
}
