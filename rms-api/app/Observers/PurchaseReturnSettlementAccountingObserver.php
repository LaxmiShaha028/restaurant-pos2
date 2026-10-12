<?php

namespace App\Observers;

use App\Models\PurchaseReturnSettlement;
use App\Models\User;
use App\Services\PurchaseReturnAccountingPostingService;
use Illuminate\Support\Facades\DB;
use Throwable;

class PurchaseReturnSettlementAccountingObserver
{
    private static array $queued =
        [];


    public function created(
        PurchaseReturnSettlement $settlement
    ): void {
        $id =
            (int) $settlement->id;

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
            $settlement
                ->updated_by
            ??
            $settlement
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
                        PurchaseReturnSettlement::query()
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
                        PurchaseReturnAccountingPostingService::class
                    )->syncSettlement(
                        $fresh,
                        $actor
                    );

                } catch (Throwable $exception) {
                    /*
                    |--------------------------------------------------------------------------
                    | Business Transaction Is Already Committed
                    |--------------------------------------------------------------------------
                    |
                    | Do not report a false business failure to the restaurant user.
                    | Laravel logs the accounting error and the idempotent
                    | accounting:sync-purchase-returns command can safely repair it.
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
