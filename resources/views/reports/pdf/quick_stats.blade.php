@extends('reports.pdf.layout')

@section('title', 'Quick Financial Snapshot')
@section('report_name', 'Quick Financial Snapshot')

@section('content')
    <style>
        @page {
            margin-top: 1.25in;
            margin-bottom: 0.5in;
            margin-left: 0.4in;
            margin-right: 0.4in;
        }

        .report-title {
            font-size: 16px !important;
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

    <div class="details-box text-center" style="margin-bottom: 15px; padding: 7px 12px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px;">
        <strong>Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} 
        @if($startDate !== $endDate)
            to {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}
        @else
            (Single Day)
        @endif
        &nbsp; | &nbsp; <strong>Shop / Warehouse:</strong> {{ $warehouse ? $warehouse->name : 'All Shops / Warehouses' }}
        &nbsp; | &nbsp; <strong>Generated:</strong> {{ now()->format('d-m-Y h:i A') }} ({{ auth()->user()->name ?? 'System' }})
    </div>

    <!-- Key Performance Indicators (Cards / Grid Table) -->
    <h4 style="text-transform: uppercase; font-size: 11px; margin: 10px 0 6px 0; color: #1e293b; letter-spacing: 0.5px; font-weight: bold;">1. Business Trading & Activity Overview</h4>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;">
        <thead>
            <tr style="background-color: #f1f5f9;">
                <th width="30%" style="font-size: 9px; padding: 6px 8px; border: 1px solid #cbd5e1;">Metric</th>
                <th width="20%" class="text-center" style="font-size: 9px; padding: 6px 8px; border: 1px solid #cbd5e1;">Transactions / Qty</th>
                <th width="25%" class="text-right" style="font-size: 9px; padding: 6px 8px; border: 1px solid #cbd5e1;">Total Value (Rs)</th>
                <th width="25%" style="font-size: 9px; padding: 6px 8px; border: 1px solid #cbd5e1;">Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="font-weight: bold; padding: 6px 8px; border: 1px solid #cbd5e1; color: #0f172a;">Sales Invoices</td>
                <td class="text-center" style="padding: 6px 8px; border: 1px solid #cbd5e1; font-weight: bold;">{{ number_format($salesCount) }}</td>
                <td class="text-right" style="padding: 6px 8px; border: 1px solid #cbd5e1; font-weight: bold; color: #252ebf;">Rs {{ number_format($salesToday, 2) }}</td>
                <td style="padding: 6px 8px; border: 1px solid #cbd5e1; color: #64748b; font-size: 10px;">Total sales billed during period</td>
            </tr>
            @if($repairsCount > 0 || $repairsToday > 0)
            <tr>
                <td style="font-weight: bold; padding: 6px 8px; border: 1px solid #cbd5e1; color: #0f172a;">Mobile Repairs & Lab</td>
                <td class="text-center" style="padding: 6px 8px; border: 1px solid #cbd5e1; font-weight: bold;">{{ number_format($repairsCount) }}</td>
                <td class="text-right" style="padding: 6px 8px; border: 1px solid #cbd5e1; font-weight: bold; color: #4f46e5;">Rs {{ number_format($repairsToday, 2) }}</td>
                <td style="padding: 6px 8px; border: 1px solid #cbd5e1; color: #64748b; font-size: 10px;">Repair jobs billed / booked</td>
            </tr>
            @endif
            <tr>
                <td style="font-weight: bold; padding: 6px 8px; border: 1px solid #cbd5e1; color: #0f172a;">Purchases</td>
                <td class="text-center" style="padding: 6px 8px; border: 1px solid #cbd5e1; font-weight: bold;">{{ number_format($purchasesCount) }}</td>
                <td class="text-right" style="padding: 6px 8px; border: 1px solid #cbd5e1; font-weight: bold; color: #334155;">Rs {{ number_format($purchasesToday, 2) }}</td>
                <td style="padding: 6px 8px; border: 1px solid #cbd5e1; color: #64748b; font-size: 10px;">Stock purchases recorded</td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 6px 8px; border: 1px solid #cbd5e1; color: #0f172a;">Operating Expenses</td>
                <td class="text-center" style="padding: 6px 8px; border: 1px solid #cbd5e1; font-weight: bold;">{{ number_format($expensesCount) }}</td>
                <td class="text-right" style="padding: 6px 8px; border: 1px solid #cbd5e1; font-weight: bold; color: #e11d48;">Rs {{ number_format($expensesToday, 2) }}</td>
                <td style="padding: 6px 8px; border: 1px solid #cbd5e1; color: #64748b; font-size: 10px;">Daily business expenses</td>
            </tr>
        </tbody>
    </table>

    <!-- Cash & Bank Flow Summary -->
    <h4 style="text-transform: uppercase; font-size: 11px; margin: 10px 0 6px 0; color: #1e293b; letter-spacing: 0.5px; font-weight: bold;">2. Cash & Bank Movement (Liquidity Summary)</h4>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
        <thead>
            <tr style="background-color: #f1f5f9;">
                <th width="40%" style="font-size: 9px; padding: 6px 8px; border: 1px solid #cbd5e1;">Account Name</th>
                <th width="20%" class="text-right" style="font-size: 9px; padding: 6px 8px; border: 1px solid #cbd5e1; color: #16a34a;">Cash In (Received)</th>
                <th width="20%" class="text-right" style="font-size: 9px; padding: 6px 8px; border: 1px solid #cbd5e1; color: #dc2626;">Cash Out (Paid)</th>
                <th width="20%" class="text-right" style="font-size: 9px; padding: 6px 8px; border: 1px solid #cbd5e1;">Net Movement</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movements as $accountName => $details)
                <tr>
                    <td style="font-weight: bold; padding: 6px 8px; border: 1px solid #cbd5e1; color: #1e293b;">{{ $accountName }}</td>
                    <td class="text-right" style="padding: 6px 8px; border: 1px solid #cbd5e1; color: #16a34a; font-weight: bold;">{{ number_format($details['in'], 2) }}</td>
                    <td class="text-right" style="padding: 6px 8px; border: 1px solid #cbd5e1; color: #dc2626; font-weight: bold;">{{ number_format($details['out'], 2) }}</td>
                    <td class="text-right" style="padding: 6px 8px; border: 1px solid #cbd5e1; font-weight: bold; color: {{ $details['net'] >= 0 ? '#252ebf' : '#dc2626' }};">
                        {{ $details['net'] >= 0 ? '+' : '' }}{{ number_format($details['net'], 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding: 12px; color: #94a3b8; font-style: italic; border: 1px solid #cbd5e1;">No bank or cash movements recorded for this period.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f8fafc; font-weight: bold; border-top: 2px solid #94a3b8;">
                <td style="padding: 7px 8px; border: 1px solid #cbd5e1; text-transform: uppercase; font-size: 10px;">Total Liquid Flow</td>
                <td class="text-right" style="padding: 7px 8px; border: 1px solid #cbd5e1; color: #16a34a; font-size: 11px;">Rs {{ number_format($totalReceivedToday, 2) }}</td>
                <td class="text-right" style="padding: 7px 8px; border: 1px solid #cbd5e1; color: #dc2626; font-size: 11px;">Rs {{ number_format($totalPaidToday, 2) }}</td>
                <td class="text-right" style="padding: 7px 8px; border: 1px solid #cbd5e1; font-size: 11px; color: {{ $netFlow >= 0 ? '#16a34a' : '#dc2626' }};">
                    {{ $netFlow >= 0 ? '+' : '' }}Rs {{ number_format($netFlow, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Net Cash Summary Card -->
    <div style="margin-top: 5px; width: 55%; float: right; font-family: sans-serif;">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
            <tr>
                <td style="padding: 5px 8px; font-weight: bold; background: #f8fafc; border: 1px solid #cbd5e1; font-size: 10px; text-transform: uppercase; color: #475569;">Total Cash & Bank Inflow:</td>
                <td class="text-right" style="padding: 5px 8px; font-weight: bold; background: #f8fafc; border: 1px solid #cbd5e1; font-size: 11px; color: #16a34a;">Rs {{ number_format($totalReceivedToday, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 8px; font-weight: bold; background: #f8fafc; border: 1px solid #cbd5e1; font-size: 10px; text-transform: uppercase; color: #475569;">Total Cash & Bank Outflow:</td>
                <td class="text-right" style="padding: 5px 8px; font-weight: bold; background: #f8fafc; border: 1px solid #cbd5e1; font-size: 11px; color: #dc2626;">Rs {{ number_format($totalPaidToday, 2) }}</td>
            </tr>
            <tr style="background: {{ $netFlow >= 0 ? '#f0fdf4' : '#fef2f2' }};">
                <td style="padding: 7px 8px; font-weight: bold; border: 1px solid {{ $netFlow >= 0 ? '#86efac' : '#fca5a5' }}; font-size: 11px; text-transform: uppercase; color: #0f172a;">Net Balance Delta:</td>
                <td class="text-right" style="padding: 7px 8px; font-weight: bold; border: 1px solid {{ $netFlow >= 0 ? '#86efac' : '#fca5a5' }}; font-size: 12px; color: {{ $netFlow >= 0 ? '#15803d' : '#b91c1c' }};">
                    {{ $netFlow >= 0 ? '+' : '' }}Rs {{ number_format($netFlow, 2) }}
                </td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
@endsection
