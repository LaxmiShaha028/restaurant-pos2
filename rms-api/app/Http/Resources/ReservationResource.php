<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Date / Time
        |--------------------------------------------------------------------------
        */

        $startAt =
            $this->start_time;

        $endAt =
            $this->end_time;


        /*
        |--------------------------------------------------------------------------
        | Tables
        |--------------------------------------------------------------------------
        */

        $tables =
            $this->whenLoaded(
                'tables',
                fn () =>
                    $this->tables
            );


        /*
        |--------------------------------------------------------------------------
        | Orders
        |--------------------------------------------------------------------------
        */

        $ordersLoaded =
            $this->relationLoaded(
                'orders'
            );

        $orders =
            $ordersLoaded
                ? $this->orders
                : collect();


        /*
        |--------------------------------------------------------------------------
        | Reservation State
        |--------------------------------------------------------------------------
        */

        $isSeated =
            $this->status ===
            'seated';

        $isCompleted =
            $this->status ===
            'completed';

        $isCancelled =
            $this->status ===
            'cancelled';

        $isFinalized =
            $isCompleted
            ||
            $isCancelled;


        /*
        |--------------------------------------------------------------------------
        | Edit / Cancel Rules
        |--------------------------------------------------------------------------
        |
        | Edit:
        |     reserved / confirmed only
        |
        | Cancel:
        |     reserved / confirmed only
        |
        | Seated:
        |     must be managed through order lifecycle
        |
        */

        $canEdit =
            in_array(
                $this->status,
                [
                    'reserved',
                    'confirmed',
                ],
                true
            );

        $canCancel =
            in_array(
                $this->status,
                [
                    'reserved',
                    'confirmed',
                ],
                true
            );


        /*
        |--------------------------------------------------------------------------
        | Seat Rule
        |--------------------------------------------------------------------------
        */

        $canSeat =
            in_array(
                $this->status,
                [
                    'reserved',
                    'confirmed',
                ],
                true
            );


        return [

            /*
            |----------------------------------------------------------------------
            | Basic Information
            |----------------------------------------------------------------------
            */

            'id' =>
                (int)
                $this->id,

            'reservation_no' =>
                $this->reservation_no,


            /*
            |----------------------------------------------------------------------
            | Customer
            |----------------------------------------------------------------------
            */

            'customer_id' =>
                $this->customer_id
                    ? (int)
                    $this->customer_id
                    : null,

            'customer' => [

                'id' =>
                    $this->customer?->id
                        ? (int)
                        $this->customer->id
                        : null,

                'name' =>
                    $this->customer?->name,

                'phone' =>
                    $this->customer?->phone,

                'email' =>
                    $this->customer?->email,

                'date_of_birth' =>
                    $this->customer?->date_of_birth?->format('Y-m-d'),

                'date_of_birth' =>
                    $this->customer?->date_of_birth?->format('Y-m-d'),

            ],


            /*
            |----------------------------------------------------------------------
            | Reservation Date
            |----------------------------------------------------------------------
            */

            'reservation_date' =>
                $this->reservation_date
                    ?->format(
                        'Y-m-d'
                    ),

            'reservation_date_label' =>
                $this->reservation_date
                    ?->format(
                        'd M Y'
                    ),

            'day_name' =>
                $this->reservation_date
                    ?->format(
                        'l'
                    ),


            /*
            |----------------------------------------------------------------------
            | Start Time
            |----------------------------------------------------------------------
            */

            'start_time' =>
                $startAt
                    ?->format(
                        'H:i'
                    ),

            'start_time_formatted' =>
                $startAt
                    ?->format(
                        'h:i A'
                    ),

            'start_time_iso' =>
                $startAt
                    ?->toISOString(),


            /*
            |----------------------------------------------------------------------
            | End Time
            |----------------------------------------------------------------------
            */

            'end_time' =>
                $endAt
                    ?->format(
                        'H:i'
                    ),

            'end_time_formatted' =>
                $endAt
                    ?->format(
                        'h:i A'
                    ),

            'end_time_iso' =>
                $endAt
                    ?->toISOString(),


            /*
            |----------------------------------------------------------------------
            | Guest Information
            |----------------------------------------------------------------------
            */

            'guest_count' =>
                (int)
                $this->guest_count,


            /*
            |----------------------------------------------------------------------
            | Payment
            |----------------------------------------------------------------------
            */

            'advance_amount' =>
                (float)
                $this->advance_amount,

            'advance_amount_formatted' =>
                '৳ '
                .
                number_format(
                    (float)
                    $this->advance_amount,
                    2
                ),


            /*
            |----------------------------------------------------------------------
            | Status
            |----------------------------------------------------------------------
            */

            'status' =>
                $this->status,

            'status_label' =>
                match (
                    $this->status
                ) {

                    'reserved' =>
                        'Reserved',

                    'confirmed' =>
                        'Confirmed',

                    'seated' =>
                        'Seated',

                    'completed' =>
                        'Completed',

                    'cancelled' =>
                        'Cancelled',

                    default =>
                        ucfirst(
                            (string)
                            $this->status
                        ),
                },


            /*
            |----------------------------------------------------------------------
            | State Flags
            |----------------------------------------------------------------------
            */

            'is_seated' =>
                $isSeated,

            'is_completed' =>
                $isCompleted,

            'is_cancelled' =>
                $isCancelled,

            'is_finalized' =>
                $isFinalized,

            'is_active' =>
                in_array(
                    $this->status,
                    [
                        'reserved',
                        'confirmed',
                        'seated',
                    ],
                    true
                ),


            /*
            |----------------------------------------------------------------------
            | Frontend Actions
            |----------------------------------------------------------------------
            */

            'can_view' =>
                true,

            'can_edit' =>
                $canEdit,

            'can_cancel' =>
                $canCancel,

            'can_seat' =>
                $canSeat,


            /*
            |----------------------------------------------------------------------
            | Note
            |----------------------------------------------------------------------
            */

            'note' =>
                $this->note,


            /*
            |----------------------------------------------------------------------
            | Tables
            |----------------------------------------------------------------------
            */

            'tables' =>
                $tables
                    ->map(
                        static function (
                            $table
                        ): array {

                            return [

                                'id' =>
                                    (int)
                                    $table->id,

                                'name' =>
                                    $table->table_name,

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

                            ];
                        }
                    )
                    ->values(),


            /*
            |----------------------------------------------------------------------
            | Table Summary
            |----------------------------------------------------------------------
            */

            'table_ids' =>
                $tables
                    ->pluck('id')
                    ->map(
                        static fn (
                            mixed $id
                        ): int =>
                            (int) $id
                    )
                    ->values(),

            'table_count' =>
                $tables->count(),

            'total_table_capacity' =>
                (int)
                $tables->sum(
                    'capacity'
                ),


            /*
            |----------------------------------------------------------------------
            | Orders
            |----------------------------------------------------------------------
            */

            'has_order' =>
                $ordersLoaded
                    ? $orders->isNotEmpty()
                    : false,

            'order_count' =>
                $ordersLoaded
                    ? $orders->count()
                    : 0,

            'orders' =>
                $ordersLoaded
                    ? $orders
                        ->map(
                            static function (
                                $order
                            ): array {

                                return [

                                    'id' =>
                                        (int)
                                        $order->id,

                                    'order_number' =>
                                        $order->order_number,

                                    'status' =>
                                        $order->status,

                                    'total_amount' =>
                                        (float)
                                        $order->total_amount,

                                    'payment_status' =>
                                        $order->payment_status,

                                    'restaurant_table_id' =>
                                        $order->restaurant_table_id
                                            ? (int)
                                            $order->restaurant_table_id
                                            : null,

                                    'created_at' =>
                                        $order->created_at
                                            ?->toISOString(),

                                ];
                            }
                        )
                        ->values()
                    : [],


            /*
            |----------------------------------------------------------------------
            | Created By
            |----------------------------------------------------------------------
            */

            'created_by' =>
                $this->created_by
                    ? (int)
                    $this->created_by
                    : null,


            /*
            |----------------------------------------------------------------------
            | Timestamps
            |----------------------------------------------------------------------
            */

            'created_at' =>
                $this->created_at
                    ?->toISOString(),

            'updated_at' =>
                $this->updated_at
                    ?->toISOString(),

        ];
    }
}
