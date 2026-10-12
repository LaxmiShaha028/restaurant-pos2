<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseReturnSettlement;
use App\Models\SupplierCreditApplication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class SupplierCreditService
{
    public function getCredits(
        array $filters = []
    ): LengthAwarePaginator {

        $query =
            PurchaseReturnSettlement::query()
                ->with([
                    'purchaseReturn',
                    'supplier',
                    'creditApplications.purchaseOrder',
                    'creditApplications.creator',
                ])
                ->where(
                    'settlement_type',
                    PurchaseReturnSettlement::TYPE_SUPPLIER_CREDIT
                )
                ->whereNull(
                    'parent_settlement_id'
                );


        if (
            !empty(
                $filters['supplier_id']
            )
        ) {
            $query->where(
                'supplier_id',
                (int) $filters['supplier_id']
            );
        }


        if (
            !empty(
                $filters['available_only']
            )
        ) {
            $query->whereRaw(
                '
                    purchase_return_settlements.amount
                    >
                    COALESCE(
                        (
                            SELECT SUM(sca.amount)
                            FROM supplier_credit_applications sca
                            WHERE sca.source_settlement_id =
                                purchase_return_settlements.id
                        ),
                        0
                    )
                '
            );
        }


        $search =
            trim(
                (string) (
                    $filters['search']
                    ??
                    ''
                )
            );


        if ($search !== '') {
            $like = "%{$search}%";

            $query->where(
                static function ($subQuery) use ($like): void {
                    $subQuery
                        ->where(
                            'settlement_no',
                            'like',
                            $like
                        )
                        ->orWhereHas(
                            'purchaseReturn',
                            static function ($returnQuery) use ($like): void {
                                $returnQuery
                                    ->where(
                                        'return_no',
                                        'like',
                                        $like
                                    )
                                    ->orWhere(
                                        'supplier_name',
                                        'like',
                                        $like
                                    );
                            }
                        );
                }
            );
        }


        return $query
            ->orderByDesc('settled_at')
            ->orderByDesc('id')
            ->paginate(
                min(
                    max(
                        (int) (
                            $filters['per_page']
                            ??
                            20
                        ),
                        1
                    ),
                    100
                )
            )
            ->withQueryString();
    }


    public function getApplications(
        array $filters = []
    ): LengthAwarePaginator {

        $query =
            SupplierCreditApplication::query()
                ->with([
                    'purchaseOrder',
                    'sourceSettlement.purchaseReturn',
                    'creator',
                ]);


        if (
            !empty(
                $filters['supplier_id']
            )
        ) {
            $query->where(
                'supplier_id',
                (int) $filters['supplier_id']
            );
        }


        if (
            !empty(
                $filters['purchase_order_id']
            )
        ) {
            $query->where(
                'purchase_order_id',
                (int) $filters['purchase_order_id']
            );
        }


        if (
            !empty(
                $filters['source_settlement_id']
            )
        ) {
            $query->where(
                'source_settlement_id',
                (int) $filters['source_settlement_id']
            );
        }


        return $query
            ->orderByDesc('applied_at')
            ->orderByDesc('id')
            ->paginate(
                min(
                    max(
                        (int) (
                            $filters['per_page']
                            ??
                            20
                        ),
                        1
                    ),
                    100
                )
            )
            ->withQueryString();
    }


    public function getSummary(
        ?int $supplierId = null
    ): array {

        $creditQuery =
            PurchaseReturnSettlement::query()
                ->where(
                    'settlement_type',
                    PurchaseReturnSettlement::TYPE_SUPPLIER_CREDIT
                )
                ->whereNull(
                    'parent_settlement_id'
                );


        $applicationQuery =
            SupplierCreditApplication::query();


        if ($supplierId) {
            $creditQuery->where(
                'supplier_id',
                $supplierId
            );

            $applicationQuery->where(
                'supplier_id',
                $supplierId
            );
        }


        $issued =
            round(
                (float) $creditQuery
                    ->sum('amount'),
                2
            );


        $applied =
            round(
                (float) $applicationQuery
                    ->sum('amount'),
                2
            );


        return [
            'issued_amount' =>
                $issued,

            'issued_amount_formatted' =>
                $this->money($issued),

            'applied_amount' =>
                $applied,

            'applied_amount_formatted' =>
                $this->money($applied),

            'available_amount' =>
                round(
                    max(
                        0,
                        $issued - $applied
                    ),
                    2
                ),

            'available_amount_formatted' =>
                $this->money(
                    max(
                        0,
                        $issued - $applied
                    )
                ),
        ];
    }


    public function getEligiblePurchaseOrders(
        int $sourceSettlementId
    ): Collection {

        $source =
            PurchaseReturnSettlement::query()
                ->with([
                    'purchaseReturn.items',
                ])
                ->findOrFail(
                    $sourceSettlementId
                );


        $this->assertSupplierCreditSource(
            $source
        );


        $excludedPurchaseOrderIds =
            $source
                ->purchaseReturn
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


        $query =
            PurchaseOrder::query()
                ->with([
                    'supplier',
                ])
                ->where(
                    'supplier_id',
                    $source->supplier_id
                )
                ->where(
                    'due_amount',
                    '>',
                    0
                )
                ->where(
                    'status',
                    '!=',
                    'cancelled'
                );


        if ($excludedPurchaseOrderIds !== []) {
            $query->whereNotIn(
                'id',
                $excludedPurchaseOrderIds
            );
        }


        return $query
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->limit(100)
            ->get();
    }


    public function applyCredit(
        array $data,
        User $user
    ): SupplierCreditApplication {

        return DB::transaction(

            function () use (
                $data,
                $user
            ): SupplierCreditApplication {

                $sourceSettlementId =
                    (int) (
                        $data['source_settlement_id']
                        ??
                        0
                    );


                $purchaseOrderId =
                    (int) (
                        $data['purchase_order_id']
                        ??
                        0
                    );


                $source =
                    PurchaseReturnSettlement::query()
                        ->with([
                            'purchaseReturn.items',
                        ])
                        ->lockForUpdate()
                        ->findOrFail(
                            $sourceSettlementId
                        );


                $this->assertSupplierCreditSource(
                    $source
                );


                $applications =
                    SupplierCreditApplication::query()
                        ->where(
                            'source_settlement_id',
                            $source->id
                        )
                        ->lockForUpdate()
                        ->get();


                $sourceCents =
                    $this->moneyToCents(
                        $source->amount
                    );


                $appliedCents =
                    $applications
                        ->sum(
                            fn (
                                SupplierCreditApplication $application
                            ): int =>
                                $this->moneyToCents(
                                    $application->amount
                                )
                        );


                $availableCents =
                    $sourceCents
                    -
                    $appliedCents;


                if ($availableCents <= 0) {
                    throw ValidationException::withMessages([
                        'source_settlement_id' => [
                            'This supplier credit has already been fully used.',
                        ],
                    ]);
                }


                $purchaseOrder =
                    PurchaseOrder::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $purchaseOrderId
                        );


                if (
                    (int) $purchaseOrder->supplier_id
                    !==
                    (int) $source->supplier_id
                ) {
                    throw ValidationException::withMessages([
                        'purchase_order_id' => [
                            'Supplier credit can only be applied to a Purchase Order from the same supplier.',
                        ],
                    ]);
                }


                $status =
                    $purchaseOrder->status?->value
                    ??
                    $purchaseOrder->status
                    ??
                    '';


                if (
                    strtolower(
                        (string) $status
                    )
                    ===
                    'cancelled'
                ) {
                    throw ValidationException::withMessages([
                        'purchase_order_id' => [
                            'Supplier credit cannot be applied to a cancelled Purchase Order.',
                        ],
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Keep Source Return's Original PO Separate
                |--------------------------------------------------------------------------
                |
                | The original return already has "Reduce Supplier Payable" for
                | direct settlement. General Supplier Credit is for another/future
                | Purchase Order.
                |
                */

                $sourceOriginalPoIds =
                    $source
                        ->purchaseReturn
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


                if (
                    in_array(
                        $purchaseOrder->id,
                        $sourceOriginalPoIds,
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'purchase_order_id' => [
                            'Use Reduce Supplier Payable for the original Purchase Order. Supplier Credit is reserved for another/future Purchase Order.',
                        ],
                    ]);
                }


                $totalCents =
                    $this->moneyToCents(
                        $purchaseOrder->total_amount
                    );


                $paidCents =
                    $this->moneyToCents(
                        $purchaseOrder->paid_amount
                    );


                $returnCreditCents =
                    $this->moneyToCents(
                        $purchaseOrder->return_credit_applied
                        ??
                        0
                    );


                $supplierCreditAppliedCents =
                    $this->moneyToCents(
                        $purchaseOrder->supplier_credit_applied
                        ??
                        0
                    );


                $dueCents =
                    $totalCents
                    -
                    $paidCents
                    -
                    $returnCreditCents
                    -
                    $supplierCreditAppliedCents;


                if ($dueCents <= 0) {
                    throw ValidationException::withMessages([
                        'purchase_order_id' => [
                            'The selected Purchase Order has no remaining due.',
                        ],
                    ]);
                }


                $amountCents =
                    $this->moneyToCents(
                        $data['amount']
                        ??
                        null
                    );


                if ($amountCents <= 0) {
                    throw ValidationException::withMessages([
                        'amount' => [
                            'Supplier credit application amount must be greater than zero.',
                        ],
                    ]);
                }


                if ($amountCents > $availableCents) {
                    throw ValidationException::withMessages([
                        'amount' => [
                            'Amount cannot exceed the available supplier credit of '
                            .
                            $this->money(
                                $this->centsToMoney(
                                    $availableCents
                                )
                            )
                            .
                            '.',
                        ],
                    ]);
                }


                if ($amountCents > $dueCents) {
                    throw ValidationException::withMessages([
                        'amount' => [
                            'Amount cannot exceed the Purchase Order due of '
                            .
                            $this->money(
                                $this->centsToMoney(
                                    $dueCents
                                )
                            )
                            .
                            '.',
                        ],
                    ]);
                }


                $appliedAt =
                    !empty(
                        $data['applied_at']
                    )
                        ? Carbon::parse(
                            $data['applied_at']
                        )
                        : now();


                $application =
                    SupplierCreditApplication::create([
                        'application_no' =>
                            'TMP-SCA-'
                            .
                            Str::upper(
                                Str::random(20)
                            ),

                        'source_settlement_id' =>
                            $source->id,

                        'supplier_id' =>
                            $source->supplier_id,

                        'purchase_order_id' =>
                            $purchaseOrder->id,

                        'amount' =>
                            $this->centsToMoney(
                                $amountCents
                            ),

                        'applied_at' =>
                            $appliedAt,

                        'notes' =>
                            $this->nullableText(
                                $data['notes']
                                ??
                                null
                            ),

                        'created_by' =>
                            $user->id,
                    ]);


                $applicationNo =
                    sprintf(
                        'SCA-%s-%06d',
                        $appliedAt->format('Ymd'),
                        $application->id
                    );


                DB::table(
                    'supplier_credit_applications'
                )
                    ->where(
                        'id',
                        $application->id
                    )
                    ->update([
                        'application_no' =>
                            $applicationNo,

                        'updated_at' =>
                            now(),
                    ]);


                $newSupplierCreditAppliedCents =
                    $supplierCreditAppliedCents
                    +
                    $amountCents;


                $newDueCents =
                    $totalCents
                    -
                    $paidCents
                    -
                    $returnCreditCents
                    -
                    $newSupplierCreditAppliedCents;


                DB::table(
                    'purchase_orders'
                )
                    ->where(
                        'id',
                        $purchaseOrder->id
                    )
                    ->update([
                        'supplier_credit_applied' =>
                            $this->centsToMoney(
                                $newSupplierCreditAppliedCents
                            ),

                        'due_amount' =>
                            $this->centsToMoney(
                                max(
                                    0,
                                    $newDueCents
                                )
                            ),

                        'updated_by' =>
                            $user->id,

                        'updated_at' =>
                            now(),
                    ]);


                return $application
                    ->fresh([
                        'purchaseOrder',
                        'sourceSettlement.purchaseReturn',
                        'creator',
                    ]);
            },

            3
        );
    }


    private function assertSupplierCreditSource(
        PurchaseReturnSettlement $source
    ): void {

        if (
            $source->settlement_type
            !==
            PurchaseReturnSettlement::TYPE_SUPPLIER_CREDIT
            ||
            !$source->allocation_effect
            ||
            $source->parent_settlement_id !== null
        ) {
            throw ValidationException::withMessages([
                'source_settlement_id' => [
                    'The selected settlement is not an available Supplier Credit source.',
                ],
            ]);
        }
    }


    private function moneyToCents(
        mixed $value
    ): int {

        if (!is_numeric($value)) {
            return 0;
        }


        return (int) round(
            (float) $value
            *
            100
        );
    }


    private function centsToMoney(
        int $cents
    ): float {

        return round(
            $cents / 100,
            2
        );
    }


    private function money(
        mixed $value
    ): string {

        return '৳ '
            .
            number_format(
                (float) $value,
                2
            );
    }


    private function nullableText(
        mixed $value
    ): ?string {

        if ($value === null) {
            return null;
        }


        $value =
            trim(
                (string) $value
            );


        return $value !== ''
            ? $value
            : null;
    }
}
