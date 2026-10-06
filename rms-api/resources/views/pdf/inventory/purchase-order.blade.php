@extends('pdf.inventory.layout')

@section('title', 'Purchase Order #' . $purchaseOrder->id)

@section('content')
@php
    /*
    |--------------------------------------------------------------------------
    | Current Tenant Restaurant Branding
    |--------------------------------------------------------------------------
    |
    | ResolveTenant stores the current central Restaurant model on the request
    | attribute named "tenant". Use that name for every printable document.
    |
    */

    $tenantRestaurant =
        request()->attributes->get('tenant');

    $restaurantName =
        trim(
            (string) (
                $tenantRestaurant?->name
                ??
                ''
            )
        );

    if ($restaurantName === '') {
        $restaurantName =
            (string) config(
                'app.name',
                'Restaurant'
            );
    }

    $supplier = $purchaseOrder->supplier;
    $items = $purchaseOrder->items ?? collect();
    $payments = $purchaseOrder->payments ?? collect();

    $subtotal = round((float) $purchaseOrder->subtotal, 2);
    $tax = round((float) $purchaseOrder->tax, 2);
    $serviceCharge = round((float) $purchaseOrder->service_charge, 2);
    $total = round((float) $purchaseOrder->total_amount, 2);
    $paid = round((float) $purchaseOrder->paid_amount, 2);
    $returnCredit = round((float) ($purchaseOrder->return_credit_applied ?? 0), 2);
    $supplierCredit = round((float) ($purchaseOrder->supplier_credit_applied ?? 0), 2);
    $due = round(max(0, (float) $purchaseOrder->due_amount), 2);

    $pdfDate = static function ($value, string $format): string {
        if ($value === null || $value === '') {
            return '-';
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format($format);
        } catch (\Throwable $exception) {
            return (string) $value;
        }
    };

    $rawStatus = $purchaseOrder->status ?? 'unknown';

    $statusValue =
        is_object($rawStatus) && isset($rawStatus->value)
            ? $rawStatus->value
            : (string) $rawStatus;

    $supplierDisplayName =
        $supplier?->supplier_name
        ?? $supplier?->company_name
        ?? $supplier?->name
        ?? 'Unknown Supplier';
@endphp

<div class="topbar">
    <table class="topbar-table">
        <tr>
            <td>
                <div class="brand">{{ $restaurantName }}</div>
                <div class="brand-subtitle">Restaurant Management System</div>
            </td>

            <td>
                <div class="document-name">PURCHASE ORDER</div>
                <div class="document-number">
                    PO #{{ $purchaseOrder->id }}
                </div>
            </td>
        </tr>
    </table>
</div>


<table class="grid-2">
    <tr>
        <td>
            <div class="card">
                <div class="card-title">Supplier</div>

                <table class="kv">
                    <tr>
                        <td>Name</td>
                        <td>{{ $supplierDisplayName }}</td>
                    </tr>
                    <tr>
                        <td>Phone</td>
                        <td>{{ $supplier?->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>{{ $supplier?->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Address</td>
                        <td>{{ $supplier?->address ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </td>

        <td>
            <div class="card">
                <div class="card-title">Order Information</div>

                <table class="kv">
                    <tr>
                        <td>Order Date</td>
                        <td>
                            {{ $pdfDate($purchaseOrder->order_date, 'd M Y, h:i A') }}
                        </td>
                    </tr>
                    <tr>
                        <td>Delivery Date</td>
                        <td>
                            {{ $pdfDate($purchaseOrder->delivery_date, 'd M Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>
                            {{ ucwords(str_replace('_', ' ', (string) $statusValue)) }}
                        </td>
                    </tr>
                    <tr>
                        <td>Ordered By</td>
                        <td>{{ $purchaseOrder->orderedBy?->name ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>


<div class="section">
    <div class="section-title">Purchase Items</div>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th>Item</th>
                <th style="width: 12%;">Unit</th>
                <th style="width: 13%;" class="right">Qty</th>
                <th style="width: 16%;" class="right">Unit Price</th>
                <th style="width: 17%;" class="right">Line Total</th>
            </tr>
        </thead>

        <tbody>
            @forelse($items as $index => $item)
                @php
                    $qty = round((float) $item->quantity, 4);
                    $unitPrice = round((float) $item->unit_price, 4);
                    $lineTotal = round(
                        (float) ($item->total_price ?? ($qty * $unitPrice)),
                        2
                    );
                @endphp

                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->item_name }}</strong>
                    </td>
                    <td>{{ $item->unit }}</td>
                    <td class="right">{{ rtrim(rtrim(number_format($qty, 4, '.', ''), '0'), '.') }}</td>
                    <td class="right">BDT {{ number_format($unitPrice, 2) }}</td>
                    <td class="right"><strong>BDT {{ number_format($lineTotal, 2) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center muted">
                        No purchase items found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>


    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td>BDT {{ number_format($subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>Tax</td>
            <td>BDT {{ number_format($tax, 2) }}</td>
        </tr>
        <tr>
            <td>Service Charge</td>
            <td>BDT {{ number_format($serviceCharge, 2) }}</td>
        </tr>
        <tr class="total">
            <td>Total</td>
            <td>BDT {{ number_format($total, 2) }}</td>
        </tr>
        <tr>
            <td>Paid</td>
            <td>BDT {{ number_format($paid, 2) }}</td>
        </tr>
        <tr>
            <td>Purchase Return Credit</td>
            <td>BDT {{ number_format($returnCredit, 2) }}</td>
        </tr>
        <tr>
            <td>Supplier Credit Applied</td>
            <td>BDT {{ number_format($supplierCredit, 2) }}</td>
        </tr>
        <tr class="total">
            <td>Effective Due</td>
            <td>BDT {{ number_format($due, 2) }}</td>
        </tr>
    </table>
</div>


@if($payments->count())
<div class="section">
    <div class="section-title">Payment History</div>

    <table class="data">
        <thead>
            <tr>
                <th>Date</th>
                <th>Method</th>
                <th>Reference</th>
                <th>Recorded By</th>
                <th class="right">Amount</th>
            </tr>
        </thead>

        <tbody>
            @foreach($payments as $payment)
                <tr>
                    <td>{{ $pdfDate($payment->payment_date, 'd M Y') }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', (string) $payment->payment_method)) }}</td>
                    <td>{{ $payment->transaction_reference ?? '-' }}</td>
                    <td>{{ $payment->creator?->name ?? '-' }}</td>
                    <td class="right">BDT {{ number_format((float) $payment->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif


@if($purchaseOrder->notes)
<div class="section">
    <div class="section-title">Notes</div>
    <div class="note">{{ $purchaseOrder->notes }}</div>
</div>
@endif


<table class="signature-table">
    <tr>
        <td>
            <div class="signature-line">Prepared By</div>
        </td>
        <td>
            <div class="signature-line">Approved By</div>
        </td>
        <td>
            <div class="signature-line">Supplier Signature</div>
        </td>
    </tr>
</table>
@endsection
