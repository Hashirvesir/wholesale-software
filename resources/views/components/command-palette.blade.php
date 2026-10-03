<!-- Quick Search Modal (Command Palette) -->
<div x-data="commandPalette()" x-show="open" x-cloak @keydown.window.ctrl.k.prevent="toggle()"
    @keydown.window.meta.k.prevent="toggle()" @keydown.escape.window="open = false"
    class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

    <!-- Backdrop -->
    <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" @click="open = false">
    </div>

    <!-- Modal -->
    <div class="flex min-h-screen items-start justify-center p-4 pt-[10vh]">
        <div x-show="open" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="w-full max-w-2xl transform rounded-2xl bg-white dark:bg-gray-800 shadow-2xl ring-1 ring-black/5 transition-all"
            @click.away="open = false">

            <!-- Search Input -->
            <div class="relative">
                <div class="pointer-events-none absolute left-4 top-4 text-gray-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input x-ref="searchInput" x-model="query" @input="filterResults()" @keydown.down.prevent="selectNext()"
                    @keydown.up.prevent="selectPrevious()" @keydown.enter.prevent="navigateToSelected()" type="text"
                    placeholder="Search pages, reports, actions... (Ctrl+K)"
                    class="w-full border-0 border-b border-gray-200 dark:border-gray-700 bg-transparent pl-12 pr-4 py-4 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-0 text-base font-medium">
                <div class="pointer-events-none absolute right-4 top-4 flex items-center gap-2">
                    <kbd
                        class="hidden sm:inline-flex items-center rounded border border-gray-200 dark:border-gray-700 px-2 py-1 text-xs font-semibold text-gray-500 dark:text-gray-400">
                        ESC
                    </kbd>
                </div>
            </div>

            <!-- Results -->
            <div class="max-h-[60vh] overflow-y-auto p-2">
                <template x-if="filteredResults.length === 0 && query !== ''">
                    <div class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="font-medium">No results found</p>
                        <p class="text-xs mt-1">Try searching for something else</p>
                    </div>
                </template>

                <template x-for="(group, groupName) in groupedResults" :key="groupName">
                    <div class="mb-4">
                        <div class="px-3 py-2 text-xs font-black uppercase tracking-widest text-gray-400 dark:text-gray-500"
                            x-text="groupName"></div>
                        <div class="space-y-1">
                            <template x-for="(item, index) in group" :key="item.url">
                                <a :href="item.url" @mouseenter="selectedIndex = getGlobalIndex(groupName, index)"
                                    :class="{
                                        'bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800': selectedIndex ===
                                            getGlobalIndex(groupName, index),
                                        'border-transparent hover:bg-gray-50 dark:hover:bg-gray-700/50': selectedIndex !==
                                            getGlobalIndex(groupName, index)
                                    }"
                                    class="flex items-center gap-3 rounded-lg border px-3 py-2.5 transition-all duration-150 group">
                                    <div :class="{
                                        'bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400': selectedIndex ===
                                            getGlobalIndex(groupName, index),
                                        'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400': selectedIndex !==
                                            getGlobalIndex(groupName, index)
                                    }"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg transition-colors">
                                        <span x-html="item.icon" class="h-4 w-4"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div :class="{
                                            'text-primary-700 dark:text-primary-300 font-bold': selectedIndex ===
                                                getGlobalIndex(groupName, index),
                                            'text-gray-900 dark:text-gray-100 font-semibold': selectedIndex !==
                                                getGlobalIndex(groupName, index)
                                        }"
                                            class="text-sm" x-text="item.name"></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate"
                                            x-text="item.description"></div>
                                    </div>
                                    <div x-show="selectedIndex === getGlobalIndex(groupName, index)"
                                        class="flex items-center gap-1">
                                        <kbd
                                            class="hidden sm:inline-flex items-center rounded bg-primary-100 dark:bg-primary-900/40 px-1.5 py-0.5 text-xs font-semibold text-primary-600 dark:text-primary-400">
                                            ↵
                                        </kbd>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer -->
            <div
                class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900/50 rounded-b-2xl">
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-1">
                            <kbd
                                class="inline-flex items-center rounded border border-gray-300 dark:border-gray-600 px-1.5 py-0.5 font-semibold">↑</kbd>
                            <kbd
                                class="inline-flex items-center rounded border border-gray-300 dark:border-gray-600 px-1.5 py-0.5 font-semibold">↓</kbd>
                            <span class="ml-1">Navigate</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <kbd
                                class="inline-flex items-center rounded border border-gray-300 dark:border-gray-600 px-1.5 py-0.5 font-semibold">↵</kbd>
                            <span class="ml-1">Select</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <kbd
                            class="inline-flex items-center rounded border border-gray-300 dark:border-gray-600 px-1.5 py-0.5 font-semibold">ESC</kbd>
                        <span class="ml-1">Close</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function commandPalette() {
        return {
            open: false,
            query: '',
            selectedIndex: 0,
            filteredResults: [],
            groupedResults: {},

            allPages: [
                // Quick Actions
                {
                    name: 'Create New Sale Invoice',
                    url: '{{ route('sales.create') }}',
                    description: 'keyboard: Alt+S',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>',
                    category: 'Quick Actions'
                },
                {
                    name: 'Record New Purchase',
                    url: '{{ route('purchases.create') }}',
                    description: 'keyboard: Alt+P',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>',
                    category: 'Quick Actions'
                },
                {
                    name: 'Add New Product',
                    url: '{{ route('products.create') }}',
                    description: 'keyboard: Alt+Q',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>',
                    category: 'Quick Actions'
                },
                {
                    name: 'Add New Customer/Vendor',
                    url: '{{ route('parties.create') }}',
                    description: 'Add new business partner',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>',
                    category: 'Quick Actions'
                },
                {
                    name: 'Create New Expense',
                    url: '{{ route('expenses.create') }}',
                    description: 'Record business expense',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                    category: 'Quick Actions'
                },
                // Dashboard
                {
                    name: 'Dashboard',
                    url: '{{ route('dashboard') }}',
                    description: 'Overview and statistics',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
                    category: 'Main'
                },

                // Masters
                {
                    name: 'Products',
                    url: '{{ route('products.index') }}',
                    description: 'Manage your products',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
                    category: 'Masters'
                },
                {
                    name: 'Categories',
                    url: '{{ route('categories.index') }}',
                    description: 'Product categories',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>',
                    category: 'Masters'
                },
                {
                    name: 'Brands',
                    url: '{{ route('brands.index') }}',
                    description: 'Product brands',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>',
                    category: 'Masters'
                },
                {
                    name: 'Units',
                    url: '{{ route('units.index') }}',
                    description: 'Measurement units',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>',
                    category: 'Masters'
                },
                {
                    name: 'Warehouses',
                    url: '{{ route('warehouses.index') }}',
                    description: 'Storage locations',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
                    category: 'Masters'
                },
                {
                    name: 'Parties',
                    url: '{{ route('parties.index') }}',
                    description: 'Customers and vendors',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                    category: 'Masters'
                },
                {
                    name: 'Opening Stock',
                    url: '{{ route('opening-stocks.index') }}',
                    description: 'Initial inventory',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                    category: 'Masters'
                },

                // Trading
                {
                    name: 'Sales',
                    url: '{{ route('sales.index') }}',
                    description: 'Sales invoices',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                    category: 'Trading'
                },
                {
                    name: 'Purchases',
                    url: '{{ route('purchases.index') }}',
                    description: 'Purchase bills',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                    category: 'Trading'
                },
                {
                    name: 'Sales Returns',
                    url: '{{ route('sales-returns.index') }}',
                    description: 'Customer returns',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>',
                    category: 'Trading'
                },
                {
                    name: 'Purchase Returns',
                    url: '{{ route('purchase-returns.index') }}',
                    description: 'Vendor returns',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"/></svg>',
                    category: 'Trading'
                },
                {
                    name: 'Stock Transfers',
                    url: '{{ route('stock-transfers.index') }}',
                    description: 'Move stock between warehouses',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>',
                    category: 'Trading'
                },
                {
                    name: 'Stock Adjustments',
                    url: '{{ route('stock-adjustments.index') }}',
                    description: 'Manually adjust stock levels',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>',
                    category: 'Trading'
                },
                {
                    name: 'Cargo Charges',
                    url: '{{ route('cargo-charges.index') }}',
                    description: 'Shipping costs',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>',
                    category: 'Trading'
                },

                // Finance
                {
                    name: 'Chart of Accounts',
                    url: '{{ route('chart-of-accounts.index') }}',
                    description: 'Account structure',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
                    category: 'Finance'
                },
                {
                    name: 'Payments',
                    url: '{{ route('payments.index') }}',
                    description: 'Payment records',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                    category: 'Finance'
                },
                {
                    name: 'Expenses',
                    url: '{{ route('expenses.index') }}',
                    description: 'Business expenses',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                    category: 'Finance'
                },
                {
                    name: 'Expense Categories',
                    url: '{{ route('expense-categories.index') }}',
                    description: 'Expense types',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>',
                    category: 'Finance'
                },
                {
                    name: 'Account Transfers',
                    url: '{{ route('account-transfers.index') }}',
                    description: 'Money transfers',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>',
                    category: 'Finance'
                },

                // Reports
                {
                    name: 'Account Ledger',
                    url: '{{ route('reports.account-ledger') }}',
                    description: 'Account transactions',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                    category: 'Reports'
                },
                {
                    name: 'Party Ledger',
                    url: '{{ route('reports.party-ledger') }}',
                    description: 'Party transactions',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                    category: 'Reports'
                },
                {
                    name: 'Trial Balance',
                    url: '{{ route('reports.trial-balance') }}',
                    description: 'Account balances',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>',
                    category: 'Reports'
                },
                {
                    name: 'Profit & Loss',
                    url: '{{ route('reports.profit-loss') }}',
                    description: 'Income statement',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>',
                    category: 'Reports'
                },
                {
                    name: 'Balance Sheet',
                    url: '{{ route('reports.balance-sheet') }}',
                    description: 'Financial position',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
                    category: 'Reports'
                },
                {
                    name: 'Dead Stock Report',
                    url: '{{ route('reports.dead-stock') }}',
                    description: 'Slow-moving inventory',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
                    category: 'Reports'
                },
                {
                    name: 'Stock Alert Report',
                    url: '{{ route('reports.stock-alert') }}',
                    description: 'Low stock & reorder alerts',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
                    category: 'Reports'
                },

                // Settings
                {
                    name: 'Settings',
                    url: '{{ route('settings.edit') }}',
                    description: 'Application settings',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                    category: 'Settings'
                },
                {
                    name: 'Profile',
                    url: '{{ route('profile.edit') }}',
                    description: 'Your profile',
                    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
                    category: 'Settings'
                },
            ],

            toggle() {
                this.open = !this.open;
                if (this.open) {
                    this.$nextTick(() => {
                        this.$refs.searchInput.focus();
                        this.filterResults();
                    });
                } else {
                    this.query = '';
                    this.selectedIndex = 0;
                }
            },

            filterResults() {
                if (this.query === '') {
                    this.filteredResults = this.allPages;
                } else {
                    const searchTerm = this.query.toLowerCase();
                    this.filteredResults = this.allPages.filter(page =>
                        page.name.toLowerCase().includes(searchTerm) ||
                        page.description.toLowerCase().includes(searchTerm) ||
                        page.category.toLowerCase().includes(searchTerm)
                    );
                }

                // Group results by category
                this.groupedResults = this.filteredResults.reduce((groups, item) => {
                    const category = item.category;
                    if (!groups[category]) {
                        groups[category] = [];
                    }
                    groups[category].push(item);
                    return groups;
                }, {});

                // Reset selection
                this.selectedIndex = 0;
            },

            getGlobalIndex(groupName, localIndex) {
                let globalIndex = 0;
                for (const [key, group] of Object.entries(this.groupedResults)) {
                    if (key === groupName) {
                        return globalIndex + localIndex;
                    }
                    globalIndex += group.length;
                }
                return 0;
            },

            selectNext() {
                if (this.selectedIndex < this.filteredResults.length - 1) {
                    this.selectedIndex++;
                }
            },

            selectPrevious() {
                if (this.selectedIndex > 0) {
                    this.selectedIndex--;
                }
            },

            navigateToSelected() {
                if (this.filteredResults.length > 0) {
                    const selected = this.filteredResults[this.selectedIndex];
                    window.location.href = selected.url;
                }
            }
        }
    }
</script>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>
