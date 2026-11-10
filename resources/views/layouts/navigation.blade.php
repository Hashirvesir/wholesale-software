<nav x-data="{
    open: false,
    darkMode: localStorage.getItem('dark-mode') === 'true' || (!('dark-mode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('dark-mode', this.darkMode);
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}" @toggle-mobile-menu.window="open = !open" @close-mobile-menu.window="open = false" @open-mobile-menu.window="open = true" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 sticky top-0 z-40">
    <!-- Primary Navigation Menu -->
    <div class="max-w-[1750px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Quick Search Button -->
                <div class="hidden xl:flex xl:items-center xl:ml-4">
                    <button @click="$dispatch('toggle-command-palette')"
                        onclick="document.querySelector('[x-data*=commandPalette]').__x.$data.toggle()"
                        class="group flex items-center gap-2 px-2.5 py-1.5 text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 transition-all duration-150 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span class="hidden 2xl:inline font-medium">Quick Search</span>
                        <kbd
                            class="hidden 2xl:inline-flex items-center gap-1 rounded bg-white dark:bg-gray-800 px-1.5 py-0.5 text-[10px] font-semibold text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600 shadow-sm">
                            <span class="text-[9px]">⌘</span>K
                        </kbd>
                    </button>

                    <button @click="$dispatch('toggle-shortcut-help')"
                        class="ml-2 group p-1.5 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 transition-all duration-150 shrink-0"
                        title="Keyboard Shortcuts (Ctrl+/)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <!-- AI Copilot Button -->
                    <button type="button" @click="$dispatch('toggle-ai-copilot')"
                        class="ml-2 group flex items-center gap-1.5 px-2.5 py-1.5 text-xs text-violet-700 dark:text-violet-300 bg-violet-50 dark:bg-violet-950/50 border border-violet-200 dark:border-violet-800 rounded-lg hover:bg-violet-100 dark:hover:bg-violet-900/60 transition-all duration-150 shrink-0 cursor-pointer shadow-2xs"
                        title="StockPOS AI Copilot (Alt + A)">
                        <svg class="w-3.5 h-3.5 text-amber-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span class="font-bold">Ask AI</span>
                        <kbd class="hidden 2xl:inline-flex items-center rounded bg-violet-100 dark:bg-violet-900 px-1 py-0.2 text-[9px] font-bold">Alt+A</kbd>
                    </button>

                    <!-- POS Counter Button -->
                    @can('view sales')
                        <a href="{{ route('pos.index') }}"
                            class="ml-2 group flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-black uppercase tracking-wider text-white bg-gradient-to-r from-emerald-500 to-teal-600 rounded-lg hover:from-emerald-600 hover:to-teal-700 transition-all duration-150 shadow-md shadow-emerald-500/20 transform hover:scale-105 shrink-0"
                            title="POS Counter Screen (F1)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span class="hidden 2xl:inline">POS Counter</span>
                            <span class="hidden xl:inline 2xl:hidden">POS</span>
                        </a>
                    @endcan

                    <!-- Quick View Button -->
                    <div x-data="{
                        open: false,
                        loading: false,
                        stats: null,
                        warehouse_id: '',
                        start_date: new Date().toISOString().split('T')[0],
                        end_date: new Date().toISOString().split('T')[0],
                        fetchStats() {
                            this.loading = true;
                            fetch(`{{ route('dashboard.quick-stats') }}?warehouse_id=${this.warehouse_id}&start_date=${this.start_date}&end_date=${this.end_date}`)
                                .then(response => response.json())
                                .then(data => {
                                    this.stats = data;
                                    this.loading = false;
                                    try { localStorage.setItem('invenco_cached_quick_stats', JSON.stringify(data)); } catch(e){}
                                })
                                .catch(() => {
                                    try {
                                        const cached = localStorage.getItem('invenco_cached_quick_stats');
                                        if (cached) {
                                            this.stats = JSON.parse(cached);
                                            this.stats.is_offline_cached = true;
                                        }
                                    } catch(e){}
                                    this.loading = false;
                                });
                        }
                    }" class="ml-2">
                        <button @click="open = true; fetchStats()"
                            class="group flex items-center gap-2 px-3 py-1.5 text-sm font-bold text-white bg-gradient-to-r from-indigo-500 to-purple-600 border border-transparent rounded-lg hover:from-indigo-600 hover:to-purple-700 transition-all duration-150 shadow-md transform hover:scale-105">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span class="hidden">Quick View</span>
                        </button>

                        <!-- Modal -->
                        <div x-show="open" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto"
                            style="display: none;">
                            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity"
                                @click="open = false"></div>

                            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                                <div
                                    class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-gray-100 dark:border-gray-700">

                                    <!-- Header -->
                                    <div
                                        class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 flex justify-between items-center">
                                        <h3
                                            class="text-xl font-black text-white tracking-wide uppercase flex items-center gap-2">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                            <span x-text="start_date === end_date ? 'Quick View — ' + start_date : 'Quick View — ' + start_date + ' to ' + end_date"></span>
                                        </h3>
                                        <button @click="open = false"
                                            class="text-white hover:text-gray-200 focus:outline-none">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Controls -->
                                    <div
                                        class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-100 dark:border-gray-700">
                                        <div class="flex flex-wrap gap-3 items-end">
                                            <div class="flex flex-col gap-1">
                                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Start Date</label>
                                                <input type="date" x-model="start_date" @change="fetchStats()"
                                                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                            </div>
                                            <div class="flex flex-col gap-1">
                                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">End Date</label>
                                                <input type="date" x-model="end_date" @change="fetchStats()"
                                                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                            </div>
                                            <div class="flex flex-col gap-1">
                                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Warehouse</label>
                                                <select x-model="warehouse_id" @change="fetchStats()"
                                                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                                    <option value="">All Warehouses</option>
                                                    @foreach (\App\Models\Warehouse::all() as $w)
                                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button @click="start_date = new Date().toISOString().split('T')[0]; end_date = new Date().toISOString().split('T')[0]; fetchStats()"
                                                class="px-3 py-2 text-xs font-bold bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded-md hover:bg-indigo-200 dark:hover:bg-indigo-900/70 transition">
                                                Today
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
                                        <div x-show="loading" class="flex justify-center items-center py-12">
                                            <svg class="animate-spin h-10 w-10 text-indigo-500"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                        </div>

                                        <div x-show="!loading && stats" style="display: none;">
                                            <!-- Offline Cached Notice -->
                                            <template x-if="stats && stats.is_offline_cached">
                                                <div class="mb-4 p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-xs font-bold flex items-center justify-between">
                                                    <span>⚡ Offline Mode: Showing locally cached statistics</span>
                                                    <span class="text-[10px] bg-amber-200 dark:bg-amber-900/60 px-2 py-0.5 rounded">Cached</span>
                                                </div>
                                            </template>

                                            <!-- Top KPIs -->
                                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                                                <!-- Total Sales Today -->
                                                <div
                                                    class="bg-gradient-to-br from-green-50 to-emerald-100 dark:from-gray-800 dark:to-gray-800 p-4 rounded-xl border border-green-200 dark:border-gray-700 shadow-sm">
                                                    <div
                                                        class="text-xs font-bold text-green-600 dark:text-green-400 uppercase tracking-wider mb-1">
                                                        Sales</div>
                                                    <div
                                                        class="text-2xl font-black text-gray-900 dark:text-gray-100 flex items-baseline">
                                                        <span class="text-sm mr-1">Rs</span>
                                                        <span
                                                            x-text="new Intl.NumberFormat().format(stats?.sales || 0)"></span>
                                                    </div>
                                                    <div
                                                        class="text-xs text-green-700 dark:text-green-500 mt-1 font-medium">
                                                        <span x-text="stats?.sales_count || 0"></span> Invoices
                                                    </div>
                                                </div>

                                                <!-- Total Receipts (Cash In) -->
                                                <div
                                                    class="bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-800 dark:to-gray-800 p-4 rounded-xl border border-blue-200 dark:border-gray-700 shadow-sm">
                                                    <div
                                                        class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-1">
                                                        Cash Received</div>
                                                    <div
                                                        class="text-2xl font-black text-gray-900 dark:text-gray-100 flex items-baseline">
                                                        <span class="text-sm mr-1">Rs</span>
                                                        <span
                                                            x-text="new Intl.NumberFormat().format(stats?.total_received || 0)"></span>
                                                    </div>
                                                    <div
                                                        class="text-xs text-blue-700 dark:text-blue-500 mt-1 font-medium">
                                                        Money In
                                                    </div>
                                                </div>

                                                <!-- Total Payments (Cash Out) -->
                                                <div
                                                    class="bg-gradient-to-br from-orange-50 to-red-100 dark:from-gray-800 dark:to-gray-800 p-4 rounded-xl border border-orange-200 dark:border-gray-700 shadow-sm">
                                                    <div
                                                        class="text-xs font-bold text-orange-600 dark:text-orange-400 uppercase tracking-wider mb-1">
                                                        Cash Paid</div>
                                                    <div
                                                        class="text-2xl font-black text-gray-900 dark:text-gray-100 flex items-baseline">
                                                        <span class="text-sm mr-1">Rs</span>
                                                        <span
                                                            x-text="new Intl.NumberFormat().format(stats?.total_paid || 0)"></span>
                                                    </div>
                                                    <div
                                                        class="text-xs text-orange-700 dark:text-orange-500 mt-1 font-medium">
                                                        Purchases & Expenses
                                                    </div>
                                                </div>

                                                <!-- Net Cash Flow -->
                                                <div
                                                    class="bg-gray-50 dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                                                    <div
                                                        class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                                                        Net Cash Flow</div>
                                                    <div class="text-2xl font-black text-gray-900 dark:text-gray-100 flex items-baseline"
                                                        :class="(stats?.total_received - stats?.total_paid) >= 0 ?
                                                            'text-green-600' : 'text-red-600'">
                                                        <span
                                                            x-text="(stats?.total_received - stats?.total_paid) >= 0 ? '+' : ''"></span>
                                                        <span class="text-sm mr-1">Rs</span>
                                                        <span
                                                            x-text="new Intl.NumberFormat().format(Math.abs((stats?.total_received || 0) - (stats?.total_paid || 0)))"></span>
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1 font-medium">
                                                        Balance Change
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Detailed Breakdown Grid -->
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                                <!-- Bank/Cash Accounts -->
                                                <div>
                                                    <h4
                                                        class="text-sm font-bold text-gray-900 dark:text-gray-100 uppercase tracking-widest border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                                                        Bank & Cash Details
                                                    </h4>
                                                    <div
                                                        class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                                                        <table
                                                            class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                            <thead class="bg-gray-50 dark:bg-gray-800">
                                                                <tr>
                                                                    <th
                                                                        class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                                        Account</th>
                                                                    <th
                                                                        class="px-3 py-2 text-right text-xs font-medium text-green-600 dark:text-green-400 uppercase">
                                                                        In</th>
                                                                    <th
                                                                        class="px-3 py-2 text-right text-xs font-medium text-red-600 dark:text-red-400 uppercase">
                                                                        Out</th>
                                                                    <th
                                                                        class="px-3 py-2 text-right text-xs font-medium text-indigo-600 dark:text-indigo-400 uppercase">
                                                                        Net</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody
                                                                class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                                                <template
                                                                    x-for="(details, account) in stats?.bank_cash_details"
                                                                    :key="account">
                                                                    <tr>
                                                                        <td class="px-3 py-2 whitespace-nowrap text-xs font-medium text-gray-900 dark:text-gray-100"
                                                                            x-text="account"></td>
                                                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-right text-green-600 dark:text-green-400 font-bold"
                                                                            x-text="new Intl.NumberFormat().format(details.in)">
                                                                        </td>
                                                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-right text-red-600 dark:text-red-400 font-bold"
                                                                            x-text="new Intl.NumberFormat().format(details.out)">
                                                                        </td>
                                                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-right font-black"
                                                                            :class="details.net >= 0 ?
                                                                                'text-indigo-600 dark:text-indigo-400' :
                                                                                'text-rose-600'"
                                                                            x-text="new Intl.NumberFormat().format(details.net)">
                                                                        </td>
                                                                    </tr>
                                                                </template>
                                                                <tr
                                                                    x-show="!stats?.bank_cash_details || Object.keys(stats?.bank_cash_details).length === 0">
                                                                    <td colspan="3"
                                                                        class="px-3 py-4 text-center text-xs text-gray-500 italic">
                                                                        No cash movements today.</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                <!-- Other Today Stats -->
                                                <div>
                                                    <h4
                                                        class="text-sm font-bold text-gray-900 dark:text-gray-100 uppercase tracking-widest border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                                                        Summary Stats
                                                    </h4>
                                                    <div class="space-y-3">
                                                        <div
                                                            class="flex justify-between items-center bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                                                            <span
                                                                class="text-sm text-gray-600 dark:text-gray-400 font-medium">Purchases
                                                                Made</span>
                                                            <span
                                                                class="text-sm font-bold text-gray-900 dark:text-gray-100"
                                                                x-text="'Rs ' + new Intl.NumberFormat().format(stats?.purchases || 0)"></span>
                                                        </div>
                                                        <div
                                                            class="flex justify-between items-center bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                                                            <span
                                                                class="text-sm text-gray-600 dark:text-gray-400 font-medium">Expenses
                                                                Incurred</span>
                                                            <span
                                                                class="text-sm font-bold text-gray-900 dark:text-gray-100"
                                                                x-text="'Rs ' + new Intl.NumberFormat().format(stats?.expenses || 0)"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Footer -->
                                    <div
                                        class="bg-gray-50 dark:bg-gray-900 px-6 py-3 sm:flex sm:flex-row-reverse border-t border-gray-100 dark:border-gray-700">
                                        <button type="button"
                                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 sm:mt-0 sm:w-auto"
                                            @click="open = false">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="hidden xl:flex xl:items-center space-x-3 2xl:space-x-5 xl:-my-px xl:ms-4 shrink-0">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <!-- Trading Dropdown -->
                    @canany([
                        'view sales',
                        'view purchases',
                        'view stock transfers',
                        'view stock adjustments',
                        'view
                        cargo charges',
                        ])
                        <div class="hidden xl:flex xl:items-center">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-xs font-black leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out h-16 uppercase tracking-wider">
                                        <div>{{ __('Trading') }}</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    @can('view sales')
                                        <x-dropdown-link :href="route('pos.index')" class="font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            {{ __('POS Counter') }}
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('sales.index')">{{ __('Sales') }}</x-dropdown-link>
                                        <x-dropdown-link :href="route('quotations.index')">{{ __('Quotations / Estimates') }}</x-dropdown-link>
                                    @endcan
                                    @can('view purchases')
                                        <x-dropdown-link :href="route('purchase-orders.index')">{{ __('Purchase Orders (PO)') }}</x-dropdown-link>
                                        <x-dropdown-link :href="route('purchases.index')">{{ __('Purchases') }}</x-dropdown-link>
                                    @endcan
                                    @can('view stock transfers')
                                        <x-dropdown-link :href="route('stock-transfers.index')">{{ __('Stock Transfers') }}</x-dropdown-link>
                                    @endcan
                                    @can('view stock adjustments')
                                        <x-dropdown-link :href="route('stock-adjustments.index')">{{ __('Stock Adjustments') }}</x-dropdown-link>
                                    @endcan
                                    @can('view cargo charges')
                                        <x-dropdown-link :href="route('cargo-charges.index')">{{ __('Cargo Charges') }}</x-dropdown-link>
                                    @endcan
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endcanany

                    <!-- Finance Dropdown -->
                    @canany(['view chart of accounts', 'view payments', 'view expenses', 'view account transfers'])
                        <div class="hidden xl:flex xl:items-center">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-xs font-black leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out h-16 uppercase tracking-wider">
                                        <div>{{ __('Finance') }}</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    @can('view chart of accounts')
                                        <x-dropdown-link :href="route('chart-of-accounts.index')">{{ __('Chart of Accounts') }}</x-dropdown-link>
                                    @endcan
                                    @can('view payments')
                                        <x-dropdown-link :href="route('payments.index')">{{ __('Payments') }}</x-dropdown-link>
                                        <x-dropdown-link :href="route('cheques.index')">{{ __('Cheques / PDCs') }}</x-dropdown-link>
                                    @endcan
                                    @can('view expenses')
                                        <x-dropdown-link :href="route('expenses.index')">{{ __('Expenses') }}</x-dropdown-link>
                                    @endcan
                                    @can('view account transfers')
                                        <x-dropdown-link :href="route('account-transfers.index')">{{ __('Account Transfers') }}</x-dropdown-link>
                                    @endcan
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endcanany

                    <!-- Reports Dropdown -->
                    @canany([
                        'view account ledger',
                        'view party ledger',
                        'view trial balance',
                        'view profit loss',
                        'view
                        balance sheet',
                        'view dead stock',
                        'view stock alert',
                        'view products summary',
                        'view aging report',
                        'view daily collection report',
                        'view expense report',
                        'view sales analysis report',
                        'view returns summary',
                        'view cargo charges report',
                        'view activity
                        logs',
                        ])
                        <div class="hidden xl:flex xl:items-center">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-xs font-black leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out h-16 uppercase tracking-wider">
                                        <div>{{ __('Reports') }}</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    @can('view account ledger')
                                        <x-dropdown-link :href="route('reports.account-ledger')">{{ __('Account Ledger') }}</x-dropdown-link>
                                    @endcan
                                    @can('view party ledger')
                                        <x-dropdown-link :href="route('reports.party-ledger')">{{ __('Party Ledger') }}</x-dropdown-link>
                                    @endcan
                                    <div class="border-t border-gray-100 dark:border-gray-700"></div>
                                    @can('view trial balance')
                                        <x-dropdown-link :href="route('reports.trial-balance')">{{ __('Trial Balance') }}</x-dropdown-link>
                                    @endcan
                                    @can('view profit loss')
                                        <x-dropdown-link :href="route('reports.profit-loss')">{{ __('Profit & Loss') }}</x-dropdown-link>
                                    @endcan
                                    @can('view balance sheet')
                                        <x-dropdown-link :href="route('reports.balance-sheet')">{{ __('Balance Sheet') }}</x-dropdown-link>
                                    @endcan
                                    @can('view dead stock')
                                        <x-dropdown-link :href="route('reports.dead-stock')">{{ __('Dead Stock Report') }}</x-dropdown-link>
                                    @endcan
                                    @can('view products summary')
                                        <x-dropdown-link :href="route('reports.products-summary')">{{ __('Product Ledger') }}</x-dropdown-link>
                                    @endcan
                                    @can('view stock alert')
                                        <x-dropdown-link :href="route('reports.stock-alert')">{{ __('Stock Alert Report') }}</x-dropdown-link>
                                    @endcan
                                    <div class="border-t border-gray-100 dark:border-gray-700"></div>
                                    @can('view aging report')
                                        <x-dropdown-link :href="route('reports.aging')">{{ __('Aging Report') }}</x-dropdown-link>
                                    @endcan
                                    @can('view daily collection report')
                                        <x-dropdown-link :href="route('reports.daily-collection')">{{ __('Daily Collection Report') }}</x-dropdown-link>
                                    @endcan
                                    @can('view expense report')
                                        <x-dropdown-link :href="route('reports.expenses')">{{ __('Expense Report') }}</x-dropdown-link>
                                    @endcan
                                    @can('view sales analysis report')
                                        <x-dropdown-link :href="route('reports.sales-analysis')">{{ __('Sales Analysis Report') }}</x-dropdown-link>
                                        <x-dropdown-link :href="route('reports.salesman-performance')">{{ __('Salesman & Recovery Report') }}</x-dropdown-link>
                                    @endcan
                                    @can('view returns summary')
                                        <x-dropdown-link :href="route('reports.returns-summary')">{{ __('Returns Summary') }}</x-dropdown-link>
                                    @endcan
                                    @can('view cargo charges report')
                                        <x-dropdown-link :href="route('reports.cargo-charges')">{{ __('Cargo Charges Report') }}</x-dropdown-link>
                                    @endcan
                                    @can('view activity logs')
                                        <div class="border-t border-gray-100 dark:border-gray-700"></div>
                                        <x-dropdown-link :href="route('activity-logs.index')">{{ __('Audit Logs') }}</x-dropdown-link>
                                    @endcan
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endcanany

                    <!-- Masters Dropdown -->
                    @canany([
                        'view products',
                        'view categories',
                        'view brands',
                        'view units',
                        'view warehouses',
                        'view
                        parties',
                        'view opening stocks',
                        ])
                        <div class="hidden xl:flex xl:items-center">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-xs font-black leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out h-16 uppercase tracking-wider">
                                        <div>{{ __('Masters') }}</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    @can('view products')
                                        <x-dropdown-link :href="route('products.index')">{{ __('Products') }}</x-dropdown-link>
                                        <x-dropdown-link :href="route('products.print-labels')">{{ __('Print Barcodes') }}</x-dropdown-link>
                                    @endcan
                                    @can('view categories')
                                        <x-dropdown-link :href="route('categories.index')">{{ __('Categories') }}</x-dropdown-link>
                                    @endcan
                                    @can('view brands')
                                        <x-dropdown-link :href="route('brands.index')">{{ __('Brands') }}</x-dropdown-link>
                                    @endcan
                                    @can('view units')
                                        <x-dropdown-link :href="route('units.index')">{{ __('Units') }}</x-dropdown-link>
                                    @endcan
                                    @can('view warehouses')
                                        <x-dropdown-link :href="route('warehouses.index')">{{ __('Warehouses') }}</x-dropdown-link>
                                    @endcan
                                    <div class="border-t border-gray-100 dark:border-gray-700"></div>
                                    @can('view parties')
                                        <x-dropdown-link :href="route('parties.index')">{{ __('Parties') }}</x-dropdown-link>
                                    @endcan
                                    @can('view opening stocks')
                                        <x-dropdown-link :href="route('opening-stocks.index')">{{ __('Opening Stock') }}</x-dropdown-link>
                                    @endcan
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endcanany
                    <!-- Access Control Dropdown -->
                    @canany(['manage users', 'manage roles'])
                        <div class="hidden xl:flex xl:items-center">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-xs font-black leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out h-16 uppercase tracking-wider">
                                        <div>{{ __('Access Control') }}</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    @can('manage users')
                                        <x-dropdown-link :href="route('users.index')">{{ __('Manage Users') }}</x-dropdown-link>
                                    @endcan
                                    @can('manage roles')
                                        <x-dropdown-link :href="route('roles.index')">{{ __('Manage Roles') }}</x-dropdown-link>
                                    @endcan
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endcanany
                </div>
            </div>

            <!-- Settings & Sync -->
            <div class="hidden xl:flex xl:items-center xl:ms-6 space-x-3">
                <!-- Offline / Online Sync Indicator -->
                <div x-data="{
                    open: false,
                    isOnline: navigator.onLine,
                    isSyncing: false,
                    pendingCount: 0,
                    salesCount: 0,
                    paymentsCount: 0,
                    lastSync: null,
                    init() {
                        if (window.OfflineSync) {
                            this.isOnline = window.OfflineSync.isOnline;
                            this.isSyncing = window.OfflineSync.isSyncing;
                            this.pendingCount = window.OfflineSync.getPendingCount();
                            this.salesCount = window.OfflineSync.salesQueue.length;
                            this.paymentsCount = window.OfflineSync.paymentsQueue.length;
                            this.lastSync = window.OfflineSync.lastSyncTime;
                        }
                        window.addEventListener('offline-status-changed', (e) => {
                            this.isOnline = e.detail.isOnline;
                            this.isSyncing = e.detail.isSyncing;
                            this.pendingCount = e.detail.pendingCount;
                            this.salesCount = e.detail.salesCount;
                            this.paymentsCount = e.detail.paymentsCount;
                            this.lastSync = e.detail.lastSyncTime;
                        });
                    },
                    syncNow() {
                        if (window.OfflineSync) {
                            window.OfflineSync.syncAll();
                        }
                    },
                    downloadCache() {
                        if (window.OfflineSync) {
                            window.OfflineSync.fetchMasterData().then(() => {
                                alert('Master data successfully cached for offline use!');
                            });
                        }
                    }
                }" class="relative">
                    <button @click="open = !open" type="button"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-wider transition-all duration-200 border shadow-xs"
                        :class="isSyncing ? 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800' :
                                (isOnline ?
                                    (pendingCount > 0 ? 'bg-amber-50 text-amber-700 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-700' : 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-700') :
                                    'bg-red-50 text-red-700 border-red-300 dark:bg-red-950/60 dark:text-red-300 dark:border-red-700 animate-pulse')">

                        <span class="w-2 h-2 rounded-full shrink-0"
                            :class="isSyncing ? 'bg-blue-500 animate-ping' :
                                    (isOnline ? (pendingCount > 0 ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500') : 'bg-red-500')"></span>

                        <span x-show="isSyncing">Syncing...</span>
                        <span x-show="!isSyncing && isOnline && pendingCount === 0">Online</span>
                        <span x-show="!isSyncing && isOnline && pendingCount > 0" x-text="'Sync (' + pendingCount + ')'"></span>
                        <span x-show="!isSyncing && !isOnline" x-text="'Offline' + (pendingCount > 0 ? ' (' + pendingCount + ')' : '')"></span>
                    </button>

                    <!-- Sync Manager Dropdown -->
                    <div x-show="open" @click.away="open = false" x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 p-4 z-50 text-xs">
                        
                        <div class="flex items-center justify-between pb-3 border-b dark:border-gray-700 mb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full" :class="isOnline ? 'bg-emerald-500' : 'bg-red-500'"></span>
                                <span class="font-black text-gray-900 dark:text-white uppercase tracking-wider" x-text="isOnline ? 'System Online' : 'System Offline'"></span>
                            </div>
                            <span class="text-[10px] text-gray-400" x-show="lastSync" x-text="'Synced: ' + lastSync"></span>
                        </div>

                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between items-center p-2 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
                                <span class="text-gray-600 dark:text-gray-400 font-semibold">Offline Sales in Queue:</span>
                                <span class="font-black font-mono px-2 py-0.5 rounded text-xs"
                                    :class="salesCount > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200' : 'text-gray-500'"
                                    x-text="salesCount"></span>
                            </div>
                            <div class="flex justify-between items-center p-2 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
                                <span class="text-gray-600 dark:text-gray-400 font-semibold">Offline Payments in Queue:</span>
                                <span class="font-black font-mono px-2 py-0.5 rounded text-xs"
                                    :class="paymentsCount > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200' : 'text-gray-500'"
                                    x-text="paymentsCount"></span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <button type="button" @click="syncNow()" :disabled="!isOnline || isSyncing || pendingCount === 0"
                                class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white rounded-xl font-bold uppercase tracking-wider flex items-center justify-center gap-1.5 transition shadow-sm">
                                <svg class="w-4 h-4" :class="isSyncing ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span x-text="isSyncing ? 'Synchronizing...' : 'Sync Now'"></span>
                            </button>

                            <button type="button" @click="downloadCache()" :disabled="!isOnline || isSyncing"
                                class="w-full py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 disabled:opacity-40 text-gray-700 dark:text-gray-300 rounded-xl font-semibold transition text-[11px]">
                                Update Offline Master Cache
                            </button>
                        </div>
                    </div>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-black uppercase tracking-wider leading-4 rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('settings.edit')">
                            {{ __('Settings') }}
                        </x-dropdown-link>

                        <!-- Dark/Light Mode Toggle inside Dropdown -->
                        <button x-data="{
                            darkMode: localStorage.getItem('dark-mode') === 'true' || (!('dark-mode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                            toggle() {
                                this.darkMode = !this.darkMode;
                                localStorage.setItem('dark-mode', this.darkMode);
                                if (this.darkMode) {
                                    document.documentElement.classList.add('dark');
                                } else {
                                    document.documentElement.classList.remove('dark');
                                }
                            }
                        }" @click="toggle()"
                            class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none transition duration-150 ease-in-out">
                            <div class="flex items-center">
                                <span x-show="darkMode" class="flex items-center">

                                    {{ __('Light Mode') }}
                                </span>
                                <span x-show="!darkMode" class="flex items-center">

                                    {{ __('Dark Mode') }}
                                </span>
                            </div>
                        </button>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Mobile / Tablet Action Icons & Hamburger -->
            <div class="-me-2 flex items-center space-x-1.5 xl:hidden">
                <!-- Mobile POS Button -->
                @can('view sales')
                    <a href="{{ route('pos.index') }}"
                        class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-black uppercase tracking-wider text-white bg-gradient-to-r from-emerald-500 to-teal-600 rounded-lg shadow-sm hover:from-emerald-600 hover:to-teal-700 transition"
                        title="POS Counter">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span>POS</span>
                    </a>
                @endcan

                <!-- Mobile Quick Search -->
                <button @click="$dispatch('toggle-command-palette')"
                    class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition"
                    title="Search (⌘K)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>

                <!-- Mobile Dark Mode -->
                <button @click="toggleDarkMode()"
                    class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition"
                    title="Toggle Dark/Light Mode">
                    <svg x-show="darkMode" class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                    </svg>
                    <svg x-show="!darkMode" class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                    </svg>
                </button>

                <!-- Hamburger Button -->
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-xl text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }"
        class="hidden xl:hidden max-h-[calc(100vh-4rem)] overflow-y-auto border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-2xl">
        <div x-data="{
            activeSection: '{{ request()->routeIs(['pos.*', 'sales.*', 'quotations.*', 'purchases.*', 'purchase-orders.*', 'stock-transfers.*', 'stock-adjustments.*', 'cargo-charges.*']) ? 'trading' : (request()->routeIs(['chart-of-accounts.*', 'payments.*', 'cheques.*', 'expenses.*', 'account-transfers.*']) ? 'finance' : (request()->routeIs(['units.*', 'categories.*', 'brands.*', 'warehouses.*', 'products.*', 'opening-stocks.*', 'parties.*']) ? 'masters' : (request()->routeIs(['reports.*', 'activity-logs.*']) ? 'reports' : (request()->routeIs(['users.*', 'roles.*']) ? 'access' : '')))) }}',
            toggle(sec) {
                this.activeSection = this.activeSection === sec ? '' : sec;
            }
        }">
            <!-- User Info Card -->
            <div class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800/80 dark:to-gray-900 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary-600 text-white font-black flex items-center justify-center text-sm shadow-sm uppercase">
                        {{ substr(Auth::user()->name, 0, 2) }}
                    </div>
                    <div>
                        <div class="font-bold text-sm text-gray-900 dark:text-gray-100">{{ Auth::user()->name }}</div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] px-2.5 py-1 rounded-full font-black uppercase tracking-wider bg-primary-50 dark:bg-primary-950/60 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
                        {{ Auth::user()->roles->first()->name ?? 'User' }}
                    </span>
                </div>
            </div>

            <!-- Primary Direct Links -->
            <div class="p-2 space-y-1">
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="rounded-xl flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>{{ __('Dashboard') }}</span>
                </x-responsive-nav-link>

                @can('view sales')
                    <x-responsive-nav-link :href="route('pos.index')" :active="request()->routeIs('pos.*')"
                        class="rounded-xl font-black text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border-l-4 border-emerald-500 flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <span>{{ __('⚡ POS Counter Screen') }}</span>
                        </span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-600 text-white font-mono uppercase font-black">Live</span>
                    </x-responsive-nav-link>
                @endcan
            </div>

            <!-- Accordion Section: Trading -->
            @canany(['view sales', 'view purchases', 'view stock transfers', 'view stock adjustments', 'view cargo charges'])
                <div class="border-t border-gray-100 dark:border-gray-700">
                    <button @click="toggle('trading')" type="button"
                        class="w-full flex items-center justify-between px-4 py-3 text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>{{ __('Trading & Orders') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] px-1.5 py-0.2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 font-mono font-bold">8</span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="activeSection === 'trading' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="activeSection === 'trading'" class="pl-4 pr-2 pb-2 space-y-1 bg-gray-50/50 dark:bg-gray-900/30">
                        @can('view sales')
                            <x-responsive-nav-link :href="route('sales.index')" :active="request()->routeIs('sales.*')">{{ __('Sales Invoices') }}</x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('quotations.index')" :active="request()->routeIs('quotations.*')">{{ __('Quotations / Estimates') }}</x-responsive-nav-link>
                        @endcan
                        @can('view purchases')
                            <x-responsive-nav-link :href="route('purchase-orders.index')" :active="request()->routeIs('purchase-orders.*')">{{ __('Purchase Orders (PO)') }}</x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('purchases.index')" :active="request()->routeIs('purchases.*')">{{ __('Purchase Invoices') }}</x-responsive-nav-link>
                        @endcan
                        @can('view stock transfers')
                            <x-responsive-nav-link :href="route('stock-transfers.index')" :active="request()->routeIs('stock-transfers.*')">{{ __('Stock Transfers') }}</x-responsive-nav-link>
                        @endcan
                        @can('view stock adjustments')
                            <x-responsive-nav-link :href="route('stock-adjustments.index')" :active="request()->routeIs('stock-adjustments.*')">{{ __('Stock Adjustments') }}</x-responsive-nav-link>
                        @endcan
                        @can('view cargo charges')
                            <x-responsive-nav-link :href="route('cargo-charges.index')" :active="request()->routeIs('cargo-charges.*')">{{ __('Cargo Charges') }}</x-responsive-nav-link>
                        @endcan
                    </div>
                </div>
            @endcanany

            <!-- Accordion Section: Finance -->
            @canany(['view chart of accounts', 'view payments', 'view expenses', 'view account transfers'])
                <div class="border-t border-gray-100 dark:border-gray-700">
                    <button @click="toggle('finance')" type="button"
                        class="w-full flex items-center justify-between px-4 py-3 text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ __('Finance & Accounts') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] px-1.5 py-0.2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 font-mono font-bold">5</span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="activeSection === 'finance' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="activeSection === 'finance'" class="pl-4 pr-2 pb-2 space-y-1 bg-gray-50/50 dark:bg-gray-900/30">
                        @can('view chart of accounts')
                            <x-responsive-nav-link :href="route('chart-of-accounts.index')" :active="request()->routeIs('chart-of-accounts.*')">{{ __('Chart of Accounts') }}</x-responsive-nav-link>
                        @endcan
                        @can('view payments')
                            <x-responsive-nav-link :href="route('payments.index')" :active="request()->routeIs('payments.*')">{{ __('Payment Vouchers') }}</x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('cheques.index')" :active="request()->routeIs('cheques.*')">{{ __('Cheques / PDCs Management') }}</x-responsive-nav-link>
                        @endcan
                        @can('view expenses')
                            <x-responsive-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')">{{ __('Expenses') }}</x-responsive-nav-link>
                        @endcan
                        @can('view account transfers')
                            <x-responsive-nav-link :href="route('account-transfers.index')" :active="request()->routeIs('account-transfers.*')">{{ __('Account Transfers') }}</x-responsive-nav-link>
                        @endcan
                    </div>
                </div>
            @endcanany

            <!-- Accordion Section: Masters -->
            @canany(['view units', 'view categories', 'view brands', 'view warehouses', 'view products', 'view opening stocks', 'view parties'])
                <div class="border-t border-gray-100 dark:border-gray-700">
                    <button @click="toggle('masters')" type="button"
                        class="w-full flex items-center justify-between px-4 py-3 text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>{{ __('Masters & Catalog') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] px-1.5 py-0.2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 font-mono font-bold">8</span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="activeSection === 'masters' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="activeSection === 'masters'" class="pl-4 pr-2 pb-2 space-y-1 bg-gray-50/50 dark:bg-gray-900/30">
                        @can('view products')
                            <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.index')">{{ __('Products Catalog') }}</x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('products.print-labels')" :active="request()->routeIs('products.print-labels')">{{ __('Print Barcodes') }}</x-responsive-nav-link>
                        @endcan
                        @can('view categories')
                            <x-responsive-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')">{{ __('Categories') }}</x-responsive-nav-link>
                        @endcan
                        @can('view brands')
                            <x-responsive-nav-link :href="route('brands.index')" :active="request()->routeIs('brands.*')">{{ __('Brands') }}</x-responsive-nav-link>
                        @endcan
                        @can('view units')
                            <x-responsive-nav-link :href="route('units.index')" :active="request()->routeIs('units.*')">{{ __('Units') }}</x-responsive-nav-link>
                        @endcan
                        @can('view warehouses')
                            <x-responsive-nav-link :href="route('warehouses.index')" :active="request()->routeIs('warehouses.*')">{{ __('Warehouses & Shops') }}</x-responsive-nav-link>
                        @endcan
                        @can('view parties')
                            <x-responsive-nav-link :href="route('parties.index')" :active="request()->routeIs('parties.*')">{{ __('Parties (Customers & Vendors)') }}</x-responsive-nav-link>
                        @endcan
                        @can('view opening stocks')
                            <x-responsive-nav-link :href="route('opening-stocks.index')" :active="request()->routeIs('opening-stocks.*')">{{ __('Opening Stock') }}</x-responsive-nav-link>
                        @endcan
                    </div>
                </div>
            @endcanany

            <!-- Accordion Section: Reports -->
            @canany(['view account ledger', 'view party ledger', 'view trial balance', 'view profit loss', 'view balance sheet', 'view dead stock', 'view products summary', 'view stock alert', 'view aging report', 'view daily collection report', 'view expense report', 'view sales analysis report', 'view returns summary', 'view cargo charges report', 'view activity logs'])
                <div class="border-t border-gray-100 dark:border-gray-700">
                    <button @click="toggle('reports')" type="button"
                        class="w-full flex items-center justify-between px-4 py-3 text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <span>{{ __('Reports & Analytics') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] px-1.5 py-0.2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 font-mono font-bold">16</span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="activeSection === 'reports' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="activeSection === 'reports'" class="pl-4 pr-2 pb-2 space-y-1 bg-gray-50/50 dark:bg-gray-900/30">
                        @can('view account ledger')
                            <x-responsive-nav-link :href="route('reports.account-ledger')" :active="request()->routeIs('reports.account-ledger')">{{ __('Account Ledger') }}</x-responsive-nav-link>
                        @endcan
                        @can('view party ledger')
                            <x-responsive-nav-link :href="route('reports.party-ledger')" :active="request()->routeIs('reports.party-ledger')">{{ __('Party Ledger') }}</x-responsive-nav-link>
                        @endcan
                        @can('view sales analysis report')
                            <x-responsive-nav-link :href="route('reports.salesman-performance')" :active="request()->routeIs('reports.salesman-performance')">
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ __('Salesman & Recovery Report') }}</span>
                            </x-responsive-nav-link>
                        @endcan
                        @can('view trial balance')
                            <x-responsive-nav-link :href="route('reports.trial-balance')" :active="request()->routeIs('reports.trial-balance')">{{ __('Trial Balance') }}</x-responsive-nav-link>
                        @endcan
                        @can('view profit loss')
                            <x-responsive-nav-link :href="route('reports.profit-loss')" :active="request()->routeIs('reports.profit-loss')">{{ __('Profit & Loss') }}</x-responsive-nav-link>
                        @endcan
                        @can('view balance sheet')
                            <x-responsive-nav-link :href="route('reports.balance-sheet')" :active="request()->routeIs('reports.balance-sheet')">{{ __('Balance Sheet') }}</x-responsive-nav-link>
                        @endcan
                        @can('view daily collection report')
                            <x-responsive-nav-link :href="route('reports.daily-collection')" :active="request()->routeIs('reports.daily-collection')">{{ __('Daily Collection Report') }}</x-responsive-nav-link>
                        @endcan
                        @can('view sales analysis report')
                            <x-responsive-nav-link :href="route('reports.sales-analysis')" :active="request()->routeIs('reports.sales-analysis')">{{ __('Sales Analysis Report') }}</x-responsive-nav-link>
                        @endcan
                        @can('view products summary')
                            <x-responsive-nav-link :href="route('reports.products-summary')" :active="request()->routeIs('reports.products-summary')">{{ __('Product Ledger') }}</x-responsive-nav-link>
                        @endcan
                        @can('view stock alert')
                            <x-responsive-nav-link :href="route('reports.stock-alert')" :active="request()->routeIs('reports.stock-alert')">{{ __('Stock Alert Report') }}</x-responsive-nav-link>
                        @endcan
                        @can('view aging report')
                            <x-responsive-nav-link :href="route('reports.aging')" :active="request()->routeIs('reports.aging')">{{ __('Aging Report') }}</x-responsive-nav-link>
                        @endcan
                        @can('view dead stock')
                            <x-responsive-nav-link :href="route('reports.dead-stock')" :active="request()->routeIs('reports.dead-stock')">{{ __('Dead Stock Report') }}</x-responsive-nav-link>
                        @endcan
                        @can('view expense report')
                            <x-responsive-nav-link :href="route('reports.expenses')" :active="request()->routeIs('reports.expenses')">{{ __('Expense Report') }}</x-responsive-nav-link>
                        @endcan
                        @can('view returns summary')
                            <x-responsive-nav-link :href="route('reports.returns-summary')" :active="request()->routeIs('reports.returns-summary')">{{ __('Returns Summary') }}</x-responsive-nav-link>
                        @endcan
                        @can('view cargo charges report')
                            <x-responsive-nav-link :href="route('reports.cargo-charges')" :active="request()->routeIs('reports.cargo-charges')">{{ __('Cargo Charges Report') }}</x-responsive-nav-link>
                        @endcan
                        @can('view activity logs')
                            <x-responsive-nav-link :href="route('activity-logs.index')" :active="request()->routeIs('activity-logs.*')">{{ __('Audit Logs') }}</x-responsive-nav-link>
                        @endcan
                    </div>
                </div>
            @endcanany

            <!-- Accordion Section: Access Control -->
            @canany(['manage users', 'manage roles'])
                <div class="border-t border-gray-100 dark:border-gray-700">
                    <button @click="toggle('access')" type="button"
                        class="w-full flex items-center justify-between px-4 py-3 text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>{{ __('Access Control') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] px-1.5 py-0.2 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 font-mono font-bold">2</span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="activeSection === 'access' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="activeSection === 'access'" class="pl-4 pr-2 pb-2 space-y-1 bg-gray-50/50 dark:bg-gray-900/30">
                        @can('manage users')
                            <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">{{ __('Manage Users') }}</x-responsive-nav-link>
                        @endcan
                        @can('manage roles')
                            <x-responsive-nav-link :href="route('roles.index')" :active="request()->routeIs('roles.*')">{{ __('Manage Roles') }}</x-responsive-nav-link>
                        @endcan
                    </div>
                </div>
            @endcanany

            <!-- Mobile Offline Sync Status Bar -->
            <div class="p-3 m-2 rounded-2xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-xs">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full"
                            :class="navigator.onLine ? 'bg-emerald-500' : 'bg-red-500 animate-ping'"></span>
                        <span class="font-black uppercase tracking-wider text-gray-800 dark:text-gray-200 text-[11px]"
                            x-text="navigator.onLine ? 'System Online' : 'Offline Mode (Local Storage)'"></span>
                    </div>
                    <button type="button" @click="if (window.OfflineSync) window.OfflineSync.syncAll();"
                        class="px-3 py-1.5 text-[11px] font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-xs flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Sync Now</span>
                    </button>
                </div>
            </div>

            <!-- Profile & Settings -->
            <div class="p-2 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/40 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')" class="rounded-xl flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>{{ __('Profile Settings') }}</span>
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('settings.edit')" :active="request()->routeIs('settings.*')" class="rounded-xl flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>{{ __('Shop / App Settings') }}</span>
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full text-start px-3 py-2 text-sm font-bold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-xl transition flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>{{ __('Log Out') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
