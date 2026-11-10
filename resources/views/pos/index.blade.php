<x-app-layout>
    <div x-data="posApp()" x-init="initPos()" @keydown.window="handleGlobalKey($event)" class="min-h-[calc(100vh-4rem)] bg-gray-100 dark:bg-gray-900 pb-12 select-none">

        <!-- Top Status Bar & Shortcuts Banner -->
        <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-2.5 shadow-sm">
            <div class="max-w-[1750px] mx-auto flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span class="flex h-3 w-3 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                        </span>
                        <h1 class="text-sm sm:text-base font-black text-gray-800 dark:text-gray-100 tracking-wide uppercase flex items-center gap-1.5">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>POS Counter</span>
                        </h1>
                    </div>

                    <!-- Hold / Recall Indicator -->
                    <template x-if="heldCarts.length > 0">
                        <button type="button" @click="showHeldModal = true"
                            class="px-2.5 py-1 text-xs font-bold bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300 rounded-lg border border-amber-300 dark:border-amber-700 hover:bg-amber-200 dark:hover:bg-amber-900 flex items-center gap-1 transition-all duration-150 animate-pulse">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Held Sales (<span x-text="heldCarts.length"></span>)</span>
                        </button>
                    </template>
                </div>

                <!-- Shortcuts Pills -->
                <div class="hidden lg:flex items-center gap-2 text-[11px] text-gray-500 dark:text-gray-400 font-semibold">
                    <span class="bg-gray-100 dark:bg-gray-700/70 px-2 py-0.5 rounded border border-gray-200 dark:border-gray-600"><kbd class="font-bold text-gray-800 dark:text-gray-200">F1</kbd> Scan / Search</span>
                    <span class="bg-gray-100 dark:bg-gray-700/70 px-2 py-0.5 rounded border border-gray-200 dark:border-gray-600"><kbd class="font-bold text-gray-800 dark:text-gray-200">F2</kbd> Customer</span>
                    <span class="bg-gray-100 dark:bg-gray-700/70 px-2 py-0.5 rounded border border-gray-200 dark:border-gray-600"><kbd class="font-bold text-gray-800 dark:text-gray-200">F4</kbd> Pay & Thermal</span>
                    <span class="bg-gray-100 dark:bg-gray-700/70 px-2 py-0.5 rounded border border-gray-200 dark:border-gray-600"><kbd class="font-bold text-gray-800 dark:text-gray-200">F7</kbd> Hold</span>
                    <span class="bg-gray-100 dark:bg-gray-700/70 px-2 py-0.5 rounded border border-gray-200 dark:border-gray-600"><kbd class="font-bold text-gray-800 dark:text-gray-200">F8</kbd> Discount</span>
                    <span class="bg-gray-100 dark:bg-gray-700/70 px-2 py-0.5 rounded border border-gray-200 dark:border-gray-600"><kbd class="font-bold text-gray-800 dark:text-gray-200">F9</kbd> Tender Cash</span>
                </div>

                <!-- Right Controls: Cost Toggle & Reset -->
                <div class="flex items-center gap-2">
                    <button type="button" @click="toggleCosts()"
                        class="px-2.5 py-1 text-xs font-bold rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all flex items-center gap-1.5 shadow-sm active:scale-95"
                        :title="showCosts ? 'Hide Costs' : 'Show Costs'">
                        <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <span x-text="showCosts ? 'Hide Cost' : 'Show Cost'"></span>
                    </button>

                    <button type="button" @click="confirmClearCart()"
                        class="px-2.5 py-1 text-xs font-bold rounded-lg border border-red-200 dark:border-red-900/60 bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 transition-all flex items-center gap-1 shadow-sm active:scale-95"
                        title="Clear Cart (Esc)">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        <span>Clear</span>
                    </button>

                    <a href="{{ route('sales.index') }}"
                        class="px-2.5 py-1 text-xs font-bold rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all shadow-sm active:scale-95">
                        Back to Sales
                    </a>
                </div>
            </div>
        </div>

        <!-- Main POS Split View -->
        <div class="max-w-[1750px] mx-auto p-3 sm:p-4">
            <!-- Global Alert/Error Banner -->
            <div x-show="errorMessage" x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="mb-3 p-3 bg-red-100 dark:bg-red-900/50 border-l-4 border-red-500 text-red-800 dark:text-red-200 flex justify-between items-center rounded-r-lg shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="text-sm font-bold" x-text="errorMessage"></span>
                </div>
                <button type="button" @click="errorMessage = ''" class="text-red-500 hover:text-red-700 text-lg font-black">&times;</button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                <!-- LEFT SIDE (7 Cols on desktop): Customer, Warehouse, Barcode/Search, Cart Table -->
                <div class="lg:col-span-7 xl:col-span-8 flex flex-col gap-3">

                    <!-- Top Bar: Customer Selector & Warehouse Source -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-3.5 shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">

                            <!-- Customer Selection (7 cols) -->
                            <div class="sm:col-span-7 relative" @click.away="customerDropdownOpen = false">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                        <span>Customer (F2)</span>
                                        <template x-if="isWalkIn()">
                                            <span class="px-1.5 py-0.2 bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 rounded text-[9px] font-black uppercase">Walk-In</span>
                                        </template>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="resetToWalkIn()"
                                            class="text-[10px] font-bold text-primary-600 hover:text-primary-700 dark:text-primary-400 hover:underline">
                                            Reset Walk-In
                                        </button>
                                        <span class="text-gray-300 dark:text-gray-600">|</span>
                                        <button type="button" @click="openQuickCustomerModal()"
                                            class="text-[10px] font-bold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 hover:underline flex items-center gap-0.5">
                                            <span>+ New Customer</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="relative">
                                    <input type="text" x-ref="customerInput" x-model="customerSearch"
                                        @focus="customerDropdownOpen = true" @input="customerDropdownOpen = true"
                                        @keydown="handleCustomerKey($event)"
                                        class="w-full text-xs font-semibold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:ring-emerald-500 pr-10 shadow-sm transition"
                                        placeholder="Search customer by name or phone (F2)..." autocomplete="off" />

                                    <div class="absolute right-2 top-2 text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>

                                <!-- Customer Dropdown -->
                                <div x-show="customerDropdownOpen && filteredCustomers().length > 0" x-cloak
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-1"
                                    class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 max-h-60 overflow-y-auto">
                                    <template x-for="(c, cIdx) in filteredCustomers()" :key="c.id">
                                        <div @click="selectCustomer(c)"
                                            class="p-2.5 border-b last:border-0 dark:border-gray-700 cursor-pointer hover:bg-emerald-50 dark:hover:bg-gray-700/70 flex items-center justify-between transition-colors duration-150 text-xs"
                                            :class="customerHighlightedIndex === cIdx ? 'bg-emerald-100/70 dark:bg-gray-700' : ''">
                                            <div>
                                                <div class="font-bold text-gray-800 dark:text-gray-100 uppercase" x-text="c.name"></div>
                                                <div class="text-[10px] text-gray-500 dark:text-gray-400" x-text="c.phone || 'No phone'"></div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-black text-xs"
                                                    :class="c.current_balance >= 0 ? 'text-red-500' : 'text-emerald-500'">
                                                    <span x-text="currency"></span> <span x-text="formatNumber(Math.abs(c.current_balance))"></span>
                                                </div>
                                                <div class="text-[9px] uppercase font-bold text-gray-400"
                                                    x-text="c.current_balance >= 0 ? 'Receivable' : 'Advance / Credit'"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Active Customer Balance & Credit Badges -->
                                <div class="mt-1 flex flex-wrap items-center justify-between text-[11px] font-bold gap-1">
                                    <template x-if="selectedCustomer">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-gray-500 dark:text-gray-400">Balance:</span>
                                            <span :class="selectedCustomer.current_balance >= 0 ? 'text-red-500' : 'text-emerald-500'">
                                                <span x-text="currency"></span> <span x-text="formatNumber(Math.abs(selectedCustomer.current_balance))"></span>
                                                <span x-text="selectedCustomer.current_balance >= 0 ? '(Due)' : '(Adv)'"></span>
                                            </span>
                                            <template x-if="!isWalkIn() && getCustomerCreditLimit() > 0">
                                                <span class="text-gray-500 dark:text-gray-400">
                                                    | Limit: <span class="text-gray-700 dark:text-gray-200" x-text="currency + ' ' + formatNumber(getCustomerCreditLimit())"></span>
                                                </span>
                                            </template>
                                            <template x-if="!isWalkIn() && getCustomerCreditDays() > 0">
                                                <span class="text-gray-500 dark:text-gray-400">
                                                    | Terms: <span class="text-gray-700 dark:text-gray-200" x-text="getCustomerCreditDays() + 'd'"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                <!-- Customer Alerts (Overdue & Exceeded) -->
                                <template x-if="!isWalkIn() && isCustomerOverdue()">
                                    <div class="mt-1.5 p-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-[10px] font-bold flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Customer has overdue invoices past <span x-text="getCustomerCreditDays()"></span> days due period!</span>
                                    </div>
                                </template>

                                <template x-if="!isWalkIn() && isCreditLimitExceeded()">
                                    <div class="mt-1.5 p-1.5 rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-300 dark:border-red-800 text-red-800 dark:text-red-200 text-[10px] font-bold flex items-center justify-between">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 shrink-0 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Exceeds Credit Limit by:</span>
                                        </div>
                                        <span class="font-mono text-red-600 dark:text-red-400" x-text="currency + ' ' + formatNumber(getCreditOverflow())"></span>
                                    </div>
                                </template>
                            </div>

                            <!-- Warehouse Stock Source (3 cols) -->
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                    Stock Source
                                </label>
                                <select x-model="warehouse_id" @change="onWarehouseChange()"
                                    class="w-full text-xs font-semibold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition">
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Date (2 cols) -->
                            <div class="sm:col-span-2">
                                <label class="block text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                    Date
                                </label>
                                <input type="date" x-model="sale_date"
                                    class="w-full text-xs font-semibold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition" />
                            </div>
                        </div>
                    </div>

                    <!-- Barcode Scanner & Search Bar -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-3 shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="relative" @click.away="productDropdownOpen = false">
                            <div class="flex items-center gap-2">
                                <div class="relative flex-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-emerald-600 dark:text-emerald-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                    </div>
                                    <input type="text" x-ref="barcodeInput" x-model="productSearch"
                                        @input="onProductSearchInput()"
                                        @keydown="handleProductInputKey($event)"
                                        class="w-full pl-10 pr-24 py-2.5 text-sm font-bold rounded-xl border-2 border-emerald-500/50 dark:border-emerald-600/50 dark:bg-gray-900 dark:text-gray-100 focus:border-emerald-600 focus:ring-emerald-500 shadow-sm transition"
                                        placeholder="Scan barcode or type product name / SKU (F1)..." autocomplete="off" />

                                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center gap-1">
                                        <span class="text-[10px] font-bold uppercase bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 px-2 py-0.5 rounded">
                                            Scan Ready
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Search Results Dropdown -->
                            <div x-show="productDropdownOpen && filteredProducts().length > 0" x-cloak
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-1"
                                class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="(p, pIdx) in filteredProducts()" :key="p.id">
                                    <div @click="addProductToCart(p)"
                                        class="p-2.5 cursor-pointer hover:bg-emerald-50 dark:hover:bg-gray-700/70 flex items-center justify-between transition-colors duration-150"
                                        :class="productHighlightedIndex === pIdx ? 'bg-emerald-100/70 dark:bg-gray-700' : ''">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center font-black text-gray-600 dark:text-gray-300 text-xs">
                                                <span x-text="(p.unit_name || 'PC').toUpperCase().substring(0, 2)"></span>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 dark:text-gray-100 text-xs uppercase" x-text="p.name"></div>
                                                <div class="flex items-center gap-2 text-[10px] text-gray-500 dark:text-gray-400 font-mono">
                                                    <span x-text="'SKU: ' + (p.sku || 'N/A')"></span>
                                                    <template x-if="p.barcode">
                                                        <span x-text="'| Bar: ' + p.barcode"></span>
                                                    </template>
                                                    <template x-if="p.category_name">
                                                        <span class="text-gray-400 dark:text-gray-500" x-text="'(' + p.category_name + ')'"></span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-3 text-right">
                                            <div>
                                                <div class="font-black text-xs text-gray-900 dark:text-gray-100">
                                                    <span x-text="currency"></span> <span x-text="formatNumber(p.sale_price)"></span>
                                                </div>
                                                <div class="text-[10px] font-bold"
                                                    :class="getProductWarehouseStock(p) > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-500'">
                                                    Stock: <span x-text="formatNumber(getProductWarehouseStock(p))"></span> <span x-text="p.unit_name"></span>
                                                </div>
                                            </div>
                                            <button type="button" class="p-1 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 active:scale-95 transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Category Filter Chips -->
                        <div class="flex items-center gap-1.5 mt-2 overflow-x-auto pb-1 scrollbar-thin">
                            <button type="button" @click="selectedCategory = ''"
                                class="px-2.5 py-0.5 rounded-full text-[11px] font-bold shrink-0 transition-all duration-150 active:scale-95"
                                :class="selectedCategory === '' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'">
                                All Items
                            </button>
                            @foreach ($categories as $cat)
                                <button type="button" @click="selectedCategory = '{{ $cat->id }}'"
                                    class="px-2.5 py-0.5 rounded-full text-[11px] font-bold shrink-0 transition-all duration-150 active:scale-95"
                                    :class="selectedCategory == '{{ $cat->id }}' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'">
                                    {{ $cat->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Cart Items Table -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex-1 flex flex-col overflow-hidden">
                        <div class="p-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-200">Current Cart</span>
                                <span class="bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 text-[11px] font-black px-2 py-0.5 rounded-full"
                                    x-text="items.length + ' Item(s)'"></span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" @click="holdCurrentCart()" :disabled="items.length === 0"
                                    class="px-2.5 py-1 text-[11px] font-bold rounded-lg border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/50 disabled:opacity-40 transition-all flex items-center gap-1 active:scale-95">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span>Hold Sale (F7)</span>
                                </button>
                            </div>
                        </div>

                        <!-- Table Area -->
                        <div class="overflow-x-auto flex-1 max-h-[460px] overflow-y-auto">
                            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700 text-left">
                                <thead class="bg-gray-50 dark:bg-gray-900/50 text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-3 py-2.5">#</th>
                                        <th class="px-3 py-2.5">Product</th>
                                        <th class="px-2 py-2.5 text-center">Stock</th>
                                        <th class="px-2 py-2.5 text-right" x-show="showCosts">Avg Cost</th>
                                        <th class="px-2 py-2.5 text-center w-28">Qty</th>
                                        <th class="px-2 py-2.5 text-right w-24">Price (PKR)</th>
                                        <th class="px-3 py-2.5 text-right">Total</th>
                                        <th class="px-2 py-2.5 text-center w-8"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-750 text-xs">
                                    <!-- Empty State -->
                                    <template x-if="items.length === 0">
                                        <tr>
                                            <td colspan="8" class="py-14 text-center text-gray-400 dark:text-gray-500">
                                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                                <p class="font-bold text-sm">Cart is empty</p>
                                                <p class="text-[11px] mt-0.5">Scan barcode or search products above to start billing</p>
                                            </td>
                                        </tr>
                                    </template>

                                    <!-- Cart Rows (Fixed dark hover: never turns white) -->
                                    <template x-for="(item, idx) in items" :key="idx">
                                        <tr class="transition-colors duration-150 border-b border-gray-100 dark:border-gray-700/60"
                                            :class="item.justAdded ? 'bg-emerald-100/40 dark:bg-emerald-950/50' : 'bg-white hover:bg-emerald-50/50 dark:bg-gray-800 dark:hover:bg-gray-700/60'">
                                            <td class="px-3 py-2 text-gray-400 font-mono text-[10px]" x-text="idx + 1"></td>
                                            <td class="px-3 py-2">
                                                <div class="font-bold text-gray-900 dark:text-gray-100 uppercase" x-text="item.name"></div>
                                                <div class="text-[10px] text-gray-500 dark:text-gray-400 font-mono" x-text="'SKU: ' + item.sku + (item.barcode ? ' | ' + item.barcode : '')"></div>
                                            </td>
                                            <td class="px-2 py-2 text-center">
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold"
                                                    :class="item.stock >= item.qty ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' : 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300'">
                                                    <span x-text="formatNumber(item.stock)"></span>
                                                </span>
                                                <template x-if="item.qty > item.stock">
                                                    <span class="block text-[8px] font-black text-amber-600 dark:text-amber-400 uppercase">Backorder</span>
                                                </template>
                                            </td>
                                            <td class="px-2 py-2 text-right font-mono text-rose-500 text-[11px]" x-show="showCosts">
                                                <span x-text="formatNumber(item.cost)"></span>
                                            </td>
                                            <td class="px-2 py-2">
                                                <div class="flex items-center justify-center gap-1">
                                                    <button type="button" @click="decrementQty(idx)"
                                                        class="w-6 h-6 rounded bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 active:scale-95 transition-all font-bold text-gray-700 dark:text-gray-200 flex items-center justify-center text-xs shadow-sm">
                                                        -
                                                    </button>
                                                    <input type="number" x-model.number="item.qty" step="0.01" min="0.01"
                                                        @input="recalculate()"
                                                        class="w-14 text-center py-1 px-1 text-xs font-bold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition" />
                                                    <button type="button" @click="incrementQty(idx)"
                                                        class="w-6 h-6 rounded bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 active:scale-95 transition-all font-bold text-gray-700 dark:text-gray-200 flex items-center justify-center text-xs shadow-sm">
                                                        +
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-2 py-2 text-right">
                                                <input type="number" x-model.number="item.price" step="0.01" min="0"
                                                    @input="recalculate()"
                                                    class="w-20 text-right py-1 px-1 text-xs font-bold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition" />
                                                <template x-if="showCosts && item.cost > 0 && item.price < item.cost">
                                                    <span class="block text-[8px] font-black text-rose-500 uppercase tracking-tighter">Below Cost!</span>
                                                </template>
                                                <template x-if="item.lastPrice && item.lastPrice.found">
                                                    <button type="button" @click="item.price = item.lastPrice.price; recalculate();"
                                                        class="mt-1 inline-flex items-center gap-0.5 text-[9px] font-black text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 px-1 py-0.5 rounded cursor-pointer transition whitespace-nowrap"
                                                        :title="'Last sold: ' + currency + ' ' + formatNumber(item.lastPrice.price) + ' on ' + item.lastPrice.date + ' (Inv #' + item.lastPrice.invoice_formatted + ')'">
                                                        <span>Last: <span x-text="formatNumber(item.lastPrice.price)"></span></span>
                                                    </button>
                                                </template>
                                            </td>
                                            <td class="px-3 py-2 text-right font-black text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                                <span x-text="formatNumber(item.qty * item.price)"></span>
                                            </td>
                                            <td class="px-2 py-2 text-center">
                                                <button type="button" @click="removeItem(idx)"
                                                    class="text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 hover:scale-110 active:scale-95 transition-all p-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- RIGHT SIDE (5 Cols on desktop): Checkout Panel & Financials -->
                <div class="lg:col-span-5 xl:col-span-4 flex flex-col gap-3">

                    <!-- Invoice Summary & Payment Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col justify-between">

                        <!-- Main Financial Figures -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400 font-bold border-b dark:border-gray-700 pb-2">
                                <span>Items: <strong class="text-gray-800 dark:text-gray-200" x-text="items.length"></strong></span>
                                <span>Total Qty: <strong class="text-gray-800 dark:text-gray-200" x-text="formatNumber(totalQty())"></strong></span>
                            </div>

                            <!-- Subtotal -->
                            <div class="flex justify-between items-center text-sm">
                                <span class="font-bold text-gray-600 dark:text-gray-400">Subtotal:</span>
                                <span class="font-black text-gray-800 dark:text-gray-200">
                                    <span x-text="currency"></span> <span x-text="formatNumber(subtotal())"></span>
                                </span>
                            </div>

                            <!-- Discount Input with Quick Buttons -->
                            <div class="bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="flex justify-between items-center mb-1.5">
                                    <label class="text-[11px] font-black uppercase tracking-wider text-rose-500 flex items-center gap-1">
                                        <span>Discount (Flat PKR - F8)</span>
                                    </label>
                                    <template x-if="discount_amount > 0">
                                        <button type="button" @click="discount_amount = 0; recalculate()"
                                            class="text-[10px] text-gray-400 hover:text-red-500 font-bold">Reset</button>
                                    </template>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="number" x-ref="discountInput" x-model.number="discount_amount"
                                        @input="recalculate()" min="0" step="1"
                                        class="w-full text-sm font-bold text-rose-600 dark:text-rose-400 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 py-1.5 focus:border-rose-500 focus:ring-rose-500 transition"
                                        placeholder="0.00" />
                                </div>
                                <div class="flex items-center gap-1 mt-1.5">
                                    <button type="button" @click="applyQuickDiscount(50)"
                                        class="px-2 py-0.5 text-[10px] font-bold rounded bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 active:scale-95 transition-all">+50</button>
                                    <button type="button" @click="applyQuickDiscount(100)"
                                        class="px-2 py-0.5 text-[10px] font-bold rounded bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 active:scale-95 transition-all">+100</button>
                                    <button type="button" @click="applyQuickDiscount(500)"
                                        class="px-2 py-0.5 text-[10px] font-bold rounded bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 active:scale-95 transition-all">+500</button>
                                </div>
                            </div>

                            <!-- GRAND TOTAL HERO -->
                            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white p-4 rounded-xl shadow-lg relative overflow-hidden transition-all duration-200">
                                <div class="relative z-10 flex justify-between items-baseline">
                                    <div>
                                        <div class="text-[10px] font-black uppercase tracking-widest text-emerald-100 opacity-90">Net Payable</div>
                                        <div class="text-3xl font-black tracking-tight font-mono">
                                            <span class="text-xl" x-text="currency"></span> <span x-text="formatNumber(grandTotal())"></span>
                                        </div>
                                    </div>
                                    <template x-if="selectedCustomer && !isWalkIn()">
                                        <div class="text-right">
                                            <div class="text-[10px] font-bold text-emerald-100">Customer Net Due</div>
                                            <div class="text-xs font-black font-mono"
                                                x-text="currency + ' ' + formatNumber(grandTotal() + (selectedCustomer.current_balance || 0) - totalPaid())"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Payment Splits Section -->
                            <div class="border-t dark:border-gray-700 pt-3 space-y-2">
                                <div class="flex justify-between items-center">
                                    <label class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Payment Method(s)
                                    </label>
                                    <button type="button" @click="addPaymentSplit()"
                                        class="text-[10px] font-bold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400">
                                        + Split Account
                                    </button>
                                </div>

                                <template x-for="(payment, pIdx) in payments" :key="pIdx">
                                    <div class="flex items-center gap-2">
                                        <select x-model="payment.account_id"
                                            class="w-1/2 text-xs font-semibold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 py-1.5 focus:border-emerald-500 focus:ring-emerald-500 transition">
                                            <option value="">-- Select Account --</option>
                                            @foreach ($paymentAccounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" x-model.number="payment.amount" step="0.01" min="0"
                                            class="w-1/2 text-xs font-bold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 py-1.5 text-right focus:border-emerald-500 focus:ring-emerald-500 transition"
                                            placeholder="0.00" />
                                        <template x-if="payments.length > 1">
                                            <button type="button" @click="removePaymentSplit(pIdx)" class="text-red-500 hover:text-red-700 text-sm font-bold">&times;</button>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            <!-- Cash Tendered & Change Return (Baaqi / Wapsi) -->
                            <div class="bg-gray-50 dark:bg-gray-900/50 p-3 rounded-xl border border-gray-200 dark:border-gray-700 space-y-2">
                                <div class="flex justify-between items-center">
                                    <label class="text-[11px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Cash Tendered (F9)
                                    </label>
                                    <button type="button" @click="setExactCashTender()"
                                        class="text-[10px] font-bold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400">
                                        Exact Amount
                                    </button>
                                </div>

                                <input type="number" x-ref="cashTenderInput" x-model.number="cashTendered"
                                    min="0" step="10"
                                    class="w-full text-base font-black text-gray-900 dark:text-gray-100 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-right py-2 focus:border-emerald-500 focus:ring-emerald-500 transition"
                                    placeholder="0" />

                                <!-- Quick Cash Denomination Buttons -->
                                <div class="grid grid-cols-5 gap-1.5 pt-1">
                                    <button type="button" @click="addCashTender(100)"
                                        class="py-1.5 text-center font-bold text-xs bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-100 shadow-sm active:scale-95 transition-all">+100</button>
                                    <button type="button" @click="addCashTender(500)"
                                        class="py-1.5 text-center font-bold text-xs bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-100 shadow-sm active:scale-95 transition-all">+500</button>
                                    <button type="button" @click="addCashTender(1000)"
                                        class="py-1.5 text-center font-bold text-xs bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-100 shadow-sm active:scale-95 transition-all">+1000</button>
                                    <button type="button" @click="addCashTender(2000)"
                                        class="py-1.5 text-center font-bold text-xs bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-100 shadow-sm active:scale-95 transition-all">+2000</button>
                                    <button type="button" @click="addCashTender(5000)"
                                        class="py-1.5 text-center font-bold text-xs bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-100 shadow-sm active:scale-95 transition-all">+5000</button>
                                </div>

                                <!-- Change Return Output -->
                                <div class="flex justify-between items-center pt-2 border-t dark:border-gray-700">
                                    <span class="text-xs font-black uppercase text-gray-500 dark:text-gray-400">Change Return (Wapsi):</span>
                                    <span class="text-lg font-black font-mono transition-colors duration-150"
                                        :class="changeReturn() >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400'">
                                        <span x-text="currency"></span> <span x-text="formatNumber(Math.max(0, changeReturn()))"></span>
                                    </span>
                                </div>
                            </div>

                            <!-- Invoice Note -->
                            <div>
                                <input type="text" x-model="invoice_note"
                                    class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:ring-emerald-500 transition"
                                    placeholder="Order Note / Remarks (Optional)..." />
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4 pt-3 border-t dark:border-gray-700 space-y-2">
                            <!-- Credit Limit Exceeded Notice -->
                            <template x-if="!isWalkIn() && isCreditLimitExceeded()">
                                <div class="p-2.5 rounded-xl bg-red-100 dark:bg-red-950/60 border border-red-300 dark:border-red-800 text-red-800 dark:text-red-200 text-xs font-bold flex items-center justify-between">
                                    <span>⚠️ Credit Limit Exceeded:</span>
                                    <span x-text="'+ ' + currency + ' ' + formatNumber(getCreditOverflow())"></span>
                                </div>
                            </template>

                            <!-- Primary: Pay & Thermal Print -->
                            <button type="button" @click="submitSale('thermal')" :disabled="isSubmitting || items.length === 0"
                                class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-xl shadow-lg shadow-emerald-600/30 font-black text-sm uppercase tracking-wider flex items-center justify-center gap-2 transition transform active:scale-98">
                                <template x-if="!isSubmitting">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                        <span>Pay & Print Thermal (F4)</span>
                                    </div>
                                </template>
                                <template x-if="isSubmitting">
                                    <span>Processing Sale...</span>
                                </template>
                            </button>

                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="submitSale('pdf')" :disabled="isSubmitting || items.length === 0"
                                    class="py-2.5 px-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-1.5 transition active:scale-95 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    <span>Pay & A4 PDF</span>
                                </button>

                                <button type="button" @click="submitSale('save_only')" :disabled="isSubmitting || items.length === 0"
                                    class="py-2.5 px-3 bg-gray-700 hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 disabled:opacity-50 text-white rounded-xl font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-1.5 transition active:scale-95 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    <span>Save Only</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- POST-SALE SUCCESS & PRINT MODAL -->
        <div x-show="showSuccessModal" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/75 backdrop-blur-sm p-4">
            <div x-show="showSuccessModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden transform transition-all">
                <div class="p-6 text-center">
                    <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                    </div>

                    <h3 class="text-xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-wide">
                        Sale Completed!
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                        x-text="'Invoice #' + (lastSale?.id || '') + ' — ' + (lastSale?.party_name || '')"></p>

                    <!-- Change Return Banner -->
                    <template x-if="lastChangeReturn > 0">
                        <div class="my-4 p-3 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 rounded-xl">
                            <div class="text-[11px] font-bold uppercase text-emerald-700 dark:text-emerald-300">Change to Return (Wapsi)</div>
                            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                                <span x-text="currency"></span> <span x-text="formatNumber(lastChangeReturn)"></span>
                            </div>
                        </div>
                    </template>

                    <!-- Summary Stats -->
                    <div class="grid grid-cols-2 gap-2 my-4 text-left text-xs bg-gray-50 dark:bg-gray-900/50 p-3 rounded-xl border dark:border-gray-700">
                        <div>
                            <span class="text-gray-400">Total Bill:</span>
                            <div class="font-black text-gray-800 dark:text-gray-200" x-text="currency + ' ' + formatNumber(lastSale?.total_amount || 0)"></div>
                        </div>
                        <div>
                            <span class="text-gray-400">Paid:</span>
                            <div class="font-black text-emerald-600" x-text="currency + ' ' + formatNumber(lastSale?.paid_amount || 0)"></div>
                        </div>
                    </div>

                    <!-- Print Action Buttons -->
                    <div class="space-y-2">
                        <button type="button" @click="printThermal(lastSale?.thermal_url)"
                            class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-md active:scale-98 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            <span>Print Thermal Receipt</span>
                        </button>

                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="printPdf(lastSale?.pdf_url)"
                                class="py-2 px-3 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 rounded-xl font-bold text-xs flex items-center justify-center gap-1 active:scale-95 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                <span>A4 Invoice</span>
                            </button>

                            <template x-if="lastSale?.whatsapp_url">
                                <a :href="lastSale.whatsapp_url" target="_blank"
                                    class="py-2 px-3 bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800 hover:bg-green-100 rounded-xl font-bold text-xs flex items-center justify-center gap-1 active:scale-95 transition-all">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                    <span>WhatsApp</span>
                                </a>
                            </template>
                        </div>

                        <!-- Big Reset Button -->
                        <button type="button" @click="startNextSale()"
                            class="w-full mt-2 py-3 px-4 bg-gray-900 hover:bg-black dark:bg-gray-700 dark:hover:bg-gray-600 text-white rounded-xl font-black text-xs uppercase tracking-widest transition-all active:scale-98 shadow">
                            Next Sale (Press Enter)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- QUICK ADD CUSTOMER MODAL -->
        <div x-show="showCustomerModal" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/75 backdrop-blur-sm p-4">
            <div x-show="showCustomerModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden p-6"
                @click.away="showCustomerModal = false">
                <div class="flex justify-between items-center pb-3 border-b dark:border-gray-700">
                    <h3 class="text-base font-black text-gray-900 dark:text-gray-100 uppercase">Add New Customer</h3>
                    <button type="button" @click="showCustomerModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-black">&times;</button>
                </div>

                <form @submit.prevent="saveQuickCustomer" class="mt-4 space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Customer Name *</label>
                        <input type="text" x-model="newCustomer.name" required
                            class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500 focus:border-emerald-500 transition"
                            placeholder="e.g. Haji Aslam Traders" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                        <input type="text" x-model="newCustomer.phone"
                            class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500 focus:border-emerald-500 transition"
                            placeholder="e.g. 03001234567" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Address / Market</label>
                        <input type="text" x-model="newCustomer.address"
                            class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500 focus:border-emerald-500 transition"
                            placeholder="e.g. Shop #12, Wholesale Market" />
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Opening Balance</label>
                            <input type="number" x-model.number="newCustomer.opening_balance" step="0.01" min="0"
                                class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500 focus:border-emerald-500 transition"
                                placeholder="0.00" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Balance Type</label>
                            <select x-model="newCustomer.balance_type"
                                class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500 focus:border-emerald-500 transition">
                                <option value="debit">Receivable (Lena ha)</option>
                                <option value="credit">Advance (Dena ha)</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-3 flex justify-end gap-2 border-t dark:border-gray-700">
                        <button type="button" @click="showCustomerModal = false"
                            class="px-4 py-2 text-xs font-bold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Cancel</button>
                        <button type="submit" :disabled="savingCustomer"
                            class="px-4 py-2 text-xs font-bold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white disabled:opacity-50 transition active:scale-95">
                            <span x-show="!savingCustomer">Save Customer</span>
                            <span x-show="savingCustomer">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- HELD CARTS MODAL -->
        <div x-show="showHeldModal" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/75 backdrop-blur-sm p-4">
            <div x-show="showHeldModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden p-6"
                @click.away="showHeldModal = false">
                <div class="flex justify-between items-center pb-3 border-b dark:border-gray-700">
                    <h3 class="text-base font-black text-gray-900 dark:text-gray-100 uppercase">Held Sales</h3>
                    <button type="button" @click="showHeldModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-black">&times;</button>
                </div>

                <div class="mt-4 space-y-2 max-h-72 overflow-y-auto">
                    <template x-for="(h, hIdx) in heldCarts" :key="hIdx">
                        <div class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-700 flex justify-between items-center">
                            <div>
                                <div class="font-bold text-xs text-gray-900 dark:text-gray-100" x-text="h.customer_name"></div>
                                <div class="text-[10px] text-gray-500 font-mono" x-text="h.time + ' — ' + h.items.length + ' item(s) — Total: ' + currency + ' ' + formatNumber(h.grand_total)"></div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="restoreHeldCart(hIdx)"
                                    class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold active:scale-95 transition-all">Recall</button>
                                <button type="button" @click="removeHeldCart(hIdx)"
                                    class="text-red-500 hover:text-red-700 text-sm font-black">&times;</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- HIDDEN PRINT IFRAME (For direct thermal printing) -->
        <iframe x-ref="printIframe" class="hidden"></iframe>

    </div>

    <!-- POS Alpine.js Application Engine -->
    <script>
        function posApp() {
            return {
                currency: '{{ $settings->currency_symbol ?? "Rs" }}',
                showCosts: localStorage.getItem('hide-sale-costs') === 'true' ? false : true,
                toggleCosts() {
                    this.showCosts = !this.showCosts;
                    localStorage.setItem('hide-sale-costs', !this.showCosts);
                },

                // Master Data (passed from Controller)
                customers: @json($customers),
                products: @json($products),
                categories: @json($categories),
                paymentAccounts: @json($paymentAccounts),

                // Selected State
                party_id: '{{ $settings->walk_in_customer_id ?? ($customers->first()->id ?? "") }}',
                customerSearch: '{{ $settings->walkInCustomer->name ?? ($customers->first()->name ?? "") }}',
                selectedCustomer: null,
                customerDropdownOpen: false,
                customerHighlightedIndex: -1,

                warehouse_id: '{{ $settings->shop_warehouse_id ?? ($warehouses->first()->id ?? "") }}',
                sale_date: '{{ date("Y-m-d") }}',
                walkInCustomerId: {{ $settings->walk_in_customer_id ?? "null" }},

                // Product Search / Barcode
                productSearch: '',
                productDropdownOpen: false,
                productHighlightedIndex: -1,
                selectedCategory: '',

                // Cart Items
                items: [],
                discount_amount: 0,
                cashTendered: 0,
                invoice_note: '',

                // Multi-payment Splits
                payments: [
                    {
                        account_id: '{{ $paymentAccounts->first()->id ?? "" }}',
                        amount: 0
                    }
                ],

                // UI & State Control
                isSubmitting: false,
                errorMessage: '',
                showSuccessModal: false,
                showCustomerModal: false,
                showHeldModal: false,
                savingCustomer: false,

                // Customer quick form
                newCustomer: {
                    name: '',
                    phone: '',
                    address: '',
                    opening_balance: 0,
                    balance_type: 'debit'
                },

                // Held Sales List
                heldCarts: [],

                // Last finalized sale
                lastSale: null,
                lastChangeReturn: 0,

                initPos() {
                    // LocalStorage offline master cache fallback
                    try {
                        if (this.customers && this.customers.length > 0) {
                            localStorage.setItem('invenco_pos_cached_customers', JSON.stringify(this.customers));
                        } else {
                            const cachedCust = localStorage.getItem('invenco_pos_cached_customers');
                            if (cachedCust) this.customers = JSON.parse(cachedCust);
                        }

                        if (this.products && this.products.length > 0) {
                            localStorage.setItem('invenco_pos_cached_products', JSON.stringify(this.products));
                        } else {
                            const cachedProd = localStorage.getItem('invenco_pos_cached_products');
                            if (cachedProd) this.products = JSON.parse(cachedProd);
                        }
                    } catch (e) {}

                    // Initialize active customer
                    this.selectedCustomer = this.customers.find(c => c.id == this.party_id) || null;
                    if (this.selectedCustomer) {
                        this.customerSearch = this.selectedCustomer.name;
                    }

                    // Load held sales from localStorage
                    try {
                        const saved = localStorage.getItem('invenco_pos_held_carts');
                        if (saved) {
                            this.heldCarts = JSON.parse(saved);
                        }
                    } catch(e) {
                        this.heldCarts = [];
                    }

                    // Focus barcode input on mount
                    this.$nextTick(() => {
                        this.focusBarcode();
                    });
                },

                isWalkIn() {
                    return this.walkInCustomerId && this.party_id == this.walkInCustomerId;
                },

                focusBarcode() {
                    if (this.$refs.barcodeInput) {
                        this.$refs.barcodeInput.focus();
                        this.$refs.barcodeInput.select();
                    }
                },

                focusCustomer() {
                    if (this.$refs.customerInput) {
                        this.$refs.customerInput.focus();
                        this.$refs.customerInput.select();
                    }
                },

                focusDiscount() {
                    if (this.$refs.discountInput) {
                        this.$refs.discountInput.focus();
                        this.$refs.discountInput.select();
                    }
                },

                focusCashTender() {
                    if (this.$refs.cashTenderInput) {
                        this.$refs.cashTenderInput.focus();
                        this.$refs.cashTenderInput.select();
                    }
                },

                // Global Shortcuts Handler
                handleGlobalKey(e) {
                    // Don't intercept if typing in a modal textarea or customer modal
                    if (this.showCustomerModal) return;

                    if (e.key === 'F1') {
                        e.preventDefault();
                        this.focusBarcode();
                    } else if (e.key === 'F2') {
                        e.preventDefault();
                        this.focusCustomer();
                    } else if (e.key === 'F4') {
                        e.preventDefault();
                        this.submitSale('thermal');
                    } else if (e.key === 'F7') {
                        e.preventDefault();
                        this.holdCurrentCart();
                    } else if (e.key === 'F8') {
                        e.preventDefault();
                        this.focusDiscount();
                    } else if (e.key === 'F9') {
                        e.preventDefault();
                        this.focusCashTender();
                    } else if (e.key === 'Escape') {
                        if (this.showSuccessModal) {
                            this.startNextSale();
                        } else if (this.showCustomerModal || this.showHeldModal) {
                            this.showCustomerModal = false;
                            this.showHeldModal = false;
                        } else {
                            this.productDropdownOpen = false;
                            this.customerDropdownOpen = false;
                        }
                    } else if (e.key === 'Enter' && this.showSuccessModal) {
                        e.preventDefault();
                        this.startNextSale();
                    }
                },

                // Customer Methods
                filteredCustomers() {
                    if (!this.customerSearch) return this.customers.slice(0, 15);
                    const q = this.customerSearch.toLowerCase().trim();
                    return this.customers.filter(c =>
                        c.name.toLowerCase().includes(q) || (c.phone && c.phone.includes(q))
                    ).slice(0, 15);
                },

                selectCustomer(c) {
                    this.party_id = c.id;
                    this.selectedCustomer = c;
                    this.customerSearch = c.name;
                    this.customerDropdownOpen = false;
                    this.customerHighlightedIndex = -1;
                    this.recalculate();
                    this.focusBarcode();

                    // Check last sold rate for any items in cart for this customer
                    this.items.forEach(item => this.fetchLastSoldPrice(item));
                },

                resetToWalkIn() {
                    if (this.walkInCustomerId) {
                        const walkIn = this.customers.find(c => c.id == this.walkInCustomerId);
                        if (walkIn) {
                            this.selectCustomer(walkIn);
                        }
                    }
                },

                handleCustomerKey(e) {
                    const list = this.filteredCustomers();
                    if (!list.length) return;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.customerHighlightedIndex = (this.customerHighlightedIndex + 1) % list.length;
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.customerHighlightedIndex = (this.customerHighlightedIndex - 1 + list.length) % list.length;
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (this.customerHighlightedIndex >= 0 && this.customerHighlightedIndex < list.length) {
                            this.selectCustomer(list[this.customerHighlightedIndex]);
                        } else if (list.length > 0) {
                            this.selectCustomer(list[0]);
                        }
                    }
                },

                openQuickCustomerModal() {
                    this.newCustomer = {
                        name: '',
                        phone: '',
                        address: '',
                        opening_balance: 0,
                        balance_type: 'debit'
                    };
                    this.showCustomerModal = true;
                },

                async saveQuickCustomer() {
                    this.savingCustomer = true;
                    try {
                        const res = await fetch('{{ route("pos.quick-add-customer") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.newCustomer)
                        });

                        const data = await res.json();
                        if (data.success && data.customer) {
                            this.customers.unshift(data.customer);
                            this.selectCustomer(data.customer);
                            this.showCustomerModal = false;
                        } else {
                            alert(data.message || 'Failed to save customer');
                        }
                    } catch (err) {
                        alert('Error saving customer: ' + err.message);
                    } finally {
                        this.savingCustomer = false;
                    }
                },

                // Product & Barcode Methods
                getProductWarehouseStock(product) {
                    if (!product.warehouse_stocks) return 0;
                    return (product.warehouse_stocks[this.warehouse_id] || 0);
                },

                getProductWarehouseCost(product) {
                    if (!product.warehouse_costs) return 0;
                    return (product.warehouse_costs[this.warehouse_id] || 0);
                },

                onWarehouseChange() {
                    // Update in-cart stocks and costs based on newly selected warehouse
                    this.items.forEach(item => {
                        const p = this.products.find(prod => prod.id == item.product_id);
                        if (p) {
                            item.stock = this.getProductWarehouseStock(p);
                            item.cost = this.getProductWarehouseCost(p);
                        }
                    });
                    this.recalculate();
                },

                filteredProducts() {
                    let list = this.products;
                    if (this.selectedCategory) {
                        list = list.filter(p => p.category_id == this.selectedCategory);
                    }
                    if (!this.productSearch) return list.slice(0, 15);

                    const q = this.productSearch.toLowerCase().trim();
                    return list.filter(p =>
                        p.name.toLowerCase().includes(q) ||
                        (p.sku && p.sku.toLowerCase().includes(q)) ||
                        (p.barcode && p.barcode.toLowerCase().includes(q))
                    ).slice(0, 15);
                },

                onProductSearchInput() {
                    this.productDropdownOpen = true;
                    this.productHighlightedIndex = 0;
                },

                handleProductInputKey(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.handleBarcodeScanOrSelect();
                    } else if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        const list = this.filteredProducts();
                        if (list.length) {
                            this.productHighlightedIndex = (this.productHighlightedIndex + 1) % list.length;
                        }
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        const list = this.filteredProducts();
                        if (list.length) {
                            this.productHighlightedIndex = (this.productHighlightedIndex - 1 + list.length) % list.length;
                        }
                    }
                },

                handleBarcodeScanOrSelect() {
                    const raw = (this.productSearch || '').trim();
                    if (!raw) return;

                    // 1. Exact Barcode Match
                    let found = this.products.find(p => p.barcode && p.barcode.toLowerCase() === raw.toLowerCase());

                    // 2. Exact SKU Match
                    if (!found) {
                        found = this.products.find(p => p.sku && p.sku.toLowerCase() === raw.toLowerCase());
                    }

                    // 3. Dropdown Highlighted Item
                    if (!found) {
                        const list = this.filteredProducts();
                        if (this.productHighlightedIndex >= 0 && this.productHighlightedIndex < list.length) {
                            found = list[this.productHighlightedIndex];
                        } else if (list.length > 0) {
                            found = list[0];
                        }
                    }

                    if (found) {
                        this.addProductToCart(found);
                    } else {
                        this.errorMessage = `Product not found for: "${raw}"`;
                        setTimeout(() => this.errorMessage = '', 4000);
                    }
                },

                addProductToCart(product) {
                    const existingIdx = this.items.findIndex(i => i.product_id == product.id && i.price == product.sale_price);

                    if (existingIdx >= 0) {
                        // Already in cart with same rate -> increment quantity
                        this.items[existingIdx].qty = Math.round((this.items[existingIdx].qty + 1) * 100) / 100;
                        this.items[existingIdx].justAdded = true;
                        setTimeout(() => {
                            if (this.items[existingIdx]) this.items[existingIdx].justAdded = false;
                        }, 800);
                    } else {
                        // Add new line item
                        const itemObj = {
                            product_id: product.id,
                            name: product.name,
                            sku: product.sku || '',
                            barcode: product.barcode || '',
                            unit_name: product.unit_name || 'pc',
                            stock: this.getProductWarehouseStock(product),
                            cost: this.getProductWarehouseCost(product),
                            qty: 1,
                            price: parseFloat(product.sale_price) || 0,
                            justAdded: true,
                            lastPrice: null
                        };
                        this.items.push(itemObj);
                        this.fetchLastSoldPrice(itemObj);
                        setTimeout(() => {
                            itemObj.justAdded = false;
                        }, 800);
                    }

                    this.productSearch = '';
                    this.productDropdownOpen = false;
                    this.productHighlightedIndex = -1;
                    this.recalculate();
                    this.focusBarcode();
                },

                fetchLastSoldPrice(item) {
                    if (!item || !item.product_id || !this.party_id) {
                        if (item) item.lastPrice = null;
                        return;
                    }
                    if (this.isWalkIn()) {
                        item.lastPrice = null;
                        return;
                    }

                    fetch(`{{ route('sales.customer-last-price') }}?party_id=${this.party_id}&product_id=${item.product_id}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data && data.found) {
                                item.lastPrice = data;
                            } else {
                                item.lastPrice = null;
                            }
                        })
                        .catch(() => {
                            item.lastPrice = null;
                        });
                },

                incrementQty(idx) {
                    this.items[idx].qty = Math.round((this.items[idx].qty + 1) * 100) / 100;
                    this.recalculate();
                },

                decrementQty(idx) {
                    if (this.items[idx].qty > 1) {
                        this.items[idx].qty = Math.round((this.items[idx].qty - 1) * 100) / 100;
                        this.recalculate();
                    } else {
                        this.removeItem(idx);
                    }
                },

                removeItem(idx) {
                    this.items.splice(idx, 1);
                    this.recalculate();
                    this.focusBarcode();
                },

                confirmClearCart() {
                    if (this.items.length === 0) return;
                    if (confirm('Are you sure you want to clear the entire cart?')) {
                        this.items = [];
                        this.discount_amount = 0;
                        this.cashTendered = 0;
                        this.invoice_note = '';
                        this.recalculate();
                        this.focusBarcode();
                    }
                },

                // Calculations
                totalQty() {
                    return this.items.reduce((sum, item) => sum + (parseFloat(item.qty) || 0), 0);
                },

                subtotal() {
                    return this.items.reduce((sum, item) => sum + Math.round((parseFloat(item.qty) || 0) * (parseFloat(item.price) || 0) * 100) / 100, 0);
                },

                grandTotal() {
                    return Math.max(0, this.subtotal() - (parseFloat(this.discount_amount) || 0));
                },

                totalPaid() {
                    return this.payments.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
                },

                changeReturn() {
                    return (parseFloat(this.cashTendered) || 0) - this.grandTotal();
                },

                getCustomerCreditLimit() {
                    return this.selectedCustomer && this.selectedCustomer.credit_limit ? parseFloat(this.selectedCustomer.credit_limit) : 0;
                },

                getCustomerCreditDays() {
                    return this.selectedCustomer && this.selectedCustomer.credit_days ? parseInt(this.selectedCustomer.credit_days) : 0;
                },

                isCustomerOverdue() {
                    if (this.isWalkIn()) return false;
                    return !!(this.selectedCustomer && this.selectedCustomer.has_overdue);
                },

                getProjectedBalance() {
                    const currentBal = this.selectedCustomer ? Math.max(0, parseFloat(this.selectedCustomer.current_balance || 0)) : 0;
                    const unpaid = Math.max(0, this.grandTotal() - this.totalPaid());
                    return currentBal + unpaid;
                },

                isCreditLimitExceeded() {
                    if (this.isWalkIn()) return false;
                    const limit = this.getCustomerCreditLimit();
                    if (limit <= 0) return false;
                    return this.getProjectedBalance() > limit;
                },

                getCreditOverflow() {
                    const limit = this.getCustomerCreditLimit();
                    if (limit <= 0) return 0;
                    return Math.max(0, this.getProjectedBalance() - limit);
                },

                recalculate() {
                    const total = this.grandTotal();
                    // Auto sync primary payment split amount to Grand Total if single payment split
                    if (this.payments.length === 1) {
                        this.payments[0].amount = total;
                    }
                    if (this.cashTendered === 0 || this.cashTendered < total) {
                        this.cashTendered = total;
                    }
                },

                applyQuickDiscount(amt) {
                    this.discount_amount = (parseFloat(this.discount_amount) || 0) + amt;
                    this.recalculate();
                },

                setExactCashTender() {
                    this.cashTendered = this.grandTotal();
                },

                addCashTender(amt) {
                    this.cashTendered = (parseFloat(this.cashTendered) || 0) + amt;
                },

                addPaymentSplit() {
                    this.payments.push({
                        account_id: '{{ $paymentAccounts->first()->id ?? "" }}',
                        amount: 0
                    });
                },

                removePaymentSplit(idx) {
                    this.payments.splice(idx, 1);
                    this.recalculate();
                },

                // Hold & Recall Sale
                holdCurrentCart() {
                    if (this.items.length === 0) return;

                    const cartSnapshot = {
                        customer_name: this.customerSearch,
                        party_id: this.party_id,
                        warehouse_id: this.warehouse_id,
                        items: JSON.parse(JSON.stringify(this.items)),
                        discount_amount: this.discount_amount,
                        grand_total: this.grandTotal(),
                        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                    };

                    this.heldCarts.push(cartSnapshot);
                    localStorage.setItem('invenco_pos_held_carts', JSON.stringify(this.heldCarts));

                    // Reset cart for next customer
                    this.items = [];
                    this.discount_amount = 0;
                    this.cashTendered = 0;
                    this.resetToWalkIn();
                    this.recalculate();
                    this.focusBarcode();
                },

                restoreHeldCart(idx) {
                    const h = this.heldCarts[idx];
                    if (!h) return;

                    this.party_id = h.party_id;
                    this.customerSearch = h.customer_name;
                    this.selectedCustomer = this.customers.find(c => c.id == h.party_id) || null;
                    this.warehouse_id = h.warehouse_id;
                    this.items = JSON.parse(JSON.stringify(h.items));
                    this.discount_amount = h.discount_amount;
                    this.recalculate();

                    this.heldCarts.splice(idx, 1);
                    localStorage.setItem('invenco_pos_held_carts', JSON.stringify(this.heldCarts));
                    this.showHeldModal = false;
                    this.focusBarcode();
                },

                removeHeldCart(idx) {
                    this.heldCarts.splice(idx, 1);
                    localStorage.setItem('invenco_pos_held_carts', JSON.stringify(this.heldCarts));
                },

                // Submit POS Sale
                async submitSale(mode = 'thermal') {
                    this.errorMessage = '';

                    if (this.items.length === 0) {
                        this.errorMessage = 'Please add at least one item to the cart before completing sale.';
                        return;
                    }

                    // Walk-in full payment check
                    if (this.isWalkIn()) {
                        const total = this.grandTotal();
                        const paid = this.totalPaid();
                        if (paid + 0.01 < total) {
                            this.errorMessage = 'Walk-In Customer sale must be paid in full — no credit allowed.';
                            return;
                        }
                    }

                    this.isSubmitting = true;

                    const payload = {
                        party_id: this.party_id,
                        warehouse_id: this.warehouse_id,
                        date: this.sale_date,
                        items: this.items.map(i => ({
                            product_id: i.product_id,
                            qty: i.qty,
                            price: i.price,
                            name: i.name
                        })),
                        payments: this.payments.filter(p => p.amount > 0),
                        discount_amount: this.discount_amount,
                        note: this.invoice_note
                    };

                    // Check if offline before attempting network request
                    if (!navigator.onLine || (window.OfflineSync && !window.OfflineSync.isOnline)) {
                        return this.submitOfflineSale(payload, mode);
                    }

                    try {
                        const res = await fetch('{{ route("pos.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await res.json();

                        if (!res.ok || !data.success) {
                            this.errorMessage = data.message || 'Error saving sale invoice. Please check entries.';
                            this.isSubmitting = false;
                            return;
                        }

                        // Sale successfully created!
                        this.lastSale = data;
                        this.lastChangeReturn = Math.max(0, this.changeReturn());
                        this.showSuccessModal = true;

                        // Auto trigger thermal print if requested
                        if (mode === 'thermal' && data.thermal_url) {
                            this.printThermal(data.thermal_url);
                        } else if (mode === 'pdf' && data.pdf_url) {
                            this.printPdf(data.pdf_url);
                        }

                    } catch (err) {
                        // Network error: automatically fallback to offline sale queue!
                        return this.submitOfflineSale(payload, mode);
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                submitOfflineSale(payload, mode) {
                    try {
                        const offlineSale = window.OfflineSync ? window.OfflineSync.saveOfflineSale(payload) : null;
                        
                        this.lastSale = {
                            success: true,
                            message: 'Sale saved in Local Offline Queue!',
                            is_offline: true,
                            sale: {
                                id: offlineSale ? offlineSale.offline_id : 'OFFLINE-' + Date.now(),
                                total_amount: this.grandTotal(),
                                date: this.sale_date,
                                party: { name: this.customerSearch },
                                items: JSON.parse(JSON.stringify(this.items))
                            }
                        };
                        this.lastChangeReturn = Math.max(0, this.changeReturn());
                        this.showSuccessModal = true;

                        if (mode === 'thermal') {
                            this.printOfflineThermal(this.lastSale.sale);
                        }
                    } catch (e) {
                        this.errorMessage = 'Failed to save offline: ' + e.message;
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                printOfflineThermal(sale) {
                    const printWindow = window.open('', '_blank', 'width=380,height=600');
                    if (!printWindow) return;

                    const itemsHtml = (sale.items || []).map(i => `
                        <tr>
                            <td style="padding: 2px 0;">${i.name || 'Item'}</td>
                            <td style="text-align: right; padding: 2px 0;">${i.qty} x ${i.price}</td>
                            <td style="text-align: right; padding: 2px 0;">${(i.qty * i.price).toFixed(2)}</td>
                        </tr>
                    `).join('');

                    printWindow.document.write(`
                        <html>
                        <head>
                            <title>Offline Receipt</title>
                            <style>
                                body { font-family: monospace; font-size: 12px; margin: 10px; }
                                .center { text-align: center; }
                                .bold { font-weight: bold; }
                                table { width: 100%; border-collapse: collapse; }
                                hr { border: none; border-top: 1px dashed #000; margin: 8px 0; }
                            </style>
                        </head>
                        <body>
                            <div class="center bold">${'{{ $settings->company_name ?? "Wholesale POS" }}'}</div>
                            <div class="center bold">OFFLINE RECEIPT</div>
                            <div class="center" style="font-size: 10px;">Tracking: ${sale.id}</div>
                            <div class="center">Date: ${sale.date}</div>
                            <hr/>
                            <div>Customer: ${sale.party?.name || 'Walk-In'}</div>
                            <hr/>
                            <table>${itemsHtml}</table>
                            <hr/>
                            <div style="display:flex; justify-content:space-between;" class="bold">
                                <span>TOTAL:</span>
                                <span>Rs ${parseFloat(sale.total_amount).toFixed(2)}</span>
                            </div>
                            <hr/>
                            <div class="center" style="font-size: 10px; margin-top: 10px;">
                                *** Saved Offline in Local Queue ***<br/>
                                Will auto-sync when internet reconnects.
                            </div>
                        </body>
                        </html>
                    `);
                    printWindow.document.close();
                    printWindow.focus();
                    setTimeout(() => {
                        printWindow.print();
                    }, 250);
                },

                printThermal(url) {
                    if (!url) return;
                    const w = window.open(url, '_blank', 'width=380,height=600,top=100,left=100');
                    if (w) {
                        w.focus();
                    }
                },

                printPdf(url) {
                    if (!url) return;
                    window.open(url, '_blank');
                },

                startNextSale() {
                    this.showSuccessModal = false;
                    this.items = [];
                    this.discount_amount = 0;
                    this.cashTendered = 0;
                    this.invoice_note = '';
                    this.resetToWalkIn();
                    this.recalculate();
                    this.$nextTick(() => {
                        this.focusBarcode();
                    });
                },

                formatNumber(val) {
                    return new Intl.NumberFormat('en-US', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    }).format(val || 0);
                }
            };
        }
    </script>
</x-app-layout>
