<?php

namespace App\Services;

use App\Models\WarehouseStockLot;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;


class WarehouseStockLotQueryService
{
    /*
    |--------------------------------------------------------------------------
    | Paginated Lot List
    |--------------------------------------------------------------------------
    */

    public function getLots(
        array $filters = []
    ): LengthAwarePaginator {

        $query =
            WarehouseStockLot::query()

                ->with([
                    'rawMaterial',
                    'supplier',
                    'purchaseOrder',
                    'purchaseOrderReceipt',
                    'creator',
                    'updater',
                ]);


        $this->applyFilters(
            query:
                $query,

            filters:
                $filters
        );


        $this->applySorting(
            query:
                $query,

            sortBy:
                (string) (
                    $filters[
                        'sort_by'
                    ]
                    ??
                    'expiry_date'
                ),

            direction:
                (string) (
                    $filters[
                        'sort_direction'
                    ]
                    ??
                    'asc'
                )
        );


        return $query->paginate(

            perPage:
                min(
                    max(
                        (int) (
                            $filters[
                                'per_page'
                            ]
                            ??
                            20
                        ),
                        1
                    ),
                    100
                ),

            page:
                max(
                    (int) (
                        $filters[
                            'page'
                        ]
                        ??
                        1
                    ),
                    1
                )

        );
    }


    /*
    |--------------------------------------------------------------------------
    | Lot Details
    |--------------------------------------------------------------------------
    */

    public function getLot(
        int $lotId
    ): WarehouseStockLot {

        return WarehouseStockLot::query()

            ->with([
                'rawMaterial',
                'supplier',
                'purchaseOrder',
                'purchaseOrderReceipt',
                'purchaseOrderReceiptItem',
                'creator',
                'updater',
                'movements.creator',
            ])

            ->findOrFail(
                $lotId
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    public function getSummary(): array
    {
        $today =
            now()
                ->startOfDay();

        $criticalEnd =
            $today
                ->copy()
                ->addDays(
                    WarehouseStockLot::DEFAULT_CRITICAL_DAYS
                );

        $soonEnd =
            $today
                ->copy()
                ->addDays(
                    WarehouseStockLot::DEFAULT_EXPIRING_SOON_DAYS
                );


        $openQuery =
            WarehouseStockLot::query()
                ->where(
                    'remaining_quantity',
                    '>',
                    0
                );


        $openLots =
            (clone $openQuery)
                ->count();


        $unknownExpiry =
            (clone $openQuery)
                ->whereNull(
                    'expiry_date'
                )
                ->count();


        $expired =
            (clone $openQuery)
                ->whereDate(
                    'expiry_date',
                    '<',
                    $today->toDateString()
                )
                ->count();


        $critical =
            (clone $openQuery)
                ->whereBetween(
                    'expiry_date',
                    [
                        $today->toDateString(),
                        $criticalEnd->toDateString(),
                    ]
                )
                ->count();


        $expiringSoon =
            (clone $openQuery)
                ->whereDate(
                    'expiry_date',
                    '>',
                    $criticalEnd->toDateString()
                )
                ->whereDate(
                    'expiry_date',
                    '<=',
                    $soonEnd->toDateString()
                )
                ->count();


        $healthy =
            (clone $openQuery)
                ->whereDate(
                    'expiry_date',
                    '>',
                    $soonEnd->toDateString()
                )
                ->count();


        $depleted =
            WarehouseStockLot::query()
                ->where(
                    'remaining_quantity',
                    '<=',
                    0
                )
                ->count();


        $riskValue =
            round(
                (float) WarehouseStockLot::query()

                    ->where(
                        'remaining_quantity',
                        '>',
                        0
                    )

                    ->whereNotNull(
                        'expiry_date'
                    )

                    ->whereDate(
                        'expiry_date',
                        '<=',
                        $soonEnd->toDateString()
                    )

                    ->selectRaw(
                        'COALESCE(SUM(remaining_quantity * unit_cost), 0) AS risk_value'
                    )

                    ->value(
                        'risk_value'
                    ),
                4
            );


        $expiredValue =
            round(
                (float) WarehouseStockLot::query()

                    ->where(
                        'remaining_quantity',
                        '>',
                        0
                    )

                    ->whereNotNull(
                        'expiry_date'
                    )

                    ->whereDate(
                        'expiry_date',
                        '<',
                        $today->toDateString()
                    )

                    ->selectRaw(
                        'COALESCE(SUM(remaining_quantity * unit_cost), 0) AS expired_value'
                    )

                    ->value(
                        'expired_value'
                    ),
                4
            );


        return [

            'open_lots' =>
                $openLots,

            'healthy' =>
                $healthy,

            'expiring_soon' =>
                $expiringSoon,

            'critical' =>
                $critical,

            'expired' =>
                $expired,

            'unknown_expiry' =>
                $unknownExpiry,

            'depleted' =>
                $depleted,

            'expiry_watch_count' =>
                $expired
                +
                $critical
                +
                $expiringSoon,

            'risk_value' =>
                $riskValue,

            'risk_value_formatted' =>
                $this->formatMoney(
                    $riskValue
                ),

            'expired_value' =>
                $expiredValue,

            'expired_value_formatted' =>
                $this->formatMoney(
                    $expiredValue
                ),

            'critical_days' =>
                WarehouseStockLot::DEFAULT_CRITICAL_DAYS,

            'expiring_soon_days' =>
                WarehouseStockLot::DEFAULT_EXPIRING_SOON_DAYS,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    private function applyFilters(
        Builder $query,
        array $filters
    ): void {

        $search =
            trim(
                (string) (
                    $filters[
                        'search'
                    ]
                    ??
                    ''
                )
            );


        if (
            $search !== ''
        ) {

            $query->where(
                static function (
                    Builder $searchQuery
                ) use (
                    $search
                ): void {

                    $like =
                        "%{$search}%";


                    $searchQuery

                        ->where(
                            'lot_no',
                            'like',
                            $like
                        )

                        ->orWhere(
                            'supplier_batch_no',
                            'like',
                            $like
                        )

                        ->orWhereHas(
                            'rawMaterial',
                            static function (
                                Builder $materialQuery
                            ) use (
                                $like
                            ): void {

                                $materialQuery

                                    ->where(
                                        'material_name',
                                        'like',
                                        $like
                                    )

                                    ->orWhere(
                                        'category',
                                        'like',
                                        $like
                                    );
                            }
                        )

                        ->orWhereHas(
                            'supplier',
                            static function (
                                Builder $supplierQuery
                            ) use (
                                $like
                            ): void {

                                $supplierQuery->where(
                                    'supplier_name',
                                    'like',
                                    $like
                                );
                            }
                        )

                        ->orWhereHas(
                            'purchaseOrderReceipt',
                            static function (
                                Builder $receiptQuery
                            ) use (
                                $like
                            ): void {

                                $receiptQuery->where(
                                    'receipt_no',
                                    'like',
                                    $like
                                );
                            }
                        );
                }
            );
        }


        $rawMaterialId =
            (int) (
                $filters[
                    'raw_material_id'
                ]
                ??
                0
            );


        if (
            $rawMaterialId
            >
            0
        ) {

            $query->where(
                'raw_material_id',
                $rawMaterialId
            );
        }


        $sourceType =
            trim(
                (string) (
                    $filters[
                        'source_type'
                    ]
                    ??
                    ''
                )
            );


        if (
            $sourceType !== ''
        ) {

            $query->where(
                'source_type',
                $sourceType
            );
        }


        $scope =
            trim(
                (string) (
                    $filters[
                        'scope'
                    ]
                    ??
                    ''
                )
            );


        if (
            $scope ===
            'expiry_watch'
        ) {

            $today =
                now()
                    ->startOfDay();

            $soonEnd =
                $today
                    ->copy()
                    ->addDays(
                        WarehouseStockLot::DEFAULT_EXPIRING_SOON_DAYS
                    );


            $query
                ->where(
                    'remaining_quantity',
                    '>',
                    0
                )
                ->whereNotNull(
                    'expiry_date'
                )
                ->whereDate(
                    'expiry_date',
                    '<=',
                    $soonEnd->toDateString()
                );
        }


        $status =
            trim(
                (string) (
                    $filters[
                        'status'
                    ]
                    ??
                    ''
                )
            );


        if (
            $status !== ''
        ) {

            $this->applyStatusFilter(
                query:
                    $query,

                status:
                    $status
            );
        }


        if (
            array_key_exists(
                'has_stock',
                $filters
            )
            &&
            $filters[
                'has_stock'
            ]
            !==
            null
        ) {

            if (
                (bool) $filters[
                    'has_stock'
                ]
            ) {

                $query->where(
                    'remaining_quantity',
                    '>',
                    0
                );

            } else {

                $query->where(
                    'remaining_quantity',
                    '<=',
                    0
                );
            }
        }


        $expiryFrom =
            trim(
                (string) (
                    $filters[
                        'expiry_from'
                    ]
                    ??
                    ''
                )
            );


        if (
            $expiryFrom !== ''
        ) {

            $query->whereDate(
                'expiry_date',
                '>=',
                $expiryFrom
            );
        }


        $expiryTo =
            trim(
                (string) (
                    $filters[
                        'expiry_to'
                    ]
                    ??
                    ''
                )
            );


        if (
            $expiryTo !== ''
        ) {

            $query->whereDate(
                'expiry_date',
                '<=',
                $expiryTo
            );
        }
    }


    private function applyStatusFilter(
        Builder $query,
        string $status
    ): void {

        $today =
            now()
                ->startOfDay();

        $criticalEnd =
            $today
                ->copy()
                ->addDays(
                    WarehouseStockLot::DEFAULT_CRITICAL_DAYS
                );

        $soonEnd =
            $today
                ->copy()
                ->addDays(
                    WarehouseStockLot::DEFAULT_EXPIRING_SOON_DAYS
                );


        match (
            $status
        ) {

            WarehouseStockLot::STATUS_DEPLETED =>
                $query->where(
                    'remaining_quantity',
                    '<=',
                    0
                ),

            WarehouseStockLot::STATUS_UNKNOWN_EXPIRY =>
                $query
                    ->where(
                        'remaining_quantity',
                        '>',
                        0
                    )
                    ->whereNull(
                        'expiry_date'
                    ),

            WarehouseStockLot::STATUS_EXPIRED =>
                $query
                    ->where(
                        'remaining_quantity',
                        '>',
                        0
                    )
                    ->whereDate(
                        'expiry_date',
                        '<',
                        $today->toDateString()
                    ),

            WarehouseStockLot::STATUS_CRITICAL =>
                $query
                    ->where(
                        'remaining_quantity',
                        '>',
                        0
                    )
                    ->whereBetween(
                        'expiry_date',
                        [
                            $today->toDateString(),
                            $criticalEnd->toDateString(),
                        ]
                    ),

            WarehouseStockLot::STATUS_EXPIRING_SOON =>
                $query
                    ->where(
                        'remaining_quantity',
                        '>',
                        0
                    )
                    ->whereDate(
                        'expiry_date',
                        '>',
                        $criticalEnd->toDateString()
                    )
                    ->whereDate(
                        'expiry_date',
                        '<=',
                        $soonEnd->toDateString()
                    ),

            WarehouseStockLot::STATUS_HEALTHY =>
                $query
                    ->where(
                        'remaining_quantity',
                        '>',
                        0
                    )
                    ->whereDate(
                        'expiry_date',
                        '>',
                        $soonEnd->toDateString()
                    ),

            default =>
                null,

        };
    }


    /*
    |--------------------------------------------------------------------------
    | Sorting
    |--------------------------------------------------------------------------
    */

    private function applySorting(
        Builder $query,
        string $sortBy,
        string $direction
    ): void {

        $direction =
            strtolower(
                $direction
            )
            ===
            'desc'
                ? 'desc'
                : 'asc';


        if (
            $sortBy ===
            'expiry_date'
        ) {

            /*
            |--------------------------------------------------------------------------
            | Unknown expiry last for normal FEFO-style view
            |--------------------------------------------------------------------------
            */

            $query
                ->orderByRaw(
                    'CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END ASC'
                )
                ->orderBy(
                    'expiry_date',
                    $direction
                )
                ->orderBy(
                    'received_at',
                    'asc'
                )
                ->orderBy(
                    'id',
                    'asc'
                );

            return;
        }


        $allowed = [

            'received_at',

            'remaining_quantity',

            'received_quantity',

            'unit_cost',

            'created_at',

            'updated_at',

        ];


        if (
            !in_array(
                $sortBy,
                $allowed,
                true
            )
        ) {

            $sortBy =
                'created_at';
        }


        $query
            ->orderBy(
                $sortBy,
                $direction
            )
            ->orderBy(
                'id',
                $direction
            );
    }


    private function formatMoney(
        float $amount
    ): string {

        return '৳ '
            .
            number_format(
                $amount,
                2
            );
    }
}
