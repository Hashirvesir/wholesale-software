<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Product Sales Report</title>
    <style>
        @page {
            margin: 12mm 10mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #222;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
        }
        .report-title {
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            color: #111;
            margin-top: 2px;
        }
        .meta-box {
            width: 100%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 14px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            font-size: 10px;
            padding: 2px 4px;
            vertical-align: top;
        }
        .summary-cards {
            width: 100%;
            margin-bottom: 14px;
        }
        .summary-card {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 4px;
            padding: 6px 10px;
            text-align: center;
        }
        .summary-card-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1e40af;
        }
        .summary-card-val {
            font-size: 14px;
            font-weight: 900;
            color: #1e3a8a;
            font-family: 'Courier New', Courier, monospace;
        }
        .invoice-card {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .invoice-header {
            background: #f1f5f9;
            border-bottom: 1px solid #cbd5e1;
            padding: 5px 8px;
            font-weight: bold;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th {
            background: #f8fafc;
            border-bottom: 1px solid #cbd5e1;
            padding: 4px 6px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
            color: #475569;
        }
        .items-table td {
            padding: 4px 6px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }
        .font-bold {
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                @if ($settings && $settings->company_logo && file_exists(public_path($settings->company_logo)))
                    @php
                        $logoPath = public_path($settings->company_logo);
                        $logoData = base64_encode(file_get_contents($logoPath));
                        $logoMime = mime_content_type($logoPath) ?: 'image/jpeg';
                    @endphp
                    <img src="data:{{ $logoMime }};base64,{{ $logoData }}" style="max-height: 45px; margin-bottom: 4px;" alt="Logo" />
                @endif
                <div class="company-name">{{ $settings->company_name ?? 'Wholesale Software' }}</div>
                @if ($settings && $settings->company_address)
                    <div style="font-size: 10px; color: #475569;">{{ $settings->company_address }}</div>
                @endif
                @if ($settings && $settings->company_phone)
                    <div style="font-size: 10px; color: #475569;">Phone: {{ $settings->company_phone }}</div>
                @endif
            </td>
            <td style="width: 40%; text-align: right; vertical-align: top;">
                <div class="report-title">Product Sales Report</div>
                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                    Generated: {{ now()->timezone(config('app.timezone'))->format('d-M-Y h:i A') }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Meta Details Box -->
    <div class="meta-box">
        <table class="meta-table">
            <tr>
                <td style="width: 25%;"><strong>Date Range:</strong> {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d-M-Y') : 'All Time' }} to {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d-M-Y') : 'Today' }}</td>
                <td style="width: 30%;"><strong>Customer:</strong> {{ $selectedCustomer ? $selectedCustomer->name : 'All Customers' }}</td>
                <td style="width: 25%;"><strong>Branch / Warehouse:</strong> {{ $selectedWarehouse ? $selectedWarehouse->name : 'All Branches' }}</td>
                <td style="width: 20%; text-align: right;"><strong>Invoices Count:</strong> {{ number_format($totalInvoices) }}</td>
            </tr>
            @if ($searchProduct)
                <tr>
                    <td colspan="4"><strong>Product Filter:</strong> "{{ $searchProduct }}"</td>
                </tr>
            @endif
        </table>
    </div>

    <!-- Summary Metrics -->
    <table class="summary-cards" style="width: 100%; border-collapse: separate; border-spacing: 6px 0;">
        <tr>
            <td style="width: 33.3%;">
                <div class="summary-card">
                    <div class="summary-card-title">Total Invoices</div>
                    <div class="summary-card-val">{{ number_format($totalInvoices) }}</div>
                </div>
            </td>
            <td style="width: 33.3%;">
                <div class="summary-card" style="background: #ecfdf5; border-color: #a7f3d0;">
                    <div class="summary-card-title" style="color: #065f46;">Total Quantity Sold</div>
                    <div class="summary-card-val" style="color: #047857;">{{ number_format($totalQty, 2) }} units</div>
                </div>
            </td>
            <td style="width: 33.3%;">
                <div class="summary-card" style="background: #fdf2f8; border-color: #fbcfe8;">
                    <div class="summary-card-title" style="color: #9d174d;">Total Sales Turnover</div>
                    <div class="summary-card-val" style="color: #be185d;">{{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($totalAmount, 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Invoices Breakdown -->
    @forelse ($groupedByInvoice as $saleId => $items)
        @php
            $first = $items->first();
            $invSubtotal = $items->sum('item_total');
            $invQty = $items->sum('qty');
        @endphp
        <div class="invoice-card">
            <div class="invoice-header">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 25%; font-size: 11px;">
                            <span style="background: #2563eb; color: #fff; padding: 1px 6px; border-radius: 3px; font-family: monospace;">
                                INV-{{ $saleId }}
                            </span>
                        </td>
                        <td style="width: 35%; font-size: 11px; text-transform: uppercase;">
                            <strong>Customer:</strong> {{ $first->customer_name ?? 'Walk-In' }}
                            @if ($first->customer_phone) ({{ $first->customer_phone }}) @endif
                        </td>
                        <td style="width: 20%; font-size: 10px;">
                            <strong>Date:</strong> {{ \Carbon\Carbon::parse($first->sale_date)->format('d-M-Y') }}
                        </td>
                        <td style="width: 20%; text-align: right; font-size: 11px;">
                            <strong>Bill Total:</strong> <span class="font-mono">{{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($first->sale_grand_total ?? $invSubtotal, 2) }}</span>
                        </td>
                    </tr>
                </table>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 6%; text-align: center;">#</th>
                        <th style="width: 44%;">Product Name & SKU</th>
                        <th style="width: 16%; text-align: center;">Sold Qty</th>
                        <th style="width: 17%; text-align: right;">Rate ({{ $settings->currency_symbol ?? 'Rs' }})</th>
                        <th style="width: 17%; text-align: right;">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $idx => $item)
                        <tr>
                            <td class="text-center font-mono" style="color: #64748b;">{{ $idx + 1 }}</td>
                            <td>
                                <strong>{{ $item->product_name }}</strong>
                                @if ($item->product_sku)
                                    <span style="font-size: 9px; color: #64748b; font-family: monospace;">(SKU: {{ $item->product_sku }})</span>
                                @endif
                            </td>
                            <td class="text-center font-mono font-bold">{{ number_format($item->qty, 2) }}</td>
                            <td class="text-right font-mono">{{ number_format($item->price, 2) }}</td>
                            <td class="text-right font-mono font-bold">{{ number_format($item->item_total, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr style="background: #f8fafc; font-weight: bold;">
                        <td colspan="2" class="text-right" style="font-size: 9px; text-transform: uppercase; color: #64748b;">Items Subtotal:</td>
                        <td class="text-center font-mono" style="color: #047857;">{{ number_format($invQty, 2) }}</td>
                        <td></td>
                        <td class="text-right font-mono">{{ number_format($invSubtotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @empty
        <div style="text-align: center; padding: 30px; color: #94a3b8;">
            No sales records found for this period.
        </div>
    @endforelse

    <!-- Report Grand Footer -->
    <table style="width: 100%; margin-top: 15px; border-top: 2px solid #333; padding-top: 8px;">
        <tr>
            <td style="font-size: 11px; font-weight: bold; text-transform: uppercase;">
                Report Grand Total ({{ number_format($totalInvoices) }} Invoices, {{ number_format($totalQty, 2) }} Units)
            </td>
            <td style="text-align: right; font-size: 14px; font-weight: 900; font-family: 'Courier New', Courier, monospace;">
                {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($totalAmount, 2) }}
            </td>
        </tr>
    </table>

    <div class="footer">
        {{ $settings->company_name ?? 'Wholesale Software' }} — Confidential Sales Report — Page 1
    </div>

</body>
</html>
