<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Filter Header Card -->
            <div class="bg-white dark:bg-gray-800 shadow-xl rounded-3xl border border-gray-100 dark:border-gray-700 p-6 md:p-8">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b dark:border-gray-700 pb-6">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="p-2.5 rounded-2xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 font-bold text-lg">
                                📊
                            </span>
                            <div>
                                <h2 class="text-2xl font-black text-gray-800 dark:text-white tracking-tight uppercase italic">
                                    Profit & Loss Statement
                                </h2>
                                <p class="text-xs text-gray-400 font-bold tracking-wider uppercase mt-0.5">
                                    Trading & Operating Performance for {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('reports.profit-loss') }}" method="GET" class="flex flex-wrap gap-3 items-end w-full md:w-auto">
                        <div>
                            <x-input-label for="warehouse_id" :value="__('Warehouse')" class="text-[10px] font-black uppercase tracking-widest mb-1 text-gray-500" />
                            <select name="warehouse_id" id="warehouse_id"
                                class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-xl text-xs uppercase font-bold py-2.5"
                                style="min-width: 140px;">
                                <option value="">All Warehouses</option>
                                @foreach ($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="start_date" :value="__('From Date')" class="text-[10px] font-black uppercase tracking-widest mb-1 text-gray-500" />
                            <x-text-input type="date" name="start_date" id="start_date"
                                class="block w-full !rounded-xl text-xs py-2" :value="$startDate" />
                        </div>
                        <div>
                            <x-input-label for="end_date" :value="__('To Date')" class="text-[10px] font-black uppercase tracking-widest mb-1 text-gray-500" />
                            <x-text-input type="date" name="end_date" id="end_date"
                                class="block w-full !rounded-xl text-xs py-2" :value="$endDate" />
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-black text-xs uppercase tracking-wider transition shadow-md shadow-indigo-200 dark:shadow-none">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                                    </path>
                                </svg>
                                Filter
                            </button>
                            <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" target="_blank"
                                class="inline-flex items-center px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-black text-xs uppercase tracking-wider transition shadow-md shadow-red-200 dark:shadow-none">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                    </path>
                                </svg>
                                PDF
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Top Metric Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                    <div class="bg-gray-50 dark:bg-gray-900/60 p-4 rounded-2xl border border-gray-100 dark:border-gray-700/60">
                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">Net Revenue</span>
                        <div class="text-xl font-black font-mono text-gray-800 dark:text-gray-100 mt-1">
                            {{ $settings->currency_symbol }} {{ number_format($netSales, 2) }}
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/60 p-4 rounded-2xl border border-gray-100 dark:border-gray-700/60">
                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">Cost of Sales (COGS)</span>
                        <div class="text-xl font-black font-mono text-red-500 mt-1">
                            {{ $settings->currency_symbol }} {{ number_format($cogs, 2) }}
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/60 p-4 rounded-2xl border border-gray-100 dark:border-gray-700/60">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">Gross Profit</span>
                            <span class="text-[10px] font-black px-2 py-0.5 rounded-full {{ $grossMarginPercent >= 0 ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-700' }}">
                                {{ $grossMarginPercent }}%
                            </span>
                        </div>
                        <div class="text-xl font-black font-mono {{ $grossProfit >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                            {{ $settings->currency_symbol }} {{ number_format($grossProfit, 2) }}
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/60 p-4 rounded-2xl border border-gray-100 dark:border-gray-700/60">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">Net Profit</span>
                            <span class="text-[10px] font-black px-2 py-0.5 rounded-full {{ $netMarginPercent >= 0 ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300' : 'bg-red-100 text-red-700' }}">
                                {{ $netMarginPercent }}%
                            </span>
                        </div>
                        <div class="text-xl font-black font-mono {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                            {{ $settings->currency_symbol }} {{ number_format($netProfit, 2) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Breakdown Sections -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- Left Column: Trading Account (Revenue & Cost of Goods Sold) -->
                <div class="bg-white dark:bg-gray-800 shadow-xl rounded-3xl border border-gray-100 dark:border-gray-700 p-8">
                    <div class="flex justify-between items-center mb-6 pb-3 border-b dark:border-gray-700">
                        <h3 class="text-base font-black text-gray-800 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <span class="p-1.5 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600">📦</span>
                            Trading Account & COGS
                        </h3>
                        <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500">
                            Sales Margin: {{ $grossMarginPercent }}%
                        </span>
                    </div>

                    <div class="space-y-3">
                        <!-- Revenue Sub-section -->
                        <div class="flex justify-between items-center text-xs py-1">
                            <span class="text-gray-500 dark:text-gray-400 font-bold uppercase">Total Sales (Gross)</span>
                            <span class="font-black font-mono text-sm text-gray-800 dark:text-gray-200">
                                {{ number_format($totalSales, 2) }}
                            </span>
                        </div>
                        @if ($totalSalesReturn > 0)
                            <div class="flex justify-between items-center text-xs py-1 text-red-500">
                                <span class="font-bold uppercase">Less: Sales Return</span>
                                <span class="font-black font-mono text-sm">- {{ number_format($totalSalesReturn, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between items-center bg-emerald-50/70 dark:bg-emerald-900/20 p-3 rounded-xl border border-emerald-100 dark:border-emerald-800/40">
                            <span class="text-xs uppercase font-black tracking-wider text-emerald-800 dark:text-emerald-300">Net Revenue</span>
                            <span class="font-black font-mono text-base text-emerald-700 dark:text-emerald-300">
                                {{ $settings->currency_symbol }} {{ number_format($netSales, 2) }}
                            </span>
                        </div>

                        <!-- COGS Section -->
                        <div class="pt-6">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3 flex items-center justify-between">
                                <span>Cost of Goods Sold Breakdown</span>
                                <span class="text-[9px] font-bold text-gray-400">Trading Method</span>
                            </h4>

                            <div class="space-y-2 border-l-2 border-gray-100 dark:border-gray-700 pl-3">
                                <div class="flex justify-between items-center text-xs py-0.5">
                                    <span class="text-gray-600 dark:text-gray-400 font-semibold">Opening Stock (at cost)</span>
                                    <span class="font-bold font-mono text-gray-800 dark:text-gray-200">{{ number_format($openingValuation, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-xs py-0.5">
                                    <span class="text-gray-600 dark:text-gray-400 font-semibold">Purchases (Goods)</span>
                                    <span class="font-bold font-mono text-gray-800 dark:text-gray-200">{{ number_format($grossPurchases, 2) }}</span>
                                </div>
                                @if ($totalPurchaseDiscount > 0)
                                    <div class="flex justify-between items-center text-xs py-0.5 text-amber-600 dark:text-amber-400">
                                        <span class="font-semibold">Less: Purchase Discounts</span>
                                        <span class="font-bold font-mono">- {{ number_format($totalPurchaseDiscount, 2) }}</span>
                                    </div>
                                @endif
                                @if ($totalCargoCharges > 0)
                                    <div class="flex justify-between items-center text-xs py-0.5 text-indigo-600 dark:text-indigo-400">
                                        <span class="font-semibold">Add: Freight & Cargo Inward</span>
                                        <span class="font-bold font-mono">+ {{ number_format($totalCargoCharges, 2) }}</span>
                                    </div>
                                @endif
                                @if ($totalPurchaseReturn > 0)
                                    <div class="flex justify-between items-center text-xs py-0.5 text-red-500">
                                        <span class="font-semibold">Less: Purchase Returns</span>
                                        <span class="font-bold font-mono">- {{ number_format($totalPurchaseReturn, 2) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between items-center text-xs py-1 border-t border-gray-100 dark:border-gray-700 text-gray-500 font-bold">
                                    <span>Total Stock Available</span>
                                    <span class="font-mono">{{ number_format($totalStock, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-xs py-0.5 text-red-500">
                                    <span class="font-semibold">Less: Closing Stock (at cost)</span>
                                    <span class="font-bold font-mono">({{ number_format($closingValuation, 2) }})</span>
                                </div>
                                @if (abs($inventoryVariance) > 0.01)
                                    <div class="flex justify-between items-center text-xs py-0.5 text-gray-500 dark:text-gray-400 italic">
                                        <span>Stock Adjustments / Variance</span>
                                        <span class="font-mono">{{ $inventoryVariance >= 0 ? '+' : '' }}{{ number_format($inventoryVariance, 2) }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/60 p-3 rounded-xl mt-3 font-black">
                                <span class="text-xs uppercase tracking-tight text-gray-600 dark:text-gray-300">Total Cost of Goods Sold (COGS)</span>
                                <span class="font-mono text-base text-red-600 dark:text-red-400">
                                    {{ $settings->currency_symbol }} {{ number_format($cogs, 2) }}
                                </span>
                            </div>
                        </div>

                        <!-- Gross Profit Result -->
                        <div class="pt-4 border-t-2 border-gray-200 dark:border-gray-600 flex justify-between items-center">
                            <div>
                                <span class="text-xs font-black uppercase tracking-wider text-gray-800 dark:text-white">Gross Profit</span>
                                <div class="text-[10px] text-gray-400 font-bold">Revenue minus Cost of Goods Sold</div>
                            </div>
                            <span class="font-black font-mono text-2xl {{ $grossProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600' }}">
                                {{ $settings->currency_symbol }} {{ number_format($grossProfit, 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Operating Expenses & Other Income -->
                <div class="bg-white dark:bg-gray-800 shadow-xl rounded-3xl border border-gray-100 dark:border-gray-700 p-8 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-6 pb-3 border-b dark:border-gray-700">
                            <h3 class="text-base font-black text-gray-800 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="p-1.5 rounded-xl bg-red-100 dark:bg-red-900/30 text-red-600">📉</span>
                                Operating Expenses
                            </h3>
                            <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500">
                                {{ count($expenseData) }} Accounts
                            </span>
                        </div>

                        <div class="space-y-2.5 max-h-[380px] overflow-y-auto pr-1">
                            @forelse ($expenseData as $item)
                                <div class="flex justify-between items-center text-xs py-1.5 border-b border-gray-100 dark:border-gray-700/60">
                                    <span class="text-gray-600 dark:text-gray-300 font-bold uppercase tracking-wide">{{ $item['name'] }}</span>
                                    <span class="font-black font-mono text-sm text-red-500">{{ number_format($item['amount'], 2) }}</span>
                                </div>
                            @empty
                                <div class="p-6 text-center text-gray-400 text-xs italic">
                                    No operating expenses recorded for this period.
                                </div>
                            @endforelse
                        </div>

                        <div class="flex justify-between items-center pt-4 border-t-2 border-gray-200 dark:border-gray-600 font-black mt-4">
                            <span class="text-xs uppercase tracking-tight text-gray-700 dark:text-gray-200">Total Operating Expenses</span>
                            <span class="font-mono text-base text-red-600">
                                - {{ $settings->currency_symbol }} {{ number_format($totalOtherExpenses, 2) }}
                            </span>
                        </div>

                        @if (count($incomeData) > 0)
                            <div class="pt-6 mt-6 border-t border-gray-100 dark:border-gray-700">
                                <h4 class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-3 flex items-center gap-1.5">
                                    <span>✨</span> Other Income
                                </h4>
                                <div class="space-y-2">
                                    @foreach ($incomeData as $income)
                                        <div class="flex justify-between items-center text-xs py-1 border-b border-gray-100 dark:border-gray-700/60">
                                            <span class="text-gray-600 dark:text-gray-300 font-bold uppercase">{{ $income['name'] }}</span>
                                            <span class="font-black font-mono text-sm text-emerald-600">+ {{ number_format($income['amount'], 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Final Result Card at Bottom of Right Column -->
                    <div class="mt-8 p-6 rounded-2xl {{ $netProfit >= 0 ? 'bg-gradient-to-r from-emerald-500/10 to-green-500/15 border-2 border-emerald-300 dark:border-emerald-600/50' : 'bg-gradient-to-r from-red-500/10 to-rose-500/15 border-2 border-red-300 dark:border-red-600/50' }}">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] {{ $netProfit >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-700 dark:text-red-300' }}">
                                    Final Accounting Outcome
                                </span>
                                <h3 class="text-2xl font-black uppercase italic tracking-tight text-gray-800 dark:text-white mt-0.5">
                                    {{ $netProfit >= 0 ? 'Net Profit' : 'Net Loss' }}
                                </h3>
                                <div class="text-[11px] font-bold text-gray-500 mt-1">
                                    Net Margin: {{ $netMarginPercent }}% of Revenue
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-3xl font-black font-mono {{ $netProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $settings->currency_symbol }} {{ number_format(abs($netProfit), 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
