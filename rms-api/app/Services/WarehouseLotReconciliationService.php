<?php

namespace App\Services;

use App\Models\WarehouseStock;
use App\Models\WarehouseStockLot;


class WarehouseLotReconciliationService
{
    private const EPSILON =
        0.00005;


    /*
    |--------------------------------------------------------------------------
    | Check Current Tenant
    |--------------------------------------------------------------------------
    */

    public function checkCurrentTenant(): array
    {
        $warehouseStocks =
            WarehouseStock::withTrashed()
                ->orderBy(
                    'raw_material_id'
                )
                ->get()
                ->keyBy(
                    'raw_material_id'
                );


        $lotTotals =
            WarehouseStockLot::query()

                ->selectRaw(
                    'raw_material_id, SUM(remaining_quantity) AS lot_quantity'
                )

                ->groupBy(
                    'raw_material_id'
                )

                ->get()

                ->keyBy(
                    'raw_material_id'
                );


        $materialIds =
            $warehouseStocks
                ->keys()
                ->merge(
                    $lotTotals->keys()
                )
                ->map(
                    static fn (
                        mixed $id
                    ): int =>
                        (int) $id
                )
                ->unique()
                ->sort()
                ->values();


        $rows = [];

        $mismatchCount =
            0;


        foreach (
            $materialIds
            as
            $rawMaterialId
        ) {

            $warehouseStock =
                $warehouseStocks->get(
                    $rawMaterialId
                );


            $lotSummary =
                $lotTotals->get(
                    $rawMaterialId
                );


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
                    (float) (
                        $lotSummary
                            ?->lot_quantity
                        ??
                        0
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


            $matches =
                abs(
                    $difference
                )
                <=
                self::EPSILON;


            if (
                !$matches
            ) {

                $mismatchCount++;
            }


            $rows[] = [

                'raw_material_id' =>
                    $rawMaterialId,

                'warehouse_quantity' =>
                    $warehouseQuantity,

                'lot_quantity' =>
                    $lotQuantity,

                'difference' =>
                    $difference,

                'matches' =>
                    $matches,

            ];
        }


        return [

            'materials_checked' =>
                count(
                    $rows
                ),

            'mismatch_count' =>
                $mismatchCount,

            'matches' =>
                $mismatchCount
                ===
                0,

            'rows' =>
                $rows,

        ];
    }
}
