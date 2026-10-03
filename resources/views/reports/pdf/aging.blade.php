@extends('reports.pdf.layout')

@section('title', 'Aging Report')
@section('report_name', 'Aging Report')

@section('content')
    <div class="details-box text-center">
        <strong>As of:</strong> {{ \Carbon\Carbon::parse($asOfDate)->format('d-m-Y') }}
    </div>

    @foreach ([['title' => 'Receivables (Customers)', 'rows' => $receivables], ['title' => 'Payables (Vendors & Cargo)', 'rows' => $payables]] as $section)
        <h4 style="text-transform: uppercase; font-size: 13px; margin-bottom: 8px;">{{ $section['title'] }}</h4>
        <table>
            <thead>
                <tr>
                    <th width="24%">Party</th>
                    <th width="19%" class="text-right">0-30 Days</th>
                    <th width="19%" class="text-right">31-60 Days</th>
                    <th width="19%" class="text-right">61-90 Days</th>
                    <th width="19%" class="text-right">90+ Days</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($section['rows'] as $row)
                    <tr>
                        <td>{{ $row['party']->name ?? 'Unknown' }}</td>
                        <td class="text-right">{{ number_format($row['bucket_0_30'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['bucket_31_60'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['bucket_61_90'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['bucket_90_plus'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No outstanding balances found.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="font-bold" style="background: #f3f4f6;">
                    <td class="text-right">Total</td>
                    <td class="text-right">{{ number_format($section['rows']->sum('bucket_0_30'), 2) }}</td>
                    <td class="text-right">{{ number_format($section['rows']->sum('bucket_31_60'), 2) }}</td>
                    <td class="text-right">{{ number_format($section['rows']->sum('bucket_61_90'), 2) }}</td>
                    <td class="text-right">{{ number_format($section['rows']->sum('bucket_90_plus'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @endforeach
@endsection
