<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the order into an API response.
     */
    public function toArray(
        Request $request
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Merged Tables
        |--------------------------------------------------------------------------
        */

        $mergedTables = $this->whenLoaded(
            'tables',
            function () {

                return $this->tables
                    ->filter(
                        static function (
                            $table
                        ): bool {

                            return ! (bool) (
                                $table->pivot
                                    ?->is_primary
                                ?? false
                            );

                        }
                    )
                    ->map(
                        static function (
                            $table
                        ): array {

                            return [

                                'id' =>
                                    (int) $table->id,

                                'table_name' =>
                                    $table->table_name,

                                'capacity' =>
                                    (int) (
                                        $table->capacity
                                        ?? 0
                                    ),

                                'section' =>
                                    $table->section,

                                'status' =>
                                    $table->status,

                            ];

                        }
                    )
                    ->values();

            }
        );



        /*
        |--------------------------------------------------------------------------
        | Main Response
        |--------------------------------------------------------------------------
        */

        return [


            /*
            |--------------------------------------------------------------------------
            | Basic Order Information
            |--------------------------------------------------------------------------
            */


            'id' =>
                (int) $this->id,


            'order_number' =>
                $this->order_number,

            'fulfillment_type' =>
                $this->fulfillment_type,

            'pickup_token' =>
                $this->pickup_token,

            'pickup_status' =>
                $this->pickup_status,

            'picked_up_at' =>
                $this->picked_up_at?->toISOString(),

            'restaurant_table_id' =>
    $this->restaurant_table_id
        ? (int) $this->restaurant_table_id
        : null,

            /*
|--------------------------------------------------------------------------
| Reservation Information
|--------------------------------------------------------------------------
*/

'reservation_id' =>
    $this->reservation_id
        ? (int) $this->reservation_id
        : null,


'reservation' =>
    $this->whenLoaded(
        'reservation',
        function (): ?array {


            if (
                ! $this->reservation
            ) {

                return null;

            }



            return [

                'id' =>
                    (int)
                    $this->reservation->id,


                'reservation_no' =>
                    $this->reservation
                        ->reservation_no,


                'status' =>
                    $this->reservation
                        ->status,


                'reservation_date' =>
                    $this->reservation
                        ->reservation_date
                        ?->format('Y-m-d'),


                'start_time' =>
                    $this->reservation
                        ->start_time
                        ?->format('H:i'),


                'end_time' =>
                    $this->reservation
                        ->end_time
                        ?->format('H:i'),


                'guest_count' =>
                    (int)
                    (
                        $this->reservation
                            ->guest_count
                        ?? 0
                    ),

            ];

        }
    ),

    /*
|--------------------------------------------------------------------------
| Reservation Status
|--------------------------------------------------------------------------
*/

'reservation_status' =>
    $this->whenLoaded(
        'reservation',
        fn (): ?string =>
            $this->reservation?->status
    ),



            /*
            |--------------------------------------------------------------------------
            | Order Status
            |--------------------------------------------------------------------------
            */


            'status' =>
                $this->status,


            'status_label' =>
                $this->statusLabel(),



            /*
            |--------------------------------------------------------------------------
            | Payment Status
            |--------------------------------------------------------------------------
            */


            'payment_status' =>
                $this->payment_status,


            'payment_status_label' =>
                $this->paymentStatusLabel(),


            'payment_method' =>
                $this->payment_method,


            'payment_method_label' =>
                $this->paymentMethodLabel(),


            'payment_reference' =>
                $this->payment_reference,


            'payment_breakdown' =>
                $this->payment_breakdown,
                            /*
            |--------------------------------------------------------------------------
            | Customer Information
            |--------------------------------------------------------------------------
            |
            | Snapshot fields are used so historical order information remains
            | available even if the customer record changes later.
            |
            */


            'customer' => [

                'id' =>
                    $this->customer_id
                        ? (int) $this->customer_id
                        : null,


                'name' =>
                    $this->customer_name
                    ?: ($this->fulfillment_type === 'takeaway'
                        ? 'Walk-in Customer'
                        : null),


                'phone' =>
                    $this->customer_phone,


                'email' =>
                    $this->customer_email,

                'date_of_birth' =>
                    $this->whenLoaded('customer', fn () => $this->customer?->date_of_birth?->format('Y-m-d')),


                'total_orders' =>
                    $this->whenLoaded(
                        'customer',
                        fn (): int =>
                            (int) (
                                $this->customer
                                    ?->total_orders
                                ?? 0
                            )
                    ),


                'total_spent' =>
                    $this->whenLoaded(
                        'customer',
                        fn (): float =>
                            (float) (
                                $this->customer
                                    ?->total_spent
                                ?? 0
                            )
                    ),


                'last_visit_at' =>
                    $this->whenLoaded(
                        'customer',
                        fn (): ?string =>
                            $this->customer
                                ?->last_visit_at
                                ?->toISOString()
                    ),

            ],



            /*
            |--------------------------------------------------------------------------
            | Primary Table
            |--------------------------------------------------------------------------
            */


            'primary_table' =>
                $this->whenLoaded(
                    'primaryTable',
                    function (): ?array {

                        if (
                            ! $this->primaryTable
                        ) {

                            return null;

                        }


                        return [

                            'id' =>
                                (int)
                                    $this->primaryTable->id,


                            'table_name' =>
                                $this->primaryTable
                                    ->table_name,


                            'capacity' =>
                                (int) (
                                    $this->primaryTable
                                        ->capacity
                                    ?? 0
                                ),


                            'section' =>
                                $this->primaryTable
                                    ->section,


                            'status' =>
                                $this->primaryTable
                                    ->status,

                        ];

                    }
                ),



            /*
            |--------------------------------------------------------------------------
            | Merged Tables
            |--------------------------------------------------------------------------
            */


            'merged_tables' =>
                $mergedTables,


            'merged_table_names' =>
                $this->whenLoaded(
                    'tables',
                    function (): string {

                        return $this->tables
                            ->filter(
                                static function (
                                    $table
                                ): bool {

                                    return ! (bool) (
                                        $table->pivot
                                            ?->is_primary
                                        ?? false
                                    );

                                }
                            )
                            ->pluck(
                                'table_name'
                            )
                            ->filter()
                            ->implode(', ');

                    }
                ),


            'total_table_capacity' =>
                $this->whenLoaded(
                    'tables',
                    function (): int {

                        return (int)
                            $this->tables
                                ->sum(
                                    static fn (
                                        $table
                                    ): int =>
                                        (int) (
                                            $table->capacity
                                            ?? 0
                                        )
                                );

                    }
                ),



            /*
            |--------------------------------------------------------------------------
            | Financial Summary
            |--------------------------------------------------------------------------
            */


            'subtotal' =>
                (float) $this->subtotal,


            'discount_amount' =>
                (float)
                    $this->discount_amount,

            'offer_id' =>
                $this->offer_id
                    ? (int) $this->offer_id
                    : null,

            'offer_discount_amount' =>
                (float) $this->offer_discount_amount,


            'tax_amount' =>
                (float) $this->tax_amount,


            'service_charge' =>
                (float)
                    $this->service_charge,


            'total_amount' =>
                (float)
                    $this->total_amount,


            'paid_amount' =>
                (float)
                    $this->paid_amount,


            'due_amount' =>
                (float)
                    $this->due_amount,



            /*
            |--------------------------------------------------------------------------
            | Formatted Financial Summary
            |--------------------------------------------------------------------------
            */


            'subtotal_formatted' =>
                $this->formatMoney(
                    $this->subtotal
                ),


            'discount_amount_formatted' =>
                $this->formatMoney(
                    $this->discount_amount
                ),


            'tax_amount_formatted' =>
                $this->formatMoney(
                    $this->tax_amount
                ),


            'service_charge_formatted' =>
                $this->formatMoney(
                    $this->service_charge
                ),


            'total_amount_formatted' =>
                $this->formatMoney(
                    $this->total_amount
                ),


            'paid_amount_formatted' =>
                $this->formatMoney(
                    $this->paid_amount
                ),


            'due_amount_formatted' =>
                $this->formatMoney(
                    $this->due_amount
                ),



            /*
            |--------------------------------------------------------------------------
            | Notes
            |--------------------------------------------------------------------------
            */


            'order_note' =>
                $this->order_note,


            'kitchen_note' =>
                $this->kitchen_note,


            'cancellation_reason' =>
                $this->cancellation_reason,



            /*
            |--------------------------------------------------------------------------
            | Creator / Waiter
            |--------------------------------------------------------------------------
            */


            'creator' =>
                $this->whenLoaded(
                    'creator',
                    function (): ?array {

                        if (! $this->creator) {

                            return null;

                        }


                        return [

                            'id' =>
                                (int)
                                    $this->creator->id,


                            'name' =>
                                $this->creator->name,


                            'email' =>
                                $this->creator->email,

                        ];

                    }
                ),


            'created_by' =>
                $this->created_by
                    ? (int) $this->created_by
                    : null,



            /*
            |--------------------------------------------------------------------------
            | Available Actions
            |--------------------------------------------------------------------------
            */


            'can_edit' =>
                $this->canEdit(),


            'can_send_to_kitchen' =>
                $this->status ===
                Order::STATUS_PENDING,


            'can_cancel' =>
                $this->canCancel(),


            'can_complete' =>
                $this->status ===
                Reservation::STATUS_SEATED,


            /*
            |--------------------------------------------------------------------------
            | Date and Time
            |--------------------------------------------------------------------------
            */


            'created_at' =>
                $this->created_at
                    ?->toISOString(),


            'updated_at' =>
                $this->updated_at
                    ?->toISOString(),


            'date' =>
                $this->created_at
                    ?->format('d M Y'),


            'time' =>
                $this->created_at
                    ?->format('h:i A'),


            'day' =>
                $this->created_at
                    ?->format('l'),



            /*
            |--------------------------------------------------------------------------
            | Order Lifecycle Timestamps
            |--------------------------------------------------------------------------
            */


            'sent_to_kitchen_at' =>
                $this->sent_to_kitchen_at
                    ?->toISOString(),


            'served_at' =>
                $this->served_at
                    ?->toISOString(),


            'completed_at' =>
                $this->completed_at
                    ?->toISOString(),


            'canceled_at' =>
                $this->canceled_at
                    ?->toISOString(),



            /*
            |--------------------------------------------------------------------------
            | Order Items
            |--------------------------------------------------------------------------
            */


            'items' =>
                OrderItemResource::collection(
                    $this->whenLoaded(
                        'items'
                    )
                ),



            /*
            |--------------------------------------------------------------------------
            | Payment History
            |--------------------------------------------------------------------------
            */


            'payments' =>
                PaymentResource::collection(
                    $this->whenLoaded(
                        'payments'
                    )
                ),

        ];

    }



    /**
     * Return a readable order status.
     */
    private function statusLabel(): string
    {

        return match ($this->status) {

            Order::STATUS_PENDING =>
                'Pending',

            Order::STATUS_PREPARING =>
                'Preparing',

            Order::STATUS_READY =>
                'Ready',

            Order::STATUS_SERVED =>
                'Served',

            Order::STATUS_COMPLETED =>
                'Completed',

            Order::STATUS_CANCELED =>
                'Canceled',

            default =>
                $this->formatLabel(
                    $this->status
                ),

        };

    }



    /**
     * Return a readable payment status.
     */
    private function paymentStatusLabel(): string
    {

        return match (
            $this->payment_status
        ) {

            Order::PAYMENT_DUE =>
                'Due',

            Order::PAYMENT_PARTIALLY_PAID =>
                'Partially Paid',

            Order::PAYMENT_PAID =>
                'Paid',

            default =>
                $this->formatLabel(
                    $this->payment_status
                ),

        };

    }



    /**
     * Return a readable payment method.
     */
    private function paymentMethodLabel(): ?string
    {

        if (! $this->payment_method) {

            return null;

        }


        return match (
            $this->payment_method
        ) {

            Order::METHOD_CASH =>
                'Cash',

            Order::METHOD_CARD =>
                'Card',

            Order::METHOD_BKASH =>
                'bKash',

            Order::METHOD_NAGAD =>
                'Nagad',

            Order::METHOD_BANK_TRANSFER =>
                'Bank Transfer',

            Order::METHOD_STRIPE =>
                'Stripe',

            Order::METHOD_MIXED =>
                'Mixed Payment',

            default =>
                $this->formatLabel(
                    $this->payment_method
                ),

        };

    }



    private function canEdit(): bool
    {

        return in_array(
            $this->status,
            [
                Order::STATUS_PENDING,
                Order::STATUS_PREPARING,
                Order::STATUS_READY,
                Order::STATUS_SERVED,
            ],
            true
        );

    }



    private function canCancel(): bool
    {

        return in_array(
            $this->status,
            [
                Order::STATUS_PENDING,
                Order::STATUS_PREPARING,
                Order::STATUS_READY,
                Order::STATUS_SERVED,
            ],
            true
        );

    }



    private function formatMoney(
        mixed $amount
    ): string {

        return '৳ '.number_format(
            (float) $amount,
            2
        );

    }



    private function formatLabel(
        mixed $value
    ): string {

        return ucfirst(
            str_replace(
                '_',
                ' ',
                (string) $value
            )
        );

    }

}
