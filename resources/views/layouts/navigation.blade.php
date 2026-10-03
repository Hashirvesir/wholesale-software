<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-4 lg:px-6">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Quick Search Button -->
                <div class="hidden sm:flex sm:items-center sm:ml-4">
                    <button @click="$dispatch('toggle-command-palette')"
                        onclick="document.querySelector('[x-data*=commandPalette]').__x.$data.toggle()"
                        class="group flex items-center gap-2 px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 transition-all duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span class="hidden xl:inline font-medium">Quick Search</span>
                        <kbd
                            class="hidden xl:inline-flex items-center gap-1 rounded bg-white dark:bg-gray-800 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600 shadow-sm">
                            <span class="text-[10px]">⌘</span>K
                        </kbd>
                    </button>

                    <button @click="$dispatch('toggle-shortcut-help')"
                        class="ml-2 group p-1.5 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 transition-all duration-150"
                        title="Keyboard Shortcuts (Ctrl+/)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

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
                <div class="hidden space-x-4 xl:space-x-6 sm:-my-px sm:ms-2 sm:flex">
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
                        <div class="hidden sm:flex sm:items-center">
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
                                        <x-dropdown-link :href="route('sales.index')">{{ __('Sales') }}</x-dropdown-link>
                                    @endcan
                                    @can('view purchases')
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
                        <div class="hidden sm:flex sm:items-center">
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
                        <div class="hidden sm:flex sm:items-center">
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
                        <div class="hidden sm:flex sm:items-center">
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
                        <div class="hidden sm:flex sm:items-center">
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

            <!-- Settings -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-4">
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

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
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
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <!-- Masters Section -->
            @canany([
                'view units',
                'view categories',
                'view brands',
                'view warehouses',
                'view products',
                'view opening
                stocks',
                'view parties',
                ])
                <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>
                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Masters') }}
                </div>
                @can('view units')
                    <x-responsive-nav-link :href="route('units.index')" :active="request()->routeIs('units.*')">{{ __('Units') }}</x-responsive-nav-link>
                @endcan
                @can('view categories')
                    <x-responsive-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')">{{ __('Categories') }}</x-responsive-nav-link>
                @endcan
                @can('view brands')
                    <x-responsive-nav-link :href="route('brands.index')" :active="request()->routeIs('brands.*')">{{ __('Brands') }}</x-responsive-nav-link>
                @endcan
                @can('view warehouses')
                    <x-responsive-nav-link :href="route('warehouses.index')" :active="request()->routeIs('warehouses.*')">{{ __('Warehouses') }}</x-responsive-nav-link>
                @endcan
                @can('view products')
                    <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">{{ __('Products') }}</x-responsive-nav-link>
                @endcan
                @can('view opening stocks')
                    <x-responsive-nav-link :href="route('opening-stocks.index')"
                        :active="request()->routeIs('opening-stocks.*')">{{ __('Opening Stock') }}</x-responsive-nav-link>
                @endcan
                @can('view parties')
                    <x-responsive-nav-link :href="route('parties.index')" :active="request()->routeIs('parties.*')">{{ __('Parties') }}</x-responsive-nav-link>
                @endcan
            @endcanany

            <!-- Trading Section -->
            @canany([
                'view sales',
                'view purchases',
                'view stock transfers',
                'view stock adjustments',
                'view cargo
                charges',
                ])
                <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>
                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Trading') }}
                </div>
                @can('view sales')
                    <x-responsive-nav-link :href="route('sales.index')" :active="request()->routeIs('sales.*')">{{ __('Sales') }}</x-responsive-nav-link>
                @endcan
                @can('view purchases')
                    <x-responsive-nav-link :href="route('purchases.index')" :active="request()->routeIs('purchases.*')">{{ __('Purchases') }}</x-responsive-nav-link>
                @endcan
                @can('view stock transfers')
                    <x-responsive-nav-link :href="route('stock-transfers.index')"
                        :active="request()->routeIs('stock-transfers.*')">{{ __('Stock Transfers') }}</x-responsive-nav-link>
                @endcan
                @can('view stock adjustments')
                    <x-responsive-nav-link :href="route('stock-adjustments.index')"
                        :active="request()->routeIs('stock-adjustments.*')">{{ __('Stock Adjustments') }}</x-responsive-nav-link>
                @endcan
                @can('view cargo charges')
                    <x-responsive-nav-link :href="route('cargo-charges.index')"
                        :active="request()->routeIs('cargo-charges.*')">{{ __('Cargo Charges') }}</x-responsive-nav-link>
                @endcan
            @endcanany

            <!-- Finance Section -->
            @canany(['view chart of accounts', 'view payments', 'view expenses', 'view account transfers'])
                <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>
                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Finance') }}
                </div>
                @can('view chart of accounts')
                    <x-responsive-nav-link :href="route('chart-of-accounts.index')"
                        :active="request()->routeIs('chart-of-accounts.*')">{{ __('Chart of Accounts') }}</x-responsive-nav-link>
                @endcan
                @can('view payments')
                    <x-responsive-nav-link :href="route('payments.index')" :active="request()->routeIs('payments.*')">{{ __('Payments') }}</x-responsive-nav-link>
                @endcan
                @can('view expenses')
                    <x-responsive-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')">{{ __('Expenses') }}</x-responsive-nav-link>
                @endcan
                @can('view account transfers')
                    <x-responsive-nav-link :href="route('account-transfers.index')"
                        :active="request()->routeIs('account-transfers.*')">{{ __('Account Transfers') }}</x-responsive-nav-link>
                @endcan
            @endcanany

            <!-- Reports Section -->
            @canany([
                'view account ledger',
                'view party ledger',
                'view trial balance',
                'view profit loss',
                'view balance
                sheet',
                'view dead stock',
                'view products summary',
                'view stock alert',
                'view aging report',
                'view daily collection report',
                'view expense report',
                'view sales analysis report',
                'view returns summary',
                'view cargo charges report',
                'view activity logs',
                ])
                <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>
                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Reports') }}
                </div>
                @can('view account ledger')
                    <x-responsive-nav-link :href="route('reports.account-ledger')"
                        :active="request()->routeIs('reports.account-ledger')">{{ __('Account Ledger') }}</x-responsive-nav-link>
                @endcan
                @can('view party ledger')
                    <x-responsive-nav-link :href="route('reports.party-ledger')"
                        :active="request()->routeIs('reports.party-ledger')">{{ __('Party Ledger') }}</x-responsive-nav-link>
                @endcan
                @can('view trial balance')
                    <x-responsive-nav-link :href="route('reports.trial-balance')"
                        :active="request()->routeIs('reports.trial-balance')">{{ __('Trial Balance') }}</x-responsive-nav-link>
                @endcan
                @can('view profit loss')
                    <x-responsive-nav-link :href="route('reports.profit-loss')"
                        :active="request()->routeIs('reports.profit-loss')">{{ __('Profit & Loss') }}</x-responsive-nav-link>
                @endcan
                @can('view balance sheet')
                    <x-responsive-nav-link :href="route('reports.balance-sheet')"
                        :active="request()->routeIs('reports.balance-sheet')">{{ __('Balance Sheet') }}</x-responsive-nav-link>
                @endcan
                @can('view dead stock')
                    <x-responsive-nav-link :href="route('reports.dead-stock')"
                        :active="request()->routeIs('reports.dead-stock')">{{ __('Dead Stock Report') }}</x-responsive-nav-link>
                @endcan
                @can('view products summary')
                    <x-responsive-nav-link :href="route('reports.products-summary')"
                        :active="request()->routeIs('reports.products-summary')">{{ __('Product Ledger') }}</x-responsive-nav-link>
                @endcan
                @can('view stock alert')
                    <x-responsive-nav-link :href="route('reports.stock-alert')"
                        :active="request()->routeIs('reports.stock-alert')">{{ __('Stock Alert Report') }}</x-responsive-nav-link>
                @endcan
                @can('view aging report')
                    <x-responsive-nav-link :href="route('reports.aging')"
                        :active="request()->routeIs('reports.aging')">{{ __('Aging Report') }}</x-responsive-nav-link>
                @endcan
                @can('view daily collection report')
                    <x-responsive-nav-link :href="route('reports.daily-collection')"
                        :active="request()->routeIs('reports.daily-collection')">{{ __('Daily Collection Report') }}</x-responsive-nav-link>
                @endcan
                @can('view expense report')
                    <x-responsive-nav-link :href="route('reports.expenses')"
                        :active="request()->routeIs('reports.expenses')">{{ __('Expense Report') }}</x-responsive-nav-link>
                @endcan
                @can('view sales analysis report')
                    <x-responsive-nav-link :href="route('reports.sales-analysis')"
                        :active="request()->routeIs('reports.sales-analysis')">{{ __('Sales Analysis Report') }}</x-responsive-nav-link>
                @endcan
                @can('view returns summary')
                    <x-responsive-nav-link :href="route('reports.returns-summary')"
                        :active="request()->routeIs('reports.returns-summary')">{{ __('Returns Summary') }}</x-responsive-nav-link>
                @endcan
                @can('view cargo charges report')
                    <x-responsive-nav-link :href="route('reports.cargo-charges')"
                        :active="request()->routeIs('reports.cargo-charges')">{{ __('Cargo Charges Report') }}</x-responsive-nav-link>
                @endcan
                @can('view activity logs')
                    <x-responsive-nav-link :href="route('activity-logs.index')" :active="request()->routeIs('activity-logs.*')">{{ __('Audit Logs') }}</x-responsive-nav-link>
                @endcan
            @endcanany

            <!-- Access Control Section -->
            @canany(['manage users', 'manage roles'])
                <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>
                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    {{ __('Access Control') }}</div>
                @can('manage users')
                    <x-responsive-nav-link :href="route('users.index')"
                        :active="request()->routeIs('users.*')">{{ __('Manage Users') }}</x-responsive-nav-link>
                @endcan
                @can('manage roles')
                    <x-responsive-nav-link :href="route('roles.index')"
                        :active="request()->routeIs('roles.*')">{{ __('Manage Roles') }}</x-responsive-nav-link>
                @endcan
            @endcanany
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('settings.edit')">
                    {{ __('Settings') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
