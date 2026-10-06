<?php

namespace App\Services;

use App\Models\RawMaterial;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockLotMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class WarehouseStockAdjustmentService
{
    public const TYPE_INCREASE =
        'increase';

    public const TYPE_DECREASE =
        'decrease';


    public function __construct(
        private readonly WarehouseLotService $warehouseLotService
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Adjust Warehouse Stock
    |--------------------------------------------------------------------------
    |
    | Replaces only the warehouse-adjustment mutation path.
    |
    | Existing InventoryService remains responsible for reads, summaries,
    | raw-material CRUD, and stock movement history.
    |
    */

    public function adjustWarehouseStock(
        RawMaterial $rawMaterial,
        array $data,
        User $user
    ): WarehouseStock {

        return DB::transaction(

            function () use (
                $rawMaterial,
                $data,
                $user
            ): WarehouseStock {

                /*
                |--------------------------------------------------------------------------
                | Lock Material
                |--------------------------------------------------------------------------
                */

                $lockedMaterial =
                    RawMaterial::query()

                        ->whereKey(
                            $rawMaterial->id
                        )

                        ->whereNull(
                            'deleted_at'
                        )

                        ->lockForUpdate()

                        ->firstOrFail();


                if (
                    !$lockedMaterial->is_active
                ) {

                    throw ValidationException::withMessages([

                        'raw_material_id' => [
                            'Activate this raw material before adjusting warehouse stock.',
                        ],

                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Normalize Adjustment
                |--------------------------------------------------------------------------
                */

                $adjustmentType =
                    strtolower(
                        trim(
                            (string) (
                                $data[
                                    'adjustment_type'
                                ]
                                ??
                                ''
                            )
                        )
                    );


                if (
                    !in_array(
                        $adjustmentType,
                        [
                            self::TYPE_INCREASE,
                            self::TYPE_DECREASE,
                        ],
                        true
                    )
                ) {

                    throw ValidationException::withMessages([

                        'adjustment_type' => [
                            'Adjustment type must be increase or decrease.',
                        ],

                    ]);
                }


                $quantity =
                    round(
                        (float) (
                            $data[
                                'quantity'
                            ]
                            ??
                            0
                        ),
                        4
                    );


                if (
                    $quantity
                    <=
                    0
                ) {

                    throw ValidationException::withMessages([

                        'quantity' => [
                            'Adjustment quantity must be greater than zero.',
                        ],

                    ]);
                }


                $notes =
                    trim(
                        (string) (
                            $data[
                                'notes'
                            ]
                            ??
                            ''
                        )
                    );


                if (
                    $notes ===
                    ''
                ) {

                    throw ValidationException::withMessages([

                        'notes' => [
                            'Adjustment reason is required.',
                        ],

                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Lock / Create Warehouse Snapshot
                |--------------------------------------------------------------------------
                */

                $warehouseStock =
                    WarehouseStock::withTrashed()

                        ->where(
                            'raw_material_id',
                            $lockedMaterial->id
                        )

                        ->lockForUpdate()

                        ->first();


                if (
                    !$warehouseStock
                ) {

                    $warehouseStock =
                        WarehouseStock::create([

                            'raw_material_id' =>
                                $lockedMaterial->id,

                            'quantity' =>
                                0,

                            'average_unit_cost' =>
                                0,

                            'last_received_at' =>
                                null,

                            'created_by' =>
                                $user->id,

                            'updated_by' =>
                                $user->id,

                        ]);


                    $warehouseStock =
                        WarehouseStock::query()

                            ->whereKey(
                                $warehouseStock->id
                            )

                            ->lockForUpdate()

                            ->firstOrFail();
                }


                if (
                    $warehouseStock->trashed()
                ) {

                    $warehouseStock->restore();


                    $warehouseStock =
                        WarehouseStock::query()

                            ->whereKey(
                                $warehouseStock->id
                            )

                            ->lockForUpdate()

                            ->firstOrFail();
                }


                $quantityBefore =
                    round(
                        (float) $warehouseStock
                            ->quantity,
                        4
                    );


                $averageBefore =
                    round(
                        (float) $warehouseStock
                            ->average_unit_cost,
                        4
                    );


                /*
                |--------------------------------------------------------------------------
                | Increase
                |--------------------------------------------------------------------------
                */

                if (
                    $adjustmentType
                    ===
                    self::TYPE_INCREASE
                ) {

                    $incomingUnitCost =
                        $this->resolveIncreaseUnitCost(
                            data:
                                $data,

                            currentAverage:
                                $averageBefore
                        );


                    $quantityAfter =
                        round(
                            $quantityBefore
                            +
                            $quantity,
                            4
                        );


                    $averageAfter =
                        $this->calculateWeightedAverageCost(

                            oldQuantity:
                                $quantityBefore,

                            oldAverageCost:
                                $averageBefore,

                            receivedQuantity:
                                $quantity,

                            receivedUnitCost:
                                $incomingUnitCost

                        );


                    $warehouseStock->update([

                        'quantity' =>
                            $quantityAfter,

                        'average_unit_cost' =>
                            $averageAfter,

                        'last_received_at' =>
                            now(),

                        'updated_by' =>
                            $user->id,

                    ]);


                    $movement =
                        StockMovement::create([

                            'raw_material_id' =>
                                $lockedMaterial->id,

                            'location' =>
                                StockMovement::LOCATION_WAREHOUSE,

                            'movement_type' =>
                                StockMovement::TYPE_WAREHOUSE_ADJUSTMENT_IN,

                            'quantity' =>
                                $quantity,

                            'quantity_before' =>
                                $quantityBefore,

                            'quantity_after' =>
                                $quantityAfter,

                            'unit_cost' =>
                                $incomingUnitCost,

                            'reference_type' =>
                                WarehouseStock::class,

                            'reference_id' =>
                                $warehouseStock->id,

                            'unit' =>
                                $lockedMaterial->base_unit,

                            'notes' =>
                                $notes,

                            'created_by' =>
                                $user->id,

                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Create Unknown-Expiry Adjustment Lot
                    |--------------------------------------------------------------------------
                    */

                    $this->warehouseLotService
                        ->createWarehouseAdjustmentInLot(

                            rawMaterial:
                                $lockedMaterial,

                            quantity:
                                $quantity,

                            unitCost:
                                $incomingUnitCost,

                            stockMovement:
                                $movement,

                            notes:
                                $notes,

                            user:
                                $user

                        );


                    return $warehouseStock
                        ->fresh([
                            'rawMaterial',
                            'creator',
                            'updater',
                        ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Decrease
                |--------------------------------------------------------------------------
                */

                if (
                    $quantity
                    >
                    $quantityBefore
                ) {

                    throw ValidationException::withMessages([

                        'quantity' => [

                            sprintf(
                                'Adjustment quantity cannot exceed warehouse stock. Available quantity is %.4f %s.',
                                $quantityBefore,
                                $lockedMaterial->base_unit
                            ),

                        ],

                    ]);
                }


                $quantityAfter =
                    round(
                        $quantityBefore
                        -
                        $quantity,
                        4
                    );


                $warehouseStock->update([

                    'quantity' =>
                        $quantityAfter,

                    /*
                    |--------------------------------------------------------------------------
                    | Stock OUT Keeps Weighted Average Cost
                    |--------------------------------------------------------------------------
                    */

                    'updated_by' =>
                        $user->id,

                ]);


                $movement =
                    StockMovement::create([

                        'raw_material_id' =>
                            $lockedMaterial->id,

                        'location' =>
                            StockMovement::LOCATION_WAREHOUSE,

                        'movement_type' =>
                            StockMovement::TYPE_WAREHOUSE_ADJUSTMENT_OUT,

                        'quantity' =>
                            $quantity,

                        'quantity_before' =>
                            $quantityBefore,

                        'quantity_after' =>
                            $quantityAfter,

                        'unit_cost' =>
                            $averageBefore,

                        'reference_type' =>
                            WarehouseStock::class,

                        'reference_id' =>
                            $warehouseStock->id,

                        'unit' =>
                            $lockedMaterial->base_unit,

                        'notes' =>
                            $notes,

                        'created_by' =>
                            $user->id,

                    ]);


                /*
                |--------------------------------------------------------------------------
                | FEFO Physical Lot Deduction
                |--------------------------------------------------------------------------
                |
                | Adjustment OUT is a physical correction, not a restaurant issue.
                | It MAY remove expired stock. Dated/oldest lots are consumed first.
                |
                */

                $this->warehouseLotService
                    ->allocateFefoOut(

                        rawMaterial:
                            $lockedMaterial,

                        quantity:
                            $quantity,

                        movementType:
                            WarehouseStockLotMovement::TYPE_WAREHOUSE_ADJUSTMENT_OUT,

                        referenceType:
                            StockMovement::class,

                        referenceId:
                            $movement->id,

                        occurredAt:
                            $movement->created_at
                            ??
                            now(),

                        notes:
                            $notes,

                        user:
                            $user,

                        allowExpired:
                            true,

                        validationField:
                            'quantity'

                    );


                return $warehouseStock
                    ->fresh([
                        'rawMaterial',
                        'creator',
                        'updater',
                    ]);
            },

            3
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Increase Cost
    |--------------------------------------------------------------------------
    */

    private function resolveIncreaseUnitCost(
        array $data,
        float $currentAverage
    ): float {

        $rawUnitCost =
            $data[
                'unit_cost'
            ]
            ??
            null;


        if (
            $rawUnitCost ===
            null
            ||
            $rawUnitCost ===
            ''
        ) {

            return round(
                max(
                    0,
                    $currentAverage
                ),
                4
            );
        }


        if (
            !is_numeric(
                $rawUnitCost
            )
        ) {

            throw ValidationException::withMessages([

                'unit_cost' => [
                    'Unit cost must be a valid number.',
                ],

            ]);
        }


        $unitCost =
            round(
                (float) $rawUnitCost,
                4
            );


        if (
            $unitCost
            <
            0
        ) {

            throw ValidationException::withMessages([

                'unit_cost' => [
                    'Unit cost cannot be negative.',
                ],

            ]);
        }


        return $unitCost;
    }


    /*
    |--------------------------------------------------------------------------
    | Weighted Average Cost
    |--------------------------------------------------------------------------
    */

    private function calculateWeightedAverageCost(
        float $oldQuantity,
        float $oldAverageCost,
        float $receivedQuantity,
        float $receivedUnitCost
    ): float {

        $oldQuantity =
            round(
                max(
                    0,
                    $oldQuantity
                ),
                4
            );


        $oldAverageCost =
            round(
                max(
                    0,
                    $oldAverageCost
                ),
                4
            );


        $receivedQuantity =
            round(
                max(
                    0,
                    $receivedQuantity
                ),
                4
            );


        $receivedUnitCost =
            round(
                max(
                    0,
                    $receivedUnitCost
                ),
                4
            );


        $newQuantity =
            round(
                $oldQuantity
                +
                $receivedQuantity,
                4
            );


        if (
            $newQuantity
            <=
            0
        ) {
            return 0.0;
        }


        $oldValue =
            $oldQuantity
            *
            $oldAverageCost;


        $receivedValue =
            $receivedQuantity
            *
            $receivedUnitCost;


        return round(
            (
                $oldValue
                +
                $receivedValue
            )
            /
            $newQuantity,
            4
        );
    }
}
