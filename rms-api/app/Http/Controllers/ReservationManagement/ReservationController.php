<?php

namespace App\Http\Controllers\ReservationManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AvailableTableRequest;
use App\Http\Requests\Api\CancelReservationRequest;
use App\Http\Requests\Api\StoreReservationRequest;
use App\Http\Requests\Api\UpdateReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function __construct(
        protected ReservationService $service
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Reservation List
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): JsonResponse {

        $query =
            Reservation::query()
                ->with([
                    'customer',
                    'tables',
                    'orders',
                ]);


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('search')
        ) {

            $search =
                trim(
                    (string)
                    $request->input(
                        'search'
                    )
                );

            $query->where(
                function (
                    $builder
                ) use (
                    $search
                ): void {

                    $builder
                        ->where(
                            'reservation_no',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'customer',
                            function (
                                $customerQuery
                            ) use (
                                $search
                            ): void {

                                $customerQuery
                                    ->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'phone',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        )
                        ->orWhereHas(
                            'tables',
                            function (
                                $tableQuery
                            ) use (
                                $search
                            ): void {

                                $tableQuery
                                    ->where(
                                        'table_name',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('status')
        ) {

            $query->where(
                'status',
                $request->input('status')
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Exact Reservation Date
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'reservation_date'
            )
        ) {

            $query->whereDate(
                'reservation_date',
                $request->input(
                    'reservation_date'
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Date From
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('date_from')
        ) {

            $query->whereDate(
                'reservation_date',
                '>=',
                $request->input(
                    'date_from'
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Date To
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('date_to')
        ) {

            $query->whereDate(
                'reservation_date',
                '<=',
                $request->input(
                    'date_to'
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $sort =
            $request->input(
                'sort',
                'latest'
            );

        match ($sort) {

            'oldest' =>
                $query->orderBy(
                    'created_at'
                ),

            'date_asc' =>
                $query
                    ->orderBy(
                        'reservation_date'
                    )
                    ->orderBy(
                        'start_time'
                    ),

            'date_desc' =>
                $query
                    ->orderByDesc(
                        'reservation_date'
                    )
                    ->orderByDesc(
                        'start_time'
                    ),

            default =>
                $query->latest('id'),

        };


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage =
            max(
                5,
                min(
                    (int)
                    $request->input(
                        'per_page',
                        10
                    ),
                    100
                )
            );

        $reservations =
            $query->paginate(
                $perPage
            );


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        |
        | Summary date filter follows date_from/date_to.
        | Pagination or search does not affect summary totals.
        |
        */

        $summaryQuery =
            Reservation::query();


        if (
            $request->filled(
                'date_from'
            )
        ) {

            $summaryQuery->whereDate(
                'reservation_date',
                '>=',
                $request->input(
                    'date_from'
                )
            );
        }


        if (
            $request->filled(
                'date_to'
            )
        ) {

            $summaryQuery->whereDate(
                'reservation_date',
                '<=',
                $request->input(
                    'date_to'
                )
            );
        }


        $totalAdvance =
            (float)
            (clone $summaryQuery)
                ->whereIn(
                    'status',
                    Reservation::activeStatuses()
                )
                ->sum(
                    'advance_amount'
                );


        $summary = [

            'total' =>
                (clone $summaryQuery)
                    ->count(),

            'reserved' =>
                (clone $summaryQuery)
                    ->where(
                        'status',
                        Reservation::STATUS_RESERVED
                    )
                    ->count(),

            'confirmed' =>
                (clone $summaryQuery)
                    ->where(
                        'status',
                        Reservation::STATUS_CONFIRMED
                    )
                    ->count(),

            'seated' =>
                (clone $summaryQuery)
                    ->where(
                        'status',
                        Reservation::STATUS_SEATED
                    )
                    ->count(),

            'completed' =>
                (clone $summaryQuery)
                    ->where(
                        'status',
                        Reservation::STATUS_COMPLETED
                    )
                    ->count(),

            'cancelled' =>
                (clone $summaryQuery)
                    ->where(
                        'status',
                        Reservation::STATUS_CANCELLED
                    )
                    ->count(),

            'total_guests' =>
                (int)
                (clone $summaryQuery)
                    ->whereIn(
                        'status',
                        Reservation::activeStatuses()
                    )
                    ->sum(
                        'guest_count'
                    ),

            'total_advance' =>
                $totalAdvance,

            'total_advance_formatted' =>
                '৳ '
                .
                number_format(
                    $totalAdvance,
                    2
                ),
        ];


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' =>
                true,

            'message' =>
                'Reservations retrieved successfully.',

            'data' =>
                ReservationResource::collection(
                    $reservations
                        ->getCollection()
                ),

            'summary' =>
                $summary,

            'meta' => [

                'current_page' =>
                    $reservations->currentPage(),

                'last_page' =>
                    $reservations->lastPage(),

                'per_page' =>
                    $reservations->perPage(),

                'total' =>
                    $reservations->total(),

                'from' =>
                    $reservations->firstItem(),

                'to' =>
                    $reservations->lastItem(),
            ],

            'filters' => [

                'statuses' => [

                    [
                        'value' =>
                            Reservation::STATUS_RESERVED,

                        'label' =>
                            'Reserved',
                    ],

                    [
                        'value' =>
                            Reservation::STATUS_CONFIRMED,

                        'label' =>
                            'Confirmed',
                    ],

                    [
                        'value' =>
                            Reservation::STATUS_SEATED,

                        'label' =>
                            'Seated',
                    ],

                    [
                        'value' =>
                            Reservation::STATUS_COMPLETED,

                        'label' =>
                            'Completed',
                    ],

                    [
                        'value' =>
                            Reservation::STATUS_CANCELLED,

                        'label' =>
                            'Cancelled',
                    ],
                ],
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Available Tables
    |--------------------------------------------------------------------------
    */

    public function availableTables(
        AvailableTableRequest $request
    ): JsonResponse {

        $tables =
            $this->service
                ->getAvailableTables(
                    $request->validated()
                );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Available tables retrieved successfully.',

            'data' =>
                $tables
                    ->map(
                        static function (
                            RestaurantTable $table
                        ): array {

                            return [

                                'id' =>
                                    (int)
                                    $table->id,

                                'table_name' =>
                                    $table->table_name,

                                'capacity' =>
                                    (int)
                                    $table->capacity,

                                'section' =>
                                    $table->section,

                                'status' =>
                                    $table->status,

                                'current_status' =>
                                    $table->current_status,

                                'reservation_start_at' =>
                                    $table
                                        ->reservation_start_at
                                        ?->toISOString(),

                                'reservation_end_at' =>
                                    $table
                                        ->reservation_end_at
                                        ?->toISOString(),
                            ];
                        }
                    )
                    ->values(),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Create Reservation
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreReservationRequest $request
    ): JsonResponse {

        $reservation =
            $this->service->create(
                data:
                    $request->validated(),

                userId:
                    $request->user()?->id
            );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Reservation created successfully.',

            'data' =>
                new ReservationResource(
                    $reservation
                ),

        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | Show Single Reservation
    |--------------------------------------------------------------------------
    */

    public function show(
        Reservation $reservation
    ): JsonResponse {

        $reservation =
            $this->service->get(
                $reservation
            );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Reservation retrieved successfully.',

            'data' =>
                new ReservationResource(
                    $reservation
                ),

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Edit Options
    |--------------------------------------------------------------------------
    |
    | Returns:
    |
    | - Complete reservation
    | - Current selected table IDs
    | - Available tables for requested edit window
    |
    | Current reservation itself is excluded from conflict checking.
    |
    */

    public function editOptions(
        Request $request,
        Reservation $reservation
    ): JsonResponse {

        $reservation =
            $this->service->get(
                $reservation
            );


        /*
        |--------------------------------------------------------------------------
        | Resolve Edit Window
        |--------------------------------------------------------------------------
        |
        | Frontend may send temporary date/time/guest_count values while
        | changing the edit form.
        |
        | Missing values fall back to the current reservation.
        |
        */

        $date =
            $request->input(
                'reservation_date',
                $reservation
                    ->reservation_date
                    ?->format('Y-m-d')
            );


        $startTime =
            $request->input(
                'start_time',
                $reservation
                    ->start_time
                    ?->format('H:i')
            );


        $endTime =
            $request->input(
                'end_time',
                $reservation
                    ->end_time
                    ?->format('H:i')
            );


        $guestCount =
            (int)
            $request->input(
                'guest_count',
                $reservation->guest_count
            );


        /*
        |--------------------------------------------------------------------------
        | Basic Edit Window Validation
        |--------------------------------------------------------------------------
        */

        if (
            ! $date
            ||
            ! $startTime
            ||
            ! $endTime
        ) {

            throw ValidationException::withMessages([

                'reservation' => [
                    'Reservation date, start time and end time are required.',
                ],

            ]);
        }


        try {

            $start =
                Carbon::createFromFormat(
                    'Y-m-d H:i',
                    "{$date} {$startTime}"
                );


            $end =
                Carbon::createFromFormat(
                    'Y-m-d H:i',
                    "{$date} {$endTime}"
                );

        }
        catch (
            \Throwable
        ) {

            throw ValidationException::withMessages([

                'reservation' => [
                    'The requested reservation date or time is invalid.',
                ],

            ]);
        }


        if (
            $end->lessThanOrEqualTo(
                $start
            )
        ) {

            throw ValidationException::withMessages([

                'end_time' => [
                    'Reservation end time must be after the reservation start time.',
                ],

            ]);
        }


        if (
            $guestCount < 1
        ) {

            throw ValidationException::withMessages([

                'guest_count' => [
                    'Guest count must be at least 1.',
                ],

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Available Tables
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Current reservation is excluded from reservation conflict checking.
        |
        */

        $availableTables =
            $this->service
                ->getAvailableTables(

                    [
                        'reservation_date' =>
                            Carbon::parse(
                                $date
                            )->format(
                                'Y-m-d'
                            ),

                        'start_time' =>
                            Carbon::parse(
                                $start
                            )->format(
                                'H:i'
                            ),

                        'end_time' =>
                            Carbon::parse(
                                $end
                            )->format(
                                'H:i'
                            ),

                        'guest_count' =>
                            $guestCount,
                    ],

                    (int)
                    $reservation->id
                );


        /*
        |--------------------------------------------------------------------------
        | Current Tables
        |--------------------------------------------------------------------------
        */

        $currentTableIds =
            $reservation
                ->tables
                ->pluck('id')
                ->map(
                    static fn (
                        mixed $id
                    ): int =>
                        (int) $id
                )
                ->values()
                ->all();


        /*
        |--------------------------------------------------------------------------
        | Current Table Data
        |--------------------------------------------------------------------------
        |
        | Even if current table is physically occupied/unavailable,
        | it remains visible in edit UI as the currently selected table.
        |
        */

        $currentTables =
            $reservation
                ->tables
                ->map(
                    static function (
                        RestaurantTable $table
                    ): array {

                        return [

                            'id' =>
                                (int)
                                $table->id,

                            'table_name' =>
                                $table->table_name,

                            'capacity' =>
                                (int)
                                $table->capacity,

                            'section' =>
                                $table->section,

                            'status' =>
                                $table->status,

                            'current_status' =>
                                $table->current_status,

                            'reservation_start_at' =>
                                $table
                                    ->reservation_start_at
                                    ?->toISOString(),

                            'reservation_end_at' =>
                                $table
                                    ->reservation_end_at
                                    ?->toISOString(),

                            'is_current' =>
                                true,
                        ];
                    }
                );


        /*
        |--------------------------------------------------------------------------
        | Available Table Data
        |--------------------------------------------------------------------------
        */

        $availableTableData =
            $availableTables
                ->map(
                    static function (
                        RestaurantTable $table
                    ): array {

                        return [

                            'id' =>
                                (int)
                                $table->id,

                            'table_name' =>
                                $table->table_name,

                            'capacity' =>
                                (int)
                                $table->capacity,

                            'section' =>
                                $table->section,

                            'status' =>
                                $table->status,

                            'current_status' =>
                                $table->current_status,

                            'reservation_start_at' =>
                                $table
                                    ->reservation_start_at
                                    ?->toISOString(),

                            'reservation_end_at' =>
                                $table
                                    ->reservation_end_at
                                    ?->toISOString(),

                            'is_current' =>
                                false,
                        ];
                    }
                );


        /*
        |--------------------------------------------------------------------------
        | Merge Current + Available
        |--------------------------------------------------------------------------
        |
        | Duplicate table IDs must never appear twice.
        |
        */

        $mergedTables =
            $currentTables
                ->concat(
                    $availableTableData
                )
                ->unique('id')
                ->sortBy([
                    [
                        'capacity',
                        'asc',
                    ],

                    [
                        'table_name',
                        'asc',
                    ],
                ])
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' =>
                true,

            'message' =>
                'Reservation edit information retrieved successfully.',

            'data' => [

                'reservation' =>
                    new ReservationResource(
                        $reservation
                    ),

                'current_table_ids' =>
                    $currentTableIds,

                'available_tables' =>
                    $mergedTables,

                'edit_window' => [

                    'reservation_date' =>
                        Carbon::parse(
                            $date
                        )->format(
                            'Y-m-d'
                        ),

                    'start_time' =>
                        $start->format(
                            'H:i'
                        ),

                    'end_time' =>
                        $end->format(
                            'H:i'
                        ),

                    'guest_count' =>
                        $guestCount,
                ],
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Update Reservation
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateReservationRequest $request,
        Reservation $reservation
    ): JsonResponse {

        $updatedReservation =
            $this->service->update(

                reservation:
                    $reservation,

                data:
                    $request->validated(),

                userId:
                    $request->user()?->id
            );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Reservation updated successfully.',

            'data' =>
                new ReservationResource(
                    $updatedReservation
                ),

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Cancel Reservation
    |--------------------------------------------------------------------------
    */

    public function cancel(
        CancelReservationRequest $request,
        Reservation $reservation
    ): JsonResponse {

        $cancelledReservation =
            $this->service->cancel(

                reservation:
                    $reservation,

                reason:
                    $request->input(
                        'reason',
                        'No reason provided'
                    )
            );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Reservation cancelled successfully.',

            'data' =>
                new ReservationResource(
                    $cancelledReservation
                ),

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Seat Reservation
    |--------------------------------------------------------------------------
    */

    public function seat(
        Request $request,
        Reservation $reservation
    ): JsonResponse {

        $reservation =
            $this->service->seat(

                reservation:
                    $reservation,

                userId:
                    $request->user()?->id
            );


        return response()->json([

            'success' =>
                true,

            'message' =>
                'Reservation seated successfully.',

            'data' =>
                new ReservationResource(
                    $reservation
                ),

        ]);
    }
}