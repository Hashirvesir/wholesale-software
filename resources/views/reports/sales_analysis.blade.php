<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter">
                        Sales Analysis Report
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium italic">
                        Top customers and top products for the selected period
                    </p>
                </div>
            </div>

            <!-- Filter -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700 mb-6">
                <form action="{{ route('reports.sales-analysis') }}" method="GET" class="flex flex-wrap items-end gap-4">
                    <div class="w-full md:w-48">
                        <x-input-label for="start_date" :value="__('Start Date')" />
                        <x-text-input id="start_date" name="start_date" type="date" class="block mt-1 w-full" :value="$startDate" />
                    </div>
                    <div class="w-full md:w-48">
                        <x-input-label for="end_date" :value="__('End Date')" />
                        <x-text-input id="end_date" name="end_date" type="date" class="block mt-1 w-full" :value="$endDate" />
                    </div>
                    <div class="w-full md:w-32">
                        <x-input-label for="limit" :value="__('Top N')" />
                        <x-text-input id="limit" name="limit" type="number" min="1" class="block mt-1 w-full" :value="$limit" />
                    </div>
                    <div class="flex space-x-2">
                        <x-primary-button>{{ __('Generate Report') }}</x-primary-button>
                        <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" target="_blank"
                            class="inline-flex items-center px-4 py-2 bg-red-600 dark:bg-red-700 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-widest hover:bg-red-700 dark:hover:bg-red-600 transition shadow-lg shadow-red-200 dark:shadow-none">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Download PDF
                        </a>
                        <a href="{{ route('reports.sales-analysis') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none transition ease-in-out duration-150">
                            {{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Top Customers -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-2xl sm:rounded-3xl border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b dark:border-gray-700">
                        <h4 class="text-sm font-black uppercase tracking-widest text-gray-700 dark:text-gray-300">Top Customers</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                    <th class="px-6 py-4 text-left">#</th>
                                    <th class="px-6 py-4 text-left">Customer</th>
                                    <th class="px-6 py-4 text-right">Invoices</th>
                                    <th class="px-6 py-4 text-right">Gross Sales</th>
                                    <th class="px-6 py-4 text-right">Returns</th>
                                    <th class="px-6 py-4 text-right">Net Sales</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                @forelse ($topCustomers as $i => $row)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition duration-75">
                                        <td class="px-6 py-4 text-gray-400 font-bold">{{ $i + 1 }}</td>
                                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $row->party->name ?? 'Unknown' }}</td>
                                        <td class="px-6 py-4 text-right text-gray-500">{{ $row->invoice_count }}</td>
                                        <td class="px-6 py-4 text-right text-gray-500">{{ number_format($row->total_sales, 2) }}</td>
                                        <td class="px-6 py-4 text-right text-red-500">{{ $row->total_returns > 0 ? '-' . number_format($row->total_returns, 2) : '-' }}</td>
                                        <td class="px-6 py-4 text-right font-black text-indigo-600 dark:text-indigo-400">{{ number_format($row->net_sales, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 italic">No sales found for this period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-2xl sm:rounded-3xl border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b dark:border-gray-700">
                        <h4 class="text-sm font-black uppercase tracking-widest text-gray-700 dark:text-gray-300">Top Products</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                    <th class="px-6 py-4 text-left">#</th>
                                    <th class="px-6 py-4 text-left">Product</th>
                                    <th class="px-6 py-4 text-right">Qty Sold</th>
                                    <th class="px-6 py-4 text-right">Returned</th>
                                    <th class="px-6 py-4 text-right">Net Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                @forelse ($topProducts as $i => $row)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition duration-75">
                                        <td class="px-6 py-4 text-gray-400 font-bold">{{ $i + 1 }}</td>
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900 dark:text-white">{{ $row->product->name ?? 'Unknown' }}</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ $row->product->sku ?? '' }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-500">{{ number_format($row->total_qty, 2) }}</td>
                                        <td class="px-6 py-4 text-right text-red-500">{{ $row->returned_qty > 0 ? '-' . number_format($row->returned_qty, 2) : '-' }}</td>
                                        <td class="px-6 py-4 text-right font-black text-indigo-600 dark:text-indigo-400">{{ number_format($row->net_revenue, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 italic">No sales found for this period.</td>
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
