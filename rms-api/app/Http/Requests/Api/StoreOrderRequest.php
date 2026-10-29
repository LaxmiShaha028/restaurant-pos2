<?php

namespace App\Http\Requests\Api;

use App\Models\Order;
use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine whether the authenticated user
     * can create an order.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Prepare incoming data before validation.
     */
    protected function prepareForValidation(): void
    {
        $customerName = $this->input(
            'customer_name'
        );

        $customerPhone = $this->input(
            'customer_phone'
        );

        $customerEmail = $this->input(
            'customer_email'
        );

        $paymentMethod = $this->input(
            'payment_method'
        );

        $paymentReference = $this->input(
            'payment_reference'
        );

        $fulfillmentType = $this->input('fulfillment_type');

        $items = collect(
            $this->input('items', [])
        )
            ->map(
                static function (
                    mixed $item
                ): mixed {
                    if (!is_array($item)) {
                        return $item;
                    }

                    return [
                        ...$item,

                        'order_item_id' =>
                            isset($item['order_item_id'])
                            &&
                            $item['order_item_id'] !== ''
                                ? (int) $item['order_item_id']
                                : null,

                        'quantity' =>
                            isset($item['quantity'])
                                ? (int) $item['quantity']
                                : 1,

                        'addon_ids' =>
                            collect(
                                $item['addon_ids'] ?? []
                            )
                                ->filter(
                                    static fn (
                                        mixed $id
                                    ): bool =>
                                        $id !== null &&
                                        $id !== ''
                                )
                                ->map(
                                    static fn (
                                        mixed $id
                                    ): int =>
                                        (int) $id
                                )
                                ->unique()
                                ->values()
                                ->all(),

                        'kitchen_note' =>
                            isset(
                                $item['kitchen_note']
                            )
                                ? trim(
                                    (string)
                                    $item['kitchen_note']
                                )
                                : null,
                    ];
                }
            )
            ->values()
            ->all();

        $mergedTableIds = collect(
            $this->input(
                'merged_table_ids',
                []
            )
        )
            ->filter(
                static fn (
                    mixed $id
                ): bool =>
                    $id !== null &&
                    $id !== ''
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

        $paidAmount = $this->filled(
            'paid_amount'
        )
            ? round(
                max(
                    0,
                    (float) $this->input(
                        'paid_amount'
                    )
                ),
                2
            )
            : 0;

        $this->merge([
            'status' =>
                $this->filled('status')
                    ? $this->input('status')
                    : Order::STATUS_PENDING,

            'fulfillment_type' =>
                in_array($fulfillmentType, ['dine_in', 'takeaway'], true)
                    ? $fulfillmentType
                    : ($this->filled('restaurant_table_id') ? 'dine_in' : 'takeaway'),

            'discount_amount' =>
                $this->filled(
                    'discount_amount'
                )
                    ? $this->input(
                        'discount_amount'
                    )
                    : 0,

            /*
             * Tax and service charge are fixed at zero
             * for now. Backend will calculate them again.
             */
            'tax_amount' => 0,
            'service_charge' => 0,

            /*
             * Payment information.
             */
            'paid_amount' =>
                $paidAmount,

            'payment_method' =>
                is_string($paymentMethod)
                    ? strtolower(
                        trim($paymentMethod)
                    )
                    : $paymentMethod,

            'payment_reference' =>
                is_string($paymentReference)
                    ? trim($paymentReference)
                    : $paymentReference,

            'customer_name' =>
                is_string($customerName)
                    ? trim($customerName)
                    : $customerName,

            'customer_phone' =>
                is_string($customerPhone)
                    ? trim($customerPhone)
                    : $customerPhone,

            'customer_email' =>
                is_string($customerEmail)
                    ? strtolower(
                        trim($customerEmail)
                    )
                    : $customerEmail,

            'merged_table_ids' =>
                $mergedTableIds,

            'items' => $items,
        ]);
    }

    /**
     * Validation rules for creating an order.
     */
    public function rules(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | Primary and merged tables
            |--------------------------------------------------------------------------
            */

            'restaurant_table_id' => [
                'nullable',
                'integer',
                'exists:restaurant_tables,id',
            ],
            'fulfillment_type' => [
                'required',
                'string',
                Rule::in(['dine_in', 'takeaway']),
            ],
            'reservation_id' => [
    'nullable',
    'integer',
    'exists:reservations,id',
],

            'merged_table_ids' => [
                'nullable',
                'array',
            ],

            'merged_table_ids.*' => [
                'required',
                'integer',
                'distinct',
                'different:restaurant_table_id',
                'exists:restaurant_tables,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            'customer_id' => [
                'nullable',
                'integer',
                'exists:customers,id',
            ],

            'customer_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'customer_phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'customer_email' => [
                'nullable',
                'email:rfc',
                'max:190',
            ],

            'date_of_birth' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            /*
            |--------------------------------------------------------------------------
            | Order information
            |--------------------------------------------------------------------------
            */

            'status' => [
                'required',
                'string',
                Rule::in([
                    Order::STATUS_PENDING,
                    Order::STATUS_PREPARING,
                    Order::STATUS_READY,
                    Order::STATUS_SERVED,
                ]),
            ],

            'discount_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999999.99',
            ],

            'tax_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'service_charge' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'order_note' => [
                'nullable',
                'string',
                'max:2000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Payment information
            |--------------------------------------------------------------------------
            */

            'paid_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999999.99',
            ],

            'payment_method' => [
                Rule::requiredIf(
                    fn (): bool =>
                        (float) $this->input(
                            'paid_amount',
                            0
                        ) > 0
                ),
                'nullable',
                'string',
                Rule::in(
                    Order::allowedPaymentMethods()
                ),
            ],

            'payment_reference' => [
                'nullable',
                'string',
                'max:150',
            ],

            /*
            |--------------------------------------------------------------------------
            | Order items
            |--------------------------------------------------------------------------
            */

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.order_item_id' => [
                'nullable',
                'integer',
                'exists:order_items,id',
            ],

            'items.*.menu_item_id' => [
                'required',
                'integer',
                'exists:menu_items,id',
            ],

            'items.*.menu_item_variant_id' => [
                'nullable',
                'integer',
                'exists:menu_item_variants,id',
            ],

            'items.*.addon_ids' => [
                'nullable',
                'array',
            ],

            'items.*.addon_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:add_ons,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                'max:999',
            ],

            'items.*.kitchen_note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Additional payment validation.
     */
    public function after(): array
{
    return [
        function (
            Validator $validator
        ): void {

            /*
            |--------------------------------------------------------------------------
            | Stop when basic validation already failed
            |--------------------------------------------------------------------------
            */

            if (
                $validator->errors()
                    ->isNotEmpty()
            ) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Existing Payment Validation
            |--------------------------------------------------------------------------
            */

            $paidAmount = round(
                (float) $this->input(
                    'paid_amount',
                    0
                ),
                2
            );

            if (
                $paidAmount <= 0 &&
                $this->filled(
                    'payment_method'
                )
            ) {
                $validator->errors()->add(
                    'payment_method',
                    'A payment method should only be selected when a payment amount is entered.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Reservation Validation
            |--------------------------------------------------------------------------
            */

            $reservationId =
                $this->input(
                    'reservation_id'
                );

            /*
|--------------------------------------------------------------------------
| Table-less Order Validation
|--------------------------------------------------------------------------
|
| A normal order may be created without a table.
| However, merged tables cannot exist without a primary table.
|
*/

$submittedPrimaryTableId =
    $this->filled(
        'restaurant_table_id'
    )
        ? (int) $this->input(
            'restaurant_table_id'
        )
        : null;

$submittedMergedTableIds =
    collect(
        $this->input(
            'merged_table_ids',
            []
        )
    )
        ->filter(
            static fn (
                mixed $id
            ): bool =>
                $id !== null &&
                $id !== ''
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
    $submittedPrimaryTableId === null &&
    $submittedMergedTableIds->isNotEmpty()
) {
    $validator->errors()->add(
        'merged_table_ids',
        'Merged tables cannot be selected without a primary table.'
    );

    return;
}
            /*
            |--------------------------------------------------------------------------
            | Normal Order
            |--------------------------------------------------------------------------
            |
            | reservation_id is optional.
            |
            */

            if (
                empty($reservationId)
            ) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Load Reservation
            |--------------------------------------------------------------------------
            */

            $reservation =
                Reservation::query()
                    ->with([
                        'customer',
                        'tables',
                    ])
                    ->find(
                        (int) $reservationId
                    );

            if (!$reservation) {
                $validator->errors()->add(
                    'reservation_id',
                    'The selected reservation no longer exists.'
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Reservation Status
            |--------------------------------------------------------------------------
            |
            | A NEW reservation-to-order conversion requires a seated reservation.
            |
            | UpdateOrderRequest extends this request, so this validator also runs
            | during existing order updates such as Pay & Complete.
            |
            | If this is the SAME reservation already linked to the route order,
            | do not re-run create-time reservation eligibility.
            |
            */

            $routeOrder =
                $this->route(
                    'order'
                );

            $isSameLinkedReservation =
                $routeOrder instanceof Order
                &&
                $routeOrder->reservation_id !== null
                &&
                (int) $routeOrder->reservation_id ===
                    (int) $reservationId;

            if (
                ! $isSameLinkedReservation
                &&
                $reservation->status !==
                    Reservation::STATUS_SEATED
            ) {
                $validator->errors()->add(
                    'reservation_id',
                    'Only a seated reservation can be used to create an order.'
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Reservation must have a customer
            |--------------------------------------------------------------------------
            */

            if (
                !$reservation->customer_id
            ) {
                $validator->errors()->add(
                    'reservation_id',
                    'The selected reservation has no customer.'
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Reservation must have tables
            |--------------------------------------------------------------------------
            */

            if (
                $reservation->tables->isEmpty()
            ) {
                $validator->errors()->add(
                    'reservation_id',
                    'The selected reservation has no assigned tables.'
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Customer Consistency
            |--------------------------------------------------------------------------
            */

            $submittedCustomerId =
                $this->filled(
                    'customer_id'
                )
                    ? (int)
                        $this->input(
                            'customer_id'
                        )
                    : null;

            $reservationCustomerId =
                (int)
                    $reservation->customer_id;

            if (
                $submittedCustomerId !== null &&
                $submittedCustomerId !==
                    $reservationCustomerId
            ) {
                $validator->errors()->add(
                    'customer_id',
                    'The selected customer does not belong to the reservation.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Primary Table Consistency
            |--------------------------------------------------------------------------
            */

            $submittedPrimaryTableId =
                $this->filled(
                    'restaurant_table_id'
                )
                    ? (int) $this->input(
                        'restaurant_table_id'
                    )
                    : null;

            $reservationTableIds =
                $reservation
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

            if (
                $submittedPrimaryTableId === null ||
                !$reservationTableIds->contains(
                    $submittedPrimaryTableId
                )
            ) {
                $validator->errors()->add(
                    'restaurant_table_id',
                    'The selected primary table does not belong to the reservation.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Merged Table Consistency
            |--------------------------------------------------------------------------
            */

            $submittedMergedTableIds =
                collect(
                    $this->input(
                        'merged_table_ids',
                        []
                    )
                )
                    ->map(
                        static fn (
                            mixed $id
                        ): int =>
                            (int) $id
                    )
                    ->unique()
                    ->values();

            foreach (
                $submittedMergedTableIds
                as $mergedTableId
            ) {
                if (
                    !$reservationTableIds->contains(
                        $mergedTableId
                    )
                ) {
                    $validator->errors()->add(
                        'merged_table_ids',
                        'One or more selected merged tables do not belong to the reservation.'
                    );

                    break;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Complete Table Set Consistency
            |--------------------------------------------------------------------------
            |
            | Reservation tables must be exactly the tables used by the order.
            |
            */

            $selectedOrderTableIds =
                collect([
                    $submittedPrimaryTableId,
                    ...$submittedMergedTableIds
                        ->all(),
                ])
                    ->filter(
                        static fn (
                            mixed $id
                        ): bool =>
                            (int) $id > 0
                    )
                    ->map(
                        static fn (
                            mixed $id
                        ): int =>
                            (int) $id
                    )
                    ->unique()
                    ->sort()
                    ->values();

            $expectedReservationTableIds =
                $reservationTableIds
                    ->sort()
                    ->values();

            if (
                $selectedOrderTableIds->all() !==
                $expectedReservationTableIds->all()
            ) {
                $validator->errors()->add(
                    'merged_table_ids',
                    'The order must use exactly the tables assigned to the reservation.'
                );
            }
        },
    ];
}

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'restaurant_table_id.exists' =>
                'The selected table no longer exists.',
            'reservation_id.exists' =>
                'The selected reservation no longer exists.',

            'merged_table_ids.array' =>
                'The merged table selection is invalid.',

            'merged_table_ids.*.distinct' =>
                'The same merged table cannot be selected more than once.',

            'merged_table_ids.*.different' =>
                'The primary table cannot also be selected as a merged table.',

            'merged_table_ids.*.exists' =>
                'One or more selected merged tables no longer exist.',

            'customer_id.exists' =>
                'The selected customer no longer exists.',

            'customer_name.max' =>
                'The customer name cannot exceed 150 characters.',

            'customer_phone.max' =>
                'The customer phone number cannot exceed 30 characters.',

            'customer_email.email' =>
                'Please enter a valid customer email address.',

            'status.required' =>
                'Please select an order status.',

            'status.in' =>
                'The selected order status is invalid.',

            'discount_amount.numeric' =>
                'The discount must be a valid number.',

            'discount_amount.min' =>
                'The discount cannot be less than zero.',

            'paid_amount.numeric' =>
                'The paid amount must be a valid number.',

            'paid_amount.min' =>
                'The paid amount cannot be less than zero.',

            'paid_amount.max' =>
                'The paid amount is too large.',

            'payment_method.required' =>
                'Please select a payment method.',

            'payment_method.in' =>
                'The selected payment method is invalid.',

            'payment_reference.max' =>
                'The payment reference cannot exceed 150 characters.',

            'items.required' =>
                'Please add at least one menu item.',

            'items.array' =>
                'The order items must be a valid list.',

            'items.min' =>
                'Please add at least one menu item.',

            'items.*.order_item_id.integer' =>
                'The order item reference is invalid.',

            'items.*.order_item_id.exists' =>
                'One or more historical order items no longer exist.',

            'items.*.menu_item_id.required' =>
                'Please select a menu item.',

            'items.*.menu_item_id.exists' =>
                'One or more selected menu items no longer exist.',

            'items.*.menu_item_variant_id.exists' =>
                'The selected variant no longer exists.',

            'items.*.addon_ids.array' =>
                'The selected add-ons are invalid.',

            'items.*.addon_ids.*.distinct' =>
                'The same add-on cannot be selected more than once in one item.',

            'items.*.addon_ids.*.exists' =>
                'One or more selected add-ons no longer exist.',

            'items.*.quantity.required' =>
                'Please enter the item quantity.',

            'items.*.quantity.integer' =>
                'The item quantity must be a whole number.',

            'items.*.quantity.min' =>
                'The item quantity must be at least 1.',

            'items.*.quantity.max' =>
                'The item quantity cannot exceed 999.',

            'items.*.kitchen_note.max' =>
                'The item note cannot exceed 1000 characters.',
        ];
    }

    /**
     * Friendly field names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'restaurant_table_id' =>
                'primary table',
            'reservation_id' =>
    'reservation',

            'merged_table_ids' =>
                'merged tables',

            'customer_name' =>
                'customer name',

            'customer_phone' =>
                'customer phone',

            'customer_email' =>
                'customer email',

            'date_of_birth' =>
                'customer birthday',

            'discount_amount' =>
                'discount',

            'paid_amount' =>
                'paid amount',

            'payment_method' =>
                'payment method',

            'payment_reference' =>
                'payment reference',

            'items.*.order_item_id' =>
                'order item',

            'items.*.menu_item_id' =>
                'menu item',

            'items.*.menu_item_variant_id' =>
                'variant',

            'items.*.addon_ids' =>
                'add-ons',

            'items.*.quantity' =>
                'quantity',

            'items.*.kitchen_note' =>
                'item note',
        ];
    }
}
