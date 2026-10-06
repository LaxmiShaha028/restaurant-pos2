<?php

namespace App\Http\Resources;

use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantTableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(
        Request $request
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Merge Master ID
        |--------------------------------------------------------------------------
        */

        $mergeMasterId =
            $this->merged_with_id !== null
                ? (int) $this->merged_with_id
                : (int) $this->id;


        /*
        |--------------------------------------------------------------------------
        | Complete Merge Group
        |--------------------------------------------------------------------------
        */

        $mergeGroup =
            RestaurantTable::query()
                ->where(
                    function (
                        $query
                    ) use (
                        $mergeMasterId
                    ): void {

                        $query
                            ->where(
                                'id',
                                $mergeMasterId
                            )
                            ->orWhere(
                                'merged_with_id',
                                $mergeMasterId
                            );

                    }
                )
                ->orderBy('id')
                ->get();


        $isMerged =
            $mergeGroup->count() > 1;


        /*
        |--------------------------------------------------------------------------
        | Merge Group IDs
        |--------------------------------------------------------------------------
        */

        $mergeGroupIds =
            $isMerged
                ? $mergeGroup
                    ->pluck('id')
                    ->map(
                        fn (
                            mixed $id
                        ): int =>
                            (int) $id
                    )
                    ->values()
                    ->all()
                : [];


        /*
        |--------------------------------------------------------------------------
        | Other Merged Table IDs
        |--------------------------------------------------------------------------
        */

        $mergedWithIds =
            $isMerged
                ? $mergeGroup
                    ->pluck('id')
                    ->map(
                        fn (
                            mixed $id
                        ): int =>
                            (int) $id
                    )
                    ->reject(
                        fn (
                            int $id
                        ): bool =>
                            $id ===
                            (int) $this->id
                    )
                    ->values()
                    ->all()
                : [];


        /*
        |--------------------------------------------------------------------------
        | Merge Group Table Details
        |--------------------------------------------------------------------------
        */

        $mergeGroupTables =
            $isMerged
                ? $mergeGroup
                    ->map(
                        fn (
                            RestaurantTable $table
                        ): array => [

                            'id' =>
                                (int) $table->id,

                            'table_name' =>
                                $table->table_name,

                            'capacity' =>
                                (int) $table->capacity,

                            'section' =>
                                $table->section,

                            'section_label' =>
                                $this->formatSectionLabel(
                                    $table->section
                                ),

                            'status' =>
                                $table->status,

                            'status_label' =>
                                $this->formatStatusLabel(
                                    $table->status
                                ),

                            'current_status' =>
                                $table->current_status,

                            'current_status_label' =>
                                $this->formatStatusLabel(
                                    $table->current_status
                                ),

                            /*
                             * Legacy reservation fields are still returned
                             * for backward compatibility.
                             */
                            'reservation_start_at' =>
                                $table
                                    ->reservation_start_at
                                    ?->toISOString(),

                            'reservation_end_at' =>
                                $table
                                    ->reservation_end_at
                                    ?->toISOString(),

                            'merged_with_id' =>
                                $table->merged_with_id !== null
                                    ? (int)
                                        $table->merged_with_id
                                    : null,

                            'is_master' =>
                                (int) $table->id ===
                                $mergeMasterId,
                        ]
                    )
                    ->values()
                    ->all()
                : [];


        /*
        |--------------------------------------------------------------------------
        | Reservation Information
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | New reservation system is:
        |
        | reservations
        |      ↓
        | reservation_tables
        |      ↓
        | restaurant_tables
        |
        | Active reservation gets priority over upcoming reservation.
        |
        */

        $activeReservation =
            $this->getActiveReservation();


        $upcomingReservation =
            $this->getUpcomingReservation();


        /*
        |--------------------------------------------------------------------------
        | Current / Display Reservation
        |--------------------------------------------------------------------------
        */

        $currentReservation =
            $activeReservation
            ??
            $upcomingReservation;


        $hasActiveReservation =
            $activeReservation !== null;


        $hasUpcomingReservation =
            $upcomingReservation !== null;


        $hasValidReservation =
            $hasActiveReservation ||
            $hasUpcomingReservation;


        $reservationType =
            $hasActiveReservation
                ? 'active'
                : (
                    $hasUpcomingReservation
                        ? 'upcoming'
                        : null
                );


        /*
        |--------------------------------------------------------------------------
        | Reservation Schedule
        |--------------------------------------------------------------------------
        */

        $reservationStartAt =
            $currentReservation?->start_time;


        $reservationEndAt =
            $currentReservation?->end_time;


        /*
        |--------------------------------------------------------------------------
        | Return Response
        |--------------------------------------------------------------------------
        */

        return [

            /*
            |--------------------------------------------------------------------------
            | Basic Table Information
            |--------------------------------------------------------------------------
            */

            'id' =>
                (int) $this->id,

            'table_id' =>
                (int) $this->id,

            'table_name' =>
                $this->table_name,

            'capacity' =>
                (int) $this->capacity,

            'section' =>
                $this->section,

            'section_label' =>
                $this->formatSectionLabel(
                    $this->section
                ),


            /*
            |--------------------------------------------------------------------------
            | Raw Database Status
            |--------------------------------------------------------------------------
            */

            'status' =>
                $this->status,

            'status_label' =>
                $this->formatStatusLabel(
                    $this->status
                ),


            /*
            |--------------------------------------------------------------------------
            | Effective Current Status
            |--------------------------------------------------------------------------
            |
            | Occupied
            |   ↓
            | Cleaning
            |   ↓
            | Active Reservation
            |   ↓
            | Available
            |
            */

            'current_status' =>
                $this->current_status,

            'current_status_label' =>
                $this->formatStatusLabel(
                    $this->current_status
                ),


            /*
            |--------------------------------------------------------------------------
            | Reservation Summary
            |--------------------------------------------------------------------------
            */

            'has_reservation' =>
                $hasValidReservation,

            'has_reservation_schedule' =>
                $hasValidReservation,

            'has_active_reservation' =>
                $hasActiveReservation,

            'has_upcoming_reservation' =>
                $hasUpcomingReservation,

            'reservation_type' =>
                $reservationType,


            /*
            |--------------------------------------------------------------------------
            | New Reservation Object
            |--------------------------------------------------------------------------
            */

            'reservation' =>
                $this->buildReservationData(
                    $currentReservation
                ),


            /*
            |--------------------------------------------------------------------------
            | Reservation Customer
            |--------------------------------------------------------------------------
            */

            'reservation_customer' =>
                $currentReservation?->customer
                    ? [
                        'id' =>
                            (int)
                                $currentReservation
                                    ->customer
                                    ->id,

                        'name' =>
                            $currentReservation
                                ->customer
                                ->name,

                        'phone' =>
                            $currentReservation
                                ->customer
                                ->phone,
                    ]
                    : null,


            /*
            |--------------------------------------------------------------------------
            | Reservation Date
            |--------------------------------------------------------------------------
            */

            'reservation_date' =>
                $reservationStartAt
                    ?->format('Y-m-d'),


            /*
            |--------------------------------------------------------------------------
            | Reservation Start / End
            |--------------------------------------------------------------------------
            */

            'reservation_start_at' =>
                $reservationStartAt
                    ?->toISOString(),

            'reservation_end_at' =>
                $reservationEndAt
                    ?->toISOString(),


            /*
            |--------------------------------------------------------------------------
            | Reservation Time
            |--------------------------------------------------------------------------
            */

            'reservation_start_time' =>
                $reservationStartAt
                    ?->format('H:i'),

            'reservation_end_time' =>
                $reservationEndAt
                    ?->format('H:i'),


            /*
            |--------------------------------------------------------------------------
            | Reservation Display
            |--------------------------------------------------------------------------
            */

            'reservation_display' =>
                $this->buildReservationDisplay(
                    $currentReservation
                ),

            'reservation_time_display' =>
                $this->buildReservationTimeDisplay(
                    $currentReservation
                ),


            /*
            |--------------------------------------------------------------------------
            | Edit Compatibility
            |--------------------------------------------------------------------------
            |
            | Old frontend fields remain available.
            |
            */

            'edit_status' =>
                $hasValidReservation
                    ? RestaurantTable::STATUS_RESERVED
                    : $this->status,

            'edit_reservation_date' =>
                $reservationStartAt
                    ?->format('Y-m-d'),

            'edit_reservation_start_time' =>
                $reservationStartAt
                    ?->format('H:i'),

            'edit_reservation_end_time' =>
                $reservationEndAt
                    ?->format('H:i'),


            /*
            |--------------------------------------------------------------------------
            | Direct Merge Parent Information
            |--------------------------------------------------------------------------
            */

            'merged_with_id' =>
                $this->merged_with_id !== null
                    ? (int)
                        $this->merged_with_id
                    : null,


            'merged_with' =>
                $this->whenLoaded(
                    'mergedWith',
                    function (): ?array {

                        if (
                            ! $this->mergedWith
                        ) {
                            return null;
                        }

                        return [

                            'id' =>
                                (int)
                                    $this
                                        ->mergedWith
                                        ->id,

                            'table_name' =>
                                $this
                                    ->mergedWith
                                    ->table_name,

                            'capacity' =>
                                (int)
                                    $this
                                        ->mergedWith
                                        ->capacity,

                            'section' =>
                                $this
                                    ->mergedWith
                                    ->section,

                            'status' =>
                                $this
                                    ->mergedWith
                                    ->status,

                            'current_status' =>
                                $this
                                    ->mergedWith
                                    ->current_status,
                        ];
                    }
                ),


            /*
            |--------------------------------------------------------------------------
            | Merge Information
            |--------------------------------------------------------------------------
            */

            'is_merged' =>
                $isMerged,

            'is_merge_master' =>
                $isMerged &&
                $this->merged_with_id === null,

            'is_merge_child' =>
                $this->merged_with_id !== null,

            'merge_master_id' =>
                $isMerged
                    ? $mergeMasterId
                    : null,

            'merge_group_ids' =>
                $mergeGroupIds,

            'merged_with_ids' =>
                $mergedWithIds,

            'merge_group_tables' =>
                $mergeGroupTables,

            'merged_table_count' =>
                $isMerged
                    ? $mergeGroup->count()
                    : 0,

            'merged_total_capacity' =>
                $isMerged
                    ? (int)
                        $mergeGroup->sum(
                            'capacity'
                        )
                    : null,


            /*
            |--------------------------------------------------------------------------
            | Other Information
            |--------------------------------------------------------------------------
            */

            'notes' =>
                $this->notes,

            'created_at' =>
                $this->created_at
                    ?->toISOString(),

            'updated_at' =>
                $this->updated_at
                    ?->toISOString(),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Active Reservation
    |--------------------------------------------------------------------------
    */

    private function getActiveReservation()
    {
        /*
        |--------------------------------------------------------------------------
        | Prefer eager-loaded relationship
        |--------------------------------------------------------------------------
        */

        if (
            $this->relationLoaded(
                'activeReservation'
            )
        ) {
            return $this
                ->activeReservation
                ->first();
        }


        /*
        |--------------------------------------------------------------------------
        | Fallback database query
        |--------------------------------------------------------------------------
        */

        return $this
            ->activeReservation()
            ->with('customer')
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Upcoming Reservation
    |--------------------------------------------------------------------------
    */

    private function getUpcomingReservation()
    {
        /*
        |--------------------------------------------------------------------------
        | Prefer eager-loaded relationship
        |--------------------------------------------------------------------------
        */

        if (
            $this->relationLoaded(
                'upcomingReservation'
            )
        ) {
            return $this
                ->upcomingReservation
                ->first();
        }


        /*
        |--------------------------------------------------------------------------
        | Fallback database query
        |--------------------------------------------------------------------------
        */

        return $this
            ->upcomingReservation()
            ->with('customer')
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Reservation Data
    |--------------------------------------------------------------------------
    */

    private function buildReservationData(
        $reservation
    ): ?array {

        if (
            ! $reservation
        ) {
            return null;
        }


        return [

            'id' =>
                (int)
                    $reservation->id,

            'reservation_no' =>
                $reservation->reservation_no,

            'status' =>
                $reservation->status,

            'reservation_date' =>
                $reservation
                    ->reservation_date
                    ?->format('Y-m-d'),

            'start_time' =>
                $reservation
                    ->start_time
                    ?->toISOString(),

            'end_time' =>
                $reservation
                    ->end_time
                    ?->toISOString(),

            'start_time_display' =>
                $reservation
                    ->start_time
                    ?->format('h:i A'),

            'end_time_display' =>
                $reservation
                    ->end_time
                    ?->format('h:i A'),

            'guest_count' =>
                (int)
                    $reservation
                        ->guest_count,

            'advance_amount' =>
                (float)
                    $reservation
                        ->advance_amount,

            'note' =>
                $reservation->note,

            'customer' =>
                $reservation->customer
                    ? [
                        'id' =>
                            (int)
                                $reservation
                                    ->customer
                                    ->id,

                        'name' =>
                            $reservation
                                ->customer
                                ->name,

                        'phone' =>
                            $reservation
                                ->customer
                                ->phone,

                        'email' =>
                            $reservation
                                ->customer
                                ->email,
                    ]
                    : null,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Reservation Display
    |--------------------------------------------------------------------------
    */

    private function buildReservationDisplay(
        $reservation
    ): ?string {

        if (
            ! $reservation
        ) {
            return null;
        }


        if (
            ! $reservation->start_time ||
            ! $reservation->end_time
        ) {
            return null;
        }


        return sprintf(

            '%s, %s - %s',

            $reservation
                ->start_time
                ->format(
                    'd M Y'
                ),

            $reservation
                ->start_time
                ->format(
                    'h:i A'
                ),

            $reservation
                ->end_time
                ->format(
                    'h:i A'
                )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reservation Time Display
    |--------------------------------------------------------------------------
    */

    private function buildReservationTimeDisplay(
        $reservation
    ): ?string {

        if (
            ! $reservation
        ) {
            return null;
        }


        if (
            ! $reservation->start_time ||
            ! $reservation->end_time
        ) {
            return null;
        }


        return sprintf(

            '%s - %s',

            $reservation
                ->start_time
                ->format(
                    'h:i A'
                ),

            $reservation
                ->end_time
                ->format(
                    'h:i A'
                )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Section Label
    |--------------------------------------------------------------------------
    */

    private function formatSectionLabel(
        ?string $section
    ): string {

        return match (
            $section
        ) {

            RestaurantTable::SECTION_AC =>
                'AC',

            RestaurantTable::SECTION_NON_AC =>
                'Non-AC',

            RestaurantTable::SECTION_OUTDOOR =>
                'Outdoor',

            default =>
                ucfirst(
                    str_replace(
                        '_',
                        ' ',
                        (string)
                            $section
                    )
                ),
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Status Label
    |--------------------------------------------------------------------------
    */

    private function formatStatusLabel(
        ?string $status
    ): string {

        return match (
            $status
        ) {

            RestaurantTable::STATUS_AVAILABLE =>
                'Available',

            RestaurantTable::STATUS_OCCUPIED =>
                'Occupied',

            RestaurantTable::STATUS_RESERVED =>
                'Reserved',

            RestaurantTable::STATUS_CLEANING =>
                'Cleaning',

            default =>
                ucfirst(
                    str_replace(
                        '_',
                        ' ',
                        (string)
                            $status
                    )
                ),
        };
    }
}