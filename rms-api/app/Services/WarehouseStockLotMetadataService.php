<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\User;
use App\Models\WarehouseStockLot;
use App\Models\WarehouseStockLotMetadataChange;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class WarehouseStockLotMetadataService
{
    private const EDITABLE_FIELDS = [

        'supplier_id',

        'supplier_batch_no',

        'manufactured_at',

        'expiry_date',

        'manual_notes',

    ];


    /*
    |--------------------------------------------------------------------------
    | Update Manual Metadata
    |--------------------------------------------------------------------------
    |
    | This NEVER changes:
    |
    | raw material
    | quantities
    | unit cost
    | source type
    | PO / GRN references
    |
    | Therefore inventory and accounting values remain untouched.
    |
    */

    public function update(
        int $lotId,
        array $data,
        User $user
    ): WarehouseStockLot {

        return DB::transaction(

            function () use (
                $lotId,
                $data,
                $user
            ): WarehouseStockLot {

                $lot =
                    WarehouseStockLot::query()

                        ->with([
                            'supplier',
                        ])

                        ->lockForUpdate()

                        ->findOrFail(
                            $lotId
                        );


                $before =
                    $this->snapshot(
                        lot:
                            $lot
                    );


                $next =
                    $this->normalizeNextMetadata(
                        lot:
                            $lot,

                        data:
                            $data
                    );


                /*
                |--------------------------------------------------------------------------
                | Purchase Receive Supplier Link Is Historical
                |--------------------------------------------------------------------------
                |
                | The supplier came from the original PO and must not be manually
                | changed. Legacy / adjustment lots may be enriched with supplier.
                |
                */

                if (
                    $lot->source_type
                    ===
                    WarehouseStockLot::SOURCE_PURCHASE_RECEIVE
                    &&
                    $next[
                        'supplier_id'
                    ]
                    !==
                    $before[
                        'supplier_id'
                    ]
                ) {

                    throw ValidationException::withMessages([

                        'supplier_id' => [
                            'Supplier cannot be changed for a purchase-receive lot because it is linked to the original purchase order.',
                        ],

                    ]);
                }


                $changedFields =
                    [];


                foreach (
                    self::EDITABLE_FIELDS
                    as
                    $field
                ) {

                    if (
                        $before[
                            $field
                        ]
                        !==
                        $next[
                            $field
                        ]
                    ) {

                        $changedFields[] =
                            $field;
                    }
                }


                if (
                    $changedFields
                    ===
                    []
                ) {

                    throw ValidationException::withMessages([

                        'metadata' => [
                            'No warehouse lot information was changed.',
                        ],

                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Update Metadata Only
                |--------------------------------------------------------------------------
                */

                $lot->update([

                    'supplier_id' =>
                        $next[
                            'supplier_id'
                        ],

                    'supplier_batch_no' =>
                        $next[
                            'supplier_batch_no'
                        ],

                    'manufactured_at' =>
                        $next[
                            'manufactured_at'
                        ],

                    'expiry_date' =>
                        $next[
                            'expiry_date'
                        ],

                    'manual_notes' =>
                        $next[
                            'manual_notes'
                        ],

                    'updated_by' =>
                        $user->id,

                ]);


                $lot->refresh();

                $lot->load([
                    'supplier',
                ]);


                $after =
                    $this->snapshot(
                        lot:
                            $lot
                    );


                /*
                |--------------------------------------------------------------------------
                | Immutable Audit Record
                |--------------------------------------------------------------------------
                */

                WarehouseStockLotMetadataChange::create([

                    'warehouse_stock_lot_id' =>
                        $lot->id,

                    'changed_fields' =>
                        $changedFields,

                    'before_snapshot' =>
                        $before,

                    'after_snapshot' =>
                        $after,

                    'change_reason' =>
                        trim(
                            (string) $data[
                                'change_reason'
                            ]
                        ),

                    'created_by' =>
                        $user->id,

                ]);


                return WarehouseStockLot::query()

                    ->with([
                        'rawMaterial',
                        'supplier',
                        'purchaseOrder',
                        'purchaseOrderReceipt',
                        'creator',
                        'updater',
                    ])

                    ->findOrFail(
                        $lot->id
                    );
            },

            3
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Metadata History
    |--------------------------------------------------------------------------
    */

    public function history(
        int $lotId
    ): Collection {

        WarehouseStockLot::query()
            ->findOrFail(
                $lotId
            );


        return WarehouseStockLotMetadataChange::query()

            ->with([
                'creator',
            ])

            ->where(
                'warehouse_stock_lot_id',
                $lotId
            )

            ->orderByDesc(
                'created_at'
            )

            ->orderByDesc(
                'id'
            )

            ->get();
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize
    |--------------------------------------------------------------------------
    */

    private function normalizeNextMetadata(
        WarehouseStockLot $lot,
        array $data
    ): array {

        $supplierId =
            $data[
                'supplier_id'
            ]
            ??
            null;


        $supplierId =
            $supplierId !==
            null

                ? (int) $supplierId

                : null;


        $supplierBatchNo =
            $this->cleanText(
                $data[
                    'supplier_batch_no'
                ]
                ??
                null
            );


        $manufacturedAt =
            $this->normalizeDate(
                $data[
                    'manufactured_at'
                ]
                ??
                null
            );


        $expiryDate =
            $this->normalizeDate(
                $data[
                    'expiry_date'
                ]
                ??
                null
            );


        if (
            $manufacturedAt
            &&
            $expiryDate
            &&
            Carbon::parse(
                $expiryDate
            )
                ->lt(
                    Carbon::parse(
                        $manufacturedAt
                    )
                )
        ) {

            throw ValidationException::withMessages([

                'expiry_date' => [
                    'Expiry date cannot be earlier than manufacturing date.',
                ],

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Past Expiry Is Allowed
        |--------------------------------------------------------------------------
        |
        | If the user discovers that stock is already expired, they MUST be able
        | to record the true past date. Phase 4 will then automatically block
        | that quantity from normal restaurant transfer.
        |
        */


        return [

            'supplier_id' =>
                $supplierId,

            'supplier_batch_no' =>
                $supplierBatchNo,

            'manufactured_at' =>
                $manufacturedAt,

            'expiry_date' =>
                $expiryDate,

            'manual_notes' =>
                $this->cleanText(
                    $data[
                        'manual_notes'
                    ]
                    ??
                    null
                ),

        ];
    }


    private function snapshot(
        WarehouseStockLot $lot
    ): array {

        $supplier =
            $lot->relationLoaded(
                'supplier'
            )
                ? $lot->supplier
                : null;


        return [

            'supplier_id' =>
                $lot->supplier_id
                    !==
                    null
                        ? (int) $lot->supplier_id
                        : null,

            'supplier_name' =>
                $supplier
                    ?->supplier_name,

            'supplier_batch_no' =>
                $lot->supplier_batch_no,

            'manufactured_at' =>
                $lot->manufactured_at
                    ?->format(
                        'Y-m-d'
                    ),

            'expiry_date' =>
                $lot->expiry_date
                    ?->format(
                        'Y-m-d'
                    ),

            'manual_notes' =>
                $lot->manual_notes,

        ];
    }


    private function normalizeDate(
        mixed $value
    ): ?string {

        if (
            $value ===
            null
            ||
            $value ===
            ''
        ) {
            return null;
        }


        return Carbon::parse(
            $value
        )
            ->format(
                'Y-m-d'
            );
    }


    private function cleanText(
        mixed $value
    ): ?string {

        if (
            $value ===
            null
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
