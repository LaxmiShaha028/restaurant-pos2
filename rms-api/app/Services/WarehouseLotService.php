<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderReceipt;
use App\Models\PurchaseOrderReceiptItem;
use App\Models\RawMaterial;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockLot;
use App\Models\WarehouseStockLotMovement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class WarehouseLotService
{
    private const EPSILON =
        0.00005;


    /*
    |--------------------------------------------------------------------------
    | Create Lot From Purchase Receive
    |--------------------------------------------------------------------------
    |
    | Phase 3 behavior is preserved.
    |
    */

    public function createPurchaseReceiveLot(
        PurchaseOrder $purchaseOrder,
        PurchaseOrderReceipt $receipt,
        PurchaseOrderReceiptItem $receiptItem,
        RawMaterial $rawMaterial,
        float $quantity,
        float $unitCost,
        ?string $supplierBatchNo,
        ?string $manufacturedAt,
        ?string $expiryDate,
        ?string $itemNotes,
        User $user
    ): WarehouseStockLot {

        $quantity =
            $this->normalizePositiveQuantity(
                quantity:
                    $quantity,

                field:
                    'warehouse_lot',

                message:
                    'Purchase receive lot quantity must be greater than zero.'
            );


        $unitCost =
            $this->normalizeNonNegativeCost(
                unitCost:
                    $unitCost,

                field:
                    'warehouse_lot',

                message:
                    'Purchase receive lot unit cost cannot be negative.'
            );


        $temporaryLotNo =
            'TMP-LOT-'
            .
            Str::upper(
                Str::random(
                    20
                )
            );


        $lot =
            WarehouseStockLot::create([

                'lot_no' =>
                    $temporaryLotNo,

                'raw_material_id' =>
                    $rawMaterial->id,

                'source_type' =>
                    WarehouseStockLot::SOURCE_PURCHASE_RECEIVE,

                'supplier_id' =>
                    $purchaseOrder->supplier_id,

                'purchase_order_id' =>
                    $purchaseOrder->id,

                'purchase_order_receipt_id' =>
                    $receipt->id,

                'purchase_order_receipt_item_id' =>
                    $receiptItem->id,

                'supplier_batch_no' =>
                    $supplierBatchNo,

                'manufactured_at' =>
                    $manufacturedAt,

                'expiry_date' =>
                    $expiryDate,

                'received_quantity' =>
                    $quantity,

                'remaining_quantity' =>
                    $quantity,

                'unit_cost' =>
                    $unitCost,

                'unit' =>
                    $rawMaterial->base_unit,

                'received_at' =>
                    $receipt->received_at,

                'notes' =>
                    $this->buildPurchaseReceiveLotNotes(
                        purchaseOrder:
                            $purchaseOrder,

                        receipt:
                            $receipt,

                        receiptItem:
                            $receiptItem,

                        supplierBatchNo:
                            $supplierBatchNo,

                        expiryDate:
                            $expiryDate,

                        itemNotes:
                            $itemNotes
                    ),

                'created_by' =>
                    $user->id,

                'updated_by' =>
                    $user->id,

            ]);


        $datePart =
            $receipt
                ->received_at
                ?->format(
                    'Ymd'
                )
            ??
            now()
                ->format(
                    'Ymd'
                );


        $lotNo =
            sprintf(
                'LOT-%s-%06d',
                $datePart,
                $lot->id
            );


        DB::table(
            'warehouse_stock_lots'
        )
            ->where(
                'id',
                $lot->id
            )
            ->update([

                'lot_no' =>
                    $lotNo,

                'updated_at' =>
                    now(),

            ]);


        $lot->refresh();


        WarehouseStockLotMovement::create([

            'warehouse_stock_lot_id' =>
                $lot->id,

            'raw_material_id' =>
                $rawMaterial->id,

            'movement_type' =>
                WarehouseStockLotMovement::TYPE_PURCHASE_RECEIVE,

            'direction' =>
                WarehouseStockLotMovement::DIRECTION_IN,

            'quantity' =>
                $quantity,

            'quantity_before' =>
                0,

            'quantity_after' =>
                $quantity,

            'unit_cost' =>
                $unitCost,

            'unit' =>
                $rawMaterial->base_unit,

            'reference_type' =>
                PurchaseOrderReceipt::class,

            'reference_id' =>
                $receipt->id,

            'occurred_at' =>
                $receipt->received_at
                ??
                now(),

            'notes' =>
                $this->buildPurchaseReceiveMovementNotes(
                    lot:
                        $lot,

                    receipt:
                        $receipt,

                    supplierBatchNo:
                        $supplierBatchNo,

                    expiryDate:
                        $expiryDate
                ),

            'created_by' =>
                $user->id,

        ]);


        $this->assertMaterialBalance(
            rawMaterialId:
                $rawMaterial->id
        );


        return $lot;
    }


    /*
    |--------------------------------------------------------------------------
    | Create Lot From Warehouse Adjustment IN
    |--------------------------------------------------------------------------
    |
    | Manual stock increase has no supplier / PO / GRN by definition.
    |
    | Therefore it becomes a traceable UNKNOWN-EXPIRY adjustment lot.
    | Dated purchase lots will still be consumed first by FEFO.
    |
    */

    public function createWarehouseAdjustmentInLot(
        RawMaterial $rawMaterial,
        float $quantity,
        float $unitCost,
        StockMovement $stockMovement,
        ?string $notes,
        User $user
    ): WarehouseStockLot {

        $quantity =
            $this->normalizePositiveQuantity(
                quantity:
                    $quantity,

                field:
                    'quantity',

                message:
                    'Warehouse adjustment quantity must be greater than zero.'
            );


        $unitCost =
            $this->normalizeNonNegativeCost(
                unitCost:
                    $unitCost,

                field:
                    'unit_cost',

                message:
                    'Warehouse adjustment unit cost cannot be negative.'
            );


        $temporaryLotNo =
            'TMP-ADJ-'
            .
            Str::upper(
                Str::random(
                    20
                )
            );


        $occurredAt =
            $stockMovement->created_at
            ??
            now();


        $lot =
            WarehouseStockLot::create([

                'lot_no' =>
                    $temporaryLotNo,

                'raw_material_id' =>
                    $rawMaterial->id,

                'source_type' =>
                    WarehouseStockLot::SOURCE_WAREHOUSE_ADJUSTMENT_IN,

                'supplier_id' =>
                    null,

                'purchase_order_id' =>
                    null,

                'purchase_order_receipt_id' =>
                    null,

                'purchase_order_receipt_item_id' =>
                    null,

                'supplier_batch_no' =>
                    null,

                'manufactured_at' =>
                    null,

                'expiry_date' =>
                    null,

                'received_quantity' =>
                    $quantity,

                'remaining_quantity' =>
                    $quantity,

                'unit_cost' =>
                    $unitCost,

                'unit' =>
                    $rawMaterial->base_unit,

                'received_at' =>
                    $occurredAt,

                'notes' =>
                    $this->cleanText(
                        implode(
                            ' | ',
                            array_filter([
                                'Created from warehouse adjustment IN.',
                                $notes,
                            ])
                        )
                    ),

                'created_by' =>
                    $user->id,

                'updated_by' =>
                    $user->id,

            ]);


        $datePart =
            Carbon::parse(
                $occurredAt
            )
                ->format(
                    'Ymd'
                );


        $lotNo =
            sprintf(
                'LOT-ADJ-%s-%06d',
                $datePart,
                $lot->id
            );


        DB::table(
            'warehouse_stock_lots'
        )
            ->where(
                'id',
                $lot->id
            )
            ->update([

                'lot_no' =>
                    $lotNo,

                'updated_at' =>
                    now(),

            ]);


        $lot->refresh();


        WarehouseStockLotMovement::create([

            'warehouse_stock_lot_id' =>
                $lot->id,

            'raw_material_id' =>
                $rawMaterial->id,

            'movement_type' =>
                WarehouseStockLotMovement::TYPE_WAREHOUSE_ADJUSTMENT_IN,

            'direction' =>
                WarehouseStockLotMovement::DIRECTION_IN,

            'quantity' =>
                $quantity,

            'quantity_before' =>
                0,

            'quantity_after' =>
                $quantity,

            'unit_cost' =>
                $unitCost,

            'unit' =>
                $rawMaterial->base_unit,

            'reference_type' =>
                StockMovement::class,

            'reference_id' =>
                $stockMovement->id,

            'occurred_at' =>
                $occurredAt,

            'notes' =>
                $this->cleanText(
                    implode(
                        ' | ',
                        array_filter([
                            "Warehouse adjustment IN created {$lot->lot_no}.",
                            $notes,
                        ])
                    )
                ),

            'created_by' =>
                $user->id,

        ]);


        $this->assertMaterialBalance(
            rawMaterialId:
                $rawMaterial->id
        );


        return $lot;
    }


    /*
    |--------------------------------------------------------------------------
    | Transferable Quantity
    |--------------------------------------------------------------------------
    |
    | Normal Restaurant Transfer MUST NOT use already-expired lots.
    |
    | Legacy / adjustment lots with unknown expiry remain usable.
    |
    */

    public function transferableQuantity(
        int $rawMaterialId
    ): float {

        return round(
            (float) WarehouseStockLot::query()

                ->where(
                    'raw_material_id',
                    $rawMaterialId
                )

                ->where(
                    'remaining_quantity',
                    '>',
                    0
                )

                ->where(
                    static function (
                        $query
                    ): void {

                        $query
                            ->whereNull(
                                'expiry_date'
                            )
                            ->orWhereDate(
                                'expiry_date',
                                '>=',
                                now()
                                    ->toDateString()
                            );
                    }
                )

                ->sum(
                    'remaining_quantity'
                ),
            4
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Expired Quantity
    |--------------------------------------------------------------------------
    */

    public function expiredQuantity(
        int $rawMaterialId
    ): float {

        return round(
            (float) WarehouseStockLot::query()

                ->where(
                    'raw_material_id',
                    $rawMaterialId
                )

                ->where(
                    'remaining_quantity',
                    '>',
                    0
                )

                ->whereDate(
                    'expiry_date',
                    '<',
                    now()
                        ->toDateString()
                )

                ->sum(
                    'remaining_quantity'
                ),
            4
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FEFO Stock OUT
    |--------------------------------------------------------------------------
    |
    | FEFO = First Expire, First Out.
    |
    | Normal transfer:
    |     allowExpired = false
    |
    | Generic physical warehouse correction:
    |     allowExpired = true
    |
    | Unknown-expiry legacy/adjustment lots are sorted AFTER dated lots.
    |
    | This method assumes the aggregate WarehouseStock row has ALREADY been
    | decreased inside the same DB transaction. It then decreases the physical
    | lots and finally proves aggregate quantity == sum(lot balances).
    |
    */

    public function allocateFefoOut(
        RawMaterial $rawMaterial,
        float $quantity,
        string $movementType,
        string $referenceType,
        int $referenceId,
        mixed $occurredAt,
        ?string $notes,
        User $user,
        bool $allowExpired = false,
        string $validationField = 'quantity'
    ): array {

        $quantity =
            $this->normalizePositiveQuantity(
                quantity:
                    $quantity,

                field:
                    $validationField,

                message:
                    'Stock-out quantity must be greater than zero.'
            );


        if (
            !in_array(
                $movementType,
                [
                    WarehouseStockLotMovement::TYPE_TRANSFER_OUT,
                    WarehouseStockLotMovement::TYPE_WAREHOUSE_ADJUSTMENT_OUT,
                    WarehouseStockLotMovement::TYPE_PURCHASE_RETURN_OUT,
                ],
                true
            )
        ) {

            throw ValidationException::withMessages([

                $validationField => [
                    'The selected warehouse lot stock-out movement type is invalid.',
                ],

            ]);
        }


        if (
            trim(
                $referenceType
            )
            ===
            ''
            ||
            $referenceId
            <=
            0
        ) {

            throw ValidationException::withMessages([

                $validationField => [
                    'A valid stock-out reference is required.',
                ],

            ]);
        }


        $occurredAt =
            $occurredAt
                ? Carbon::parse(
                    $occurredAt
                )
                : now();


        $lotsQuery =
            WarehouseStockLot::query()

                ->where(
                    'raw_material_id',
                    $rawMaterial->id
                )

                ->where(
                    'remaining_quantity',
                    '>',
                    0
                );


        if (
            !$allowExpired
        ) {

            $lotsQuery->where(
                static function (
                    $query
                ): void {

                    $query
                        ->whereNull(
                            'expiry_date'
                        )
                        ->orWhereDate(
                            'expiry_date',
                            '>=',
                            now()
                                ->toDateString()
                        );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FEFO Ordering
        |--------------------------------------------------------------------------
        |
        | 1. Dated lots before unknown-expiry lots.
        | 2. Earliest expiry first.
        | 3. Oldest received lot first.
        | 4. ID for deterministic final ordering.
        |
        | When allowExpired = true, already-expired dated lots naturally come
        | before future-dated lots, making physical correction oldest-first.
        |
        */

        $lots =
            $lotsQuery

                ->orderByRaw(
                    'CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END ASC'
                )

                ->orderBy(
                    'expiry_date'
                )

                ->orderByRaw(
                    'CASE WHEN received_at IS NULL THEN 1 ELSE 0 END ASC'
                )

                ->orderBy(
                    'received_at'
                )

                ->orderBy(
                    'id'
                )

                ->lockForUpdate()

                ->get();


        $available =
            round(
                (float) $lots->sum(
                    static fn (
                        WarehouseStockLot $lot
                    ): float =>
                        (float) $lot
                            ->remaining_quantity
                ),
                4
            );


        if (
            $quantity
            >
            $available
            +
            self::EPSILON
        ) {

            $expiredQuantity =
                $this->expiredQuantity(
                    rawMaterialId:
                        $rawMaterial->id
                );


            $message =
                $allowExpired

                    ? sprintf(
                        'Insufficient warehouse lot balance for "%s". Available lot quantity is %.4f %s.',
                        $rawMaterial->material_name,
                        $available,
                        $rawMaterial->base_unit
                    )

                    : sprintf(
                        'Insufficient transferable stock for "%s". Transferable quantity is %.4f %s. Expired blocked quantity is %.4f %s.',
                        $rawMaterial->material_name,
                        $available,
                        $rawMaterial->base_unit,
                        $expiredQuantity,
                        $rawMaterial->base_unit
                    );


            throw ValidationException::withMessages([

                $validationField => [
                    $message,
                ],

            ]);
        }


        $remainingToAllocate =
            $quantity;


        $allocations =
            [];


        foreach (
            $lots
            as
            $lot
        ) {

            if (
                $remainingToAllocate
                <=
                self::EPSILON
            ) {
                break;
            }


            $lotBefore =
                round(
                    (float) $lot
                        ->remaining_quantity,
                    4
                );


            if (
                $lotBefore
                <=
                self::EPSILON
            ) {
                continue;
            }


            $allocated =
                round(
                    min(
                        $lotBefore,
                        $remainingToAllocate
                    ),
                    4
                );


            $lotAfter =
                round(
                    $lotBefore
                    -
                    $allocated,
                    4
                );


            if (
                $lotAfter
                <
                -self::EPSILON
            ) {

                throw ValidationException::withMessages([

                    $validationField => [
                        "Lot {$lot->lot_no} would become negative.",
                    ],

                ]);
            }


            if (
                abs(
                    $lotAfter
                )
                <=
                self::EPSILON
            ) {

                $lotAfter =
                    0.0;
            }


            $lot->update([

                'remaining_quantity' =>
                    $lotAfter,

                'updated_by' =>
                    $user->id,

            ]);


            WarehouseStockLotMovement::create([

                'warehouse_stock_lot_id' =>
                    $lot->id,

                'raw_material_id' =>
                    $rawMaterial->id,

                'movement_type' =>
                    $movementType,

                'direction' =>
                    WarehouseStockLotMovement::DIRECTION_OUT,

                'quantity' =>
                    $allocated,

                'quantity_before' =>
                    $lotBefore,

                'quantity_after' =>
                    $lotAfter,

                /*
                |--------------------------------------------------------------------------
                | Original Lot Cost
                |--------------------------------------------------------------------------
                |
                | This is physical/source traceability cost, not the current
                | aggregate weighted-average transfer valuation.
                |
                */

                'unit_cost' =>
                    (float) $lot
                        ->unit_cost,

                'unit' =>
                    $lot->unit,

                'reference_type' =>
                    $referenceType,

                'reference_id' =>
                    $referenceId,

                'occurred_at' =>
                    $occurredAt,

                'notes' =>
                    $this->buildFefoMovementNotes(
                        lot:
                            $lot,

                        notes:
                            $notes,

                        allowExpired:
                            $allowExpired
                    ),

                'created_by' =>
                    $user->id,

            ]);


            $allocations[] = [

                'lot_id' =>
                    $lot->id,

                'lot_no' =>
                    $lot->lot_no,

                'supplier_batch_no' =>
                    $lot->supplier_batch_no,

                'expiry_date' =>
                    $lot->expiry_date
                        ?->format(
                            'Y-m-d'
                        ),

                'quantity' =>
                    $allocated,

                'quantity_before' =>
                    $lotBefore,

                'quantity_after' =>
                    $lotAfter,

                'original_unit_cost' =>
                    round(
                        (float) $lot
                            ->unit_cost,
                        4
                    ),

            ];


            $remainingToAllocate =
                round(
                    $remainingToAllocate
                    -
                    $allocated,
                    4
                );
        }


        if (
            $remainingToAllocate
            >
            self::EPSILON
        ) {

            throw ValidationException::withMessages([

                $validationField => [
                    'Warehouse FEFO allocation could not satisfy the requested quantity.',
                ],

            ]);
        }


        $this->assertMaterialBalance(
            rawMaterialId:
                $rawMaterial->id
        );


        return $allocations;
    }


    /*
    |--------------------------------------------------------------------------
    | Deduct Exact Lot for Purchase Return
    |--------------------------------------------------------------------------
    |
    | Restaurant Transfer uses FEFO.
    | Purchase Return returns the exact lot selected by the manager.
    |
    | Aggregate WarehouseStock must already be reduced by the caller inside
    | the same transaction. This method reduces the exact lot and then proves
    | aggregate warehouse quantity still equals lot remaining total.
    |
    */

    public function deductExactPurchaseReturnLot(
        int $lotId,
        RawMaterial $rawMaterial,
        float $quantity,
        string $referenceType,
        int $referenceId,
        mixed $occurredAt,
        ?string $notes,
        User $user,
        string $validationField = 'quantity'
    ): array {

        $quantity =
            $this->normalizePositiveQuantity(
                quantity:
                    $quantity,

                field:
                    $validationField,

                message:
                    'Purchase Return quantity must be greater than zero.'
            );


        $lot =
            WarehouseStockLot::query()

                ->whereKey(
                    $lotId
                )

                ->lockForUpdate()

                ->firstOrFail();


        if (
            (int) $lot->raw_material_id
            !==
            (int) $rawMaterial->id
        ) {

            throw ValidationException::withMessages([

                $validationField => [
                    'The selected warehouse lot does not belong to this raw material.',
                ],

            ]);
        }


        $lotBefore =
            round(
                (float) $lot->remaining_quantity,
                4
            );


        if (
            $quantity
            >
            $lotBefore
            +
            self::EPSILON
        ) {

            throw ValidationException::withMessages([

                $validationField => [

                    sprintf(
                        'Return quantity cannot exceed lot balance. Available in %s is %.4f %s.',
                        $lot->lot_no,
                        $lotBefore,
                        $lot->unit
                    ),

                ],

            ]);
        }


        $lotAfter =
            round(
                $lotBefore
                -
                $quantity,
                4
            );


        if (
            abs(
                $lotAfter
            )
            <=
            self::EPSILON
        ) {

            $lotAfter =
                0.0;
        }


        $lot->update([

            'remaining_quantity' =>
                $lotAfter,

            'updated_by' =>
                $user->id,

        ]);


        WarehouseStockLotMovement::create([

            'warehouse_stock_lot_id' =>
                $lot->id,

            'raw_material_id' =>
                $rawMaterial->id,

            'movement_type' =>
                WarehouseStockLotMovement::TYPE_PURCHASE_RETURN_OUT,

            'direction' =>
                WarehouseStockLotMovement::DIRECTION_OUT,

            'quantity' =>
                $quantity,

            'quantity_before' =>
                $lotBefore,

            'quantity_after' =>
                $lotAfter,

            'unit_cost' =>
                (float) $lot->unit_cost,

            'unit' =>
                $lot->unit,

            'reference_type' =>
                $referenceType,

            'reference_id' =>
                $referenceId,

            'occurred_at' =>
                $occurredAt
                    ? Carbon::parse(
                        $occurredAt
                    )
                    : now(),

            'notes' =>
                $this->cleanText(
                    implode(
                        ' | ',
                        array_filter([
                            "Purchase Return exact lot: {$lot->lot_no}",
                            $lot->supplier_batch_no
                                ? "Supplier batch: {$lot->supplier_batch_no}"
                                : null,
                            $lot->expiry_date
                                ? 'Expiry: '
                                    .
                                    $lot->expiry_date
                                        ->format(
                                            'Y-m-d'
                                        )
                                : 'Expiry: unknown',
                            $notes,
                        ])
                    )
                ),

            'created_by' =>
                $user->id,

        ]);


        $this->assertMaterialBalance(
            rawMaterialId:
                $rawMaterial->id
        );


        return [

            'lot_id' =>
                $lot->id,

            'lot_no' =>
                $lot->lot_no,

            'quantity_before' =>
                $lotBefore,

            'quantity_after' =>
                $lotAfter,

            'quantity' =>
                $quantity,

            'original_unit_cost' =>
                round(
                    (float) $lot->unit_cost,
                    4
                ),

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Material Reconciliation
    |--------------------------------------------------------------------------
    */

    public function assertMaterialBalance(
        int $rawMaterialId
    ): void {

        $warehouseStock =
            WarehouseStock::withTrashed()

                ->where(
                    'raw_material_id',
                    $rawMaterialId
                )

                ->lockForUpdate()

                ->first();


        $warehouseQuantity =
            round(
                (float) (
                    $warehouseStock
                        ?->quantity
                    ??
                    0
                ),
                4
            );


        $lotQuantity =
            round(
                (float) WarehouseStockLot::query()

                    ->where(
                        'raw_material_id',
                        $rawMaterialId
                    )

                    ->sum(
                        'remaining_quantity'
                    ),
                4
            );


        $difference =
            round(
                $warehouseQuantity
                -
                $lotQuantity,
                4
            );


        if (
            abs(
                $difference
            )
            <=
            self::EPSILON
        ) {
            return;
        }


        throw ValidationException::withMessages([

            'warehouse_lot_reconciliation' => [

                sprintf(
                    'Warehouse lot reconciliation failed for raw material #%d. Warehouse quantity is %.4f but lot quantity is %.4f. The operation was rolled back.',
                    $rawMaterialId,
                    $warehouseQuantity,
                    $lotQuantity
                ),

            ],

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Normalization
    |--------------------------------------------------------------------------
    */

    private function normalizePositiveQuantity(
        float $quantity,
        string $field,
        string $message
    ): float {

        $quantity =
            round(
                $quantity,
                4
            );


        if (
            $quantity
            <=
            0
        ) {

            throw ValidationException::withMessages([

                $field => [
                    $message,
                ],

            ]);
        }


        return $quantity;
    }


    private function normalizeNonNegativeCost(
        float $unitCost,
        string $field,
        string $message
    ): float {

        $unitCost =
            round(
                $unitCost,
                4
            );


        if (
            $unitCost
            <
            0
        ) {

            throw ValidationException::withMessages([

                $field => [
                    $message,
                ],

            ]);
        }


        return $unitCost;
    }


    private function cleanText(
        ?string $value
    ): ?string {

        if (
            $value ===
            null
        ) {
            return null;
        }


        $value =
            trim(
                $value
            );


        if (
            $value ===
            ''
        ) {
            return null;
        }


        return mb_substr(
            $value,
            0,
            2000
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Notes
    |--------------------------------------------------------------------------
    */

    private function buildPurchaseReceiveLotNotes(
        PurchaseOrder $purchaseOrder,
        PurchaseOrderReceipt $receipt,
        PurchaseOrderReceiptItem $receiptItem,
        ?string $supplierBatchNo,
        ?string $expiryDate,
        ?string $itemNotes
    ): string {

        $parts = [

            "Created from purchase receive {$receipt->receipt_no}",

            "Purchase order #{$purchaseOrder->id}",

            "Receipt item #{$receiptItem->id}",

        ];


        if (
            $supplierBatchNo
        ) {

            $parts[] =
                "Supplier batch: {$supplierBatchNo}";
        }


        if (
            $expiryDate
        ) {

            $parts[] =
                "Expiry: {$expiryDate}";
        }


        if (
            $itemNotes
        ) {

            $parts[] =
                "Item note: {$itemNotes}";
        }


        return mb_substr(
            implode(
                ' | ',
                $parts
            ),
            0,
            2000
        );
    }


    private function buildPurchaseReceiveMovementNotes(
        WarehouseStockLot $lot,
        PurchaseOrderReceipt $receipt,
        ?string $supplierBatchNo,
        ?string $expiryDate
    ): string {

        $parts = [

            "Lot created from {$receipt->receipt_no}",

            "Lot: {$lot->lot_no}",

        ];


        if (
            $supplierBatchNo
        ) {

            $parts[] =
                "Supplier batch: {$supplierBatchNo}";
        }


        if (
            $expiryDate
        ) {

            $parts[] =
                "Expiry: {$expiryDate}";
        }


        return mb_substr(
            implode(
                ' | ',
                $parts
            ),
            0,
            2000
        );
    }


    private function buildFefoMovementNotes(
        WarehouseStockLot $lot,
        ?string $notes,
        bool $allowExpired
    ): string {

        $parts = [

            "FEFO lot allocation: {$lot->lot_no}",

        ];


        if (
            $lot->supplier_batch_no
        ) {

            $parts[] =
                "Supplier batch: {$lot->supplier_batch_no}";
        }


        if (
            $lot->expiry_date
        ) {

            $parts[] =
                'Expiry: '
                .
                $lot->expiry_date
                    ->format(
                        'Y-m-d'
                    );
        } else {

            $parts[] =
                'Expiry: unknown';
        }


        if (
            $allowExpired
        ) {

            $parts[] =
                'Expired lots permitted for physical adjustment.';
        }


        if (
            $notes
        ) {

            $parts[] =
                trim(
                    $notes
                );
        }


        return mb_substr(
            implode(
                ' | ',
                array_filter(
                    $parts
                )
            ),
            0,
            2000
        );
    }
}
