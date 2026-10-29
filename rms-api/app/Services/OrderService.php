<?php

namespace App\Services;

use App\Models\AddOn;
use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\MenuItemVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderKitchenBatch;
use App\Models\RestaurantTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Reservation;
use App\Services\SystemSettingService;
use App\Services\SalesAccountingService;
use App\Services\OfferService;

class OrderService
{
    private SystemSettingService $systemSettingService;

    private SalesAccountingService $salesAccountingService;

    private OfferService $offerService;

    public function __construct(
        SystemSettingService $systemSettingService,
        SalesAccountingService $salesAccountingService,
        OfferService $offerService
    ) {
        $this->systemSettingService =
            $systemSettingService;

        $this->salesAccountingService =
            $salesAccountingService;

        $this->offerService =
            $offerService;
    }
public function getEditOptions(
    Order $order,
    ?int $userId = null
): array {
    /*
    |--------------------------------------------------------------------------
    | Load order relationships
    |--------------------------------------------------------------------------
    */

    $order->load([
    'customer',
    'reservation',
    'primaryTable',
    'tables',
    'items.addons',
    'payments',
    'recipeConsumptions',
    'latestKitchenBatch',
    'creator',
]);


    /*
    |--------------------------------------------------------------------------
    | Finalized order protection
    |--------------------------------------------------------------------------
    */

    if ($order->isFinalized()) {
        throw ValidationException::withMessages([
            'order' => [
                'A completed or canceled order cannot be edited.',
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Editing policy
    |--------------------------------------------------------------------------
    */

    $this->ensureOrderCanBeEdited(
        $order
    );


    /*
    |--------------------------------------------------------------------------
    | Current assigned table IDs
    |--------------------------------------------------------------------------
    */

    $currentTableIds = $order->tables
        ->pluck('id')
        ->map(
            static fn (mixed $id): int => (int) $id
        )
        ->unique()
        ->values();

    if (
        $currentTableIds->isEmpty() &&
        $order->restaurant_table_id
    ) {
        $currentTableIds = collect([
            (int) $order->restaurant_table_id,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Available / current tables
    |--------------------------------------------------------------------------
    */

    $tables = RestaurantTable::query()
        ->where(
            function (
                Builder $query
            ) use (
                $currentTableIds
            ): void {

                $query->where(
                    'status',
                    RestaurantTable::STATUS_AVAILABLE
                );

                if ($currentTableIds->isNotEmpty()) {
                    $query->orWhereIn(
                        'id',
                        $currentTableIds
                    );
                }
            }
        )
        ->orderBy(
            'table_name'
        )
        ->get([
            'id',
            'table_name',
            'capacity',
            'section',
            'status',
            'merged_with_id',
        ]);


    /*
    |--------------------------------------------------------------------------
    | Menu Categories
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | Return a simple and consistent structure for Vue.
    |
    */

    $categories = MenuCategory::query()
        ->available()
        ->ordered()
        ->get([
            'id',
            'category_name',
            'description',
            'is_available',
            'display_order',
        ])
        ->map(
            static function (
                MenuCategory $category
            ): array {

                return [
                    'id' => (int) $category->id,

                    'category_name' =>
                        $category->category_name,

                    'name' =>
                        $category->category_name,

                    'description' =>
                        $category->description,

                    'is_available' =>
                        (bool) $category->is_available,

                    'display_order' =>
                        (int) $category->display_order,
                ];
            }
        )
        ->values();


    /*
    |--------------------------------------------------------------------------
    | Available Menu Items
    |--------------------------------------------------------------------------
    */

    $menuItems = MenuItem::query()
        ->available()
        ->with([
            'category:id,category_name',

            'variants' => function (
                $query
            ): void {

                $query
                    ->where(
                        'is_available',
                        true
                    )
                    ->orderBy(
                        'price'
                    )
                    ->orderBy(
                        'variant_name'
                    );
            },

            'addOns' => function (
                $query
            ): void {

                $query
                    ->available()
                    ->orderBy(
                        'add_on_name'
                    );
            },
        ])
        ->orderBy(
            'menu_name'
        )
        ->get()
        ->map(
            static function (
                MenuItem $menuItem
            ): array {

                return [
                    'id' =>
                        (int) $menuItem->id,

                    'menu_name' =>
                        $menuItem->menu_name,

                    'menu_category_id' =>
                        $menuItem->menu_category_id
                            ? (int) $menuItem->menu_category_id
                            : null,

                    'category_id' =>
                        $menuItem->menu_category_id
                            ? (int) $menuItem->menu_category_id
                            : null,

                    'category_name' =>
                        $menuItem->category?->category_name,

                    'item_type' =>
                        $menuItem->item_type,

                    'item_type_label' =>
                        $menuItem->item_type_label,

                    'price' =>
                        (float) $menuItem->price,

                    'price_formatted' =>
                        '৳ ' .
                        number_format(
                            (float) $menuItem->price,
                            2
                        ),

                    'display_name' =>
                        sprintf(
                            '%s — ৳ %s',
                            $menuItem->menu_name,
                            number_format(
                                (float) $menuItem->price,
                                2
                            )
                        ),

                    'image_url' =>
                        $menuItem->image_url,

                    'has_variants' =>
                        $menuItem
                            ->variants
                            ->isNotEmpty(),

                    'has_addons' =>
                        $menuItem
                            ->addOns
                            ->isNotEmpty(),

                    'variants' =>
                        $menuItem
                            ->variants
                            ->map(
                                static function (
                                    $variant
                                ): array {

                                    return [
                                        'id' =>
                                            (int) $variant->id,

                                        'menu_item_id' =>
                                            (int)
                                            $variant->menu_item_id,

                                        'variant_name' =>
                                            $variant->variant_name,

                                        'price' =>
                                            (float)
                                            $variant->price,

                                        'price_formatted' =>
                                            '৳ ' .
                                            number_format(
                                                (float)
                                                $variant->price,
                                                2
                                            ),

                                        'display_name' =>
                                            sprintf(
                                                '%s — ৳ %s',
                                                $variant->variant_name,
                                                number_format(
                                                    (float)
                                                    $variant->price,
                                                    2
                                                )
                                            ),
                                    ];
                                }
                            )
                            ->values(),

                    'addons' =>
                        $menuItem
                            ->addOns
                            ->map(
                                static function (
                                    $addon
                                ): array {

                                    return [
                                        'id' =>
                                            (int) $addon->id,

                                        'add_on_name' =>
                                            $addon->add_on_name,

                                        'price' =>
                                            (float) $addon->price,

                                        'price_formatted' =>
                                            '৳ ' .
                                            number_format(
                                                (float) $addon->price,
                                                2
                                            ),

                                        'display_name' =>
                                            sprintf(
                                                '%s — ৳ %s',
                                                $addon->add_on_name,
                                                number_format(
                                                    (float) $addon->price,
                                                    2
                                                )
                                            ),
                                    ];
                                }
                            )
                            ->values(),
                ];
            }
        )
        ->values();


    /*
    |--------------------------------------------------------------------------
    | Available Add-ons
    |--------------------------------------------------------------------------
    */

    $addons = AddOn::query()
        ->available()
        ->orderBy(
            'add_on_name'
        )
        ->get([
            'id',
            'add_on_name',
            'price',
            'description',
            'is_available',
        ]);


    /*
    |--------------------------------------------------------------------------
    | Current Order Status
    |--------------------------------------------------------------------------
    */

    $statuses = [
        [
            'value' =>
                $order->status,

            'label' =>
                ucfirst(
                    (string) $order->status
                ),
        ],
    ];


    /*
    |--------------------------------------------------------------------------
    | Waiter
    |--------------------------------------------------------------------------
    */

    $waiter = $order->creator;

    if (
        !$waiter &&
        $userId
    ) {
        $waiter =
            \App\Models\User::query()
                ->find($userId);
    }


    /*
    |--------------------------------------------------------------------------
    | Return
    |--------------------------------------------------------------------------
    */

    return [
        'order' =>
            $order,

        'tables' =>
            $tables,

        'merge_tables' =>
            $tables,

        'categories' =>
            $categories,

        'menu_items' =>
            $menuItems,

        'addons' =>
            $addons,

        'statuses' =>
            $statuses,

        'waiter' => [
            'id' =>
                $waiter?->id,

            'name' =>
                $waiter?->name,
        ],
    ];
}

    /**
     * Create a complete dine-in order.
     */
    /**
 * Create a new dine-in order.
 */
public function createOrder(
    array $data,
    ?int $userId
): Order {

    return DB::transaction(
        function () use (
            $data,
            $userId
        ): Order {


            /*
            |--------------------------------------------------------------------------
            | Resolve Reservation
            |--------------------------------------------------------------------------
            */

            $reservation = null;


            if (
                ! empty(
                    $data['reservation_id']
                    ?? null
                )
            ) {


                $reservation =
                    Reservation::query()
                        ->with([
                            'customer',
                            'tables',
                        ])
                        ->lockForUpdate()
                        ->findOrFail(
                            (int) $data['reservation_id']
                        );



                /*
                |--------------------------------------------------------------------------
                | Reservation must be seated
                |--------------------------------------------------------------------------
                */


                if (
                    ! in_array(
                        $reservation->status,
                        [
                            Reservation::STATUS_SEATED,
                        ],
                        true
                    )
                ) {

                    throw ValidationException::withMessages([
                        'reservation_id' => [
                            'Only a seated reservation can create an order. The reservation must not be cancelled or completed.',
                        ],
                    ]);

                }



                /*
                |--------------------------------------------------------------------------
                | Reservation must have tables
                |--------------------------------------------------------------------------
                */


                if (
                    $reservation
                        ->tables
                        ->isEmpty()
                ) {

                    throw ValidationException::withMessages([
                        'reservation_id' => [
                            'The selected reservation has no assigned tables.',
                        ],
                    ]);

                }

            }




            /*
            |--------------------------------------------------------------------------
            | Lock primary table
            |--------------------------------------------------------------------------
            */


            /*
|--------------------------------------------------------------------------
| Lock primary table
|--------------------------------------------------------------------------
|
| Table is optional for parcel / takeaway orders.
| For dine-in orders, restaurant_table_id will be provided.
|
*/

$primaryTable = null;

if (
    !empty(
        $data['restaurant_table_id']
        ?? null
    )
) {
    $primaryTable =
        RestaurantTable::query()
            ->lockForUpdate()
            ->findOrFail(
                (int) $data['restaurant_table_id']
            );
}




            /*
            |--------------------------------------------------------------------------
            | Reservation order:
            | Primary table MUST belong to reservation
            |--------------------------------------------------------------------------
            */


            /*
|--------------------------------------------------------------------------
| Reservation / Normal Order Table Validation
|--------------------------------------------------------------------------
|
| Reservation orders require a primary table.
| Normal dine-in orders also require a primary table.
| Table-less orders are allowed for non-dine-in flows.
|
*/

if ($reservation) {

    if (!$primaryTable) {
        throw ValidationException::withMessages([
            'restaurant_table_id' => [
                'A primary table is required when creating an order from a reservation.',
            ],
        ]);
    }

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
        ! $reservationTableIds->contains(
            (int) $primaryTable->id
        )
    ) {
        throw ValidationException::withMessages([
            'restaurant_table_id' => [
                'The selected primary table does not belong to the selected reservation.',
            ],
        ]);
    }

} elseif ($primaryTable) {

    /*
    |--------------------------------------------------------------------------
    | Normal dine-in order with table
    |--------------------------------------------------------------------------
    */

    $this->ensurePrimaryTableIsAvailable(
        table: $primaryTable,
        field: 'restaurant_table_id'
    );

} else {

    /*
    |--------------------------------------------------------------------------
    | Table-less order
    |--------------------------------------------------------------------------
    |
    | Allowed for parcel / takeaway / other non-table orders.
    |
    */

    $primaryTable = null;
}





            /*
            |--------------------------------------------------------------------------
            | Prepare merged table IDs
            |--------------------------------------------------------------------------
            */


            $mergedTableIds = collect(
                $data['merged_table_ids'] ?? []
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
    !$primaryTable &&
    $mergedTableIds->isNotEmpty()
) {
    throw ValidationException::withMessages([
        'merged_table_ids' => [
            'A primary table is required when merged tables are selected.',
        ],
    ]);
}

            if (
    $primaryTable &&
    $mergedTableIds->contains(
        (int) $primaryTable->id
    )
) {

    throw ValidationException::withMessages([
        'merged_table_ids' => [
            'The primary table cannot also be selected as a merged table.',
        ],
    ]);

}





            /*
            |--------------------------------------------------------------------------
            | Load merged tables
            |--------------------------------------------------------------------------
            */


            if (
                $reservation
            ) {



                /*
                |--------------------------------------------------------------------------
                | Reservation order
                |--------------------------------------------------------------------------
                |
                | Only reservation assigned tables allowed
                |
                */


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



                foreach (
                    $mergedTableIds
                    as $mergedTableId
                ) {


                    if (
                        ! $reservationTableIds->contains(
                            (int)
                            $mergedTableId
                        )
                    ) {


                        throw ValidationException::withMessages([
                            'merged_table_ids' => [
                                'One or more selected merged tables do not belong to the reservation.',
                            ],
                        ]);


                    }

                }




                $mergedTables =
                    RestaurantTable::query()
                        ->whereIn(
                            'id',
                            $mergedTableIds
                        )
                        ->lockForUpdate()
                        ->get();




                if (
                    $mergedTables->count()
                    !==
                    $mergedTableIds->count()
                ) {


                    throw ValidationException::withMessages([
                        'merged_table_ids' => [
                            'One or more reservation tables no longer exist.',
                        ],
                    ]);


                }



            } else {



                /*
                |--------------------------------------------------------------------------
                | Normal order
                |--------------------------------------------------------------------------
                */


                $mergedTables =
                    $this->loadMergedTables(
                        $mergedTableIds
                    );

                if ($primaryTable) {
                    $preMergedTables =
                        RestaurantTable::query()
                            ->where(
                                'merged_with_id',
                                $primaryTable->id
                            )
                            ->lockForUpdate()
                            ->get();

                    foreach ($preMergedTables as $preMergedTable) {
                        if (
                            !in_array(
                                $preMergedTable->current_status,
                                [
                                    RestaurantTable::STATUS_AVAILABLE,
                                    RestaurantTable::STATUS_OCCUPIED,
                                ],
                                true
                            ) ||
                            $preMergedTable->activeOrders()
                                ->lockForUpdate()
                                ->exists()
                        ) {
                            throw ValidationException::withMessages([
                                'restaurant_table_id' => [
                                    'The merged table group is not available for a new order.',
                                ],
                            ]);
                        }
                    }

                    $mergedTables = $mergedTables
                        ->concat($preMergedTables)
                        ->unique('id')
                        ->values();
                }


            }





            if ($primaryTable) {
                foreach ($mergedTables as $mergedTable) {
                    if ($mergedTable->section !== $primaryTable->section) {
                        throw ValidationException::withMessages([
                            'merged_table_ids' => [
                                'Tables can only be combined within the same section.',
                            ],
                        ]);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Reservation customer protection
            |--------------------------------------------------------------------------
            */


            if (
                $reservation
            ) {


                $reservationCustomerId =
                    $reservation->customer_id
                        ? (int)
                            $reservation->customer_id
                        : null;



                $submittedCustomerId =
                    ! empty(
                        $data['customer_id']
                        ?? null
                    )
                        ? (int)
                            $data['customer_id']
                        : null;




                /*
|--------------------------------------------------------------------------
| Reservation customer protection
|--------------------------------------------------------------------------
*/

if (
    $reservation
) {

    $reservationCustomerId =
        $reservation->customer_id
            ? (int)
                $reservation->customer_id
            : null;


    $submittedCustomerId =
        ! empty(
            $data['customer_id'] ?? null
        )
            ? (int)
                $data['customer_id']
            : null;



    /*
    |--------------------------------------------------------------------------
    | Customer must match reservation
    |--------------------------------------------------------------------------
    */

    if (
        $reservationCustomerId &&
        (
            !$submittedCustomerId ||
            $reservationCustomerId !==
            $submittedCustomerId
        )
    ) {

        throw ValidationException::withMessages([
            'customer_id' => [
                'The order customer must match the reservation customer.',
            ],
        ]);

    }



    /*
    |--------------------------------------------------------------------------
    | Force reservation customer
    |--------------------------------------------------------------------------
    |
    | Reservation customer is the source of truth.
    |
    */

    if (
        $reservationCustomerId
    ) {

        $data['customer_id'] =
            $reservationCustomerId;

    }

}




                /*
                |--------------------------------------------------------------------------
                | Force reservation customer
                |--------------------------------------------------------------------------
                */


                if (
                    $reservationCustomerId
                ) {

                    $data['customer_id'] =
                        $reservationCustomerId;

                }


            }




            /*
            |--------------------------------------------------------------------------
            | Resolve customer
            |--------------------------------------------------------------------------
            */


            $customer =
                $this->resolveCustomer(
                    $data
                );
            /*
            |--------------------------------------------------------------------------
            | Prepare and validate order items
            |--------------------------------------------------------------------------
            */


            $preparedItems =
                $this->prepareOrderItems(
                    $data['items']
                );



            $subtotal = round(
                (float)
                $preparedItems->sum(
                    'line_total'
                ),
                2
            );



            /*
            |--------------------------------------------------------------------------
            | Discount Calculation
            |--------------------------------------------------------------------------
            */


            $discount = $this->calculateOrderDiscount(
                subtotal: $subtotal,
                manualDiscount: (float) ($data['discount_amount'] ?? 0),
                customer: $customer
            );
            $discountAmount = $discount['discount_amount'];
            $offerId = $discount['offer_id'];
            $offerDiscountAmount = $discount['offer_discount_amount'];


            /*
|--------------------------------------------------------------------------
| Tax & Service Charge
|--------------------------------------------------------------------------
| Calculate using current restaurant system settings.
| Percentage is calculated on taxable amount after discount.
| Fixed amount is applied directly.
|--------------------------------------------------------------------------
*/

$settings =
    $this->systemSettingService->get();

$taxableAmount = round(
    max(
        0,
        $subtotal - $discountAmount
    ),
    2
);

$taxAmount = $this->calculateCharge(
    amount: $taxableAmount,
    type: (string) (
        $settings->vat_type
        ?? 'percentage'
    ),
    value: (float) (
        $settings->vat_rate
        ?? 0
    )
);

$serviceCharge = $this->calculateCharge(
    amount: $taxableAmount,
    type: (string) (
        $settings->service_charge_type
        ?? 'percentage'
    ),
    value: (float) (
        $settings->service_charge_rate
        ?? 0
    )
);

$totalAmount = round(
    max(
        0,
        $taxableAmount
        + $taxAmount
        + $serviceCharge
    ),
    2
);





            /*
            |--------------------------------------------------------------------------
            | Payment Calculation
            |--------------------------------------------------------------------------
            */


            $paidAmount = round(
                max(
                    0,
                    (float)
                    (
                        $data['paid_amount']
                        ?? 0
                    )
                ),
                2
            );



            if (
                $paidAmount >
                $totalAmount
            ) {

                throw ValidationException::withMessages([
                    'paid_amount' => [
                        'Paid amount cannot be greater than the total amount.',
                    ],
                ]);

            }



            $dueAmount = round(
                max(
                    0,
                    $totalAmount
                    -
                    $paidAmount
                ),
                2
            );



            $paymentStatus = match (true) {

                $paidAmount <= 0 =>
                    Order::PAYMENT_DUE,


                $dueAmount <= 0 =>
                    Order::PAYMENT_PAID,


                default =>
                    Order::PAYMENT_PARTIALLY_PAID,

            };




            $paymentMethod =
                $paidAmount > 0
                    ? $this->nullableString(
                        $data['payment_method']
                        ?? null
                    )
                    : null;



            $paymentReference =
                $paidAmount > 0
                    ? $this->nullableString(
                        $data['payment_reference']
                        ?? null
                    )
                    : null;




            if (
                $paidAmount > 0 &&
                blank($paymentMethod)
            ) {

                throw ValidationException::withMessages([
                    'payment_method' => [
                        'Payment method is required when a paid amount is provided.',
                    ],
                ]);

            }





            /*
            |--------------------------------------------------------------------------
            | Create Order
            |--------------------------------------------------------------------------
            */


            $order =
                Order::query()
                    ->create([


                        'order_number' =>
                            $this->generateOrderNumber(),



                        'customer_id' =>
                            $customer?->id,



                        'customer_name' =>
                            $customer?->name
                            ??
                            $this->nullableString(
                                $data['customer_name']
                                ?? null
                            ),



                        'customer_phone' =>
                            $customer?->phone
                            ??
                            $this->nullableString(
                                $data['customer_phone']
                                ?? null
                            ),



                        'customer_email' =>
                            $customer?->email
                            ??
                            $this->nullableString(
                                $data['customer_email']
                                ?? null
                            ),



                        'restaurant_table_id' =>
    $primaryTable?->id,

                        'fulfillment_type' =>
                            ($data['fulfillment_type'] ?? 'dine_in') === 'takeaway'
                                ? 'takeaway'
                                : 'dine_in',



                        /*
                        |--------------------------------------------------------------------------
                        | Reservation Link
                        |--------------------------------------------------------------------------
                        */


                        'reservation_id' =>
                            $reservation?->id,



                        'status' =>
                            Order::STATUS_PENDING,



                        'subtotal' =>
                            $subtotal,



                        'discount_amount' =>
                            $discountAmount,

                        'offer_id' =>
                            $offerId,

                        'offer_discount_amount' =>
                            $offerDiscountAmount,



                        'tax_amount' =>
                            $taxAmount,



                        'service_charge' =>
                            $serviceCharge,



                        'total_amount' =>
                            $totalAmount,



                        'paid_amount' =>
                            $paidAmount,



                        'due_amount' =>
                            $dueAmount,



                        'payment_status' =>
                            $paymentStatus,



                        'payment_method' =>
                            $paymentMethod,



                        'payment_breakdown' =>
                            null,



                        'payment_reference' =>
                            $paymentReference,



                        'order_note' =>
                            $this->nullableString(
                                $data['order_note']
                                ?? null
                            ),



                        'kitchen_note' =>
                            null,



                        'sent_to_kitchen_at' =>
                            null,



                        'is_customer_spend_recorded' =>
                            false,



                        'customer_spend_recorded_at' =>
                            null,



                        'created_by' =>
    $userId,


]);



/*
|--------------------------------------------------------------------------
| Initial Payment Ledger
|--------------------------------------------------------------------------
|
| Keep orders payment summary
| and payment ledger synchronized.
|
*/

if (
    $paidAmount > 0
) {

    $order
        ->payments()
        ->create([

            'amount' =>
                $paidAmount,


            'payment_method' =>
                $paymentMethod,


            'reference' =>
                $paymentReference,


            'note' =>
                'Initial payment during order creation.',


            'received_by' =>
                $userId,

        ]);

}



/*
|--------------------------------------------------------------------------
| Create Initial Kitchen Batch
|--------------------------------------------------------------------------
*/

$kitchenBatch =
    $order->kitchenBatches()
        ->create([
            'batch_no' =>
                1,

            'status' =>
                OrderKitchenBatch::STATUS_PENDING,

            'chef_id' =>
                null,

            'sent_to_kitchen_at' =>
                now(),

            'preparing_at' =>
                null,

            'ready_at' =>
                null,

            'served_at' =>
                null,

            'created_by' =>
                $userId,
        ]);

        /*
|--------------------------------------------------------------------------
| Create Order Items
|--------------------------------------------------------------------------
*/

foreach (
    $preparedItems
    as $preparedItem
) {

    $orderItem =
        $order
            ->items()
            ->create([

                'order_kitchen_batch_id' =>
                    $kitchenBatch->id,


                'menu_item_id' =>
                    $preparedItem[
                        'menu_item_id'
                    ],


                'menu_item_variant_id' =>
                    $preparedItem[
                        'menu_item_variant_id'
                    ],


                'item_name' =>
                    $preparedItem[
                        'item_name'
                    ],


                'variant_name' =>
                    $preparedItem[
                        'variant_name'
                    ],


                'unit_price' =>
                    $preparedItem[
                        'unit_price'
                    ],


                'quantity' =>
                    $preparedItem[
                        'quantity'
                    ],


                'addon_total' =>
                    $preparedItem[
                        'addon_total'
                    ],


                'line_total' =>
                    $preparedItem[
                        'line_total'
                    ],


                'status' =>
                    Order::STATUS_PENDING,


                'kitchen_note' =>
                    $preparedItem[
                        'kitchen_note'
                    ],

            ]);



    /*
    |--------------------------------------------------------------------------
    | Create Item Addons
    |--------------------------------------------------------------------------
    */

    foreach (
        $preparedItem['addons']
        as $addon
    ) {

        $orderItem
            ->addons()
            ->create([

                'menu_addon_id' =>
                    $addon['id'],


                'addon_name' =>
                    $addon['addon_name'],


                'unit_price' =>
                    $addon['unit_price'],


                'quantity' =>
                    $preparedItem['quantity'],


                'total_price' =>
                    $addon['total_price'],

            ]);

    }

}





            /*
            |--------------------------------------------------------------------------
            | Attach Tables
            |--------------------------------------------------------------------------
            */


            /*
|--------------------------------------------------------------------------
| Attach Tables
|--------------------------------------------------------------------------
|
| Table is optional.
| If no primary table and no merged tables are selected,
| the order simply has no order_tables records.
|
*/

$tableSyncData = [];

if ($primaryTable) {

    $tableSyncData[
        (int) $primaryTable->id
    ] = [
        'is_primary' => true,
    ];

}

foreach (
    $mergedTables
    as $mergedTable
) {

    $tableSyncData[
        (int) $mergedTable->id
    ] = [
        'is_primary' => false,
    ];

}

$order
    ->tables()
    ->sync(
        $tableSyncData
    );







            /*
            |--------------------------------------------------------------------------
            | Collect Selected Tables
            |--------------------------------------------------------------------------
            */


            /*
|--------------------------------------------------------------------------
| Selected Table IDs
|--------------------------------------------------------------------------
|
| Table is optional for parcel / takeaway orders.
|
*/

$allSelectedTableIds = collect();

if ($primaryTable) {

    $allSelectedTableIds->push(
        (int) $primaryTable->id
    );

}

$allSelectedTableIds =
    $allSelectedTableIds
        ->merge(
            $mergedTables->pluck('id')
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







            /*
            |--------------------------------------------------------------------------
            | Mark Tables Occupied
            |--------------------------------------------------------------------------
            |
            | Reservation order:
            | Keep reservation timing information.
            |
            */


            RestaurantTable::query()
                ->whereIn(
                    'id',
                    $allSelectedTableIds
                )
                ->update([


                    'status' =>
                        RestaurantTable::STATUS_OCCUPIED,



                    'reservation_start_at' =>
                        $reservation
                            ? $primaryTable
                                ->reservation_start_at
                            : null,



                    'reservation_end_at' =>
                        $reservation
                            ? $primaryTable
                                ->reservation_end_at
                            : null,


                ]);


            if ($order->fulfillment_type === 'takeaway') {
                $order->forceFill([
                    'pickup_token' => 'T-' . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT),
                    'pickup_status' => 'uncollected',
                ])->save();
            }







            /*
            |--------------------------------------------------------------------------
            | Merge Relationship
            |--------------------------------------------------------------------------
            */


            if (
                $mergedTables->isNotEmpty()
            ) {


                RestaurantTable::query()
                    ->whereIn(
                        'id',
                        $mergedTables
                            ->pluck('id')
                    )
                    ->update([

                        'merged_with_id' =>
                            $primaryTable?->id,

                    ]);

                $primaryTable->update([

                    'merged_with_id' =>
                        null,

                ]);

            }





            return $order->fresh([
                'customer',
                'reservation',
                'primaryTable',
                'tables',
                'items.addons',
                'payments.receiver',
                'latestKitchenBatch',
                'kitchenBatches',
                'creator',
            ]);
        }
    );

}

/**
 * Update an existing active dine-in order.
 */
public function updateOrder(
    Order $order,
    array $data,
    ?int $userId
): Order {
    return DB::transaction(
        function () use (
            $order,
            $data,
            $userId
        ): Order {
            /*
            |--------------------------------------------------------------------------
            | Lock existing order
            |--------------------------------------------------------------------------
            */

            $lockedOrder = Order::query()
                ->with([
                    'customer',
                    'tables',
                    'items.addons',
                    'payments',
                    'recipeConsumption',
                ])
                ->lockForUpdate()
                ->findOrFail(
                    $order->id
                );

            /*
            |--------------------------------------------------------------------------
            | Prevent editing finalized orders
            |--------------------------------------------------------------------------
            */

            if ($lockedOrder->isFinalized()) {
                throw ValidationException::withMessages([
                    'order' => [
                        'A completed or canceled order cannot be edited.',
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Served Order: Payment / Extension Branch
            |--------------------------------------------------------------------------
            |
            | Never run a served order through the legacy rebuild path below.
            | Historical served items and recipe-consumption rows are immutable.
            |
            */

            if (
                $lockedOrder->status ===
                Order::STATUS_SERVED
            ) {
                return $this
                    ->updateServedOrder(
                        order: $lockedOrder,
                        data: $data,
                        userId: $userId
                    );
            }

            $this->ensureOrderCanBeEdited(
                $lockedOrder
            );

            /*
            |--------------------------------------------------------------------------
            | Lock Editable Pending Batch
            |--------------------------------------------------------------------------
            */

            $editableBatch =
                $this->lockLatestKitchenBatch(
                    $lockedOrder
                );

            if (
                $editableBatch->status !==
                OrderKitchenBatch::STATUS_PENDING
            ) {
                throw ValidationException::withMessages([
                    'kitchen_batch' => [
                        'Only a pending kitchen batch can be edited.',
                    ],
                ]);
            }

            if (
                $lockedOrder
                    ->recipeConsumptions()
                    ->where(
                        'order_kitchen_batch_id',
                        $editableBatch->id
                    )
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'kitchen_batch' => [
                        'This kitchen batch can no longer be edited because recipe consumption has already been recorded.',
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Current assigned tables
            |--------------------------------------------------------------------------
            */

            $currentTableIds = $lockedOrder
                ->tables
                ->pluck('id')
                ->map(
                    static fn (mixed $id): int =>
                        (int) $id
                )
                ->unique()
                ->values();

            if (
                $currentTableIds->isEmpty() &&
                $lockedOrder->restaurant_table_id
            ) {
                $currentTableIds = collect([
                    (int) $lockedOrder
                        ->restaurant_table_id,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Lock and validate new primary table
            |--------------------------------------------------------------------------
            */

            /*
|--------------------------------------------------------------------------
| Lock and validate new primary table
|--------------------------------------------------------------------------
|
| Table is optional.
| Table-less orders are allowed for parcel / takeaway flows.
|
*/

$primaryTable = null;

if (
    !empty(
        $data['restaurant_table_id']
        ?? null
    )
) {
    $primaryTable = RestaurantTable::query()
        ->lockForUpdate()
        ->findOrFail(
            (int) $data['restaurant_table_id']
        );
}

            /*
            |--------------------------------------------------------------------------
            | Prepare merged table IDs
            |--------------------------------------------------------------------------
            */

            $mergedTableIds = collect(
                $data['merged_table_ids'] ?? []
            )
                ->filter(
                    static fn (mixed $id): bool =>
                        $id !== null &&
                        $id !== ''
                )
                ->map(
                    static fn (mixed $id): int =>
                        (int) $id
                )
                ->unique()
                ->values();

            if (
                ! $primaryTable &&
                $mergedTableIds->isNotEmpty()
            ) {
                throw ValidationException::withMessages([
                    'merged_table_ids' => [
                        'A primary table is required when merged tables are selected.',
                    ],
                ]);
            }

            if (
                $primaryTable &&
                $mergedTableIds->contains(
                    (int) $primaryTable->id
                )
            ) {
                throw ValidationException::withMessages([
                    'merged_table_ids' => [
                        'The primary table cannot also be selected as a merged table.',
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Load and lock merged tables
            |--------------------------------------------------------------------------
            */

            $mergedTables = $mergedTableIds->isEmpty()
                ? collect()
                : RestaurantTable::query()
                    ->whereIn(
                        'id',
                        $mergedTableIds
                    )
                    ->lockForUpdate()
                    ->get();

            if (
                $mergedTables->count() !==
                $mergedTableIds->count()
            ) {
                throw ValidationException::withMessages([
                    'merged_table_ids' => [
                        'One or more selected merged tables no longer exist.',
                    ],
                ]);
            }

            if ($primaryTable) {
                foreach ($mergedTables as $mergedTable) {
                    if ($mergedTable->section !== $primaryTable->section) {
                        throw ValidationException::withMessages([
                            'merged_table_ids' => [
                                'Tables can only be combined within the same section.',
                            ],
                        ]);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Validate selected tables
            |--------------------------------------------------------------------------
            |
            | Existing tables assigned to this order may remain occupied.
            | Newly selected tables must be available.
            |
            */

            if ($primaryTable) {
    $this->ensureTableCanBeUsedForUpdate(
        table: $primaryTable,
        field: 'restaurant_table_id',
        order: $lockedOrder,
        currentTableIds: $currentTableIds
    );
}

foreach (
    $mergedTables
    as $mergedTable
) {
    $this->ensureTableCanBeUsedForUpdate(
        table: $mergedTable,
        field: 'merged_table_ids',
        order: $lockedOrder,
        currentTableIds: $currentTableIds
    );
}

            /*
            |--------------------------------------------------------------------------
            | Resolve customer
            |--------------------------------------------------------------------------
            */

            $oldCustomerId =
                $lockedOrder->customer_id;

            $customer = $this->resolveCustomer(
                $data
            );

            $newCustomerId =
                $customer?->id;

            /*
            |--------------------------------------------------------------------------
            | Prepare and validate order items
            |--------------------------------------------------------------------------
            */

            $preparedItems = $this->prepareOrderItems(
                $data['items']
            );

            $subtotal = round(
                (float) $preparedItems->sum(
                    'line_total'
                ),
                2
            );

            /*
            |--------------------------------------------------------------------------
            | Calculate discount
            |--------------------------------------------------------------------------
            */

            $discount = $this->calculateOrderDiscount(
                subtotal: $subtotal,
                manualDiscount: (float) ($data['discount_amount'] ?? 0),
                customer: $customer,
                excludeOrderId: (int) $lockedOrder->id
            );
            $discountAmount = $discount['discount_amount'];
            $offerId = $discount['offer_id'];
            $offerDiscountAmount = $discount['offer_discount_amount'];

            /*
            |--------------------------------------------------------------------------
            | Calculate tax, service charge and total
            |--------------------------------------------------------------------------
            |
            | Tax and service charge remain zero until dynamic restaurant
            | settings are implemented.
            |
            */

            /*
|--------------------------------------------------------------------------
| Tax & Service Charge
|--------------------------------------------------------------------------
*/

$settings =
    $this->systemSettingService->get();

$taxableAmount = round(
    max(
        0,
        $subtotal - $discountAmount
    ),
    2
);

$taxAmount = $this->calculateCharge(
    amount: $taxableAmount,
    type: (string) (
        $settings->vat_type
        ?? 'percentage'
    ),
    value: (float) (
        $settings->vat_rate
        ?? 0
    )
);

$serviceCharge = $this->calculateCharge(
    amount: $taxableAmount,
    type: (string) (
        $settings->service_charge_type
        ?? 'percentage'
    ),
    value: (float) (
        $settings->service_charge_rate
        ?? 0
    )
);

$totalAmount = round(
    max(
        0,
        $taxableAmount
        + $taxAmount
        + $serviceCharge
    ),
    2
);

            /*
            |--------------------------------------------------------------------------
            | Existing payment history total
            |--------------------------------------------------------------------------
            |
            | Payment history is the audit source of truth. The paid amount cannot
            | be reduced below money that has already been recorded as received.
            |
            */

            $recordedPaidAmount = round(
                (float) $lockedOrder
                    ->payments()
                    ->sum('amount'),
                2
            );

            /*
            |--------------------------------------------------------------------------
            | Requested cumulative paid amount
            |--------------------------------------------------------------------------
            */

            $paidAmount = round(
                max(
                    0,
                    (float) (
                        $data['paid_amount']
                        ?? $recordedPaidAmount
                    )
                ),
                2
            );

            /*
            |--------------------------------------------------------------------------
            | Validate payment against recorded history and new total
            |--------------------------------------------------------------------------
            */

            if ($paidAmount < $recordedPaidAmount) {
                throw ValidationException::withMessages([
                    'paid_amount' => [
                        'Paid amount cannot be less than the amount already recorded in payment history.',
                    ],
                ]);
            }

            if ($recordedPaidAmount > $totalAmount) {
                throw ValidationException::withMessages([
                    'total_amount' => [
                        'The updated order total cannot be less than the amount already paid.',
                    ],
                ]);
            }

            if ($paidAmount > $totalAmount) {
                throw ValidationException::withMessages([
                    'paid_amount' => [
                        'Paid amount cannot be greater than the total amount.',
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Calculate additional payment
            |--------------------------------------------------------------------------
            */

            $newPaymentAmount = round(
                $paidAmount - $recordedPaidAmount,
                2
            );

            /*
            |--------------------------------------------------------------------------
            | Resolve payment method and reference
            |--------------------------------------------------------------------------
            */

            $paymentMethod = $newPaymentAmount > 0
                ? $this->nullableString(
                    $data['payment_method'] ?? null
                )
                : $lockedOrder->payment_method;

            $paymentReference = $newPaymentAmount > 0
                ? $this->nullableString(
                    $data['payment_reference'] ?? null
                )
                : $lockedOrder->payment_reference;

            if (
                $newPaymentAmount > 0 &&
                blank($paymentMethod)
            ) {
                throw ValidationException::withMessages([
                    'payment_method' => [
                        'Payment method is required when an additional payment is provided.',
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Calculate payment summary
            |--------------------------------------------------------------------------
            */

            $dueAmount = round(
                max(
                    0,
                    $totalAmount - $paidAmount
                ),
                2
            );

            $paymentStatus = match (true) {
                $paidAmount <= 0 =>
                    Order::PAYMENT_DUE,

                $dueAmount <= 0 =>
                    Order::PAYMENT_PAID,

                default =>
                    Order::PAYMENT_PARTIALLY_PAID,
            };

            /*
            |--------------------------------------------------------------------------
            | Continue in Part 2
            |--------------------------------------------------------------------------
            */
                        /*
            |--------------------------------------------------------------------------
            | Resolve order status and timestamps
            |--------------------------------------------------------------------------
            */

            $newStatus =
                $data['status']
                ?? $lockedOrder->status;

            if (
                $newStatus !==
                Order::STATUS_PENDING
            ) {
                throw ValidationException::withMessages([
                    'status' => [
                        'Kitchen status transitions must use the kitchen workflow. An editable order must remain pending.',
                    ],
                ]);
            }

            $statusTimestamps =
                $this->buildOrderStatusTimestamps(
                    order: $lockedOrder,
                    status: $newStatus
                );

            /*
            |--------------------------------------------------------------------------
            | Update main order
            |--------------------------------------------------------------------------
            */

            $lockedOrder->update([
                'customer_id' =>
                    $newCustomerId,

                'customer_name' =>
                    $customer?->name
                    ?? $this->nullableString(
                        $data['customer_name']
                        ?? null
                    ),

                'customer_phone' =>
                    $customer?->phone
                    ?? $this->nullableString(
                        $data['customer_phone']
                        ?? null
                    ),

                'customer_email' =>
                    $customer?->email
                    ?? $this->nullableString(
                        $data['customer_email']
                        ?? null
                    ),

                'restaurant_table_id' =>
                    $primaryTable?->id,

                'status' =>
                    $newStatus,

                'subtotal' =>
                    $subtotal,

                'discount_amount' =>
                    $discountAmount,

                'offer_id' =>
                    $offerId,

                'offer_discount_amount' =>
                    $offerDiscountAmount,

                'tax_amount' =>
                    $taxAmount,

                'service_charge' =>
                    $serviceCharge,

                'total_amount' =>
                    $totalAmount,

                'paid_amount' =>
                    $paidAmount,

                'due_amount' =>
                    $dueAmount,

                'payment_status' =>
                    $paymentStatus,

                /*
                 * Keep the latest successful payment method
                 * as the order-level payment summary.
                 */
                'payment_method' =>
                    $paymentMethod,

                'payment_breakdown' =>
                    null,

                'payment_reference' =>
                    $paymentReference,

                'order_note' =>
                    $this->nullableString(
                        $data['order_note']
                        ?? null
                    ),

                /*
                 * Preserve the original creator.
                 * Use the current user only when the creator is missing.
                 */
                'created_by' =>
                    $lockedOrder->created_by
                    ?? $userId,

                ...$statusTimestamps,
            ]);
            /*
            |--------------------------------------------------------------------------
            | Save additional payment history
            |--------------------------------------------------------------------------
            |
            | Only the amount added beyond the existing payment-history total is
            | inserted. Editing an order without increasing paid_amount will not
            | create a duplicate payment record.
            |
            */

            if ($newPaymentAmount > 0) {
                $lockedOrder
                    ->payments()
                    ->create([
                        'amount' =>
                            $newPaymentAmount,

                        'payment_method' =>
                            $paymentMethod,

                        'reference' =>
                            $paymentReference,

                        'note' =>
                            'Additional payment received during order update.',

                        'received_by' =>
                            $userId,
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Verify and synchronise payment summary
            |--------------------------------------------------------------------------
            |
            | The database payment-history total is recalculated after inserting
            | the additional payment. This prevents the order summary and payment
            | history from becoming inconsistent.
            |
            */

            $updatedRecordedPaidAmount = round(
                (float) $lockedOrder
                    ->payments()
                    ->sum('amount'),
                2
            );

            $updatedDueAmount = round(
                max(
                    0,
                    $totalAmount
                    - $updatedRecordedPaidAmount
                ),
                2
            );

            $updatedPaymentStatus = match (true) {
                $updatedRecordedPaidAmount <= 0 =>
                    Order::PAYMENT_DUE,

                $updatedDueAmount <= 0 =>
                    Order::PAYMENT_PAID,

                default =>
                    Order::PAYMENT_PARTIALLY_PAID,
            };

            /*
             * Normally these values already match the first update. This second
             * update guarantees that payment history remains the source of truth.
             */
            $lockedOrder->update([
                'paid_amount' =>
                    $updatedRecordedPaidAmount,

                'due_amount' =>
                    $updatedDueAmount,

                'payment_status' =>
                    $updatedPaymentStatus,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Build newly selected table IDs
            |--------------------------------------------------------------------------
            */

            $newSelectedTableIds = collect();

if ($primaryTable) {
    $newSelectedTableIds->push(
        (int) $primaryTable->id
    );
}

$newSelectedTableIds = $newSelectedTableIds
    ->merge(
        $mergedTables
            ->pluck('id')
            ->map(
                static fn (
                    mixed $id
                ): int =>
                    (int) $id
            )
    )
    ->unique()
    ->values();

            /*
            |--------------------------------------------------------------------------
            | Determine removed tables
            |--------------------------------------------------------------------------
            */

            $removedTableIds = $currentTableIds
                ->diff(
                    $newSelectedTableIds
                )
                ->values();

            /*
            |--------------------------------------------------------------------------
            | Release removed tables
            |--------------------------------------------------------------------------
            */

            if ($removedTableIds->isNotEmpty()) {
                RestaurantTable::query()
                    ->whereIn(
                        'id',
                        $removedTableIds
                    )
                    ->lockForUpdate()
                    ->update([
                        'status' =>
                            RestaurantTable::STATUS_AVAILABLE,

                        'merged_with_id' =>
                            null,

                        'reservation_start_at' =>
                            null,

                        'reservation_end_at' =>
                            null,
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Continue in Part 3
            |--------------------------------------------------------------------------
            */
                        /*
            |--------------------------------------------------------------------------
            | Occupy selected tables
            |--------------------------------------------------------------------------
            */

            RestaurantTable::query()
                ->whereIn(
                    'id',
                    $newSelectedTableIds
                )
                ->lockForUpdate()
                ->update([
                    'status' =>
                        RestaurantTable::STATUS_OCCUPIED,

                    'merged_with_id' =>
                        null,

                    'reservation_start_at' =>
                        null,

                    'reservation_end_at' =>
                        null,
                ]);

            /*
            |--------------------------------------------------------------------------
            | Rebuild physical merge relationship
            |--------------------------------------------------------------------------
            */

            if (
    $primaryTable &&
    $mergedTables->isNotEmpty()
) {
    RestaurantTable::query()
        ->whereIn(
            'id',
            $mergedTableIds
        )
        ->update([
            'merged_with_id' =>
                $primaryTable->id,
        ]);
}

            /*
             * Ensure the primary table is never marked as
             * merged into another table.
             */
            if ($primaryTable) {
    $primaryTable->update([
        'merged_with_id' =>
            null,
    ]);
}

            /*
            |--------------------------------------------------------------------------
            | Sync order-table pivot
            |--------------------------------------------------------------------------
            */

            $tableSyncData = [];

            if ($primaryTable) {
                $tableSyncData[
                    (int) $primaryTable->id
                ] = [
                    'is_primary' => true,
                ];
            }

            foreach (
                $mergedTables
                as $mergedTable
            ) {
                $tableSyncData[
                    (int) $mergedTable->id
                ] = [
                    'is_primary' => false,
                ];
            }

            $lockedOrder
                ->tables()
                ->sync(
                    $tableSyncData
                );

            /*
            |--------------------------------------------------------------------------
            | Remove existing order items and add-ons
            |--------------------------------------------------------------------------
            |
            | The edit form submits the complete updated order state.
            | Existing item snapshots are therefore replaced.
            |
            */

            $existingItems = $lockedOrder
                ->items()
                ->with('addons')
                ->lockForUpdate()
                ->get();

            foreach (
                $existingItems
                as $existingItem
            ) {
                $existingItem
                    ->addons()
                    ->delete();

                $existingItem->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | Save updated order items
            |--------------------------------------------------------------------------
            */

            foreach (
                $preparedItems
                as $preparedItem
            ) {
                $orderItem = $lockedOrder
                    ->items()
                    ->create([
                        'order_kitchen_batch_id' =>
                            $editableBatch->id,

                        'menu_item_id' =>
                            $preparedItem[
                                'menu_item_id'
                            ],

                        'menu_item_variant_id' =>
                            $preparedItem[
                                'menu_item_variant_id'
                            ],

                        'item_name' =>
                            $preparedItem[
                                'item_name'
                            ],

                        'variant_name' =>
                            $preparedItem[
                                'variant_name'
                            ],

                        'unit_price' =>
                            $preparedItem[
                                'unit_price'
                            ],

                        'quantity' =>
                            $preparedItem[
                                'quantity'
                            ],

                        'addon_total' =>
                            $preparedItem[
                                'addon_total'
                            ],

                        'line_total' =>
                            $preparedItem[
                                'line_total'
                            ],

                        'status' =>
                            $newStatus,

                        'kitchen_note' =>
                            $preparedItem[
                                'kitchen_note'
                            ],
                    ]);

                /*
                |--------------------------------------------------------------------------
                | Save updated item add-ons
                |--------------------------------------------------------------------------
                */

                foreach (
                    $preparedItem['addons']
                    as $addon
                ) {
                    $orderItem
                        ->addons()
                        ->create([
                            'menu_addon_id' =>
                                $addon['id'],

                            'addon_name' =>
                                $addon[
                                    'addon_name'
                                ],

                            'unit_price' =>
                                $addon[
                                    'unit_price'
                                ],

                            'quantity' =>
                                $preparedItem[
                                    'quantity'
                                ],

                            'total_price' =>
                                $addon[
                                    'total_price'
                                ],
                        ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Continue in Part 4
            |--------------------------------------------------------------------------
            */
                        /*
            |--------------------------------------------------------------------------
            | Handle customer change
            |--------------------------------------------------------------------------
            */

            if (
                $oldCustomerId &&
                (int) $oldCustomerId !==
                (int) $newCustomerId
            ) {
                $oldCustomer = Customer::query()
                    ->lockForUpdate()
                    ->find(
                        $oldCustomerId
                    );

                if ($oldCustomer) {
                    $oldCustomer->update([
                        'total_orders' =>
                            max(
                                0,
                                (int)
                                    $oldCustomer
                                        ->total_orders
                                - 1
                            ),
                    ]);
                }
            }

            if (
                $newCustomerId &&
                (int) $oldCustomerId !==
                (int) $newCustomerId
            ) {
                $newCustomer = Customer::query()
                    ->lockForUpdate()
                    ->find(
                        $newCustomerId
                    );

                if ($newCustomer) {
                    $newCustomer->update([
                        'total_orders' =>
                            (int)
                                $newCustomer
                                    ->total_orders
                            + 1,

                        'last_visit_at' =>
                            now(),
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Refresh order relationships
            |--------------------------------------------------------------------------
            */

            return $lockedOrder->fresh([
                'customer',
                'reservation',
                'primaryTable',
                'tables',
                'items.addons',
                'payments.receiver',
                'recipeConsumption',
                'creator',
            ]);
        }
    );
}

public function updateStatus(
    Order $order,
    string $newStatus
): Order {
    return DB::transaction(
        function () use (
            $order,
            $newStatus
        ): Order {
            $lockedOrder = Order::query()
                ->with([
                    'recipeConsumptions',
                    'latestKitchenBatch',
                ])
                ->lockForUpdate()
                ->findOrFail(
                    $order->id
                );

            if ($lockedOrder->isFinalized()) {
                throw ValidationException::withMessages([
                    'status' => [
                        'A completed or canceled order cannot be updated.',
                    ],
                ]);
            }

            if (
                ! in_array(
                    $newStatus,
                    Order::allowedStatuses(),
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'status' => [
                        'The selected order status is invalid.',
                    ],
                ]);
            }

            if (
                $newStatus ===
                $lockedOrder->status
            ) {
                return $this->freshOrder(
                    $lockedOrder
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Kitchen-controlled transitions
            |--------------------------------------------------------------------------
            */

            if (
                in_array(
                    $newStatus,
                    [
                        Order::STATUS_PREPARING,
                        Order::STATUS_READY,
                    ],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'status' => [
                        'Preparing and ready statuses must be updated through the kitchen workflow.',
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Ready -> Served
            |--------------------------------------------------------------------------
            |
            | Only the latest ready kitchen batch is served. Historical batches and
            | their item rows are never rewritten.
            |
            */

            if (
                $newStatus ===
                Order::STATUS_SERVED
            ) {
                if (
                    $lockedOrder->status !==
                    Order::STATUS_READY
                ) {
                    throw ValidationException::withMessages([
                        'status' => [
                            'Only a ready order can be marked as served.',
                        ],
                    ]);
                }

                $latestBatch =
                    $this->lockLatestKitchenBatch(
                        $lockedOrder
                    );

                if (
                    $latestBatch->status !==
                    OrderKitchenBatch::STATUS_READY
                ) {
                    throw ValidationException::withMessages([
                        'kitchen_batch' => [
                            'Only the latest ready kitchen batch can be marked as served.',
                        ],
                    ]);
                }

                $hasConsumption =
                    $lockedOrder
                        ->recipeConsumptions()
                        ->where(
                            'order_kitchen_batch_id',
                            $latestBatch->id
                        )
                        ->exists();

                if (!$hasConsumption) {
                    throw ValidationException::withMessages([
                        'status' => [
                            'The latest kitchen batch has no recipe consumption record. Complete the kitchen preparation workflow before serving it.',
                        ],
                    ]);
                }

                $servedAt = now();

                $latestBatch->update([
                    'status' =>
                        OrderKitchenBatch::STATUS_SERVED,
                    'served_at' =>
                        $latestBatch->served_at
                        ?? $servedAt,
                ]);

                $latestBatch
                    ->items()
                    ->where(
                        'status',
                        '!=',
                        Order::STATUS_CANCELED
                    )
                    ->update([
                        'status' =>
                            Order::STATUS_SERVED,
                    ]);

                $servedOrderUpdates = [
                    'status' =>
                        Order::STATUS_SERVED,
                    'served_at' =>
                        $servedAt,
                ];

                if (
                    $lockedOrder->fulfillment_type === 'takeaway'
                    && $lockedOrder->pickup_status !== 'collected'
                ) {
                    $servedOrderUpdates['pickup_status'] = 'collected';
                    $servedOrderUpdates['picked_up_at'] = $servedAt;
                }

                $lockedOrder->update($servedOrderUpdates);

                return $this->freshOrder(
                    $lockedOrder
                );
            }

            if (
                $newStatus ===
                Order::STATUS_CANCELED
            ) {
                throw ValidationException::withMessages([
                    'status' => [
                        'Use the order cancellation action to cancel an order.',
                    ],
                ]);
            }

            if (
                $newStatus ===
                Order::STATUS_COMPLETED
            ) {
                throw ValidationException::withMessages([
                    'status' => [
                        'Use the order completion action to complete an order.',
                    ],
                ]);
            }

            throw ValidationException::withMessages([
                'status' => [
                    'This order status transition is not allowed from the general order update endpoint.',
                ],
            ]);
        },
        3
    );
}

    /**
     * Cancel an active order.
 */
public function cancelOrder(
    Order $order,
    string $reason
): Order {
    return DB::transaction(
        function () use (
            $order,
            $reason
        ): Order {

            $lockedOrder =
                Order::query()
                    ->with([
                        'customer',
                        'tables',
                        'reservation',
                    ])
                    ->lockForUpdate()
                    ->findOrFail(
                        $order->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Prevent canceling finalized orders
            |--------------------------------------------------------------------------
            */

            if (
                $lockedOrder->isFinalized()
            ) {
                throw ValidationException::withMessages([
                    'order' => [
                        'This order is already completed or canceled.',
                    ],
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Store original payment amount
            |--------------------------------------------------------------------------
            */

            $previousPaidAmount =
                round(
                    (float) $lockedOrder->paid_amount,
                    2
                );


            /*
            |--------------------------------------------------------------------------
            | Reverse customer spending if already recorded
            |--------------------------------------------------------------------------
            */

            if (
                $lockedOrder->customer &&
                $lockedOrder->is_customer_spend_recorded &&
                $previousPaidAmount > 0
            ) {

                $customer =
                    Customer::query()
                        ->lockForUpdate()
                        ->find(
                            $lockedOrder->customer_id
                        );


                if ($customer) {

                    $customer->update([

                        'total_spent' =>
                            round(
                                max(
                                    0,
                                    (float)
                                        $customer->total_spent
                                        -
                                    $previousPaidAmount
                                ),
                                2
                            ),

                    ]);
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Cancel Order & Reset Payment Summary
            |--------------------------------------------------------------------------
            */

            $lockedOrder->update([

                'status' =>
                    Order::STATUS_CANCELED,


                'canceled_at' =>
                    now(),


                'cancellation_reason' =>
                    trim($reason),



                /*
                |--------------------------------------------------------------------------
                | Payment Reset
                |--------------------------------------------------------------------------
                */

                'paid_amount' =>
                    0.00,


                'due_amount' =>
                    0.00,


                'payment_status' =>
                    Order::PAYMENT_DUE,


                'payment_method' =>
                    null,


                'payment_breakdown' =>
                    null,


                'payment_reference' =>
                    null,



                /*
                |--------------------------------------------------------------------------
                | Customer Spend Reset
                |--------------------------------------------------------------------------
                */

                'is_customer_spend_recorded' =>
                    false,


                'customer_spend_recorded_at' =>
                    null,

            ]);



            /*
            |--------------------------------------------------------------------------
            | Cancel all order items
            |--------------------------------------------------------------------------
            */

            $lockedOrder
                ->items()
                ->update([

                    'status' =>
                        Order::STATUS_CANCELED,

                ]);



            /*
|--------------------------------------------------------------------------
| Release Tables
|--------------------------------------------------------------------------
|
| Reservation linked order:
| Keep reservation table occupied.
|
| Normal order:
| Release tables normally.
|
*/

if (
    ! $lockedOrder->reservation_id
) {

    $this->releaseOrderTables(
        $lockedOrder
    );

}



            /*
            |--------------------------------------------------------------------------
            | Return Fresh Order Data
            |--------------------------------------------------------------------------
            */

            return $lockedOrder->fresh([

    'reservation',

    'customer',

    'primaryTable',

    'tables',

    'items.addons',

    'payments.receiver',

    'creator',

]);

        }
    );
}

   /**
 * Complete a served and fully paid order.
 */
public function completeOrder(
    Order $order
): Order {

    return DB::transaction(
        function () use (
            $order
        ): Order {


            /*
            |--------------------------------------------------------------------------
            | Lock Order
            |--------------------------------------------------------------------------
            */

            $lockedOrder = Order::query()
                ->with([
                    'customer',
                    'tables',
                    'payments',
                    'latestKitchenBatch',
                    'reservation',
                ])
                ->lockForUpdate()
                ->findOrFail(
                    $order->id
                );




            /*
            |--------------------------------------------------------------------------
            | Validate Order Status
            |--------------------------------------------------------------------------
            */


            if (
                $lockedOrder->status !==
                Order::STATUS_SERVED
            ) {

                throw ValidationException::withMessages([
                    'order' => [
                        'Only a served order can be completed.',
                    ],
                ]);

            }




            /*
            |--------------------------------------------------------------------------
            | Validate Kitchen Batch
            |--------------------------------------------------------------------------
            */


            $latestBatch =
                $this->lockLatestKitchenBatch(
                    $lockedOrder
                );


            if (
                $latestBatch->status !==
                OrderKitchenBatch::STATUS_SERVED
            ) {

                throw ValidationException::withMessages([
                    'kitchen_batch' => [
                        'The latest kitchen batch must be served before the order can be completed.',
                    ],
                ]);

            }





            /*
            |--------------------------------------------------------------------------
            | Authoritative Payment Ledger Check
            |--------------------------------------------------------------------------
            */


            $lockedPayments = $lockedOrder
                ->payments()
                ->lockForUpdate()
                ->get();



            $recordedPaidAmount = round(
                (float)
                $lockedPayments
                    ->sum('amount'),
                2
            );



            $totalAmount = round(
                (float)
                $lockedOrder->total_amount,
                2
            );



            $dueAmount = round(
                max(
                    0,
                    $totalAmount
                    -
                    $recordedPaidAmount
                ),
                2
            );



            $paymentStatus = match (true) {


                $recordedPaidAmount <= 0 =>
                    Order::PAYMENT_DUE,


                $dueAmount <= 0 =>
                    Order::PAYMENT_PAID,


                default =>
                    Order::PAYMENT_PARTIALLY_PAID,

            };





            /*
            |--------------------------------------------------------------------------
            | Update Payment Summary
            |--------------------------------------------------------------------------
            */


            $lockedOrder->update([

                'paid_amount' =>
                    $recordedPaidAmount,


                'due_amount' =>
                    $dueAmount,


                'payment_status' =>
                    $paymentStatus,

            ]);





            if (
                $dueAmount > 0
            ) {

                throw ValidationException::withMessages([
                    'due_amount' => [
                        'The outstanding due must be paid before this order can be completed.',
                    ],
                ]);

            }





            /*
            |--------------------------------------------------------------------------
            | Complete Order
            |--------------------------------------------------------------------------
            */


            $lockedOrder->update([

                'status' =>
                    Order::STATUS_COMPLETED,


                'completed_at' =>
                    now(),

            ]);


            /*
            |--------------------------------------------------------------------------
            | Automatic Sales Accounting
            |--------------------------------------------------------------------------
            |
            | A sale is posted only after the order is served and fully paid.
            | POS and normal restaurant orders use this same order completion flow.
            |
            */

            $this->salesAccountingService
                ->createSaleJournal(
                    $lockedOrder,
                    auth()->id()
                );







            /*
            |--------------------------------------------------------------------------
            | Complete linked reservation
            |--------------------------------------------------------------------------
            |
            | Only seated reservation will be completed.
            | Already completed reservation will not throw error.
            |
            */


            if (
                $lockedOrder->reservation_id
            ) {


                $reservation =
                    Reservation::query()
                        ->lockForUpdate()
                        ->find(
                            (int)
                            $lockedOrder->reservation_id
                        );



                if (
                    $reservation &&
                    $reservation->status ===
                    Reservation::STATUS_SEATED
                ) {


                    $reservation->forceFill([

                        'status' =>
                            Reservation::STATUS_COMPLETED,

                    ])->saveQuietly();


                }

            }







            /*
            |--------------------------------------------------------------------------
            | Customer Spending
            |--------------------------------------------------------------------------
            */


            if (
                $lockedOrder->customer &&
                ! $lockedOrder
                    ->is_customer_spend_recorded
            ) {


                $customer =
                    Customer::query()
                        ->lockForUpdate()
                        ->find(
                            $lockedOrder->customer_id
                        );



                if (
                    $customer
                ) {


                    $customer->update([

                        'total_spent' =>
                            round(
                                (float)
                                $customer->total_spent
                                +
                                $totalAmount,
                                2
                            ),


                        'last_visit_at' =>
                            now(),

                    ]);




                    $lockedOrder->update([

                        'is_customer_spend_recorded' =>
                            true,


                        'customer_spend_recorded_at' =>
                            now(),

                    ]);

                }

            }








            /*
            |--------------------------------------------------------------------------
            | Release Order Tables
            |--------------------------------------------------------------------------
            */


            $this->releaseOrderTables(
                $lockedOrder
            );







            /*
            |--------------------------------------------------------------------------
            | Return Fresh Order
            |--------------------------------------------------------------------------
            */


            return $lockedOrder->fresh([

                'reservation',

                'customer',

                'primaryTable',

                'tables',

                'items.addons',

                'payments.receiver',

                'recipeConsumptions',

                'latestKitchenBatch',

                'kitchenBatches',

                'creator',

            ]);

        },
        3
    );

}

    /**
     * Resolve the best eligible promotion without stacking it with a manual discount.
     *
     * @return array{discount_amount: float, offer_id: ?int, offer_discount_amount: float}
     */
    private function calculateOrderDiscount(
        float $subtotal,
        float $manualDiscount,
        ?Customer $customer,
        ?int $excludeOrderId = null
    ): array {
        $manualDiscount = round(max(0, $manualDiscount), 2);

        if ($manualDiscount > $subtotal) {
            throw ValidationException::withMessages([
                'discount_amount' => [
                    'The discount cannot be greater than the subtotal.',
                ],
            ]);
        }

        $promotion = $this->offerService->bestForOrder(
            subtotal: $subtotal,
            customer: $customer,
            excludeOrderId: $excludeOrderId
        );

        if (
            $promotion &&
            $promotion['discount_amount'] >= $manualDiscount &&
            $promotion['discount_amount'] > 0
        ) {
            return [
                'discount_amount' => $promotion['discount_amount'],
                'offer_id' => (int) $promotion['offer']->id,
                'offer_discount_amount' => $promotion['discount_amount'],
            ];
        }

        return [
            'discount_amount' => $manualDiscount,
            'offer_id' => null,
            'offer_discount_amount' => 0.0,
        ];
    }

    /**
     * Find an existing customer or create a new one.
     */
    private function resolveCustomer(
        array $data
    ): ?Customer {
        $customerId =
            $data['customer_id']
            ?? null;

        if ($customerId) {
            $customer = Customer::query()
                ->active()
                ->lockForUpdate()
                ->findOrFail(
                    (int) $customerId
                );

            if (!empty($data['date_of_birth'])) {
                $customer->date_of_birth = $data['date_of_birth'];
                $customer->save();
            }

            return $customer;
        }

        $name =
            $this->nullableString(
                $data[
                    'customer_name'
                ] ?? null
            );

        $phone =
            $this->nullableString(
                $data[
                    'customer_phone'
                ] ?? null
            );

        $email =
            $this->nullableString(
                $data[
                    'customer_email'
                ] ?? null
            );

        if (
            !$name &&
            !$phone &&
            !$email
        ) {
            return null;
        }

        $existingCustomer =
            Customer::query()
                ->where(
                    function (
                        Builder $query
                    ) use (
                        $phone,
                        $email
                    ): void {
                        if ($phone) {
                            $query->where(
                                'phone',
                                $phone
                            );
                        }

                        if (
                            $phone &&
                            $email
                        ) {
                            $query->orWhere(
                                'email',
                                $email
                            );
                        } elseif (
                            !$phone &&
                            $email
                        ) {
                            $query->where(
                                'email',
                                $email
                            );
                        }
                    }
                )
                ->lockForUpdate()
                ->first();

        if ($existingCustomer) {
            $existingCustomer->update([
                'name' =>
                    $name
                        ?? $existingCustomer
                            ->name,

                'phone' =>
                    $phone
                        ?? $existingCustomer
                            ->phone,

                'email' =>
                    $email
                        ?? $existingCustomer
                            ->email,

                'date_of_birth' =>
                    $data['date_of_birth']
                        ?? $existingCustomer->date_of_birth,
            ]);

            return $existingCustomer;
        }

        return Customer::query()
            ->create([
                'name' =>
                    $name
                        ?? 'Walk-in Customer',

                'phone' =>
                    $phone,

                'email' =>
                    $email,

                'date_of_birth' =>
                    $data['date_of_birth'] ?? null,

                'last_visit_at' =>
                    null,

                'total_orders' =>
                    0,

                'total_spent' =>
                    0,

                'is_active' =>
                    true,

                'notes' =>
                    null,
            ]);
    }

    /**
     * Load, validate and calculate all order items.
     */
    private function prepareOrderItems(
        array $items
    ): Collection {
        return collect($items)
            ->values()
            ->map(
                function (
                    array $item,
                    int $index
                ): array {
                    $menuItem =
                        MenuItem::query()
                            ->with([
                                'variants' =>
                                    fn (
                                        $query
                                    ) =>
                                        $query
                                            ->available(),

                                'addOns' =>
                                    fn (
                                        $query
                                    ) =>
                                        $query
                                            ->available(),
                            ])
                            ->lockForUpdate()
                            ->findOrFail(
                                (int)
                                    $item[
                                        'menu_item_id'
                                    ]
                            );

                    if (
                        !$menuItem
                            ->is_available
                    ) {
                        throw ValidationException::withMessages([
                            "items.{$index}.menu_item_id" => [
                                "{$menuItem->menu_name} is currently unavailable.",
                            ],
                        ]);
                    }

                    $variant = null;

                    if (
                        !empty(
                            $item[
                                'menu_item_variant_id'
                            ]
                        )
                    ) {
                        $variant =
                            MenuItemVariant::query()
                                ->lockForUpdate()
                                ->where(
                                    'id',
                                    (int)
                                        $item[
                                            'menu_item_variant_id'
                                        ]
                                )
                                ->where(
                                    'menu_item_id',
                                    $menuItem->id
                                )
                                ->where(
                                    'is_available',
                                    true
                                )
                                ->first();

                        if (!$variant) {
                            throw ValidationException::withMessages([
                                "items.{$index}.menu_item_variant_id" => [
                                    'The selected variant does not belong to this menu item or is unavailable.',
                                ],
                            ]);
                        }
                    }

                    /*
                     * Variant price replaces base item price.
                     * If no variant is selected, use menu item price.
                     */
                    $unitPrice = round(
                        (float)
                            (
                                $variant?->price
                                ?? $menuItem->price
                            ),
                        2
                    );

                    $quantity = max(
                        1,
                        (int)
                            (
                                $item[
                                    'quantity'
                                ] ?? 1
                            )
                    );

                    $requestedAddonIds =
                        collect(
                            $item[
                                'addon_ids'
                            ] ?? []
                        )
                            ->map(
                                static fn (
                                    mixed $id
                                ): int =>
                                    (int) $id
                            )
                            ->unique()
                            ->values();

                    $addons =
                        $this->resolveItemAddons(
                            menuItem: $menuItem,
                            requestedAddonIds:
                                $requestedAddonIds,
                            itemIndex: $index,
                            quantity: $quantity
                        );

                    $addonTotalPerUnit =
                        round(
                            (float)
                                $addons->sum(
                                    'unit_price'
                                ),
                            2
                        );

                    $addonTotal = round(
                        $addonTotalPerUnit
                            * $quantity,
                        2
                    );

                    $lineTotal = round(
                        (
                            $unitPrice
                            +
                            $addonTotalPerUnit
                        )
                            * $quantity,
                        2
                    );

                    return [
                        'menu_item_id' =>
                            (int)
                                $menuItem->id,

                        'menu_item_variant_id' =>
                            $variant
                                ? (int)
                                    $variant->id
                                : null,

                        'item_name' =>
                            $menuItem
                                ->menu_name,

                        'variant_name' =>
                            $variant
                                ? $variant
                                    ->variant_name
                                : null,

                        'unit_price' =>
                            $unitPrice,

                        'quantity' =>
                            $quantity,

                        'addon_total' =>
                            $addonTotal,

                        'line_total' =>
                            $lineTotal,

                        'kitchen_note' =>
                            $this->nullableString(
                                $item[
                                    'kitchen_note'
                                ] ?? null
                            ),

                        'addons' =>
                            $addons->all(),
                    ];
                }
            );
    }

    /**
     * Validate add-ons against the selected menu item.
     */
    /**
 * Validate global add-ons.
 *
 * Any available add-on can be used with any menu item.
 */
private function resolveItemAddons(
    MenuItem $menuItem,
    Collection $requestedAddonIds,
    int $itemIndex,
    int $quantity
): Collection {
    if ($requestedAddonIds->isEmpty()) {
        return collect();
    }

    /*
    |--------------------------------------------------------------------------
    | Load all requested global add-ons
    |--------------------------------------------------------------------------
    */

    $availableAddons = AddOn::query()
        ->whereIn(
            'id',
            $requestedAddonIds
        )
        ->where(
            'is_available',
            true
        )
        ->lockForUpdate()
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Validate selected add-ons
    |--------------------------------------------------------------------------
    */

    if (
        $availableAddons->count()
        !==
        $requestedAddonIds->count()
    ) {
        throw ValidationException::withMessages([
            "items.{$itemIndex}.addon_ids" => [
                'One or more selected add-ons do not exist or are unavailable.',
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Prepare add-on price snapshot
    |--------------------------------------------------------------------------
    */

    return $availableAddons
        ->map(
            static function (
                AddOn $addon
            ) use (
                $quantity
            ): array {
                $unitPrice = round(
                    (float) $addon->price,
                    2
                );

                return [
                    'id' =>
                        (int) $addon->id,

                    'addon_name' =>
                        $addon->add_on_name,

                    'unit_price' =>
                        $unitPrice,

                    'total_price' =>
                        round(
                            $unitPrice
                            * $quantity,
                            2
                        ),
                ];
            }
        )
        ->values();
}

    /**
     * Load and validate merged tables.
     */
    private function loadMergedTables(
        Collection $mergedTableIds
    ): Collection {
        if (
            $mergedTableIds->isEmpty()
        ) {
            return collect();
        }

        $mergedTables =
            RestaurantTable::query()
                ->whereIn(
                    'id',
                    $mergedTableIds
                )
                ->lockForUpdate()
                ->get();

        if (
            $mergedTables->count()
            !==
            $mergedTableIds->count()
        ) {
            throw ValidationException::withMessages([
                'merged_table_ids' => [
                    'One or more selected merged tables no longer exist.',
                ],
            ]);
        }

        foreach (
            $mergedTables
            as $mergedTable
        ) {
            $this->ensureTableIsAvailable(
                table: $mergedTable,
                field: 'merged_table_ids'
            );
        }

        return $mergedTables;
    }

    

    /**
     * Ensure a table is currently usable.
     */
    private function ensureTableIsAvailable(
        RestaurantTable $table,
        string $field
    ): void {
        if (
            $table->current_status !==
            RestaurantTable::STATUS_AVAILABLE
        ) {
            throw ValidationException::withMessages([
                $field => [
                    "{$table->table_name} is not currently available.",
                ],
            ]);
        }

        if (
            $table->merged_with_id !==
            null
        ) {
            throw ValidationException::withMessages([
                $field => [
                    "{$table->table_name} already belongs to a merged table group.",
                ],
            ]);
        }

        $controlsAnotherGroup =
            RestaurantTable::query()
                ->where(
                    'merged_with_id',
                    $table->id
                )
                ->lockForUpdate()
                ->exists();

        if ($controlsAnotherGroup) {
            throw ValidationException::withMessages([
                $field => [
                    "{$table->table_name} already controls another merged table group.",
                ],
            ]);
        }

        $hasActiveOrder =
            $table->activeOrders()
                ->lockForUpdate()
                ->exists();

        if ($hasActiveOrder) {
            throw ValidationException::withMessages([
                $field => [
                    "{$table->table_name} already has an active order.",
                ],
            ]);
        }
    }

    /**
     * Validate a primary table, allowing a pre-merged group with no order.
     */
    private function ensurePrimaryTableIsAvailable(
        RestaurantTable $table,
        string $field
    ): void {
        $hasMergedMembers = RestaurantTable::query()
            ->where('merged_with_id', $table->id)
            ->lockForUpdate()
            ->exists();

        if (!$hasMergedMembers) {
            $this->ensureTableIsAvailable($table, $field);

            return;
        }

        if ($table->merged_with_id !== null) {
            throw ValidationException::withMessages([
                $field => [
                    'Select the master table for this merged group.',
                ],
            ]);
        }

        if (!in_array(
            $table->current_status,
            [
                RestaurantTable::STATUS_AVAILABLE,
                RestaurantTable::STATUS_OCCUPIED,
            ],
            true
        )) {
            throw ValidationException::withMessages([
                $field => [
                    "{$table->table_name} is not currently available.",
                ],
            ]);
        }

        if ($table->activeOrders()->lockForUpdate()->exists()) {
            throw ValidationException::withMessages([
                $field => [
                    "{$table->table_name} already has an active order.",
                ],
            ]);
        }
    }

    /**
     * Release and split all tables assigned to an order.
     */
    private function releaseOrderTables(
        Order $order
    ): void {
        $order->loadMissing(
            'tables'
        );

        $tableIds =
            $order->tables
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
            $tableIds->isEmpty() &&
            $order
                ->restaurant_table_id
        ) {
            $tableIds = collect([
                (int)
                    $order
                        ->restaurant_table_id,
            ]);
        }

        if (
            $tableIds->isEmpty()
        ) {
            return;
        }

        RestaurantTable::query()
            ->whereIn(
                'id',
                $tableIds
            )
            ->lockForUpdate()
            ->update([
                'status' =>
                    RestaurantTable::STATUS_AVAILABLE,

                'merged_with_id' =>
                    null,

                'reservation_start_at' =>
                    null,

                'reservation_end_at' =>
                    null,
            ]);
    }

    /**
     * Generate a unique order number.
     */
    private function generateOrderNumber(): string
    {
        do {
            $orderNumber =
                'ORD-'
                .now()->format(
                    'Ymd'
                )
                .'-'
                .strtoupper(
                    Str::random(6)
                );
        } while (
            Order::query()
                ->where(
                    'order_number',
                    $orderNumber
                )
                ->exists()
        );

        return $orderNumber;
    }

    /**
     * Return trimmed string or null.
     */
    private function nullableString(
        mixed $value
    ): ?string {
        if (
            $value === null
        ) {
            return null;
        }

        $value = trim(
            (string) $value
        );

        return $value !== ''
            ? $value
            : null;
    }


        /*
    |--------------------------------------------------------------------------
    | Edit Protection
    |--------------------------------------------------------------------------
    |
    | Order item/add-on snapshots are replaced during updateOrder().
    | Once preparation has started or recipe consumption exists, rebuilding
    | those snapshots would make immutable consumption history inaccurate.
    |
    */
private function ensureOrderCanBeEdited(
    Order $order
): void {
    if ($order->isFinalized()) {
        throw ValidationException::withMessages([
            'order' => [
                'A completed or canceled order cannot be edited.',
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Served = Payment / Extension Mode
    |--------------------------------------------------------------------------
    |
    | Historical recipe consumption is expected on a served order. It must not
    | block payment collection or creation of a new kitchen batch.
    |
    */

    if (
        $order->status ===
        Order::STATUS_SERVED
    ) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Only pristine pending orders use the legacy rebuild editor
    |--------------------------------------------------------------------------
    */

    if (
        $order->status !==
        Order::STATUS_PENDING
    ) {
        throw ValidationException::withMessages([
            'order' => [
                'This order cannot be edited while the kitchen cycle is active. Wait until the current batch is served.',
            ],
        ]);
    }

    $hasRecipeConsumption =
        $order->relationLoaded(
            'recipeConsumptions'
        )
            ? $order
                ->recipeConsumptions
                ->isNotEmpty()
            : $order
                ->recipeConsumptions()
                ->exists();

    if ($hasRecipeConsumption) {
        throw ValidationException::withMessages([
            'order' => [
                'This order has historical kitchen consumption and an active extension cycle. Wait until the current batch is served before editing the order again.',
            ],
        ]);
    }
}


    /*
    |--------------------------------------------------------------------------
    | Served Order Update / Extension
    |--------------------------------------------------------------------------
    */

    private function updateServedOrder(
        Order $order,
        array $data,
        ?int $userId
    ): Order {
        $order->loadMissing([
            'customer',
            'tables',
            'items.addons',
            'payments',
            'recipeConsumptions',
            'latestKitchenBatch',
        ]);

        if (
            isset($data['status']) &&
            $data['status'] !==
            Order::STATUS_SERVED
        ) {
            throw ValidationException::withMessages([
                'status' => [
                    'A served order must stay served unless new extension items are added. The backend creates the pending extension status automatically.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Served-order identity is immutable
        |--------------------------------------------------------------------------
        */

        $this->ensureServedOrderIdentityUnchanged(
            order: $order,
            data: $data
        );

        $payloadItems = collect(
            $data['items'] ?? []
        )->values();

        if ($payloadItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => [
                    'At least one order item is required.',
                ],
            ]);
        }

        $existingItems = $order
            ->items()
            ->with('addons')
            ->lockForUpdate()
            ->orderBy('id')
            ->get();

        $historicalPayloadItems =
            $payloadItems
                ->filter(
                    static fn (array $item): bool =>
                        ! empty(
                            $item['order_item_id']
                            ?? null
                        )
                )
                ->values();

        $newPayloadItems =
            $payloadItems
                ->filter(
                    static fn (array $item): bool =>
                        empty(
                            $item['order_item_id']
                            ?? null
                        )
                )
                ->values();

        $this->ensureHistoricalItemsUnchanged(
            order: $order,
            existingItems: $existingItems,
            payloadItems:
                $historicalPayloadItems
        );

        $preparedNewItems =
            $newPayloadItems->isEmpty()
                ? collect()
                : $this->prepareOrderItems(
                    $newPayloadItems->all()
                );

        /*
        |--------------------------------------------------------------------------
        | Aggregate bill = immutable historical snapshots + new items
        |--------------------------------------------------------------------------
        */

        $historicalSubtotal = round(
            (float) $existingItems
                ->sum('line_total'),
            2
        );

        $newSubtotal = round(
            (float) $preparedNewItems
                ->sum('line_total'),
            2
        );

        $subtotal = round(
            $historicalSubtotal
            + $newSubtotal,
            2
        );

        $discount = $this->calculateOrderDiscount(
            subtotal: $subtotal,
            manualDiscount: (float) (
                $data['discount_amount']
                ?? $order->discount_amount
                ?? 0
            ),
            customer: $order->customer,
            excludeOrderId: (int) $order->id
        );
        $discountAmount = $discount['discount_amount'];
        $offerId = $discount['offer_id'];
        $offerDiscountAmount = $discount['offer_discount_amount'];

        /*
|--------------------------------------------------------------------------
| Tax & Service Charge
|--------------------------------------------------------------------------
*/

$settings =
    $this->systemSettingService->get();

$taxableAmount = round(
    max(
        0,
        $subtotal - $discountAmount
    ),
    2
);

$taxAmount = $this->calculateCharge(
    amount: $taxableAmount,
    type: (string) (
        $settings->vat_type
        ?? 'percentage'
    ),
    value: (float) (
        $settings->vat_rate
        ?? 0
    )
);

$serviceCharge = $this->calculateCharge(
    amount: $taxableAmount,
    type: (string) (
        $settings->service_charge_type
        ?? 'percentage'
    ),
    value: (float) (
        $settings->service_charge_rate
        ?? 0
    )
);

$totalAmount = round(
    max(
        0,
        $taxableAmount
        + $taxAmount
        + $serviceCharge
    ),
    2
);

        /*
        |--------------------------------------------------------------------------
        | Immutable payment ledger
        |--------------------------------------------------------------------------
        */

        $lockedPayments = $order
            ->payments()
            ->lockForUpdate()
            ->get();

        $recordedPaidAmount = round(
            (float) $lockedPayments
                ->sum('amount'),
            2
        );

        $paidAmount = round(
            max(
                0,
                (float) (
                    $data['paid_amount']
                    ?? $recordedPaidAmount
                )
            ),
            2
        );

        if (
            $paidAmount <
            $recordedPaidAmount
        ) {
            throw ValidationException::withMessages([
                'paid_amount' => [
                    'Paid amount cannot be less than the amount already recorded in payment history.',
                ],
            ]);
        }

        if (
            $recordedPaidAmount >
            $totalAmount
        ) {
            throw ValidationException::withMessages([
                'total_amount' => [
                    'The updated order total cannot be less than the amount already paid.',
                ],
            ]);
        }

        if (
            $paidAmount >
            $totalAmount
        ) {
            throw ValidationException::withMessages([
                'paid_amount' => [
                    'Paid amount cannot be greater than the total amount.',
                ],
            ]);
        }

        $newPaymentAmount = round(
            $paidAmount
            - $recordedPaidAmount,
            2
        );

        $paymentMethod =
            $newPaymentAmount > 0
                ? $this->nullableString(
                    $data['payment_method']
                    ?? null
                )
                : $order->payment_method;

        $paymentReference =
            $newPaymentAmount > 0
                ? $this->nullableString(
                    $data['payment_reference']
                    ?? null
                )
                : $order->payment_reference;

        if (
            $newPaymentAmount > 0 &&
            blank($paymentMethod)
        ) {
            throw ValidationException::withMessages([
                'payment_method' => [
                    'Payment method is required when an additional payment is provided.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Create the next batch only when new items exist
        |--------------------------------------------------------------------------
        */

        $newBatch = null;

        if (
            $preparedNewItems
                ->isNotEmpty()
        ) {
            $latestBatch =
                $this->lockLatestKitchenBatch(
                    $order
                );

            if (
                $latestBatch->status !==
                OrderKitchenBatch::STATUS_SERVED
            ) {
                throw ValidationException::withMessages([
                    'kitchen_batch' => [
                        'The previous kitchen batch must be served before this order can be extended again.',
                    ],
                ]);
            }

            $nextBatchNo =
                (int) $latestBatch
                    ->batch_no
                + 1;

            $newBatch = $order
                ->kitchenBatches()
                ->create([
                    'batch_no' =>
                        $nextBatchNo,
                    'status' =>
                        OrderKitchenBatch::
                            STATUS_PENDING,
                    'chef_id' => null,
                    'sent_to_kitchen_at' =>
                        null,
                    'preparing_at' => null,
                    'ready_at' => null,
                    'served_at' => null,
                    'created_by' =>
                        $userId,
                ]);

            foreach (
                $preparedNewItems
                as $preparedItem
            ) {
                $this->createOrderItemInBatch(
                    order: $order,
                    batch: $newBatch,
                    preparedItem:
                        $preparedItem,
                    status:
                        Order::STATUS_PENDING
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Save additional payment exactly once
        |--------------------------------------------------------------------------
        */

        if (
            $newPaymentAmount > 0
        ) {
            $order
                ->payments()
                ->create([
                    'amount' =>
                        $newPaymentAmount,
                    'payment_method' =>
                        $paymentMethod,
                    'reference' =>
                        $paymentReference,
                    'note' =>
                        'Additional payment received during served order update.',
                    'received_by' =>
                        $userId,
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Recalculate payment summary from the ledger after insert
        |--------------------------------------------------------------------------
        */

        $updatedPaidAmount = round(
            (float) $order
                ->payments()
                ->sum('amount'),
            2
        );

        $updatedDueAmount = round(
            max(
                0,
                $totalAmount
                - $updatedPaidAmount
            ),
            2
        );

        $updatedPaymentStatus =
            match (true) {
                $updatedPaidAmount <= 0 =>
                    Order::PAYMENT_DUE,

                $updatedDueAmount <= 0 =>
                    Order::PAYMENT_PAID,

                default =>
                    Order::PAYMENT_PARTIALLY_PAID,
            };

        /*
        |--------------------------------------------------------------------------
        | Parent Order = compatibility mirror of latest kitchen cycle
        |--------------------------------------------------------------------------
        */

        $update = [
            'subtotal' =>
                $subtotal,
            'discount_amount' =>
                $discountAmount,

            'offer_id' =>
                $offerId,

            'offer_discount_amount' =>
                $offerDiscountAmount,
            'tax_amount' =>
                $taxAmount,
            'service_charge' =>
                $serviceCharge,
            'total_amount' =>
                $totalAmount,
            'paid_amount' =>
                $updatedPaidAmount,
            'due_amount' =>
                $updatedDueAmount,
            'payment_status' =>
                $updatedPaymentStatus,
            'payment_method' =>
                $paymentMethod,
            'payment_breakdown' =>
                null,
            'payment_reference' =>
                $paymentReference,
            'order_note' =>
                $this->nullableString(
                    $data['order_note']
                    ?? $order->order_note
                ),
            'created_by' =>
                $order->created_by
                ?? $userId,
        ];

        if ($newBatch) {
            $update = [
                ...$update,

                /*
                 * New kitchen cycle starts now. Parent fields mirror the latest
                 * batch while historical timestamps live on older batch rows.
                 */
                'status' =>
                    Order::STATUS_PENDING,
                'chef_id' =>
                    null,
                'sent_to_kitchen_at' =>
                    null,
                'preparing_at' =>
                    null,
                'ready_at' =>
                    null,
                'served_at' =>
                    null,
            ];
        } else {
            $update['status'] =
                Order::STATUS_SERVED;
        }

        $order->update(
            $update
        );
        return $this->freshOrder(
            $order
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Served-order identity protection
    |--------------------------------------------------------------------------
    */

    private function ensureServedOrderIdentityUnchanged(
        Order $order,
        array $data
    ): void {
        $submittedPrimaryTableId =
            (int) (
                $data[
                    'restaurant_table_id'
                ]
                ?? $order
                    ->restaurant_table_id
            );

        if (
            $submittedPrimaryTableId !==
            (int) $order
                ->restaurant_table_id
        ) {
            throw ValidationException::withMessages([
                'restaurant_table_id' => [
                    'The table cannot be changed after an order has been served.',
                ],
            ]);
        }

        $currentMergedTableIds =
            $order->tables
                ->pluck('id')
                ->map(
                    static fn (
                        mixed $id
                    ): int =>
                        (int) $id
                )
                ->reject(
                    static fn (
                        int $id
                    ): bool =>
                        $id ===
                        (int) $order
                            ->restaurant_table_id
                )
                ->sort()
                ->values();

        $submittedMergedTableIds =
            collect(
                $data[
                    'merged_table_ids'
                ]
                ?? []
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

        if (
            $currentMergedTableIds
                ->all()
            !==
            $submittedMergedTableIds
                ->all()
        ) {
            throw ValidationException::withMessages([
                'merged_table_ids' => [
                    'Merged tables cannot be changed after an order has been served.',
                ],
            ]);
        }

        $submittedCustomerId =
            isset($data['customer_id'])
            &&
            $data['customer_id'] !==
            null
                ? (int)
                    $data['customer_id']
                : null;

        $currentCustomerId =
            $order->customer_id !==
            null
                ? (int)
                    $order->customer_id
                : null;

        if (
            $submittedCustomerId !==
            $currentCustomerId
        ) {
            throw ValidationException::withMessages([
                'customer_id' => [
                    'The customer cannot be changed after an order has been served.',
                ],
            ]);
        }

        /*
         * For walk-in orders without customer_id, protect the stored customer
         * snapshot as well.
         */
        if (
            $currentCustomerId ===
            null
        ) {
            $snapshotFields = [
                'customer_name',
                'customer_phone',
                'customer_email',
            ];

            foreach (
                $snapshotFields
                as $field
            ) {
                $submitted =
                    $this->nullableString(
                        $data[$field]
                        ?? null
                    );

                $current =
                    $this->nullableString(
                        $order->{$field}
                    );

                if (
                    $submitted !==
                    $current
                ) {
                    throw ValidationException::withMessages([
                        $field => [
                            'Customer details cannot be changed after an order has been served.',
                        ],
                    ]);
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Historical item immutability
    |--------------------------------------------------------------------------
    */

    private function ensureHistoricalItemsUnchanged(
        Order $order,
        Collection $existingItems,
        Collection $payloadItems
    ): void {
        $submittedIds =
            $payloadItems
                ->pluck(
                    'order_item_id'
                )
                ->map(
                    static fn (
                        mixed $id
                    ): int =>
                        (int) $id
                )
                ->values();

        if (
            $submittedIds->count()
            !==
            $submittedIds
                ->unique()
                ->count()
        ) {
            throw ValidationException::withMessages([
                'items' => [
                    'Each historical order item must be submitted exactly once.',
                ],
            ]);
        }

        $existingIds =
            $existingItems
                ->pluck('id')
                ->map(
                    static fn (
                        mixed $id
                    ): int =>
                        (int) $id
                )
                ->sort()
                ->values();

        if (
            $submittedIds
                ->sort()
                ->values()
                ->all()
            !==
            $existingIds
                ->all()
        ) {
            throw ValidationException::withMessages([
                'items' => [
                    'All historical served items must remain on the order exactly once.',
                ],
            ]);
        }

        $existingById =
            $existingItems
                ->keyBy(
                    static fn (
                        OrderItem $item
                    ): int =>
                        (int) $item->id
                );

        foreach (
            $payloadItems
            as $index =>
                $payloadItem
        ) {
            $itemId =
                (int)
                    $payloadItem[
                        'order_item_id'
                    ];

            /** @var OrderItem|null $existing */
            $existing =
                $existingById
                    ->get(
                        $itemId
                    );

            if (
                ! $existing ||
                (int) $existing
                    ->order_id !==
                (int) $order->id
            ) {
                throw ValidationException::withMessages([
                    "items.{$index}.order_item_id" => [
                        'The historical order item does not belong to this order.',
                    ],
                ]);
            }

            $submittedVariantId =
                ! empty(
                    $payloadItem[
                        'menu_item_variant_id'
                    ]
                    ?? null
                )
                    ? (int)
                        $payloadItem[
                            'menu_item_variant_id'
                        ]
                    : null;

            $existingVariantId =
                $existing
                    ->menu_item_variant_id !==
                    null
                    ? (int)
                        $existing
                            ->menu_item_variant_id
                    : null;

            $submittedNote =
                $this->nullableString(
                    $payloadItem[
                        'kitchen_note'
                    ]
                    ?? null
                );

            $existingNote =
                $this->nullableString(
                    $existing
                        ->kitchen_note
                );

            $submittedAddonIds =
                collect(
                    $payloadItem[
                        'addon_ids'
                    ]
                    ?? []
                )
                    ->map(
                        static fn (
                            mixed $id
                        ): int =>
                            (int) $id
                    )
                    ->unique()
                    ->sort()
                    ->values()
                    ->all();

            $existingAddonIds =
                $existing
                    ->addons
                    ->pluck(
                        'menu_addon_id'
                    )
                    ->map(
                        static fn (
                            mixed $id
                        ): int =>
                            (int) $id
                    )
                    ->unique()
                    ->sort()
                    ->values()
                    ->all();

            $unchanged =
                (int)
                    $payloadItem[
                        'menu_item_id'
                    ]
                ===
                (int) $existing
                    ->menu_item_id

                &&
                $submittedVariantId ===
                $existingVariantId

                &&
                (int)
                    $payloadItem[
                        'quantity'
                    ]
                ===
                (int) $existing
                    ->quantity

                &&
                $submittedNote ===
                $existingNote

                &&
                $submittedAddonIds ===
                $existingAddonIds;

            if (!$unchanged) {
                throw ValidationException::withMessages([
                    "items.{$index}" => [
                        'Served historical items are immutable. Add a new item instead of changing an existing served item.',
                    ],
                ]);
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Create one immutable item snapshot inside a kitchen batch
    |--------------------------------------------------------------------------
    */

    private function createOrderItemInBatch(
        Order $order,
        OrderKitchenBatch $batch,
        array $preparedItem,
        string $status
    ): OrderItem {
        $orderItem = $order
            ->items()
            ->create([
                'order_kitchen_batch_id' =>
                    $batch->id,
                'menu_item_id' =>
                    $preparedItem[
                        'menu_item_id'
                    ],
                'menu_item_variant_id' =>
                    $preparedItem[
                        'menu_item_variant_id'
                    ],
                'item_name' =>
                    $preparedItem[
                        'item_name'
                    ],
                'variant_name' =>
                    $preparedItem[
                        'variant_name'
                    ],
                'unit_price' =>
                    $preparedItem[
                        'unit_price'
                    ],
                'quantity' =>
                    $preparedItem[
                        'quantity'
                    ],
                'addon_total' =>
                    $preparedItem[
                        'addon_total'
                    ],
                'line_total' =>
                    $preparedItem[
                        'line_total'
                    ],
                'status' =>
                    $status,
                'kitchen_note' =>
                    $preparedItem[
                        'kitchen_note'
                    ],
            ]);

        foreach (
            $preparedItem['addons']
            as $addon
        ) {
            $orderItem
                ->addons()
                ->create([
                    'menu_addon_id' =>
                        $addon['id'],
                    'addon_name' =>
                        $addon[
                            'addon_name'
                        ],
                    'unit_price' =>
                        $addon[
                            'unit_price'
                        ],
                    'quantity' =>
                        $preparedItem[
                            'quantity'
                        ],
                    'total_price' =>
                        $addon[
                            'total_price'
                        ],
                ]);
        }

        return $orderItem;
    }


    /*
    |--------------------------------------------------------------------------
    | Lock latest kitchen batch
    |--------------------------------------------------------------------------
    */

    private function lockLatestKitchenBatch(
        Order $order
    ): OrderKitchenBatch {
        $batch =
            OrderKitchenBatch::query()
                ->where(
                    'order_id',
                    $order->id
                )
                ->orderByDesc(
                    'batch_no'
                )
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

        if (!$batch) {
            throw ValidationException::withMessages([
                'kitchen_batch' => [
                    'No kitchen batch exists for this order. Run the kitchen batch migration/backfill before continuing.',
                ],
            ]);
        }

        return $batch;
    }


    /*
    |--------------------------------------------------------------------------
    | Fresh order with lifecycle relations
    |--------------------------------------------------------------------------
    */

    private function freshOrder(
    Order $order
): Order {
    return $order->fresh([
        'customer',
        'reservation',
        'primaryTable',
        'tables',
        'items.addons',
        'payments.receiver',
        'recipeConsumptions',
        'latestKitchenBatch',
        'kitchenBatches',
        'creator',
    ]);
    
}



/**
     * Build timestamp values when an order status changes.
     */
    private function buildOrderStatusTimestamps(
        ?Order $order,
        string $status
    ): array {
        $timestamps = [
            'preparing_at' =>
                $order?->preparing_at,

            'ready_at' =>
                $order?->ready_at,

            'served_at' =>
                $order?->served_at,
        ];

        if (
            $status ===
            Order::STATUS_PREPARING &&
            !$timestamps['preparing_at']
        ) {
            $timestamps['preparing_at'] = now();
        }

        if (
            $status ===
            Order::STATUS_READY
        ) {
            if (!$timestamps['preparing_at']) {
                $timestamps['preparing_at'] = now();
            }

            if (!$timestamps['ready_at']) {
                $timestamps['ready_at'] = now();
            }
        }

        if (
            $status ===
            Order::STATUS_SERVED
        ) {
            if (!$timestamps['preparing_at']) {
                $timestamps['preparing_at'] = now();
            }

            if (!$timestamps['ready_at']) {
                $timestamps['ready_at'] = now();
            }

            if (!$timestamps['served_at']) {
                $timestamps['served_at'] = now();
            }
        }

        return $timestamps;
    }

    /**
 * Validate whether a table can be used while updating an order.
 *
 * Tables already assigned to the current order are allowed to remain
 * occupied. Newly selected tables must pass the normal availability check.
 */
private function ensureTableCanBeUsedForUpdate(
    RestaurantTable $table,
    string $field,
    Order $order,
    \Illuminate\Support\Collection $currentTableIds
): void {
    /*
    |--------------------------------------------------------------------------
    | Allow tables already assigned to this order
    |--------------------------------------------------------------------------
    |
    | Existing tables are normally marked as occupied. Since they belong to
    | this same order, they are still valid during an update.
    |
    */

    if (
        $currentTableIds->contains(
            (int) $table->id
        )
    ) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Validate newly selected table
    |--------------------------------------------------------------------------
    */

    $this->ensureTableIsAvailable(
        table: $table,
        field: $field
    );
}
private function calculateCharge(
    float $amount,
    string $type,
    float $value
): float {
    $amount = max(0, $amount);
    $value = max(0, $value);

    if ($value <= 0) {
        return 0.00;
    }

    if ($type === 'percentage') {
        return round(
            $amount * ($value / 100),
            2
        );
    }

    return round(
        $value,
        2
    );
}
}
