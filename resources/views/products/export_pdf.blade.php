<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Products List</title>
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
            <div class="report-heading">Products List</div>
            <div class="report-date">Generated: {{ date('d M, Y h:i A') }}</div>
            <div class="report-date">Total Products: {{ $products->count() }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="22%">Product Name</th>
                <th width="10%">SKU</th>
                <th width="12%">Category</th>
                <th width="10%">Brand</th>
                <th width="8%">Unit</th>
                <th width="10%" class="text-right">Sale Price</th>
                <th width="10%" class="text-right">Stock</th>
                <th width="8%" class="text-right">Alert Qty</th>
                <th width="5%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $index => $product)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $product->name }}</td>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td>{{ $product->brand->name }}</td>
                    <td>{{ $product->unit->short_name }}</td>
                    <td class="text-right">{{ $settings->currency_symbol }}
                        {{ number_format($product->sale_price, 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($product->getCurrentStock(), 2) }}</td>
                    <td class="text-right">{{ $product->alert_quantity }}</td>
                    <td class="text-center">
                        @if ($product->status)
                            <span class="status-active">✓</span>
                        @else
                            <span class="status-inactive">✗</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary clearfix">
        <strong>Summary:</strong>
        Total Products: {{ $products->count() }} |
        Active: {{ $products->where('status', true)->count() }} |
        Inactive: {{ $products->where('status', false)->count() }}
    </div>

    <div class="footer">
        {{ $settings->company_name }} — Products Report
        <div style="margin-top: 3px;">Developed by Invenco Tel: +92 324 3980274</div>
    </div>
</body>

</html>
