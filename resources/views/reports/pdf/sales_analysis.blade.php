@extends('reports.pdf.layout')

@section('title', 'Sales Analysis Report')
@section('report_name', 'Sales Analysis Report')

@section('content')
    <div class="details-box text-center">
        <strong>Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }} — Top {{ $limit }}
    </div>

    <h4 style="text-transform: uppercase; font-size: 13px; margin-bottom: 8px;">Top Customers</h4>
    <table>
        <thead>
            <tr>
                <th width="6%">#</th>
                <th width="38%">Customer</th>
                <th width="14%" class="text-right">Invoices</th>
                <th width="14%" class="text-right">Gross Sales</th>
                <th width="14%" class="text-right">Returns</th>
                <th width="14%" class="text-right">Net Sales</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($topCustomers as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row->party->name ?? 'Unknown' }}</td>
                    <td class="text-right">{{ $row->invoice_count }}</td>
                    <td class="text-right">{{ number_format($row->total_sales, 2) }}</td>
                    <td class="text-right">{{ $row->total_returns > 0 ? '-' . number_format($row->total_returns, 2) : '-' }}</td>
                    <td class="text-right">{{ number_format($row->net_sales, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h4 style="text-transform: uppercase; font-size: 13px; margin-bottom: 8px;">Top Products</h4>
    <table>
        <thead>
            <tr>
                <th width="8%">#</th>
                <th width="42%">Product</th>
                <th width="16%" class="text-right">Qty Sold</th>
                <th width="16%" class="text-right">Returned</th>
                <th width="18%" class="text-right">Net Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($topProducts as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row->product->name ?? 'Unknown' }}</td>
                    <td class="text-right">{{ number_format($row->total_qty, 2) }}</td>
                    <td class="text-right">{{ $row->returned_qty > 0 ? '-' . number_format($row->returned_qty, 2) : '-' }}</td>
                    <td class="text-right">{{ number_format($row->net_revenue, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
