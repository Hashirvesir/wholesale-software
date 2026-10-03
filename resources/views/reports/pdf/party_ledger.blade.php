@extends('reports.pdf.layout')

@section('title', 'Party Ledger - ' . $party->name)
@section('report_name', 'Party Ledger')

@section('content')
    <div class="details-box">
        <table style="margin-bottom: 0; border: none;">
            <tr style="border: none;">
                <td style="border: none; width: 50%;">
                    <strong>Party:</strong> {{ $party->name }} ({{ strtoupper($party->type) }})<br>
                    <strong>Phone:</strong> {{ $party->phone }}<br>
                    <strong>Address:</strong> {{ $party->address }}
                </td>
                <td style="border: none; width: 50%; text-align: right;">
                    <strong>Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d M, Y') }} to
                    {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}<br>
                    <strong>Opening Balance:</strong> {{ $settings->currency_symbol }}
                    {{ number_format(abs($openingBalance), 2) }} {{ $openingBalance >= 0 ? 'Rec' : 'Pay' }}
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th width="12%">Date</th>
                <th width="15%">Ref</th>
                <th width="33%">Description / Account</th>
                <th width="13%" class="text-right">Debit (Rec)</th>
                <th width="13%" class="text-right">Credit (Pay)</th>
                <th width="14%" class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @php $currentBalance = $openingBalance; @endphp
            <tr>
                <td colspan="5" class="font-bold">Opening Balance</td>
                <td class="text-right font-bold">{{ number_format(abs($currentBalance), 2) }}
                    {{ $currentBalance >= 0 ? 'Rec' : 'Pay' }}</td>
            </tr>
            @foreach ($entries as $entry)
                @php $currentBalance += ($entry->debit - $entry->credit); @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($entry->date)->format('d-m-Y') }}</td>
                    <td>{{ $entry->reference_type }}-{{ $entry->reference_id }}</td>
                    <td>
                        {{ $entry->description ? $entry->description : $entry->contra_account_name }}
                    </td>
                    <td class="text-right">{{ $entry->debit > 0 ? number_format($entry->debit, 2) : '-' }}</td>
                    <td class="text-right">{{ $entry->credit > 0 ? number_format($entry->credit, 2) : '-' }}</td>
                    <td class="text-right">{{ number_format(abs($currentBalance), 2) }}
                        {{ $currentBalance >= 0 ? 'Rec' : 'Pay' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background: #f9f9f9;">
                <td colspan="3" class="text-right">Totals / Closing Balance</td>
                <td class="text-right">{{ number_format($totalDebit, 2) }}</td>
                <td class="text-right">{{ number_format($totalCredit, 2) }}</td>
                <td class="text-right" style="font-size: 13px;">{{ number_format(abs($currentBalance), 2) }}
                    {{ $currentBalance >= 0 ? 'Rec' : 'Pay' }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
