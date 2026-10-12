<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReservationsPaginationTestSeeder extends Seeder
{
    private const RESERVATION_PREFIX = 'TEST-RES-';

    public function run(): int
    {
        $connection = DB::connection('tenant');
        $baseTime = now();

        return $connection->transaction(function () use ($connection, $baseTime): int {
            $existingReservationIds = $connection->table('reservations')
                ->where('reservation_no', 'like', self::RESERVATION_PREFIX . '%')
                ->pluck('id');

            if ($existingReservationIds->isNotEmpty()) {
                if ($connection->getSchemaBuilder()->hasTable('reservation_tables')) {
                    $connection->table('reservation_tables')
                        ->whereIn('reservation_id', $existingReservationIds)
                        ->delete();
                }

                $connection->table('reservations')
                    ->whereIn('id', $existingReservationIds)
                    ->delete();
            }

            $reservationDate = $baseTime->copy()->addDay()->startOfDay();

            for ($number = 1; $number <= 10; $number++) {
                $startTime = $reservationDate->copy()->setTime(12 + intdiv($number - 1, 2), ($number % 2) * 30);
                $endTime = $startTime->copy()->addHours(2);

                $connection->table('reservations')->insert([
                    'reservation_no' => sprintf(
                        '%s%s-%02d',
                        self::RESERVATION_PREFIX,
                        $baseTime->format('YmdHis'),
                        $number,
                    ),
                    'reservation_date' => $reservationDate->toDateString(),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'guest_count' => 2 + ($number % 5),
                    'advance_amount' => 0,
                    'status' => 'reserved',
                    'note' => sprintf('Pagination test reservation %02d.', $number),
                    'created_at' => $baseTime,
                    'updated_at' => $baseTime,
                ]);
            }

            return 10;
        });
    }
}
