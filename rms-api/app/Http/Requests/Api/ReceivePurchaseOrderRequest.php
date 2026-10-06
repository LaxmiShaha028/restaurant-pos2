<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;


class ReceivePurchaseOrderRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

    public function authorize(): bool
    {
        return $this->user()?->hasAnyPermission([
            'inventory.manage',
            'suppliers.manage',
        ]) ?? false;
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Input
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | PurchaseOrderController passes ONLY $request->validated() to
    | PurchaseReceiveService.
    |
    | Therefore supplier_batch_no / manufactured_at / expiry_date MUST be
    | declared in rules(), otherwise Laravel removes them from the validated
    | payload even though the browser sent them correctly.
    |
    */

    protected function prepareForValidation(): void
    {
        $data = [];


        /*
        |--------------------------------------------------------------------------
        | General Receive Notes
        |--------------------------------------------------------------------------
        */

        if ($this->exists('notes')) {
            $notes =
                trim(
                    (string) (
                        $this->input('notes')
                        ??
                        ''
                    )
                );


            $data['notes'] =
                $notes !== ''
                    ? $notes
                    : null;
        }


        /*
        |--------------------------------------------------------------------------
        | Receive Items
        |--------------------------------------------------------------------------
        */

        $items =
            $this->input('items');


        if (is_array($items)) {

            $data['items'] =
                collect($items)
                    ->map(
                        static function (
                            mixed $item
                        ): mixed {

                            if (!is_array($item)) {
                                return $item;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Supplier Batch
                            |--------------------------------------------------------------------------
                            */

                            if (
                                array_key_exists(
                                    'supplier_batch_no',
                                    $item
                                )
                            ) {

                                $batch =
                                    trim(
                                        (string) (
                                            $item['supplier_batch_no']
                                            ??
                                            ''
                                        )
                                    );


                                $item['supplier_batch_no'] =
                                    $batch !== ''
                                        ? $batch
                                        : null;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Manufacturing Date
                            |--------------------------------------------------------------------------
                            */

                            if (
                                array_key_exists(
                                    'manufactured_at',
                                    $item
                                )
                            ) {

                                $manufacturedAt =
                                    trim(
                                        (string) (
                                            $item['manufactured_at']
                                            ??
                                            ''
                                        )
                                    );


                                $item['manufactured_at'] =
                                    $manufacturedAt !== ''
                                        ? $manufacturedAt
                                        : null;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Expiry Date
                            |--------------------------------------------------------------------------
                            */

                            if (
                                array_key_exists(
                                    'expiry_date',
                                    $item
                                )
                            ) {

                                $expiryDate =
                                    trim(
                                        (string) (
                                            $item['expiry_date']
                                            ??
                                            ''
                                        )
                                    );


                                $item['expiry_date'] =
                                    $expiryDate !== ''
                                        ? $expiryDate
                                        : null;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Item Notes
                            |--------------------------------------------------------------------------
                            */

                            if (
                                array_key_exists(
                                    'notes',
                                    $item
                                )
                            ) {

                                $notes =
                                    trim(
                                        (string) (
                                            $item['notes']
                                            ??
                                            ''
                                        )
                                    );


                                $item['notes'] =
                                    $notes !== ''
                                        ? $notes
                                        : null;
                            }


                            return $item;
                        }
                    )
                    ->values()
                    ->all();
        }


        /*
        |--------------------------------------------------------------------------
        | Optional Supplier Payment
        |--------------------------------------------------------------------------
        */

        if ($this->exists('payment')) {

            $payment =
                $this->input('payment');


            if (is_array($payment)) {

                if (
                    array_key_exists(
                        'payment_method',
                        $payment
                    )
                ) {

                    $method =
                        strtolower(
                            trim(
                                (string) (
                                    $payment['payment_method']
                                    ??
                                    ''
                                )
                            )
                        );


                    $payment['payment_method'] =
                        $method !== ''
                            ? $method
                            : null;
                }


                if (
                    array_key_exists(
                        'payment_date',
                        $payment
                    )
                ) {

                    $date =
                        trim(
                            (string) (
                                $payment['payment_date']
                                ??
                                ''
                            )
                        );


                    $payment['payment_date'] =
                        $date !== ''
                            ? $date
                            : null;
                }


                if (
                    array_key_exists(
                        'transaction_reference',
                        $payment
                    )
                ) {

                    $reference =
                        trim(
                            (string) (
                                $payment['transaction_reference']
                                ??
                                ''
                            )
                        );


                    $payment['transaction_reference'] =
                        $reference !== ''
                            ? $reference
                            : null;
                }


                if (
                    array_key_exists(
                        'notes',
                        $payment
                    )
                ) {

                    $paymentNotes =
                        trim(
                            (string) (
                                $payment['notes']
                                ??
                                ''
                            )
                        );


                    $payment['notes'] =
                        $paymentNotes !== ''
                            ? $paymentNotes
                            : null;
                }


                $data['payment'] =
                    $payment;
            } elseif ($payment === null) {

                $data['payment'] =
                    null;
            }
        }


        if ($data !== []) {
            $this->merge(
                $data
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */

    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Receive Notes
            |--------------------------------------------------------------------------
            */

            'notes' => [
                'bail',
                'nullable',
                'string',
                'max:2000',
            ],


            /*
            |--------------------------------------------------------------------------
            | Receive Items
            |--------------------------------------------------------------------------
            */

            'items' => [
                'bail',
                'required',
                'array',
                'min:1',
                'max:200',
            ],


            /*
            |--------------------------------------------------------------------------
            | Purchase Order Item
            |--------------------------------------------------------------------------
            */

            'items.*.purchase_order_item_id' => [
                'bail',
                'required',
                'integer',
                'distinct',
                'min:1',
            ],


            /*
            |--------------------------------------------------------------------------
            | Receive Quantity
            |--------------------------------------------------------------------------
            */

            'items.*.receive_quantity' => [
                'bail',
                'required',
                'numeric',
                'decimal:0,4',
                'gt:0',
                'max:9999999999.9999',
            ],


            /*
            |--------------------------------------------------------------------------
            | Unit Cost
            |--------------------------------------------------------------------------
            |
            | PurchaseReceiveService also validates the exact business rules.
            |
            */

            'items.*.unit_cost' => [
                'bail',
                'nullable',
                'numeric',
                'decimal:0,4',
                'min:0',
                'max:9999999999.9999',
            ],


            /*
            |--------------------------------------------------------------------------
            | FINAL LOT / BATCH FIELDS
            |--------------------------------------------------------------------------
            |
            | These three rules are the critical fix.
            |
            | Without these declarations Laravel excludes the fields from
            | $request->validated(), so PurchaseReceiveService receives NULL and
            | the new warehouse lot becomes "Unknown Expiry".
            |
            */

            'items.*.supplier_batch_no' => [
                'bail',
                'nullable',
                'string',
                'max:120',
            ],

            'items.*.manufactured_at' => [
                'bail',
                'nullable',
                'date_format:Y-m-d',
            ],

            'items.*.expiry_date' => [
                'bail',
                'nullable',
                'date_format:Y-m-d',
            ],


            /*
            |--------------------------------------------------------------------------
            | Item Notes
            |--------------------------------------------------------------------------
            */

            'items.*.notes' => [
                'bail',
                'nullable',
                'string',
                'max:1000',
            ],


            /*
            |--------------------------------------------------------------------------
            | Optional Payment
            |--------------------------------------------------------------------------
            |
            | PurchaseOrderPaymentService remains the financial source of truth
            | and performs the final amount / due / method validation.
            |
            */

            'payment' => [
                'bail',
                'nullable',
                'array',
            ],

            'payment.amount' => [
                'bail',
                'required_with:payment',
                'numeric',
                'decimal:0,2',
                'gt:0',
                'max:999999999999.99',
            ],

            'payment.payment_method' => [
                'bail',
                'required_with:payment',
                'string',
                'max:80',
            ],

            'payment.payment_date' => [
                'bail',
                'nullable',
                'date',
            ],

            'payment.transaction_reference' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],

            'payment.notes' => [
                'bail',
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Validation Messages
    |--------------------------------------------------------------------------
    */

    public function messages(): array
    {
        return [
            'items.required' =>
                'Select at least one purchase item to receive.',

            'items.array' =>
                'Receive items must be provided as a valid list.',

            'items.min' =>
                'Select at least one purchase item to receive.',

            'items.*.purchase_order_item_id.required' =>
                'Purchase order item ID is required.',

            'items.*.purchase_order_item_id.distinct' =>
                'Duplicate purchase order items are not allowed in one receive request.',

            'items.*.receive_quantity.required' =>
                'Receive quantity is required.',

            'items.*.receive_quantity.gt' =>
                'Receive quantity must be greater than zero.',

            'items.*.unit_cost.min' =>
                'Unit cost cannot be negative.',

            'items.*.supplier_batch_no.max' =>
                'Supplier batch number cannot exceed 120 characters.',

            'items.*.manufactured_at.date_format' =>
                'Manufacturing date must use YYYY-MM-DD format.',

            'items.*.expiry_date.date_format' =>
                'Expiry date must use YYYY-MM-DD format.',

            'items.*.notes.max' =>
                'Item note cannot exceed 1000 characters.',

            'notes.max' =>
                'Receive notes cannot exceed 2000 characters.',

            'payment.amount.required_with' =>
                'Payment amount is required when payment details are submitted.',

            'payment.amount.gt' =>
                'Payment amount must be greater than zero.',

            'payment.payment_method.required_with' =>
                'Payment method is required when payment details are submitted.',

            'payment.transaction_reference.max' =>
                'Transaction reference cannot exceed 255 characters.',

            'payment.notes.max' =>
                'Payment note cannot exceed 2000 characters.',
        ];
    }
}
