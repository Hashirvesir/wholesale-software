<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Receipt - {{ $sale->id }}</title>
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
            line-height: 1.2;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: 800;
            /* Extra bold for thermal printers */
        }

        .mb-1 {
            margin-bottom: 3px;
        }

        .mb-2 {
            margin-bottom: 6px;
        }

        .border-b {
            border-bottom: 2px solid #000;
            /* Darker, thicker lines */
            padding-bottom: 5px;
            margin-bottom: 5px;
        }

        .header {
            margin-bottom: 12px;
        }

        .company-name {
            font-size: 18px;
            font-weight: 900;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th {
            border-bottom: 2px solid #000;
            text-align: left;
            padding: 4px 0;
            font-size: 12px;
            font-weight: 900;
        }

        td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 12px;
            color: #000;
        }

        .totals {
            margin-top: 8px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .total-row.font-bold {
            font-size: 14px;
            border-top: 1px solid #000;
            padding-top: 2px;
        }

        @media print {
            body {
                width: 72mm;
                padding: 1mm;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="no-print" style="background: #fdf6e3; padding: 10px; margin-bottom: 10px; border: 1px solid #eee;">
        <button onclick="window.print()" style="padding: 5px 15px; cursor: pointer;">Print Receipt</button>
        <button onclick="window.history.back()" style="padding: 5px 15px; cursor: pointer;">Back</button>
    </div>

    <div class="header text-center">
        <div class="company-name">{{ $settings->company_name }}</div>
        <div class="mb-1">{{ $settings->company_address }}</div>
        <div class="mb-1">Phone: {{ $settings->company_phone }}</div>
        <div class="border-b"></div>
    </div>

    <div class="mb-2">
        <div><strong class="font-bold">Bill No:</strong> <span
                class="font-bold">{{ $settings->invoice_prefix }}{{ $sale->id }}</span></div>
        <div><strong class="font-bold">Date:</strong>
            {{ $sale->created_at->timezone(config('app.timezone'))->format('d-M-Y') }}@if($settings->show_sale_time) {{ $sale->created_at->timezone(config('app.timezone'))->format('h:i A') }}@endif
        </div>
        <div><strong class="font-bold">Cust:</strong> <span class="font-bold">{{ $sale->party->name }}</span></div>
        <div class="border-b" style="margin-top: 5px;"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="font-bold">Item</th>
                <th class="text-right font-bold">Qty</th>
                <th class="text-right font-bold">Price</th>
                <th class="text-right font-bold">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td colspan="4" class="font-bold" style="padding-top: 5px;">{{ $item->product->name }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #eee;">
                    <td></td>
                    <td class="text-right font-bold">{{ number_format($item->qty, 2) }}</td>
                    <td class="text-right">{{ number_format($item->price, 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="border-b"></div>

    <div class="totals" style="border-top: 2px solid #000; padding-top: 5px;">
        @php
            $invoiceImpact = $sale->total_amount - $sale->paid_amount;
            $prevBalance = $currentBalance - $invoiceImpact;
        @endphp
        <div class="total-row">
            <span>PREV. BALANCE:</span>
            <span>{{ number_format($prevBalance, 2) }}</span>
        </div>
        <div class="total-row font-bold" style="font-size: 16px; margin: 5px 0;">
            <span>NET TOTAL:</span>
            <span>{{ $settings->currency_symbol }} {{ number_format($sale->total_amount, 2) }}</span>
        </div>
        <div class="total-row"
            style="font-size: 11px; border-bottom: 1px solid #000; padding-bottom: 4px; margin-bottom: 4px;">
            <span class="font-bold">TOTAL QTY:</span>
            <span class="font-bold">{{ number_format($sale->items->sum('qty'), 2) }}</span>
        </div>
        @if ($sale->paymentSplits->isNotEmpty())
            @foreach ($sale->paymentSplits as $split)
                <div class="total-row">
                    <span>PAID ({{ $split->account->name ?? 'Unknown' }}):</span>
                    <span class="font-bold">{{ $settings->currency_symbol }} {{ number_format($split->amount, 2) }}</span>
                </div>
            @endforeach
        @else
            <div class="total-row">
                <span>PAID @if ($sale->account)
                        ({{ $sale->account->name }})
                    @else
                        AMOUNT
                    @endif:</span>
                <span class="font-bold">{{ $settings->currency_symbol }} {{ number_format($sale->paid_amount, 2) }}</span>
            </div>
        @endif
        <div class="total-row">
            <span>BILL BALANCE:</span>
            <span>{{ $settings->currency_symbol }}
                {{ number_format($sale->total_amount - $sale->paid_amount, 2) }}</span>
        </div>
        <div class="total-row font-bold"
            style="border-top: 2px solid #000; margin-top: 5px; padding-top: 5px; font-size: 16px;">
            <span>TOTAL BALANCE:</span>
            <span>{{ $settings->currency_symbol }}
                {{ number_format($currentBalance, 2) }}</span>
        </div>
    </div>

    <div class="border-b" style="margin-top: 15px; border-bottom-style: double;"></div>
    <div class="text-center font-bold" style="margin-top: 10px; font-size: 14px;">
        {!! nl2br(e($settings->invoice_footer_note ?? 'THANK YOU FOR SHOPPING!')) !!}
        <br>
        <div style="margin-top: 5px; font-size: 14px;">Developed by Invenco <br> Tel: +92 324 3980274</div>
    </div>

</body>

</html>
