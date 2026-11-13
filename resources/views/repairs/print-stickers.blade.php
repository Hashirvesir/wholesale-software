<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Sticker Tags - {{ $repair->order_no }}</title>
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
            padding: 10px;
        }

        .sticker-card {
            width: 76mm; /* Standard thermal label or 3-inch roll width */
            min-height: 50mm;
            background: #fff;
            border: 2px dashed #000;
            border-radius: 6px;
            padding: 8px 10px;
            margin: 0 auto 15px auto;
            page-break-after: always;
            position: relative;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .shop-name {
            font-size: 11px;
            font-weight: 900 !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .token-badge {
            font-size: 11px;
            font-weight: 900 !important;
            background: #000;
            color: #fff !important;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .device-title {
            font-size: 13px;
            font-weight: 900 !important;
            margin-bottom: 2px;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .info-grid {
            font-size: 10.5px;
            line-height: 1.35;
            margin-bottom: 6px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
        }

        .bold {
            font-weight: 900 !important;
        }

        .problem-box {
            background: #fff;
            border: 1.5px solid #000;
            border-radius: 4px;
            padding: 4px 6px;
            font-size: 10px;
            font-weight: 900 !important;
            margin: 4px 0;
        }

        .barcode-container {
            text-align: center;
            margin-top: 4px;
            padding-top: 2px;
            border-top: 1.5px dashed #000;
        }

        svg.barcode {
            max-width: 100%;
            height: 38px;
        }

        .footer-note {
            text-align: center;
            font-size: 8.5px;
            text-transform: uppercase;
            font-weight: 900 !important;
            margin-top: 2px;
        }

        .no-print-bar {
            text-align: center;
            margin-bottom: 15px;
        }

        .btn {
            background: #000;
            color: #fff !important;
            border: 2px solid #000;
            padding: 8px 16px;
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
            .sticker-card {
                border: 1px solid #000;
                margin: 0;
                width: 100%;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <button class="btn" onclick="window.print()">🖨️ Print Mobile Stickers</button>
        <button class="btn" style="background:#374151" onclick="window.close()">Close Window</button>
    </div>

    @foreach($devices as $device)
        <div class="sticker-card">
            <!-- Header -->
            <div class="header">
                <div class="shop-name">{{ $settings->company_name ?? 'INVENCO REPAIR LAB' }}</div>
                <div class="token-badge">{{ $device->device_tracking_no }}</div>
            </div>

            <!-- Device Model & Customer -->
            <div class="device-title">
                {{ $device->brand }} {{ $device->model }}
                @if($device->color)
                    <span style="font-size: 10px; font-weight: normal;">({{ $device->color }})</span>
                @endif
            </div>

            <div class="info-grid">
                <div class="info-row">
                    <span>Customer: <span class="bold">{{ $repair->customer_name }}</span></span>
                    <span>Ph: <span class="bold">{{ $repair->customer_phone }}</span></span>
                </div>
                @if($device->imei)
                    <div class="info-row">
                        <span>IMEI: <span class="bold">{{ $device->imei }}</span></span>
                    </div>
                @endif
                @if($device->security_type !== 'none')
                    <div class="info-row">
                        <span>Lock: <span class="bold">{{ strtoupper($device->security_type) }} [{{ $device->security_code }}]</span></span>
                    </div>
                @endif
                <div class="info-row">
                    <span>Date: {{ $repair->received_date->format('d/m/Y h:i A') }}</span>
                    <span>Adv: <span class="bold">Rs {{ number_format($repair->advance_amount, 0) }}</span></span>
                </div>
            </div>

            <!-- Problem Box -->
            <div class="problem-box">
                FAUL T: {{ $device->problem_description }}
            </div>

            <!-- Barcode Generator -->
            <div class="barcode-container">
                <svg class="barcode" jsbarcode-value="{{ $device->device_tracking_no }}" jsbarcode-format="CODE128" jsbarcode-width="1.6" jsbarcode-height="32" jsbarcode-fontSize="10"></svg>
            </div>

            <div class="footer-note">
                STICK ON MOBILE BACK • SCAN TO SEARCH
            </div>
        </div>
    @endforeach

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            JsBarcode(".barcode").init();
            setTimeout(function () {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>
