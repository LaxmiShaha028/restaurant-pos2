<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>

    @page {

        margin: 6px 8px 8px 8px;

    }


    * {

        box-sizing: border-box;

    }


    html,
    body {

        height: auto !important;

        margin: 0;

        padding: 8px;

    }


    body {

        font-family: "Noto Sans", "DejaVu Sans", sans-serif;

        font-size: 10px;

        color: #111827;

        background: #ffffff;

        line-height: 1.35;

    }


    .receipt {

        width: 100%;

        height: auto !important;

        min-height: 0 !important;

    }


    .center {

        text-align: center;

    }


    .muted {

        color: #6b7280;

    }


    .bold {

        font-weight: 700;

    }


    .divider {

        border-top: 1px dashed #9ca3af;

        margin: 5px 0;

    }


    /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    */


    .logo-wrap {

        margin-bottom: 4px;

    }


    .logo-wrap img {

        max-width: 65px;

        max-height: 65px;

        object-fit: contain;

    }



    /*
    |--------------------------------------------------------------------------
    | Restaurant Name
    |--------------------------------------------------------------------------
    */


    .shop-name {

        font-size: 14px;

        font-weight: 700;

        margin: 1px 0;

    }


    .subtitle {

        font-size: 9px;

        color: #374151;

        margin: 1px 0;

    }


    .meta-line {

        font-size: 8.5px;

        margin: 1px 0;

        color: #4b5563;

    }



    /*
    |--------------------------------------------------------------------------
    | Invoice Title
    |--------------------------------------------------------------------------
    */


    .invoice-title {

        font-size: 12px;

        font-weight: 700;

        margin-top: 4px;

        margin-bottom: 3px;

        text-transform: uppercase;

    }



    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    */


    .info-table,
    .items-table,
    .totals-table {

        width: 100%;

        border-collapse: collapse;

        page-break-inside: avoid;

    }


    .info-table td {

        padding: 1px 0;

        vertical-align: top;

    }


    .info-label {

        width: 38%;

        color: #6b7280;

    }


    .info-value {

        width: 62%;

        text-align: right;

        font-weight: 700;

        word-break: break-word;

    }



    /*
    |--------------------------------------------------------------------------
    | Section
    |--------------------------------------------------------------------------
    */


    .section-title {

        margin: 5px 0 3px;

        font-size: 9px;

        font-weight: 700;

        text-transform: uppercase;

    }



    /*
    |--------------------------------------------------------------------------
    | Items
    |--------------------------------------------------------------------------
    */


    .items-table th {

        border-top: 1px solid #111827;

        border-bottom: 1px solid #111827;

        padding: 3px 2px;

        font-size: 8px;

        text-transform: uppercase;

    }


    .items-table td {

        padding: 3px 2px;

        vertical-align: top;

        border-bottom: 1px dashed #d1d5db;

    }


    .text-left {

        text-align: left;

    }


    .text-center {

        text-align: center;

    }


    .text-right {

        text-align: right;

    }


    .item-name {

        font-weight: 700;

        font-size: 9px;

    }


    .item-sub {

        font-size: 8px;

        color: #6b7280;

        margin-top: 1px;

    }



    /*
    |--------------------------------------------------------------------------
    | Totals
    |--------------------------------------------------------------------------
    */


    .totals-table td {

        padding: 2px 0;

    }


    .totals-label {

        color: #374151;

    }


    .totals-value {

        text-align: right;

        font-weight: 700;

    }


    .grand-total td {

        border-top: 1px solid #111827;

        border-bottom: 1px solid #111827;

        padding: 4px 0;

        font-size: 11px;

        font-weight: 700;

    }



    /*
    |--------------------------------------------------------------------------
    | Footer
    |--------------------------------------------------------------------------
    */


    .footer-note {

        margin-top: 5px;

        text-align: center;

        font-size: 8px;

        color: #4b5563;

    }


    .signature-line {

        margin-top: 6px;

        border-top: 1px solid #9ca3af;

        text-align: center;

        padding-top: 2px;

        font-size: 7px;

        color: #6b7280;

    }



    /*
    |--------------------------------------------------------------------------
    | PDF Compact Height
    |--------------------------------------------------------------------------
    */


    table,
    tr,
    td {

        page-break-inside: avoid;

    }


    .receipt:last-child {

        margin-bottom: 0 !important;

        padding-bottom: 0 !important;

    }
    .money {
    white-space: nowrap;
    font-size: 9px;
}

.restaurant-logo {

    width: 45px;

    height: 45px;

    max-width: 45px;

    max-height: 45px;

    object-fit: contain;

    display: block;

    margin: 0 auto 4px auto;

}

</style>
</head>
<body>
    <main class="receipt">

        <div class="center">
            @if(!empty($invoiceSettings['restaurant_logo']))

                <img
                    src="{{ $invoiceSettings['restaurant_logo'] }}"
                    class="restaurant-logo"
                >

            @endif

            @if(!empty($invoiceSettings['restaurant_name']))

                <div class="restaurant-name">
                    {{ $invoiceSettings['restaurant_name'] }}
                </div>

            @endif

            @if(!empty($invoiceSettings['branch_address']))
                <div class="meta-line">
                    {{ $invoiceSettings['branch_address'] }}
                </div>
            @endif

            <div class="invoice-title">Tax Invoice</div>
            <div class="meta-line">Order: {{ $order->order_number }}</div>
            <div class="meta-line">Generated: {{ $generatedAt->format('d M Y, h:i A') }}</div>
        </div>

        <div class="divider"></div>

        <table class="info-table">
            <tr>
                <td class="info-label">Customer</td>
                <td class="info-value">{{ $customer['name'] ?? 'Walk-in Customer' }}</td>
            </tr>

            @if(!empty($customer['phone']))
                <tr>
                    <td class="info-label">Phone</td>
                    <td class="info-value">{{ $customer['phone'] }}</td>
                </tr>
            @endif

            @if(!empty($customer['email']))
                <tr>
                    <td class="info-label">Email</td>
                    <td class="info-value">{{ $customer['email'] }}</td>
                </tr>
            @endif

            <tr>
                <td class="info-label">Date</td>
                <td class="info-value">{{ $order->created_at?->format('d M Y') ?? '—' }}</td>
            </tr>

            <tr>
                <td class="info-label">Time</td>
                <td class="info-value">{{ $order->created_at?->format('h:i A') ?? '—' }}</td>
            </tr>

            @if($invoiceSettings['show_token_number'])
                <tr>
                    <td class="info-label">Token</td>
                    <td class="info-value">{{ $order->order_number }}</td>
                </tr>
            @endif

            @if($invoiceSettings['show_table_number'])
                <tr>
                    <td class="info-label">Table</td>
                    <td class="info-value">{{ $primaryTable?->table_name ?? 'No Table' }}</td>
                </tr>
            @endif

            <tr>
                <td class="info-label">Status</td>
                <td class="info-value">
                    {{ ucwords(str_replace('_', ' ', $order->status)) }}
                </td>
            </tr>
        </table>

        <div class="divider"></div>

        <div class="section-title">Items</div>

        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-left" style="width:44%;">Item</th>
                    <th class="text-center" style="width:12%;">Qty</th>
                    <th class="text-right" style="width:22%;">Price</th>
                    <th class="text-right" style="width:22%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td class="text-left">
                            <div class="item-name">{{ $item['name'] }}</div>

                            @if(!empty($item['variant']))
                                <div class="item-sub">Variant: {{ $item['variant'] }}</div>
                            @endif

                            @if(!empty($item['addons']))
                                @foreach($item['addons'] as $addon)
                                    <div class="item-sub">
                                        + {{ $addon['name'] }} x{{ $addon['quantity'] }}
                                    </div>
                                @endforeach
                            @endif
                        </td>

                        <td class="text-center">
                            {{ $item['quantity'] }}
                        </td>

                        <td class="text-right money">
                            BDT {{ number_format($item['unit_price'], 2) }}
                        </td>

                        <td class="text-right money">
                            BDT {{ number_format($item['line_total'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <table class="totals-table">
            <tr>
                <td class="totals-label">Subtotal</td>
                <td class="totals-value">
                    BDT  {{ number_format($financial['subtotal'], 2) }}
                </td>
            </tr>

            <tr>
                <td class="totals-label">Discount</td>
                <td class="totals-value">
                    BDT {{ number_format($financial['discount'], 2) }}
                </td>
            </tr>

            <tr>
                <td class="totals-label">VAT / Tax</td>
                <td class="totals-value">
                    BDT {{ number_format($financial['tax'], 2) }}
                </td>
            </tr>

            <tr>
                <td class="totals-label">Service Charge</td>
                <td class="totals-value">
                    BDT {{ number_format($financial['service_charge'], 2) }}
                </td>
            </tr>

            <tr class="grand-total">
                <td>Grand Total</td>
                <td class="text-right">
                    BDT {{ number_format($financial['total'], 2) }}
                </td>
            </tr>

            <tr>
                <td class="totals-label">Paid</td>
                <td class="totals-value">
                    BDT {{ number_format($financial['paid'], 2) }}
                </td>
            </tr>

            <tr>
                <td class="totals-label">Due</td>
                <td class="totals-value">
                    BDT {{ number_format($financial['due'], 2) }}
                </td>
            </tr>
        </table>

        @if(isset($payments) && $payments->isNotEmpty())
            <div class="divider"></div>
            <div class="section-title">Payments</div>

            <table class="info-table">
                @foreach($payments as $payment)
                    <tr>
                        <td class="info-label">
                            {{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}
                        </td>
                        <td class="info-value">
                            BDT {{ number_format((float) $payment->amount, 2) }}
                        </td>
                    </tr>
                @endforeach
            </table>
        @endif

        <div class="signature-line">
            Cashier Signature
        </div>

        <div class="footer-note">
            Thank you for your order!<br>
            Please keep this receipt for your records.
        </div>

    </main>
</body>
</html>