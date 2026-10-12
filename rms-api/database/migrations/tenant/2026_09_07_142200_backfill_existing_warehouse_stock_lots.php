<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;


return new class extends Migration
{
    private const EPSILON =
        0.00005;


    public function up(): void
    {
        if (
            !Schema::connection('tenant')
                ->hasTable(
                    'warehouse_stocks'
                )
            ||
            !Schema::connection('tenant')
                ->hasTable(
                    'warehouse_stock_lots'
                )
            ||
            !Schema::connection('tenant')
                ->hasTable(
                    'warehouse_stock_lot_movements'
                )
        ) {

            throw new \RuntimeException(
                'Warehouse stock lot backfill cannot run because one or more required tenant tables are missing.'
            );
        }


        DB::connection('tenant')
            ->transaction(
                function (): void {

                    $warehouseStocks =
                        DB::connection('tenant')
                            ->table(
                                'warehouse_stocks'
                            )
                            ->where(
                                'quantity',
                                '>',
                                0
                            )
                            ->orderBy(
                                'id'
                            )
                            ->lockForUpdate()
                            ->get();


                    foreach (
                        $warehouseStocks
                        as
                        $warehouseStock
                    ) {

                        $rawMaterial =
                            DB::connection('tenant')
                                ->table(
                                    'raw_materials'
                                )
                                ->where(
                                    'id',
                                    $warehouseStock
                                        ->raw_material_id
                                )
                                ->first();


                        if (
                            !$rawMaterial
                        ) {

                            throw new \RuntimeException(
                                "Warehouse stock #{$warehouseStock->id} references missing raw material #{$warehouseStock->raw_material_id}."
                            );
                        }


                        $warehouseQuantity =
                            round(
                                (float) (
                                    $warehouseStock
                                        ->quantity
                                    ??
                                    0
                                ),
                                4
                            );


                        $existingLotQuantity =
                            round(
                                (float) DB::connection('tenant')
                                    ->table(
                                        'warehouse_stock_lots'
                                    )
                                    ->where(
                                        'raw_material_id',
                                        $warehouseStock
                                            ->raw_material_id
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
                                $existingLotQuantity,
                                4
                            );


                        if (
                            abs(
                                $difference
                            )
                            <=
                            self::EPSILON
                        ) {
                            continue;
                        }


                        if (
                            $difference
                            <
                            -self::EPSILON
                        ) {

                            throw new \RuntimeException(
                                sprintf(
                                    'Warehouse lot reconciliation failed for raw material #%d. Warehouse quantity is %.4f but existing lot quantity is %.4f.',
                                    $warehouseStock->raw_material_id,
                                    $warehouseQuantity,
                                    $existingLotQuantity
                                )
                            );
                        }


                        $unit =
                            strtolower(
                                trim(
                                    (string) (
                                        $rawMaterial
                                            ->base_unit
                                        ??
                                        ''
                                    )
                                )
                            );


                        if (
                            $unit === ''
                        ) {

                            throw new \RuntimeException(
                                "Raw material #{$rawMaterial->id} has no base unit and cannot be backfilled into warehouse lots."
                            );
                        }


                        $unitCost =
                            round(
                                (float) (
                                    $warehouseStock
                                        ->average_unit_cost
                                    ??
                                    0
                                ),
                                4
                            );


                        $receivedAt =
                            $warehouseStock
                                ->last_received_at
                            ??
                            $warehouseStock
                                ->created_at
                            ??
                            now();


                        $temporaryLotNo =
                            'TMP-LGC-'
                            .
                            Str::upper(
                                Str::random(
                                    20
                                )
                            );


                        $lotId =
                            DB::connection('tenant')
                                ->table(
                                    'warehouse_stock_lots'
                                )
                                ->insertGetId([

                                    'lot_no' =>
                                        $temporaryLotNo,

                                    'raw_material_id' =>
                                        $warehouseStock
                                            ->raw_material_id,

                                    'source_type' =>
                                        'legacy_opening',

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
                                        $difference,

                                    'remaining_quantity' =>
                                        $difference,

                                    'unit_cost' =>
                                        $unitCost,

                                    'unit' =>
                                        $unit,

                                    'received_at' =>
                                        $receivedAt,

                                    'notes' =>
                                        'System-generated legacy lot created when warehouse lot tracking was introduced. Supplier, original purchase receipt and expiry are unknown.',

                                    'created_by' =>
                                        null,

                                    'updated_by' =>
                                        null,

                                    'created_at' =>
                                        now(),

                                    'updated_at' =>
                                        now(),

                                ]);


                        $datePart =
                            now()
                                ->format(
                                    'Ymd'
                                );


                        $lotNo =
                            sprintf(
                                'LOT-LGC-%s-%06d',
                                $datePart,
                                $lotId
                            );


                        DB::connection('tenant')
                            ->table(
                                'warehouse_stock_lots'
                            )
                            ->where(
                                'id',
                                $lotId
                            )
                            ->update([

                                'lot_no' =>
                                    $lotNo,

                                'updated_at' =>
                                    now(),

                            ]);


                        DB::connection('tenant')
                            ->table(
                                'warehouse_stock_lot_movements'
                            )
                            ->insert([

                                'warehouse_stock_lot_id' =>
                                    $lotId,

                                'raw_material_id' =>
                                    $warehouseStock
                                        ->raw_material_id,

                                'movement_type' =>
                                    'legacy_opening',

                                'direction' =>
                                    'in',

                                'quantity' =>
                                    $difference,

                                'quantity_before' =>
                                    0,

                                'quantity_after' =>
                                    $difference,

                                'unit_cost' =>
                                    $unitCost,

                                'unit' =>
                                    $unit,

                                'reference_type' =>
                                    'App\\Models\\WarehouseStock',

                                'reference_id' =>
                                    $warehouseStock
                                        ->id,

                                'occurred_at' =>
                                    $receivedAt,

                                'notes' =>
                                    "Legacy warehouse balance backfill for {$lotNo}.",

                                'created_by' =>
                                    null,

                                'created_at' =>
                                    now(),

                                'updated_at' =>
                                    now(),

                            ]);
                    }
                },

                3
            );
    }


    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Intentionally No Data Mutation
        |--------------------------------------------------------------------------
        |
        | This migration establishes historical lot opening balances.
        | Removing them separately from the lot tables could destroy later audit
        | history. If this migration batch is rolled back, the following table
        | migrations will drop the lot tables in reverse order.
        |
        */
    }
};
