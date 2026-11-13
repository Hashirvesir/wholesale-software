<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repair Invoice - {{ $repair->order_no }}</title>
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

        .invoice-card {
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

        .invoice-title {
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

        .items-table {
            width: 100%;
            font-size: 11px;
            border-collapse: collapse;
            margin: 8px 0;
        }

        .items-table th {
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            padding: 4px 0;
            font-size: 11px;
            font-weight: 900 !important;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 3px 0;
            font-weight: 700;
            border-bottom: 1px dotted #000;
        }

        .device-row-title {
            font-weight: 900 !important;
            font-size: 11.5px;
            padding-top: 6px !important;
            border-bottom: 1px solid #000 !important;
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

        .warranty-box {
            border: 1.5px solid #000;
            padding: 6px 8px;
            font-size: 9.5px;
            font-weight: 700;
            margin-top: 8px;
            line-height: 1.35;
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
            .invoice-card {
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
        <button class="btn" onclick="window.print()">🖨️ PRINT INVOICE</button>
        <button class="btn" style="background:#555; border-color:#555;" onclick="window.close()">CLOSE</button>
    </div>

    <div class="invoice-card">
        <!-- Company Header -->
        <div class="company-header text-center">
            <h1>{{ $settings->company_name ?? 'INVENCO REPAIR LAB' }}</h1>
            <p>{{ $settings->address ?? 'Main Mobile Market, Electronics Center' }}</p>
            <p>PH: {{ $settings->phone ?? '0300-0000000' }}</p>
        </div>

        <!-- Invoice Title -->
        <div class="invoice-title">
            REPAIR INVOICE & CASH MEMO
        </div>

        <!-- Meta Details -->
        <table class="meta-table">
            <tr>
                <td class="bold">INVOICE NO:</td>
                <td class="text-right bold" style="font-size: 13px;">{{ $repair->order_no }}</td>
            </tr>
            <tr>
                <td class="bold">DATE:</td>
                <td class="text-right bold">{{ now()->format('d/m/Y h:i A') }}</td>
            </tr>
            <tr>
                <td class="bold">CUSTOMER:</td>
                <td class="text-right bold">{{ strtoupper($repair->customer_name) }}</td>
            </tr>
            <tr>
                <td class="bold">CONTACT:</td>
                <td class="text-right bold">{{ $repair->customer_phone }}</td>
            </tr>
            <tr>
                <td class="bold">PAYMENT:</td>
                <td class="text-right bold">
                    @if($repair->balance_amount <= 0)
                        PAID / CLEAR
                    @elseif($repair->advance_amount > 0)
                        PARTIALLY PAID
                    @else
                        UNPAID / ON CREDIT
                    @endif
                </td>
            </tr>
        </table>

        <!-- Itemized Devices & Parts -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="text-align: left;">ITEM / SERVICE</th>
                    <th class="text-center" style="width: 35px;">QTY</th>
                    <th class="text-right" style="width: 75px;">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($repair->devices as $device)
                    <tr>
                        <td colspan="3" class="device-row-title uppercase">
                            {{ $device->brand }} {{ $device->model }} [{{ $device->device_tracking_no }}]
                        </td>
                    </tr>
                    @foreach($device->parts as $part)
                        <tr>
                            <td style="padding-left: 8px;">* {{ $part->product->name }}</td>
                            <td class="text-center bold">{{ $part->quantity }}</td>
                            <td class="text-right bold">{{ number_format($part->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                    @if($device->labor_cost > 0)
                        <tr>
                            <td style="padding-left: 8px;">* REPAIR SERVICE / LABOR</td>
                            <td class="text-center bold">1</td>
                            <td class="text-right bold">{{ number_format($device->labor_cost, 2) }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

        <!-- Financial Summary -->
        <table class="financial-table">
            <tr>
                <td class="bold">TOTAL BILL:</td>
                <td class="text-right bold">Rs {{ number_format($repair->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="bold">TOTAL PAID:</td>
                <td class="text-right bold">Rs {{ number_format($repair->advance_amount, 2) }}</td>
            </tr>
            <tr class="grand-row">
                <td class="bold">BALANCE REMAINING:</td>
                <td class="text-right bold">Rs {{ number_format($repair->balance_amount, 2) }}</td>
            </tr>
        </table>

        <!-- Barcode -->
        <div class="barcode-section">
            <svg class="barcode" jsbarcode-value="{{ $repair->order_no }}" jsbarcode-format="CODE128" jsbarcode-width="1.6" jsbarcode-height="34" jsbarcode-fontSize="10"></svg>
        </div>

        <!-- Warranty Policy (Monochrome Bold) -->
        <div class="warranty-box">
            <span class="bold uppercase">WARRANTY POLICY:</span><br>
            - 7 Days checking warranty on replaced parts only.<br>
            - Physical damage, broken screen, or water damage has NO warranty.<br>
            - Warranty claim requires original printed invoice.
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
                window.print();
            }, 600);
        });
    </script>
</body>
</html>
