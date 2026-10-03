<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter">
                        Product Ledger Report
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium italic">
                        Stock movement summary & value analysis
                    </p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-red-600 dark:bg-red-700 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-widest hover:bg-red-700 dark:hover:bg-red-600 transition shadow-lg shadow-red-200 dark:shadow-none">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                            </path>
                        </svg>
                        Download PDF
                    </a>
                </div>
            </div>

            <!-- Filter & Search -->
            <div
                class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700 mb-6">
                <form action="{{ route('reports.products-summary') }}" method="GET"
                    class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-[300px]">
                        <x-input-label for="search" :value="__('Search Product (Name or SKU)')" />
                        <x-text-input id="search" name="search" type="text" class="block mt-1 w-full"
                            placeholder="Type product name or SKU..." :value="$search" />
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
                            {{ __('Search & Filter') }}
                        </x-primary-button>
                        <a href="{{ route('reports.products-summary') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none transition ease-in-out duration-150">
                            {{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-2xl sm:rounded-3xl border border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50">
                                <th
                                    class="px-6 py-4 text-left text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                    Product Name</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                    SKU</th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-black text-green-600 dark:text-green-400 uppercase tracking-widest">
                                    Qty In</th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-black text-red-600 dark:text-red-400 uppercase tracking-widest">
                                    Qty Out</th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest">
                                    Net Change</th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">
                                    Avg Cost ({{ $settings->currency_symbol }})</th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-black text-gray-900 dark:text-white uppercase tracking-widest bg-gray-100/50 dark:bg-gray-900/50">
                                    Current Stock</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($products as $product)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition duration-75">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-black text-gray-900 dark:text-gray-100">
                                        {{ $product->name }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-500 dark:text-gray-400 tracking-tighter uppercase">
                                        {{ $product->sku }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-green-600">
                                        {{ number_format($product->qty_in, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-red-600">
                                        {{ number_format($product->qty_out, 2) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-black {{ $product->qty_in - $product->qty_out >= 0 ? 'text-blue-600' : 'text-rose-600' }}">
                                        {{ number_format($product->qty_in - $product->qty_out, 2) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-indigo-600">
                                        {{ number_format($product->avg_cost, 2) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-gray-900 dark:text-white bg-gray-50/30 dark:bg-gray-900/10">
                                        {{ number_format($product->current_stock, 2) }}
                                        {{ $product->unit->short_name }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 italic">No products
                                        found matching your search.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
