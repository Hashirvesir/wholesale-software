<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Parties List</title>
    <style>
        @page {
            margin: 0.4in 0.3in;
        }

        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .company-info {
            float: left;
            width: 60%;
        }

        .report-title {
            float: right;
            width: 40%;
            text-align: right;
        }

        .logo {
            max-height: 50px;
            margin-bottom: 8px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .report-heading {
            font-size: 20px;
            font-weight: bold;
            color: #444;
            text-transform: uppercase;
        }

        .report-date {
            font-size: 10px;
            color: #888;
            margin-top: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th {
            background-color: #f3f4f6;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px 6px;
            border-bottom: 2px solid #ddd;
            text-align: left;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        td {
            padding: 6px;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }

        tr:nth-child(even) {
            background-color: #fafafa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .type-customer {
            color: #1e40af;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }

        .type-vendor {
            color: #7c3aed;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }

        .type-cargo {
            color: #b45309;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }

        .bal-dr {
            color: #dc2626;
            font-weight: bold;
        }

        .bal-cr {
            color: #16a34a;
            font-weight: bold;
        }

        .status-active {
            color: #065f46;
            font-weight: bold;
        }

        .status-inactive {
            color: #991b1b;
            font-weight: bold;
        }

        .summary {
            margin-top: 15px;
            padding: 10px;
            background-color: #f3f4f6;
            border: 1px solid #ddd;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #888;
            font-size: 9px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="header clearfix">
        <div class="company-info">
            @if ($settings->company_logo)
                <img src="{{ public_path($settings->company_logo) }}" class="logo" alt="Logo">
            @endif
            <div class="company-name">{{ $settings->company_name }}</div>
            <div>{{ $settings->company_address }}</div>
            <div>{{ $settings->company_phone }} | {{ $settings->company_email }}</div>
        </div>
        <div class="report-title">
            <div class="report-heading">Parties List</div>
            <div class="report-date">Generated: {{ date('d M, Y h:i A') }}</div>
            <div class="report-date">Total Parties: {{ $parties->count() }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="20%">Name</th>
                <th width="12%">Phone</th>
                <th width="15%">Address</th>
                <th width="8%">Type</th>
                <th width="12%" class="text-right">Opening Balance</th>
                <th width="15%" class="text-right">Current Balance</th>
                <th width="5%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalDr = 0;
                $totalCr = 0;
            @endphp
            @foreach ($parties as $index => $party)
                @php
                    $currentBal = $party->getCurrentBalance();
                    if ($currentBal >= 0) {
                        $totalDr += $currentBal;
                    } else {
                        $totalCr += abs($currentBal);
                    }
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $party->name }}</td>
                    <td>{{ $party->phone ?? '-' }}</td>
                    <td>{{ $party->address ?? '-' }}</td>
                    <td>
                        <span class="type-{{ $party->type }}">{{ $party->type }}</span>
                    </td>
                    <td class="text-right">
                        {{ $settings->currency_symbol }} {{ number_format($party->opening_balance, 2) }}
                        <small>({{ ucfirst($party->balance_type) }})</small>
                    </td>
                    <td class="text-right">
                        <span class="{{ $currentBal >= 0 ? 'bal-dr' : 'bal-cr' }}">
                            {{ $settings->currency_symbol }} {{ number_format(abs($currentBal), 2) }}
                            {{ $currentBal >= 0 ? 'Dr' : 'Cr' }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if ($party->status)
                            <span class="status-active">✓</span>
                        @else
                            <span class="status-inactive">✗</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #e5e7eb; font-weight: bold;">
                <td colspan="6" class="text-right" style="padding: 8px 6px; border-top: 2px solid #333;">TOTALS:</td>
                <td class="text-right" style="padding: 8px 6px; border-top: 2px solid #333;">
                    <span class="bal-dr">Dr: {{ $settings->currency_symbol }}
                        {{ number_format($totalDr, 2) }}</span><br>
                    <span class="bal-cr">Cr: {{ $settings->currency_symbol }} {{ number_format($totalCr, 2) }}</span>
                </td>
                <td style="border-top: 2px solid #333;"></td>
            </tr>
        </tfoot>
    </table>

    <div class="summary clearfix">
        <strong>Summary:</strong>
        Total: {{ $parties->count() }} |
        Customers: {{ $parties->where('type', 'customer')->count() }} |
        Vendors: {{ $parties->where('type', 'vendor')->count() }} |
        Cargo: {{ $parties->where('type', 'cargo')->count() }} |
        Active: {{ $parties->where('status', true)->count() }} |
        Inactive: {{ $parties->where('status', false)->count() }}
    </div>

    <div class="footer">
        {{ $settings->company_name }} — Parties Report
        <div style="margin-top: 3px;">Developed by Invenco Tel: +92 324 3980274</div>
    </div>
</body>

</html>
