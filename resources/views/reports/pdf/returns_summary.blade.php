@extends('reports.pdf.layout')

@section('title', 'Returns Summary Report')
@section('report_name', 'Returns Summary Report')

@section('content')
    <div class="details-box text-center">
        <strong>Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}
    </div>

    <h4 style="text-transform: uppercase; font-size: 13px; margin-bottom: 8px;">Sales Returns</h4>
    <table>
        <thead>
            <tr>
                <th width="15%">Date</th>
                <th width="40%">Customer</th>
                <th width="20%">Invoice #</th>
                <th width="25%" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($salesReturns as $return)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($return->date)->format('d-m-Y') }}</td>
                    <td>{{ $return->party->name ?? 'Unknown' }}</td>
                    <td>{{ $return->sale_id ? '#' . $return->sale_id : '-' }}</td>
                    <td class="text-right">{{ number_format($return->total_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background: #f3f4f6;">
                <td colspan="3" class="text-right">Total</td>
                <td class="text-right">{{ number_format($totalSalesReturns, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <h4 style="text-transform: uppercase; font-size: 13px; margin-bottom: 8px;">Purchase Returns</h4>
    <table>
        <thead>
            <tr>
                <th width="15%">Date</th>
                <th width="40%">Vendor</th>
                <th width="20%">Purchase #</th>
                <th width="25%" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchaseReturns as $return)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($return->date)->format('d-m-Y') }}</td>
                    <td>{{ $return->party->name ?? 'Unknown' }}</td>
                    <td>{{ $return->purchase_id ? '#' . $return->purchase_id : '-' }}</td>
                    <td class="text-right">{{ number_format($return->total_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background: #f3f4f6;">
                <td colspan="3" class="text-right">Total</td>
                <td class="text-right">{{ number_format($totalPurchaseReturns, 2) }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
