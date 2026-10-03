@extends('reports.pdf.layout')

@section('title', 'Cargo Charges Report')
@section('report_name', 'Cargo Charges Report')

@section('content')
    <div class="details-box text-center">
        <strong>Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}
    </div>

    <h4 style="text-transform: uppercase; font-size: 13px; margin-bottom: 8px;">By Cargo Party</h4>
    <table>
        <thead>
            <tr>
                <th width="60%">Cargo Party</th>
                <th width="20%" class="text-right">Charges</th>
                <th width="20%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($partySummary as $row)
                <tr>
                    <td>{{ $row['party']->name ?? 'Unknown' }}</td>
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
                <th width="25%">Cargo Party</th>
                <th width="13%">Purchase #</th>
                <th width="15%" class="text-right">Weight</th>
                <th width="15%" class="text-right">Rate/Kg</th>
                <th width="20%" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cargoCharges as $charge)
                <tr>
                    <td>{{ $charge->purchase ? \Carbon\Carbon::parse($charge->purchase->date)->format('d-m-Y') : '-' }}</td>
                    <td>{{ $charge->cargoParty->name ?? 'Unknown' }}</td>
                    <td>{{ $charge->purchase_id ? '#' . $charge->purchase_id : '-' }}</td>
                    <td class="text-right">{{ $charge->total_weight > 0 ? number_format($charge->total_weight, 2) : '-' }}</td>
                    <td class="text-right">{{ $charge->rate_per_kg > 0 ? number_format($charge->rate_per_kg, 2) : '-' }}</td>
                    <td class="text-right">{{ number_format($charge->total_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background: #f3f4f6;">
                <td colspan="5" class="text-right">Total</td>
                <td class="text-right">{{ number_format($totalCargo, 2) }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
