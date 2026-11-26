<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12">
        <div class="max-w-[1750px] mx-auto px-3 sm:px-6 lg:px-8 space-y-6">

            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h3 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tight flex items-center gap-2">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span>{{ __('Product Sales & Customer Invoices Report') }}</span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Track which products were sold to which customer, at what rate and quantity, with full invoice breakdown and PDF export.
                    </p>
                </div>

                <!-- Export Actions -->
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow transition active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Export CSV</span>
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" target="_blank"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow transition active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Download PDF</span>
                    </a>
                </div>
            </div>

            <!-- Quick Date Filter Presets Bar -->
            <div class="bg-white dark:bg-gray-800 p-2.5 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-wrap items-center gap-1.5 sm:gap-2">
                <span class="text-xs font-black uppercase tracking-wider text-gray-400 px-2 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Quick Dates:</span>
                </span>

                <a href="{{ request()->fullUrlWithQuery(['preset' => 'today', 'start_date' => null, 'end_date' => null]) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ ($preset ?? '') === 'today' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    Aj (Today)
                </a>

                <a href="{{ request()->fullUrlWithQuery(['preset' => 'yesterday', 'start_date' => null, 'end_date' => null]) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ ($preset ?? '') === 'yesterday' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    Kal (Yesterday)
                </a>

                <a href="{{ request()->fullUrlWithQuery(['preset' => 'last_3_days', 'start_date' => null, 'end_date' => null]) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ ($preset ?? '') === 'last_3_days' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    Parso (Last 3 Days)
                </a>

                <a href="{{ request()->fullUrlWithQuery(['preset' => 'this_week', 'start_date' => null, 'end_date' => null]) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ ($preset ?? '') === 'this_week' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    This Week
                </a>

                <a href="{{ request()->fullUrlWithQuery(['preset' => 'this_month', 'start_date' => null, 'end_date' => null]) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ ($preset ?? '') === 'this_month' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    1 Mahina (This Month)
                </a>

                <a href="{{ request()->fullUrlWithQuery(['preset' => 'last_30_days', 'start_date' => null, 'end_date' => null]) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ ($preset ?? '') === 'last_30_days' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    Last 30 Days
                </a>

                <div class="ml-auto flex items-center gap-1.5">
                    <span class="text-xs font-bold text-gray-400">View:</span>
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'invoice']) }}"
                        class="px-2.5 py-1 text-xs font-bold rounded-lg transition {{ ($viewMode ?? 'invoice') === 'invoice' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-300' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        By Invoice
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'flat']) }}"
                        class="px-2.5 py-1 text-xs font-bold rounded-lg transition {{ ($viewMode ?? 'invoice') === 'flat' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-300' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        Flat Items
                    </a>
                </div>
            </div>

            <!-- KPI Metric Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="bg-white dark:bg-gray-800 p-4 sm:p-5 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-[11px] font-black uppercase tracking-wider text-gray-400">Total Invoices</div>
                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1 font-mono">
                        {{ number_format($totalInvoices) }}
                    </div>
                    <div class="text-[10px] text-gray-500 mt-0.5">Matching selected range</div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-4 sm:p-5 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-[11px] font-black uppercase tracking-wider text-emerald-500">Total Qty Sold</div>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 font-mono">
                        {{ number_format($totalQty, 2) }}
                    </div>
                    <div class="text-[10px] text-gray-500 mt-0.5">Units / Items delivered</div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-4 sm:p-5 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-[11px] font-black uppercase tracking-wider text-primary-500">Total Sales Value</div>
                    <div class="text-2xl font-black text-primary-600 dark:text-primary-400 mt-1 font-mono">
                        {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($totalAmount, 2) }}
                    </div>
                    <div class="text-[10px] text-gray-500 mt-0.5">Gross item sales turnover</div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-4 sm:p-5 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-[11px] font-black uppercase tracking-wider text-amber-500">Customers Served</div>
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 font-mono">
                        {{ number_format($uniqueCustomersCount) }}
                    </div>
                    <div class="text-[10px] text-gray-500 mt-0.5">Unique customers billed</div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
                <form action="{{ route('reports.product-sales') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 sm:gap-4 items-end">
                    <input type="hidden" name="view" value="{{ $viewMode ?? 'invoice' }}" />

                    <!-- Customer Filter (3 cols) -->
                    <div class="lg:col-span-3">
                        <label for="customer_id" class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Customer
                        </label>
                        <select id="customer_id" name="customer_id"
                            class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm">
                            <option value="">-- All Customers --</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}" {{ ($selectedCustomerId ?? '') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} {{ $c->phone ? "({$c->phone})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search Product Name / SKU (3 cols) -->
                    <div class="lg:col-span-3">
                        <label for="search" class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Product Name or SKU
                        </label>
                        <input type="text" id="search" name="search" value="{{ $searchProduct ?? '' }}"
                            placeholder="Type product name or barcode..."
                            class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm" />
                    </div>

                    <!-- Warehouse / Branch Filter (2 cols) -->
                    <div class="lg:col-span-2">
                        <label for="warehouse_id" class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Shop / Branch
                        </label>
                        <select id="warehouse_id" name="warehouse_id"
                            class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm">
                            <option value="">-- All Branches --</option>
                            @foreach ($warehouses as $w)
                                <option value="{{ $w->id }}" {{ ($selectedWarehouseId ?? '') == $w->id ? 'selected' : '' }}>
                                    {{ $w->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Range Start (2 cols) -->
                    <div class="lg:col-span-2">
                        <label for="start_date" class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            From Date
                        </label>
                        <input type="date" id="start_date" name="start_date" value="{{ $startDate ?? '' }}"
                            class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm" />
                    </div>

                    <!-- Date Range End (2 cols) -->
                    <div class="lg:col-span-2">
                        <label for="end_date" class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            To Date
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="date" id="end_date" name="end_date" value="{{ $endDate ?? '' }}"
                                class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm" />
                            <button type="submit"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow transition">
                                Filter
                            </button>
                            <a href="{{ route('reports.product-sales') }}"
                                class="px-2.5 py-2 border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl text-xs font-bold transition"
                                title="Reset Filters">
                                ↺
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Active Filter Badge Summary -->
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-gray-500 dark:text-gray-400">
                <span>Active Filter:</span>
                <span class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                    Date: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d-M-Y') : 'All Time' }} to {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d-M-Y') : 'Today' }}
                </span>
                @if ($selectedCustomer)
                    <span class="px-2.5 py-1 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                        Customer: {{ $selectedCustomer->name }}
                    </span>
                @endif
                @if ($searchProduct)
                    <span class="px-2.5 py-1 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300">
                        Product: "{{ $searchProduct }}"
                    </span>
                @endif
            </div>

            <!-- CONTENT: INVOICE BREAKDOWN VIEW (Default) -->
            @if (($viewMode ?? 'invoice') === 'invoice')
                <div class="space-y-4">
                    @forelse ($invoicesOnPage as $saleId => $items)
                        @php
                            $firstItem = $items->first();
                            $invTotal = $items->sum('item_total');
                            $invQty = $items->sum('qty');
                        @endphp
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <!-- Invoice Header Banner -->
                            <div class="p-3.5 sm:p-4 bg-gray-50 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="px-2.5 py-1 rounded-lg bg-primary-600 text-white font-mono font-black text-xs uppercase tracking-wider">
                                        INV-{{ $saleId }}
                                    </span>
                                    <div>
                                        <div class="font-black text-sm text-gray-900 dark:text-gray-100 uppercase">
                                            {{ $firstItem->customer_name ?? 'Walk-In Customer' }}
                                        </div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                            @if ($firstItem->customer_phone)
                                                <span>{{ $firstItem->customer_phone }}</span>
                                                <span>•</span>
                                            @endif
                                            <span>Date: {{ \Carbon\Carbon::parse($firstItem->sale_date)->format('d-M-Y') }}</span>
                                            @if ($firstItem->warehouse_name)
                                                <span>•</span>
                                                <span class="text-gray-600 dark:text-gray-300">{{ $firstItem->warehouse_name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="text-right">
                                        <div class="text-[10px] uppercase font-bold text-gray-400">Invoice Total</div>
                                        <div class="text-sm font-black font-mono text-gray-900 dark:text-gray-100">
                                            {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($firstItem->sale_grand_total ?? $invTotal, 2) }}
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('sales.show', $saleId) }}" target="_blank"
                                            class="px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 transition"
                                            title="View Original Invoice">
                                            View
                                        </a>
                                        <a href="{{ route('sales.thermal', $saleId) }}" target="_blank"
                                            class="px-2.5 py-1.5 rounded-lg bg-gray-900 hover:bg-black text-white text-xs font-bold transition flex items-center gap-1"
                                            title="Print Thermal Receipt">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            <span>Receipt</span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Items Table -->
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs text-gray-700 dark:text-gray-200">
                                    <thead class="bg-gray-100/60 dark:bg-gray-900/50 text-[10px] uppercase font-black tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                                        <tr>
                                            <th class="px-4 py-2.5 w-12 text-center">#</th>
                                            <th class="px-4 py-2.5">Product Name & SKU</th>
                                            <th class="px-4 py-2.5 text-center w-28">Qty Sold</th>
                                            <th class="px-4 py-2.5 text-right w-36">Rate ({{ $settings->currency_symbol ?? 'Rs' }})</th>
                                            <th class="px-4 py-2.5 text-right w-36">Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium">
                                        @foreach ($items as $idx => $item)
                                            <tr class="hover:bg-emerald-50/40 dark:hover:bg-gray-700/40 transition">
                                                <td class="px-4 py-2.5 text-center text-gray-400 font-mono text-[11px]">{{ $idx + 1 }}</td>
                                                <td class="px-4 py-2.5">
                                                    <div class="font-bold text-gray-900 dark:text-gray-100">{{ $item->product_name }}</div>
                                                    @if ($item->product_sku)
                                                        <div class="text-[10px] text-gray-500 font-mono">SKU: {{ $item->product_sku }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2.5 text-center font-bold">
                                                    <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-mono">
                                                        {{ number_format($item->qty, 2) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2.5 text-right font-mono font-bold">
                                                    {{ number_format($item->price, 2) }}
                                                </td>
                                                <td class="px-4 py-2.5 text-right font-mono font-black text-gray-900 dark:text-gray-100">
                                                    {{ number_format($item->item_total, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50 dark:bg-gray-900/60 border-t border-gray-200 dark:border-gray-700 text-xs font-bold">
                                        <tr>
                                            <td colspan="2" class="px-4 py-2.5 text-right uppercase text-[10px] text-gray-400">Invoice Items Subtotal:</td>
                                            <td class="px-4 py-2.5 text-center font-mono font-black text-emerald-600 dark:text-emerald-400">{{ number_format($invQty, 2) }}</td>
                                            <td></td>
                                            <td class="px-4 py-2.5 text-right font-mono font-black text-gray-900 dark:text-gray-100">{{ number_format($invTotal, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-12 text-center text-gray-400 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p class="font-bold text-sm">No sales records found for the selected criteria.</p>
                            <p class="text-xs text-gray-400 mt-1">Try selecting another date range or clearing customer/product filter.</p>
                        </div>
                    @endforelse
                </div>

            <!-- CONTENT: FLAT ITEMS LIST VIEW -->
            @else
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                            <thead class="bg-gray-50 dark:bg-gray-900/60 text-[10px] uppercase font-black tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="px-4 py-3.5">Date</th>
                                    <th class="px-4 py-3.5">Invoice #</th>
                                    <th class="px-4 py-3.5">Customer Name</th>
                                    <th class="px-4 py-3.5">Product Name & SKU</th>
                                    <th class="px-4 py-3.5 text-center">Qty Sold</th>
                                    <th class="px-4 py-3.5 text-right">Sale Rate</th>
                                    <th class="px-4 py-3.5 text-right">Item Total</th>
                                    <th class="px-4 py-3.5">Branch</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700/60">
                                @forelse ($records as $row)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                                        <td class="px-4 py-3 whitespace-nowrap font-mono text-[11px]">
                                            {{ \Carbon\Carbon::parse($row->sale_date)->format('d-M-Y') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap font-mono font-bold">
                                            <a href="{{ route('sales.show', $row->sale_id) }}" target="_blank"
                                                class="text-primary-600 hover:underline">
                                                INV-{{ $row->sale_id }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-gray-800 dark:text-gray-100 uppercase">{{ $row->customer_name ?? 'Walk-In Customer' }}</div>
                                            @if ($row->customer_phone)
                                                <div class="text-[10px] text-gray-500 font-mono">{{ $row->customer_phone }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-gray-900 dark:text-gray-100">{{ $row->product_name }}</div>
                                            @if ($row->product_sku)
                                                <div class="text-[10px] text-gray-500 font-mono">SKU: {{ $row->product_sku }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center font-bold">
                                            <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-mono">
                                                {{ number_format($row->qty, 2) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono font-bold">
                                            {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($row->price, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono font-black text-gray-900 dark:text-gray-100">
                                            {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($row->item_total, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-500">
                                            {{ $row->warehouse_name ?? '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                                            No sales records found matching this criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Pagination -->
            @if ($records->hasPages())
                <div class="mt-4">
                    {{ $records->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
