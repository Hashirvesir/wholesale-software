<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Quotation #{{ $quotation->id }}</title>
    <style>
        @page {
            margin: 0.5in 0.4in;
        }

        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .header {
            width: 100%;
            margin-bottom: 25px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
        }

        .company-info {
            float: left;
            width: 58%;
        }

        .quotation-title {
            float: right;
            width: 42%;
            text-align: right;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 4px;
            text-transform: uppercase;
            color: #1e293b;
        }

        .heading {
            font-size: 22px;
            font-weight: 800;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .subheading {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .details-box {
            width: 100%;
            clear: both;
            margin-bottom: 20px;
        }

        .left-box {
            float: left;
            width: 55%;
        }

        .right-box {
            float: right;
            width: 45%;
            text-align: right;
        }

        .section-label {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 3px;
        }

        .party-name {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background-color: #f1f5f9;
            text-transform: uppercase;
            font-size: 10px;
            font-weight: bold;
            padding: 8px 10px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            color: #334155;
            text-align: left;
        }

        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals-box {
            float: right;
            width: 45%;
        }

        .total-row {
            padding: 4px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }

        .grand-total {
            font-size: 15px;
            font-weight: bold;
            border-top: 2px solid #2563eb;
            border-bottom: 2px solid #2563eb;
            padding: 8px 0;
            margin-top: 4px;
            color: #0f172a;
        }

        .notes {
            float: left;
            width: 50%;
            font-size: 11px;
            color: #475569;
        }

        .notes-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 10px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .signatures {
            margin-top: 60px;
            width: 100%;
            clear: both;
        }

        .signature-box {
            float: left;
            width: 45%;
            border-top: 1px solid #94a3b8;
            text-align: center;
            padding-top: 5px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #475569;
        }

        .signature-box-right {
            float: right;
            width: 45%;
            border-top: 1px solid #94a3b8;
            text-align: center;
            padding-top: 5px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #475569;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header clearfix">
            <div class="company-info">
                @if (!empty($settings->logo))
                    <img src="{{ public_path('storage/' . $settings->logo) }}" style="max-height: 50px; margin-bottom: 5px;" alt="Logo" />
                @endif
                <div class="company-name">{{ $settings->company_name ?? config('app.name') }}</div>
                @if (!empty($settings->address))
                    <div>{{ $settings->address }}</div>
                @endif
                @if (!empty($settings->phone))
                    <div>Phone: {{ $settings->phone }}</div>
                @endif
                @if (!empty($settings->email))
                    <div>Email: {{ $settings->email }}</div>
                @endif
            </div>
            <div class="quotation-title">
                <div class="heading">QUOTATION</div>
                <div class="subheading">Price Estimate / Kacha Bill</div>
                <div style="margin-top: 6px; font-size: 13px; font-weight: bold;">
                    #{{ $quotation->id }}
                </div>
                <div>
                    <span class="status-badge" style="background-color: #e0f2fe; color: #0369a1;">
                        Status: {{ strtoupper($quotation->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="details-box clearfix">
            <div class="left-box">
                <div class="section-label">Quotation Prepared For:</div>
                <div class="party-name">{{ $quotation->party->name }}</div>
                @if ($quotation->party->phone)
                    <div>Phone: {{ $quotation->party->phone }}</div>
                @endif
                @if ($quotation->party->address)
                    <div>Address: {{ $quotation->party->address }}</div>
                @endif
            </div>
            <div class="right-box">
                <div><strong>Quote Date:</strong> {{ \Carbon\Carbon::parse($quotation->date)->format('d M, Y') }}</div>
                @if ($quotation->valid_until)
                    <div style="margin-top: 3px;"><strong>Valid Until:</strong> {{ \Carbon\Carbon::parse($quotation->valid_until)->format('d M, Y') }}</div>
                @endif
                <div style="margin-top: 3px;"><strong>Warehouse:</strong> {{ $quotation->warehouse->name }}</div>
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 30px;">#</th>
                    <th>Item Description</th>
                    <th class="text-center" style="width: 60px;">Unit</th>
                    <th class="text-right" style="width: 70px;">Qty</th>
                    <th class="text-right" style="width: 90px;">Rate ({{ $settings->currency_symbol ?? 'Rs' }})</th>
                    <th class="text-right" style="width: 100px;">Total ({{ $settings->currency_symbol ?? 'Rs' }})</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quotation->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->product->name ?? 'N/A' }}</strong>
                            @if (!empty($item->product->sku))
                                <br><span style="font-size: 9px; color: #64748b;">SKU: {{ $item->product->sku }}</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->product->unit->name ?? 'Pcs' }}</td>
                        <td class="text-right">{{ number_format($item->qty, 2) }}</td>
                        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right"><strong>{{ number_format($item->total_price, 2) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals & Notes -->
        <div class="clearfix">
            <div class="notes">
                <div class="notes-card">
                    <strong>Terms & Conditions:</strong>
                    <div style="margin-top: 4px; line-height: 1.4;">
                        @if ($quotation->note)
                            {{ $quotation->note }}
                        @else
                            Prices quoted are valid until the specified date. Delivery charges and stock confirmation apply upon conversion to a confirmed order.
                        @endif
                    </div>
                </div>
            </div>

            <div class="totals-box">
                <div class="total-row clearfix">
                    <span style="float: left;">Subtotal:</span>
                    <span style="float: right;">{{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($quotation->subtotal, 2) }}</span>
                </div>
                @if ($quotation->discount_amount > 0)
                    <div class="total-row clearfix">
                        <span style="float: left;">Discount:</span>
                        <span style="float: right; color: #dc2626;">- {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($quotation->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="grand-total clearfix">
                    <span style="float: left; text-transform: uppercase;">Quotation Total:</span>
                    <span style="float: right; color: #2563eb;">{{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($quotation->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Signatures -->
        <div class="signatures clearfix">
            <div class="signature-box">
                Customer Acceptance
            </div>
            <div class="signature-box-right">
                Authorized Signatory
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            This estimate was generated electronically by {{ config('app.name') }} on {{ date('d M, Y H:i A') }}.
        </div>
    </div>
</body>
</html>
