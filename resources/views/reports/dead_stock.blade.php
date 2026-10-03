<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter">
                        Dead Stock Report
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium italic">
                        Identify fast-moving vs slow-moving inventory
                    </p>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700">
                    <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Total Dead Items</span>
                    <div class="text-2xl font-black text-gray-800 dark:text-gray-100 mt-1">
                        {{ $products->count() }} <span class="text-sm text-gray-500 font-medium">Products</span>
                    </div>
                </div>
                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700">
                    <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Total Quantity Stuck</span>
                    <div class="text-2xl font-black text-gray-800 dark:text-gray-100 mt-1">
                        {{ number_format($products->sum('current_stock')) }} <span
                            class="text-sm text-gray-500 font-medium">Units</span>
                    </div>
                </div>
                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-red-200 dark:border-red-900/30 bg-red-50/30 dark:bg-red-900/10">
                    <span class="text-xs font-black text-red-600 dark:text-red-400 uppercase tracking-widest">Total
                        Stock Value</span>
                    <div class="text-3xl font-black text-red-700 dark:text-red-400 mt-1">
                        {{ $settings->currency_symbol }} {{ number_format($products->sum('dead_stock_value'), 2) }}
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div
                class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700 mb-6">
                <form action="{{ route('reports.dead-stock') }}" method="GET" class="flex flex-wrap items-end gap-4">
                    <div class="w-full md:w-64">
                        <x-input-label for="days" :value="__('Show products not sold in last (days)')" />
                        <x-text-input id="days" name="days" type="number" min="1"
                            class="block mt-1 w-full" :value="$days" />
                    </div>
                    <div class="flex space-x-2">
                        <x-primary-button>
                            {{ __('Generate Report') }}
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
                        <a href="{{ route('reports.dead-stock') }}"
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
                                    <th class="px-6 py-4 text-left">Product</th>
                                    <th class="px-6 py-4 text-left">Category / Brand</th>
                                    <th class="px-6 py-4 text-right">Stock Qty</th>
                                    <th class="px-6 py-4 text-right">Cost Price</th>
                                    <th class="px-6 py-4 text-right">Total Value</th>
                                    <th class="px-6 py-4 text-right">Last Sale</th>
                                    <th class="px-6 py-4 text-right">Days Inactive</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                @forelse($products as $product)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition duration-75">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-bold text-gray-900 dark:text-white">{{ $product->name }}
                                            </div>
                                            <div class="text-xs text-gray-500 font-mono">{{ $product->sku }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                            <div class="text-xs font-bold uppercase">
                                                {{ $product->category->name ?? '-' }}</div>
                                            <div class="text-[10px] uppercase">{{ $product->brand->name ?? '-' }}</div>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right font-bold text-gray-800 dark:text-gray-200">
                                            {{ number_format($product->current_stock) }}
                                            {{ $product->unit->name ?? '' }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-gray-600 dark:text-gray-400 font-medium">
                                            {{ $settings->currency_symbol }}
                                            {{ number_format($product->avg_cost, 2) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right font-black text-red-600 dark:text-red-400">
                                            {{ $settings->currency_symbol }}
                                            {{ number_format($product->dead_stock_value, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-gray-500">
                                            {{ $product->last_sale_date ? date('d M, Y', strtotime($product->last_sale_date)) : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            @if ($product->days_since_last_sale === 'Never Sold')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                    Never Sold
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                                    {{ $product->days_since_last_sale }} Days
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7"
                                            class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 italic">
                                            Good news! No dead stock found matching your criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
