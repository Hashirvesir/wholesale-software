@extends('reports.pdf.layout')

@section('title', 'Customer Product Last Price Report')
@section('report_name', 'Customer Product Last Price Report')

@section('content')
    <div class="details-box text-center" style="margin-bottom: 15px; font-size: 11px;">
        @if ($selectedCustomer)
            <strong>Customer:</strong> {{ $selectedCustomer->name }} {{ $selectedCustomer->phone ? "({$selectedCustomer->phone})" : '' }} &nbsp;|&nbsp;
        @else
            <strong>Customer:</strong> All Customers &nbsp;|&nbsp;
        @endif
        @if ($startDate || $endDate)
            <strong>Period:</strong> {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d-m-Y') : 'Earliest' }} to {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d-m-Y') : 'Today' }} &nbsp;|&nbsp;
        @endif
        <strong>Generated:</strong> {{ now()->format('d-m-Y h:i A') }}
    </div>

    <table>
        <thead>
            <tr>
                <th width="18%">Customer</th>
                <th width="22%">Product / SKU</th>
                <th width="10%">Last Date</th>
                <th width="10%">Invoice #</th>
                <th width="10%" class="text-right">Last Price</th>
                <th width="6%" class="text-right">Qty</th>
                <th width="8%" class="text-right">Std Price</th>
                <th width="8%" class="text-right">Lifetime Qty</th>
                <th width="8%" class="text-right">Lifetime Spend</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $row)
                @php
                    $diff = (float)$row->last_price - (float)$row->standard_price;
                @endphp
                <tr>
                    <td>
                        <strong>{{ $row->customer_name }}</strong>
                        @if ($row->customer_phone)
                            <div style="font-size: 9px; color: #666;">{{ $row->customer_phone }}</div>
                        @endif
                    </td>
                    <td>
                        {{ $row->product_name }}
                        @if ($row->product_sku)
                            <div style="font-size: 9px; color: #666;">SKU: {{ $row->product_sku }}</div>
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($row->last_sale_date)->format('d-m-Y') }}</td>
                    <td>INV-{{ str_pad($row->last_sale_id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td class="text-right" style="font-weight: bold; color: #059669;">
                        {{ number_format($row->last_price, 2) }}
                    </td>
                    <td class="text-right">{{ number_format($row->last_qty) }}</td>
                    <td class="text-right">{{ number_format($row->standard_price, 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($row->total_qty) }}</td>
                    <td class="text-right font-bold">{{ number_format($row->total_amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px;">No sales records found matching the criteria.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background: #f3f4f6;">
                <td colspan="7" class="text-right">Total Summary ({{ $records->count() }} records)</td>
                <td class="text-right">{{ number_format($records->sum('total_qty')) }}</td>
                <td class="text-right">{{ number_format($records->sum('total_amount'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
