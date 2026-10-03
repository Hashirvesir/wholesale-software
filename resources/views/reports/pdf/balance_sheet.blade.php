@extends('reports.pdf.layout')

@section('title', 'Balance Sheet - ' . $date)
@section('report_name', 'Balance Sheet')

@section('content')
    <div class="details-box text-center">
        <strong>As of Date:</strong> {{ \Carbon\Carbon::parse($date)->format('d F, Y') }}
    </div>

    <div class="clearfix">
        <!-- Assets -->
        <div style="float: left; width: 48%;">
            <h3 style="color: #2563eb; border-bottom: 2px solid #2563eb; padding-bottom: 5px;">Assets</h3>
            <table>
                <tbody>
                    @php $sumAssets = 0; @endphp
                    @foreach ($assetsData as $asset)
                        <tr>
                            <td>{{ $asset['name'] }}</td>
                            <td class="text-right">{{ number_format($asset['amount'], 2) }}</td>
                        </tr>
                        @php $sumAssets += $asset['amount']; @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-bold" style="background: #f3f4f6;">
                        <td>Total Assets</td>
                        <td class="text-right">{{ number_format($sumAssets, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Liabilities & Equity -->
        <div style="float: right; width: 48%;">
            <h3 style="color: #dc2626; border-bottom: 2px solid #dc2626; padding-bottom: 5px;">Liabilities & Equity</h3>
            <table>
                <tbody>
                    @php $sumLiabilities = 0; @endphp
                    @foreach ($liabilitiesData as $liability)
                        <tr>
                            <td>{{ $liability['name'] }}</td>
                            <td class="text-right">{{ number_format($liability['amount'], 2) }}</td>
                        </tr>
                        @php $sumLiabilities += $liability['amount']; @endphp
                    @endforeach
                    <tr style="background: #f9f9f9;">
                        <td class="font-bold">Total Liabilities</td>
                        <td class="text-right font-bold">{{ number_format($sumLiabilities, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="height: 10px;"></td>
                    </tr>
                    <tr style="background: #fffbea;">
                        <td class="font-bold">Retained Earnings / Equity</td>
                        <td class="text-right font-bold">{{ number_format($equity, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="font-bold" style="background: #f3f4f6;">
                        <td>Total Equity & Liabilities</td>
                        <td class="text-right">{{ number_format($sumLiabilities + $equity, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Balance Check -->
    <div style="margin-top: 30px; text-align: center; font-style: italic; color: #666;">
        @if (round($totalAssets, 2) == round($totalLiabilities + $equity, 2))
            <span style="color: #059669; font-weight: bold;">✔ Assets match Liabilities & Equity.</span>
        @else
            <span style="color: #dc2626; font-weight: bold;">✘ Assets do not match Liabilities & Equity. Difference:
                {{ number_format(abs($totalAssets - ($totalLiabilities + $equity)), 2) }}</span>
        @endif
    </div>
@endsection
