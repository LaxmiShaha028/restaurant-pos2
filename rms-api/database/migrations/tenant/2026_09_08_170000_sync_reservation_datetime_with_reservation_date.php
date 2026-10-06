<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Repair Reservation DateTime Values
    |--------------------------------------------------------------------------
    |
    | reservations.start_time and reservations.end_time are DATETIME columns.
    |
    | Older reservation code persisted only H:i values such as "16:30".
    | Because the Reservation model casts these columns as datetime, Laravel
    | could attach the date on which the row was created instead of the selected
    | reservation_date.
    |
    | Example broken row:
    |
    | reservation_date = 2026-09-16
    | start_time       = 2026-09-08 16:30:00
    | end_time         = 2026-09-08 18:30:00
    |
    | Correct row:
    |
    | reservation_date = 2026-09-16
    | start_time       = 2026-09-16 16:30:00
    | end_time         = 2026-09-16 18:30:00
    |
    | This migration keeps the TIME portion and synchronizes the DATE portion
    | with reservation_date for every existing reservation.
    |
    */

    public function up(): void
    {
        DB::connection('tenant')
            ->table('reservations')
            ->select([
                'id',
                'reservation_date',
                'start_time',
                'end_time',
            ])
            ->orderBy('id')
            ->chunkById(
                200,
                function ($rows): void {

                    foreach ($rows as $row) {

                        if (
                            ! $row->reservation_date
                            ||
                            ! $row->start_time
                            ||
                            ! $row->end_time
                        ) {
                            continue;
                        }


                        $date =
                            Carbon::parse(
                                $row->reservation_date
                            )
                                ->format('Y-m-d');


                        $startTime =
                            Carbon::parse(
                                $row->start_time
                            )
                                ->format('H:i:s');


                        $endTime =
                            Carbon::parse(
                                $row->end_time
                            )
                                ->format('H:i:s');


                        DB::connection('tenant')
                            ->table('reservations')
                            ->where(
                                'id',
                                $row->id
                            )
                            ->update([

                                'start_time' =>
                                    "{$date} {$startTime}",

                                'end_time' =>
                                    "{$date} {$endTime}",

                            ]);
                    }
                },
                'id'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Down
    |--------------------------------------------------------------------------
    |
    | There is no safe reverse operation because the old incorrect date portion
    | is not meaningful business data.
    |
    */

    public function down(): void
    {
        // Intentionally left empty.
    }
};
