<?php

use App\Services\SubscriptionLifecycleService;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Automatic Attendance Sync
|--------------------------------------------------------------------------
*/

Schedule::command(
    'attendance:sync'
)
    ->everyMinute()
    ->withoutOverlapping();


/*
|--------------------------------------------------------------------------
| Automatic Daily Salary Processing
|--------------------------------------------------------------------------
*/

Schedule::command(
    'salary:process-daily'
)
    ->dailyAt('23:59')
    ->timezone(
        config('app.timezone')
    )
    ->withoutOverlapping();

Schedule::command(
    'salary:process-tenants'
)
    ->cron(
        '10 0,4,8 * * *'
    )
    ->timezone(
        config(
            'app.timezone',
            'Asia/Dhaka'
        )
    )
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Automatic Subscription Expiry
|--------------------------------------------------------------------------
|
| Runs the lifecycle service directly every minute.
| This removes the duplicate subscriptions:expire schedules and avoids
| depending on a separate command implementation for this critical rule.
|
*/

Schedule::call(
    function (): void {
        app(
            SubscriptionLifecycleService::class
        )->expireDueSubscriptions();
    }
)
    ->name('expire-due-subscriptions')
    ->everyMinute()
    ->withoutOverlapping();
