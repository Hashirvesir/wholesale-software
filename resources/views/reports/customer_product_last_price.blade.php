<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12">
        <div class="max-w-[1750px] mx-auto px-3 sm:px-6 lg:px-8 space-y-6">

            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h3 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tight flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <span>{{ __('Customer Product Last Price Report') }}</span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Historical tracking of last sale prices charged to customers per product, with lifetime quantities and standard price comparisons.
                    </p>
                </div>

                <!-- Export Actions -->
                <div class="flex items-center gap-2">
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Export CSV</span>
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" target="_blank"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Export PDF</span>
                    </a>
                </div>
            </div>

            <!-- KPI Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-[11px] font-black uppercase tracking-wider text-gray-400">Total Product Mappings</div>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1 font-mono">
                        {{ number_format($totalPairsCount) }}
                    </div>
                    <div class="text-[11px] text-gray-500 mt-0.5">Distinct customer-product combinations</div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-[11px] font-black uppercase tracking-wider text-emerald-500">Page Volume Sold</div>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 font-mono">
                        {{ number_format($pageTotalQty) }} units
                    </div>
                    <div class="text-[11px] text-gray-500 mt-0.5">Lifetime quantity on current page</div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-[11px] font-black uppercase tracking-wider text-primary-500">Page Total Turnover</div>
                    <div class="text-2xl font-black text-primary-600 dark:text-primary-400 mt-1 font-mono">
                        {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($pageTotalSpend, 2) }}
                    </div>
                    <div class="text-[11px] text-gray-500 mt-0.5">Lifetime revenue on current page</div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
                <form action="{{ route('reports.customer-product-last-price') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Customer Filter -->
                    <div class="lg:col-span-2">
                        <label for="customer_id" class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Filter by Customer
                        </label>
                        <select id="customer_id" name="customer_id"
                            class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm">
                            <option value="">-- All Customers --</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}" {{ $selectedCustomerId == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} {{ $c->phone ? "({$c->phone})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search Product Name / SKU -->
                    <div>
                        <label for="search" class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Search Product
                        </label>
                        <input type="text" id="search" name="search" value="{{ $searchProduct }}"
                            placeholder="Product name or SKU..."
                            class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm" />
                    </div>

                    <!-- Date Range Start -->
                    <div>
                        <label for="start_date" class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Sale Date From
                        </label>
                        <input type="date" id="start_date" name="start_date" value="{{ $startDate }}"
                            class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm" />
                    </div>

                    <!-- Date Range End & Submit -->
                    <div>
                        <label for="end_date" class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Sale Date To
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="date" id="end_date" name="end_date" value="{{ $endDate }}"
                                class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm" />
                            <button type="submit"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow transition">
                                Filter
                            </button>
                            @if ($selectedCustomerId || $searchProduct || $startDate || $endDate)
                                <a href="{{ route('reports.customer-product-last-price') }}"
                                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl text-xs font-bold transition"
                                    title="Clear Filters">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <!-- Report Table Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-gray-900/60 text-[10px] uppercase font-black tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-4 py-3.5">Customer Name</th>
                                <th class="px-4 py-3.5">Product Name & SKU</th>
                                <th class="px-4 py-3.5">Last Sale Date</th>
                                <th class="px-4 py-3.5">Last Invoice #</th>
                                <th class="px-4 py-3.5 text-right">Last Price</th>
                                <th class="px-4 py-3.5 text-right">Last Qty</th>
                                <th class="px-4 py-3.5 text-right">Standard Price</th>
                                <th class="px-4 py-3.5 text-right">Price Variance</th>
                                <th class="px-4 py-3.5 text-right">Lifetime Qty</th>
                                <th class="px-4 py-3.5 text-right">Lifetime Spend</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 font-medium">
                            @forelse ($records as $row)
                                @php
                                    $diff = (float)$row->last_price - (float)$row->standard_price;
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition">
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-900 dark:text-gray-100 uppercase">{{ $row->customer_name }}</div>
                                        @if ($row->customer_phone)
                                            <div class="text-[10px] text-gray-400 font-mono">{{ $row->customer_phone }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-900 dark:text-gray-100">{{ $row->product_name }}</div>
                                        @if ($row->product_sku)
                                            <div class="text-[10px] text-gray-400 font-mono">SKU: {{ $row->product_sku }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-mono text-gray-500 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($row->last_sale_date)->format('d M, Y') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <a href="{{ route('sales.show', $row->last_sale_id) }}" class="font-black text-primary-600 hover:underline font-mono">
                                            INV-{{ str_pad($row->last_sale_id, 5, '0', STR_PAD_LEFT) }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-right font-black font-mono text-emerald-600 dark:text-emerald-400 text-sm whitespace-nowrap">
                                        {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($row->last_price, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-black font-mono text-gray-700 dark:text-gray-300">
                                        {{ number_format($row->last_qty) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-gray-500 whitespace-nowrap">
                                        {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($row->standard_price, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        @if ($diff > 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 font-mono">
                                                +{{ number_format($diff, 2) }}
                                            </span>
                                        @elseif ($diff < 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 font-mono">
                                                {{ number_format($diff, 2) }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 font-mono">
                                                0.00
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-black font-mono text-gray-800 dark:text-gray-200">
                                        {{ number_format($row->total_qty) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-black font-mono text-primary-600 dark:text-primary-400 whitespace-nowrap">
                                        {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($row->total_amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center text-gray-400 italic font-bold">
                                        No customer sale records found matching your filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                @if ($records->hasPages())
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $records->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
