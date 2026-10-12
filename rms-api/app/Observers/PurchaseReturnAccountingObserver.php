<?php

namespace App\Observers;

use App\Models\PurchaseReturn;
use App\Models\User;
use App\Services\PurchaseReturnAccountingPostingService;
use Illuminate\Support\Facades\DB;
use Throwable;

class PurchaseReturnAccountingObserver
{
    private static array $queued =
        [];


    public function created(
        PurchaseReturn $purchaseReturn
    ): void {
        $this->queueSync(
            $purchaseReturn
        );
    }


    public function updated(
        PurchaseReturn $purchaseReturn
    ): void {
        if (
            ! $purchaseReturn->wasChanged([
                'expected_supplier_credit',
                'inventory_value_reduction',
                'valuation_variance',
                'returned_at',
            ])
        ) {
            return;
        }

        $this->queueSync(
            $purchaseReturn
        );
    }


    private function queueSync(
        PurchaseReturn $purchaseReturn
    ): void {
        $id =
            (int) $purchaseReturn->id;

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
            $purchaseReturn
                ->updated_by
            ??
            $purchaseReturn
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
                        PurchaseReturn::query()
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
                    )->syncReturn(
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
