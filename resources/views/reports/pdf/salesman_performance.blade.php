@extends('reports.pdf.layout')

@section('title', 'Salesman & Recovery Commission Report')
@section('report_name', 'Salesman Performance & Commission')

@section('content')
    <style>
        @page {
            margin-top: 1.25in;
            margin-bottom: 0.5in;
            margin-left: 0.4in;
            margin-right: 0.4in;
        }

        .report-title {
            font-size: 15px !important;
            margin-top: 6px !important;
            letter-spacing: 0.5px;
        }

        .company-name {
            font-size: 16px !important;
        }

        * {
            font-family: sans-serif !important;
        }
    </style>

    <div class="details-box text-center" style="margin-bottom: 12px; padding: 7px 12px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px;">
        <strong>Period:</strong> {{ \Carbon\Carbon::parse($dateFrom)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('d-m-Y') }}
        &nbsp; | &nbsp; <strong>Salesman:</strong> {{ $selectedSalesman ? $selectedSalesman->name . ' (' . (float)$selectedSalesman->commission_rate . '% Comm.)' : 'All Salesmen / Staff' }}
        &nbsp; | &nbsp; <strong>Shop:</strong> {{ $selectedWarehouse ? $selectedWarehouse->name : 'All Shops' }}
        &nbsp; | &nbsp; <strong>Printed:</strong> {{ now()->format('d-m-Y h:i A') }} ({{ auth()->user()->name ?? 'System' }})
    </div>

    <!-- Summary Performance & Commission Metrics -->
    <h4 style="text-transform: uppercase; font-size: 11px; margin: 10px 0 6px 0; color: #1e293b; letter-spacing: 0.5px; font-weight: bold;">1. Performance & Commission Summary</h4>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;">
        <thead>
            <tr style="background-color: #f1f5f9;">
                <th width="30%" style="font-size: 9px; padding: 6px 8px; border: 1px solid #cbd5e1;">Metric</th>
                <th width="20%" class="text-center" style="font-size: 9px; padding: 6px 8px; border: 1px solid #cbd5e1;">Transactions</th>
                <th width="25%" class="text-right" style="font-size: 9px; padding: 6px 8px; border: 1px solid #cbd5e1;">Total Value (Rs)</th>
                <th width="25%" class="text-right" style="font-size: 9px; padding: 6px 8px; border: 1px solid #cbd5e1;">Commission ({{ $commissionRate }}%)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="font-weight: bold; padding: 6px 8px; border: 1px solid #cbd5e1; color: #0f172a;">Sales Booked</td>
                <td class="text-center font-bold" style="padding: 6px 8px; border: 1px solid #cbd5e1;">{{ number_format($sales->count()) }} Invoices</td>
                <td class="text-right font-bold" style="padding: 6px 8px; border: 1px solid #cbd5e1; color: #252ebf;">Rs {{ number_format($totalSales, 2) }}</td>
                <td class="text-right font-bold" style="padding: 6px 8px; border: 1px solid #cbd5e1; color: #d97706;">Rs {{ number_format($commissionOnSales, 2) }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 6px 8px; border: 1px solid #cbd5e1; color: #0f172a;">Cash Recoveries Collected</td>
                <td class="text-center font-bold" style="padding: 6px 8px; border: 1px solid #cbd5e1;">{{ number_format($collections->count()) }} Vouchers</td>
                <td class="text-right font-bold" style="padding: 6px 8px; border: 1px solid #cbd5e1; color: #16a34a;">Rs {{ number_format($totalCollected, 2) }}</td>
                <td class="text-right font-bold" style="padding: 6px 8px; border: 1px solid #cbd5e1; color: #7c3aed;">Rs {{ number_format($commissionOnRecovery, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Detailed Sales Invoices -->
    <h4 style="text-transform: uppercase; font-size: 11px; margin: 12px 0 6px 0; color: #1e293b; letter-spacing: 0.5px; font-weight: bold;">2. Sales Invoices Detail ({{ $sales->count() }})</h4>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;">
        <thead>
            <tr style="background-color: #f1f5f9;">
                <th width="12%" style="font-size: 8.5px; padding: 5px 6px; border: 1px solid #cbd5e1;">Date</th>
                <th width="12%" style="font-size: 8.5px; padding: 5px 6px; border: 1px solid #cbd5e1;">Invoice #</th>
                <th width="24%" style="font-size: 8.5px; padding: 5px 6px; border: 1px solid #cbd5e1;">Customer</th>
                <th width="16%" style="font-size: 8.5px; padding: 5px 6px; border: 1px solid #cbd5e1;">Salesman</th>
                <th width="12%" class="text-right" style="font-size: 8.5px; padding: 5px 6px; border: 1px solid #cbd5e1;">Total (Rs)</th>
                <th width="12%" class="text-right" style="font-size: 8.5px; padding: 5px 6px; border: 1px solid #cbd5e1;">Paid (Rs)</th>
                <th width="12%" class="text-right" style="font-size: 8.5px; padding: 5px 6px; border: 1px solid #cbd5e1;">Due (Rs)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sales as $sale)
                @php $due = max(0, $sale->total_amount - $sale->paid_amount); @endphp
                <tr>
                    <td style="padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 8.5px;">{{ \Carbon\Carbon::parse($sale->date)->format('d-m-Y') }}</td>
                    <td style="padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 8.5px; font-weight: bold; color: #252ebf;">#{{ $sale->id }}</td>
                    <td style="padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 8.5px; font-weight: bold;">{{ $sale->party->name ?? 'Walk-In' }}</td>
                    <td style="padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 8.5px;">{{ $sale->salesman->name ?? 'Unassigned' }}</td>
                    <td class="text-right" style="padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 8.5px; font-weight: bold;">{{ number_format($sale->total_amount, 2) }}</td>
                    <td class="text-right" style="padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 8.5px; color: #16a34a;">{{ number_format($sale->paid_amount, 2) }}</td>
                    <td class="text-right" style="padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 8.5px; font-weight: bold; color: {{ $due > 0 ? '#dc2626' : '#64748b' }};">{{ number_format($due, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 10px; color: #94a3b8; font-style: italic;">No sales recorded for this period.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f8fafc; font-weight: bold; border-top: 2px solid #94a3b8;">
                <td colspan="4" class="text-right" style="padding: 6px; font-size: 9px; text-transform: uppercase;">Total Sales:</td>
                <td class="text-right" style="padding: 6px; font-size: 9px; color: #252ebf;">Rs {{ number_format($totalSales, 2) }}</td>
                <td class="text-right" style="padding: 6px; font-size: 9px; color: #16a34a;">Rs {{ number_format($sales->sum('paid_amount'), 2) }}</td>
                <td class="text-right" style="padding: 6px; font-size: 9px; color: #dc2626;">Rs {{ number_format(max(0, $totalSales - $sales->sum('paid_amount')), 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Detailed Cash Collections -->
    <h4 style="text-transform: uppercase; font-size: 11px; margin: 12px 0 6px 0; color: #1e293b; letter-spacing: 0.5px; font-weight: bold;">3. Cash Recovery Payments Collected ({{ $collections->count() }})</h4>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
        <thead>
            <tr style="background-color: #f1f5f9;">
                <th width="12%" style="font-size: 8.5px; padding: 5px 6px; border: 1px solid #cbd5e1;">Date</th>
                <th width="12%" style="font-size: 8.5px; padding: 5px 6px; border: 1px solid #cbd5e1;">Voucher #</th>
                <th width="26%" style="font-size: 8.5px; padding: 5px 6px; border: 1px solid #cbd5e1;">Customer</th>
                <th width="18%" style="font-size: 8.5px; padding: 5px 6px; border: 1px solid #cbd5e1;">Account</th>
                <th width="16%" style="font-size: 8.5px; padding: 5px 6px; border: 1px solid #cbd5e1;">Collected By</th>
                <th width="16%" class="text-right" style="font-size: 8.5px; padding: 5px 6px; border: 1px solid #cbd5e1;">Amount (Rs)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($collections as $col)
                <tr>
                    <td style="padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 8.5px;">{{ \Carbon\Carbon::parse($col->date)->format('d-m-Y') }}</td>
                    <td style="padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 8.5px; font-weight: bold; color: #1e3a8a;">PV-{{ $col->id }}</td>
                    <td style="padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 8.5px; font-weight: bold;">{{ $col->party->name ?? 'N/A' }}</td>
                    <td style="padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 8.5px;">{{ $col->account->name ?? 'Cash' }}</td>
                    <td style="padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 8.5px;">{{ $col->salesman->name ?? 'Unassigned' }}</td>
                    <td class="text-right" style="padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 8.5px; font-weight: bold; color: #16a34a;">Rs {{ number_format($col->amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 10px; color: #94a3b8; font-style: italic;">No recovery payments collected in this period.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f8fafc; font-weight: bold; border-top: 2px solid #94a3b8;">
                <td colspan="5" class="text-right" style="padding: 6px; font-size: 9px; text-transform: uppercase;">Total Collected:</td>
                <td class="text-right" style="padding: 6px; font-size: 9px; color: #16a34a;">Rs {{ number_format($totalCollected, 2) }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
