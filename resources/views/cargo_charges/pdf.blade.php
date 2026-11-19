<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Cargo Voucher - #CRG-{{ str_pad($cargoCharge->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            margin: 0.5in 0.4in;
        }

        body {
            font-family: sans-serif;
            font-size: 13px;
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
            border-bottom: 2px solid #ddd;
            padding-bottom: 15px;
        }

        .company-info {
            float: left;
            width: 60%;
        }

        .receipt-title {
            float: right;
            width: 40%;
            text-align: right;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .receipt-heading {
            font-size: 22px;
            font-weight: bold;
            color: #4f46e5;
            text-transform: uppercase;
        }

        .details-box {
            width: 100%;
            clear: both;
            margin-bottom: 25px;
        }

        .left-box {
            float: left;
            width: 48%;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 12px;
            border-radius: 6px;
        }

        .right-box {
            float: right;
            width: 48%;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 12px;
            border-radius: 6px;
        }

        .box-label {
            font-weight: bold;
            text-transform: uppercase;
            color: #6b7280;
            font-size: 10px;
            margin-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 3px;
        }

        .party-name {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .table-box {
            width: 100%;
            clear: both;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.data-table th {
            background-color: #f3f4f6;
            border-bottom: 2px solid #d1d5db;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        table.data-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }

        .text-right {
            text-align: right;
        }

        .amount-highlight {
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            color: #4f46e5;
            background-color: #f9fafb;
            border: 2px solid #e5e7eb;
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
            clear: both;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-paid {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-unpaid {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .signatures {
            width: 100%;
            margin-top: 50px;
            clear: both;
        }

        .sig-col {
            width: 50%;
            float: left;
            text-align: center;
        }

        .sig-line {
            width: 80%;
            margin: 0 auto;
            border-top: 1px solid #999;
            padding-top: 5px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            color: #888;
            font-size: 10px;
            border-top: 1px solid #eee;
            padding-top: 10px;
            clear: both;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header clearfix">
            <div class="company-info">
                <div class="company-name">{{ $settings->company_name ?? 'StockPOS AI' }}</div>
                <div>{{ $settings->company_address }}</div>
                @php
                    $phones = array_filter([
                        $settings->company_phone ?? null,
                        $settings->company_phone_2 ?? null,
                        $settings->company_phone_3 ?? null
                    ]);
                @endphp
                @if (!empty($phones))
                    <div>Tel: {{ implode(' | ', $phones) }}</div>
                @endif
                @if ($settings->company_email)
                    <div>Email: {{ $settings->company_email }}</div>
                @endif
            </div>
            <div class="receipt-title">
                <div class="receipt-heading">CARGO VOUCHER</div>
                <div>Voucher #: <strong>#CRG-{{ str_pad($cargoCharge->id, 5, '0', STR_PAD_LEFT) }}</strong></div>
                <div>Date: <strong>{{ $cargoCharge->created_at->format('d M, Y') }}</strong></div>
                <div style="margin-top: 6px;">
                    @if ($cargoCharge->is_paid)
                        <span class="badge badge-paid">PAID</span>
                    @else
                        <span class="badge badge-unpaid">UNPAID / ON KHATA</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Party & Shipment Details -->
        <div class="details-box clearfix">
            <div class="left-box">
                <div class="box-label">Cargo Agent / Transporter</div>
                <div class="party-name">{{ $cargoCharge->cargoParty->name ?? 'N/A' }}</div>
                <div>Phone: {{ $cargoCharge->cargoParty->phone ?? 'N/A' }}</div>
                @if ($cargoCharge->cargoParty->address)
                    <div>Address: {{ $cargoCharge->cargoParty->address }}</div>
                @endif
                @if ($cargoCharge->cargoParty)
                    <div style="margin-top: 6px; font-weight: bold; font-size: 11px;">
                        Current Ledger Balance: {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format(abs($currentBalance), 2) }}
                        {{ $currentBalance >= 0 ? '(Receivable)' : '(Payable)' }}
                    </div>
                @endif
            </div>

            <div class="right-box">
                <div class="box-label">Shipment Origin / Bill</div>
                @if ($cargoCharge->purchase)
                    <div class="party-name">{{ $cargoCharge->purchase->party->name ?? 'Supplier' }}</div>
                    <div>Purchase Reference: <strong>PUR-#{{ $cargoCharge->purchase_id }}</strong></div>
                    <div>Purchase Date: {{ $cargoCharge->purchase->date }}</div>
                    <div>Supplier Contact: {{ $cargoCharge->purchase->party->phone ?? 'N/A' }}</div>
                @else
                    <div class="party-name">Direct Cargo Dispatch</div>
                    <div>Reference: Standalone Freight Shipment</div>
                    <div>Date Recorded: {{ $cargoCharge->created_at->format('d M, Y') }}</div>
                @endif
            </div>
        </div>

        <!-- Freight Breakdown Table -->
        <div class="table-box">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="8%">#</th>
                        <th>Freight Service Description</th>
                        <th width="20%" class="text-right">Billed Weight</th>
                        <th width="20%" class="text-right">Rate / kg</th>
                        <th width="24%" class="text-right">Total Freight</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>
                            <strong>Cargo Transport & Freight Charges</strong>
                            <div style="font-size: 10px; color: #666;">
                                @if ($cargoCharge->purchase_id)
                                    Consignment freight for Purchase Bill #{{ $cargoCharge->purchase_id }}
                                @else
                                    Direct freight dispatch via {{ $cargoCharge->cargoParty->name ?? 'Transporter' }}
                                @endif
                            </div>
                        </td>
                        <td class="text-right" style="font-weight: bold;">
                            {{ number_format($cargoCharge->total_weight, 2) }} kg
                        </td>
                        <td class="text-right">
                            {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($cargoCharge->rate_per_kg, 2) }}
                        </td>
                        <td class="text-right" style="font-weight: bold; font-size: 14px;">
                            {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($cargoCharge->total_amount, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Highlight Total -->
        <div class="amount-highlight">
            TOTAL FREIGHT: {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($cargoCharge->total_amount, 2) }}
        </div>

        <!-- Consignment Manifest if linked to purchase -->
        @if ($cargoCharge->purchase && $cargoCharge->purchase->items->count() > 0)
            <div style="margin-top: 15px; margin-bottom: 25px; clear: both;">
                <div style="font-weight: bold; text-transform: uppercase; font-size: 11px; margin-bottom: 5px;">
                    Consignment Manifest (Items in Bill #PUR-{{ $cargoCharge->purchase_id }}):
                </div>
                <table class="data-table" style="font-size: 11px;">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>SKU</th>
                            <th class="text-right" width="20%">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cargoCharge->purchase->items as $item)
                            <tr>
                                <td>{{ $item->product->name ?? 'Product' }}</td>
                                <td style="color: #666;">{{ $item->product->sku ?? '-' }}</td>
                                <td class="text-right" style="font-weight: bold;">{{ number_format($item->quantity) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Signatures -->
        <div class="signatures clearfix">
            <div class="sig-col">
                <div class="sig-line">Prepared & Authorized By</div>
            </div>
            <div class="sig-col">
                <div class="sig-line">Cargo Transporter / Received By</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            {{ $settings->invoice_footer_note ?? 'Thank you for your business. Computer-generated cargo voucher.' }}<br>
            Powered by Invenco Cloud Software
        </div>
    </div>
</body>
</html>
