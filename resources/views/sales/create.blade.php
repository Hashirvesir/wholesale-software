<x-app-layout>
    <div class="py-12" x-data="saleForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-start mb-6 border-b dark:border-gray-700 pb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">
                                {{ __('Create Sale Invoice') }}
                            </h3>
                            <div class="text-[10px] text-gray-500 font-bold uppercase tracking-widest italic mt-1">
                                Keyboard Shortcuts: <span class="text-primary-600">Arrows</span> to Navigate | <span
                                    class="text-primary-600">Enter</span> on last field for New Row
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <button type="button" @click="toggleCosts()"
                                class="p-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition group flex items-center gap-2"
                                :title="showCosts ? 'Hide Costs' : 'Show Costs'">
                                <template x-if="showCosts">
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-rose-500" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </template>
                                <template x-if="!showCosts">
                                    <svg class="w-5 h-5 text-rose-500 font-bold" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.047m4.5 4.5a3 3 0 104.243 4.242M9.88 9.88l4.24 4.24m1.757-4.305a3 3 0 00-4.305-4.305M20.375 18.825L3.625 2M16.5 6.5l3.5 3.5" />
                                    </svg>
                                </template>
                                <span class="text-[10px] font-black uppercase tracking-tighter text-gray-500"
                                    x-text="showCosts ? 'HIDE COST' : 'SHOW COST'"></span>
                            </button>
                        </div>
                    </div>

                    <form action="{{ route('sales.store') }}" method="POST" @submit.prevent="submitForm">
                        @csrf
                        @if (isset($quotation) && $quotation)
                            <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">
                            <div class="mb-4 p-4 bg-emerald-50 dark:bg-emerald-950/40 border-l-4 border-emerald-500 text-emerald-800 dark:text-emerald-200 flex justify-between items-center rounded-r-lg">
                                <div>
                                    <span class="font-bold text-sm">Converting Quotation #{{ $quotation->id }} to Sale Invoice</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block">Items and prices have been pre-filled from the quotation.</span>
                                </div>
                                <span class="px-2.5 py-1 text-xs font-black uppercase rounded bg-emerald-200 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-100">Quotation #{{ $quotation->id }}</span>
                            </div>
                        @endif

                        <!-- Error Display -->
                        @if ($errors->any())
                            <div
                                class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-400">
                                <ul class="list-disc list-inside text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div x-show="formError" x-cloak
                            class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-400 flex justify-between items-start rounded-r-lg">
                            <p class="text-sm font-bold" x-text="formError"></p>
                            <button type="button" @click="formError = ''" class="ml-4 text-red-400 hover:text-red-600 font-black text-lg leading-none">&times;</button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <!-- Customer -->
                            <div>
                                <x-input-label for="party_id" :value="__('Customer')" />
                                <div class="relative" @click.away="customerOpen = false">
                                    <input type="text" x-model="customerSearch" @focus="customerOpen = true"
                                        @input="customerOpen = true" @keydown="handleCustomerKey($event)"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition"
                                        placeholder="Search customer..." autocomplete="off" required>

                                    <input type="hidden" name="party_id" :value="party_id">

                                    <div x-show="customerOpen && getFilteredCustomers().length > 0"
                                        class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-xs ring-1 ring-black ring-opacity-5 overflow-auto border dark:border-gray-700">
                                        <template x-for="(customer, cIdx) in getFilteredCustomers()"
                                            :key="customer.id">
                                            <div @click="selectCustomer(customer)"
                                                class="cursor-pointer select-none relative py-2 pl-3 pr-9 border-b last:border-0 dark:border-gray-700 hover:bg-primary-50 dark:hover:bg-primary-900 transition"
                                                :class="customerHighlightedIndex === cIdx ?
                                                    'bg-primary-100 dark:bg-primary-900' : ''">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <span class="block truncate uppercase font-bold"
                                                            x-text="customer.name"></span>
                                                        <span class="text-gray-500 dark:text-[10px]"
                                                            x-text="customer.phone || 'No Phone'"></span>
                                                    </div>
                                                    <span class="font-bold whitespace-nowrap ml-2"
                                                        :class="customer.current_balance >= 0 ? 'text-red-500' :
                                                            'text-green-500'">
                                                        <span x-text="currency"></span> <span
                                                            x-text="formatNumber(Math.abs(customer.current_balance))"></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <template x-if="party_id">
                                        <div class="mt-1.5 space-y-1">
                                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                                <template x-if="getSelectedCustomer()">
                                                    <span class="px-2 py-0.5 rounded text-[11px] font-black uppercase tracking-wider"
                                                        :class="(getSelectedCustomer()?.price_tier || 'wholesale') === 'retail' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : ((getSelectedCustomer()?.price_tier || 'wholesale') === 'dealer' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300')">
                                                        <span x-text="(getSelectedCustomer()?.price_tier || 'wholesale') + ' rate'"></span>
                                                    </span>
                                                </template>
                                                <span class="font-bold" :class="getCustomerBalance() >= 0 ? 'text-red-500' : 'text-green-500'">
                                                    Prev. Balance: <span x-text="currency"></span> <span
                                                        x-text="formatNumber(Math.abs(getCustomerBalance()))"></span>
                                                    <span
                                                        x-text="getCustomerBalance() >= 0 ? '(Receivable)' : '(Payable)'"></span>
                                                </span>
                                                <template x-if="!isWalkIn() && getCustomerCreditLimit() > 0">
                                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                                        Limit: <span x-text="currency"></span> <span x-text="formatNumber(getCustomerCreditLimit())"></span>
                                                    </span>
                                                </template>
                                                <template x-if="!isWalkIn() && getCustomerCreditDays() > 0">
                                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                                        Terms: <span x-text="getCustomerCreditDays() + ' Days'"></span>
                                                    </span>
                                                </template>
                                            </div>

                                            <!-- Overdue Notice Banner -->
                                            <template x-if="!isWalkIn() && isCustomerOverdue()">
                                                <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-xs flex items-center gap-2">
                                                    <svg class="w-4 h-4 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                    </svg>
                                                    <span><strong>Overdue Notice:</strong> Customer has unpaid invoices past the <span x-text="getCustomerCreditDays()"></span>-day due period!</span>
                                                </div>
                                            </template>

                                            <!-- Credit Limit Exceeded Alert -->
                                            <template x-if="!isWalkIn() && isCreditLimitExceeded()">
                                                <div class="p-2 rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-300 dark:border-red-800 text-red-800 dark:text-red-200 text-xs flex items-center gap-2">
                                                    <svg class="w-4 h-4 shrink-0 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span><strong>Credit Limit Alert:</strong> This invoice exceeds customer's credit limit of <span x-text="currency + ' ' + formatNumber(getCustomerCreditLimit())"></span> by <span class="font-bold underline" x-text="currency + ' ' + formatNumber(getCreditOverflow())"></span>!</span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Warehouse -->
                            <div>
                                <x-input-label for="warehouse_id" :value="__('Warehouse (Stock Source)')" />
                                <select id="warehouse_id" name="warehouse_id" x-model="warehouse_id"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition"
                                    required>
                                    <option value="">-- Select Warehouse --</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                                <template x-if="isWalkIn()">
                                    <p class="mt-1 text-[10px] font-bold text-primary-500 uppercase tracking-widest">
                                        Defaults to Shop for Walk-In Customer — change if needed</p>
                                </template>
                            </div>

                            <!-- Date -->
                            <div>
                                <x-input-label for="date" :value="__('Date')" />
                                <x-text-input id="date" class="block mt-1 w-full" type="date" name="date"
                                    :value="date('Y-m-d')" required />
                            </div>
                        </div>

                        <!-- Desktop Items Table -->
                        <div class="hidden md:block mb-6 overflow-x-auto pb-48">
                            <table
                                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border dark:border-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/3">
                                            Product (Name/SKU)</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Stock</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Avg Cost</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Qty</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Price (PKR)</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Total</th>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-10">
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition duration-75">
                                            <td class="px-2 py-3 relative" @click.away="item.open = false">
                                                <input type="text" x-model="item.search" @focus="item.open = true"
                                                    @input="item.open = true" @keydown="handleKey($event, index, 0)"
                                                    :id="'row-' + index + '-col-0'"
                                                    class="block w-full text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 keyboard-nav"
                                                    :class="item.error ? 'border-red-500 ring-red-500' : ''"
                                                    placeholder="Search product..." autocomplete="off" required>

                                                <input type="hidden" :name="'items[' + index + '][product_id]'"
                                                    :value="item.product_id">

                                                <div x-show="item.open && getFilteredProducts(item.search).length > 0"
                                                    class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-40 rounded-md py-1 text-xs ring-1 ring-black ring-opacity-5 overflow-auto border dark:border-gray-700">
                                                    <template
                                                        x-for="(product, pIdx) in getFilteredProducts(item.search)"
                                                        :key="product.id">
                                                        <div @click="selectProduct(index, product)"
                                                            class="cursor-pointer select-none relative py-2 pl-3 pr-9 border-b last:border-0 dark:border-gray-700 hover:bg-primary-50 dark:hover:bg-primary-900 transition"
                                                            :class="item.highlightedIndex === pIdx ?
                                                                'bg-primary-100 dark:bg-primary-900' : ''">
                                                            <div class="flex items-center">
                                                                <div>
                                                                    <span class="block truncate uppercase font-bold"
                                                                        x-text="product.name"></span>
                                                                    <div class="flex items-center gap-2 mt-0.5">
                                                                        <span
                                                                            class="text-gray-500 dark:text-gray-400 text-[9px] font-mono tracking-tighter"
                                                                            x-text="'SKU: ' + product.sku"></span>
                                                                        <template x-if="product.barcode">
                                                                            <span
                                                                                class="text-gray-500 dark:text-gray-400 text-[9px] font-mono tracking-tighter"
                                                                                x-text="'| BAR: ' + product.barcode"></span>
                                                                        </template>
                                                                    </div>
                                                                </div>
                                                                <span
                                                                    class="ml-auto font-black text-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded text-[10px]"
                                                                    x-text="'Stock: ' + (product.warehouse_stocks && product.warehouse_stocks[warehouse_id] ? product.warehouse_stocks[warehouse_id] : 0)"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </td>
                                            <td
                                                class="px-2 py-3 text-xs font-bold"
                                                :class="isOverstock(index) ? 'text-amber-500' : 'text-indigo-600 dark:text-indigo-400'">
                                                <span x-text="getItemStock(index)"></span>
                                                <span x-show="isOverstock(index)" x-cloak
                                                    class="block text-[9px] font-black uppercase tracking-tighter">Backorder</span>
                                            </td>
                                            <td
                                                class="px-2 py-3 text-xs font-black text-rose-500 dark:text-rose-400 uppercase italic">
                                                <span x-show="showCosts"
                                                    x-text="currency + ' ' + getItemCost(index)"></span>
                                                <span x-show="!showCosts" x-cloak>***</span>
                                            </td>
                                            <td class="px-2 py-3">
                                                <input type="number" x-model.number="item.qty"
                                                    :name="'items[' + index + '][qty]'"
                                                    @keydown="handleKey($event, index, 1)"
                                                    :id="'row-' + index + '-col-1'"
                                                    class="block w-full text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 keyboard-nav"
                                                    required step="0.01" min="0.01"
                                                    @input="validateNumber(item, 'qty')">
                                            </td>
                                            <td class="px-2 py-3">
                                                <input type="number" x-model.number="item.price"
                                                    :name="'items[' + index + '][price]'"
                                                    @keydown="handleKey($event, index, 2)"
                                                    :id="'row-' + index + '-col-2'"
                                                    class="block w-full text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 keyboard-nav"
                                                    required step="0.01" min="0"
                                                    @input="validateNumber(item, 'price')">
                                                <template x-if="item.lastPrice && item.lastPrice.found">
                                                    <div class="mt-1 flex items-center">
                                                        <button type="button" @click="applyLastPrice(index)"
                                                            class="inline-flex items-center gap-1 text-[10px] font-black text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/60 hover:bg-primary-100 dark:hover:bg-primary-900/60 px-1.5 py-0.5 rounded transition shadow-xs"
                                                            :title="'Last sold: ' + currency + ' ' + formatNumber(item.lastPrice.price) + ' on ' + item.lastPrice.date + ' (Inv #' + item.lastPrice.invoice_formatted + ')'">
                                                            <span>Last: <span x-text="currency + ' ' + formatNumber(item.lastPrice.price)"></span></span>
                                                            <span class="underline">Use</span>
                                                        </button>
                                                    </div>
                                                </template>
                                            </td>
                                            <td
                                                class="px-4 py-3 text-xs font-bold text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                                <span class="text-[10px] text-gray-400" x-text="currency"></span>
                                                <span x-text="formatNumber(item.qty * item.price)"></span>
                                            </td>
                                            <td class="px-2 py-3 text-center">
                                                <button type="button" @click="removeItem(index)"
                                                    class="text-red-500 hover:text-red-700 transition transform hover:scale-110">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <div class="mt-4">
                                <button type="button" @click="addItem"
                                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-primary-600 text-primary-600 rounded-xl hover:bg-primary-600 hover:text-white transition shadow-sm text-xs font-black uppercase tracking-widest">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Add New Product
                                </button>
                            </div>
                        </div>

                        <!-- Mobile Items Cards View (Touch & Phone Friendly) -->
                        <div class="block md:hidden space-y-3 mb-6">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="bg-gray-50 dark:bg-gray-900/60 p-3.5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-3 relative">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-black uppercase text-primary-600 dark:text-primary-400 font-mono tracking-wider">
                                            Item #<span x-text="index + 1"></span>
                                        </span>
                                        <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                            class="p-1 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>

                                    <!-- Product Search -->
                                    <div class="relative" @click.away="item.open = false">
                                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-500 mb-1">Product *</label>
                                        <input type="text" x-model="item.search" @focus="item.open = true"
                                            @input="item.open = true"
                                            class="block w-full text-xs font-bold border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-xl shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                            placeholder="Search product..." autocomplete="off">

                                        <div x-show="item.open && getFilteredProducts(item.search).length > 0"
                                            class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-2xl max-h-48 rounded-xl py-1 text-xs ring-1 ring-black ring-opacity-5 overflow-auto border dark:border-gray-700">
                                            <template x-for="(product, pIdx) in getFilteredProducts(item.search)" :key="product.id">
                                                <div @click="selectProduct(index, product)"
                                                    class="cursor-pointer py-2 px-3 border-b last:border-0 dark:border-gray-700 hover:bg-primary-50 dark:hover:bg-primary-950/40 transition">
                                                    <div class="flex items-center justify-between">
                                                        <span class="font-bold text-gray-900 dark:text-gray-100" x-text="product.name"></span>
                                                        <span class="text-[10px] font-mono text-indigo-500" x-text="'Stock: ' + (product.warehouse_stocks && product.warehouse_stocks[warehouse_id] ? product.warehouse_stocks[warehouse_id] : 0)"></span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Stock & Cost Preview -->
                                    <div class="flex items-center justify-between text-xs px-2.5 py-1 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700/60">
                                        <span class="text-gray-400">Stock: <strong class="text-indigo-600 dark:text-indigo-400" x-text="getItemStock(index)"></strong></span>
                                        <span x-show="showCosts" class="text-rose-500 font-mono text-[11px]">Cost: <span x-text="currency + ' ' + getItemCost(index)"></span></span>
                                    </div>

                                    <!-- Qty & Price -->
                                    <div class="grid grid-cols-3 gap-2">
                                        <div>
                                            <label class="block text-[10px] font-black uppercase text-gray-400 tracking-wider mb-1">Qty</label>
                                            <input type="number" x-model.number="item.qty" inputmode="decimal"
                                                class="block w-full text-xs font-bold border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-xl"
                                                step="0.01" min="0.01" required>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black uppercase text-gray-400 tracking-wider mb-1">Price</label>
                                            <input type="number" x-model.number="item.price" inputmode="decimal"
                                                class="block w-full text-xs font-bold border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-xl"
                                                step="0.01" min="0" required>
                                        </div>
                                        <div class="text-right">
                                            <span class="block text-[10px] font-black uppercase text-gray-400 tracking-wider mb-1">Total</span>
                                            <span class="text-xs font-black font-mono text-gray-900 dark:text-gray-100 block pt-2"
                                                x-text="currency + ' ' + formatNumber(item.qty * item.price)"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <button type="button" @click="addItem"
                                class="w-full py-2.5 bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 border border-primary-300 dark:border-primary-800 rounded-xl text-xs font-black uppercase tracking-wider flex items-center justify-center gap-1.5 shadow-sm active:scale-95 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                + Add Another Product
                            </button>
                        </div>

                        <!-- Notes, Payment and Grand Total -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-t dark:border-gray-700 pt-6">
                            <div class="space-y-6">
                                <div>
                                    <x-input-label for="note" :value="__('Invoice Note (Optional)')" />
                                    <textarea id="note" name="note" rows="3"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150"></textarea>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between">
                                        <x-input-label
                                            :value="__('Payment (Optional, split across accounts)')" />
                                    </div>
                                    <template x-if="isWalkIn()">
                                        <p class="mt-1 text-[10px] font-bold text-primary-500 uppercase tracking-widest">
                                            Walk-In Customer: total payment must cover the full amount, no credit allowed</p>
                                    </template>
                                    <template x-for="(payment, pIdx) in payments" :key="pIdx">
                                        <div class="flex gap-2 mt-2 items-center">
                                            <select :name="'payments[' + pIdx + '][account_id]'"
                                                x-model="payment.account_id"
                                                class="block w-1/2 text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition"
                                                :required="payment.amount > 0">
                                                <option value="">-- Account --</option>
                                                @foreach ($paymentAccounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                            <input type="number" :name="'payments[' + pIdx + '][amount]'"
                                                x-model.number="payment.amount" step="0.01" min="0"
                                                placeholder="0.00"
                                                class="block w-1/2 text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition">
                                            <button type="button" @click="removePayment(pIdx)"
                                                x-show="payments.length > 1"
                                                class="text-red-500 hover:text-red-700 transition transform hover:scale-110 shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                    <button type="button" @click="addPayment"
                                        class="mt-2 inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-800 border border-primary-600 text-primary-600 rounded-lg hover:bg-primary-600 hover:text-white transition shadow-sm text-[10px] font-black uppercase tracking-widest">
                                        + Add Payment Method
                                    </button>
                                </div>
                                <div>
                                    <x-input-label for="discount_amount" :value="__('Discount (Flat)')" />
                                    <x-text-input id="discount_amount" x-model.number="discount_amount"
                                        class="block mt-1 w-full text-red-600 font-bold" type="number"
                                        step="0.01" min="0" name="discount_amount" placeholder="0.00" />
                                </div>
                            </div>
                            <div
                                class="bg-primary-50 dark:bg-primary-900/30 p-8 rounded-2xl border-2 border-primary-100 dark:border-primary-800/50 flex flex-col justify-center shadow-inner relative overflow-hidden">
                                <div class="absolute -right-10 -bottom-10 opacity-5 transform rotate-12">
                                    <svg class="w-48 h-48" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                                        <path fill-rule="evenodd"
                                            d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div
                                    class="flex justify-between items-center mb-2 text-sm border-b border-primary-100 dark:border-primary-800/50 pb-2">
                                    <span class="text-gray-600 dark:text-gray-400 font-bold">Total Qty:</span>
                                    <span class="text-gray-700 dark:text-gray-300 font-bold"
                                        x-text="formatNumber(calculateTotalQty())"></span>
                                </div>
                                <div class="flex justify-between items-center mb-2 text-sm border-b border-primary-100 dark:border-primary-800/50 pb-2"
                                    x-show="party_id">
                                    <span class="text-gray-600 dark:text-gray-400 font-bold">Prev. Balance:</span>
                                    <span :class="getCustomerBalance() >= 0 ? 'text-red-500' : 'text-green-500'"
                                        class="font-bold"
                                        x-text="currency + ' ' + formatNumber(Math.abs(getCustomerBalance()))"></span>
                                </div>
                                <div class="flex justify-between items-center mb-2">
                                    <span
                                        class="text-gray-600 dark:text-gray-400 font-black uppercase tracking-widest text-xs opacity-70">Subtotal:</span>
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200"
                                        x-text="currency + ' ' + formatNumber(calculateSubtotal())"></span>
                                </div>
                                <div class="flex justify-between items-center mb-4">
                                    <span
                                        class="text-red-500 dark:text-rose-400 font-black uppercase tracking-widest text-xs opacity-70">Discount:</span>
                                    <span class="text-sm font-bold text-red-500"
                                        x-text="'- ' + currency + ' ' + formatNumber(discount_amount || 0)"></span>
                                </div>
                                <div
                                    class="flex justify-between items-center mb-4 border-t border-primary-100 dark:border-primary-800/50 pt-2">
                                    <span
                                        class="text-gray-600 dark:text-gray-400 font-black uppercase tracking-widest text-xs">Grand
                                        Total:</span>
                                    <span class="text-2xl font-black text-gray-900 dark:text-white font-mono"
                                        x-text="currency + ' ' + formatNumber(calculateGrandTotal())"></span>
                                </div>
                                <div class="flex justify-between items-center mb-4 text-sm font-bold border-t border-primary-100 dark:border-primary-800/50 pt-2"
                                    x-show="party_id">
                                    <span class="text-gray-600 dark:text-gray-400">Net Total (Inc. Prev):</span>
                                    <span class="text-indigo-600 dark:text-indigo-400"
                                        x-text="currency + ' ' + formatNumber(calculateGrandTotal() + getCustomerBalance() - paidAmount())"></span>
                                </div>
                                <div class="flex justify-between items-center mb-4 text-sm">
                                    <span class="text-gray-600 dark:text-gray-400 font-bold">Paid:</span>
                                    <span class="text-green-600 dark:text-green-400 font-bold"
                                        x-text="currency + ' ' + formatNumber(paidAmount())"></span>
                                </div>
                                <div class="flex justify-between items-center text-2xl font-black border-t-2 border-primary-100 dark:border-primary-800/50 pt-6"
                                    :class="(calculateGrandTotal() - paidAmount()) > 0 ?
                                        'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                    <span class="tracking-tighter text-lg">BALANCE DUE:</span>
                                    <span
                                        x-text="currency + ' ' + formatNumber(calculateGrandTotal() - paidAmount())"></span>
                                </div>
                                <template x-if="!isWalkIn() && isCreditLimitExceeded()">
                                    <div class="mt-3 p-2.5 rounded-xl bg-red-100 dark:bg-red-950/60 border border-red-300 dark:border-red-800 text-red-800 dark:text-red-200 text-xs font-bold flex items-center justify-between">
                                        <span>Credit Limit Exceeded:</span>
                                        <span x-text="'+ ' + currency + ' ' + formatNumber(getCreditOverflow())"></span>
                                    </div>
                                </template>
                                <div
                                    class="mt-4 pt-2 border-t border-primary-100 dark:border-primary-800/50 text-center">
                                    <span
                                        class="text-[10px] font-black uppercase text-primary-400 tracking-widest">Total
                                        Items Quantity: </span>
                                    <span class="text-sm font-bold text-primary-600 dark:text-primary-400"
                                        x-text="formatNumber(calculateTotalQty())"></span>
                                </div>
                            </div>
                        </div>
                </div>

                <div class="flex items-center justify-end mt-8 sm:mt-12 border-t dark:border-gray-700 pt-6 sm:pt-8 pb-20 md:pb-0">
                    <a href="{{ route('sales.index') }}"
                        class="mr-6 text-sm text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 font-bold transition italic tracking-widest uppercase">Discard</a>
                    <x-primary-button class="!px-12 !py-4 !text-sm !rounded-xl !shadow-2xl" ::disabled="isSubmitting">
                        <span x-show="!isSubmitting">{{ __('Finalize Sale Invoice') }}</span>
                        <span x-show="isSubmitting">{{ __('Processing...') }}</span>
                    </x-primary-button>
                </div>

                <!-- Mobile Sticky Bottom Action Bar -->
                <div class="md:hidden fixed bottom-16 inset-x-0 z-40 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md border-t border-gray-200 dark:border-gray-700 px-4 py-2.5 shadow-2xl flex items-center justify-between">
                    <div>
                        <span class="block text-[10px] font-black uppercase tracking-wider text-gray-400">Total Bill</span>
                        <span class="text-base font-black font-mono text-primary-600 dark:text-primary-400"
                            x-text="currency + ' ' + formatNumber(calculateGrandTotal())"></span>
                    </div>
                    <x-primary-button class="!px-6 !py-2.5 !text-xs !rounded-xl !shadow-lg" ::disabled="isSubmitting">
                        <span x-show="!isSubmitting">{{ __('Finalize Sale') }}</span>
                        <span x-show="isSubmitting">{{ __('Saving...') }}</span>
                    </x-primary-button>
                </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    @php
        $initialItems = [
            ['product_id' => '', 'search' => '', 'open' => false, 'qty' => 1, 'price' => 0, 'error' => false, 'highlightedIndex' => -1, 'lastPrice' => null]
        ];
        if (isset($quotation) && $quotation && $quotation->items->count() > 0) {
            $initialItems = $quotation->items->map(function ($it) {
                return [
                    'product_id' => $it->product_id,
                    'search' => ($it->product->name ?? '') . ' (' . ($it->product->sku ?? '') . ')',
                    'open' => false,
                    'qty' => (float) $it->qty,
                    'price' => (float) $it->price,
                    'error' => false,
                    'highlightedIndex' => -1,
                    'lastPrice' => null,
                ];
            })->values()->all();
        }
    @endphp

    <script>
        function saleForm() {
            return {
                currency: '{{ $settings->currency_symbol }}',
                showCosts: localStorage.getItem('hide-sale-costs') === 'true' ? false : true,
                toggleCosts() {
                    this.showCosts = !this.showCosts;
                    localStorage.setItem('hide-sale-costs', !this.showCosts);
                },
                isSubmitting: false,
                formError: '',
                party_id: '{{ isset($quotation) && $quotation ? $quotation->party_id : ($settings->walk_in_customer_id ?? '') }}',
                customerSearch: '{{ isset($quotation) && $quotation ? ($quotation->party->name ?? '') : ($settings->walkInCustomer->name ?? '') }}',
                customerOpen: false,
                customerHighlightedIndex: -1,
                warehouse_id: '{{ isset($quotation) && $quotation ? $quotation->warehouse_id : ($settings->shop_warehouse_id ?? '') }}',
                walkInCustomerId: {{ $settings->walk_in_customer_id ?? 'null' }},
                shopWarehouseId: {{ $settings->shop_warehouse_id ?? 'null' }},
                isWalkIn() {
                    return this.walkInCustomerId && this.party_id == this.walkInCustomerId;
                },
                payments: [{
                    account_id: '',
                    amount: 0
                }],
                discount_amount: {{ isset($quotation) && $quotation ? (float)$quotation->discount_amount : 0 }},
                items: @json($initialItems),
                products: @json($products),
                customers: @json($customers),

                addItem() {
                    this.items.push({
                        product_id: '',
                        search: '',
                        open: false,
                        qty: 1,
                        price: 0,
                        error: false,
                        highlightedIndex: -1,
                        lastPrice: null
                    });
                    this.$nextTick(() => {
                        const nextRowIdx = this.items.length - 1;
                        document.getElementById('row-' + nextRowIdx + '-col-0').focus();
                    });
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },

                paidAmount() {
                    return this.payments.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
                },

                addPayment() {
                    this.payments.push({
                        account_id: '',
                        amount: 0
                    });
                },

                removePayment(index) {
                    if (this.payments.length > 1) {
                        this.payments.splice(index, 1);
                    }
                },

                getFilteredProducts(search) {
                    if (!search || search === '') return [];
                    const s = search.toLowerCase();
                    return this.products.filter(p => {
                        return p.name.toLowerCase().includes(s) ||
                            p.sku.toLowerCase().includes(s) ||
                            (p.barcode && p.barcode.toLowerCase().includes(s));
                    }).slice(0, 10);
                },

                getProductPrice(product, tier) {
                    if (!tier) {
                        const cust = this.customers.find(c => c.id == this.party_id);
                        tier = cust?.price_tier || 'wholesale';
                    }
                    if (tier === 'retail' && product.retail_price && parseFloat(product.retail_price) > 0) {
                        return parseFloat(product.retail_price);
                    }
                    if (tier === 'dealer' && product.dealer_price && parseFloat(product.dealer_price) > 0) {
                        return parseFloat(product.dealer_price);
                    }
                    if (tier === 'wholesale' && product.wholesale_price && parseFloat(product.wholesale_price) > 0) {
                        return parseFloat(product.wholesale_price);
                    }
                    return parseFloat(product.sale_price) || 0;
                },

                selectProduct(index, product) {
                    const price = this.getProductPrice(product);
                    // Check if SAME product with SAME price already exists
                    const exists = this.items.some((item, i) => i !== index && item.product_id === product.id && item
                        .price === price);
                    if (exists) {
                        alert('Product "' + product.name + '" with price ' + price +
                            ' is already added!');
                        this.items[index].search = '';
                        this.items[index].product_id = '';
                        return;
                    }

                    this.items[index].product_id = product.id;
                    this.items[index].search = product.name + ' (' + product.sku + ')';
                    this.items[index].price = price;
                    this.items[index].open = false;
                    this.items[index].error = false;
                    this.items[index].highlightedIndex = -1;

                    this.fetchLastSoldPrice(index);

                    this.$nextTick(() => {
                        document.getElementById('row-' + index + '-col-1').focus();
                    });
                },

                handleKey(e, index, col) {
                    const maxCol = 2;
                    const maxRow = this.items.length - 1;

                    if (col === 0 && this.items[index].open) {
                        const filtered = this.getFilteredProducts(this.items[index].search);
                        if (filtered.length > 0) {
                            if (e.key === 'ArrowDown') {
                                e.preventDefault();
                                this.items[index].highlightedIndex = (this.items[index].highlightedIndex + 1) % filtered
                                    .length;
                                return;
                            } else if (e.key === 'ArrowUp') {
                                e.preventDefault();
                                this.items[index].highlightedIndex = (this.items[index].highlightedIndex - 1 + filtered
                                    .length) % filtered.length;
                                return;
                            } else if (e.key === 'Enter') {
                                e.preventDefault();
                                if (this.items[index].highlightedIndex >= 0) {
                                    this.selectProduct(index, filtered[this.items[index].highlightedIndex]);
                                } else {
                                    this.selectProduct(index, filtered[0]);
                                }
                                return;
                            } else if (e.key === 'Escape') {
                                this.items[index].open = false;
                                return;
                            }
                        }
                    }

                    if (e.key === 'ArrowRight') {
                        if (col < maxCol) {
                            e.preventDefault();
                            document.getElementById('row-' + index + '-col-' + (col + 1)).focus();
                        }
                    } else if (e.key === 'ArrowLeft') {
                        if (col > 0) {
                            e.preventDefault();
                            document.getElementById('row-' + index + '-col-' + (col - 1)).focus();
                        }
                    } else if (e.key === 'ArrowDown') {
                        if (index < maxRow) {
                            e.preventDefault();
                            document.getElementById('row-' + (index + 1) + '-col-' + col).focus();
                        }
                    } else if (e.key === 'ArrowUp') {
                        if (index > 0) {
                            e.preventDefault();
                            document.getElementById('row-' + (index - 1) + '-col-' + col).focus();
                        }
                    } else if (e.key === 'Enter') {
                        if (col === maxCol) {
                            e.preventDefault();
                            if (index === maxRow) {
                                this.addItem();
                            } else {
                                document.getElementById('row-' + (index + 1) + '-col-0').focus();
                            }
                        }
                    }
                },

                validateNumber(item, field) {
                    if (item[field] < 0) {
                        alert('Negative values are not allowed!');
                        item[field] = field === 'qty' ? 1 : 0;
                    }
                },

                calculateSubtotal() {
                    return this.items.reduce((sum, item) => sum + (item.qty * item.price), 0);
                },

                calculateGrandTotal() {
                    return Math.max(0, this.calculateSubtotal() - (this.discount_amount || 0));
                },

                calculateTotalQty() {
                    return this.items.reduce((sum, item) => sum + (item.qty || 0), 0);
                },

                getCustomerBalance() {
                    if (!this.party_id) return 0;
                    const customer = this.customers.find(c => c.id == this.party_id);
                    return customer ? parseFloat(customer.current_balance) : 0;
                },

                getSelectedCustomer() {
                    if (!this.party_id) return null;
                    return this.customers.find(c => c.id == this.party_id) || null;
                },

                getCustomerCreditLimit() {
                    const c = this.getSelectedCustomer();
                    return c && c.credit_limit ? parseFloat(c.credit_limit) : 0;
                },

                getCustomerCreditDays() {
                    const c = this.getSelectedCustomer();
                    return c && c.credit_days ? parseInt(c.credit_days) : 0;
                },

                isCustomerOverdue() {
                    if (this.isWalkIn()) return false;
                    const c = this.getSelectedCustomer();
                    return !!(c && c.has_overdue);
                },

                getProjectedBalance() {
                    const currentBal = Math.max(0, this.getCustomerBalance());
                    const invoiceDue = Math.max(0, this.calculateGrandTotal() - this.paidAmount());
                    return currentBal + invoiceDue;
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

                formatNumber(num) {
                    const parsed = parseFloat(num);
                    if (isNaN(parsed)) return '0.00';
                    return parsed.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },

                getItemStock(index) {
                    const item = this.items[index];
                    if (!item.product_id || !this.warehouse_id) return '0.00';
                    const product = this.products.find(p => p.id == item.product_id);
                    if (!product || !product.warehouse_stocks) return '0.00';
                    return product.warehouse_stocks[this.warehouse_id] || '0.00';
                },

                // Backorder is allowed (stock can go negative) — this only flags it in the UI
                // and asks for confirmation before saving, it never blocks the sale.
                isOverstock(index) {
                    const item = this.items[index];
                    if (!item.product_id || !item.qty) return false;
                    return item.qty > parseFloat(this.getItemStock(index));
                },

                getOverstockItems() {
                    return this.items.filter((item, index) => this.isOverstock(index));
                },

                getItemCost(index) {
                    const item = this.items[index];
                    if (!item.product_id || !this.warehouse_id) return '0.00';
                    const product = this.products.find(p => p.id == item.product_id);
                    if (!product || !product.warehouse_costs) return '0.00';
                    return this.formatNumber(product.warehouse_costs[this.warehouse_id] || 0);
                },

                getFilteredCustomers() {
                    const s = this.customerSearch.toLowerCase();
                    return this.customers.filter(c => {
                        return c.name.toLowerCase().includes(s) ||
                            (c.phone && c.phone.toLowerCase().includes(s));
                    }).slice(0, 10);
                },

                selectCustomer(customer) {
                    this.party_id = customer.id;
                    this.customerSearch = customer.name;
                    this.customerOpen = false;
                    this.customerHighlightedIndex = -1;

                    if (this.isWalkIn() && this.shopWarehouseId) {
                        this.warehouse_id = this.shopWarehouseId;
                    }

                    // Update prices for any existing items to match selected customer's price tier
                    this.items.forEach((item, idx) => {
                        if (item.product_id) {
                            const p = this.products.find(prod => prod.id == item.product_id);
                            if (p) {
                                item.price = this.getProductPrice(p, customer.price_tier);
                            }
                            this.fetchLastSoldPrice(idx);
                        }
                    });
                },

                fetchLastSoldPrice(index) {
                    const item = this.items[index];
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

                applyLastPrice(index) {
                    const item = this.items[index];
                    if (item && item.lastPrice && item.lastPrice.found) {
                        item.price = item.lastPrice.price;
                    }
                },

                handleCustomerKey(e) {
                    const filtered = this.getFilteredCustomers();
                    if (this.customerOpen && filtered.length > 0) {
                        if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            this.customerHighlightedIndex = (this.customerHighlightedIndex + 1) % filtered.length;
                        } else if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            this.customerHighlightedIndex = (this.customerHighlightedIndex - 1 + filtered.length) % filtered
                                .length;
                        } else if (e.key === 'Enter') {
                            e.preventDefault();
                            if (this.customerHighlightedIndex >= 0) {
                                this.selectCustomer(filtered[this.customerHighlightedIndex]);
                            } else {
                                this.selectCustomer(filtered[0]);
                            }
                        } else if (e.key === 'Escape') {
                            this.customerOpen = false;
                        }
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        this.customerOpen = true;
                    }
                },

                submitForm(e) {
                    if (this.isSubmitting) return;

                    let hasError = false;
                    this.items.forEach(item => {
                        if (!item.product_id) {
                            item.error = true;
                            hasError = true;
                        }
                        if (item.qty <= 0) hasError = true;
                    });

                    if (!this.party_id) {
                        this.formError = 'Please select a customer.';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    if (hasError) {
                        this.formError = 'Please fix all errors and select valid products before saving.';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    // Validate unique Product + Rate combination
                    const combinations = new Set();
                    let duplicateFound = false;
                    this.items.forEach(item => {
                        if (item.product_id) {
                            const key = `${item.product_id}-${item.price}`;
                            if (combinations.has(key)) {
                                duplicateFound = true;
                            }
                            combinations.add(key);
                        }
                    });

                    if (duplicateFound) {
                        this.formError = 'You cannot add the same product multiple times with the SAME price/rate. Please change the rate or remove the duplicate row.';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    for (const payment of this.payments) {
                        if ((parseFloat(payment.amount) || 0) > 0 && !payment.account_id) {
                            this.formError = 'Please select an account for every payment row with an amount.';
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            return;
                        }
                    }

                    if (this.isWalkIn()) {
                        if (!this.warehouse_id) {
                            this.formError = 'Walk-In Customer requires a Shop Warehouse to be configured in Settings.';
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            return;
                        }
                        if (this.paidAmount() + 0.01 < this.calculateGrandTotal()) {
                            this.formError = 'Walk-In Customer sales must be paid in full — no credit allowed. Please add enough payment (split across accounts if needed) to cover the full amount.';
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            return;
                        }
                    }

                    const overstockItems = this.getOverstockItems();
                    if (overstockItems.length > 0) {
                        const names = overstockItems.map(i => i.search).join(', ');
                        const proceed = confirm('Not enough stock for: ' + names +
                            '. This will be sold as a backorder (negative stock). Continue?');
                        if (!proceed) return;
                    }

                    // Check if offline: Save in local offline queue
                    if (!navigator.onLine || (window.OfflineSync && !window.OfflineSync.isOnline)) {
                        const offlinePayload = {
                            party_id: this.party_id,
                            warehouse_id: this.warehouse_id,
                            date: document.querySelector('input[name="date"]')?.value || new Date().toISOString().split('T')[0],
                            items: this.items.filter(i => i.product_id).map(i => ({
                                product_id: i.product_id,
                                qty: parseFloat(i.qty) || 0,
                                price: parseFloat(i.price) || 0
                            })),
                            payments: this.payments.filter(p => (parseFloat(p.amount) || 0) > 0).map(p => ({
                                account_id: p.account_id,
                                amount: parseFloat(p.amount) || 0
                            })),
                            discount_amount: parseFloat(this.discount_amount) || 0,
                            note: document.querySelector('textarea[name="note"]')?.value || ''
                        };

                        if (window.OfflineSync) {
                            window.OfflineSync.saveOfflineSale(offlinePayload);
                        }

                        alert('Offline Mode: Sale saved to local queue! It will automatically sync to the server when connection is restored.');
                        window.location.href = '{{ route("sales.index") }}';
                        return;
                    }

                    this.formError = '';
                    this.isSubmitting = true;
                    e.target.submit();
                }
            }
        }
    </script>
</x-app-layout>
