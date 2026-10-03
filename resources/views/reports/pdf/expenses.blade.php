@extends('reports.pdf.layout')

@section('title', 'Expense Report')
@section('report_name', 'Expense Report')

@section('content')
    <div class="details-box text-center">
        <strong>Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}
    </div>

    <h4 style="text-transform: uppercase; font-size: 13px; margin-bottom: 8px;">By Category</h4>
    <table>
        <thead>
            <tr>
                <th width="60%">Category</th>
                <th width="20%" class="text-right">Count</th>
                <th width="20%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categorySummary as $row)
                <tr>
                    <td>{{ $row['category']->name ?? 'Uncategorized' }}</td>
                    <td class="text-right">{{ $row['count'] }}</td>
                    <td class="text-right">{{ number_format($row['total'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h4 style="text-transform: uppercase; font-size: 13px; margin-bottom: 8px;">Detail</h4>
    <table>
        <thead>
            <tr>
                <th width="12%">Date</th>
                <th width="20%">Category</th>
                <th width="18%">Paid From</th>
                <th width="30%">Note</th>
                <th width="20%" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($expenses as $expense)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($expense->date)->format('d-m-Y') }}</td>
                    <td>{{ $expense->category->name ?? 'Uncategorized' }}</td>
                    <td>{{ $expense->account->name ?? '-' }}</td>
                    <td><small>{{ $expense->note ?? '-' }}</small></td>
                    <td class="text-right">{{ number_format($expense->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background: #f3f4f6;">
                <td colspan="4" class="text-right">Total</td>
                <td class="text-right">{{ $settings->currency_symbol }} {{ number_format($totalExpense, 2) }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
