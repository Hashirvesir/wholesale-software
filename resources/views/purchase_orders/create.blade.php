<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12" x-data="poForm()" @ai-bill-imported.window="handleAiBill($event.detail)">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-6 border-b dark:border-gray-700 pb-4">
                        <div>
                            <h3 class="text-xl font-black text-gray-800 dark:text-gray-100 tracking-wide uppercase flex items-center gap-2">
                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>{{ __('Create Purchase Order (PO)') }}</span>
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Book supplier purchase orders for future delivery. Supports partial Goods Receiving Notes (GRN) and PKR/RMB currencies.
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="$dispatch('open-bill-scanner')"
                                class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white text-xs font-black uppercase tracking-wider transition shadow-md flex items-center gap-1.5 active:scale-95 cursor-pointer">
                                <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>AI Bill Scanner</span>
                            </button>
                            <a href="{{ route('purchase-orders.index') }}"
                                class="px-3.5 py-1.5 rounded-xl border border-gray-300 dark:border-gray-600 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Back to Purchase Orders
                            </a>
                        </div>
                    </div>

                    <!-- Errors -->
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-400 rounded-r-xl">
                            <ul class="list-disc list-inside text-sm font-bold">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div x-show="formError" x-cloak
                        class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-400 flex justify-between items-start rounded-r-xl">
                        <p class="text-sm font-bold" x-text="formError"></p>
                        <button type="button" @click="formError = ''" class="ml-4 text-red-400 hover:text-red-600 font-black text-lg leading-none">&times;</button>
                    </div>

                    <form action="{{ route('purchase-orders.store') }}" method="POST" @submit.prevent="submitForm($event)">
                        @csrf

                        <!-- Top Information Grid (Same as Purchases Create) -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                            <!-- Vendor Autocomplete -->
                            <div class="md:col-span-1">
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                    Supplier / Vendor *
                                </label>
                                <div class="relative" @click.away="vendorOpen = false">
                                    <input type="text" x-model="vendorSearch" @focus="vendorOpen = true"
                                        @input="vendorOpen = true" @keydown="handleVendorKey($event)"
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 rounded-xl shadow-sm text-xs font-bold transition"
                                        placeholder="Search vendor..." autocomplete="off">

                                    <input type="hidden" name="party_id" :value="party_id">

                                    <div x-show="vendorOpen && getFilteredVendors().length > 0" x-cloak
                                        class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-2xl max-h-60 rounded-xl py-1 text-xs border border-gray-200 dark:border-gray-700 overflow-auto divide-y dark:divide-gray-700">
                                        <template x-for="(vendor, vIdx) in getFilteredVendors()" :key="vendor.id">
                                            <div @click="selectVendor(vendor)"
                                                class="cursor-pointer select-none relative py-2.5 px-3 hover:bg-primary-50 dark:hover:bg-primary-950/40 transition"
                                                :class="vendorHighlightedIndex === vIdx ? 'bg-primary-50 dark:bg-primary-950/40' : ''">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <span class="block truncate uppercase font-bold text-gray-900 dark:text-gray-100"
                                                            x-text="vendor.name"></span>
                                                        <span class="text-gray-500 text-[10px]"
                                                            x-text="vendor.phone || 'No Phone'"></span>
                                                    </div>
                                                    <span class="font-black whitespace-nowrap ml-2 text-xs"
                                                        :class="vendor.current_balance >= 0 ? 'text-red-500' : 'text-emerald-500'">
                                                        <span x-text="currencySymbol"></span> <span
                                                            x-text="formatNumber(Math.abs(vendor.current_balance))"></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <template x-if="party_id">
                                        <div class="mt-1 text-[11px] font-bold"
                                            :class="getVendorBalance() >= 0 ? 'text-red-500' : 'text-emerald-500'">
                                            Prev. Balance: <span x-text="currencySymbol"></span> <span
                                                x-text="formatNumber(Math.abs(getVendorBalance()))"></span>
                                            <span x-text="getVendorBalance() >= 0 ? '(Receivable)' : '(Payable)'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Warehouse Target -->
                            <div class="md:col-span-1">
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                    Destination Warehouse *
                                </label>
                                <select id="warehouse_id" name="warehouse_id" x-model="warehouse_id"
                                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 rounded-xl shadow-sm text-xs font-bold transition"
                                    required>
                                    <option value="">-- Select Warehouse --</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Order & Expected Delivery Dates -->
                            <div class="md:col-span-1">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                            Order Date *
                                        </label>
                                        <input class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 rounded-xl shadow-sm text-xs font-bold transition"
                                            type="date" name="order_date" x-model="order_date" required />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                            Delivery Date
                                        </label>
                                        <input class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 rounded-xl shadow-sm text-xs font-bold transition"
                                            type="date" name="expected_delivery_date" x-model="expected_delivery_date" />
                                    </div>
                                </div>
                            </div>

                            <!-- Currency & Exchange Rate -->
                            <div class="md:col-span-1 bg-gray-50 dark:bg-gray-900/40 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Currency</label>
                                    <div class="flex items-center space-x-3">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" x-model="currency" name="currency" value="PKR"
                                                class="text-primary-600 focus:ring-primary-500">
                                            <span class="ml-1.5 text-xs font-bold">PKR</span>
                                        </label>
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" x-model="currency" name="currency" value="RMB"
                                                class="text-primary-600 focus:ring-primary-500">
                                            <span class="ml-1.5 text-xs font-bold">RMB</span>
                                        </label>
                                    </div>
                                </div>
                                <div x-show="currency === 'RMB'" x-transition class="mt-2">
                                    <label for="exchange_rate" class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Exchange Rate (1 RMB = ? PKR)</label>
                                    <input id="exchange_rate" x-model.number="exchange_rate" @input="recalculateAllPkr()"
                                        class="block w-full text-xs font-bold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" type="number" step="0.01" min="0.01"
                                        name="exchange_rate" />
                                </div>
                                <input type="hidden" name="exchange_rate"
                                    :value="currency === 'PKR' ? 1 : exchange_rate" x-show="currency === 'PKR'">
                            </div>
                        </div>

                        <!-- Items Table (Matching Purchases Create) -->
                        <div class="mb-6 overflow-x-auto pb-48">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border dark:border-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/3">
                                            Product (Name / SKU)
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Curr. Stock
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Ordered Qty
                                        </th>
                                        <th x-show="currency === 'RMB'"
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Price (RMB)
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Price (PKR)
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Total (PKR)
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-10">
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" id="itemsTableBody">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition duration-75">
                                            <!-- Product Searchable Input -->
                                            <td class="px-2 py-3 relative" @click.away="item.open = false">
                                                <input type="text" x-model="item.search" @focus="item.open = true"
                                                    @input="item.open = true; item.product_id = ''; item.error = false"
                                                    @keydown="handleKey($event, index, 0)"
                                                    :id="'row-' + index + '-col-0'"
                                                    class="block w-full text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 keyboard-nav"
                                                    :class="item.error ? 'border-red-500 ring-red-500' : ''"
                                                    placeholder="Search product..." autocomplete="off" required>

                                                <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.product_id">

                                                <!-- Dropdown Results -->
                                                <div x-show="item.open && getFilteredProducts(item.search).length > 0" x-cloak
                                                    class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-48 rounded-md py-1 text-xs ring-1 ring-black ring-opacity-5 overflow-auto border dark:border-gray-700">
                                                    <template x-for="(product, pIdx) in getFilteredProducts(item.search)" :key="product.id">
                                                        <div @click="selectProduct(index, product)"
                                                            class="cursor-pointer select-none relative py-2 pl-3 pr-9 border-b last:border-0 dark:border-gray-700 hover:bg-primary-50 dark:hover:bg-primary-900 transition"
                                                            :class="item.highlightedIndex === pIdx ? 'bg-primary-100 dark:bg-primary-900' : ''">
                                                            <div class="flex items-center">
                                                                <div>
                                                                    <span class="block truncate uppercase font-bold" x-text="product.name"></span>
                                                                    <div class="flex items-center gap-2 mt-0.5">
                                                                        <span class="text-gray-500 dark:text-gray-400 text-[9px] font-mono tracking-tighter"
                                                                            x-text="'SKU: ' + product.sku"></span>
                                                                        <template x-if="product.barcode">
                                                                            <span class="text-gray-500 dark:text-gray-400 text-[9px] font-mono tracking-tighter"
                                                                                x-text="'| BAR: ' + product.barcode"></span>
                                                                        </template>
                                                                    </div>
                                                                </div>
                                                                <span class="ml-auto font-black text-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded text-[10px]"
                                                                    x-text="'Curr. Stock: ' + (product.warehouse_stocks && product.warehouse_stocks[warehouse_id] ? product.warehouse_stocks[warehouse_id] : 0)"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </td>

                                            <!-- Curr Stock -->
                                            <td class="px-2 py-3 text-xs font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                                                <span x-text="getItemStock(index)"></span>
                                            </td>

                                            <!-- Qty -->
                                            <td class="px-2 py-3">
                                                <input type="number" x-model.number="item.qty"
                                                    :name="'items[' + index + '][qty]'"
                                                    @keydown="handleKey($event, index, 1)"
                                                    :id="'row-' + index + '-col-1'"
                                                    class="block w-full text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 keyboard-nav"
                                                    required step="0.01" min="0.01"
                                                    @input="validateNumber(item, 'qty')">
                                            </td>

                                            <!-- RMB Price -->
                                            <td class="px-2 py-3" x-show="currency === 'RMB'">
                                                <input type="number" x-model.number="item.price_rmb"
                                                    :name="'items[' + index + '][price_rmb]'"
                                                    @keydown="handleKey($event, index, 2)"
                                                    :id="'row-' + index + '-col-2'"
                                                    @input="calculatePkr(item); validateNumber(item, 'price_rmb')"
                                                    class="block w-full text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 keyboard-nav"
                                                    step="0.01" min="0">
                                            </td>

                                            <!-- PKR Price -->
                                            <td class="px-2 py-3">
                                                <input type="number" x-model.number="item.price_pkr"
                                                    :name="'items[' + index + '][price]'"
                                                    @keydown="handleKey($event, index, currency === 'RMB' ? 3 : 2)"
                                                    :id="'row-' + index + '-col-' + (currency === 'RMB' ? '3' : '2')"
                                                    class="block w-full text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 keyboard-nav"
                                                    required step="0.01" min="0"
                                                    :readonly="currency === 'RMB'"
                                                    :class="currency === 'RMB' ? 'bg-gray-100 dark:bg-gray-700' : ''"
                                                    @input="validateNumber(item, 'price_pkr')">
                                            </td>

                                            <!-- Total -->
                                            <td class="px-4 py-3 text-xs font-bold text-gray-700 dark:text-gray-300 whitespace-nowrap font-mono">
                                                <span class="text-[10px] text-gray-400" x-text="currencySymbol"></span>
                                                <span x-text="formatNumber((item.qty || 0) * (item.price_pkr || 0))"></span>
                                            </td>

                                            <!-- Delete Row -->
                                            <td class="px-2 py-3 text-center">
                                                <button type="button" @click="removeItem(index)"
                                                    class="text-red-500 hover:text-red-700 transition transform hover:scale-110">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Add New Product
                                </button>
                            </div>
                        </div>

                        <!-- Bottom Section (Notes & Order Summary) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start mb-8">
                            <!-- Left: PO Number & Notes -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                        Purchase Order # *
                                    </label>
                                    <input type="text" name="po_number" x-model="po_number" required
                                        class="w-full text-xs font-mono font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm">
                                </div>

                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                        Notes / Special Instructions
                                    </label>
                                    <textarea name="note" x-model="note" rows="3"
                                        placeholder="Add any delivery instructions, terms, or order specifications..."
                                        class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm"></textarea>
                                </div>
                            </div>

                            <!-- Right: Order Summary Card (Matching Purchases Create) -->
                            <div class="p-6 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-inner">
                                <h4 class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4 pb-2 border-b dark:border-gray-700 flex justify-between items-center">
                                    <span>Purchase Order Summary</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-bold" x-text="currency"></span>
                                </h4>

                                <div class="space-y-2 text-xs">
                                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                        <span>Total Product Lines:</span>
                                        <span class="font-bold text-gray-900 dark:text-gray-100" x-text="items.length"></span>
                                    </div>
                                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                        <span>Total Ordered Quantity:</span>
                                        <span class="font-bold text-gray-900 dark:text-gray-100 font-mono" x-text="calculateTotalQty().toLocaleString()"></span>
                                    </div>

                                    <template x-if="currency === 'RMB'">
                                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                            <span>Subtotal (RMB):</span>
                                            <span class="font-bold text-gray-900 dark:text-gray-100 font-mono"
                                                x-text="'¥ ' + formatNumber(calculateRmbTotal())"></span>
                                        </div>
                                    </template>

                                    <div class="flex justify-between text-gray-600 dark:text-gray-400 pt-2 border-t dark:border-gray-700">
                                        <span>Order Value (PKR):</span>
                                        <span class="font-bold text-gray-900 dark:text-gray-100 font-mono"
                                            x-text="currencySymbol + ' ' + formatNumber(calculateGrandTotal())"></span>
                                    </div>

                                    <div class="flex justify-between items-center pt-3 border-t-2 border-gray-300 dark:border-gray-600 text-base font-black text-indigo-600 dark:text-indigo-400">
                                        <span class="tracking-tighter">ESTIMATED TOTAL:</span>
                                        <span class="font-mono text-xl"
                                            x-text="currencySymbol + ' ' + formatNumber(calculateGrandTotal())"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="flex items-center justify-end mt-12 border-t dark:border-gray-700 pt-8">
                            <a href="{{ route('purchase-orders.index') }}"
                                class="mr-6 text-sm text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 font-bold transition italic tracking-widest uppercase">
                                Discard
                            </a>
                            <button type="submit"
                                class="inline-flex items-center px-10 py-3.5 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white text-sm font-bold uppercase tracking-wider rounded-xl transition shadow-xl"
                                :disabled="submitting"
                                :class="submitting ? 'opacity-50 cursor-not-allowed' : ''">
                                <span x-show="!submitting" class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Create Purchase Order</span>
                                </span>
                                <span x-show="submitting">Processing...</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Script Block (Safely outside HTML attributes to avoid quotes breakdown) -->
    <script>
        function poForm() {
            return {
                submitting: false,
                formError: '',
                currencySymbol: '{{ $settings->currency_symbol ?? 'Rs' }}',
                po_number: '{{ old('po_number', $suggestedPoNumber) }}',
                party_id: '{{ old('party_id', '') }}',
                vendorSearch: '',
                vendorOpen: false,
                vendorHighlightedIndex: -1,
                warehouse_id: '{{ old('warehouse_id', $settings->shop_warehouse_id ?? '') }}',
                order_date: '{{ old('order_date', now()->toDateString()) }}',
                expected_delivery_date: '{{ old('expected_delivery_date', now()->addDays(7)->toDateString()) }}',
                currency: '{{ old('currency', 'PKR') }}',
                exchange_rate: {{ old('exchange_rate', 40.00) }},
                note: '{{ old('note', '') }}',
                items: [
                    {
                        product_id: '',
                        search: '',
                        open: false,
                        qty: 1,
                        price_rmb: 0,
                        price_pkr: 0,
                        error: false,
                        highlightedIndex: -1
                    }
                ],
                products: @json($products),
                vendors: @json($vendors),

                init() {
                    if (this.party_id) {
                        const existing = this.vendors.find(v => v.id == this.party_id);
                        if (existing) {
                            this.vendorSearch = existing.name;
                        }
                    }
                },

                getVendorBalance() {
                    const vendor = this.vendors.find(v => v.id == this.party_id);
                    return vendor ? vendor.current_balance : 0;
                },

                getFilteredVendors() {
                    if (!this.vendorSearch || !this.vendorSearch.trim()) return this.vendors.slice(0, 10);
                    const s = this.vendorSearch.toLowerCase().trim();
                    return this.vendors.filter(v => {
                        const nameMatch = v.name && v.name.toLowerCase().includes(s);
                        const phoneMatch = v.phone && v.phone.toLowerCase().includes(s);
                        return nameMatch || phoneMatch;
                    }).slice(0, 15);
                },

                selectVendor(vendor) {
                    this.party_id = vendor.id;
                    this.vendorSearch = vendor.name;
                    this.vendorOpen = false;
                    this.vendorHighlightedIndex = -1;
                },

                handleVendorKey(e) {
                    const list = this.getFilteredVendors();
                    if (list.length === 0) return;
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.vendorHighlightedIndex = (this.vendorHighlightedIndex + 1) % list.length;
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.vendorHighlightedIndex = (this.vendorHighlightedIndex - 1 + list.length) % list.length;
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (this.vendorHighlightedIndex >= 0 && this.vendorHighlightedIndex < list.length) {
                            this.selectVendor(list[this.vendorHighlightedIndex]);
                        } else if (list.length > 0) {
                            this.selectVendor(list[0]);
                        }
                    } else if (e.key === 'Escape' || e.key === 'Tab') {
                        this.vendorOpen = false;
                    }
                },

                getFilteredProducts(search) {
                    if (!search || !search.trim()) return this.products.slice(0, 10);
                    const s = search.toLowerCase().trim();
                    return this.products.filter(p => {
                        return (p.name && p.name.toLowerCase().includes(s)) ||
                               (p.sku && p.sku.toLowerCase().includes(s)) ||
                               (p.barcode && p.barcode.toLowerCase().includes(s));
                    }).slice(0, 15);
                },

                selectProduct(index, product) {
                    const item = this.items[index];
                    item.product_id = product.id;
                    item.search = product.name + (product.sku ? ' (' + product.sku + ')' : '');
                    item.open = false;
                    item.error = false;
                    item.highlightedIndex = -1;

                    // Set default purchase price if available
                    const defPrice = parseFloat(product.purchase_price) || 0;
                    if (this.currency === 'RMB' && this.exchange_rate > 0) {
                        item.price_rmb = parseFloat((defPrice / this.exchange_rate).toFixed(2)) || 0;
                        item.price_pkr = defPrice;
                    } else {
                        item.price_pkr = defPrice;
                    }

                    this.$nextTick(() => {
                        const qtyInput = document.getElementById('row-' + index + '-col-1');
                        if (qtyInput) qtyInput.focus();
                    });
                },

                addItem() {
                    this.items.push({
                        product_id: '',
                        search: '',
                        open: false,
                        qty: 1,
                        price_rmb: 0,
                        price_pkr: 0,
                        error: false,
                        highlightedIndex: -1
                    });
                    this.$nextTick(() => {
                        const nextRowIdx = this.items.length - 1;
                        const input = document.getElementById('row-' + nextRowIdx + '-col-0');
                        if (input) input.focus();
                    });
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    } else {
                        this.items[0] = {
                            product_id: '',
                            search: '',
                            open: false,
                            qty: 1,
                            price_rmb: 0,
                            price_pkr: 0,
                            error: false,
                            highlightedIndex: -1
                        };
                    }
                },

                handleAiBill(data) {
                    if (!data) return;

                    // 1. Auto-select Vendor if matched
                    if (data.supplier && data.supplier.matched_id) {
                        this.party_id = data.supplier.matched_id;
                        this.vendorSearch = data.supplier.matched_name;
                    }

                    // 2. Populate items
                    if (data.items && data.items.length > 0) {
                        this.items = [];
                        data.items.forEach(scanned => {
                            this.items.push({
                                product_id: scanned.matched_product_id || '',
                                search: scanned.matched_product_name || scanned.raw_name,
                                qty: parseFloat(scanned.qty) || 1,
                                price_pkr: parseFloat(scanned.unit_price) || 0,
                                price_rmb: 0,
                                open: false,
                                highlightedIndex: -1,
                                error: !scanned.matched_product_id,
                            });
                        });
                    }

                    if (data.discount > 0) {
                        this.discount_amount = parseFloat(data.discount);
                    }
                },

                handleKey(e, index, col) {
                    const item = this.items[index];
                    const maxCol = (this.currency === 'RMB') ? 3 : 2;
                    const maxRow = this.items.length - 1;

                    // Product Dropdown navigation
                    if (col === 0 && item.open) {
                        const prods = this.getFilteredProducts(item.search);
                        if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            item.highlightedIndex = (item.highlightedIndex + 1) % prods.length;
                            return;
                        } else if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            item.highlightedIndex = (item.highlightedIndex - 1 + prods.length) % prods.length;
                            return;
                        } else if (e.key === 'Enter') {
                            e.preventDefault();
                            if (item.highlightedIndex >= 0 && item.highlightedIndex < prods.length) {
                                this.selectProduct(index, prods[item.highlightedIndex]);
                            } else if (prods.length > 0) {
                                this.selectProduct(index, prods[0]);
                            }
                            return;
                        } else if (e.key === 'Escape' || e.key === 'Tab') {
                            item.open = false;
                            return;
                        }
                    }

                    // Grid Navigation with Arrow keys
                    if (e.key === 'ArrowRight' && col < maxCol) {
                        e.preventDefault();
                        const nextEl = document.getElementById('row-' + index + '-col-' + (col + 1));
                        if (nextEl) nextEl.focus();
                    } else if (e.key === 'ArrowLeft' && col > 0) {
                        e.preventDefault();
                        const prevEl = document.getElementById('row-' + index + '-col-' + (col - 1));
                        if (prevEl) prevEl.focus();
                    } else if (e.key === 'ArrowDown' && index < maxRow) {
                        e.preventDefault();
                        const downEl = document.getElementById('row-' + (index + 1) + '-col-' + col);
                        if (downEl) downEl.focus();
                    } else if (e.key === 'ArrowUp' && index > 0) {
                        e.preventDefault();
                        const upEl = document.getElementById('row-' + (index - 1) + '-col-' + col);
                        if (upEl) upEl.focus();
                    } else if (e.key === 'Enter') {
                        if (col === maxCol) {
                            e.preventDefault();
                            if (index === maxRow) {
                                this.addItem();
                            } else {
                                const nextRow = document.getElementById('row-' + (index + 1) + '-col-0');
                                if (nextRow) nextRow.focus();
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

                calculatePkr(item) {
                    if (this.currency === 'RMB') {
                        item.price_pkr = parseFloat(((item.price_rmb || 0) * (this.exchange_rate || 1)).toFixed(2));
                    }
                },

                recalculateAllPkr() {
                    if (this.currency === 'RMB') {
                        this.items.forEach(item => {
                            this.calculatePkr(item);
                        });
                    }
                },

                calculateTotalQty() {
                    return this.items.reduce((sum, item) => sum + (parseFloat(item.qty) || 0), 0);
                },

                calculateGrandTotal() {
                    return this.items.reduce((sum, item) => sum + ((parseFloat(item.qty) || 0) * (parseFloat(item.price_pkr) || 0)), 0);
                },

                calculateRmbTotal() {
                    return this.items.reduce((sum, item) => sum + ((parseFloat(item.qty) || 0) * (parseFloat(item.price_rmb) || 0)), 0);
                },

                getItemStock(index) {
                    const item = this.items[index];
                    if (!item.product_id || !this.warehouse_id) return '0.00';
                    const product = this.products.find(p => p.id == item.product_id);
                    if (!product || !product.warehouse_stocks) return '0.00';
                    return product.warehouse_stocks[this.warehouse_id] || '0.00';
                },

                formatNumber(num) {
                    const parsed = parseFloat(num);
                    if (isNaN(parsed)) return '0.00';
                    return parsed.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },

                submitForm(e) {
                    if (this.submitting) return;

                    if (!this.party_id) {
                        this.formError = 'Please select a vendor / supplier before saving.';
                        this.vendorOpen = true;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    if (!this.warehouse_id) {
                        this.formError = 'Please select a destination warehouse.';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    let hasError = false;
                    this.items.forEach(item => {
                        if (!item.product_id) {
                            item.error = true;
                            hasError = true;
                        }
                        if ((parseFloat(item.qty) || 0) <= 0) hasError = true;
                        if ((parseFloat(item.price_pkr) || 0) < 0) hasError = true;
                    });

                    if (hasError) {
                        this.formError = 'Please fix item errors: Select valid products, and enter quantity for all rows.';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    this.submitting = true;
                    e.target.submit();
                }
            };
        }
    </script>

    <!-- AI Bill OCR Scanner Modal Component -->
    <x-ai-bill-scanner />
</x-app-layout>
