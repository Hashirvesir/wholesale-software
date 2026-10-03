@extends('reports.pdf.layout')

@section('title', 'Trial Balance - ' . $date)
@section('report_name', 'Trial Balance')

@section('content')
    <div class="details-box text-center">
        <strong>As of Date:</strong> {{ \Carbon\Carbon::parse($date)->format('d F, Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th width="60%">Account Name</th>
                <th width="20%" class="text-right">Debit ({{ $settings->currency_symbol }})</th>
                <th width="20%" class="text-right">Credit ({{ $settings->currency_symbol }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reportData as $row)
                <tr>
                    <td>{{ $row['account_name'] }}</td>
                    <td class="text-right">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '-' }}</td>
                    <td class="text-right">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background: #f3f4f6;">
                <td class="text-right">Total Balance</td>
                <td class="text-right">{{ number_format($totalDebit, 2) }}</td>
                <td class="text-right">{{ number_format($totalCredit, 2) }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
