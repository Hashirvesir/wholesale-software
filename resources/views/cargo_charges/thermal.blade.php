<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargo Receipt - {{ $cargoCharge->id }}</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            width: 72mm;
            margin: 0;
            padding: 3mm;
            background: #fff;
            color: #000;
            font-size: 13px;
            line-height: 1.25;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: 900;
        }

        .mb-1 {
            margin-bottom: 3px;
        }

        .mb-2 {
            margin-bottom: 6px;
        }

        .border-b {
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }

        .border-b-dotted {
            border-bottom: 1px dotted #000;
            padding-bottom: 4px;
            margin-bottom: 4px;
        }

        .header {
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 17px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .receipt-title {
            font-size: 14px;
            font-weight: 900;
            border: 2px solid #000;
            padding: 4px;
            margin: 8px 0;
            text-transform: uppercase;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 5px 0;
        }

        .items-table th {
            border-bottom: 1px solid #000;
            text-align: left;
            padding: 2px 0;
            font-size: 10px;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        @media print {
            body {
                width: 72mm;
                padding: 1mm;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <!-- Screen Action Buttons -->
    <div class="no-print" style="background: #fdf6e3; padding: 10px; margin-bottom: 10px; border: 1px solid #eee; text-align: center;">
        <button onclick="window.print()" style="padding: 6px 18px; font-weight: bold; background: #4f46e5; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Print Receipt</button>
        <button onclick="window.history.back()" style="padding: 6px 15px; margin-left: 6px; cursor: pointer;">Back</button>
    </div>

    <!-- Header -->
    <div class="header text-center">
        <div class="company-name">{{ $settings->company_name ?? 'StockPOS AI' }}</div>
        @if ($settings->company_address)
            <div class="mb-1" style="font-size: 11px;">{{ $settings->company_address }}</div>
        @endif
        @php
            $phones = array_filter([
                $settings->company_phone ?? null,
                $settings->company_phone_2 ?? null,
                $settings->company_phone_3 ?? null
            ]);
        @endphp
        @if (!empty($phones))
            <div class="mb-1" style="font-size: 11px; font-weight: bold;">Tel: {{ implode(' | ', $phones) }}</div>
        @endif
        <div class="border-b"></div>
    </div>

    <!-- Receipt Title -->
    <div class="text-center receipt-title">
        CARGO FREIGHT VOUCHER
    </div>

    <!-- Meta Details -->
    <div class="mb-2">
        <div class="row">
            <span><strong>Date:</strong></span>
            <span>{{ $cargoCharge->created_at->timezone(config('app.timezone'))->format('d-M-Y h:i A') }}</span>
        </div>
        <div class="row">
            <span><strong>Voucher #:</strong></span>
            <span class="font-bold">#CRG-{{ str_pad($cargoCharge->id, 5, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="row">
            <span><strong>Cargo Party:</strong></span>
            <span class="font-bold uppercase">{{ $cargoCharge->cargoParty->name ?? 'N/A' }}</span>
        </div>
        @if ($cargoCharge->cargoParty && $cargoCharge->cargoParty->phone)
            <div class="row">
                <span><strong>Party Tel:</strong></span>
                <span>{{ $cargoCharge->cargoParty->phone }}</span>
            </div>
        @endif
        <div class="row">
            <span><strong>Shipment Origin:</strong></span>
            <span>
                @if ($cargoCharge->purchase_id)
                    PUR-#{{ $cargoCharge->purchase_id }} ({{ $cargoCharge->purchase->party->name ?? 'Vendor' }})
                @else
                    Direct Cargo Dispatch
                @endif
            </span>
        </div>
        <div class="border-b"></div>
    </div>

    <!-- Weight & Rate Breakdown -->
    <div class="mb-2">
        <div class="row">
            <span>Total Weight:</span>
            <span class="font-bold">{{ number_format($cargoCharge->total_weight, 2) }} kg</span>
        </div>
        <div class="row">
            <span>Rate per kg:</span>
            <span>{{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($cargoCharge->rate_per_kg, 2) }}</span>
        </div>
        <div class="row font-bold" style="border-top: 1px solid #000; padding-top: 4px; margin-top: 4px; font-size: 15px;">
            <span>TOTAL FREIGHT:</span>
            <span>{{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($cargoCharge->total_amount, 2) }}</span>
        </div>
        <div class="row" style="margin-top: 4px;">
            <span>Payment Status:</span>
            <span class="font-bold">
                @if ($cargoCharge->is_paid)
                    [ PAID ]
                @else
                    [ UNPAID / DUE ON KHATA ]
                @endif
            </span>
        </div>
    </div>

    @if ($cargoCharge->cargoParty)
        <div class="border-b-dotted"></div>
        <div class="mb-2 font-bold" style="font-size: 12px;">
            <div class="row">
                <span>Party Current Balance:</span>
                <span>{{ $settings->currency_symbol ?? 'Rs' }} {{ number_format(abs($currentBalance), 2) }} {{ $currentBalance >= 0 ? '(Rec)' : '(Pay)' }}</span>
            </div>
        </div>
    @endif

    <!-- Items in Consignment Manifest if linked to purchase -->
    @if ($cargoCharge->purchase && $cargoCharge->purchase->items->count() > 0)
        <div class="border-b"></div>
        <div style="font-size: 11px; font-weight: bold; margin-bottom: 2px;">CONSIGNMENT ITEMS:</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-right">Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cargoCharge->purchase->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? 'Item' }}</td>
                        <td class="text-right font-bold">{{ number_format($item->quantity) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="border-b"></div>

    <!-- Signatures -->
    <div style="display: flex; justify-content: space-between; margin-top: 25px; font-size: 10px; font-weight: bold; text-align: center;">
        <div style="width: 45%;">
            ____________________<br>
            Authorized Sign
        </div>
        <div style="width: 45%;">
            ____________________<br>
            Cargo Agent Sign
        </div>
    </div>

    <!-- Footer Note -->
    <div class="text-center" style="margin-top: 15px; font-size: 11px; font-weight: bold;">
        {!! nl2br(e($settings->invoice_footer_note ?? 'THANK YOU FOR YOUR SERVICE!')) !!}
        <div style="margin-top: 4px; font-size: 10px; color: #555;">
            Invenco Cloud Software
        </div>
    </div>

</body>

</html>
