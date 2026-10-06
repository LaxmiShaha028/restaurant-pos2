<?php

namespace App\Services;

use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\RawMaterial;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockLot;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class PurchaseReturnService
{
    private const EPSILON = 0.00005;

    public function __construct(
        private readonly WarehouseLotService $warehouseLotService
    ) {
    }

    public function getReturns(array $filters = []): LengthAwarePaginator
    {
        $query = PurchaseReturn::query()
            ->with([
                'items.purchaseOrder',
                'settlements.purchaseOrder',
                'settlements.creator',
                'settlements.children.creator',
                'settlements.creditApplications.purchaseOrder',
                'settlements.creditApplications.creator',
                'creator',
            ]);

        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(
                static function ($searchQuery) use ($search): void {
                    $like = "%{$search}%";

                    $searchQuery
                        ->where('return_no', 'like', $like)
                        ->orWhere('supplier_name', 'like', $like)
                        ->orWhereHas(
                            'items',
                            static function ($itemQuery) use ($like): void {
                                $itemQuery
                                    ->where('item_name', 'like', $like)
                                    ->orWhere('lot_no', 'like', $like)
                                    ->orWhere('supplier_batch_no', 'like', $like);
                            }
                        );
                }
            );
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', (int) $filters['supplier_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('returned_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('returned_at', '<=', $filters['date_to']);
        }

        return $query
            ->orderByDesc('returned_at')
            ->orderByDesc('id')
            ->paginate(
                min(
                    max(
                        (int) ($filters['per_page'] ?? 20),
                        1
                    ),
                    100
                )
            )
            ->withQueryString();
    }

    public function getReturn(int $returnId): PurchaseReturn
    {
        return PurchaseReturn::query()
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
            ->findOrFail($returnId);
    }

    public function createReturn(array $data, User $user): PurchaseReturn
    {
        return DB::transaction(
            function () use ($data, $user): PurchaseReturn {
                $submittedItems = collect($data['items'] ?? [])->values();

                if ($submittedItems->isEmpty()) {
                    throw ValidationException::withMessages([
                        'items' => [
                            'At least one warehouse lot is required for Purchase Return.',
                        ],
                    ]);
                }

                $lotIds = $submittedItems
                    ->pluck('warehouse_stock_lot_id')
                    ->map(static fn (mixed $id): int => (int) $id)
                    ->values();

                /*
                |--------------------------------------------------------------------------
                | Lock selected lots in stable order
                |--------------------------------------------------------------------------
                */

                $lockedLots = WarehouseStockLot::query()
                    ->whereIn(
                        'id',
                        $lotIds->sort()->values()->all()
                    )
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($lockedLots->count() !== $lotIds->unique()->count()) {
                    throw ValidationException::withMessages([
                        'items' => [
                            'One or more selected warehouse lots no longer exist.',
                        ],
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | One Purchase Return = one supplier
                |--------------------------------------------------------------------------
                */

                $supplierIds = $lockedLots
                    ->pluck('supplier_id')
                    ->map(static fn (mixed $id): int => (int) ($id ?? 0))
                    ->unique()
                    ->values();

                if (
                    $supplierIds->contains(
                        static fn (int $id): bool => $id <= 0
                    )
                ) {
                    throw ValidationException::withMessages([
                        'supplier' => [
                            'Every returned lot must be linked to a supplier. Add the supplier to legacy/adjustment lots before Purchase Return.',
                        ],
                    ]);
                }

                if ($supplierIds->count() !== 1) {
                    throw ValidationException::withMessages([
                        'supplier' => [
                            'All items in one Purchase Return must belong to the same supplier.',
                        ],
                    ]);
                }

                $supplierId = (int) $supplierIds->first();

                $supplier = Supplier::withTrashed()
                    ->findOrFail($supplierId);

                $rawMaterialIds = $lockedLots
                    ->pluck('raw_material_id')
                    ->map(static fn (mixed $id): int => (int) $id)
                    ->unique()
                    ->sort()
                    ->values();

                $materials = RawMaterial::withTrashed()
                    ->whereIn('id', $rawMaterialIds->all())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $warehouseStocks = WarehouseStock::withTrashed()
                    ->whereIn('raw_material_id', $rawMaterialIds->all())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('raw_material_id');

                /*
                |--------------------------------------------------------------------------
                | Prepare immutable item snapshots before any mutation
                |--------------------------------------------------------------------------
                */

                $preparedItems = [];
                $warehouseWorkingQuantities = [];

                foreach ($warehouseStocks as $rawMaterialId => $stock) {
                    $warehouseWorkingQuantities[(int) $rawMaterialId] =
                        round((float) $stock->quantity, 4);
                }

                $totalExpectedSupplierCredit = 0.0;
                $totalInventoryValueReduction = 0.0;

                foreach ($submittedItems as $index => $submittedItem) {
                    if (!is_array($submittedItem)) {
                        throw ValidationException::withMessages([
                            "items.{$index}" => [
                                'Invalid Purchase Return item.',
                            ],
                        ]);
                    }

                    $lotId = (int) (
                        $submittedItem['warehouse_stock_lot_id'] ?? 0
                    );

                    /** @var WarehouseStockLot|null $lot */
                    $lot = $lockedLots->get($lotId);

                    if (!$lot) {
                        throw ValidationException::withMessages([
                            "items.{$index}.warehouse_stock_lot_id" => [
                                'The selected warehouse lot was not found.',
                            ],
                        ]);
                    }

                    if ((int) $lot->supplier_id !== $supplierId) {
                        throw ValidationException::withMessages([
                            "items.{$index}.warehouse_stock_lot_id" => [
                                'The selected warehouse lot belongs to a different supplier.',
                            ],
                        ]);
                    }

                    $quantity = round(
                        (float) ($submittedItem['quantity'] ?? 0),
                        4
                    );

                    if ($quantity <= 0) {
                        throw ValidationException::withMessages([
                            "items.{$index}.quantity" => [
                                'Return quantity must be greater than zero.',
                            ],
                        ]);
                    }

                    $lotQuantityBefore = round(
                        (float) $lot->remaining_quantity,
                        4
                    );

                    if ($quantity > $lotQuantityBefore + self::EPSILON) {
                        throw ValidationException::withMessages([
                            "items.{$index}.quantity" => [
                                sprintf(
                                    'Return quantity cannot exceed lot balance. Available in %s is %.4f %s.',
                                    $lot->lot_no,
                                    $lotQuantityBefore,
                                    $lot->unit
                                ),
                            ],
                        ]);
                    }

                    $rawMaterialId = (int) $lot->raw_material_id;

                    /** @var RawMaterial|null $rawMaterial */
                    $rawMaterial = $materials->get($rawMaterialId);

                    if (!$rawMaterial) {
                        throw ValidationException::withMessages([
                            "items.{$index}.warehouse_stock_lot_id" => [
                                'The raw material linked to this lot was not found.',
                            ],
                        ]);
                    }

                    /** @var WarehouseStock|null $warehouseStock */
                    $warehouseStock = $warehouseStocks->get($rawMaterialId);

                    if (!$warehouseStock) {
                        throw ValidationException::withMessages([
                            "items.{$index}.quantity" => [
                                'Warehouse aggregate stock was not found for this material.',
                            ],
                        ]);
                    }

                    $warehouseQuantityBefore = round(
                        (float) (
                            $warehouseWorkingQuantities[$rawMaterialId] ?? 0
                        ),
                        4
                    );

                    if ($quantity > $warehouseQuantityBefore + self::EPSILON) {
                        throw ValidationException::withMessages([
                            "items.{$index}.quantity" => [
                                sprintf(
                                    'Return quantity cannot exceed warehouse stock. Available quantity is %.4f %s.',
                                    $warehouseQuantityBefore,
                                    $rawMaterial->base_unit
                                ),
                            ],
                        ]);
                    }

                    $warehouseQuantityAfter = round(
                        $warehouseQuantityBefore - $quantity,
                        4
                    );

                    $warehouseWorkingQuantities[$rawMaterialId] =
                        $warehouseQuantityAfter;

                    $lotQuantityAfter = round(
                        $lotQuantityBefore - $quantity,
                        4
                    );

                    $returnUnitCost = round(
                        (float) $lot->unit_cost,
                        4
                    );

                    $inventoryAverageUnitCost = round(
                        (float) $warehouseStock->average_unit_cost,
                        4
                    );

                    $expectedSupplierCredit = round(
                        $quantity * $returnUnitCost,
                        4
                    );

                    $inventoryValueReduction = round(
                        $quantity * $inventoryAverageUnitCost,
                        4
                    );

                    $valuationVariance = round(
                        $expectedSupplierCredit - $inventoryValueReduction,
                        4
                    );

                    $totalExpectedSupplierCredit = round(
                        $totalExpectedSupplierCredit + $expectedSupplierCredit,
                        4
                    );

                    $totalInventoryValueReduction = round(
                        $totalInventoryValueReduction + $inventoryValueReduction,
                        4
                    );

                    $preparedItems[] = [
                        'request_index' => $index,
                        'lot' => $lot,
                        'raw_material' => $rawMaterial,
                        'warehouse_stock' => $warehouseStock,
                        'quantity' => $quantity,
                        'lot_quantity_before' => $lotQuantityBefore,
                        'lot_quantity_after' => $lotQuantityAfter,
                        'warehouse_quantity_before' => $warehouseQuantityBefore,
                        'warehouse_quantity_after' => $warehouseQuantityAfter,
                        'return_unit_cost' => $returnUnitCost,
                        'expected_supplier_credit' => $expectedSupplierCredit,
                        'inventory_average_unit_cost' => $inventoryAverageUnitCost,
                        'inventory_value_reduction' => $inventoryValueReduction,
                        'valuation_variance' => $valuationVariance,
                        'reason' => strtolower(
                            trim((string) ($submittedItem['reason'] ?? ''))
                        ),
                        'reason_notes' => $this->nullableText(
                            $submittedItem['notes'] ?? null
                        ),
                    ];
                }

                $totalValuationVariance = round(
                    $totalExpectedSupplierCredit - $totalInventoryValueReduction,
                    4
                );

                $returnedAt = !empty($data['returned_at'])
                    ? Carbon::parse($data['returned_at'])
                    : now();

                /*
                |--------------------------------------------------------------------------
                | Create immutable return header
                |--------------------------------------------------------------------------
                */

                $temporaryReturnNo = 'TMP-PR-' . Str::upper(Str::random(20));

                $purchaseReturn = PurchaseReturn::create([
                    'return_no' => $temporaryReturnNo,
                    'supplier_id' => $supplier->id,
                    'supplier_name' => $supplier->supplier_name,
                    'supplier_phone' => $supplier->phone,
                    'supplier_email' => $supplier->email,
                    'supplier_address' => $supplier->address,
                    'returned_at' => $returnedAt,
                    'total_items' => count($preparedItems),
                    'expected_supplier_credit' => $totalExpectedSupplierCredit,
                    'inventory_value_reduction' => $totalInventoryValueReduction,
                    'valuation_variance' => $totalValuationVariance,
                    'notes' => $this->nullableText($data['notes'] ?? null),
                    'created_by' => $user->id,
                ]);

                $returnNo = sprintf(
                    'PR-%s-%06d',
                    $returnedAt->format('Ymd'),
                    $purchaseReturn->id
                );

                // Direct DB update avoids triggering immutable update guard.
                DB::table('purchase_returns')
                    ->where('id', $purchaseReturn->id)
                    ->update([
                        'return_no' => $returnNo,
                        'updated_at' => now(),
                    ]);

                $purchaseReturn->return_no = $returnNo;

                /*
                |--------------------------------------------------------------------------
                | Execute exact-lot return
                |--------------------------------------------------------------------------
                */

                foreach ($preparedItems as $prepared) {
                    /** @var RawMaterial $rawMaterial */
                    $rawMaterial = $prepared['raw_material'];

                    /** @var WarehouseStock $warehouseStock */
                    $warehouseStock = $prepared['warehouse_stock'];

                    /** @var WarehouseStockLot $lot */
                    $lot = $prepared['lot'];

                    $requestIndex = (int) $prepared['request_index'];

                    $actualWarehouseBefore = round(
                        (float) $warehouseStock->quantity,
                        4
                    );

                    if (
                        abs(
                            $actualWarehouseBefore
                            - (float) $prepared['warehouse_quantity_before']
                        ) > self::EPSILON
                    ) {
                        throw ValidationException::withMessages([
                            "items.{$requestIndex}.quantity" => [
                                'Warehouse stock changed while Purchase Return was being recorded. Please retry.',
                            ],
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Aggregate quantity OUT
                    |--------------------------------------------------------------------------
                    |
                    | Moving-average unit cost stays unchanged on stock outflow.
                    |
                    */

                    $warehouseStock->update([
                        'quantity' => $prepared['warehouse_quantity_after'],
                        'updated_by' => $user->id,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Exact lot OUT + immutable lot movement
                    |--------------------------------------------------------------------------
                    */

                    $lotResult = $this->warehouseLotService
                        ->deductExactPurchaseReturnLot(
                            lotId: $lot->id,
                            rawMaterial: $rawMaterial,
                            quantity: $prepared['quantity'],
                            referenceType: PurchaseReturn::class,
                            referenceId: $purchaseReturn->id,
                            occurredAt: $returnedAt,
                            notes: $this->buildReturnMovementNotes(
                                returnNo: $returnNo,
                                reason: $prepared['reason'],
                                reasonNotes: $prepared['reason_notes']
                            ),
                            user: $user,
                            validationField: "items.{$requestIndex}.quantity"
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Aggregate warehouse movement
                    |--------------------------------------------------------------------------
                    */

                    StockMovement::create([
                        'raw_material_id' => $rawMaterial->id,
                        'location' => StockMovement::LOCATION_WAREHOUSE,
                        'movement_type' =>
                            StockMovement::TYPE_PURCHASE_RETURN_OUT,
                        'quantity' => $prepared['quantity'],
                        'quantity_before' =>
                            $prepared['warehouse_quantity_before'],
                        'quantity_after' =>
                            $prepared['warehouse_quantity_after'],
                        'unit_cost' =>
                            $prepared['inventory_average_unit_cost'],
                        'reference_type' => PurchaseReturn::class,
                        'reference_id' => $purchaseReturn->id,
                        'unit' => $rawMaterial->base_unit,
                        'notes' => $this->buildReturnMovementNotes(
                            returnNo: $returnNo,
                            reason: $prepared['reason'],
                            reasonNotes: $prepared['reason_notes']
                        ),
                        'created_by' => $user->id,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Immutable item snapshot
                    |--------------------------------------------------------------------------
                    */

                    PurchaseReturnItem::create([
                        'purchase_return_id' => $purchaseReturn->id,
                        'warehouse_stock_lot_id' => $lot->id,
                        'raw_material_id' => $rawMaterial->id,
                        'purchase_order_id' => $lot->purchase_order_id,
                        'purchase_order_receipt_id' =>
                            $lot->purchase_order_receipt_id,
                        'item_name' => $rawMaterial->material_name,
                        'unit' => $rawMaterial->base_unit,
                        'lot_no' => $lot->lot_no,
                        'supplier_batch_no' => $lot->supplier_batch_no,
                        'manufactured_at' => $lot->manufactured_at,
                        'expiry_date' => $lot->expiry_date,
                        'quantity' => $prepared['quantity'],
                        'lot_quantity_before' =>
                            $lotResult['quantity_before'],
                        'lot_quantity_after' =>
                            $lotResult['quantity_after'],
                        'warehouse_quantity_before' =>
                            $prepared['warehouse_quantity_before'],
                        'warehouse_quantity_after' =>
                            $prepared['warehouse_quantity_after'],
                        'return_unit_cost' => $prepared['return_unit_cost'],
                        'expected_supplier_credit' =>
                            $prepared['expected_supplier_credit'],
                        'inventory_average_unit_cost' =>
                            $prepared['inventory_average_unit_cost'],
                        'inventory_value_reduction' =>
                            $prepared['inventory_value_reduction'],
                        'valuation_variance' =>
                            $prepared['valuation_variance'],
                        'reason' => $prepared['reason'],
                        'reason_notes' => $prepared['reason_notes'],
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Final reconciliation
                |--------------------------------------------------------------------------
                */

                foreach ($rawMaterialIds as $rawMaterialId) {
                    $this->warehouseLotService->assertMaterialBalance(
                        rawMaterialId: (int) $rawMaterialId
                    );
                }

                return $purchaseReturn->fresh([
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
                ]);
            },
            3
        );
    }

    private function nullableText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function buildReturnMovementNotes(
        string $returnNo,
        string $reason,
        ?string $reasonNotes
    ): string {
        $parts = [
            "Purchase Return {$returnNo}",
            'Reason: ' . PurchaseReturnItem::reasonLabel($reason),
        ];

        if ($reasonNotes) {
            $parts[] = "Note: {$reasonNotes}";
        }

        return mb_substr(implode(' | ', $parts), 0, 2000);
    }
}
