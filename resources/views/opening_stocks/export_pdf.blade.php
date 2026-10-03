<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Opening Stocks</title>
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
            <div class="report-heading">Opening Stocks</div>
            <div class="report-date">Generated: {{ date('d M, Y h:i A') }}</div>
            <div class="report-date">Total Entries: {{ $openingStocks->count() }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="12%">Date</th>
                <th width="25%">Product</th>
                <th width="15%">Warehouse</th>
                <th width="9%" class="text-right">Qty</th>
                <th width="10%" class="text-right">Rate</th>
                <th width="10%" class="text-right">Cargo</th>
                <th width="10%" class="text-right">Total</th>
                <th width="9%">Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($openingStocks as $index => $os)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $os->date }}</td>
                    <td class="font-bold">{{ $os->product->name }}</td>
                    <td>{{ $os->warehouse->name }}</td>
                    <td class="text-right font-bold">{{ number_format($os->qty, 2) }}</td>
                    <td class="text-right">{{ $settings->currency_symbol }}
                        {{ number_format($os->rate, 2) }}</td>
                    <td class="text-right">{{ $settings->currency_symbol }}
                        {{ number_format($os->cargo_amount, 2) }}</td>
                    <td class="text-right font-bold">{{ $settings->currency_symbol }}
                        {{ number_format($os->total, 2) }}</td>
                    <td>{{ $os->note ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #e5e7eb; font-weight: bold;">
                <td colspan="4" class="text-right" style="padding: 8px 6px; border-top: 2px solid #333;">TOTALS:</td>
                <td class="text-right" style="padding: 8px 6px; border-top: 2px solid #333;">
                    {{ number_format($openingStocks->sum('qty'), 2) }}</td>
                <td style="border-top: 2px solid #333;"></td>
                <td class="text-right" style="padding: 8px 6px; border-top: 2px solid #333;">
                    {{ $settings->currency_symbol }} {{ number_format($openingStocks->sum('cargo_amount'), 2) }}</td>
                <td class="text-right" style="padding: 8px 6px; border-top: 2px solid #333;">
                    {{ $settings->currency_symbol }} {{ number_format($openingStocks->sum('total'), 2) }}</td>
                <td style="border-top: 2px solid #333;"></td>
            </tr>
        </tfoot>
    </table>

    <div class="summary clearfix">
        <strong>Summary:</strong>
        Total Entries: {{ $openingStocks->count() }} |
        Total Qty: {{ number_format($openingStocks->sum('qty'), 2) }} |
        Total Value: {{ $settings->currency_symbol }} {{ number_format($openingStocks->sum('total'), 2) }}
    </div>

    <div class="footer">
        {{ $settings->company_name }} — Opening Stocks Report
        <div style="margin-top: 3px;">Developed by Invenco Tel: +92 324 3980274</div>
    </div>
</body>

</html>
