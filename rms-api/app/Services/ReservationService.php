<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    /*
    |--------------------------------------------------------------------------
    | Create Reservation
    |--------------------------------------------------------------------------
    */

    public function create(
        array $data,
        ?int $userId = null
    ): Reservation {

        return DB::transaction(
            function () use (
                $data,
                $userId
            ): Reservation {

                /*
                |--------------------------------------------------------------------------
                | Validate Table Availability
                |--------------------------------------------------------------------------
                */

                $this->validateTablesAvailability(
                    tableIds:
                        $data['table_ids'],

                    date:
                        $data['reservation_date'],

                    startTime:
                        $data['start_time'],

                    endTime:
                        $data['end_time'],

                    guestCount:
                        (int)
                        $data['guest_count']
                );


                /*
                |--------------------------------------------------------------------------
                | Resolve Customer
                |--------------------------------------------------------------------------
                */

                $customer =
                    $this->resolveCustomer(
                        $data
                    );


                /*
                |--------------------------------------------------------------------------
                | Build Persisted Reservation DateTimes
                |--------------------------------------------------------------------------
                |
                | reservations.start_time and reservations.end_time are DATETIME
                | columns.
                |
                | The request sends H:i values such as "16:30". Persisting only
                | "16:30" into a datetime-cast attribute makes Laravel attach the
                | current date, which breaks future overlap detection.
                |
                | Always combine reservation_date + selected time before save.
                |
                */

                $reservationStartAt =
                    $this->reservationDateTime(
                        $data['reservation_date'],
                        $data['start_time']
                    );


                $reservationEndAt =
                    $this->reservationDateTime(
                        $data['reservation_date'],
                        $data['end_time']
                    );


                /*
                |--------------------------------------------------------------------------
                | Create Reservation
                |--------------------------------------------------------------------------
                */

                $reservation =
                    Reservation::query()->create([

                        'reservation_no' =>
                            $this->generateReservationNumber(),

                        'customer_id' =>
                            $customer->id,

                        'reservation_date' =>
                            $data['reservation_date'],

                        'start_time' =>
                            $reservationStartAt,

                        'end_time' =>
                            $reservationEndAt,

                        'guest_count' =>
                            (int)
                            $data['guest_count'],

                        'advance_amount' =>
                            $data['advance_amount']
                            ??
                            0,

                        'status' =>
                            Reservation::STATUS_RESERVED,

                        'note' =>
                            $data['note']
                            ??
                            null,

                        'created_by' =>
                            $userId,

                    ]);


                /*
                |--------------------------------------------------------------------------
                | Attach Tables
                |--------------------------------------------------------------------------
                */

                $reservation
                    ->tables()
                    ->sync(
                        $data['table_ids']
                    );


                /*
                |--------------------------------------------------------------------------
                | Keep Reservation Metadata on Tables
                |--------------------------------------------------------------------------
                */

                $this->refreshTableReservationMetadata(
                    $data['table_ids']
                );


                /*
                |--------------------------------------------------------------------------
                | Final Response
                |--------------------------------------------------------------------------
                */

                return $reservation->fresh([
                    'customer',
                    'tables',
                    'orders',
                ]);
            },
            3
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Get Reservation
    |--------------------------------------------------------------------------
    */

    public function get(
        Reservation $reservation
    ): Reservation {

        return $reservation->load([
            'customer',
            'tables',
            'orders',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Update Reservation
    |--------------------------------------------------------------------------
    */

    public function update(
        Reservation $reservation,
        array $data,
        ?int $userId = null
    ): Reservation {

        return DB::transaction(
            function () use (
                $reservation,
                $data,
                $userId
            ): Reservation {

                /*
                |--------------------------------------------------------------------------
                | Lock Reservation
                |--------------------------------------------------------------------------
                */

                $lockedReservation =
                    Reservation::query()
                        ->with([
                            'customer',
                            'tables',
                            'orders',
                        ])
                        ->lockForUpdate()
                        ->findOrFail(
                            $reservation->id
                        );


                /*
                |--------------------------------------------------------------------------
                | Finalized Reservation Protection
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedReservation->isFinalized()
                ) {

                    throw ValidationException::withMessages([

                        'reservation' => [
                            'Completed or cancelled reservations cannot be edited.',
                        ],

                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Seated Reservation Protection
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedReservation->status ===
                    Reservation::STATUS_SEATED
                ) {

                    throw ValidationException::withMessages([

                        'reservation' => [
                            'A seated reservation cannot be edited. Manage the related order instead.',
                        ],

                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Active Order Protection
                |--------------------------------------------------------------------------
                |
                | Normally reserved / confirmed reservation should not already
                | have an active order. Still protect against inconsistent state.
                |
                */

                $hasActiveOrder =
                    $lockedReservation
                        ->orders()
                        ->whereNotIn(
                            'status',
                            [
                                Order::STATUS_COMPLETED,
                                Order::STATUS_CANCELED,
                            ]
                        )
                        ->exists();

                if (
                    $hasActiveOrder
                ) {

                    throw ValidationException::withMessages([

                        'reservation' => [
                            'This reservation cannot be edited because an active order exists.',
                        ],

                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Existing Table IDs
                |--------------------------------------------------------------------------
                */

                $oldTableIds =
                    $lockedReservation
                        ->tables
                        ->pluck('id')
                        ->map(
                            static fn (
                                mixed $id
                            ): int =>
                                (int) $id
                        )
                        ->unique()
                        ->values();


                /*
                |--------------------------------------------------------------------------
                | Resolve Customer
                |--------------------------------------------------------------------------
                */

                $customer =
                    $this->resolveCustomer(
                        $data
                    );


                /*
                |--------------------------------------------------------------------------
                | New Table IDs
                |--------------------------------------------------------------------------
                */

                $newTableIds =
                    collect(
                        $data['table_ids']
                    )
                        ->map(
                            static fn (
                                mixed $id
                            ): int =>
                                (int) $id
                        )
                        ->unique()
                        ->values();


                if (
                    $newTableIds->isEmpty()
                ) {

                    throw ValidationException::withMessages([

                        'table_ids' => [
                            'Please select at least one table.',
                        ],

                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Validate Table Availability
                |--------------------------------------------------------------------------
                |
                | Existing tables belonging to this reservation are excluded
                | from reservation-conflict checks.
                |
                */

                $this->validateTablesAvailabilityForUpdate(

                    tableIds:
                        $newTableIds
                            ->all(),

                    date:
                        $data['reservation_date'],

                    startTime:
                        $data['start_time'],

                    endTime:
                        $data['end_time'],

                    guestCount:
                        (int)
                        $data['guest_count'],

                    reservationId:
                        $lockedReservation->id
                );


                /*
                |--------------------------------------------------------------------------
                | Build Persisted Reservation DateTimes
                |--------------------------------------------------------------------------
                */

                $reservationStartAt =
                    $this->reservationDateTime(
                        $data['reservation_date'],
                        $data['start_time']
                    );


                $reservationEndAt =
                    $this->reservationDateTime(
                        $data['reservation_date'],
                        $data['end_time']
                    );


                /*
                |--------------------------------------------------------------------------
                | Update Reservation Master
                |--------------------------------------------------------------------------
                */

                $lockedReservation->update([

                    'customer_id' =>
                        $customer->id,

                    'reservation_date' =>
                        $data['reservation_date'],

                    'start_time' =>
                        $reservationStartAt,

                    'end_time' =>
                        $reservationEndAt,

                    'guest_count' =>
                        (int)
                        $data['guest_count'],

                    'advance_amount' =>
                        $data['advance_amount']
                        ??
                        0,

                    'status' =>
                        $data['status']
                        ??
                        $lockedReservation->status,

                    'note' =>
                        array_key_exists(
                            'note',
                            $data
                        )
                            ? $data['note']
                            : $lockedReservation->note,

                ]);


                /*
                |--------------------------------------------------------------------------
                | Tables Removed from Reservation
                |--------------------------------------------------------------------------
                */

                $releasedTableIds =
                    $oldTableIds
                        ->diff(
                            $newTableIds
                        )
                        ->values();


                /*
                |--------------------------------------------------------------------------
                | Released Tables
                |--------------------------------------------------------------------------
                |
                | Do not change live status or merge links here. A future
                | reservation may be edited while the same table is currently
                | occupied by an unrelated live order.
                |
                */


                /*
                |--------------------------------------------------------------------------
                | Sync Reservation Pivot
                |--------------------------------------------------------------------------
                */

                $lockedReservation
                    ->tables()
                    ->sync(
                        $newTableIds
                            ->all()
                    );


                /*
                |--------------------------------------------------------------------------
                | Refresh Backward-Compatible Reservation Metadata
                |--------------------------------------------------------------------------
                |
                | Refresh both removed and newly assigned tables without
                | touching their current physical status or merge structure.
                |
                */

                $this->refreshTableReservationMetadata(
                    $oldTableIds
                        ->merge(
                            $newTableIds
                        )
                        ->unique()
                        ->values()
                );


                /*
                |--------------------------------------------------------------------------
                | Final Refresh
                |--------------------------------------------------------------------------
                */

                return $lockedReservation->fresh([
                    'customer',
                    'tables',
                    'orders',
                ]);
            },
            3
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Get Available Tables
    |--------------------------------------------------------------------------
    */

    public function getAvailableTables(
        array $data = [],
        ?int $exceptReservationId = null
    ): Collection {

        $date =
            $data['reservation_date']
            ??
            $data['date']
            ??
            null;

        $startTime =
            $data['start_time']
            ??
            null;

        $endTime =
            $data['end_time']
            ??
            null;


        /*
        |--------------------------------------------------------------------------
        | No Schedule Yet
        |--------------------------------------------------------------------------
        |
        | Reservation availability is schedule-based, not based on the table's
        | live physical state. If a schedule has not been supplied yet, return
        | all non-deleted tables and let the validated request narrow the list.
        |
        */

        if (
            ! $date
            ||
            ! $startTime
            ||
            ! $endTime
        ) {

            return RestaurantTable::query()
                ->whereNull(
                    'deleted_at'
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
        | Requested Reservation Window
        |--------------------------------------------------------------------------
        */

        $start =
            $this->reservationDateTime(
                $date,
                $startTime
            );

        $end =
            $this->reservationDateTime(
                $date,
                $endTime
            );


        if (
            $end->lessThanOrEqualTo(
                $start
            )
        ) {
            return collect();
        }


        /*
        |--------------------------------------------------------------------------
        | Schedule Conflicts
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | A table that is occupied RIGHT NOW may still be valid for a FUTURE
        | reservation. Therefore current table.status and active orders are not
        | used as future booking filters.
        |
        | Only another active reservation overlapping the requested window
        | blocks the table here.
        |
        | Overlap:
        | existing_start < requested_end
        | AND
        | existing_end   > requested_start
        |
        */

        $reservedTableIds =
            Reservation::query()
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


        /*
        |--------------------------------------------------------------------------
        | Final Schedule-Available Tables
        |--------------------------------------------------------------------------
        |
        | Do NOT use:
        |
        | - status = available
        | - active order table IDs
        | - individual table capacity >= guest count
        |
        | The UI supports selecting multiple tables, so smaller tables must stay
        | visible and final combined capacity is validated on create/update.
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
    | Seat Reservation
    |--------------------------------------------------------------------------
    */

    public function seat(
        Reservation $reservation,
        ?int $userId = null
    ): Reservation {

        return DB::transaction(
            function () use (
                $reservation
            ): Reservation {

                /*
                |--------------------------------------------------------------------------
                | Lock Reservation
                |--------------------------------------------------------------------------
                */

                $lockedReservation =
                    Reservation::query()
                        ->with([
                            'customer',
                            'tables',
                            'orders',
                        ])
                        ->lockForUpdate()
                        ->findOrFail(
                            $reservation->id
                        );


                /*
                |--------------------------------------------------------------------------
                | Validate Reservation State
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedReservation->status !==
                    Reservation::STATUS_RESERVED
                    &&
                    $lockedReservation->status !==
                    Reservation::STATUS_CONFIRMED
                ) {

                    throw ValidationException::withMessages([

                        'reservation' => [
                            'Only reserved or confirmed reservations can be seated.',
                        ],

                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Tables
                |--------------------------------------------------------------------------
                */

                $reservationTableIds =
                    $lockedReservation
                        ->tables
                        ->pluck('id')
                        ->map(
                            static fn (
                                mixed $id
                            ): int =>
                                (int)
                                $id
                        )
                        ->unique()
                        ->values();


                if (
                    $reservationTableIds->isEmpty()
                ) {

                    throw ValidationException::withMessages([

                        'tables' => [
                            'This reservation has no assigned tables.',
                        ],

                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Lock Physical Tables
                |--------------------------------------------------------------------------
                */

                $tables =
                    RestaurantTable::query()
                        ->whereIn(
                            'id',
                            $reservationTableIds
                        )
                        ->lockForUpdate()
                        ->get();


                if (
                    $tables->count()
                    !==
                    $reservationTableIds->count()
                ) {

                    throw ValidationException::withMessages([

                        'tables' => [
                            'One or more reservation tables no longer exist.',
                        ],

                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Validate Physical State
                |--------------------------------------------------------------------------
                */

                foreach (
                    $tables as $table
                ) {

                    if (
                        $table->status ===
                        RestaurantTable::STATUS_OCCUPIED
                    ) {

                        throw ValidationException::withMessages([

                            'tables' => [
                                "{$table->table_name} is already occupied.",
                            ],

                        ]);
                    }


                    if (
                        $table->status ===
                        RestaurantTable::STATUS_CLEANING
                    ) {

                        throw ValidationException::withMessages([

                            'tables' => [
                                "{$table->table_name} is currently being cleaned.",
                            ],

                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Active Order Check
                    |--------------------------------------------------------------------------
                    */

                    $hasActiveOrder =
                        $table
                            ->activeOrders()
                            ->lockForUpdate()
                            ->exists();


                    if (
                        $hasActiveOrder
                    ) {

                        throw ValidationException::withMessages([

                            'tables' => [
                                "{$table->table_name} already has an active order.",
                            ],

                        ]);
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Resolve Master Table
                |--------------------------------------------------------------------------
                */

                $primaryTable =
                    $tables
                        ->sortBy('id')
                        ->first();


                $mergedTables =
                    $tables
                        ->where(
                            'id',
                            '!=',
                            $primaryTable->id
                        )
                        ->values();


                /*
                |--------------------------------------------------------------------------
                | Clear Existing Merge Links
                |--------------------------------------------------------------------------
                */

                RestaurantTable::query()
                    ->whereIn(
                        'id',
                        $reservationTableIds
                    )
                    ->update([
                        'merged_with_id' =>
                            null,
                    ]);


                /*
                |--------------------------------------------------------------------------
                | Rebuild Merge Structure
                |--------------------------------------------------------------------------
                */

                if (
                    $mergedTables->isNotEmpty()
                ) {

                    RestaurantTable::query()
                        ->whereIn(
                            'id',
                            $mergedTables->pluck('id')
                        )
                        ->update([
                            'merged_with_id' =>
                                $primaryTable->id,
                        ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Occupy Reservation Tables
                |--------------------------------------------------------------------------
                */

                RestaurantTable::query()
                    ->whereIn(
                        'id',
                        $reservationTableIds
                    )
                    ->update([

                        'status' =>
                            RestaurantTable::STATUS_OCCUPIED,

                        'reservation_start_at' =>
                            $lockedReservation
                                ->reservation_date
                            &&
                            $lockedReservation
                                ->start_time
                                ? $this->reservationDateTime(
                                    $lockedReservation
                                        ->reservation_date
                                        ->format('Y-m-d'),

                                    $lockedReservation
                                        ->start_time
                                        ->format('H:i')
                                )
                                : null,

                        'reservation_end_at' =>
                            $lockedReservation
                                ->reservation_date
                            &&
                            $lockedReservation
                                ->end_time
                                ? $this->reservationDateTime(
                                    $lockedReservation
                                        ->reservation_date
                                        ->format('Y-m-d'),

                                    $lockedReservation
                                        ->end_time
                                        ->format('H:i')
                                )
                                : null,
                    ]);


                /*
                |--------------------------------------------------------------------------
                | Reservation -> Seated
                |--------------------------------------------------------------------------
                */

                $lockedReservation->update([

                    'status' =>
                        Reservation::STATUS_SEATED,

                ]);


                /*
                |--------------------------------------------------------------------------
                | Final Refresh
                |--------------------------------------------------------------------------
                */

                return $lockedReservation->fresh([
                    'customer',
                    'tables',
                    'orders',
                ]);
            },
            3
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Cancel Reservation
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Reservation $reservation,
        ?string $reason = null
    ): Reservation {

        return DB::transaction(
            function () use (
                $reservation,
                $reason
            ): Reservation {

                /*
                |--------------------------------------------------------------------------
                | Lock Reservation
                |--------------------------------------------------------------------------
                */

                $lockedReservation =
                    Reservation::query()
                        ->with([
                            'tables',
                            'orders',
                        ])
                        ->lockForUpdate()
                        ->findOrFail(
                            $reservation->id
                        );


                /*
                |--------------------------------------------------------------------------
                | Finalized Protection
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedReservation->isFinalized()
                ) {

                    throw ValidationException::withMessages([

                        'reservation' => [
                            'Completed or cancelled reservation cannot be cancelled again.',
                        ],

                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Seated Protection
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedReservation->status ===
                    Reservation::STATUS_SEATED
                ) {

                    throw ValidationException::withMessages([

                        'reservation' => [
                            'A seated reservation cannot be cancelled. Cancel the related order instead.',
                        ],

                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Active Order Protection
                |--------------------------------------------------------------------------
                */

                $hasActiveOrder =
                    $lockedReservation
                        ->orders()
                        ->whereNotIn(
                            'status',
                            [
                                Order::STATUS_COMPLETED,
                                Order::STATUS_CANCELED,
                            ]
                        )
                        ->exists();


                if (
                    $hasActiveOrder
                ) {

                    throw ValidationException::withMessages([

                        'reservation' => [
                            'This reservation cannot be cancelled because an active order exists.',
                        ],

                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Preserve Existing Note
                |--------------------------------------------------------------------------
                */

                $existingNote =
                    trim(
                        (string)
                        (
                            $lockedReservation->note
                            ??
                            ''
                        )
                    );

                $cancelReason =
                    trim(
                        (string)
                        (
                            $reason
                            ??
                            ''
                        )
                    );

                $newNote =
                    $existingNote !== ''
                        ? $existingNote
                        : '';

                if (
                    $cancelReason !== ''
                ) {

                    $newNote =
                        trim(
                            $newNote
                            .
                            (
                                $newNote !== ''
                                    ? "\n"
                                    : ''
                            )
                            .
                            'Cancellation reason: '
                            .
                            $cancelReason
                        );

                }


                /*
                |--------------------------------------------------------------------------
                | Cancel Reservation
                |--------------------------------------------------------------------------
                */

                $lockedReservation->update([

                    'status' =>
                        Reservation::STATUS_CANCELLED,

                    'note' =>
                        $newNote !== ''
                            ? $newNote
                            : null,

                ]);


                /*
                |--------------------------------------------------------------------------
                | Release Tables
                |--------------------------------------------------------------------------
                */

                $tableIds =
                    $lockedReservation
                        ->tables
                        ->pluck('id')
                        ->map(
                            static fn (
                                mixed $id
                            ): int =>
                                (int)
                                $id
                        )
                        ->unique()
                        ->values();


                /*
                |--------------------------------------------------------------------------
                | Important
                |--------------------------------------------------------------------------
                |
                | Do not set table.status = available here.
                |
                | A FUTURE reservation is allowed to use a table that may be
                | occupied RIGHT NOW. Cancelling that future reservation must
                | never release the live occupied table or destroy its merge
                | structure.
                |
                */


                /*
                |--------------------------------------------------------------------------
                | Detach Reservation Tables
                |--------------------------------------------------------------------------
                */

                $lockedReservation
                    ->tables()
                    ->detach();


                $this->refreshTableReservationMetadata(
                    $tableIds
                );


                /*
                |--------------------------------------------------------------------------
                | Final Refresh
                |--------------------------------------------------------------------------
                */

                return $lockedReservation->fresh([
                    'customer',
                    'tables',
                    'orders',
                ]);
            },
            3
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Customer
    |--------------------------------------------------------------------------
    */

    private function resolveCustomer(
        array $data
    ): Customer {

        $customerId =
            $data['customer_id']
            ??
            null;


        /*
        |--------------------------------------------------------------------------
        | Existing Customer
        |--------------------------------------------------------------------------
        */

        if (
            $customerId
        ) {

            $customer =
                Customer::query()
                    ->active()
                    ->lockForUpdate()
                    ->find(
                        (int)
                        $customerId
                    );


            if (
                ! $customer
            ) {

                throw ValidationException::withMessages([

                    'customer_id' => [
                        'The selected customer does not exist or is inactive.',
                    ],

                ]);
            }


            return $customer;
        }


        /*
        |--------------------------------------------------------------------------
        | New / Quick Reservation Customer
        |--------------------------------------------------------------------------
        |
        | A reservation may be created with ONLY a customer name.
        |
        | Phone and email are optional. This is intentionally different from the
        | full Customer Management form, where you may choose stricter rules.
        |
        */

        $name =
            trim(
                (string)
                (
                    $data['customer_name']
                    ??
                    ''
                )
            );


        if (
            mb_strlen(
                $name
            ) < 2
        ) {

            throw ValidationException::withMessages([

                'customer_name' => [
                    'Customer name must contain at least 2 characters.',
                ],

            ]);
        }


        $phone =
            isset(
                $data['customer_phone']
            )
                ? trim(
                    (string)
                    $data['customer_phone']
                )
                : '';


        $phone =
            $phone !== ''
                ? $phone
                : null;


        $email =
            isset(
                $data['customer_email']
            )
                ? strtolower(
                    trim(
                        (string)
                        $data['customer_email']
                    )
                )
                : '';


        $email =
            $email !== ''
                ? $email
                : null;


        /*
        |--------------------------------------------------------------------------
        | Reuse Exact Contact Match When Optional Contact Is Supplied
        |--------------------------------------------------------------------------
        |
        | Name alone is NOT used for deduplication because different customers
        | may legitimately have the same name.
        |
        */

        if (
            $phone
            ||
            $email
        ) {

            $matchedCustomer =
                Customer::query()
                    ->where(
                        function (
                            $query
                        ) use (
                            $phone,
                            $email
                        ): void {

                            if (
                                $phone
                            ) {

                                $query->where(
                                    'phone',
                                    $phone
                                );
                            }


                            if (
                                $email
                            ) {

                                if (
                                    $phone
                                ) {

                                    $query->orWhere(
                                        'email',
                                        $email
                                    );

                                }
                                else {

                                    $query->where(
                                        'email',
                                        $email
                                    );
                                }
                            }
                        }
                    )
                    ->lockForUpdate()
                    ->first();


            if (
                $matchedCustomer
            ) {

                if (
                    ! $matchedCustomer
                        ->is_active
                ) {

                    throw ValidationException::withMessages([

                        'customer_name' => [
                            'A customer with this phone or email exists but is inactive.',
                        ],

                    ]);
                }


                return $matchedCustomer;
            }
        }


        return Customer::query()->create([

            'name' =>
                $name,

            'phone' =>
                $phone,

            'email' =>
                $email,

            'is_active' =>
                true,

            'total_orders' =>
                0,

            'total_spent' =>
                0,

            'last_visit_at' =>
                null,

            'notes' =>
                null,

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Reservation Number
    |--------------------------------------------------------------------------
    */

    private function generateReservationNumber(): string
    {

        do {

            $number =
                'RES-'
                .
                now()->format('Ymd')
                .
                '-'
                .
                strtoupper(
                    Str::random(8)
                );

        } while (
            Reservation::query()
                ->where(
                    'reservation_no',
                    $number
                )
                ->exists()
        );


        return $number;
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Tables Availability
    |--------------------------------------------------------------------------
    */

    private function validateTablesAvailability(
        array $tableIds,
        string $date,
        string $startTime,
        string $endTime,
        int $guestCount
    ): void {

        $uniqueTableIds =
            collect(
                $tableIds
            )
                ->map(
                    static fn (
                        mixed $id
                    ): int =>
                        (int) $id
                )
                ->unique()
                ->values();


        if (
            $uniqueTableIds->isEmpty()
        ) {

            throw ValidationException::withMessages([

                'table_ids' => [
                    'Please select at least one table.',
                ],

            ]);
        }


        $start =
            $this->reservationDateTime(
                $date,
                $startTime
            );

        $end =
            $this->reservationDateTime(
                $date,
                $endTime
            );


        if (
            $end->lessThanOrEqualTo(
                $start
            )
        ) {

            throw ValidationException::withMessages([

                'end_time' => [
                    'Reservation end time must be after the start time.',
                ],

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Lock Selected Tables
        |--------------------------------------------------------------------------
        |
        | This serializes competing reservation writes for the same tables.
        |
        */

        $tables =
            RestaurantTable::query()
                ->whereIn(
                    'id',
                    $uniqueTableIds
                )
                ->whereNull(
                    'deleted_at'
                )
                ->lockForUpdate()
                ->get();


        if (
            $tables->count()
            !==
            $uniqueTableIds->count()
        ) {

            throw ValidationException::withMessages([

                'table_ids' => [
                    'One or more selected tables no longer exist.',
                ],

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Combined Capacity
        |--------------------------------------------------------------------------
        */

        $totalCapacity =
            (int)
            $tables->sum(
                'capacity'
            );


        if (
            $totalCapacity < $guestCount
        ) {

            throw ValidationException::withMessages([

                'table_ids' => [
                    'The selected tables do not have enough combined capacity for the guest count.',
                ],

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Schedule Conflict
        |--------------------------------------------------------------------------
        |
        | Reservation booking intentionally ignores the table's CURRENT
        | occupied/cleaning/active-order state. A table occupied now can still
        | be booked for a future time slot.
        |
        | Live physical availability is checked later in seat().
        |
        */

        foreach (
            $tables as $table
        ) {

            $hasConflict =
                $table
                    ->reservations()
                    ->whereIn(
                        'status',
                        Reservation::activeStatuses()
                    )
                    ->whereDate(
                        'reservation_date',
                        $date
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
                    ->exists();


            if (
                $hasConflict
            ) {

                throw ValidationException::withMessages([

                    'table_ids' => [
                        "{$table->table_name} is already reserved during the selected time.",
                    ],

                ]);
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Tables Availability for Update
    |--------------------------------------------------------------------------
    */

    private function validateTablesAvailabilityForUpdate(
        array $tableIds,
        string $date,
        string $startTime,
        string $endTime,
        int $guestCount,
        int $reservationId
    ): void {

        $uniqueTableIds =
            collect(
                $tableIds
            )
                ->map(
                    static fn (
                        mixed $id
                    ): int =>
                        (int) $id
                )
                ->unique()
                ->values();


        if (
            $uniqueTableIds->isEmpty()
        ) {

            throw ValidationException::withMessages([

                'table_ids' => [
                    'Please select at least one table.',
                ],

            ]);
        }


        $start =
            $this->reservationDateTime(
                $date,
                $startTime
            );

        $end =
            $this->reservationDateTime(
                $date,
                $endTime
            );


        if (
            $end->lessThanOrEqualTo(
                $start
            )
        ) {

            throw ValidationException::withMessages([

                'end_time' => [
                    'Reservation end time must be after the start time.',
                ],

            ]);
        }


        $tables =
            RestaurantTable::query()
                ->whereIn(
                    'id',
                    $uniqueTableIds
                )
                ->whereNull(
                    'deleted_at'
                )
                ->lockForUpdate()
                ->get();


        if (
            $tables->count()
            !==
            $uniqueTableIds->count()
        ) {

            throw ValidationException::withMessages([

                'table_ids' => [
                    'One or more selected tables no longer exist.',
                ],

            ]);
        }


        $totalCapacity =
            (int)
            $tables->sum(
                'capacity'
            );


        if (
            $totalCapacity < $guestCount
        ) {

            throw ValidationException::withMessages([

                'table_ids' => [
                    'The selected tables do not have enough combined capacity for the guest count.',
                ],

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Schedule Conflict
        |--------------------------------------------------------------------------
        |
        | The reservation being edited is excluded. Current physical table state
        | is intentionally ignored here; live state is enforced only at seating.
        |
        */

        foreach (
            $tables as $table
        ) {

            $hasConflict =
                $table
                    ->reservations()
                    ->where(
                        'reservations.id',
                        '!=',
                        $reservationId
                    )
                    ->whereIn(
                        'status',
                        Reservation::activeStatuses()
                    )
                    ->whereDate(
                        'reservation_date',
                        $date
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
                    ->exists();


            if (
                $hasConflict
            ) {

                throw ValidationException::withMessages([

                    'table_ids' => [
                        "{$table->table_name} is already reserved during the selected time.",
                    ],

                ]);
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Refresh Table Reservation Metadata
    |--------------------------------------------------------------------------
    |
    | reservation_tables + reservations are authoritative.
    |
    | The table-level reservation_start_at / reservation_end_at fields are kept
    | only as backward-compatible metadata. They must never change the table's
    | live status or merge structure.
    |
    */

    private function refreshTableReservationMetadata(
        mixed $tableIds
    ): void {

        $ids =
            collect(
                $tableIds
            )
                ->map(
                    static fn (
                        mixed $id
                    ): int =>
                        (int) $id
                )
                ->filter(
                    static fn (
                        int $id
                    ): bool =>
                        $id > 0
                )
                ->unique()
                ->values();


        foreach (
            $ids as $tableId
        ) {

            $nextReservation =
                Reservation::query()
                    ->whereIn(
                        'status',
                        Reservation::activeStatuses()
                    )
                    ->whereHas(
                        'tables',
                        function (
                            $query
                        ) use (
                            $tableId
                        ): void {

                            $query->where(
                                'restaurant_tables.id',
                                $tableId
                            );
                        }
                    )
                    ->orderBy(
                        'reservation_date'
                    )
                    ->orderBy(
                        'start_time'
                    )
                    ->first();


            $reservationStartAt =
                null;

            $reservationEndAt =
                null;


            if (
                $nextReservation
                &&
                $nextReservation
                    ->reservation_date
                &&
                $nextReservation
                    ->start_time
                &&
                $nextReservation
                    ->end_time
            ) {

                $date =
                    $nextReservation
                        ->reservation_date
                        ->format(
                            'Y-m-d'
                        );


                $reservationStartAt =
                    $this->reservationDateTime(
                        $date,
                        $nextReservation
                            ->start_time
                    );


                $reservationEndAt =
                    $this->reservationDateTime(
                        $date,
                        $nextReservation
                            ->end_time
                    );
            }


            RestaurantTable::query()
                ->whereKey(
                    $tableId
                )
                ->update([

                    'reservation_start_at' =>
                        $reservationStartAt,

                    'reservation_end_at' =>
                        $reservationEndAt,

                ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Reservation DateTime
    |--------------------------------------------------------------------------
    */

    private function reservationDateTime(
        string $date,
        mixed $time
    ): Carbon {

        /*
        |--------------------------------------------------------------------------
        | Normalize date
        |--------------------------------------------------------------------------
        */

        $datePart =
            Carbon::parse(
                $date
            )
                ->format(
                    'Y-m-d'
                );


        /*
        |--------------------------------------------------------------------------
        | Normalize time
        |--------------------------------------------------------------------------
        */

        if (
            $time instanceof Carbon
        ) {

            $timePart =
                $time->format(
                    'H:i'
                );

        }
        else {

            $timePart =
                Carbon::parse(
                    (string)
                    $time
                )
                    ->format(
                        'H:i'
                    );
        }


        return Carbon::createFromFormat(
            'Y-m-d H:i',
            "{$datePart} {$timePart}"
        );
    }
}