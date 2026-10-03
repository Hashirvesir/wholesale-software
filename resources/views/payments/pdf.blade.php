<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Payment Receipt - #{{ $payment->id }}</title>
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
            border-bottom: 2px solid #ddd;
            padding-bottom: 20px;
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

        .receipt-heading {
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

        .payment-info-box {
            width: 100%;
            background-color: #f9fafb;
            border: 2px solid #e5e7eb;
            padding: 20px;
            margin: 30px 0;
            border-radius: 8px;
            clear: both;
        }

        .payment-amount {
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }

        .payment-received {
            color: #059669;
        }

        .payment-paid {
            color: #dc2626;
        }

        .info-row {
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-label {
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            font-size: 10px;
        }

        .info-value {
            margin-top: 3px;
            font-size: 14px;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-received {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-paid {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .notes {
            margin-top: 30px;
            font-style: italic;
            color: #666;
            font-size: 11px;
            background-color: #f9fafb;
            padding: 15px;
            border-left: 3px solid #6366f1;
        }

        .transaction-explanation {
            margin-top: 30px;
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 4px;
            padding: 15px;
        }

        .transaction-explanation-title {
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
            font-size: 12px;
        }

        .transaction-explanation-text {
            color: #1e3a8a;
            font-size: 11px;
            line-height: 1.6;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            color: #888;
            font-size: 10px;
            border-top: 1px solid #eee;
            padding-top: 10px;
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
        <div class="header clearfix">
            <div class="company-info">
                @if ($settings->company_logo)
                    <img src="{{ public_path($settings->company_logo) }}" class="logo" alt="Logo">
                @endif
                <div class="company-name">{{ $settings->company_name }}</div>
                <div>{{ $settings->company_address }}</div>
                <div>{{ $settings->company_phone }} | {{ $settings->company_email }}</div>
            </div>
            <div class="receipt-title">
                <div class="receipt-heading">PAYMENT RECEIPT</div>
                <div>Receipt #: <strong>#{{ $payment->id }}</strong></div>
                <div>Date: <strong>{{ \Carbon\Carbon::parse($payment->date)->format('d M, Y') }}</strong></div>
                <div style="margin-top: 10px;">
                    <span class="badge {{ $payment->payment_type === 'received' ? 'badge-received' : 'badge-paid' }}">
                        {{ $payment->payment_type === 'received' ? 'Payment Received' : 'Payment Paid' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="details-box clearfix">
            <div class="left-box">
                <div
                    style="font-weight: bold; text-transform: uppercase; color: #888; font-size: 10px; margin-bottom: 5px;">
                    Party Information</div>
                <div style="font-size: 16px; font-weight: bold;">{{ $payment->party->name }}</div>
                <div>{{ $payment->party->phone }}</div>
                <div>{{ $payment->party->address }}</div>
                <div style="margin-top: 5px;">
                    <span
                        style="background-color: #e0e7ff; color: #3730a3; padding: 3px 8px; border-radius: 3px; font-size: 10px; font-weight: bold;">
                        {{ strtoupper($payment->party->type) }}
                    </span>
                </div>
            </div>
            <div class="right-box">
                <div
                    style="font-weight: bold; text-transform: uppercase; color: #888; font-size: 10px; margin-bottom: 5px;">
                    Payment Method</div>
                <div style="font-size: 16px; font-weight: bold;">{{ $payment->account->name }}</div>
                <div>{{ $payment->account->type }} Account</div>
            </div>
        </div>

        <div class="payment-info-box">
            <div class="info-row">
                <div class="info-label">Transaction Type</div>
                <div class="info-value">
                    {{ $payment->payment_type === 'received' ? 'Payment Received from Party' : 'Payment Paid to Party' }}
                </div>
            </div>

            <div
                class="payment-amount {{ $payment->payment_type === 'received' ? 'payment-received' : 'payment-paid' }}">
                {{ $settings->currency_symbol }} {{ number_format($payment->amount, 2) }}
            </div>

            <div class="info-row">
                <div class="info-label">Current Party Balance</div>
                <div class="info-value">
                    @php
                        $balance = $payment->party->getCurrentBalance();
                        $balanceColor = $balance >= 0 ? '#dc2626' : '#059669';
                        $balanceType = $balance >= 0 ? 'Receivable' : 'Payable';
                    @endphp
                    <span style="color: {{ $balanceColor }}; font-weight: bold;">
                        {{ $settings->currency_symbol }} {{ number_format(abs($balance), 2) }}
                        ({{ $balanceType }})
                    </span>
                </div>
            </div>
        </div>

        @if ($payment->note)
            <div class="notes">
                <strong>Payment Note:</strong><br>
                {{ $payment->note }}
            </div>
        @endif

        <div class="transaction-explanation">
            <div class="transaction-explanation-title">📋 Accounting Entry:</div>
            <div class="transaction-explanation-text">
                @if ($payment->payment_type === 'received')
                    <strong>Payment Received Transaction:</strong><br>
                    • {{ $payment->account->name }} account has been <strong>Debited</strong> (Cash/Bank increased)<br>
                    • {{ $payment->party->name }}'s account has been <strong>Credited</strong> (Receivable decreased)
                @else
                    <strong>Payment Paid Transaction:</strong><br>
                    • {{ $payment->party->name }}'s account has been <strong>Debited</strong> (Payable decreased)<br>
                    • {{ $payment->account->name }} account has been <strong>Credited</strong> (Cash/Bank decreased)
                @endif
            </div>
        </div>

        <div class="footer">
            Thank you for your business.<br>
            This is a computer-generated receipt.<br>
            <span style="font-weight: bold;">Developed by Invenco Tel: +92 324 3980274</span>
        </div>
    </div>
</body>

</html>
