<?php

namespace App\Services;

use App\Models\InventoryWastage;
use App\Models\InventoryWastageItem;
use App\Models\RawMaterial;
use App\Models\RestaurantStock;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class InventoryWastageService
{
    /*
    |--------------------------------------------------------------------------
    | History
    |--------------------------------------------------------------------------
    */

    public function getWastages(
        array $filters = []
    ): LengthAwarePaginator {

        $query =
            InventoryWastage::query()

                ->with([

                    'items.rawMaterial',

                    'recordedBy',

                    'creator',

                ]);


        if (
            !empty(
                $filters[
                    'date_from'
                ]
            )
        ) {

            $query->whereDate(
                'wasted_at',
                '>=',
                $filters[
                    'date_from'
                ]
            );
        }


        if (
            !empty(
                $filters[
                    'date_to'
                ]
            )
        ) {

            $query->whereDate(
                'wasted_at',
                '<=',
                $filters[
                    'date_to'
                ]
            );
        }


        if (
            !empty(
                $filters[
                    'raw_material_id'
                ]
            )
        ) {

            $rawMaterialId =
                (int) $filters[
                    'raw_material_id'
                ];


            $query->whereHas(
                'items',

                static function (
                    $itemQuery
                ) use (
                    $rawMaterialId
                ): void {

                    $itemQuery->where(
                        'raw_material_id',
                        $rawMaterialId
                    );
                }
            );
        }


        if (
            !empty(
                $filters[
                    'reason'
                ]
            )
        ) {

            $reason =
                strtolower(
                    trim(
                        (string) $filters[
                            'reason'
                        ]
                    )
                );


            $query->whereHas(
                'items',

                static function (
                    $itemQuery
                ) use (
                    $reason
                ): void {

                    $itemQuery->where(
                        'reason',
                        $reason
                    );
                }
            );
        }


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


        if ($search !== '') {

            $query->where(

                static function (
                    $searchQuery
                ) use (
                    $search
                ): void {

                    $searchQuery

                        ->where(
                            'wastage_no',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'notes',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhereHas(

                            'items',

                            static function (
                                $itemQuery
                            ) use (
                                $search
                            ): void {

                                $itemQuery

                                    ->where(
                                        'item_name',
                                        'like',
                                        "%{$search}%"
                                    )

                                    ->orWhere(
                                        'notes',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                }
            );
        }


        $perPage =
            max(
                1,
                min(
                    100,
                    (int) (
                        $filters[
                            'per_page'
                        ]
                        ??
                        15
                    )
                )
            );


        return $query

            ->orderByDesc(
                'wasted_at'
            )

            ->orderByDesc(
                'id'
            )

            ->paginate(
                $perPage
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Single Wastage
    |--------------------------------------------------------------------------
    */

    public function getWastage(
        InventoryWastage $wastage
    ): InventoryWastage {

        return $wastage
            ->load([

                'items.rawMaterial',

                'recordedBy',

                'creator',

            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Record Damage / Wastage
    |--------------------------------------------------------------------------
    */

    public function recordWastage(
        array $data,
        User $user
    ): InventoryWastage {

        return DB::transaction(

            function () use (
                $data,
                $user
            ): InventoryWastage {

                $rawMaterialId =
                    (int) (
                        $data[
                            'raw_material_id'
                        ]
                        ??
                        0
                    );


                /*
                |--------------------------------------------------------------------------
                | Lock Raw Material
                |--------------------------------------------------------------------------
                */

                $rawMaterial =
                    RawMaterial::withTrashed()

                        ->whereKey(
                            $rawMaterialId
                        )

                        ->lockForUpdate()

                        ->first();


                if (!$rawMaterial) {

                    throw ValidationException::withMessages([

                        'raw_material_id' => [
                            'The selected raw material was not found.',
                        ],

                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Lock Restaurant Stock
                |--------------------------------------------------------------------------
                */

                $restaurantStock =
                    RestaurantStock::query()

                        ->where(
                            'raw_material_id',
                            $rawMaterialId
                        )

                        ->lockForUpdate()

                        ->first();


                if (!$restaurantStock) {

                    throw ValidationException::withMessages([

                        'quantity' => [
                            'No restaurant stock exists for the selected raw material.',
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


                if ($quantity <= 0) {

                    throw ValidationException::withMessages([

                        'quantity' => [
                            'Damage / wastage quantity must be greater than zero.',
                        ],

                    ]);
                }


                $quantityBefore =
                    round(
                        (float) $restaurantStock
                            ->quantity,
                        4
                    );


                if (
                    $quantity
                    >
                    $quantityBefore
                ) {

                    throw ValidationException::withMessages([

                        'quantity' => [

                            "Damage / wastage quantity cannot exceed restaurant stock. Available quantity is {$quantityBefore} {$rawMaterial->base_unit}.",

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


                /*
                |--------------------------------------------------------------------------
                | Cost Snapshot
                |--------------------------------------------------------------------------
                |
                | This is the critical accounting snapshot.
                |
                | Later price changes must NOT change historical wastage loss.
                |
                */

                $unitCost =
                    round(
                        (float) $restaurantStock
                            ->average_unit_cost,
                        4
                    );


                $totalLoss =
                    round(
                        $quantity
                        *
                        $unitCost,
                        4
                    );


                /*
                |--------------------------------------------------------------------------
                | Business Date
                |--------------------------------------------------------------------------
                */

                $wastedAt =
                    !empty(
                        $data[
                            'wasted_at'
                        ]
                    )

                        ? Carbon::parse(
                            $data[
                                'wasted_at'
                            ]
                        )

                        : now();


                /*
                |--------------------------------------------------------------------------
                | Create Immutable Header
                |--------------------------------------------------------------------------
                */

                $temporaryNo =
                    'TMP-WST-'
                    .
                    Str::upper(
                        Str::random(
                            16
                        )
                    );


                $wastage =
                    InventoryWastage::create([

                        'wastage_no' =>
                            $temporaryNo,

                        'wasted_at' =>
                            $wastedAt,

                        'total_loss' =>
                            $totalLoss,

                        'notes' =>
                            $this->cleanNullableText(
                                $data[
                                    'notes'
                                ]
                                ??
                                null
                            ),

                        'recorded_by' =>
                            $user->id,

                        'created_by' =>
                            $user->id,

                    ]);


                /*
                |--------------------------------------------------------------------------
                | Human Readable Number
                |--------------------------------------------------------------------------
                |
                | Direct query is intentional because the model becomes immutable
                | immediately after creation.
                |
                */

                $wastageNo =
                    sprintf(
                        'WST-%s-%06d',
                        $wastedAt
                            ->format(
                                'Ymd'
                            ),
                        $wastage->id
                    );


                DB::table(
                    'inventory_wastages'
                )
                    ->where(
                        'id',
                        $wastage->id
                    )
                    ->update([

                        'wastage_no' =>
                            $wastageNo,

                    ]);


                $wastage->refresh();


                /*
                |--------------------------------------------------------------------------
                | Wastage Item Snapshot
                |--------------------------------------------------------------------------
                */

                InventoryWastageItem::create([

                    'inventory_wastage_id' =>
                        $wastage->id,

                    'raw_material_id' =>
                        $rawMaterial->id,

                    'item_name' =>
                        $rawMaterial
                            ->material_name,

                    'unit' =>
                        $rawMaterial
                            ->base_unit,

                    'quantity' =>
                        $quantity,

                    'unit_cost' =>
                        $unitCost,

                    'total_loss' =>
                        $totalLoss,

                    'restaurant_quantity_before' =>
                        $quantityBefore,

                    'restaurant_quantity_after' =>
                        $quantityAfter,

                    'reason' =>
                        $data[
                            'reason'
                        ],

                    'notes' =>
                        $this->cleanNullableText(
                            $data[
                                'notes'
                            ]
                            ??
                            null
                        ),

                ]);


                /*
                |--------------------------------------------------------------------------
                | Update Current Restaurant Stock
                |--------------------------------------------------------------------------
                |
                | Removing quantity does not change average unit cost.
                |
                */

                $restaurantStock
                    ->update([

                        'quantity' =>
                            $quantityAfter,

                        'updated_by' =>
                            $user->id,

                    ]);


                /*
                |--------------------------------------------------------------------------
                | Immutable Stock Movement
                |--------------------------------------------------------------------------
                */

                StockMovement::create([

                    'raw_material_id' =>
                        $rawMaterial->id,

                    'location' =>
                        StockMovement::LOCATION_RESTAURANT,

                    'movement_type' =>
                        StockMovement::TYPE_WASTAGE_OUT,

                    'quantity' =>
                        $quantity,

                    'quantity_before' =>
                        $quantityBefore,

                    'quantity_after' =>
                        $quantityAfter,

                    'unit_cost' =>
                        $unitCost,

                    'reference_type' =>
                        InventoryWastage::class,

                    'reference_id' =>
                        $wastage->id,

                    'unit' =>
                        $rawMaterial
                            ->base_unit,

                    'notes' =>
                        $this->buildMovementNotes(
                            wastage:
                                $wastage,

                            rawMaterial:
                                $rawMaterial,

                            reason:
                                $data[
                                    'reason'
                                ],

                            notes:
                                $data[
                                    'notes'
                                ]
                                ??
                                null
                        ),

                    'created_by' =>
                        $user->id,

                ]);


                return $wastage
                    ->fresh([
                        'items.rawMaterial',
                        'recordedBy',
                        'creator',
                    ]);
            },

            3
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reason Label
    |--------------------------------------------------------------------------
    */

    private function reasonLabel(
        string $reason
    ): string {

        return match (
            $reason
        ) {

            InventoryWastageItem::REASON_SPOILED =>
                'Spoiled',

            InventoryWastageItem::REASON_EXPIRED =>
                'Expired',

            InventoryWastageItem::REASON_SPILLAGE =>
                'Spillage',

            InventoryWastageItem::REASON_BURNT_OVERCOOKED =>
                'Burnt / Overcooked',

            InventoryWastageItem::REASON_CONTAMINATED =>
                'Contaminated',

            InventoryWastageItem::REASON_DAMAGED_PACKAGING =>
                'Damaged Packaging',

            InventoryWastageItem::REASON_QUALITY_REJECTED =>
                'Quality Rejected',

            InventoryWastageItem::REASON_STAFF_HANDLING_LOSS =>
                'Staff Handling Loss',

            InventoryWastageItem::REASON_OTHER =>
                'Other',

            default =>
                ucwords(
                    str_replace(
                        '_',
                        ' ',
                        $reason
                    )
                ),

        };
    }


    /*
    |--------------------------------------------------------------------------
    | Movement Notes
    |--------------------------------------------------------------------------
    */

    private function buildMovementNotes(
        InventoryWastage $wastage,
        RawMaterial $rawMaterial,
        string $reason,
        mixed $notes
    ): string {

        $parts = [

            "Wastage {$wastage->wastage_no}",

            "Item: {$rawMaterial->material_name}",

            "Reason: {$this->reasonLabel($reason)}",

        ];


        $cleanNotes =
            $this->cleanNullableText(
                $notes
            );


        if ($cleanNotes) {

            $parts[] =
                "Note: {$cleanNotes}";
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


    /*
    |--------------------------------------------------------------------------
    | Clean Nullable Text
    |--------------------------------------------------------------------------
    */

    private function cleanNullableText(
        mixed $value
    ): ?string {

        if (
            $value === null
        ) {
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
