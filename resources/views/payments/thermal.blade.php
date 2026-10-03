<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $payment->id }}</title>
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

        .header {
            margin-bottom: 12px;
        }

        .company-name {
            font-size: 18px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .receipt-title {
            font-size: 15px;
            font-weight: 900;
            border: 2px solid #000;
            padding: 5px;
            margin: 10px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
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
        <div class="border-b"></div>
    </div>

    <div class="text-center receipt-title">
        PAYMENT RECEIPT ({{ strtoupper($payment->payment_type) }})
    </div>

    <div class="mb-2">
        <div class="row">
            <span><strong>Date:</strong></span>
            <span>{{ $payment->created_at->timezone(config('app.timezone'))->format('d-M-Y h:i A') }}</span>
        </div>
        <div class="row">
            <span><strong>Receipt No:</strong></span>
            <span>#{{ $payment->id }}</span>
        </div>
        <div class="border-b"></div>
    </div>

    <div class="mb-2">
        <div class="row">
            <span><strong>Party:</strong></span>
            <span>{{ $payment->party->name }}</span>
        </div>
        <div class="row">
            <span><strong>Account:</strong></span>
            <span>{{ $payment->account->name }}</span>
        </div>
    </div>

    <div class="mb-2">
        <div class="row font-bold">
            <span>Prev. Balance:</span>
            <span>{{ $settings->currency_symbol }} {{ number_format(abs($previousBalance), 2) }}
                {{ $previousBalance >= 0 ? '(Rec)' : '(Pay)' }}</span>
        </div>
        <div class="row font-bold">
            <span>Amount Paid:</span>
            <span>{{ $settings->currency_symbol }} {{ number_format($payment->amount, 2) }}</span>
        </div>
        <div class="row font-bold" style="border-top: 1px solid #000; padding-top: 5px; margin-top: 5px;">
            <span>New Balance:</span>
            <span>{{ $settings->currency_symbol }} {{ number_format(abs($currentBalance), 2) }}
                {{ $currentBalance >= 0 ? '(Rec)' : '(Pay)' }}</span>
        </div>
    </div>

    <div class="border-b"></div>

    @if ($payment->note)
        <div class="mb-2">
            <strong>Note:</strong> {{ $payment->note }}
        </div>
    @endif

    <div class="text-center" style="margin-top: 20px;">
        <div class="font-bold" style="font-size: 13px;">SIGNATURE</div>
        <br>
        _______________________
        <div class="font-bold" style="margin-top: 15px; font-size: 14px;">
            {!! nl2br(e($settings->invoice_footer_note ?? 'THANK YOU FOR YOUR PAYMENT!')) !!}
            <div style="margin-top: 5px; font-size: 14px;">
                Developed by Invenco <br> Tel: +92 324 3980274
            </div>
        </div>
    </div>

</body>

</html>
