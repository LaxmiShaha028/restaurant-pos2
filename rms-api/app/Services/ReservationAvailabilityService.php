<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\RestaurantTable;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReservationAvailabilityService
{
    /*
    |--------------------------------------------------------------------------
    | Get Schedule-Available Tables
    |--------------------------------------------------------------------------
    |
    | Reservation availability is based on the requested DATE/TIME window.
    |
    | A table that is occupied right now can still be reserved for a future
    | time. Current physical availability belongs to the seating flow.
    |
    */

    public function getAvailableTables(
        array $data,
        ?int $exceptReservationId = null
    ): Collection {

        $date =
            Carbon::parse(
                $data['date']
                ??
                $data['reservation_date']
            )
                ->format(
                    'Y-m-d'
                );

        $start =
            $this->buildDateTime(
                $date,
                $data['start_time']
            );

        $end =
            $this->buildDateTime(
                $date,
                $data['end_time']
            );


        if (
            $end->lessThanOrEqualTo(
                $start
            )
        ) {
            return collect();
        }


        $reservedTableIds =
            $this->getReservedTableIds(
                date:
                    $date,

                start:
                    $start,

                end:
                    $end,

                exceptReservationId:
                    $exceptReservationId
            );


        /*
        |--------------------------------------------------------------------------
        | Final Schedule Availability
        |--------------------------------------------------------------------------
        |
        | Do not filter using:
        |
        | - RestaurantTable.status
        | - active Order table IDs
        | - individual table capacity >= total guest count
        |
        | Multiple smaller tables may be selected together.
        |
        */

        return RestaurantTable::query()
            ->whereNull(
                'deleted_at'
            )
            ->whereNotIn(
                'id',
                $reservedTableIds
            )
            ->orderBy(
                'capacity'
            )
            ->orderBy(
                'table_name'
            )
            ->get();
    }


    /*
    |--------------------------------------------------------------------------
    | Reserved Table IDs
    |--------------------------------------------------------------------------
    */

    private function getReservedTableIds(
        string $date,
        Carbon $start,
        Carbon $end,
        ?int $exceptReservationId = null
    ): array {

        return Reservation::query()
            ->whereDate(
                'reservation_date',
                $date
            )
            ->whereIn(
                'status',
                Reservation::activeStatuses()
            )
            ->when(
                $exceptReservationId !== null,
                function (
                    $query
                ) use (
                    $exceptReservationId
                ): void {

                    $query->where(
                        'id',
                        '!=',
                        $exceptReservationId
                    );
                }
            )
            ->where(
                'start_time',
                '<',
                $end
            )
            ->where(
                'end_time',
                '>',
                $start
            )
            ->with(
                'tables:id'
            )
            ->get()
            ->flatMap(
                static function (
                    Reservation $reservation
                ): Collection {

                    return $reservation
                        ->tables
                        ->pluck('id');
                }
            )
            ->map(
                static fn (
                    mixed $id
                ): int =>
                    (int) $id
            )
            ->unique()
            ->values()
            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Build Reservation DateTime
    |--------------------------------------------------------------------------
    */

    private function buildDateTime(
        string $date,
        mixed $time
    ): Carbon {

        if (
            $time instanceof Carbon
        ) {

            $timeString =
                $time->format(
                    'H:i'
                );

        }
        else {

            $timeString =
                Carbon::parse(
                    (string) $time
                )
                    ->format(
                        'H:i'
                    );
        }


        return Carbon::createFromFormat(
            'Y-m-d H:i',
            $date
            .
            ' '
            .
            $timeString
        );
    }
}
