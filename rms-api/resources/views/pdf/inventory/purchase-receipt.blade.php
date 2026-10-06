@extends('pdf.inventory.layout')

@section('title', ($receipt->receipt_no ?? 'GRN') . ' - PO #' . $purchaseOrder->id)

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
    $items = $receipt->items ?? collect();

    $totalCost = round(
        (float) $items->sum(
            fn ($item) =>
                (float) (
                    $item->total_cost
                    ??
                    (
                        (float) $item->quantity
                        *
                        (float) $item->unit_cost
                    )
                )
        ),
        4
    );

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
                <div class="brand-subtitle">Warehouse Receipt Ledger</div>
            </td>

            <td>
                <div class="document-name">GOODS RECEIPT NOTE</div>
                <div class="document-number">
                    {{ $receipt->receipt_no ?? ('GRN #' . $receipt->id) }}
                </div>
            </td>
        </tr>
    </table>
</div>


<table class="grid-2">
    <tr>
        <td>
            <div class="card">
                <div class="card-title">Supplier / Purchase Order</div>

                <table class="kv">
                    <tr>
                        <td>Supplier</td>
                        <td>{{ $supplierDisplayName }}</td>
                    </tr>
                    <tr>
                        <td>Phone</td>
                        <td>{{ $supplier?->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>PO</td>
                        <td>#{{ $purchaseOrder->id }}</td>
                    </tr>
                    <tr>
                        <td>PO Date</td>
                        <td>{{ $pdfDate($purchaseOrder->order_date, 'd M Y') }}</td>
                    </tr>
                </table>
            </div>
        </td>

        <td>
            <div class="card">
                <div class="card-title">Receiving Information</div>

                <table class="kv">
                    <tr>
                        <td>Received At</td>
                        <td>{{ $pdfDate($receipt->received_at, 'd M Y, h:i A') }}</td>
                    </tr>
                    <tr>
                        <td>Received By</td>
                        <td>{{ $receipt->receivedBy?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Items</td>
                        <td>{{ $items->count() }}</td>
                    </tr>
                    <tr>
                        <td>Total Cost</td>
                        <td>BDT {{ number_format($totalCost, 2) }}</td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>


<div class="section">
    <div class="section-title">Received Items</div>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th>Item</th>
                <th style="width: 10%;">Unit</th>
                <th style="width: 10%;" class="right">Qty</th>
                <th style="width: 12%;" class="right">Unit Cost</th>
                <th style="width: 13%;">Batch</th>
                <th style="width: 13%;">Expiry</th>
                <th style="width: 14%;" class="right">Total</th>
            </tr>
        </thead>

        <tbody>
            @forelse($items as $index => $item)
                @php
                    $quantity = round((float) $item->quantity, 4);
                    $unitCost = round((float) $item->unit_cost, 4);
                    $lineTotal = round(
                        (float) (
                            $item->total_cost
                            ??
                            ($quantity * $unitCost)
                        ),
                        4
                    );

                    $expiry = $item->expiry_date
                        ? \Illuminate\Support\Carbon::parse($item->expiry_date)->format('d M Y')
                        : '-';
                @endphp

                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>
                        <strong>
                            {{ $item->item_name ?? $item->rawMaterial?->material_name ?? '-' }}
                        </strong>
                    </td>
                    <td>{{ $item->unit }}</td>
                    <td class="right">
                        {{ rtrim(rtrim(number_format($quantity, 4, '.', ''), '0'), '.') }}
                    </td>
                    <td class="right">BDT {{ number_format($unitCost, 4) }}</td>
                    <td>{{ $item->supplier_batch_no ?? '-' }}</td>
                    <td>{{ $expiry }}</td>
                    <td class="right"><strong>BDT {{ number_format($lineTotal, 2) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center muted">
                        No receipt items found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr class="total">
            <td>Total Received Cost</td>
            <td>BDT {{ number_format($totalCost, 2) }}</td>
        </tr>
    </table>
</div>


@if($receipt->notes)
<div class="section">
    <div class="section-title">GRN Notes</div>
    <div class="note">{{ $receipt->notes }}</div>
</div>
@endif


<table class="signature-table">
    <tr>
        <td>
            <div class="signature-line">Received By</div>
        </td>
        <td>
            <div class="signature-line">Warehouse Verified By</div>
        </td>
        <td>
            <div class="signature-line">Supplier / Delivery Signature</div>
        </td>
    </tr>
</table>
@endsection
