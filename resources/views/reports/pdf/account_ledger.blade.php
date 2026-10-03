@extends('reports.pdf.layout')

@section('title', 'Account Ledger - ' . $account->name)
@section('report_name', 'Account Ledger')

@section('content')
    <div class="details-box">
        <table style="margin-bottom: 0; border: none;">
            <tr style="border: none;">
                <td style="border: none; width: 50%;">
                    <strong>Account:</strong> {{ $account->name }} ({{ $account->type }})<br>
                    <strong>Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d M, Y') }} to
                    {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}
                </td>
                <td style="border: none; width: 50%; text-align: right;">
                    @if ($selectedWarehouseId)
                        <strong>Warehouse:</strong> {{ \App\Models\Warehouse::find($selectedWarehouseId)->name }}<br>
                    @endif
                    <strong>Opening Balance:</strong> {{ $settings->currency_symbol }}
                    {{ number_format(abs($openingBalance), 2) }} {{ $openingBalance >= 0 ? 'Dr' : 'Cr' }}
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th width="12%">Date</th>
                <th width="15%">Ref</th>
                <th width="33%">Particulars / Party</th>
                <th width="13%" class="text-right">Debit</th>
                <th width="13%" class="text-right">Credit</th>
                <th width="14%" class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @php $currentBalance = $openingBalance; @endphp
            <tr>
                <td colspan="5" class="font-bold">Opening Balance</td>
                <td class="text-right font-bold">{{ number_format(abs($currentBalance), 2) }}
                    {{ $currentBalance >= 0 ? 'Dr' : 'Cr' }}</td>
            </tr>
            @foreach ($entries as $entry)
                @php
                    if (in_array($account->type, ['Asset', 'Expense'])) {
                        $currentBalance += $entry->debit - $entry->credit;
                    } else {
                        $currentBalance += $entry->credit - $entry->debit;
                    }
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($entry->date)->format('d-m-Y') }}</td>
                    <td>{{ $entry->reference_type }}-{{ $entry->reference_id }}</td>
                    <td>
                        {{ $entry->description ?: $entry->contra_account_name }}
                        @if ($entry->party)
                            <br><small>Party: {{ $entry->party->name }}</small>
                        @endif
                    </td>
                    <td class="text-right">{{ $entry->debit > 0 ? number_format($entry->debit, 2) : '-' }}</td>
                    <td class="text-right">{{ $entry->credit > 0 ? number_format($entry->credit, 2) : '-' }}</td>
                    <td class="text-right">{{ number_format(abs($currentBalance), 2) }}
                        {{ $currentBalance >= 0 ? 'Dr' : 'Cr' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background: #f9f9f9;">
                <td colspan="3" class="text-right">Totals / Closing Balance</td>
                <td class="text-right">{{ number_format($totalDebit, 2) }}</td>
                <td class="text-right">{{ number_format($totalCredit, 2) }}</td>
                <td class="text-right" style="font-size: 13px;">{{ number_format(abs($currentBalance), 2) }}
                    {{ $currentBalance >= 0 ? 'Dr' : 'Cr' }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
