<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnSettlement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class PurchaseReturnSettlementService
{
    public function allocate(
        int $purchaseReturnId,
        array $data,
        User $user
    ): PurchaseReturnSettlement {

        return DB::transaction(

            function () use (
                $purchaseReturnId,
                $data,
                $user
            ): PurchaseReturnSettlement {

                $purchaseReturn =
                    PurchaseReturn::query()
                        ->with([
                            'items',
                        ])
                        ->lockForUpdate()
                        ->findOrFail(
                            $purchaseReturnId
                        );


                $existingSettlements =
                    PurchaseReturnSettlement::query()
                        ->where(
                            'purchase_return_id',
                            $purchaseReturn->id
                        )
                        ->lockForUpdate()
                        ->get();


                $targetCents =
                    $this->moneyToCents(
                        $purchaseReturn
                            ->expected_supplier_credit
                    );


                $allocatedCents =
                    $existingSettlements
                        ->where(
                            'allocation_effect',
                            true
                        )
                        ->sum(
                            fn (
                                PurchaseReturnSettlement $settlement
                            ): int =>
                                $this->moneyToCents(
                                    $settlement->amount
                                )
                        );


                $remainingCents =
                    $targetCents
                    -
                    $allocatedCents;


                if ($remainingCents <= 0) {
                    throw ValidationException::withMessages([
                        'amount' => [
                            'This Purchase Return is already fully allocated.',
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
                            'Settlement amount must be greater than zero.',
                        ],
                    ]);
                }


                if ($amountCents > $remainingCents) {
                    throw ValidationException::withMessages([
                        'amount' => [
                            'Settlement amount cannot exceed the remaining unallocated Purchase Return credit of ৳ '
                            .
                            number_format(
                                $this->centsToMoney(
                                    $remainingCents
                                ),
                                2
                            )
                            .
                            '.',
                        ],
                    ]);
                }


                $type =
                    strtolower(
                        trim(
                            (string) (
                                $data['settlement_type']
                                ??
                                ''
                            )
                        )
                    );


                if (
                    !in_array(
                        $type,
                        PurchaseReturnSettlement::allocationTypes(),
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'settlement_type' => [
                            'Invalid Purchase Return settlement type.',
                        ],
                    ]);
                }


                $purchaseOrderId =
                    null;

                $paymentMethod =
                    null;

                $cashEffect =
                    false;


                if (
                    $type
                    ===
                    PurchaseReturnSettlement::TYPE_PAYABLE_OFFSET
                ) {

                    $purchaseOrderId =
                        (int) (
                            $data['purchase_order_id']
                            ??
                            0
                        );


                    if ($purchaseOrderId <= 0) {
                        throw ValidationException::withMessages([
                            'purchase_order_id' => [
                                'Select the original unpaid Purchase Order for payable reduction.',
                            ],
                        ]);
                    }


                    $linkedPurchaseOrderIds =
                        $purchaseReturn
                            ->items
                            ->pluck(
                                'purchase_order_id'
                            )
                            ->filter()
                            ->map(
                                static fn (
                                    mixed $id
                                ): int =>
                                    (int) $id
                            )
                            ->unique();


                    if (
                        !$linkedPurchaseOrderIds
                            ->contains(
                                $purchaseOrderId
                            )
                    ) {
                        throw ValidationException::withMessages([
                            'purchase_order_id' => [
                                'Payable reduction can only be applied to an original Purchase Order linked to this return.',
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
                        (int) $purchaseReturn->supplier_id
                    ) {
                        throw ValidationException::withMessages([
                            'purchase_order_id' => [
                                'The selected Purchase Order belongs to a different supplier.',
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


                    $currentReturnCreditCents =
                        $this->moneyToCents(
                            $purchaseOrder->return_credit_applied
                            ??
                            0
                        );


                    $currentDueCents =
                        $totalCents
                        -
                        $paidCents
                        -
                        $currentReturnCreditCents;


                    if ($currentDueCents <= 0) {
                        throw ValidationException::withMessages([
                            'purchase_order_id' => [
                                'The selected Purchase Order has no supplier due available to reduce.',
                            ],
                        ]);
                    }


                    if ($amountCents > $currentDueCents) {
                        throw ValidationException::withMessages([
                            'amount' => [
                                'Payable reduction cannot exceed the selected Purchase Order due of ৳ '
                                .
                                number_format(
                                    $this->centsToMoney(
                                        $currentDueCents
                                    ),
                                    2
                                )
                                .
                                '.',
                            ],
                        ]);
                    }


                    $newReturnCreditCents =
                        $currentReturnCreditCents
                        +
                        $amountCents;


                    $newDueCents =
                        $totalCents
                        -
                        $paidCents
                        -
                        $newReturnCreditCents;


                    DB::table(
                        'purchase_orders'
                    )
                        ->where(
                            'id',
                            $purchaseOrder->id
                        )
                        ->update([
                            'return_credit_applied' =>
                                $this->centsToMoney(
                                    $newReturnCreditCents
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
                }


                if (
                    $type
                    ===
                    PurchaseReturnSettlement::TYPE_DIRECT_REFUND
                ) {
                    $paymentMethod =
                        $this->resolveRefundMethod(
                            $data['payment_method']
                            ??
                            null
                        );

                    $cashEffect = true;
                }


                $settledAt =
                    !empty($data['settled_at'])
                        ? Carbon::parse(
                            $data['settled_at']
                        )
                        : now();


                $settlement =
                    PurchaseReturnSettlement::create([
                        'settlement_no' =>
                            'TMP-PRS-'
                            .
                            Str::upper(
                                Str::random(20)
                            ),

                        'purchase_return_id' =>
                            $purchaseReturn->id,

                        'supplier_id' =>
                            $purchaseReturn->supplier_id,

                        'settlement_type' =>
                            $type,

                        'amount' =>
                            $this->centsToMoney(
                                $amountCents
                            ),

                        'purchase_order_id' =>
                            $purchaseOrderId
                            ?: null,

                        'parent_settlement_id' =>
                            null,

                        'payment_method' =>
                            $paymentMethod,

                        'transaction_reference' =>
                            $this->nullableText(
                                $data['transaction_reference']
                                ??
                                null
                            ),

                        'settled_at' =>
                            $settledAt,

                        'allocation_effect' =>
                            true,

                        'cash_effect' =>
                            $cashEffect,

                        'notes' =>
                            $this->nullableText(
                                $data['notes']
                                ??
                                null
                            ),

                        'created_by' =>
                            $user->id,
                    ]);


                $settlementNo =
                    sprintf(
                        'PRS-%s-%06d',
                        $settledAt->format('Ymd'),
                        $settlement->id
                    );


                DB::table(
                    'purchase_return_settlements'
                )
                    ->where(
                        'id',
                        $settlement->id
                    )
                    ->update([
                        'settlement_no' =>
                            $settlementNo,

                        'updated_at' =>
                            now(),
                    ]);


                return $settlement
                    ->fresh([
                        'purchaseOrder',
                        'creator',
                        'children.creator',
                        'creditApplications.purchaseOrder',
                        'creditApplications.creator',
                    ]);
            },

            3
        );
    }


    public function collectReceivable(
        int $purchaseReturnId,
        array $data,
        User $user
    ): PurchaseReturnSettlement {

        return DB::transaction(

            function () use (
                $purchaseReturnId,
                $data,
                $user
            ): PurchaseReturnSettlement {

                $purchaseReturn =
                    PurchaseReturn::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $purchaseReturnId
                        );


                $receivableId =
                    (int) (
                        $data['receivable_settlement_id']
                        ??
                        0
                    );


                $receivable =
                    PurchaseReturnSettlement::query()
                        ->whereKey(
                            $receivableId
                        )
                        ->where(
                            'purchase_return_id',
                            $purchaseReturn->id
                        )
                        ->where(
                            'settlement_type',
                            PurchaseReturnSettlement::TYPE_REFUND_RECEIVABLE
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                $children =
                    PurchaseReturnSettlement::query()
                        ->where(
                            'parent_settlement_id',
                            $receivable->id
                        )
                        ->lockForUpdate()
                        ->get();


                $receivableCents =
                    $this->moneyToCents(
                        $receivable->amount
                    );


                $collectedCents =
                    $children
                        ->where(
                            'settlement_type',
                            PurchaseReturnSettlement::TYPE_RECEIVABLE_COLLECTION
                        )
                        ->sum(
                            fn (
                                PurchaseReturnSettlement $settlement
                            ): int =>
                                $this->moneyToCents(
                                    $settlement->amount
                                )
                        );


                $balanceCents =
                    $receivableCents
                    -
                    $collectedCents;


                if ($balanceCents <= 0) {
                    throw ValidationException::withMessages([
                        'receivable_settlement_id' => [
                            'This refund receivable is already fully collected.',
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
                            'Refund collection amount must be greater than zero.',
                        ],
                    ]);
                }


                if ($amountCents > $balanceCents) {
                    throw ValidationException::withMessages([
                        'amount' => [
                            'Refund collection cannot exceed receivable balance of ৳ '
                            .
                            number_format(
                                $this->centsToMoney(
                                    $balanceCents
                                ),
                                2
                            )
                            .
                            '.',
                        ],
                    ]);
                }


                $paymentMethod =
                    $this->resolveRefundMethod(
                        $data['payment_method']
                        ??
                        null
                    );


                $settledAt =
                    !empty($data['settled_at'])
                        ? Carbon::parse(
                            $data['settled_at']
                        )
                        : now();


                $collection =
                    PurchaseReturnSettlement::create([
                        'settlement_no' =>
                            'TMP-PRS-'
                            .
                            Str::upper(
                                Str::random(20)
                            ),

                        'purchase_return_id' =>
                            $purchaseReturn->id,

                        'supplier_id' =>
                            $purchaseReturn->supplier_id,

                        'settlement_type' =>
                            PurchaseReturnSettlement::TYPE_RECEIVABLE_COLLECTION,

                        'amount' =>
                            $this->centsToMoney(
                                $amountCents
                            ),

                        'purchase_order_id' =>
                            null,

                        'parent_settlement_id' =>
                            $receivable->id,

                        'payment_method' =>
                            $paymentMethod,

                        'transaction_reference' =>
                            $this->nullableText(
                                $data['transaction_reference']
                                ??
                                null
                            ),

                        'settled_at' =>
                            $settledAt,

                        'allocation_effect' =>
                            false,

                        'cash_effect' =>
                            true,

                        'notes' =>
                            $this->nullableText(
                                $data['notes']
                                ??
                                null
                            ),

                        'created_by' =>
                            $user->id,
                    ]);


                $settlementNo =
                    sprintf(
                        'PRS-%s-%06d',
                        $settledAt->format('Ymd'),
                        $collection->id
                    );


                DB::table(
                    'purchase_return_settlements'
                )
                    ->where(
                        'id',
                        $collection->id
                    )
                    ->update([
                        'settlement_no' =>
                            $settlementNo,

                        'updated_at' =>
                            now(),
                    ]);


                return $collection
                    ->fresh([
                        'parent',
                        'creator',
                    ]);
            },

            3
        );
    }


    public function getSettlements(
        int $purchaseReturnId
    ): Collection {

        PurchaseReturn::query()
            ->findOrFail(
                $purchaseReturnId
            );


        return PurchaseReturnSettlement::query()
            ->with([
                'purchaseOrder',
                'creator',
                'children.creator',
                'creditApplications.purchaseOrder',
                'creditApplications.creator',
            ])
            ->where(
                'purchase_return_id',
                $purchaseReturnId
            )
            ->whereNull(
                'parent_settlement_id'
            )
            ->orderBy('settled_at')
            ->orderBy('id')
            ->get();
    }


    private function resolveRefundMethod(
        mixed $method
    ): string {

        $method =
            strtolower(
                trim(
                    (string) $method
                )
            );


        if (
            !in_array(
                $method,
                PurchaseReturnSettlement::refundMethods(),
                true
            )
        ) {
            throw ValidationException::withMessages([
                'payment_method' => [
                    'Select a valid refund payment method.',
                ],
            ]);
        }


        return $method;
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
