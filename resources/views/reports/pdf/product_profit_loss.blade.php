@extends('reports.pdf.layout')

@section('title', 'Profit & Loss - ' . $product->name)
@section('report_name', 'Product Profit & Loss Analysis')

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
                    <strong>Total Revenue:</strong> {{ $settings->currency_symbol }}
                    {{ number_format($totalRevenue, 2) }}<br>
                    <strong>Net Profit:</strong> {{ $settings->currency_symbol }} {{ number_format($totalProfit, 2) }}
                </td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th width="10%">Date</th>
                <th width="8%">Type</th>
                <th width="12%">Invoice #</th>
                <th width="20%">Customer</th>
                <th width="8%" class="text-right">Qty</th>
                <th width="10%" class="text-right">Sale Price</th>
                <th width="10%" class="text-right">Revenue</th>
                <th width="10%" class="text-right">Total Cost</th>
                <th width="12%" class="text-right">Profit</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($profitLossDetails as $detail)
                <tr>
                    <td>{{ date('d-m-Y', strtotime($detail['date'])) }}</td>
                    <td>
                        <span style="font-weight: bold; color: {{ $detail['type'] == 'Sale' ? '#059669' : '#dc2626' }}">
                            {{ $detail['type'] }}
                        </span>
                    </td>
                    <td>{{ $settings->invoice_prefix }}{{ $detail['sale_id'] }}</td>
                    <td>{{ $detail['party'] }}</td>
                    <td class="text-right">{{ number_format($detail['qty'], 2) }}</td>
                    <td class="text-right">{{ number_format($detail['sale_price'], 2) }}</td>
                    <td class="text-right">{{ number_format($detail['revenue'], 2) }}</td>
                    <td class="text-right">{{ number_format($detail['total_cost'], 2) }}</td>
                    <td class="text-right"
                        style="font-weight: bold; color: {{ $detail['profit'] >= 0 ? '#4f46e5' : '#e11d48' }}">
                        {{ number_format($detail['profit'], 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot style="background-color: #f9fafb; font-weight: bold;">
            <tr>
                <td colspan="4" style="text-align: right;">Totals:</td>
                <td colspan="2"></td>
                <td class="text-right">{{ number_format($totalRevenue, 2) }}</td>
                <td class="text-right">{{ number_format($totalCost, 2) }}</td>
                <td class="text-right" style="color: {{ $totalProfit >= 0 ? '#4f46e5' : '#e11d48' }}; font-size: 14px;">
                    {{ $settings->currency_symbol }} {{ number_format($totalProfit, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 30px; text-align: right;">
        <div
            style="display: inline-block; padding: 20px; border-radius: 15px; border: 2px solid {{ $totalProfit >= 0 ? '#dcfce7' : '#fee2e2' }}; background-color: {{ $totalProfit >= 0 ? '#f0fdf4' : '#fef2f2' }}; min-width: 300px;">
            <table style="width: 100%; border: none;">
                <tr style="border: none;">
                    <td
                        style="border: none; text-align: left; color: #6b7280; text-transform: uppercase; font-size: 10px; font-weight: 800;">
                        Analysis Summary</td>
                    <td style="border: none;"></td>
                </tr>
                <tr style="border: none;">
                    <td style="border: none; text-align: left; padding-top: 10px;">Gross Revenue:</td>
                    <td style="border: none; text-align: right; padding-top: 10px; font-weight: bold;">
                        {{ $settings->currency_symbol }} {{ number_format($totalRevenue, 2) }}</td>
                </tr>
                <tr style="border: none;">
                    <td style="border: none; text-align: left;">Total COGS (Cost):</td>
                    <td style="border: none; text-align: right; font-weight: bold;">{{ $settings->currency_symbol }}
                        {{ number_format($totalCost, 2) }}</td>
                </tr>
                <tr style="border: none; border-top: 1px solid #e5e7eb;">
                    <td style="border: none; text-align: left; padding-top: 10px; font-weight: 900; font-size: 14px;">Net
                        Period Profit:</td>
                    <td
                        style="border: none; text-align: right; padding-top: 10px; font-weight: 900; font-size: 18px; color: {{ $totalProfit >= 0 ? '#15803d' : '#b91c1c' }};">
                        {{ $settings->currency_symbol }} {{ number_format($totalProfit, 2) }}
                    </td>
                </tr>
                <tr style="border: none;">
                    <td style="border: none; text-align: left; font-size: 11px; color: #6b7280;">Profit Margin:</td>
                    <td style="border: none; text-align: right; font-weight: bold; font-size: 11px; color: #6b7280;">
                        {{ $totalRevenue > 0 ? number_format(($totalProfit / $totalRevenue) * 100, 2) : 0 }}%
                    </td>
                </tr>
            </table>
        </div>
    </div>
@endsection
