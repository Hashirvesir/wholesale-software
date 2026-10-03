@extends('reports.pdf.layout')

@section('title', 'Profit & Loss Statement')
@section('report_name', 'Profit & Loss Statement')

@section('content')
    <style>
        .pl-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .pl-table td {
            border: none;
            padding: 4px 0;
            vertical-align: bottom;
            font-family: 'Courier New', Courier, monospace;
            /* For precise alignment like a ledger */
            font-size: 12px;
            font-weight: bold;
        }

        .pl-table .label {
            width: 50%;
            text-transform: uppercase;
        }

        .pl-table .amount-sub {
            width: 25%;
            text-align: right;
            padding-right: 20px;
        }

        .pl-table .amount-total {
            width: 25%;
            text-align: right;
        }

        .line-separator {
            border-top: 1px solid #333;
            margin: 5px 0;
            width: 40%;
        }

        .line-full {
            border-top: 2px solid #333;
            margin: 10px 0;
        }

        .indent {
            padding-left: 20px !important;
        }

        .dot-indent::before {
            content: ". ";
        }
    </style>

    <div style="font-family: sans-serif; margin-bottom: 20px;">
        <div style="font-weight: bold; text-transform: uppercase; font-size: 14px;">
            FROM: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
            TO: {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        </div>
    </div>

    <table class="pl-table">
        <tbody>
            {{-- REVENUE SECTION --}}
            <tr>
                <td class="label">SALES</td>
                <td class="amount-sub"></td>
                <td class="amount-total">{{ number_format($totalSales, 2) }}</td>
            </tr>
            @if ($totalSalesReturn > 0)
                <tr>
                    <td class="label">SALES RETURN</td>
                    <td class="amount-sub"></td>
                    <td class="amount-total">-{{ number_format($totalSalesReturn, 2) }}</td>
                </tr>
            @endif

            <tr>
                <td colspan="3">
                    <div class="line-separator"></div>
                </td>
            </tr>

            <tr style="font-weight: bold;">
                <td class="label">TOTAL REVENUE</td>
                <td class="amount-sub"></td>
                <td class="amount-total">{{ number_format($netSales, 2) }}</td>
            </tr>

            {{-- COGS SECTION --}}
            <tr>
                <td class="label">OPENING STOCK</td>
                <td class="amount-sub">{{ number_format($openingValuation, 2) }}</td>
                <td class="amount-total"></td>
            </tr>
            <tr>
                <td class="label">PURCHASES</td>
                <td class="amount-sub">{{ number_format($totalPurchases, 2) }}</td>
                <td class="amount-total"></td>
            </tr>
            @if ($totalPurchaseReturn > 0)
                <tr>
                    <td class="label indent">(less) PURCHASE RETURN</td>
                    <td class="amount-sub">-{{ number_format($totalPurchaseReturn, 2) }}</td>
                    <td class="amount-total"></td>
                </tr>
            @endif

            <tr>
                <td colspan="3">
                    <div class="line-separator"></div>
                </td>
            </tr>

            <tr>
                <td class="label">TOTAL STOCK</td>
                <td class="amount-sub">{{ number_format($totalStock, 2) }}</td>
                <td class="amount-total"></td>
            </tr>
            <tr>
                <td class="label">(less) AVAILABLE STOCK</td>
                <td class="amount-sub">{{ number_format($closingValuation, 2) }}</td>
                <td class="amount-total"></td>
            </tr>
            <tr>
                <td class="label">(less) COST OF GOODS SOLD</td>
                <td class="amount-sub"></td>
                <td class="amount-total">{{ number_format($cogs, 2) }}</td>
            </tr>

            <tr>
                <td colspan="3">
                    <div class="line-separator"></div>
                </td>
            </tr>

            <tr style="font-weight: bold; font-size: 14px;">
                <td class="label">GROSS INCOME</td>
                <td class="amount-sub"></td>
                <td class="amount-total">{{ number_format($grossProfit, 2) }}</td>
            </tr>

            {{-- OTHER INCOME SECTION --}}
            @if (count($incomeData) > 0)
                <tr>
                    <td class="label" style="padding-top: 10px;">.OTHER INCOME</td>
                    <td class="amount-sub"></td>
                    <td class="amount-total"></td>
                </tr>
                @foreach ($incomeData as $income)
                    <tr>
                        <td class="label indent dot-indent">{{ $income['name'] }}</td>
                        <td class="amount-sub">{{ number_format($income['amount'], 2) }}</td>
                        <td class="amount-total"></td>
                    </tr>
                @endforeach
            @endif

            {{-- EXPENSES SECTION --}}
            <tr>
                <td class="label" style="padding-top: 20px;">.EXPENSES</td>
                <td class="amount-sub"></td>
                <td class="amount-total"></td>
            </tr>

            @foreach ($expenseData as $expense)
                <tr>
                    <td class="label indent dot-indent">{{ $expense['name'] }}</td>
                    <td class="amount-sub">{{ number_format($expense['amount'], 2) }}</td>
                    <td class="amount-total"></td>
                </tr>
            @endforeach

            <tr>
                <td colspan="3">
                    <div class="line-separator"></div>
                </td>
            </tr>

            <tr style="font-weight: bold;">
                <td class="label">TOTAL EXPENSES</td>
                <td class="amount-sub"></td>
                <td class="amount-total">-{{ number_format($totalOtherExpenses, 2) }}</td>
            </tr>

            {{-- FINAL NET PROFIT --}}
            <tr>
                <td colspan="3" style="padding-top: 40px;">
                    <div class="line-full"></div>
                </td>
            </tr>
            <tr style="font-weight: bold; font-size: 16px;">
                <td class="label">{{ $netProfit >= 0 ? 'NET PROFIT:' : 'NET LOSS:' }}</td>
                <td class="amount-sub"></td>
                <td class="amount-total">{{ number_format($netProfit, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3">
                    <div class="line-full"></div>
                </td>
            </tr>
        </tbody>
    </table>
@endsection
