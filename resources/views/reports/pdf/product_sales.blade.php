<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Product Sales Report</title>
    <style>
        @page {
            margin: 10mm 8mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #222;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
        }
        .report-title {
            font-size: 15px;
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
            padding: 6px 8px;
            margin-bottom: 10px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            font-size: 9px;
            padding: 2px 4px;
            vertical-align: top;
        }
        .summary-cards {
            width: 100%;
            margin-bottom: 10px;
        }
        .summary-card {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 4px;
            padding: 5px 6px;
            text-align: center;
        }
        .summary-card-title {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1e40af;
        }
        .summary-card-val {
            font-size: 12px;
            font-weight: 900;
            color: #1e3a8a;
            font-family: 'Courier New', Courier, monospace;
        }
        .invoice-card {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        .invoice-header {
            background: #f1f5f9;
            border-bottom: 1px solid #cbd5e1;
            padding: 4px 6px;
            font-weight: bold;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th {
            background: #f8fafc;
            border-bottom: 1px solid #cbd5e1;
            padding: 4px 5px;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
            color: #475569;
        }
        .items-table td {
            padding: 3.5px 5px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 9px;
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
        .badge-paid {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-partial {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fcd34d;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-unpaid {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8.5px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
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
                    <img src="data:{{ $logoMime }};base64,{{ $logoData }}" style="max-height: 40px; margin-bottom: 3px;" alt="Logo" />
                @endif
                <div class="company-name">{{ $settings->company_name ?? 'Wholesale Software' }}</div>
                @if ($settings && $settings->company_address)
                    <div style="font-size: 9px; color: #475569;">{{ $settings->company_address }}</div>
                @endif
                @if ($settings && $settings->company_phone)
                    <div style="font-size: 9px; color: #475569;">Phone: {{ $settings->company_phone }}</div>
                @endif
            </td>
            <td style="width: 40%; text-align: right; vertical-align: top;">
                <div class="report-title">Product Sales Report</div>
                <div style="font-size: 9px; color: #64748b; margin-top: 2px;">
                    Generated: {{ now()->timezone(config('app.timezone'))->format('d-M-Y h:i A') }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Meta Details Box -->
    <div class="meta-box">
        <table class="meta-table">
            <tr>
                <td style="width: 28%;"><strong>Date Range:</strong> {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d-M-Y') : 'All Time' }} to {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d-M-Y') : 'Today' }}</td>
                <td style="width: 30%;"><strong>Customer:</strong> {{ $selectedCustomer ? $selectedCustomer->name : 'All Customers' }}</td>
                <td style="width: 24%;"><strong>Branch:</strong> {{ $selectedWarehouse ? $selectedWarehouse->name : 'All Branches' }}</td>
                <td style="width: 18%; text-align: right;"><strong>Invoices:</strong> {{ number_format($totalInvoices) }}</td>
            </tr>
            @if ($searchProduct)
                <tr>
                    <td colspan="4"><strong>Product Filter:</strong> "{{ $searchProduct }}"</td>
                </tr>
            @endif
        </table>
    </div>

    <!-- Summary Metrics (4 Cards) -->
    <table class="summary-cards" style="width: 100%; border-collapse: separate; border-spacing: 4px 0;">
        <tr>
            <td style="width: 25%;">
                <div class="summary-card">
                    <div class="summary-card-title">Total Invoices</div>
                    <div class="summary-card-val">{{ number_format($totalInvoices) }}</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="summary-card" style="background: #f0fdf4; border-color: #bbf7d0;">
                    <div class="summary-card-title" style="color: #166534;">Total Units Sold</div>
                    <div class="summary-card-val" style="color: #15803d;">{{ number_format($totalQty, 2) }}</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="summary-card" style="background: #fdf2f8; border-color: #fbcfe8;">
                    <div class="summary-card-title" style="color: #9d174d;">Total Sales Value</div>
                    <div class="summary-card-val" style="color: #be185d;">{{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($totalAmount, 2) }}</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="summary-card" style="background: #fff7ed; border-color: #fed7aa;">
                    <div class="summary-card-title" style="color: #9a3412;">Outstanding Due</div>
                    <div class="summary-card-val" style="color: #c2410c;">{{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($totalDue ?? 0, 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Invoices Breakdown -->
    @forelse ($groupedByInvoice as $saleId => $items)
        @php
            $first = $items->first();
            $invSubtotal = (float)$items->sum('item_total');
            $invQty = (float)$items->sum('qty');
            $grandTotal = (float)($first->sale_grand_total ?? $invSubtotal);
            $paidAmount = (float)($first->sale_paid_amount ?? 0);
            $dueBalance = max(0, $grandTotal - $paidAmount);
            $isPaid = $dueBalance <= 0.01;
            $isPartial = $paidAmount > 0.01 && $dueBalance > 0.01;
        @endphp
        <div class="invoice-card">
            <div class="invoice-header">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 18%; font-size: 10px;">
                            <span style="background: #2563eb; color: #fff; padding: 1px 5px; border-radius: 2px; font-family: monospace;">
                                INV-{{ $saleId }}
                            </span>
                            @if ($isPaid)
                                <span class="badge-paid">Paid</span>
                            @elseif ($isPartial)
                                <span class="badge-partial">Partial</span>
                            @else
                                <span class="badge-unpaid">Unpaid</span>
                            @endif
                        </td>
                        <td style="width: 34%; font-size: 9.5px; text-transform: uppercase;">
                            <strong>Customer:</strong> {{ $first->customer_name ?? 'Walk-In' }}
                            @if ($first->customer_phone) ({{ $first->customer_phone }}) @endif
                        </td>
                        <td style="width: 16%; font-size: 9px;">
                            <strong>Date:</strong> {{ \Carbon\Carbon::parse($first->sale_date)->format('d-M-Y') }}
                        </td>
                        <td style="width: 32%; text-align: right; font-size: 9.5px;">
                            <strong>Total:</strong> <span class="font-mono">{{ number_format($grandTotal, 2) }}</span>
                            @if ($paidAmount > 0.01)
                                | <span style="color: #15803d;"><strong>Paid:</strong> <span class="font-mono">{{ number_format($paidAmount, 2) }}</span></span>
                            @endif
                            @if ($dueBalance > 0.01)
                                | <span style="color: #b91c1c;"><strong>Due:</strong> <span class="font-mono">{{ number_format($dueBalance, 2) }}</span></span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">#</th>
                        <th style="width: 47%;">Product Name & SKU</th>
                        <th style="width: 16%; text-align: center;">Sold Qty</th>
                        <th style="width: 16%; text-align: right;">Rate ({{ $settings->currency_symbol ?? 'Rs' }})</th>
                        <th style="width: 16%; text-align: right;">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $idx => $item)
                        <tr>
                            <td class="text-center font-mono" style="color: #64748b;">{{ $idx + 1 }}</td>
                            <td>
                                <strong>{{ $item->product_name }}</strong>
                                @if ($item->product_sku)
                                    <span style="font-size: 8px; color: #64748b; font-family: monospace;">(SKU: {{ $item->product_sku }})</span>
                                @endif
                            </td>
                            <td class="text-center font-mono font-bold">{{ number_format($item->qty, 2) }}</td>
                            <td class="text-right font-mono">{{ number_format($item->price, 2) }}</td>
                            <td class="text-right font-mono font-bold">{{ number_format($item->item_total, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr style="background: #f8fafc; font-weight: bold;">
                        <td colspan="2" class="text-right" style="font-size: 8px; text-transform: uppercase; color: #64748b;">Items Subtotal:</td>
                        <td class="text-center font-mono" style="color: #047857;">{{ number_format($invQty, 2) }}</td>
                        <td></td>
                        <td class="text-right font-mono">{{ number_format($invSubtotal, 2) }}</td>
                    </tr>
                    <tr style="background: #f1f5f9; font-size: 8.5px;">
                        <td colspan="2">
                            <strong>Payment Status:</strong>
                            @if ($isPaid)
                                <span style="color: #15803d; font-weight: bold;">Fully Paid</span>
                            @elseif ($isPartial)
                                <span style="color: #b45309; font-weight: bold;">Partially Paid</span>
                            @else
                                <span style="color: #b91c1c; font-weight: bold;">Unpaid</span>
                            @endif
                        </td>
                        <td colspan="2" class="text-right uppercase" style="color: #64748b;">
                            @if ($dueBalance > 0.01)
                                Remaining Due:
                            @else
                                Settled:
                            @endif
                        </td>
                        <td class="text-right font-mono font-bold" style="{{ $dueBalance > 0.01 ? 'color: #b91c1c;' : 'color: #15803d;' }}">
                            @if ($dueBalance > 0.01)
                                {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($dueBalance, 2) }}
                                @if ($paidAmount > 0.01)
                                    <span style="font-size: 7.5px; color: #15803d;">(Paid: {{ number_format($paidAmount, 2) }})</span>
                                @endif
                            @else
                                {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($grandTotal, 2) }}
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    @empty
        <div style="text-align: center; padding: 25px; color: #94a3b8;">
            No sales records found for this period.
        </div>
    @endforelse

    <!-- Report Grand Footer -->
    <table style="width: 100%; margin-top: 10px; border-top: 2px solid #333; padding-top: 6px;">
        <tr>
            <td style="font-size: 9.5px; font-weight: bold; text-transform: uppercase;">
                Report Grand Total ({{ number_format($totalInvoices) }} Invoices, {{ number_format($totalQty, 2) }} Units)
            </td>
            <td style="text-align: right; font-size: 12px; font-weight: 900; font-family: 'Courier New', Courier, monospace;">
                {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($totalAmount, 2) }}
            </td>
        </tr>
    </table>

    <div class="footer">
        {{ $settings->company_name ?? 'Wholesale Software' }} — Confidential Sales Report
    </div>

</body>
</html>
