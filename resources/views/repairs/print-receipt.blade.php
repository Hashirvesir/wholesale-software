<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Intake Receipt - {{ $repair->order_no }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #000 !important;
            font-weight: 700;
        }

        body {
            background-color: #fff;
            color: #000;
            padding: 15px;
        }

        .receipt-card {
            max-width: 80mm; /* Standard 80mm POS receipt roll */
            background: #fff;
            padding: 10px 12px;
            margin: 0 auto;
            border: 2px solid #000;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: 900 !important; }
        .uppercase { text-transform: uppercase; }

        .company-header h1 {
            font-size: 18px;
            font-weight: 900 !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .company-header p {
            font-size: 11px;
            font-weight: 700 !important;
            margin-top: 2px;
        }

        .receipt-title {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-size: 12px;
            font-weight: 900 !important;
            text-transform: uppercase;
            padding: 4px 0;
            margin: 8px 0;
            letter-spacing: 0.5px;
            text-align: center;
        }

        .meta-table {
            width: 100%;
            font-size: 11px;
            margin-bottom: 8px;
            border-bottom: 2px dashed #000;
            padding-bottom: 6px;
        }

        .meta-table td {
            padding: 2px 0;
            font-weight: 700;
        }

        .device-box {
            border: 1.5px solid #000;
            padding: 6px 8px;
            margin-bottom: 6px;
            font-size: 11px;
            background: #fff;
        }

        .device-title {
            font-weight: 900 !important;
            font-size: 12px;
        }

        .problem-text {
            font-size: 10.5px;
            font-weight: 900 !important;
            margin: 2px 0;
        }

        .financial-table {
            width: 100%;
            font-size: 11.5px;
            margin: 8px 0;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 4px 0;
        }

        .financial-table td {
            padding: 2.5px 0;
            font-weight: 800;
        }

        .financial-table .grand-row td {
            font-size: 13.5px;
            font-weight: 900 !important;
            padding: 4px 0;
            border-top: 1.5px dashed #000;
        }

        .barcode-section {
            text-align: center;
            margin: 8px 0 4px 0;
        }

        svg.barcode {
            max-width: 100%;
            height: 38px;
        }

        .terms-box {
            font-size: 9px;
            font-weight: 700;
            line-height: 1.35;
            border-top: 1.5px dashed #000;
            padding-top: 6px;
            margin-top: 6px;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            padding-top: 10px;
            border-top: 1.5px dotted #000;
            font-size: 9.5px;
            font-weight: 900 !important;
        }

        .no-print-bar {
            text-align: center;
            margin-bottom: 15px;
        }

        .btn {
            background: #000;
            color: #fff !important;
            border: 2px solid #000;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 900 !important;
            border-radius: 6px;
            cursor: pointer;
            margin: 0 5px;
        }

        @media print {
            body {
                background: transparent;
                padding: 0;
            }
            .no-print-bar {
                display: none;
            }
            .receipt-card {
                border: none;
                width: 100%;
                max-width: 100%;
                padding: 2px;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <button class="btn" onclick="window.print()">🖨️ PRINT INTAKE RECEIPT</button>
        <button class="btn" style="background:#555; border-color:#555;" onclick="window.close()">CLOSE</button>
    </div>

    <div class="receipt-card">
        <!-- Company Header -->
        <div class="company-header text-center">
            <h1>{{ $settings->company_name ?? 'INVENCO REPAIR LAB' }}</h1>
            <p>{{ $settings->address ?? 'Main Mobile Market, Electronics Center' }}</p>
            <p>PH: {{ $settings->phone ?? '0300-0000000' }}</p>
        </div>

        <!-- Receipt Title -->
        <div class="receipt-title">
            REPAIR INTAKE RECEIPT (CUSTOMER COPY)
        </div>

        <!-- Meta Details -->
        <table class="meta-table">
            <tr>
                <td class="bold">RECEIPT / TOKEN:</td>
                <td class="text-right bold" style="font-size: 13px;">{{ $repair->order_no }}</td>
            </tr>
            <tr>
                <td class="bold">DATE RECEIVED:</td>
                <td class="text-right bold">{{ $repair->received_date->format('d/m/Y h:i A') }}</td>
            </tr>
            <tr>
                <td class="bold">CUSTOMER:</td>
                <td class="text-right bold">{{ strtoupper($repair->customer_name) }}</td>
            </tr>
            <tr>
                <td class="bold">CONTACT:</td>
                <td class="text-right bold">{{ $repair->customer_phone }}</td>
            </tr>
            @if($repair->expected_delivery_date)
                <tr>
                    <td class="bold">EXP. DELIVERY:</td>
                    <td class="text-right bold">{{ $repair->expected_delivery_date->format('d/m/Y h:i A') }}</td>
                </tr>
            @endif
        </table>

        <!-- Devices List -->
        <div class="bold uppercase" style="font-size: 11px; margin-bottom: 4px;">
            RECEIVED DEVICES ({{ $repair->devices->count() }}):
        </div>

        @foreach($repair->devices as $index => $device)
            <div class="device-box">
                <div class="device-title uppercase">
                    #{{ $index + 1 }}. {{ $device->brand }} {{ $device->model }} [{{ $device->device_tracking_no }}]
                </div>
                @if($device->imei)
                    <div style="font-size: 10px;">IMEI: {{ $device->imei }}</div>
                @endif
                <div class="problem-text uppercase">FAULT: {{ $device->problem_description }}</div>
                @if(!empty($device->services) && is_array($device->services) && count($device->services) > 0)
                    <div style="font-size: 9.5px; margin: 2px 0;">
                        <span class="bold">SERVICES:</span>
                        @foreach($device->services as $srv)
                            <span>&bull; {{ $srv['name'] ?? '' }} (Rs {{ number_format($srv['rate'] ?? 0, 0) }}) </span>
                        @endforeach
                    </div>
                @endif
                @if($device->warranty_days > 0)
                    <div style="font-size: 10px;" class="bold">CHECKING WARRANTY: {{ $device->warranty_days }} DAYS</div>
                @endif
            </div>
        @endforeach

        <!-- Financial Summary -->
        <table class="financial-table">
            <tr>
                <td class="bold">ESTIMATED TOTAL:</td>
                <td class="text-right bold">Rs {{ number_format($repair->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="bold">ADVANCE RECEIVED:</td>
                <td class="text-right bold">Rs {{ number_format($repair->advance_amount, 2) }}</td>
            </tr>
            <tr class="grand-row">
                <td class="bold">ESTIMATED BALANCE:</td>
                <td class="text-right bold">Rs {{ number_format($repair->balance_amount, 2) }}</td>
            </tr>
        </table>

        <!-- Barcode -->
        <div class="barcode-section">
            <svg class="barcode" jsbarcode-value="{{ $repair->order_no }}" jsbarcode-format="CODE128" jsbarcode-width="1.6" jsbarcode-height="36" jsbarcode-fontSize="10"></svg>
        </div>

        <!-- Terms & Conditions (Monochrome Bold) -->
        <div class="terms-box">
            <span class="bold uppercase">TERMS & CONDITIONS:</span><br>
            1. Mobile wapsi lene ke waqt yeh slip pesh karna lazmi hai.<br>
            2. Slip gum hone ki soorat mein CNIC copy aur tasdeeq lazmi hogi.<br>
            3. Phone data (photos, contacts) backup customer ki zimmedari hai.<br>
            4. 30 din baad mobile ki dukaan zimmedar nahi hogi.
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <span>CUSTOMER SIGNATURE</span>
            <span>AUTHORIZED SIGNATURE</span>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            JsBarcode(".barcode").init();
            setTimeout(function () {
                window.focus();
                window.print();
            }, 400);
        });

        // Auto close small popup window after printing or cancelling
        window.onafterprint = function () {
            setTimeout(function () {
                window.close();
            }, 300);
        };
    </script>
</body>
</html>
