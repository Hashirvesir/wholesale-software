@extends('reports.pdf.layout')

@section('title', 'Daily Collection Report')
@section('report_name', 'Daily Collection Report')

@section('content')
    <div class="details-box text-center">
        <strong>Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}
        @if($selectedWarehouseId && ($wh = $warehouses->firstWhere('id', $selectedWarehouseId)))
            &nbsp; | &nbsp; <strong>Shop/Warehouse:</strong> {{ $wh->name }}
        @endif
        @if($selectedSalesmanId && ($sm = $salesmen->firstWhere('id', $selectedSalesmanId)))
            &nbsp; | &nbsp; <strong>Salesman:</strong> {{ $sm->name }}
        @endif
        @if($selectedAccountId && ($acc = $paymentAccounts->firstWhere('id', $selectedAccountId)))
            &nbsp; | &nbsp; <strong>Account:</strong> {{ $acc->name }}
        @endif
    </div>

    <h4 style="text-transform: uppercase; font-size: 13px; margin-bottom: 8px;">Account Summary</h4>
    <table>
        <thead>
            <tr>
                <th width="40%">Account</th>
                <th width="20%" class="text-right">Received</th>
                <th width="20%" class="text-right">Paid</th>
                <th width="20%" class="text-right">Net</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($accountSummary as $row)
                <tr>
                    <td>{{ $row['account']->name ?? 'Unknown' }}</td>
                    <td class="text-right">{{ number_format($row['received'], 2) }}</td>
                    <td class="text-right">{{ number_format($row['paid'], 2) }}</td>
                    <td class="text-right">{{ number_format($row['received'] - $row['paid'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background: #f3f4f6;">
                <td class="text-right">Total</td>
                <td class="text-right">{{ number_format($totalReceived, 2) }}</td>
                <td class="text-right">{{ number_format($totalPaid, 2) }}</td>
                <td class="text-right">{{ number_format($totalReceived - $totalPaid, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <h4 style="text-transform: uppercase; font-size: 13px; margin-bottom: 8px;">Transaction Detail</h4>
    <table>
        <thead>
            <tr>
                <th width="12%">Date</th>
                <th width="16%">Account</th>
                <th width="24%">Reference</th>
                <th width="14%">Shop</th>
                <th width="14%">Salesman</th>
                <th width="10%" class="text-right">Received</th>
                <th width="10%" class="text-right">Paid</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($entries as $entry)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($entry->date)->format('d-m-Y') }}</td>
                    <td>{{ $entry->account->name ?? 'Unknown' }}</td>
                    <td><small>{{ $entry->note }} ({{ $entry->reference_type }} #{{ $entry->reference_id }})</small></td>
                    <td><small>{{ $entry->warehouse->name ?? '-' }}</small></td>
                    <td><small>{{ $entry->resolved_salesman->name ?? '-' }}</small></td>
                    <td class="text-right">{{ $entry->debit > 0 ? number_format($entry->debit, 2) : '-' }}</td>
                    <td class="text-right">{{ $entry->credit > 0 ? number_format($entry->credit, 2) : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background: #f3f4f6;">
                <td colspan="5" class="text-right">Total</td>
                <td class="text-right">{{ number_format($totalReceived, 2) }}</td>
                <td class="text-right">{{ number_format($totalPaid, 2) }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
