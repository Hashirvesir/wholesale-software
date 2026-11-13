<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Slips (Sticker & Customer Copy) - {{ $repair->order_no }}</title>
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
            background-color: #f3f4f6;
            color: #000;
            padding: 15px;
        }

        .container-wrapper {
            max-width: 80mm; /* Standard POS 80mm roll width / thermal paper */
            margin: 0 auto;
        }

        /* Top Action Buttons (Hidden when printing) */
        .no-print-bar {
            text-align: center;
            margin-bottom: 20px;
            background: #ffffff;
            padding: 12px;
            border-radius: 10px;
            border: 2px solid #000;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
        }

        .btn {
            background: #000;
            color: #fff !important;
            border: 2px solid #000;
            padding: 7px 12px;
            font-size: 11.5px;
            font-weight: 900 !important;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #374151;
            border-color: #374151;
        }

        .btn-outline {
            background: #fff;
            color: #000 !important;
            border-color: #000;
        }

        /* ========================================================
           PART 1: MOBILE STICKER (TO STICK ON BACK OF PHONE)
           ======================================================== */
        .sticker-section {
            background: #fff;
            margin-bottom: 15px;
        }

        .sticker-card {
            width: 76mm; /* Standard label roll width */
            background: #fff;
            border: 2px dashed #000;
            border-radius: 6px;
            padding: 8px 10px;
            margin: 0 auto 12px auto;
            position: relative;
        }

        .sticker-header {
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
            margin-bottom: 3px;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .info-grid {
            font-size: 10px;
            line-height: 1.35;
            margin-bottom: 4px;
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
            text-transform: uppercase;
        }

        .barcode-container {
            text-align: center;
            margin-top: 4px;
            padding-top: 2px;
            border-top: 1.5px dashed #000;
        }

        svg.barcode {
            max-width: 100%;
            height: 34px;
        }

        .footer-note {
            text-align: center;
            font-size: 8px;
            text-transform: uppercase;
            font-weight: 900 !important;
            margin-top: 2px;
        }

        /* Visible Tear / Cut Separator */
        .tear-line {
            text-align: center;
            font-size: 9px;
            font-weight: 900 !important;
            letter-spacing: 1px;
            margin: 15px 0;
            border-top: 2px dashed #000;
            border-bottom: 2px dashed #000;
            padding: 3px 0;
            background: #fdfdfd;
        }

        /* ========================================================
           PART 2: CUSTOMER COPY / INTAKE CLAIM INVOICE
           ======================================================== */
        .customer-section {
            background: #fff;
            border: 2px solid #000;
            padding: 10px 12px;
            margin: 0 auto;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .uppercase { text-transform: uppercase; }

        .company-header h1 {
            font-size: 17px;
            font-weight: 900 !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .company-header p {
            font-size: 10.5px;
            font-weight: 700 !important;
            margin-top: 2px;
        }

        .receipt-title {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-size: 11.5px;
            font-weight: 900 !important;
            text-transform: uppercase;
            padding: 3px 0;
            margin: 6px 0;
            letter-spacing: 0.5px;
            text-align: center;
        }

        .meta-table {
            width: 100%;
            font-size: 10.5px;
            margin-bottom: 6px;
            border-bottom: 2px dashed #000;
            padding-bottom: 4px;
        }

        .meta-table td {
            padding: 2px 0;
            font-weight: 700;
        }

        .device-box {
            border: 1.5px solid #000;
            padding: 5px 7px;
            margin-bottom: 6px;
            font-size: 10.5px;
            background: #fff;
        }

        .device-box .device-title {
            font-size: 11px;
            font-weight: 900 !important;
        }

        .financial-table {
            width: 100%;
            font-size: 11px;
            margin: 6px 0;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 4px 0;
        }

        .financial-table td {
            padding: 2px 0;
        }

        .grand-row td {
            font-size: 13px;
            font-weight: 900 !important;
            border-top: 1.5px dashed #000;
            padding-top: 4px;
        }

        .barcode-section {
            text-align: center;
            margin: 6px 0;
        }

        .terms-box {
            border: 1.5px solid #000;
            padding: 5px 6px;
            font-size: 9px;
            line-height: 1.35;
            margin-top: 6px;
            font-weight: 700 !important;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 22px;
            padding-top: 4px;
            font-size: 9px;
            font-weight: 900 !important;
            text-transform: uppercase;
        }

        .signatures span {
            border-top: 1.5px solid #000;
            padding-top: 3px;
            min-width: 85px;
            text-align: center;
        }

        /* ========================================================
           PRINT MEDIA QUERIES (SEPARATE INTO 2 PRINTS / PAGES)
           ======================================================== */
        @media print {
            body {
                background: transparent !important;
                padding: 0 !important;
            }

            .no-print-bar {
                display: none !important;
            }

            .container-wrapper {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Print 1: Sticker Section */
            .sticker-section {
                margin: 0 !important;
                page-break-after: always !important;
                break-after: page !important;
            }

            .sticker-card {
                border: 1.5px solid #000 !important;
                margin: 0 0 10px 0 !important;
                width: 100% !important;
            }

            /* Tear line hidden or clean in physical print */
            .tear-line {
                display: none !important;
            }

            /* Print 2: Customer Copy / Invoice Section */
            .customer-section {
                border: 1.5px solid #000 !important;
                margin: 0 !important;
                width: 100% !important;
                page-break-before: always !important;
                break-before: page !important;
            }
        }
    </style>
</head>
<body>

    <div class="container-wrapper">

        <!-- Top Control Bar (Hidden on Print) -->
        <div class="no-print-bar">
            <div style="font-size: 13px; font-weight: 900; margin-bottom: 8px; text-transform: uppercase;">
                🖨️ Mobile Repair Print Center
            </div>
            <div class="btn-group">
                <button class="btn" onclick="printBoth()">
                    🖨️ Print Both (Sticker + Invoice)
                </button>
                <button class="btn btn-secondary" onclick="printStickerOnly()">
                    🏷️ Print Sticker Only
                </button>
                <button class="btn btn-secondary" onclick="printCustomerOnly()">
                    🧾 Print Invoice Only
                </button>
                <button class="btn btn-outline" onclick="window.close()">
                    ✖ Close
                </button>
            </div>
            <div style="font-size: 10px; color: #4b5563; margin-top: 6px; font-weight: 700;">
                Sticker mobile par lagane ke liye aur invoice customer ko dene ke liye tayyar hai.
            </div>
        </div>

        <!-- ========================================================
             PART 1: MOBILE STICKER (TO PASTE ON PHONE)
             ======================================================== -->
        <div id="stickerSection" class="sticker-section">
            @foreach($devices as $device)
                <div class="sticker-card">
                    <!-- Header -->
                    <div class="sticker-header">
                        <div class="shop-name">{{ $settings->company_name ?? 'INVENCO REPAIR LAB' }}</div>
                        <div class="token-badge">{{ $device->device_tracking_no }}</div>
                    </div>

                    <!-- Device Model & Color -->
                    <div class="device-title">
                        {{ $device->brand }} {{ $device->model }}
                        @if($device->color)
                            <span style="font-size: 10px; font-weight: 700;">({{ $device->color }})</span>
                        @endif
                    </div>

                    <!-- Customer & Security Details -->
                    <div class="info-grid">
                        <div class="info-row">
                            <span>Cust: <span class="bold">{{ $repair->customer_name }}</span></span>
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
                        FAULT: {{ $device->problem_description }}
                    </div>

                    <!-- Barcode Generator -->
                    <div class="barcode-container">
                        <svg class="barcode" jsbarcode-value="{{ $device->device_tracking_no }}" jsbarcode-format="CODE128" jsbarcode-width="1.6" jsbarcode-height="30" jsbarcode-fontSize="10"></svg>
                    </div>

                    <div class="footer-note">
                        STICK ON MOBILE BACK • SCAN TO SEARCH
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Tear / Cut Separator Line -->
        <div class="tear-line no-print">
            ✂ - - - - - - - - TEAR HERE (MOBILE STICKER) - - - - - - - - ✂
        </div>

        <!-- ========================================================
             PART 2: CUSTOMER INTAKE RECEIPT / CLAIM INVOICE
             ======================================================== -->
        <div id="customerSection" class="customer-section">
            <!-- Shop Header -->
            <div class="company-header text-center">
                <h1>{{ $settings->company_name ?? 'INVENCO REPAIR LAB' }}</h1>
                @if(!empty($settings->company_address))
                    <p>{{ $settings->company_address }}</p>
                @endif
                <p>
                    @if(!empty($settings->company_phone))
                        PHONE: {{ $settings->company_phone }}
                    @endif
                    @if(!empty($settings->company_email))
                        | {{ $settings->company_email }}
                    @endif
                </p>
            </div>

            <!-- Title -->
            <div class="receipt-title">
                CUSTOMER INTAKE RECEIPT & CLAIM INVOICE
            </div>

            <!-- Meta Data -->
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
            <div class="bold uppercase" style="font-size: 10.5px; margin-bottom: 4px;">
                RECEIVED DEVICES ({{ $repair->devices->count() }}):
            </div>

            @foreach($repair->devices as $index => $device)
                <div class="device-box">
                    <div class="device-title uppercase">
                        #{{ $index + 1 }}. {{ $device->brand }} {{ $device->model }} [{{ $device->device_tracking_no }}]
                    </div>
                    @if($device->imei)
                        <div style="font-size: 9.5px;">IMEI: {{ $device->imei }}</div>
                    @endif
                    <div class="bold uppercase" style="font-size: 10px; margin: 2px 0;">
                        FAULT: {{ $device->problem_description }}
                    </div>

                    <!-- Spare Parts breakdown if selected -->
                    @if($device->parts->count() > 0)
                        <div style="margin-top: 3px; font-size: 9.5px; border-top: 1px dashed #000; padding-top: 2px;">
                            <span class="bold">ESTIMATED PARTS:</span>
                            @foreach($device->parts as $part)
                                <div>&bull; {{ $part->product->name }} (x{{ $part->quantity }}) - Rs {{ number_format($part->subtotal, 2) }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if($device->labor_cost > 0)
                        <div style="font-size: 9.5px;">
                            <span class="bold">LABOR / SERVICE:</span> Rs {{ number_format($device->labor_cost, 2) }}
                        </div>
                    @endif

                    @if($device->warranty_days > 0)
                        <div style="font-size: 9.5px;" class="bold">CHECKING WARRANTY: {{ $device->warranty_days }} DAYS</div>
                    @endif
                </div>
            @endforeach

            <!-- Financial Summary -->
            <table class="financial-table">
                <tr>
                    <td class="bold">TOTAL ESTIMATED:</td>
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
                <svg class="barcode" jsbarcode-value="{{ $repair->order_no }}" jsbarcode-format="CODE128" jsbarcode-width="1.6" jsbarcode-height="34" jsbarcode-fontSize="10"></svg>
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
                <span>CUSTOMER SIGN</span>
                <span>SHOP SIGN</span>
            </div>
        </div>

    </div>

    <script>
        function printBoth() {
            document.getElementById('stickerSection').style.display = 'block';
            document.getElementById('customerSection').style.display = 'block';
            window.print();
        }

        function printStickerOnly() {
            document.getElementById('stickerSection').style.display = 'block';
            document.getElementById('customerSection').style.display = 'none';
            window.print();
            // Restore visibility after print dialog closes
            setTimeout(function() {
                document.getElementById('customerSection').style.display = 'block';
            }, 1000);
        }

        function printCustomerOnly() {
            document.getElementById('stickerSection').style.display = 'none';
            document.getElementById('customerSection').style.display = 'block';
            window.print();
            // Restore visibility after print dialog closes
            setTimeout(function() {
                document.getElementById('stickerSection').style.display = 'block';
            }, 1000);
        }

        // Auto print on page load
        document.addEventListener("DOMContentLoaded", function () {
            if (typeof JsBarcode !== 'undefined') {
                JsBarcode(".barcode").init();
            }
            // Trigger print dialog automatically after slight render delay
            setTimeout(function () {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
