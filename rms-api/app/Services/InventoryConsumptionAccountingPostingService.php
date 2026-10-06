<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\OrderRecipeConsumption;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryConsumptionAccountingPostingService
{
    public const SOURCE_RECIPE_CONSUMPTION =
        'recipe_consumption';


    public function __construct(
        private readonly JournalService $journalService
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Recipe Consumption -> COGS
    |--------------------------------------------------------------------------
    |
    | Restaurant stock has already been physically consumed by the existing
    | RecipeConsumptionService.
    |
    | Accounting effect:
    |
    | Dr Food & Ingredient COGS
    |    Cr Inventory
    |
    | COGS is calculated from immutable consumption item snapshots:
    |
    | quantity * unit_cost
    |
    */

    public function syncRecipeConsumption(
        OrderRecipeConsumption $consumption,
        ?User $actor = null
    ) {
        $consumption =
            OrderRecipeConsumption::query()
                ->findOrFail(
                    $consumption->id
                );


        /*
        |--------------------------------------------------------------------------
        | Immutable Consumption Items
        |--------------------------------------------------------------------------
        |
        | Use DB snapshots directly so accounting does not depend on future
        | recipe/menu/raw-material master changes.
        |
        */

        $items =
            DB::table(
                'order_recipe_consumption_items'
            )
                ->where(
                    'order_recipe_consumption_id',
                    $consumption->id
                )
                ->orderBy(
                    'id'
                )
                ->get([
                    'id',
                    'raw_material_id',
                    'material_name',
                    'unit',
                    'quantity',
                    'unit_cost',
                ]);


        if ($items->isEmpty()) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Total Consumed Inventory Cost
        |--------------------------------------------------------------------------
        */

        $totalCost =
            round(
                (float) $items->sum(
                    static function (
                        object $item
                    ): float {

                        return round(
                            (float) $item->quantity
                            *
                            (float) $item->unit_cost,
                            4
                        );
                    }
                ),
                2
            );


        /*
        |--------------------------------------------------------------------------
        | Zero-Cost Consumption
        |--------------------------------------------------------------------------
        |
        | Quantity consumption may legitimately exist with zero carrying cost,
        | for example legacy/opening stock without cost.
        |
        | No financial journal is needed for a zero-value inventory movement.
        |
        */

        if ($totalCost <= 0) {
            return null;
        }


        $actor =
            $actor
            ??
            $this->resolveActor(
                $consumption
            );


        $cogsAccount =
            $this->systemPostingAccount(
                'food_ingredient_cogs',
                ChartOfAccount::TYPE_COGS
            );


        $inventoryAccount =
            $this->systemPostingAccount(
                'inventory',
                ChartOfAccount::TYPE_ASSET
            );


        $journalDate =
            Carbon::parse(
                $consumption->consumed_at
                ??
                $consumption->created_at
                ??
                now()
            )->format(
                'Y-m-d'
            );


        /*
        |--------------------------------------------------------------------------
        | Order / Batch Context
        |--------------------------------------------------------------------------
        */

        $orderNumber =
            null;


        if (
            (int) (
                $consumption->order_id
                ??
                0
            )
            >
            0
        ) {
            $orderNumber =
                DB::table(
                    'orders'
                )
                    ->where(
                        'id',
                        (int) $consumption->order_id
                    )
                    ->value(
                        'order_number'
                    );
        }


        $batchNo =
            null;


        if (
            (int) (
                $consumption->order_kitchen_batch_id
                ??
                0
            )
            >
            0
        ) {
            $batchNo =
                DB::table(
                    'order_kitchen_batches'
                )
                    ->where(
                        'id',
                        (int) $consumption->order_kitchen_batch_id
                    )
                    ->value(
                        'batch_no'
                    );
        }


        /*
        |--------------------------------------------------------------------------
        | Source Hash
        |--------------------------------------------------------------------------
        |
        | Include each immutable item snapshot. Historical sync can therefore be
        | run repeatedly without duplicate journals.
        |
        */

        $itemHashPayload =
            $items
                ->map(
                    static function (
                        object $item
                    ): array {

                        return [
                            'id' =>
                                (int) $item->id,

                            'raw_material_id' =>
                                (int) $item->raw_material_id,

                            'quantity' =>
                                number_format(
                                    (float) $item->quantity,
                                    4,
                                    '.',
                                    ''
                                ),

                            'unit_cost' =>
                                number_format(
                                    (float) $item->unit_cost,
                                    4,
                                    '.',
                                    ''
                                ),
                        ];
                    }
                )
                ->values()
                ->all();


        $sourceHash =
            $this->hash([
                'source_type' =>
                    self::SOURCE_RECIPE_CONSUMPTION,

                'source_id' =>
                    (int) $consumption->id,

                'journal_date' =>
                    $journalDate,

                'total_cost' =>
                    number_format(
                        $totalCost,
                        2,
                        '.',
                        ''
                    ),

                'cogs_account_id' =>
                    (int) $cogsAccount->id,

                'inventory_account_id' =>
                    (int) $inventoryAccount->id,

                'items' =>
                    $itemHashPayload,
            ]);


        /*
        |--------------------------------------------------------------------------
        | Description
        |--------------------------------------------------------------------------
        */

        $description =
            'Recipe Consumption';


        if (
            is_string(
                $orderNumber
            )
            &&
            trim(
                $orderNumber
            ) !== ''
        ) {
            $description .=
                ' — '
                .
                trim(
                    $orderNumber
                );
        }


        if (
            $batchNo !==
            null
        ) {
            $description .=
                ' — Batch #'
                .
                $batchNo;
        }


        /*
        |--------------------------------------------------------------------------
        | Ingredient Memo
        |--------------------------------------------------------------------------
        */

        $ingredientMemo =
            $items
                ->map(
                    static function (
                        object $item
                    ): string {

                        $quantity =
                            rtrim(
                                rtrim(
                                    number_format(
                                        (float) $item->quantity,
                                        4,
                                        '.',
                                        ''
                                    ),
                                    '0'
                                ),
                                '.'
                            );


                        return trim(
                            (string) $item->material_name
                        )
                        .
                        ' '
                        .
                        $quantity
                        .
                        ' '
                        .
                        trim(
                            (string) $item->unit
                        );
                    }
                )
                ->implode(
                    ', '
                );


        $ingredientMemo =
            mb_substr(
                $ingredientMemo,
                0,
                1000
            );


        return $this->journalService
            ->syncSource(

                sourceType:
                    self::SOURCE_RECIPE_CONSUMPTION,

                sourceId:
                    (int) $consumption->id,

                sourceHash:
                    $sourceHash,

                journalDate:
                    $journalDate,

                description:
                    $description,

                lines: [
                    [
                        'ledger_account_id' =>
                            (int) $cogsAccount->id,

                        'debit' =>
                            $totalCost,

                        'credit' =>
                            0,

                        'memo' =>
                            $ingredientMemo !== ''
                                ? 'Food cost: '
                                    .
                                    $ingredientMemo
                                : 'Food & Ingredient COGS',
                    ],
                    [
                        'ledger_account_id' =>
                            (int) $inventoryAccount->id,

                        'debit' =>
                            0,

                        'credit' =>
                            $totalCost,

                        'memo' =>
                            'Inventory consumed by kitchen recipe production.',
                    ],
                ],

                user:
                    $actor,

                changeReason:
                    'Recipe consumption financial snapshot changed.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Account Resolver
    |--------------------------------------------------------------------------
    */

    private function systemPostingAccount(
        string $systemKey,
        string $accountType
    ): ChartOfAccount {

        $account =
            ChartOfAccount::query()
                ->where(
                    'system_key',
                    $systemKey
                )
                ->where(
                    'account_type',
                    $accountType
                )
                ->where(
                    'is_active',
                    true
                )
                ->where(
                    'allow_manual_posting',
                    true
                )
                ->first();


        if (! $account) {
            throw ValidationException::withMessages([
                'accounting' => [
                    "Required Inventory/COGS account [{$systemKey}] is unavailable.",
                ],
            ]);
        }


        return $account;
    }


    /*
    |--------------------------------------------------------------------------
    | Actor Resolver
    |--------------------------------------------------------------------------
    */

    private function resolveActor(
        Model $source
    ): User {

        $actorId =
            auth()->id()
            ??
            $source->created_by
            ??
            null;


        if ($actorId) {
            $actor =
                User::query()
                    ->find(
                        $actorId
                    );


            if ($actor) {
                return $actor;
            }
        }


        $admin =
            User::query()
                ->where(
                    'is_active',
                    true
                )
                ->whereNull(
                    'blocked_at'
                )
                ->whereHas(
                    'role',
                    function ($query): void {

                        $query->where(
                            'name',
                            'admin'
                        );
                    }
                )
                ->orderBy(
                    'id'
                )
                ->first();


        if (! $admin) {
            throw ValidationException::withMessages([
                'accounting' => [
                    'An active Admin account is required to post Recipe Consumption accounting journals.',
                ],
            ]);
        }


        return $admin;
    }


    /*
    |--------------------------------------------------------------------------
    | Hash
    |--------------------------------------------------------------------------
    */

    private function hash(
        array $data
    ): string {

        return hash(
            'sha256',
            json_encode(
                $data,
                JSON_THROW_ON_ERROR
            )
        );
    }
}
