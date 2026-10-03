@extends('reports.pdf.layout')

@section('title', 'Stock History - ' . $product->name)
@section('report_name', 'Stock Movement History')

@section('content')
    <div class="details-box">
        <table style="margin-bottom: 0; border: none;">
            <tr style="border: none;">
                <td style="border: none; width: 50%;">
                    <strong>Product:</strong> {{ $product->name }}<br>
                    <strong>SKU:</strong> {{ $product->sku }}<br>
                    <strong>Category:</strong> {{ $product->category->name }}
                </td>
                <td style="border: none; width: 50%; text-align: right;">
                    <strong>Period:</strong> {{ $startDate ?: 'Beginning' }} to {{ $endDate }}<br>
                    <strong>Current Stock:</strong> {{ number_format($currentStockStatus, 2) }}
                    {{ $product->unit->short_name ?? '' }}<br>
                    <strong>Avg Cost:</strong> {{ $settings->currency_symbol }}
                    {{ number_format($currentAvgCostStatus, 2) }}
                </td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th width="10%">Date</th>
                <th width="12%">Type</th>
                <th width="12%">Ref #</th>
                <th width="20%">Party / Description</th>
                <th width="8%" class="text-right">Qty In</th>
                <th width="8%" class="text-right">Qty Out</th>
                <th width="8%" class="text-right">Rate</th>
                <th width="8%" class="text-right" style="color: #d97706;">Cargo</th>
                <th width="8%" class="text-right">Running Qty</th>
                <th width="8%" class="text-right">Avg Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($movements as $m)
                <tr>
                    <td>{{ date('d-m-Y', strtotime($m['date'])) }}</td>
                    <td>
                        <span
                            style="font-weight: bold; color: {{ in_array($m['type'], ['Purchase', 'Opening Stock', 'Sales Return']) ? '#059669' : '#dc2626' }}">
                            {{ $m['type'] }}
                        </span>
                    </td>
                    <td>{{ $m['reference'] }}</td>
                    <td>{{ $m['party'] ?? '-' }}</td>
                    <td class="text-right">{{ $m['qty_in'] > 0 ? number_format($m['qty_in'], 2) : '-' }}</td>
                    <td class="text-right">{{ $m['qty_out'] > 0 ? number_format($m['qty_out'], 2) : '-' }}</td>
                    <td class="text-right">{{ number_format($m['rate'], 2) }}</td>
                    <td class="text-right" style="{{ ($m['cargo_amount'] ?? 0) > 0 ? 'color:#d97706;font-weight:bold;' : 'color:#d1d5db;' }}">
                        {{ ($m['cargo_amount'] ?? 0) > 0 ? number_format($m['cargo_amount'], 2) : '—' }}
                    </td>
                    <td class="text-right" style="font-weight: bold;">{{ number_format($m['running_qty'], 2) }}</td>
                    <td class="text-right">{{ number_format($m['running_avg_cost'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot style="background-color: #f9fafb;">
            <tr>
                <td colspan="4" style="font-weight: bold; text-align: right;">Totals for Period:</td>
                <td class="text-right" style="font-weight: bold; color: #059669;">{{ number_format($totalQtyIn, 2) }}</td>
                <td class="text-right" style="font-weight: bold; color: #dc2626;">{{ number_format($totalQtyOut, 2) }}</td>
                <td colspan="4"></td>
            </tr>
            </tbody>
    </table>

    <div style="margin-top: 20px; text-align: right;">
        <div
            style="display: inline-block; background: #f3f4f6; padding: 15px; border-radius: 10px; border: 1px solid #e5e7eb; min-width: 250px;">
            <table style="width: 100%; border: none;">
                <tr style="border: none;">
                    <td
                        style="border: none; text-align: left; font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: bold;">
                        Final Stock:</td>
                    <td style="border: none; text-align: right; font-weight: bold;">
                        {{ number_format($currentStockStatus, 2) }}</td>
                </tr>
                <tr style="border: none;">
                    <td
                        style="border: none; text-align: left; font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: bold;">
                        Avg Cost:</td>
                    <td style="border: none; text-align: right; font-weight: bold;">{{ $settings->currency_symbol }}
                        {{ number_format($currentAvgCostStatus, 2) }}</td>
                </tr>
                <tr style="border: none; border-top: 1px solid #d1d5db;">
                    <td
                        style="border: none; text-align: left; font-size: 12px; text-transform: uppercase; color: #111827; font-weight: 900; padding-top: 5px;">
                        Total Inventory Value:</td>
                    <td
                        style="border: none; text-align: right; font-size: 14px; font-weight: 900; color: #b91c1c; padding-top: 5px;">
                        {{ $settings->currency_symbol }} {{ number_format($totalStockValue, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>
@endsection
