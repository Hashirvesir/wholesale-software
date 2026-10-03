<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-3xl border border-gray-100 dark:border-gray-700 mb-8 mt-10">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-8 border-b dark:border-gray-700 pb-6">
                        <h2 class="text-2xl font-black text-gray-800 dark:text-white tracking-tighter uppercase italic">
                            Profit & Loss Statement</h2>

                        <form action="{{ route('reports.profit-loss') }}" method="GET" class="flex gap-4 items-end">
                            <div>
                                <x-input-label for="warehouse_id" :value="__('Warehouse')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <select name="warehouse_id" id="warehouse_id"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-xl text-xs uppercase font-bold"
                                    style="min-width: 150px;">
                                    <option value="">All Warehouses</option>
                                    @foreach ($warehouses as $wh)
                                        <option value="{{ $wh->id }}"
                                            {{ $warehouseId == $wh->id ? 'selected' : '' }}>
                                            {{ $wh->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="start_date" :value="__('Start Date')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <x-text-input type="date" name="start_date" id="start_date"
                                    class="block mt-1 w-full !rounded-xl text-xs" :value="$startDate" />
                            </div>
                            <div>
                                <x-input-label for="end_date" :value="__('End Date')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <x-text-input type="date" name="end_date" id="end_date"
                                    class="block mt-1 w-full !rounded-xl text-xs" :value="$endDate" />
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    class="inline-flex items-center px-4 py-3 bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-widest transition shadow-lg shadow-indigo-200 dark:shadow-none">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                                        </path>
                                    </svg>
                                    {{ __('Apply Filter') }}
                                </button>
                                <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" target="_blank"
                                    class="inline-flex items-center px-4 py-3 bg-red-600 dark:bg-red-700 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-widest hover:bg-red-700 dark:hover:bg-red-600 transition shadow-lg shadow-red-200 dark:shadow-none">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                        </path>
                                    </svg>
                                    PDF
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                        <!-- Trading Section -->
                        <div>
                            <h3
                                class="text-lg font-black text-green-600 dark:text-green-400 uppercase tracking-widest mb-6 flex items-center">
                                <span class="bg-green-100 dark:bg-green-900/30 p-2 rounded-lg mr-3">💰</span>
                                Revenue & COGS
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center border-b dark:border-gray-700 pb-1">
                                    <span class="text-gray-500 font-bold uppercase text-[10px]">Total Sales</span>
                                    <span
                                        class="font-black font-mono text-lg">{{ number_format($totalSales, 2) }}</span>
                                </div>
                                <div
                                    class="flex justify-between items-center border-b dark:border-gray-700 pb-1 text-red-500">
                                    <span class="font-bold uppercase text-[10px]">Sales Return</span>
                                    <span class="font-black font-mono text-lg">-
                                        {{ number_format($totalSalesReturn, 2) }}</span>
                                </div>
                                <div
                                    class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2 rounded-lg font-black">
                                    <span class="uppercase text-xs tracking-tighter italic">Net Revenue</span>
                                    <span
                                        class="font-black font-mono text-xl text-green-600">{{ number_format($netSales, 2) }}</span>
                                </div>

                                <div class="pt-6">
                                    <h4 class="text-[9px] font-black uppercase text-gray-400 tracking-widest mb-3">Cost
                                        of Goods Sold</h4>
                                    <div class="flex justify-between items-center border-b dark:border-gray-700 pb-1">
                                        <span class="text-gray-600 text-[10px]">Opening Stock</span>
                                        <span
                                            class="font-black font-mono text-lg text-gray-800 dark:text-gray-200">{{ number_format($openingValuation, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center border-b dark:border-gray-700 pb-1">
                                        <span class="text-gray-600 text-[10px]">Purchases</span>
                                        <span
                                            class="font-black font-mono text-lg text-gray-800 dark:text-gray-200">{{ number_format($totalPurchases, 2) }}</span>
                                    </div>
                                    @if ($totalPurchaseReturn > 0)
                                        <div
                                            class="flex justify-between items-center border-b dark:border-gray-700 pb-1 text-red-500">
                                            <span class="text-[10px]">Less: Purchase Return</span>
                                            <span class="font-black font-mono text-lg">-
                                                {{ number_format($totalPurchaseReturn, 2) }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between items-center border-b dark:border-gray-700 pb-1">
                                        <span class="text-gray-600 text-[10px]">Less: Closing Stock</span>
                                        <span
                                            class="font-black font-mono text-lg text-red-500">({{ number_format($closingValuation, 2) }})</span>
                                    </div>
                                    <div class="flex justify-between items-center pt-2 font-black">
                                        <span class="text-xs uppercase italic">Total COGS</span>
                                        <span
                                            class="font-black font-mono text-xl text-red-600">{{ number_format($cogs, 2) }}</span>
                                    </div>
                                </div>

                                <div
                                    class="flex justify-between items-center pt-4 border-t-2 dark:border-gray-600 font-black text-lg">
                                    <span class="uppercase tracking-tighter">Gross Profit</span>
                                    <span
                                        class="font-black font-mono text-2xl {{ $grossProfit >= 0 ? 'text-green-600' : 'text-red-700' }}">
                                        {{ number_format($grossProfit, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Expenses Section -->
                        <div>
                            <h3
                                class="text-lg font-black text-red-600 dark:text-red-400 uppercase tracking-widest mb-6 flex items-center">
                                <span class="bg-red-100 dark:bg-red-900/30 p-2 rounded-lg mr-3">📉</span>
                                Operating Expenses
                            </h3>
                            <div class="space-y-2">
                                @forelse ($expenseData as $item)
                                    <div class="flex justify-between items-center border-b dark:border-gray-700 pb-1">
                                        <span
                                            class="text-gray-600 dark:text-gray-400 font-bold uppercase text-[10px]">{{ $item['name'] }}</span>
                                        <span
                                            class="font-black font-mono text-lg text-red-500">{{ number_format($item['amount'], 2) }}</span>
                                    </div>
                                @empty
                                    <p class="text-[10px] text-gray-400 italic">No operating expenses recorded.</p>
                                @endforelse
                                <div
                                    class="flex justify-between items-center pt-4 border-t-2 dark:border-gray-600 font-black">
                                    <span class="uppercase text-sm italic">Total Expenses</span>
                                    <span class="font-black font-mono text-xl text-red-600">-
                                        {{ number_format($totalOtherExpenses, 2) }}</span>
                                </div>

                                @if (count($incomeData) > 0)
                                    <div class="pt-6">
                                        <h4 class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-3">
                                            Other Income</h4>
                                        @foreach ($incomeData as $income)
                                            <div
                                                class="flex justify-between items-center border-b dark:border-gray-700 pb-1">
                                                <span
                                                    class="text-gray-600 text-[10px] uppercase">{{ $income['name'] }}</span>
                                                <span
                                                    class="font-black font-mono text-lg text-green-600">{{ number_format($income['amount'], 2) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Final Summary -->
                    <div
                        class="mt-12 p-8 rounded-3xl {{ $netProfit >= 0 ? 'bg-green-50 dark:bg-green-900/20 border-2 border-green-200' : 'bg-red-50 dark:bg-red-900/20 border-2 border-red-200' }}">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4
                                    class="text-xs font-black uppercase tracking-[0.2em] {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    Final Result</h4>
                                <p class="text-3xl font-black text-gray-800 dark:text-white uppercase italic mt-1">
                                    {{ $netProfit >= 0 ? 'Net Profit' : 'Net Loss' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span
                                    class="text-4xl font-black font-mono {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
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
