<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Purchase - {{ $settings->invoice_prefix }}{{ $purchase->id }}</title>
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
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
        }

        .company-info {
            float: left;
            width: 60%;
        }

        .invoice-title {
            float: right;
            width: 40%;
            text-align: right;
        }

        .logo {
            max-height: 60px;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .invoice-heading {
            font-size: 24px;
            font-weight: bold;
            color: #444;
            text-transform: uppercase;
        }

        .details-box {
            width: 100%;
            clear: both;
            margin-bottom: 30px;
        }

        .left-box {
            float: left;
            width: 50%;
        }

        .right-box {
            float: right;
            width: 50%;
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background-color: #f3f4f6;
            text-transform: uppercase;
            font-size: 11px;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
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
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }

        .grand-total {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            padding: 10px 0;
            margin-top: 5px;
        }

        .notes {
            float: left;
            width: 50%;
            font-style: italic;
            color: #666;
            font-size: 11px;
        }

        .footer-note {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            font-size: 11px;
            color: #555;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            float: right;
            margin-top: 5px;
        }

        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .status-due {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header clearfix">
            <div class="company-info">
                @if ($settings->company_logo)
                    <img src="{{ public_path($settings->company_logo) }}" class="logo" alt="Logo">
                @endif
                <div class="company-name">{{ $settings->company_name }}</div>
                <div>{{ $settings->company_address }}</div>
                <div>{{ $settings->company_phone }} | {{ $settings->company_email }}</div>
            </div>
            <div class="invoice-title">
                <div class="invoice-heading">PURCHASE</div>
                <div>Bill #: <strong>{{ $settings->invoice_prefix }}{{ $purchase->id }}</strong></div>
                <div>Date: <strong>{{ \Carbon\Carbon::parse($purchase->date)->format('d M, Y') }}</strong></div>
                @if ($purchase->total_amount - $purchase->paid_amount <= 0)
                    <div class="status-badge status-paid">PAID</div>
                @else
                    <div class="status-badge status-due">BALANCE DUE</div>
                @endif
            </div>
        </div>

        <div class="details-box clearfix">
            <div class="left-box">
                <div
                    style="font-weight: bold; text-transform: uppercase; color: #888; font-size: 10px; margin-bottom: 5px;">
                    Vendor (Bill From)</div>
                <div style="font-size: 15px; font-weight: bold;">{{ $purchase->party->name }}</div>
                <div>{{ $purchase->party->phone }}</div>
                <div>{{ $purchase->party->address }}</div>
            </div>
            <div class="right-box">
                <div
                    style="font-weight: bold; text-transform: uppercase; color: #888; font-size: 10px; margin-bottom: 5px;">
                    Destination</div>
                <div style="font-size: 15px; font-weight: bold;">{{ $purchase->warehouse->name }}</div>
                <div>{{ $purchase->warehouse->location }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="40%">Item</th>
                    <th width="20%" class="text-center">Qty</th>
                    <th width="20%" class="text-right">Price ({{ $settings->currency_symbol }})</th>
                    <th width="20%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->items as $item)
                    <tr>
                        <td>
                            <div style="font-weight: bold;">{{ $item->product->name }}</div>
                            <div style="font-size: 10px; color: #666;">{{ $item->product->sku }}</div>
                        </td>
                        <td class="text-center">{{ number_format($item->qty) }} {{ $item->product->unit->name ?? '' }}
                        </td>
                        <td class="text-right">{{ number_format($item->price_pkr, 2) }}</td>
                        <td class="text-right">{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="clearfix">
            <div class="notes">
                @if ($purchase->note)
                    <div style="margin-bottom: 10px;">
                        <strong>Notes:</strong><br>
                        {{ $purchase->note }}
                    </div>
                @endif
            </div>
            <div class="totals-box">
                <div class="totals-box">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="text-align: left; padding: 5px 0; border-bottom: 1px solid #eee;">Subtotal:
                            </td>
                            <td style="text-align: right; padding: 5px 0; border-bottom: 1px solid #eee;">
                                {{ $settings->currency_symbol }}
                                {{ number_format($purchase->items->sum('total'), 2) }}</td>
                        </tr>
                        @if ($purchase->discount_amount > 0)
                            <tr style="color: green;">
                                <td style="text-align: left; padding: 5px 0; border-bottom: 1px solid #eee;">Discount:
                                </td>
                                <td style="text-align: right; padding: 5px 0; border-bottom: 1px solid #eee;">
                                    - {{ $settings->currency_symbol }}
                                    {{ number_format($purchase->discount_amount, 2) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td
                                style="text-align: left; padding: 5px 0; border-bottom: 1px solid #eee; font-weight: bold;">
                                Grand Total:
                            </td>
                            <td
                                style="text-align: right; padding: 5px 0; border-bottom: 1px solid #eee; font-weight: bold;">
                                {{ $settings->currency_symbol }} {{ number_format($purchase->total_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td
                                style="text-align: left; padding: 5px 0; border-bottom: 1px solid #eee; font-size: 10px; color: #666;">
                                Total Items Qty:
                            </td>
                            <td
                                style="text-align: right; padding: 5px 0; border-bottom: 1px solid #eee; font-size: 10px; color: #666;">
                                {{ number_format($purchase->items->sum('qty'), 2) }}</td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding: 5px 0; border-bottom: 1px solid #eee;">Prev. Balance:
                            </td>
                            <td style="text-align: right; padding: 5px 0; border-bottom: 1px solid #eee;">
                                @php
                                    $balanceChange = $purchase->paid_amount - $purchase->total_amount;
                                    $prevBalance = $purchase->party->getCurrentBalance() - $balanceChange;
                                @endphp
                                {{ $settings->currency_symbol }} {{ number_format(abs($prevBalance), 2) }}
                                <small>{{ $prevBalance >= 0 ? '(Rec)' : '(Pay)' }}</small>
                            </td>
                        </tr>
                        <tr style="color: green;">
                            <td style="text-align: left; padding: 5px 0; border-bottom: 1px solid #eee;">
                                Paid Amount:
                                @if ($purchase->paymentSplits->isNotEmpty())
                                    @foreach ($purchase->paymentSplits as $split)
                                        <br><small style="color: #666;">{{ $split->account->name ?? 'Unknown' }}: {{ $settings->currency_symbol }} {{ number_format($split->amount, 2) }}</small>
                                    @endforeach
                                @elseif ($purchase->account)
                                    <br><small style="color: #666;">({{ $purchase->account->name }})</small>
                                @endif
                            </td>
                            <td style="text-align: right; padding: 5px 0; border-bottom: 1px solid #eee;">
                                {{ $settings->currency_symbol }} {{ number_format($purchase->paid_amount, 2) }}</td>
                        </tr>
                        <tr style="font-weight: bold; font-size: 14px;">
                            <td
                                style="text-align: left; padding: 10px 0; border-top: 2px solid #333; border-bottom: 2px solid #333;">
                                Net Balance:</td>
                            <td
                                style="text-align: right; padding: 10px 0; border-top: 2px solid #333; border-bottom: 2px solid #333;">
                                {{ $settings->currency_symbol }}
                                {{ number_format(abs($purchase->party->getCurrentBalance()), 2) }}
                                <small>{{ $purchase->party->getCurrentBalance() >= 0 ? '(Rec)' : '(Pay)' }}</small>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div
            style="margin-top: 50px; text-align: center; color: #888; font-size: 10px; border-top: 1px solid #eee; padding-top: 10px;">
            Thank you for your business.
            <div style="margin-top: 5px; font-weight: bold;">Developed by Invenco Tel: +92 324 3980274</div>
        </div>
    </div>
</body>

</html>
