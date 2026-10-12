@php
    $tenantRestaurant =
        request()->attributes->get('tenant');

    $pdfRestaurantName =
        trim(
            (string) (
                $tenantRestaurant?->name
                ??
                ''
            )
        );

    if ($pdfRestaurantName === '') {
        $pdfRestaurantName =
            (string) config(
                'app.name',
                'Restaurant'
            );
    }
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <title>
        @yield('title', 'Inventory Document')
    </title>

    <style>
        @page {
            margin: 24px 28px 32px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #1f2937;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10px;
            line-height: 1.45;
        }

        .document {
            width: 100%;
        }

        .topbar {
            width: 100%;
            margin-bottom: 16px;
            border-bottom: 2px solid #111827;
            padding-bottom: 10px;
        }

        .topbar-table {
            width: 100%;
            border-collapse: collapse;
        }

        .topbar-table td {
            vertical-align: top;
        }

        .brand {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .brand-subtitle {
            margin-top: 2px;
            color: #6b7280;
            font-size: 9px;
        }

        .document-name {
            text-align: right;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.4px;
        }

        .document-number {
            margin-top: 3px;
            text-align: right;
            color: #4b5563;
            font-size: 9px;
        }

        .grid-2 {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .grid-2 td {
            width: 50%;
            vertical-align: top;
            padding-right: 8px;
        }

        .grid-2 td:last-child {
            padding-right: 0;
            padding-left: 8px;
        }

        .card {
            border: 1px solid #d1d5db;
            border-radius: 5px;
            padding: 9px 10px;
        }

        .card-title {
            margin-bottom: 6px;
            color: #111827;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.35px;
        }

        .kv {
            width: 100%;
            border-collapse: collapse;
        }

        .kv td {
            padding: 2px 0;
            vertical-align: top;
        }

        .kv td:first-child {
            width: 42%;
            color: #6b7280;
        }

        .kv td:last-child {
            text-align: right;
            font-weight: 600;
        }

        .section {
            margin-top: 12px;
        }

        .section-title {
            margin-bottom: 6px;
            color: #111827;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.35px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data th {
            border: 1px solid #d1d5db;
            background: #f3f4f6;
            padding: 6px 5px;
            color: #374151;
            font-size: 8px;
            font-weight: 700;
            text-align: left;
        }

        table.data td {
            border: 1px solid #d1d5db;
            padding: 6px 5px;
            vertical-align: top;
            font-size: 8.5px;
        }

        table.data .right {
            text-align: right;
        }

        table.data .center {
            text-align: center;
        }

        .totals {
            width: 46%;
            margin-left: 54%;
            margin-top: 9px;
            border-collapse: collapse;
        }

        .totals td {
            padding: 3px 2px;
        }

        .totals td:first-child {
            color: #6b7280;
        }

        .totals td:last-child {
            text-align: right;
            font-weight: 600;
        }

        .totals tr.total td {
            border-top: 1px solid #9ca3af;
            padding-top: 5px;
            color: #111827;
            font-size: 10px;
            font-weight: 700;
        }

        .badge {
            display: inline-block;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 2px 6px;
            background: #f9fafb;
            font-size: 7.5px;
            font-weight: 600;
        }

        .note {
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            padding: 8px 9px;
            background: #fafafa;
            color: #4b5563;
        }

        .muted {
            color: #6b7280;
        }

        .small {
            font-size: 8px;
        }

        .signature-table {
            width: 100%;
            margin-top: 28px;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 33.333%;
            padding: 0 12px;
            text-align: center;
            vertical-align: bottom;
        }

        .signature-line {
            border-top: 1px solid #6b7280;
            padding-top: 5px;
            color: #4b5563;
            font-size: 8px;
        }

        .footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -19px;
            border-top: 1px solid #e5e7eb;
            padding-top: 5px;
            color: #9ca3af;
            font-size: 7px;
            text-align: center;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>
    <div class="document">
        @yield('content')
    </div>

    <div class="footer">
        Generated for {{ $pdfRestaurantName }}
        ·
        {{ now()->format('d M Y, h:i A') }}
    </div>
</body>
</html>
