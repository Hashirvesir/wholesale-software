<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter">
                        Product Profit & Loss
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium italic">
                        Real-time margin analysis based on weighted average cost
                    </p>
                </div>
                <div
                    class="bg-indigo-50 dark:bg-indigo-900/30 px-6 py-3 rounded-2xl border border-indigo-100 dark:border-indigo-800">
                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest block">Product:</span>
                    <span class="text-lg font-black text-gray-800 dark:text-gray-200 uppercase">{{ $product->name }}
                        ({{ $product->sku }})</span>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700">
                    <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Total Revenue</span>
                    <div class="text-2xl font-black text-gray-800 dark:text-gray-100 mt-1">
                        {{ $settings->currency_symbol }}
                        {{ number_format($totalRevenue, 2) }}</div>
                </div>
                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700">
                    <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Total COGS (Cost)</span>
                    <div class="text-2xl font-black text-gray-800 dark:text-gray-100 mt-1">
                        {{ $settings->currency_symbol }}
                        {{ number_format($totalCost, 2) }}</div>
                </div>
                <div
                    class="p-6 rounded-3xl shadow-xl border-2 {{ $totalProfit >= 0 ? 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800' : 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800' }}">
                    <span
                        class="text-xs font-black {{ $totalProfit >= 0 ? 'text-green-600' : 'text-red-600' }} uppercase tracking-widest">Net
                        Profit</span>
                    <div
                        class="text-3xl font-black {{ $totalProfit >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }} mt-1">
                        {{ $settings->currency_symbol }} {{ number_format($totalProfit, 2) }}
                    </div>
                    @if ($totalRevenue > 0)
                        <div class="text-xs font-bold mt-2 opacity-70">Margin:
                            {{ number_format(($totalProfit / $totalRevenue) * 100, 2) }}%</div>
                    @endif
                </div>
            </div>

            <!-- Date Filter -->
            <div
                class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700 mb-6">
                <form action="{{ route('reports.product-profit-loss', $product) }}" method="GET"
                    class="flex flex-wrap items-end gap-4">
                    <div>
                        <x-input-label for="warehouse_id" :value="__('Warehouse')" />
                        <select name="warehouse_id" id="warehouse_id"
                            class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-xl text-xs uppercase font-bold"
                            style="min-width: 150px;">
                            <option value="">All Warehouses</option>
                            @foreach ($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="start_date" :value="__('Start Date')" />
                        <x-text-input id="start_date" name="start_date" type="date" class="block mt-1 w-full"
                            :value="$startDate" />
                    </div>
                    <div>
                        <x-input-label for="end_date" :value="__('End Date')" />
                        <x-text-input id="end_date" name="end_date" type="date" class="block mt-1 w-full"
                            :value="$endDate" />
                    </div>
                    <div class="flex space-x-2">
                        <x-primary-button>
                            {{ __('Filter') }}
                        </x-primary-button>
                        <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" target="_blank"
                            class="inline-flex items-center px-4 py-2 bg-red-600 dark:bg-red-700 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-widest hover:bg-red-700 dark:hover:bg-red-600 transition shadow-lg shadow-red-200 dark:shadow-none">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                </path>
                            </svg>
                            Download PDF
                        </a>
                        <a href="{{ route('reports.product-profit-loss', $product) }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none transition ease-in-out duration-150">
                            {{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Details Table -->
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-2xl sm:rounded-3xl border border-gray-200 dark:border-gray-700">
                <div class="p-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr
                                    class="bg-gray-50 dark:bg-gray-700/50 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                    <th class="px-6 py-4 text-left">Date</th>
                                    <th class="px-6 py-4 text-left">Type</th>
                                    <th class="px-6 py-4 text-left">Invoice</th>
                                    <th class="px-6 py-4 text-left">Customer</th>
                                    <th class="px-6 py-4 text-right">Qty</th>
                                    <th class="px-6 py-4 text-right">Sale Price</th>
                                    <th class="px-6 py-4 text-right">Revenue</th>
                                    <th class="px-6 py-4 text-right">Total Cost</th>
                                    <th
                                        class="px-6 py-4 text-right text-indigo-600 dark:text-indigo-400 bg-indigo-50/30 dark:bg-indigo-900/10">
                                        Profit</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                @forelse($profitLossDetails as $detail)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition duration-75">
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                            {{ date('d-M-Y', strtotime($detail['date'])) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter {{ $detail['type'] == 'Sale' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $detail['type'] }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap font-bold text-gray-800 dark:text-gray-200">
                                            @if ($detail['type'] == 'Sale')
                                                {{ $settings->invoice_prefix }}{{ $detail['sale_id'] }}
                                            @else
                                                SR-{{ $detail['sale_id'] }}
                                            @endif
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400 font-medium">
                                            {{ $detail['party'] }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right font-bold {{ $detail['qty'] < 0 ? 'text-red-500' : '' }}">
                                            {{ number_format($detail['qty'], 2) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-700 dark:text-gray-300">
                                            {{ number_format($detail['sale_price'], 2) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right font-bold {{ $detail['revenue'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ number_format($detail['revenue'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-gray-500 text-xs italic">
                                            {{ number_format($detail['total_cost'], 2) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right font-black {{ $detail['profit'] >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-rose-600' }} bg-indigo-50/10 dark:bg-indigo-900/5">
                                            {{ number_format($detail['profit'], 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10"
                                            class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 italic">
                                            No sales found for this period.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if (count($profitLossDetails) > 0)
                                <tfoot class="bg-gray-100 dark:bg-gray-900 font-black">
                                    <tr>
                                        <td colspan="5"
                                            class="px-6 py-4 text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                            Total Period Summary</td>
                                        <td class="px-6 py-4 text-right text-green-600 dark:text-green-400">
                                            {{ $settings->currency_symbol }}
                                            {{ number_format($totalRevenue, 2) }}</td>
                                        <td></td>
                                        <td class="px-6 py-4 text-right text-red-600 dark:text-red-400">
                                            {{ $settings->currency_symbol }}
                                            {{ number_format($totalCost, 2) }}</td>
                                        <td
                                            class="px-6 py-4 text-right text-indigo-600 dark:text-indigo-400 bg-indigo-50/20 dark:bg-indigo-900/20">
                                            {{ $settings->currency_symbol }} {{ number_format($totalProfit, 2) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-center">
                <a href="{{ route('products.index') }}"
                    class="group flex items-center px-6 py-3 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-2xl hover:bg-gray-200 dark:hover:bg-gray-700 transition duration-200">
                    <svg class="w-5 h-5 mr-2 transform group-hover:-translate-x-1 transition duration-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span class="text-sm font-black uppercase tracking-widest">Back to Products</span>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
