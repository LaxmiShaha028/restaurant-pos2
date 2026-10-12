<?php

namespace App\Observers;

use App\Models\OrderRecipeConsumption;
use App\Models\User;
use App\Services\InventoryConsumptionAccountingPostingService;
use Illuminate\Support\Facades\DB;
use Throwable;

class OrderRecipeConsumptionAccountingObserver
{
    private static array $queued =
        [];


    /*
    |--------------------------------------------------------------------------
    | Consumption Created
    |--------------------------------------------------------------------------
    |
    | Existing RecipeConsumptionService creates:
    |
    | 1. consumption header
    | 2. immutable consumption items
    | 3. restaurant stock deductions
    | 4. recipe-consumption stock movements
    |
    | Therefore accounting runs AFTER COMMIT and reloads the final immutable
    | consumption items.
    |
    */

    public function created(
        OrderRecipeConsumption $consumption
    ): void {

        $id =
            (int) $consumption->id;


        if (
            $id <= 0
            ||
            isset(
                self::$queued[
                    $id
                ]
            )
        ) {
            return;
        }


        self::$queued[
            $id
        ] =
            true;


        $actorId =
            auth()->id()
            ??
            $consumption
                ->created_by
            ??
            null;


        $callback =
            function () use (
                $id,
                $actorId
            ): void {

                unset(
                    self::$queued[
                        $id
                    ]
                );


                try {

                    $fresh =
                        OrderRecipeConsumption::query()
                            ->find(
                                $id
                            );


                    if (! $fresh) {
                        return;
                    }


                    $actor =
                        $actorId
                            ? User::query()
                                ->find(
                                    $actorId
                                )
                            : null;


                    app(
                        InventoryConsumptionAccountingPostingService::class
                    )->syncRecipeConsumption(
                        $fresh,
                        $actor
                    );

                } catch (Throwable $exception) {

                    /*
                    |--------------------------------------------------------------------------
                    | Kitchen Consumption Is Already Committed
                    |--------------------------------------------------------------------------
                    |
                    | Do not make the Chef see a false "Start Preparing failed"
                    | response after the stock consumption already succeeded.
                    |
                    | The error is logged and can be repaired safely with:
                    |
                    | accounting:sync-recipe-cogs
                    |
                    */

                    report(
                        $exception
                    );
                }
            };


        if (
            DB::transactionLevel()
            >
            0
        ) {
            DB::afterCommit(
                $callback
            );

            return;
        }


        $callback();
    }
}
