@extends('reports.pdf.layout')

@section('title', 'Stock Alert Report')
@section('report_name', 'Stock Alert Report')

@section('content')
    <div class="details-box text-center">
        <strong>Filter:</strong> Items below {{ $threshold }} threshold.
    </div>

    <h3 style="color: #dc2626; border-bottom: 2px solid #dc2626; padding-bottom: 5px;">Out of Stock</h3>
    <table>
        <thead>
            <tr>
                <th width="40%">Product</th>
                <th width="20%">Category</th>
                <th width="15%" class="text-right">Alert Qty</th>
                <th width="15%" class="text-right">Current Stock</th>
                <th width="10%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($outOfStock as $product)
                <tr>
                    <td><strong>{{ $product->name }}</strong><br><small>{{ $product->sku }}</small></td>
                    <td>{{ $product->category->name }}</td>
                    <td class="text-right">{{ number_format($product->alert_quantity, 2) }}</td>
                    <td class="text-right font-bold" style="color: #dc2626;">{{ number_format($product->current_stock, 2) }}
                    </td>
                    <td class="text-center font-bold" style="color: #dc2626; text-transform: uppercase;">Zero</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center italic">No products currently out of stock.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3 style="color: #b45309; border-bottom: 2px solid #b45309; padding-bottom: 5px; margin-top: 30px;">Low Stock (Below
        Alert Qty)</h3>
    <table>
        <thead>
            <tr>
                <th width="30%">Product</th>
                <th width="15%">Category</th>
                <th width="15%" class="text-right">Current Stock</th>
                <th width="15%" class="text-right">Alert Qty</th>
                <th width="15%" class="text-right">Reorder Qty</th>
                <th width="10%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td><strong>{{ $product->name }}</strong><br><small>{{ $product->sku }}</small></td>
                    <td>{{ $product->category->name }}</td>
                    <td class="text-right font-bold"
                        style="color: {{ $product->status_color === 'red' ? '#dc2626' : '#b45309' }};">
                        {{ number_format($product->current_stock, 2) }} {{ $product->unit->short_name ?? '' }}
                    </td>
                    <td class="text-right">{{ number_format($product->alert_quantity, 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($product->suggested_reorder, 2) }}</td>
                    <td class="text-center">
                        <span
                            style="color: {{ $product->status_color === 'red' ? '#dc2626' : '#b45309' }}; font-weight: bold; text-transform: uppercase; font-size: 9px;">
                            {{ $product->status_label }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
