<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargo Invoice #CRG-{{ str_pad($cargoCharge->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1f2937;
            background: #f3f4f6;
            margin: 0;
            padding: 20px;
            font-size: 13px;
            line-height: 1.5;
        }

        .invoice-card {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 35px 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 8px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
        }

        .company-name {
            font-size: 22px;
            font-weight: 900;
            text-transform: uppercase;
            color: #111827;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }

        .company-meta {
            font-size: 11px;
            color: #4b5563;
            line-height: 1.6;
        }

        .invoice-badge-box {
            text-align: right;
            vertical-align: top;
        }

        .invoice-title {
            font-size: 20px;
            font-weight: 900;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .invoice-meta {
            font-size: 12px;
            color: #374151;
            line-height: 1.6;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            margin-top: 6px;
        }

        .badge-paid {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .badge-unpaid {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .details-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .details-box {
            width: 48%;
            vertical-align: top;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 14px 16px;
        }

        .details-box-title {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-bottom: 8px;
            border-bottom: 1px dashed #d1d5db;
            padding-bottom: 4px;
        }

        .party-name {
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            color: #111827;
            margin-bottom: 4px;
        }

        .party-info {
            font-size: 11px;
            color: #4b5563;
            line-height: 1.5;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .items-table th {
            background-color: #f3f4f6;
            border-bottom: 2px solid #d1d5db;
            padding: 10px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #374151;
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }

        .items-table th.text-right,
        .items-table td.text-right {
            text-align: right;
        }

        .total-section {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .total-box {
            width: 45%;
            margin-left: auto;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px 16px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 12px;
        }

        .total-row.grand-total {
            border-top: 2px solid #d1d5db;
            margin-top: 6px;
            padding-top: 8px;
            font-size: 16px;
            font-weight: 900;
            color: #4f46e5;
        }

        .signatures {
            width: 100%;
            border-collapse: collapse;
            margin-top: 45px;
            margin-bottom: 20px;
        }

        .signature-col {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 40px;
        }

        .signature-line {
            border-top: 1px solid #9ca3af;
            padding-top: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #4b5563;
        }

        .footer-note {
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
            margin-top: 25px;
        }

        .no-print-bar {
            max-width: 800px;
            margin: 0 auto 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.15s ease;
        }

        .btn-primary {
            background: #4f46e5;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #4338ca;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .invoice-card {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }

            .no-print-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Controls Bar -->
    <div class="no-print-bar">
        <a href="{{ route('cargo-charges.show', $cargoCharge) }}" class="btn btn-secondary">
            &larr; Back to Voucher
        </a>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-primary">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print Cargo Invoice
            </button>
        </div>
    </div>

    <!-- Main Printable Invoice Card -->
    <div class="invoice-card">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td style="width: 55%; vertical-align: top;">
                    <div class="company-name">{{ $settings->company_name ?? 'StockPOS AI' }}</div>
                    <div class="company-meta">
                        @if ($settings->company_address)
                            <div>{{ $settings->company_address }}</div>
                        @endif
                        <div>
                            @php
                                $phones = array_filter([
                                    $settings->company_phone ?? null,
                                    $settings->company_phone_2 ?? null,
                                    $settings->company_phone_3 ?? null
                                ]);
                            @endphp
                            @if (!empty($phones))
                                Tel: {{ implode(' | ', $phones) }}
                            @endif
                        </div>
                        @if ($settings->company_email)
                            <div>Email: {{ $settings->company_email }}</div>
                        @endif
                    </div>
                </td>
                <td class="invoice-badge-box">
                    <div class="invoice-title">Cargo Freight Invoice</div>
                    <div class="invoice-meta">
                        <div>Voucher #: <strong>#CRG-{{ str_pad($cargoCharge->id, 5, '0', STR_PAD_LEFT) }}</strong></div>
                        <div>Date: <strong>{{ $cargoCharge->created_at->format('d M, Y') }}</strong></div>
                        @if ($cargoCharge->purchase_id)
                            <div>Purchase Ref: <strong>PUR-#{{ $cargoCharge->purchase_id }}</strong></div>
                        @endif
                    </div>
                    <div>
                        @if ($cargoCharge->is_paid)
                            <span class="badge badge-paid">&bull; Paid</span>
                        @else
                            <span class="badge badge-unpaid">&bull; Unpaid (Ledger Payable)</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <!-- Details Grid: Cargo Transporter vs Linked Shipment -->
        <table class="details-grid">
            <tr>
                <td class="details-box">
                    <div class="details-box-title">Cargo Transporter / Agent</div>
                    <div class="party-name">{{ $cargoCharge->cargoParty->name ?? 'N/A' }}</div>
                    <div class="party-info">
                        <div>Phone: {{ $cargoCharge->cargoParty->phone ?? 'N/A' }}</div>
                        @if ($cargoCharge->cargoParty->address)
                            <div>Address: {{ $cargoCharge->cargoParty->address }}</div>
                        @endif
                        <div>Account Type: Cargo Agency / Transporter</div>
                    </div>
                </td>
                <td style="width: 4%;"></td>
                <td class="details-box">
                    <div class="details-box-title">Shipment / Origin Details</div>
                    @if ($cargoCharge->purchase)
                        <div class="party-name">{{ $cargoCharge->purchase->party->name ?? 'Purchase Vendor' }}</div>
                        <div class="party-info">
                            <div>Bill Reference: PUR-#{{ $cargoCharge->purchase_id }}</div>
                            <div>Bill Date: {{ $cargoCharge->purchase->date }}</div>
                            <div>Vendor Phone: {{ $cargoCharge->purchase->party->phone ?? 'N/A' }}</div>
                        </div>
                    @else
                        <div class="party-name">Direct Cargo Dispatch</div>
                        <div class="party-info">
                            <div>Reference: Independent Shipment</div>
                            <div>Recorded Date: {{ $cargoCharge->created_at->format('d M, Y') }}</div>
                            <div>Type: Standalone Freight Service</div>
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        <!-- Freight Charges Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 8%;">#</th>
                    <th>Freight Service Description</th>
                    <th class="text-right" style="width: 20%;">Total Weight</th>
                    <th class="text-right" style="width: 22%;">Freight Rate / kg</th>
                    <th class="text-right" style="width: 24%;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="color: #9ca3af; font-family: monospace;">1</td>
                    <td>
                        <strong>Cargo Transport & Freight Charges</strong>
                        <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">
                            @if ($cargoCharge->purchase_id)
                                Consignment freight for Purchase Order #{{ $cargoCharge->purchase_id }}
                            @else
                                Direct consignment freight via {{ $cargoCharge->cargoParty->name ?? 'Transporter' }}
                            @endif
                        </div>
                    </td>
                    <td class="text-right" style="font-weight: 700;">
                        {{ number_format($cargoCharge->total_weight, 2) }} kg
                    </td>
                    <td class="text-right" style="font-family: monospace; font-weight: 700;">
                        {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($cargoCharge->rate_per_kg, 2) }}
                    </td>
                    <td class="text-right" style="font-family: monospace; font-weight: 900; font-size: 14px; color: #111827;">
                        {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($cargoCharge->total_amount, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Linked Purchase Goods (If available) -->
        @if ($cargoCharge->purchase && $cargoCharge->purchase->items->count() > 0)
            <div style="margin-top: 15px; margin-bottom: 25px;">
                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #4b5563; margin-bottom: 8px;">
                    Consignment Manifest (Items in Bill #PUR-{{ $cargoCharge->purchase_id }}):
                </div>
                <table class="items-table" style="font-size: 11px;">
                    <thead>
                        <tr style="background: #f9fafb;">
                            <th style="padding: 6px 10px; font-size: 10px;">Item</th>
                            <th style="padding: 6px 10px; font-size: 10px;">SKU</th>
                            <th class="text-right" style="padding: 6px 10px; font-size: 10px;">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cargoCharge->purchase->items as $item)
                            <tr>
                                <td style="padding: 6px 10px;">{{ $item->product->name ?? 'Product' }}</td>
                                <td style="padding: 6px 10px; color: #6b7280; font-family: monospace;">{{ $item->product->sku ?? '-' }}</td>
                                <td class="text-right" style="padding: 6px 10px; font-weight: 700;">{{ number_format($item->quantity) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Calculation & Total Section -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
            <tr>
                <td style="width: 50%; vertical-align: top; padding-right: 20px;">
                    <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; font-size: 11px; color: #4b5563;">
                        <strong>Accounting Notes:</strong>
                        <div style="margin-top: 4px;">
                            • Freight charges have been recorded in the company books under <strong>Cargo Expense</strong>.<br>
                            • Transporter ledger credited under <strong>Accounts Payable ({{ $cargoCharge->cargoParty->name ?? '' }})</strong>.
                        </div>
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <div class="total-box" style="width: 100%; box-sizing: border-box;">
                        <div class="total-row">
                            <span style="color: #6b7280;">Billed Weight:</span>
                            <span style="font-weight: 700;">{{ number_format($cargoCharge->total_weight, 2) }} kg</span>
                        </div>
                        <div class="total-row">
                            <span style="color: #6b7280;">Rate per kg:</span>
                            <span style="font-family: monospace; font-weight: 700;">{{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($cargoCharge->rate_per_kg, 2) }}</span>
                        </div>
                        <div class="total-row grand-total">
                            <span>Total Freight Due:</span>
                            <span style="font-family: monospace;">{{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($cargoCharge->total_amount, 2) }}</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Signatures -->
        <table class="signatures">
            <tr>
                <td class="signature-col">
                    <div class="signature-line">Prepared & Authorized By</div>
                </td>
                <td class="signature-col">
                    <div class="signature-line">Cargo Transporter / Received By</div>
                </td>
            </tr>
        </table>

        <!-- Footer -->
        <div class="footer-note">
            <div>{{ $settings->invoice_footer_note ?? 'Thank you for your business. Computer generated invoice.' }}</div>
            <div style="margin-top: 4px; font-weight: 700;">Powered by Invenco Cloud Software</div>
        </div>
    </div>

</body>
</html>
