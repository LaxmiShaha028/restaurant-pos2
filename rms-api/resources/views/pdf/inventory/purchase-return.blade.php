@extends('pdf.inventory.layout')

@section('title', $purchaseReturn->return_no)

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

    $items = $purchaseReturn->items ?? collect();
    $settlements = $purchaseReturn->settlements ?? collect();

    $target = round((float) $purchaseReturn->expected_supplier_credit, 2);

    $allocated = round(
        (float) $settlements
            ->where('allocation_effect', true)
            ->sum('amount'),
        2
    );

    $refundReceivableCreated = round(
        (float) $settlements
            ->where('settlement_type', 'refund_receivable')
            ->sum('amount'),
        2
    );

    $refundCollected = round(
        (float) $settlements
            ->where('settlement_type', 'receivable_collection')
            ->sum('amount'),
        2
    );

    $receivableBalance = max(
        0,
        $refundReceivableCreated - $refundCollected
    );

    $status =
        $allocated <= 0
            ? 'Pending Settlement'
            : (
                $allocated + 0.005 < $target
                    ? 'Partially Settled'
                    : (
                        $receivableBalance > 0.005
                            ? 'Awaiting Refund'
                            : 'Settled'
                    )
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

    $returnReasonLabel = static function ($reason): string {
        if (method_exists(\App\Models\PurchaseReturnItem::class, 'reasonLabel')) {
            return \App\Models\PurchaseReturnItem::reasonLabel($reason);
        }

        return ucwords(str_replace('_', ' ', (string) ($reason ?? 'unknown')));
    };

    $settlementTypeLabel = static function ($type): string {
        if (method_exists(\App\Models\PurchaseReturnSettlement::class, 'typeLabel')) {
            return \App\Models\PurchaseReturnSettlement::typeLabel($type);
        }

        return ucwords(str_replace('_', ' ', (string) ($type ?? 'unknown')));
    };
@endphp

<div class="topbar">
    <table class="topbar-table">
        <tr>
            <td>
                <div class="brand">{{ $restaurantName }}</div>
                <div class="brand-subtitle">Inventory Supplier Return</div>
            </td>

            <td>
                <div class="document-name">PURCHASE RETURN NOTE</div>
                <div class="document-number">
                    {{ $purchaseReturn->return_no }}
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
                        <td>{{ $purchaseReturn->supplier_name }}</td>
                    </tr>
                    <tr>
                        <td>Phone</td>
                        <td>{{ $purchaseReturn->supplier_phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>{{ $purchaseReturn->supplier_email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Address</td>
                        <td>{{ $purchaseReturn->supplier_address ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </td>

        <td>
            <div class="card">
                <div class="card-title">Return Information</div>

                <table class="kv">
                    <tr>
                        <td>Returned At</td>
                        <td>{{ $pdfDate($purchaseReturn->returned_at, 'd M Y, h:i A') }}</td>
                    </tr>
                    <tr>
                        <td>Recorded By</td>
                        <td>{{ $purchaseReturn->creator?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Items</td>
                        <td>{{ $items->count() }}</td>
                    </tr>
                    <tr>
                        <td>Settlement</td>
                        <td>{{ $status }}</td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>


<div class="section">
    <div class="section-title">Returned Items</div>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th>Item</th>
                <th style="width: 12%;">Lot</th>
                <th style="width: 12%;">Batch</th>
                <th style="width: 12%;">Expiry</th>
                <th style="width: 10%;" class="right">Qty</th>
                <th style="width: 13%;" class="right">Return Cost</th>
                <th style="width: 14%;" class="right">Credit</th>
            </tr>
        </thead>

        <tbody>
            @forelse($items as $index => $item)
                @php
                    $expiry = $item->expiry_date
                        ? \Illuminate\Support\Carbon::parse($item->expiry_date)->format('d M Y')
                        : '-';
                @endphp

                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->item_name }}</strong>
                        <div class="small muted">
                            {{ $returnReasonLabel($item->reason) }}
                        </div>
                    </td>
                    <td>{{ $item->lot_no }}</td>
                    <td>{{ $item->supplier_batch_no ?? '-' }}</td>
                    <td>{{ $expiry }}</td>
                    <td class="right">
                        {{ rtrim(rtrim(number_format((float) $item->quantity, 4, '.', ''), '0'), '.') }}
                        {{ $item->unit }}
                    </td>
                    <td class="right">
                        BDT {{ number_format((float) $item->return_unit_cost, 4) }}
                    </td>
                    <td class="right">
                        <strong>
                            BDT {{ number_format((float) $item->expected_supplier_credit, 2) }}
                        </strong>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center muted">
                        No Purchase Return items found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>


    <table class="totals">
        <tr>
            <td>Expected Supplier Credit</td>
            <td>BDT {{ number_format((float) $purchaseReturn->expected_supplier_credit, 2) }}</td>
        </tr>
        <tr>
            <td>Inventory Value Reduction</td>
            <td>BDT {{ number_format((float) $purchaseReturn->inventory_value_reduction, 2) }}</td>
        </tr>
        <tr>
            <td>Valuation Variance</td>
            <td>BDT {{ number_format((float) $purchaseReturn->valuation_variance, 2) }}</td>
        </tr>
        <tr class="total">
            <td>Unallocated Settlement</td>
            <td>BDT {{ number_format(max(0, $target - $allocated), 2) }}</td>
        </tr>
    </table>
</div>


<div class="section">
    <div class="section-title">Supplier Settlement Ledger</div>

    <table class="data">
        <thead>
            <tr>
                <th>Settlement No</th>
                <th>Type</th>
                <th>Date</th>
                <th>Method / Reference</th>
                <th class="right">Amount</th>
                <th>Cash Effect</th>
            </tr>
        </thead>

        <tbody>
            @forelse($settlements->whereNull('parent_settlement_id') as $settlement)
                <tr>
                    <td>{{ $settlement->settlement_no }}</td>
                    <td>
                        {{ $settlementTypeLabel($settlement->settlement_type) }}
                    </td>
                    <td>{{ $pdfDate($settlement->settled_at, 'd M Y, h:i A') }}</td>
                    <td>
                        {{ $settlement->payment_method ? ucwords(str_replace('_', ' ', $settlement->payment_method)) : '-' }}
                        @if($settlement->transaction_reference)
                            <div class="small muted">{{ $settlement->transaction_reference }}</div>
                        @endif
                    </td>
                    <td class="right">BDT {{ number_format((float) $settlement->amount, 2) }}</td>
                    <td>{{ $settlement->cash_effect ? 'Yes' : 'No' }}</td>
                </tr>

                @foreach($settlement->children ?? collect() as $child)
                    <tr>
                        <td>{{ $child->settlement_no }}</td>
                        <td>Refund Collection</td>
                        <td>{{ $pdfDate($child->settled_at, 'd M Y, h:i A') }}</td>
                        <td>
                            {{ $child->payment_method ? ucwords(str_replace('_', ' ', $child->payment_method)) : '-' }}
                            @if($child->transaction_reference)
                                <div class="small muted">{{ $child->transaction_reference }}</div>
                            @endif
                        </td>
                        <td class="right">BDT {{ number_format((float) $child->amount, 2) }}</td>
                        <td>Yes</td>
                    </tr>
                @endforeach

                @foreach($settlement->creditApplications ?? collect() as $application)
                    <tr>
                        <td>{{ $application->application_no }}</td>
                        <td>Supplier Credit Applied to PO</td>
                        <td>{{ $pdfDate($application->applied_at, 'd M Y, h:i A') }}</td>
                        <td>
                            PO #{{ $application->purchase_order_id }}
                        </td>
                        <td class="right">BDT {{ number_format((float) $application->amount, 2) }}</td>
                        <td>No</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="6" class="center muted">
                        No supplier settlement recorded yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>


@if($purchaseReturn->notes)
<div class="section">
    <div class="section-title">Return Notes</div>
    <div class="note">{{ $purchaseReturn->notes }}</div>
</div>
@endif


<table class="signature-table">
    <tr>
        <td>
            <div class="signature-line">Returned By</div>
        </td>
        <td>
            <div class="signature-line">Supplier Received By</div>
        </td>
        <td>
            <div class="signature-line">Authorized By</div>
        </td>
    </tr>
</table>
@endsection
