<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Daily Customer Recovery & Route Sheet</title>
    <style>
        @page {
            margin: 0.35in 0.35in 0.4in 0.35in;
        }
        * {
            font-family: sans-serif !important;
            box-sizing: border-box;
        }
        body {
            font-size: 10px;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .company-info {
            float: left;
            width: 55%;
        }
        .report-info {
            float: right;
            width: 45%;
            text-align: right;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .meta-strip {
            width: 100%;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-size: 9.5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 10px;
        }
        th {
            background-color: #0f172a;
            color: #ffffff;
            text-transform: uppercase;
            font-size: 8.5px;
            font-weight: bold;
            padding: 5px 6px;
            border: 1px solid #0f172a;
            text-align: left;
        }
        td {
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            font-size: 9px;
            word-wrap: break-word;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        .box-input {
            border: 1px dashed #94a3b8;
            background: #ffffff;
            height: 22px;
            border-radius: 3px;
        }
        .sign-box {
            border: 1px dashed #94a3b8;
            background: #ffffff;
            height: 22px;
            border-radius: 3px;
        }
        .footer-signatures {
            margin-top: 15px;
            width: 100%;
        }
        .sig-block {
            float: left;
            width: 25%;
            text-align: center;
            padding: 0 10px;
        }
        .sig-line {
            border-top: 1px solid #475569;
            margin-top: 28px;
            padding-top: 4px;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header clearfix">
        <div class="company-info">
            <div class="company-name">{{ $settings->company_name ?? 'Alishba Communication' }}</div>
            <div style="font-size: 9px; color: #475569;">{{ $settings->company_address }}</div>
            <div style="font-size: 9px; color: #475569;">Tel: {{ $settings->company_phone }} | Email: {{ $settings->company_email }}</div>
        </div>
        <div class="report-info">
            <div class="report-title">Daily Recovery & Route Sheet</div>
            <div style="font-size: 9px; font-weight: bold; color: #0f172a;">
                Date: {{ now()->format('d M, Y (l)') }}
            </div>
            <div style="font-size: 8.5px; color: #64748b;">
                Printed on: {{ now()->format('d-m-Y h:i A') }} ({{ auth()->user()->name ?? 'System' }})
            </div>
        </div>
    </div>

    <!-- Meta Details Strip -->
    <div class="meta-strip clearfix">
        <div style="float: left; width: 60%;">
            <strong>Salesman / Recovery Officer:</strong> 
            <span style="font-weight: bold; color: #1e3a8a;">
                {{ $selectedSalesman ? $selectedSalesman->name . ' (' . ($selectedSalesman->phone ?? 'No Phone') . ')' : 'All Salesmen / General Recovery Route' }}
            </span>
            &nbsp; | &nbsp; 
            <strong>Shop / Source:</strong> {{ $selectedWarehouse ? $selectedWarehouse->name : 'All Shops' }}
        </div>
        <div style="float: right; width: 40%; text-align: right;">
            <strong>Target Accounts:</strong> {{ $routeCustomers->count() }} 
            &nbsp; | &nbsp; 
            <strong>Total Outstanding:</strong> 
            <span style="color: #b91c1c; font-weight: bold; font-size: 10px;">
                Rs {{ number_format($routeCustomers->sum('current_bal'), 2) }}
            </span>
        </div>
    </div>

    <!-- Route Sheet Table -->
    <table>
        <thead>
            <tr>
                <th width="3%" class="text-center">#</th>
                <th width="22%">Customer Name</th>
                <th width="12%">Contact / Phone</th>
                <th width="17%">Market Address</th>
                @if(!$selectedSalesman)
                    <th width="11%">Salesman</th>
                @endif
                <th width="13%" class="text-right">Balance Due</th>
                <th width="14%" class="text-center">Amount Collected (Rs)</th>
                <th width="12%" class="text-center">Customer Signature</th>
                <th width="10%">Notes / Chq #</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($routeCustomers as $index => $rc)
                <tr style="background: {{ $index % 2 === 0 ? '#ffffff' : '#f8fafc' }};">
                    <td class="text-center font-bold" style="color: #64748b;">{{ $index + 1 }}</td>
                    <td style="font-weight: bold; color: #0f172a; font-size: 9.5px;">
                        {{ $rc->name }}
                        @if($rc->price_tier && $rc->price_tier !== 'wholesale')
                            <span style="font-size: 7.5px; color: #64748b; font-weight: normal; text-transform: uppercase;">({{ $rc->price_tier }})</span>
                        @endif
                    </td>
                    <td style="color: #334155; font-size: 8.5px;">{{ $rc->phone ?? '-' }}</td>
                    <td style="color: #475569; font-size: 8.5px;">{{ $rc->address ?? '-' }}</td>
                    @if(!$selectedSalesman)
                        <td style="color: #1e3a8a; font-weight: bold; font-size: 8.5px;">{{ $rc->salesman->name ?? 'Unassigned' }}</td>
                    @endif
                    <td class="text-right font-bold" style="color: #b91c1c; font-size: 9.5px;">
                        Rs {{ number_format($rc->current_bal, 2) }}
                    </td>
                    <!-- Box for Writing Collected Amount -->
                    <td class="text-center" style="padding: 3px 4px;">
                        <div class="box-input"></div>
                    </td>
                    <!-- Box for Customer Signature -->
                    <td class="text-center" style="padding: 3px 4px;">
                        <div class="sign-box"></div>
                    </td>
                    <td style="border: 1px dashed #cbd5e1;"></td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $selectedSalesman ? '8' : '9' }}" class="text-center" style="padding: 15px; color: #94a3b8; font-style: italic;">
                        No pending receivable balance found for this salesman/route.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #e2e8f0; font-weight: bold; border-top: 2px solid #0f172a;">
                <td colspan="{{ $selectedSalesman ? '4' : '5' }}" class="text-right" style="text-transform: uppercase; font-size: 9px; padding: 6px;">
                    Total Route Outstanding Receivable:
                </td>
                <td class="text-right" style="color: #b91c1c; font-size: 10px; padding: 6px;">
                    Rs {{ number_format($routeCustomers->sum('current_bal'), 2) }}
                </td>
                <td class="text-center" style="font-size: 8.5px; color: #475569; padding: 6px;">
                    Total Cash: ____________
                </td>
                <td colspan="2" class="text-center" style="font-size: 8.5px; color: #475569; padding: 6px;">
                    Cheque: ____________
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Handover & Verification Signatures -->
    <div class="footer-signatures clearfix">
        <div class="sig-block">
            <div class="sig-line">Salesman / Recovery Officer</div>
        </div>
        <div class="sig-block">
            <div class="sig-line">Cashier / Accounts Handover</div>
        </div>
        <div class="sig-block">
            <div class="sig-line">Cash Received Verified</div>
        </div>
        <div class="sig-block">
            <div class="sig-line">Manager / Branch Approval</div>
        </div>
    </div>

</body>
</html>
