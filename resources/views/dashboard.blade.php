<x-app-layout>
    <div class="py-6" x-data="{
        showNumbers: localStorage.getItem('hide-dashboard-numbers') === 'true' ? false : true,
        toggleNumbers() {
            this.showNumbers = !this.showNumbers;
            localStorage.setItem('hide-dashboard-numbers', !this.showNumbers);
        },

        // Filter state
        warehouseId: '',
        startDate: '{{ now()->toDateString() }}',
        endDate: '{{ now()->toDateString() }}',
        filterApplied: false,
        loading: false,

        // Reactive stats — defaults = PHP-rendered all-time values
        stats: {
            sales:     {{ $stats['total_sales'] }},
            purchases: {{ $stats['total_purchases'] }},
            received:  {{ $stats['today_received'] }},
            paid:      {{ $stats['today_paid'] }},
            salesCount: null,
            label: 'All Time'
        },

        formatNum(n) {
            return parseFloat(n || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        },

        async applyFilter() {
            this.loading = true;
            const params = new URLSearchParams({
                warehouse_id: this.warehouseId,
                start_date:   this.startDate,
                end_date:     this.endDate,
            });
            const res  = await fetch('{{ route('dashboard.quick-stats') }}?' + params);
            const data = await res.json();
            const lbl  = this.startDate === this.endDate
                ? this.startDate
                : this.startDate + ' – ' + this.endDate;
            this.stats = {
                sales:      data.sales,
                purchases:  data.purchases,
                received:   data.total_received,
                paid:       data.total_paid,
                salesCount: data.sales_count,
                label:      lbl
            };
            this.filterApplied = true;
            this.loading = false;
        },

        resetFilter() {
            this.warehouseId = '';
            this.startDate   = '{{ now()->toDateString() }}';
            this.endDate     = '{{ now()->toDateString() }}';
            this.filterApplied = false;
            this.stats = {
                sales:      {{ $stats['total_sales'] }},
                purchases:  {{ $stats['total_purchases'] }},
                received:   {{ $stats['today_received'] }},
                paid:       {{ $stats['today_paid'] }},
                salesCount: null,
                label:      'All Time'
            };
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Welcome Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-black text-gray-800 dark:text-white tracking-tighter uppercase italic">
                        {{ __('Business Console') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest">
                        Real-time tracking & Performance Analytics
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <button @click="toggleNumbers()"
                        class="p-2 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition group"
                        title="Toggle Privacy Mode">
                        <template x-if="showNumbers">
                            <svg class="w-6 h-6 text-gray-400 group-hover:text-indigo-500" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </template>
                        <template x-if="!showNumbers">
                            <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.047m4.5 4.5a3 3 0 104.243 4.242M9.88 9.88l4.24 4.24m1.757-4.305a3 3 0 00-4.305-4.305M20.375 18.825L3.625 2M16.5 6.5l3.5 3.5" />
                            </svg>
                        </template>
                    </button>
                    <div class="text-right hidden sm:block">
                        <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400 italic">
                            {{ now()->format('d M, Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-4">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[160px]">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Warehouse</label>
                        <select x-model="warehouseId"
                            class="w-full text-xs border-gray-200 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 rounded-xl focus:border-primary-500 focus:ring-primary-500 shadow-sm">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Start Date</label>
                        <input type="date" x-model="startDate"
                            class="w-full text-xs border-gray-200 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 rounded-xl focus:border-primary-500 focus:ring-primary-500 shadow-sm">
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">End Date</label>
                        <input type="date" x-model="endDate"
                            class="w-full text-xs border-gray-200 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 rounded-xl focus:border-primary-500 focus:ring-primary-500 shadow-sm">
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="applyFilter()" :disabled="loading"
                            class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow transition disabled:opacity-50 flex items-center gap-2">
                            <svg x-show="loading" class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-text="loading ? 'Loading...' : 'Apply'"></span>
                        </button>
                        <button @click="resetFilter()" x-show="filterApplied" x-cloak
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs font-black uppercase tracking-widest rounded-xl transition">
                            Reset
                        </button>
                    </div>
                    <div x-show="filterApplied" x-cloak class="ml-auto">
                        <span class="text-[10px] font-black uppercase tracking-widest text-primary-500 bg-primary-50 dark:bg-primary-900/30 px-3 py-1 rounded-full" x-text="'Showing: ' + stats.label"></span>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Sales -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border-b-4 border-emerald-500 shadow-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-black uppercase text-emerald-600 tracking-widest">Total Revenue</span>
                        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="text-2xl font-black text-gray-900 dark:text-white italic">
                        <span x-show="showNumbers">{{ $settings->currency_symbol }} <span x-text="formatNum(stats.sales)"></span></span>
                        <span x-show="!showNumbers" x-cloak>******</span>
                    </div>
                    <div class="mt-2 text-xs text-gray-500 uppercase font-bold italic">
                        <span x-show="showNumbers" x-text="stats.salesCount !== null ? stats.salesCount + ' invoices' : stats.label"></span>
                        <span x-show="!showNumbers" x-cloak>{{ $settings->currency_symbol }} ****</span>
                    </div>
                </div>

                <!-- Total Purchases -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border-b-4 border-amber-500 shadow-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-black uppercase text-amber-600 tracking-widest">Procurement</span>
                        <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="text-2xl font-black text-gray-900 dark:text-white italic">
                        <span x-show="showNumbers">{{ $settings->currency_symbol }} <span x-text="formatNum(stats.purchases)"></span></span>
                        <span x-show="!showNumbers" x-cloak>******</span>
                    </div>
                    <div class="mt-2 text-xs text-gray-500 uppercase font-bold italic">
                        <span x-show="showNumbers" x-text="stats.label"></span>
                        <span x-show="!showNumbers" x-cloak>{{ $settings->currency_symbol }} ****</span>
                    </div>
                </div>

                <!-- Receipts/Payments -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border-b-4 border-indigo-500 shadow-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-black uppercase text-indigo-600 tracking-widest">Cash Flow</span>
                        <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-gray-400">IN</span>
                            <span class="font-black text-gray-900 dark:text-white">
                                <span x-show="showNumbers">{{ $settings->currency_symbol }} <span x-text="formatNum(stats.received)"></span></span>
                                <span x-show="!showNumbers" x-cloak>****</span>
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-gray-400">OUT</span>
                            <span class="font-black text-rose-500">
                                <span x-show="showNumbers">{{ $settings->currency_symbol }} <span x-text="formatNum(stats.paid)"></span></span>
                                <span x-show="!showNumbers" x-cloak>****</span>
                            </span>
                        </div>
                        <div class="mt-2 text-[10px] text-gray-400 uppercase font-bold italic" x-text="stats.label"></div>
                    </div>
                </div>

                <!-- Inventory -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border-b-4 border-rose-500 shadow-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-black uppercase text-rose-600 tracking-widest">Warehouse</span>
                        <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m-8-4V7m8 4v10M4 7l8 4" />
                        </svg>
                    </div>
                    <div class="text-2xl font-black text-gray-900 dark:text-white italic">
                        {{ $stats['total_products'] }} <span
                            class="text-sm font-bold text-gray-400 uppercase tracking-tighter">Items</span>
                    </div>
                    <div class="mt-2 text-xs text-rose-500 uppercase font-black italic">
                        {{ $lowStockProducts->count() }} ALERTS ACTIVE
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Sales vs Purchases Trend (Last 30 Days) -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl p-6 border border-gray-100 dark:border-gray-700">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3
                                class="text-lg font-black uppercase italic tracking-tighter text-gray-800 dark:text-gray-200">
                                Revenue Trend
                            </h3>
                            <p
                                class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-widest mt-1">
                                Last 30 Days Performance
                            </p>
                        </div>
                        <div class="flex items-center gap-3 text-xs font-bold">
                            <div class="flex items-center gap-1">
                                <span class="w-3 h-3 bg-emerald-500 rounded-full"></span>
                                <span class="text-gray-600 dark:text-gray-400">Sales</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="w-3 h-3 bg-amber-500 rounded-full"></span>
                                <span class="text-gray-600 dark:text-gray-400">Purchases</span>
                            </div>
                        </div>
                    </div>
                    <div class="relative h-64">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- Category Breakdown (Pie Chart) -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl p-6 border border-gray-100 dark:border-gray-700">
                    <div class="mb-6">
                        <h3
                            class="text-lg font-black uppercase italic tracking-tighter text-gray-800 dark:text-gray-200">
                            Sales by Category
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-widest mt-1">
                            Revenue Distribution (30 Days)
                        </p>
                    </div>
                    <div class="relative h-64 flex items-center justify-center">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Products Bar Chart -->
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl p-6 border border-gray-100 dark:border-gray-700">
                <div class="mb-6">
                    <h3 class="text-lg font-black uppercase italic tracking-tighter text-gray-800 dark:text-gray-200">
                        Top Selling Products
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-widest mt-1">
                        By Revenue - Last 30 Days
                    </p>
                </div>
                <div class="relative h-80">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Main Activity Feed (Sales Trends etc) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Recent Sales -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl overflow-hidden border border-gray-100 dark:border-gray-700">
                        <div
                            class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/50">
                            <h3
                                class="text-lg font-black uppercase italic tracking-tighter text-gray-800 dark:text-gray-200">
                                Recent Sales Transactions</h3>
                            <a href="{{ route('sales.index') }}"
                                class="text-xs font-black text-indigo-600 uppercase hover:underline">View All</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-xs font-black uppercase text-gray-500 tracking-widest">
                                            Date</th>
                                        <th
                                            class="px-6 py-3 text-xs font-black uppercase text-gray-500 tracking-widest">
                                            Customer</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-black uppercase text-gray-500 tracking-widest">
                                            Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach ($recentSales as $sale)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                            <td
                                                class="px-6 py-4 text-sm font-bold italic text-gray-600 dark:text-gray-400">
                                                {{ $sale->date }}</td>
                                            <td
                                                class="px-6 py-4 text-sm font-black uppercase text-gray-900 dark:text-white tracking-tighter">
                                                {{ $sale->party->name }}</td>
                                            <td
                                                class="px-6 py-4 text-right text-sm font-black text-indigo-600 dark:text-indigo-400">
                                                <span x-show="showNumbers">{{ $settings->currency_symbol }}
                                                    {{ number_format($sale->total_amount, 2) }}</span>
                                                <span x-show="!showNumbers" x-cloak>****</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Recent Purchases -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl overflow-hidden border border-gray-100 dark:border-gray-700">
                        <div
                            class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/50">
                            <h3
                                class="text-lg font-black uppercase italic tracking-tighter text-gray-800 dark:text-gray-200">
                                Recent Procurement</h3>
                            <a href="{{ route('purchases.index') }}"
                                class="text-xs font-black text-amber-600 uppercase hover:underline">View All</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-xs font-black uppercase text-gray-500 tracking-widest">
                                            Date</th>
                                        <th
                                            class="px-6 py-3 text-xs font-black uppercase text-gray-500 tracking-widest">
                                            Vendor</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-black uppercase text-gray-500 tracking-widest">
                                            Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach ($recentPurchases as $purchase)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                            <td
                                                class="px-6 py-4 text-sm font-bold italic text-gray-600 dark:text-gray-400">
                                                {{ $purchase->date }}</td>
                                            <td
                                                class="px-6 py-4 text-sm font-black uppercase text-gray-900 dark:text-white tracking-tighter">
                                                {{ $purchase->party->name }}</td>
                                            <td
                                                class="px-6 py-4 text-right text-sm font-black text-amber-600 dark:text-amber-400">
                                                <span x-show="showNumbers">{{ $settings->currency_symbol }}
                                                    {{ number_format($purchase->total_amount, 2) }}</span>
                                                <span x-show="!showNumbers" x-cloak>****</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Content -->
                <div class="space-y-6">
                    <!-- Low Stock Alerts -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-rose-100 dark:border-rose-900/30 overflow-hidden">
                        <div
                            class="p-6 border-b border-rose-50 dark:border-rose-900/20 bg-rose-50/50 dark:bg-rose-900/10">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 bg-rose-500 rounded-full animate-pulse"></span>
                                <h3
                                    class="text-sm font-black uppercase italic text-rose-600 dark:text-rose-400 tracking-tighter">
                                    Critical Stock Alerts</h3>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            @forelse($lowStockProducts as $product)
                                <div
                                    class="flex justify-between items-center p-3 rounded-2xl bg-rose-50/50 dark:bg-rose-900/5 border border-rose-100 dark:border-rose-900/20">
                                    <div>
                                        <div
                                            class="text-xs font-black uppercase tracking-tighter text-gray-900 dark:text-white">
                                            {{ $product->name }}</div>
                                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                            {{ $product->brand->name ?? 'No Brand' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-black text-rose-600 dark:text-rose-400 italic">
                                            {{ number_format($product->current_stock, 0) }}</div>
                                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Left
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <p class="text-xs font-bold text-emerald-500 uppercase italic">Stock Levels Optimal
                                    </p>
                                </div>
                            @endforelse
                            <a href="{{ route('products.index') }}"
                                class="block text-center text-[10px] font-black uppercase text-gray-400 hover:text-gray-600 tracking-widest mt-2 transition">Check
                                Full Inventory</a>
                        </div>
                    </div>

                    <!-- Market Overview / Quick Actions -->
                    <div
                        class="bg-indigo-600 rounded-3xl p-6 shadow-xl shadow-indigo-200 dark:shadow-none relative overflow-hidden">
                        <div class="relative z-10">
                            <h3 class="text-lg font-black text-white italic uppercase tracking-tighter mb-4">Quick
                                Command</h3>
                            <div class="grid grid-cols-2 gap-3">
                                <a href="{{ route('sales.create') }}"
                                    class="bg-white/20 hover:bg-white/30 p-4 rounded-2xl transition group">
                                    <div
                                        class="text-xs font-black text-white uppercase tracking-tighter group-hover:translate-x-1 transition-transform">
                                        Create Invoice</div>
                                </a>
                                <a href="{{ route('purchases.create') }}"
                                    class="bg-white/20 hover:bg-white/30 p-4 rounded-2xl transition group">
                                    <div
                                        class="text-xs font-black text-white uppercase tracking-tighter group-hover:translate-x-1 transition-transform">
                                        Add Stock</div>
                                </a>
                                <a href="{{ route('payments.create') }}"
                                    class="bg-white/20 hover:bg-white/30 p-4 rounded-2xl transition group">
                                    <div
                                        class="text-xs font-black text-white uppercase tracking-tighter group-hover:translate-x-1 transition-transform">
                                        Payment In</div>
                                </a>
                                <a href="{{ route('reports.party-ledger') }}"
                                    class="bg-white/20 hover:bg-white/30 p-4 rounded-2xl transition group">
                                    <div
                                        class="text-xs font-black text-white uppercase tracking-tighter group-hover:translate-x-1 transition-transform">
                                        Party Ledger</div>
                                </a>
                            </div>
                        </div>
                        <!-- Abstract Design Element -->
                        <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        // Chart.js Global Configuration
        Chart.defaults.font.family = "'Figtree', sans-serif";
        Chart.defaults.font.weight = '600';
        Chart.defaults.color = getComputedStyle(document.documentElement).getPropertyValue('--tw-text-opacity') ?
            '#6B7280' : '#9CA3AF';

        // Detect dark mode
        const isDarkMode = document.documentElement.classList.contains('dark');
        const gridColor = isDarkMode ? 'rgba(75, 85, 99, 0.3)' : 'rgba(229, 231, 235, 0.8)';
        const textColor = isDarkMode ? '#9CA3AF' : '#6B7280';

        // 1. Sales vs Purchases Trend Chart (Line Chart)
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json($chartData['dates']),
                datasets: [{
                    label: 'Sales',
                    data: @json($chartData['sales']),
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#10B981',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                }, {
                    label: 'Purchases',
                    data: @json($chartData['purchases']),
                    borderColor: '#F59E0B',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#F59E0B',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: isDarkMode ? '#1F2937' : '#fff',
                        titleColor: isDarkMode ? '#F3F4F6' : '#111827',
                        bodyColor: isDarkMode ? '#D1D5DB' : '#6B7280',
                        borderColor: isDarkMode ? '#374151' : '#E5E7EB',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': {{ $settings->currency_symbol }}' + context
                                    .parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: gridColor,
                            drawBorder: false,
                        },
                        ticks: {
                            color: textColor,
                            callback: function(value) {
                                return '{{ $settings->currency_symbol }}' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false,
                        },
                        ticks: {
                            color: textColor,
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 10
                        }
                    }
                }
            }
        });

        // 2. Category Breakdown (Doughnut Chart)
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryData = @json($categoryBreakdown);
        const categoryColors = [
            '#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'
        ];

        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryData.map(c => c.name),
                datasets: [{
                    data: categoryData.map(c => c.total),
                    backgroundColor: categoryColors,
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            padding: 15,
                            font: {
                                size: 11,
                                weight: '700'
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: isDarkMode ? '#1F2937' : '#fff',
                        titleColor: isDarkMode ? '#F3F4F6' : '#111827',
                        bodyColor: isDarkMode ? '#D1D5DB' : '#6B7280',
                        borderColor: isDarkMode ? '#374151' : '#E5E7EB',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': {{ $settings->currency_symbol }}' + context.parsed
                                    .toLocaleString() + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // 3. Top Products (Horizontal Bar Chart)
        const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
        const topProductsData = @json($topProducts);

        new Chart(topProductsCtx, {
            type: 'bar',
            data: {
                labels: topProductsData.map(p => p.name),
                datasets: [{
                    label: 'Revenue',
                    data: topProductsData.map(p => p.total_revenue),
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)'
                    ],
                    borderRadius: 8,
                    barThickness: 40,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: isDarkMode ? '#1F2937' : '#fff',
                        titleColor: isDarkMode ? '#F3F4F6' : '#111827',
                        bodyColor: isDarkMode ? '#D1D5DB' : '#6B7280',
                        borderColor: isDarkMode ? '#374151' : '#E5E7EB',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: {{ $settings->currency_symbol }}' + context.parsed.x
                                    .toLocaleString();
                            },
                            afterLabel: function(context) {
                                const product = topProductsData[context.dataIndex];
                                return 'Qty Sold: ' + parseFloat(product.total_qty).toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: gridColor,
                            drawBorder: false,
                        },
                        ticks: {
                            color: textColor,
                            callback: function(value) {
                                return '{{ $settings->currency_symbol }}' + value.toLocaleString();
                            }
                        }
                    },
                    y: {
                        grid: {
                            display: false,
                            drawBorder: false,
                        },
                        ticks: {
                            color: textColor,
                            font: {
                                size: 12,
                                weight: '700'
                            }
                        }
                    }
                }
            }
        });
    </script>
</x-app-layout>
