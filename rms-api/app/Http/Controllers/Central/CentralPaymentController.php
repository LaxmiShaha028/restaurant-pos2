<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Payment;
use App\Models\Central\Restaurant;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CentralPaymentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Transactions
    |--------------------------------------------------------------------------
    |
    | Read-only Super Admin payment directory.
    |
    | No payment/subscription state is changed here.
    |
    */

    public function transactions(
        Request $request
    ): JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | Load Recent Central Payments
        |--------------------------------------------------------------------------
        |
        | For the current admin UI we return the most recent 2,000 payment
        | records. Search/filtering is handled in the Vue page.
        |
        */

        $payments =
            Payment::on('central')
                ->latest('id')
                ->limit(2000)
                ->get([
                    'id',
                    'restaurant_id',
                    'subscription_id',
                    'gateway',
                    'transaction_id',
                    'reference',
                    'amount',
                    'currency',
                    'payment_method',
                    'status',
                    'paid_at',
                    'created_at',
                    'gateway_response',
                ]);


        /*
        |--------------------------------------------------------------------------
        | Resolve Restaurants
        |--------------------------------------------------------------------------
        */

        $restaurantIds =
            $payments
                ->pluck(
                    'restaurant_id'
                )
                ->filter()
                ->unique()
                ->values();


        $restaurants =
            $restaurantIds->isEmpty()
                ? collect()
                : Restaurant::on('central')
                    ->whereIn(
                        'id',
                        $restaurantIds
                    )
                    ->get([
                        'id',
                        'name',
                        'owner_name',
                        'owner_email',
                        'status',
                        'tenant_key',
                    ])
                    ->keyBy(
                        'id'
                    );


        /*
        |--------------------------------------------------------------------------
        | Response Rows
        |--------------------------------------------------------------------------
        */

        $rows =
            $payments
                ->map(
                    function (
                        Payment $payment
                    ) use (
                        $restaurants
                    ): array {

                        $meta =
                            $this->paymentMeta(
                                $payment->gateway_response
                            );


                        $restaurant =
                            $restaurants->get(
                                $payment->restaurant_id
                            );


                        return [
                            'id' =>
                                $payment->id,

                            'restaurant_id' =>
                                $payment->restaurant_id,

                            'subscription_id' =>
                                $payment->subscription_id,

                            'gateway' =>
                                $payment->gateway,

                            'transaction_id' =>
                                $payment->transaction_id,

                            'reference' =>
                                $payment->reference,

                            'invoice_url' =>
                                $meta['stripe_invoice_url']
                                ?? $meta['stripe_invoice_pdf']
                                ?? null,

                            'receipt_url' =>
                                $meta['stripe_receipt_url']
                                ?? null,

                            'amount' =>
                                $payment->amount,

                            'currency' =>
                                $payment->currency
                                ?: 'BDT',

                            'payment_method' =>
                                $payment->payment_method,

                            'payment_method_name' =>
                                $meta['payment_method_name']
                                ??
                                $payment->payment_method,

                            'payment_type' =>
                                $meta['payment_type']
                                ??
                                'other',

                            'notes' =>
                                $meta['notes']
                                ??
                                null,

                            'status' =>
                                $payment->status,

                            'paid_at' =>
                                $payment->paid_at,

                            'created_at' =>
                                $payment->created_at,

                            'restaurant' =>
                                $restaurant
                                    ? [
                                        'id' =>
                                            $restaurant->id,

                                        'name' =>
                                            $restaurant->name,

                                        'owner_name' =>
                                            $restaurant->owner_name,

                                        'owner_email' =>
                                            $restaurant->owner_email,

                                        'status' =>
                                            $restaurant->status,

                                        'tenant_key' =>
                                            $restaurant->tenant_key,
                                    ]
                                    : null,
                        ];
                    }
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Status Counts
        |--------------------------------------------------------------------------
        */

        $statusCounts = [
            'all' =>
                $rows->count(),

            'paid' =>
                $rows
                    ->where(
                        'status',
                        'paid'
                    )
                    ->count(),

            'pending' =>
                $rows
                    ->where(
                        'status',
                        'pending'
                    )
                    ->count(),

            'failed' =>
                $rows
                    ->where(
                        'status',
                        'failed'
                    )
                    ->count(),
        ];


        return response()->json([
            'success' =>
                true,

            'message' =>
                'Central payment transactions retrieved successfully.',

            'data' =>
                $rows,

            'meta' => [
                'count' =>
                    $rows->count(),

                'limit' =>
                    2000,

                'status_counts' =>
                    $statusCounts,
            ],

            'errors' =>
                null,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Revenue
    |--------------------------------------------------------------------------
    |
    | Revenue uses PAID payment records only.
    |
    | IMPORTANT:
    | Different currencies are never added together.
    |
    */

    public function revenue(
        Request $request
    ): JsonResponse {

        $payments =
            Payment::on('central')
                ->where(
                    'status',
                    'paid'
                )
                ->orderBy(
                    'paid_at'
                )
                ->get([
                    'id',
                    'restaurant_id',
                    'subscription_id',
                    'amount',
                    'currency',
                    'payment_method',
                    'paid_at',
                    'created_at',
                    'gateway_response',
                ]);


        $now =
            now();

        $todayStart =
            $now
                ->copy()
                ->startOfDay();

        $monthStart =
            $now
                ->copy()
                ->startOfMonth();

        $trendStart =
            $now
                ->copy()
                ->startOfMonth()
                ->subMonths(11);


        /*
        |--------------------------------------------------------------------------
        | Aggregation Containers
        |--------------------------------------------------------------------------
        */

        $currencySummary =
            [];

        $typeBreakdown =
            [];

        $methodBreakdown =
            [];

        $monthlyTotals =
            [];


        foreach (
            $payments as $payment
        ) {

            $currency =
                strtoupper(
                    trim(
                        (string) (
                            $payment->currency
                            ?: 'BDT'
                        )
                    )
                );


            $amount =
                (float)
                    $payment->amount;


            $paidAt =
                $this->paymentDate(
                    $payment->paid_at,
                    $payment->created_at
                );


            $meta =
                $this->paymentMeta(
                    $payment->gateway_response
                );


            $paymentType =
                (string) (
                    $meta['payment_type']
                    ??
                    'other'
                );


            $paymentMethod =
                (string) (
                    $meta['payment_method_name']
                    ??
                    $payment->payment_method
                    ??
                    'Unknown'
                );


            /*
            |--------------------------------------------------------------------------
            | Currency Summary
            |--------------------------------------------------------------------------
            */

            if (
                ! isset(
                    $currencySummary[$currency]
                )
            ) {

                $currencySummary[$currency] = [
                    'currency' =>
                        $currency,

                    'total_revenue' =>
                        0.0,

                    'total_transactions' =>
                        0,

                    'month_revenue' =>
                        0.0,

                    'month_transactions' =>
                        0,

                    'today_revenue' =>
                        0.0,

                    'today_transactions' =>
                        0,
                ];
            }


            $currencySummary[$currency]['total_revenue'] +=
                $amount;

            $currencySummary[$currency]['total_transactions']++;


            if (
                $paidAt->greaterThanOrEqualTo(
                    $monthStart
                )
            ) {

                $currencySummary[$currency]['month_revenue'] +=
                    $amount;

                $currencySummary[$currency]['month_transactions']++;
            }


            if (
                $paidAt->greaterThanOrEqualTo(
                    $todayStart
                )
            ) {

                $currencySummary[$currency]['today_revenue'] +=
                    $amount;

                $currencySummary[$currency]['today_transactions']++;
            }


            /*
            |--------------------------------------------------------------------------
            | Payment Type Breakdown
            |--------------------------------------------------------------------------
            */

            $typeKey =
                $currency .
                '|' .
                $paymentType;


            if (
                ! isset(
                    $typeBreakdown[$typeKey]
                )
            ) {

                $typeBreakdown[$typeKey] = [
                    'currency' =>
                        $currency,

                    'payment_type' =>
                        $paymentType,

                    'amount' =>
                        0.0,

                    'transactions' =>
                        0,
                ];
            }


            $typeBreakdown[$typeKey]['amount'] +=
                $amount;

            $typeBreakdown[$typeKey]['transactions']++;


            /*
            |--------------------------------------------------------------------------
            | Payment Method Breakdown
            |--------------------------------------------------------------------------
            */

            $methodKey =
                $currency .
                '|' .
                $paymentMethod;


            if (
                ! isset(
                    $methodBreakdown[$methodKey]
                )
            ) {

                $methodBreakdown[$methodKey] = [
                    'currency' =>
                        $currency,

                    'payment_method' =>
                        $paymentMethod,

                    'amount' =>
                        0.0,

                    'transactions' =>
                        0,
                ];
            }


            $methodBreakdown[$methodKey]['amount'] +=
                $amount;

            $methodBreakdown[$methodKey]['transactions']++;


            /*
            |--------------------------------------------------------------------------
            | 12 Month Trend
            |--------------------------------------------------------------------------
            */

            if (
                $paidAt->greaterThanOrEqualTo(
                    $trendStart
                )
            ) {

                $monthKey =
                    $paidAt->format(
                        'Y-m'
                    );


                $monthlyKey =
                    $currency .
                    '|' .
                    $monthKey;


                if (
                    ! isset(
                        $monthlyTotals[$monthlyKey]
                    )
                ) {

                    $monthlyTotals[$monthlyKey] = [
                        'currency' =>
                            $currency,

                        'month_key' =>
                            $monthKey,

                        'amount' =>
                            0.0,

                        'transactions' =>
                            0,
                    ];
                }


                $monthlyTotals[$monthlyKey]['amount'] +=
                    $amount;

                $monthlyTotals[$monthlyKey]['transactions']++;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Fill Missing Months With Zero
        |--------------------------------------------------------------------------
        */

        $monthlyTrend =
            [];


        foreach (
            array_keys(
                $currencySummary
            ) as $currency
        ) {

            for (
                $index = 0;
                $index < 12;
                $index++
            ) {

                $month =
                    $trendStart
                        ->copy()
                        ->addMonths(
                            $index
                        );


                $monthKey =
                    $month->format(
                        'Y-m'
                    );


                $monthlyKey =
                    $currency .
                    '|' .
                    $monthKey;


                $existing =
                    $monthlyTotals[$monthlyKey]
                    ??
                    null;


                $monthlyTrend[] = [
                    'currency' =>
                        $currency,

                    'month_key' =>
                        $monthKey,

                    'month_label' =>
                        $month->format(
                            'M Y'
                        ),

                    'amount' =>
                        (float) (
                            $existing['amount']
                            ??
                            0
                        ),

                    'transactions' =>
                        (int) (
                            $existing['transactions']
                            ??
                            0
                        ),
                ];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Sort Currency Summary By Revenue
        |--------------------------------------------------------------------------
        */

        $summaryRows =
            collect(
                array_values(
                    $currencySummary
                )
            )
                ->sortByDesc(
                    'total_revenue'
                )
                ->values();


        return response()->json([
            'success' =>
                true,

            'message' =>
                'Central payment revenue retrieved successfully.',

            'data' => [
                'total_paid_transactions' =>
                    $payments->count(),

                'currencies' =>
                    $summaryRows,

                'monthly_trend' =>
                    array_values(
                        $monthlyTrend
                    ),

                'payment_types' =>
                    collect(
                        array_values(
                            $typeBreakdown
                        )
                    )
                        ->sortByDesc(
                            'amount'
                        )
                        ->values(),

                'payment_methods' =>
                    collect(
                        array_values(
                            $methodBreakdown
                        )
                    )
                        ->sortByDesc(
                            'amount'
                        )
                        ->values(),
            ],

            'errors' =>
                null,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Payment Meta Helper
    |--------------------------------------------------------------------------
    */

    private function paymentMeta(
        mixed $value
    ): array {

        if (
            is_array(
                $value
            )
        ) {
            return $value;
        }


        if (
            is_string(
                $value
            ) &&
            trim(
                $value
            ) !== ''
        ) {

            $decoded =
                json_decode(
                    $value,
                    true
                );


            return is_array(
                $decoded
            )
                ? $decoded
                : [];
        }


        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | Payment Date Helper
    |--------------------------------------------------------------------------
    */

    private function paymentDate(
        mixed $paidAt,
        mixed $createdAt
    ): Carbon {

        $value =
            $paidAt
            ??
            $createdAt
            ??
            now();


        return $value instanceof Carbon
            ? $value->copy()
            : Carbon::parse(
                $value
            );
    }
}
