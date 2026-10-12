<?php

namespace App\Console\Commands;

use App\Services\SubscriptionLifecycleService;
use Illuminate\Console\Command;
use Throwable;

class ExpireSubscriptions extends Command
{
    /*
    |--------------------------------------------------------------------------
    | Signature
    |--------------------------------------------------------------------------
    */

    protected $signature =
        'subscriptions:expire';


    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    */

    protected $description =
        'Expire due subscriptions and deactivate their restaurants.';


    /*
    |--------------------------------------------------------------------------
    | Handle
    |--------------------------------------------------------------------------
    */

    public function handle(
        SubscriptionLifecycleService $lifecycleService
    ): int {

        $this->info(
            'Checking for expired subscriptions...'
        );


        try {

            $result =
                $lifecycleService
                    ->expireDueSubscriptions();


            $expiredCount =
                (int) (
                    $result['expired_count']
                    ?? 0
                );


            $restaurantIds =
                $result['restaurant_ids']
                ?? [];


            /*
            |--------------------------------------------------------------------------
            | Output
            |--------------------------------------------------------------------------
            */

            if (
                $expiredCount === 0
            ) {

                $this->info(
                    'No subscriptions required expiration.'
                );

                return self::SUCCESS;
            }


            $this->info(
                "Expired subscriptions: {$expiredCount}"
            );


            if (
                $restaurantIds !== []
            ) {

                $this->info(
                    'Deactivated restaurant IDs: '
                    .
                    implode(
                        ', ',
                        $restaurantIds
                    )
                );
            }


            return self::SUCCESS;

        } catch (
            Throwable $exception
        ) {

            report(
                $exception
            );

            $this->error(
                'Subscription expiry process failed.'
            );

            $this->error(
                $exception->getMessage()
            );

            return self::FAILURE;
        }
    }
}
