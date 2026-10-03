@extends('reports.pdf.layout')

@section('title', 'Dead Stock Report')
@section('report_name', 'Dead Stock Report')

@section('content')
    <div class="details-box text-center">
        <strong>Threshold:</strong> Products with no sales in the last <strong>{{ $days }} days</strong>.
    </div>

    <table>
        <thead>
            <tr>
                <th width="35%">Product</th>
                <th width="15%">Category</th>
                <th width="12%" class="text-right">Qty</th>
                <th width="13%" class="text-right">Avg Cost</th>
                <th width="13%" class="text-right">Value</th>
                <th width="12%" class="text-center">Last Sale</th>
            </tr>
        </thead>
        <tbody>
            @php $totalValue = 0; @endphp
            @foreach ($products as $product)
                <tr>
                    <td>
                        <strong>{{ $product->name }}</strong><br>
                        <small>{{ $product->sku }} | {{ $product->brand->name ?? 'No Brand' }}</small>
                    </td>
                    <td>{{ $product->category->name }}</td>
                    <td class="text-right font-bold">{{ number_format($product->current_stock, 2) }}
                        {{ $product->unit->short_name ?? '' }}</td>
                    <td class="text-right">{{ number_format($product->avg_cost, 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($product->dead_stock_value, 2) }}</td>
                    <td class="text-center">
                        {{ $product->last_sale_date ? \Carbon\Carbon::parse($product->last_sale_date)->format('d-m-Y') : 'Never' }}<br>
                        <small>({{ $product->days_since_last_sale }} days)</small>
                    </td>
                </tr>
                @php $totalValue += $product->dead_stock_value; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background: #f3f4f6;">
                <td colspan="4" class="text-right">Total Dead Stock Value</td>
                <td class="text-right">{{ $settings->currency_symbol }} {{ number_format($totalValue, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
@endsection
