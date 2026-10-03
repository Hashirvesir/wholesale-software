<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter">
                        Product Stock History
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium italic">
                        Detailed movement & Weighted Average Cost (WAC) history
                    </p>
                </div>
                <div
                    class="bg-primary-50 dark:bg-primary-900/30 px-6 py-3 rounded-2xl border border-primary-100 dark:border-primary-800">
                    <span class="text-xs font-bold text-primary-600 uppercase tracking-widest block">Product:</span>
                    <span class="text-lg font-black text-gray-800 dark:text-gray-200 uppercase">{{ $product->name }}
                        ({{ $product->sku }})</span>
                </div>
            </div>

            <!-- Date Filter Form -->
            <div
                class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700 mb-6">
                <form action="{{ route('reports.stock-history', $product) }}" method="GET"
                    class="flex flex-wrap items-end gap-4">
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
                        <a href="{{ request()->fullUrlWithQuery(['excel' => 1]) }}"
                            class="inline-flex items-center px-4 py-2 bg-green-600 dark:bg-green-700 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-widest hover:bg-green-700 dark:hover:bg-green-600 transition shadow-lg shadow-green-200 dark:shadow-none">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            Download Excel
                        </a>
                        <a href="{{ route('reports.stock-history', $product) }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none transition ease-in-out duration-150">
                            {{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-2xl sm:rounded-3xl border border-gray-200 dark:border-gray-700">
                <div class="p-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50">
                                    <th
                                        class="px-6 py-4 text-left text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                        Date</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                        Transaction</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                        Ref / Party</th>
                                    <th
                                        class="px-6 py-4 text-right text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                        Qty In</th>
                                    <th
                                        class="px-6 py-4 text-right text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                        Qty Out</th>
                                    <th
                                        class="px-6 py-4 text-right text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                        Rate (RMB)</th>
                                    <th
                                        class="px-6 py-4 text-right text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                        Rate (PKR)</th>
                                    <th
                                        class="px-6 py-4 text-right text-xs font-black text-orange-500 dark:text-orange-400 uppercase tracking-widest">
                                        Cargo</th>
                                    <th
                                        class="px-6 py-4 text-right text-xs font-black text-primary-600 dark:text-primary-400 uppercase tracking-widest bg-primary-50/30 dark:bg-primary-900/10">
                                        Resulting Qty</th>
                                    <th
                                        class="px-6 py-4 text-right text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest bg-indigo-50/30 dark:bg-indigo-900/10">
                                        Avg Cost (PKR)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($history as $row)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition duration-75">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600 dark:text-gray-400">
                                            {{ date('d-M-Y', strtotime($row['date'])) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $badgeClass = match ($row['type']) {
                                                    'Opening Stock'
                                                        => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
                                                    'Purchase'
                                                        => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
                                                    'Sale'
                                                        => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200',
                                                    'Sales Return'
                                                        => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-200',
                                                    'Purchase Return'
                                                        => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200',
                                                    default => 'bg-gray-100 text-gray-800',
                                                };
                                            @endphp
                                            <span
                                                class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter {{ $badgeClass }}">
                                                {{ $row['type'] }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-medium">
                                            <div class="flex flex-col">
                                                <span
                                                    class="text-gray-800 dark:text-gray-200 font-bold tracking-tight">{{ $row['reference'] }}</span>
                                                @if (isset($row['party']))
                                                    <span
                                                        class="text-[10px] uppercase font-black text-gray-400 italic">{{ $row['party'] }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-green-600 dark:text-green-400">
                                            {{ $row['qty_in'] > 0 ? number_format($row['qty_in'], 2) : '-' }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-red-600 dark:text-red-400">
                                            {{ $row['qty_out'] > 0 ? number_format($row['qty_out'], 2) : '-' }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-500 italic">
                                            {{ $row['type'] == 'Purchase' && $row['rate_rmb'] > 0 ? '¥ ' . number_format($row['rate_rmb'], 2) : '-' }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ number_format($row['rate'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium {{ ($row['cargo_amount'] ?? 0) > 0 ? 'text-orange-600 dark:text-orange-400 font-bold' : 'text-gray-300 dark:text-gray-600' }}">
                                            {{ ($row['cargo_amount'] ?? 0) > 0 ? number_format($row['cargo_amount'], 2) : '—' }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-primary-600 dark:text-primary-400 bg-primary-50/10 dark:bg-primary-900/5">
                                            {{ number_format($row['running_qty'], 2) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50/10 dark:bg-indigo-900/5">
                                            {{ $settings->currency_symbol }}
                                            {{ number_format($row['running_avg_cost'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-100 dark:bg-gray-900 font-black">
                                    <td colspan="3"
                                        class="px-6 py-4 text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                        Totals for this Period</td>
                                    <td class="px-6 py-4 text-right text-sm text-green-600 dark:text-green-400">
                                        {{ number_format($totalQtyIn, 2) }}</td>
                                    <td class="px-6 py-4 text-right text-sm text-red-600 dark:text-red-400">
                                        {{ number_format($totalQtyOut, 2) }}</td>
                                    <td colspan="5"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                @if ($history->hasPages())
                    <div class="p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        {{ $history->links() }}
                    </div>
                @endif
            </div>

            <!-- Final Summary Card -->
            <div class="mt-8 flex justify-end">
                <div
                    class="bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] shadow-2xl border border-gray-100 dark:border-gray-700 w-full md:w-1/3 relative overflow-hidden group">
                    <div
                        class="absolute top-0 right-0 w-32 h-32 bg-red-500/5 rounded-full -mr-10 -mt-10 group-hover:scale-110 transition duration-500">
                    </div>

                    <div class="space-y-4 relative z-10">
                        <div class="flex justify-between items-center pb-4 border-b dark:border-gray-700/50">
                            <span class="text-xs font-black uppercase tracking-widest text-gray-400">Final
                                Quantity</span>
                            <span
                                class="text-xl font-black text-gray-900 dark:text-gray-100">{{ number_format($currentStockStatus, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-4 border-b dark:border-gray-700/50">
                            <span class="text-xs font-black uppercase tracking-widest text-gray-400">Weighted Avg
                                Cost</span>
                            <span
                                class="text-xl font-black text-gray-900 dark:text-gray-100">{{ $settings->currency_symbol }}
                                {{ number_format($currentAvgCostStatus, 2) }}</span>
                        </div>
                        <div class="pt-2">
                            <span
                                class="block text-[10px] font-black uppercase tracking-[0.2em] text-red-500 mb-1">Total
                                Inventory Value</span>
                            <div class="text-4xl font-black text-red-600 dark:text-red-500 tracking-tighter">
                                {{ $settings->currency_symbol }} {{ number_format($totalStockValue, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-center">
                <a href="{{ route('products.index') }}"
                    class="group flex items-center px-6 py-3 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-700/50 border border-gray-200 dark:border-gray-700 transition duration-200">
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
