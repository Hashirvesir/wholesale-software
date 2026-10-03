<x-app-layout>
    <div class="py-12" x-data="purchaseForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6 border-b dark:border-gray-700 pb-4">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">
                            {{ __('Create Purchase Invoice') }}
                        </h3>
                        <div class="text-xs text-gray-500 font-bold uppercase tracking-widest italic">
                            Keyboard Shortcuts: <span class="text-primary-600">Arrows</span> to Navigate | <span
                                class="text-primary-600">Enter</span> on last field for New Row
                        </div>
                    </div>

                    <form action="{{ route('purchases.store') }}" method="POST" @submit.prevent="submitForm">
                        @csrf

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

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                            <!-- Vendor -->
                            <div class="md:col-span-1">
                                <x-input-label for="party_id" :value="__('Vendor')" />
                                <div class="relative" @click.away="vendorOpen = false">
                                    <input type="text" x-model="vendorSearch" @focus="vendorOpen = true"
                                        @input="vendorOpen = true" @keydown="handleVendorKey($event)"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition"
                                        placeholder="Search vendor..." autocomplete="off">

                                    <input type="hidden" name="party_id" :value="party_id">

                                    <div x-show="vendorOpen && getFilteredVendors().length > 0"
                                        class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-xs ring-1 ring-black ring-opacity-5 overflow-auto border dark:border-gray-700">
                                        <template x-for="(vendor, vIdx) in getFilteredVendors()" :key="vendor.id">
                                            <div @click="selectVendor(vendor)"
                                                class="cursor-pointer select-none relative py-2 pl-3 pr-9 border-b last:border-0 dark:border-gray-700 hover:bg-primary-50 dark:hover:bg-primary-900 transition"
                                                :class="vendorHighlightedIndex === vIdx ? 'bg-primary-100 dark:bg-primary-900' :
                                                    ''">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <span class="block truncate uppercase font-bold"
                                                            x-text="vendor.name"></span>
                                                        <span class="text-gray-500 dark:text-[10px]"
                                                            x-text="vendor.phone || 'No Phone'"></span>
                                                    </div>
                                                    <span class="font-bold whitespace-nowrap ml-2"
                                                        :class="vendor.current_balance >= 0 ? 'text-red-500' : 'text-green-500'">
                                                        <span x-text="currencySymbol"></span> <span
                                                            x-text="formatNumber(Math.abs(vendor.current_balance))"></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <template x-if="party_id">
                                        <div class="mt-1 text-xs font-bold"
                                            :class="getVendorBalance() >= 0 ? 'text-red-500' : 'text-green-500'">
                                            Prev. Balance: <span x-text="currencySymbol"></span> <span
                                                x-text="formatNumber(Math.abs(getVendorBalance()))"></span>
                                            <span
                                                x-text="getVendorBalance() >= 0 ? '(Receivable)' : '(Payable)'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Warehouse -->
                            <div class="md:col-span-1">
                                <x-input-label for="warehouse_id" :value="__('Warehouse (Stock Target)')" />
                                <select id="warehouse_id" name="warehouse_id" x-model="warehouse_id"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition"
                                    required>
                                    <option value="">-- Select Warehouse --</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Date -->
                            <div class="md:col-span-1">
                                <x-input-label for="date" :value="__('Date')" />
                                <x-text-input id="date" class="block mt-1 w-full" type="date" name="date"
                                    :value="old('date', date('Y-m-d'))" required />
                            </div>

                            <!-- Currency & Exchange Rate -->
                            <div
                                class="md:col-span-1 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center justify-between mb-2">
                                    <x-input-label :value="__('Currency')" class="!mb-0" />
                                    <div class="flex items-center space-x-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" x-model="currency" name="currency" value="PKR"
                                                class="text-primary-600 focus:ring-primary-500">
                                            <span class="ml-2 text-sm">PKR</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" x-model="currency" name="currency" value="RMB"
                                                class="text-primary-600 focus:ring-primary-500">
                                            <span class="ml-2 text-sm">RMB</span>
                                        </label>
                                    </div>
                                </div>
                                <div x-show="currency === 'RMB'" x-transition>
                                    <x-input-label for="exchange_rate" :value="__('Exchange Rate (1 RMB = ? PKR)')" class="text-[10px]" />
                                    <x-text-input id="exchange_rate" x-model.number="exchange_rate"
                                        class="block w-full text-sm" type="number" step="0.01" min="0.01"
                                        name="exchange_rate" />
                                </div>
                                <input type="hidden" name="exchange_rate"
                                    :value="currency === 'PKR' ? 1 : exchange_rate" x-show="currency === 'PKR'">
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="mb-6 overflow-x-auto pb-48">
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
                                            Qty</th>
                                        <th x-show="currency === 'RMB'"
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Price (RMB)</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Price (PKR)</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-orange-500 dark:text-orange-400 uppercase tracking-wider">
                                            Cargo (PKR)</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Total</th>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-10">
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                                    id="itemsTableBody">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition duration-75">
                                            <td class="px-2 py-3 relative" @click.away="item.open = false">
                                                <!-- Searchable Input (Col 0) -->
                                                <input type="text" x-model="item.search" @focus="item.open = true"
                                                    @input="item.open = true" @keydown="handleKey($event, index, 0)"
                                                    :id="'row-' + index + '-col-0'"
                                                    class="block w-full text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 keyboard-nav"
                                                    :class="item.error ? 'border-red-500 ring-red-500' : ''"
                                                    placeholder="Search product..." autocomplete="off" required>

                                                <input type="hidden" :name="'items[' + index + '][product_id]'"
                                                    :value="item.product_id">

                                                <!-- Results Dropdown -->
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
                                                                    x-text="'Curr. Stock: ' + (product.warehouse_stocks && product.warehouse_stocks[warehouse_id] ? product.warehouse_stocks[warehouse_id] : 0)"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </td>
                                            <td
                                                class="px-2 py-3 text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                                <span x-text="getItemStock(index)"></span>
                                            </td>
                                            <td class="px-2 py-3">
                                                <!-- Qty (Col 1) -->
                                                <input type="number" x-model.number="item.qty"
                                                    :name="'items[' + index + '][qty]'"
                                                    @keydown="handleKey($event, index, 1)"
                                                    :id="'row-' + index + '-col-1'"
                                                    class="block w-full text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 keyboard-nav"
                                                    required step="0.01" min="0.01"
                                                    @input="validateNumber(item, 'qty')">
                                            </td>
                                            <td class="px-2 py-3" x-show="currency === 'RMB'">
                                                <!-- RMB (Col 2) -->
                                                <input type="number" x-model.number="item.price_rmb"
                                                    :name="'items[' + index + '][price_rmb]'"
                                                    @keydown="handleKey($event, index, 2)"
                                                    :id="'row-' + index + '-col-2'"
                                                    @input="calculatePkr(item); validateNumber(item, 'price_rmb')"
                                                    class="block w-full text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 keyboard-nav"
                                                    step="0.01" min="0">
                                            </td>
                                            <td class="px-2 py-3">
                                                <!-- PKR (Col 3 if RMB, else Col 2 if PKR) -->
                                                <input type="number" x-model.number="item.price_pkr"
                                                    :name="'items[' + index + '][price_pkr]'"
                                                    @keydown="handleKey($event, index, currency === 'RMB' ? 3 : 2)"
                                                    :id="'row-' + index + '-col-' + (currency === 'RMB' ? '3' : '2')"
                                                    class="block w-full text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 keyboard-nav"
                                                    required step="0.01" min="0"
                                                    :readonly="currency === 'RMB'"
                                                    :class="currency === 'RMB' ? 'bg-gray-100 dark:bg-gray-700' : ''"
                                                    @input="validateNumber(item, 'price_pkr')">
                                            </td>
                                            <td class="px-2 py-3">
                                                <!-- Cargo Amount (Col 4 if RMB, else Col 3 if PKR) -->
                                                <input type="number" x-model.number="item.cargo_amount"
                                                    :name="'items[' + index + '][cargo_amount]'"
                                                    @keydown="handleKey($event, index, currency === 'RMB' ? 4 : 3)"
                                                    :id="'row-' + index + '-col-' + (currency === 'RMB' ? '4' : '3')"
                                                    :readonly="isTransportMode"
                                                    :class="isTransportMode
                                                        ? 'block w-full text-xs bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-700 text-indigo-600 dark:text-indigo-400 rounded-md shadow-sm cursor-default keyboard-nav'
                                                        : 'block w-full text-xs border-orange-300 dark:border-orange-700 dark:bg-gray-900 dark:text-orange-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 keyboard-nav'"
                                                    step="0.01" min="0" placeholder="0.00"
                                                    @input="validateNumber(item, 'cargo_amount')">
                                            </td>
                                            <td
                                                class="px-4 py-3 text-xs font-bold text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                                <span class="text-[10px] text-gray-400"
                                                    x-text="currencySymbol"></span>
                                                <span x-text="formatNumber(item.qty * item.price_pkr + item.cargo_amount)"></span>
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

                        <!-- Notes and Grand Total -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-t dark:border-gray-700 pt-6">
                            <div>
                                <x-input-label for="note" :value="__('Invoice Note (Optional)')" />
                                <textarea id="note" name="note" rows="3"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150">{{ old('note') }}</textarea>
                            </div>

                            <!-- Cargo Details Section -->
                            <div
                                class="bg-indigo-50/50 dark:bg-indigo-900/10 p-6 rounded-2xl border border-indigo-100 dark:border-indigo-800/50">
                                <h4
                                    class="text-xs font-black uppercase tracking-widest text-indigo-600 mb-4 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0">
                                        </path>
                                    </svg>
                                    Cargo / Transportation Details
                                </h4>
                                <div x-show="isTransportMode" x-cloak class="mb-3 text-xs text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-700 rounded-lg px-3 py-2">
                                    Cargo auto-distributed proportionally by value across all items.
                                    <span class="text-red-600 dark:text-red-400" x-show="!cargo_party_id"> Cargo Party selection is required.</span>
                                </div>
                                <div x-show="hasManualItemCargo" x-cloak class="mb-3 text-xs text-orange-600 dark:text-orange-400 font-bold bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg px-3 py-2">
                                    Weight & rate fields are disabled — per-item cargo entered above.
                                    <span class="text-red-600 dark:text-red-400" x-show="hasManualItemCargo && !cargo_party_id"> Cargo Party selection is required.</span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <x-input-label for="cargo_party_id" class="text-[10px]">
                                            Cargo Party
                                            <span class="text-red-500 font-black ml-1" x-show="hasManualItemCargo || isTransportMode">*</span>
                                        </x-input-label>
                                        <select name="cargo_party_id" id="cargo_party_id"
                                            x-model="cargo_party_id"
                                            :class="(hasManualItemCargo || isTransportMode) && !cargo_party_id ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300 dark:border-gray-700'"
                                            class="block mt-1 w-full dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-lg text-xs">
                                            <option value="">-- No Cargo --</option>
                                            @foreach ($cargoParties as $cp)
                                                <option value="{{ $cp->id }}">{{ $cp->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label for="total_weight" :value="__('Total Weight (Kg)')" class="text-[10px]" />
                                        <input type="number" step="0.01" name="total_weight"
                                            id="total_weight" x-model.number="cargo_weight"
                                            :disabled="hasManualItemCargo"
                                            :class="hasManualItemCargo ? 'opacity-40 cursor-not-allowed !bg-gray-100 dark:!bg-gray-700' : ''"
                                            class="block mt-1 w-full !rounded-lg text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            placeholder="0.00" />
                                    </div>
                                    <div>
                                        <x-input-label for="rate_per_kg" :value="__('Rate per Kg')" class="text-[10px]" />
                                        <input type="number" step="0.01" name="rate_per_kg"
                                            id="rate_per_kg" x-model.number="cargo_rate"
                                            :disabled="hasManualItemCargo"
                                            :class="hasManualItemCargo ? 'opacity-40 cursor-not-allowed !bg-gray-100 dark:!bg-gray-700' : ''"
                                            class="block mt-1 w-full !rounded-lg text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            placeholder="0.00" />
                                    </div>
                                </div>
                                <div
                                    class="mt-4 flex justify-between items-center bg-white dark:bg-gray-800 p-2 rounded-lg border border-indigo-50">
                                    <span class="text-[10px] font-black uppercase text-gray-400">Total Cargo
                                        Charge:</span>
                                    <span class="text-sm font-black text-indigo-600"
                                        x-text="currencySymbol + ' ' + formatNumber(cargo_weight * cargo_rate)"></span>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between">
                                    <x-input-label :value="__('Payment (Optional, split across accounts)')" />
                                </div>
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
                                <x-input-label for="discount_amount" :value="__('Discount Received (Flat)')" />
                                <x-text-input id="discount_amount" x-model.number="discount_amount"
                                    class="block mt-1 w-full text-green-600 font-bold" type="number" step="0.01"
                                    min="0" name="discount_amount" placeholder="0.00" />
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
                                <div class="flex justify-between items-center mb-2 text-sm border-b border-primary-100 dark:border-primary-800/50 pb-2"
                                    x-show="party_id">
                                    <span class="text-gray-600 dark:text-gray-400 font-bold">Prev. Balance:</span>
                                    <span :class="getVendorBalance() >= 0 ? 'text-red-500' : 'text-green-500'"
                                        class="font-bold"
                                        x-text="currencySymbol + ' ' + formatNumber(Math.abs(getVendorBalance()))"></span>
                                </div>
                                <div class="flex justify-between items-center mb-2">
                                    <span
                                        class="text-gray-600 dark:text-gray-400 font-black uppercase tracking-widest text-xs opacity-70">Subtotal:</span>
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200"
                                        x-text="currencySymbol + ' ' + formatNumber(calculateSubtotal())"></span>
                                </div>
                                <div class="flex justify-between items-center mb-2 text-orange-500 dark:text-orange-400"
                                    x-show="calculateItemCargo() > 0 && !isTransportMode">
                                    <span class="font-black uppercase tracking-widest text-xs opacity-70">Item Cargo:</span>
                                    <span class="text-sm font-bold"
                                        x-text="currencySymbol + ' ' + formatNumber(calculateItemCargo())"></span>
                                </div>
                                <div class="flex justify-between items-center mb-2 text-indigo-500 dark:text-indigo-400"
                                    x-show="cargo_weight > 0 && cargo_rate > 0">
                                    <span class="font-black uppercase tracking-widest text-xs opacity-70">Transport Cargo:</span>
                                    <span class="text-sm font-bold"
                                        x-text="currencySymbol + ' ' + formatNumber(cargo_weight * cargo_rate)"></span>
                                </div>
                                <div class="flex justify-between items-center mb-4 text-green-600">
                                    <span
                                        class="font-black uppercase tracking-widest text-xs opacity-70">Discount:</span>
                                    <span class="text-sm font-bold"
                                        x-text="'- ' + currencySymbol + ' ' + formatNumber(discount_amount || 0)"></span>
                                </div>
                                <div
                                    class="flex justify-between items-center mb-4 border-t border-primary-100 dark:border-primary-800/50 pt-2">
                                    <span
                                        class="text-gray-600 dark:text-gray-400 font-black uppercase tracking-widest text-xs">Grand
                                        Total:</span>
                                    <span class="text-2xl font-black text-gray-900 dark:text-white font-mono"
                                        x-text="currencySymbol + ' ' + formatNumber(calculateGrandTotal())"></span>
                                </div>
                                <div
                                    class="mt-2 pt-2 border-t border-primary-100 dark:border-primary-800/50 text-center">
                                    <span
                                        class="text-[10px] font-black uppercase text-primary-400 tracking-widest">Total
                                        Items Quantity: </span>
                                    <span class="text-sm font-bold text-primary-600 dark:text-primary-400"
                                        x-text="formatNumber(calculateTotalQty())"></span>
                                </div>
                                <div class="flex justify-between items-center mb-4 text-sm font-bold border-t border-primary-100 dark:border-primary-800/50 pt-2"
                                    x-show="party_id">
                                    <span class="text-gray-600 dark:text-gray-400">Net Total (Inc. Prev):</span>
                                    <span class="text-indigo-600 dark:text-indigo-400"
                                        x-text="currencySymbol + ' ' + formatNumber(calculateGrandTotal() - getVendorBalance())"></span>
                                </div>
                                <div class="flex justify-between items-center mb-4 text-sm">
                                    <span class="text-gray-600 dark:text-gray-400 font-bold">Paid:</span>
                                    <span class="text-green-600 dark:text-green-400 font-bold"
                                        x-text="currencySymbol + ' ' + formatNumber(paidAmount())"></span>
                                </div>
                                <div class="flex justify-between items-center text-2xl font-black border-t-2 border-primary-100 dark:border-primary-800/50 pt-6"
                                    :class="(calculateGrandTotal() - paidAmount()) > 0 ?
                                        'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                    <span class="tracking-tighter text-lg">BALANCE DUE:</span>
                                    <span
                                        x-text="currencySymbol + ' ' + formatNumber(calculateGrandTotal() - paidAmount())"></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-12 border-t dark:border-gray-700 pt-8">
                            <a href="{{ route('purchases.index') }}"
                                class="mr-6 text-sm text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 font-bold transition italic tracking-widest uppercase">Discard</a>
                            <x-primary-button class="!px-12 !py-4 !text-sm !rounded-xl !shadow-2xl"
                                x-bind:disabled="submitting"
                                x-bind:class="submitting ? 'opacity-50 cursor-not-allowed' : ''">
                                <span x-show="!submitting">{{ __('Finalize Purchase Invoice') }}</span>
                                <span x-show="submitting">Processing...</span>
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @php
        $oldItems = null;
        if (old('items')) {
            $oldItems = collect(old('items'))
                ->map(function ($item) use ($products) {
                    $product = $products->firstWhere('id', $item['product_id'] ?? null);
                    return [
                        'product_id' => $item['product_id'] ?? '',
                        'search' => $product ? $product->name . ' (' . $product->sku . ')' : '',
                        'open' => false,
                        'qty' => (float) ($item['qty'] ?? 1),
                        'price_rmb' => (float) ($item['price_rmb'] ?? 0),
                        'price_pkr' => (float) ($item['price_pkr'] ?? 0),
                        'cargo_amount' => (float) ($item['cargo_amount'] ?? 0),
                        'error' => false,
                        'highlightedIndex' => -1,
                    ];
                })
                ->values();
        }
    @endphp

    <script>
        function purchaseForm() {
            return {
                submitting: false,
                formError: '',
                currencySymbol: '{{ $settings->currency_symbol }}',
                party_id: '{{ old('party_id', '') }}',
                vendorSearch: '{{ old('vendor_search', '') }}',
                vendorOpen: false,
                vendorHighlightedIndex: -1,
                warehouse_id: '{{ old('warehouse_id', $settings->shop_warehouse_id ?? '') }}',
                currency: '{{ old('currency', 'PKR') }}',
                exchange_rate: {{ old('exchange_rate', '40.00') }},
                payments: @if (old('payments'))
                    @json(collect(old('payments'))->map(fn($p) => ['account_id' => $p['account_id'] ?? '', 'amount' => (float) ($p['amount'] ?? 0)])->values())
                @else
                    [{
                        account_id: '',
                        amount: 0
                    }]
                @endif ,
                discount_amount: {{ old('discount_amount', 0) }},
                cargo_party_id: '{{ old('cargo_party_id', '') }}',
                cargo_weight: {{ old('total_weight', 0) }},
                cargo_rate: {{ old('rate_per_kg', 0) }},
                items: @if ($oldItems)
                    @json($oldItems)
                @else
                    [{
                        product_id: '',
                        search: '',
                        open: false,
                        qty: 1,
                        price_rmb: 0,
                        price_pkr: 0,
                        cargo_amount: 0,
                        error: false,
                        highlightedIndex: -1
                    }]
                @endif ,
                products: @json($products),
                vendors: @json($vendors),

                get isTransportMode() {
                    return parseFloat(this.cargo_weight || 0) > 0 || parseFloat(this.cargo_rate || 0) > 0;
                },

                get hasItemCargo() {
                    return this.items.some(item => parseFloat(item.cargo_amount || 0) > 0);
                },

                get hasManualItemCargo() {
                    return !this.isTransportMode && this.items.some(item => parseFloat(item.cargo_amount || 0) > 0);
                },

                distributeTransportCargo() {
                    const totalCargo = parseFloat(this.cargo_weight || 0) * parseFloat(this.cargo_rate || 0);
                    if (totalCargo <= 0) {
                        this.items.forEach(item => { item.cargo_amount = 0; });
                        return;
                    }
                    const totalGoodsValue = this.items.reduce((sum, item) =>
                        sum + (parseFloat(item.qty || 0) * parseFloat(item.price_pkr || 0)), 0);
                    if (totalGoodsValue <= 0) return;
                    let distributed = 0;
                    this.items.forEach((item, index) => {
                        if (index === this.items.length - 1) {
                            item.cargo_amount = Math.round((totalCargo - distributed) * 100) / 100;
                        } else {
                            const itemValue = parseFloat(item.qty || 0) * parseFloat(item.price_pkr || 0);
                            const share = Math.round((itemValue / totalGoodsValue) * totalCargo * 100) / 100;
                            item.cargo_amount = share;
                            distributed += share;
                        }
                    });
                },

                getVendorBalance() {
                    const vendor = this.vendors.find(v => v.id == this.party_id);
                    return vendor ? vendor.current_balance : 0;
                },

                addItem() {
                    this.items.push({
                        product_id: '',
                        search: '',
                        open: false,
                        qty: 1,
                        price_rmb: 0,
                        price_pkr: 0,
                        cargo_amount: 0,
                        error: false,
                        highlightedIndex: -1
                    });
                    if (this.isTransportMode) this.distributeTransportCargo();
                    this.$nextTick(() => {
                        const nextRowIdx = this.items.length - 1;
                        document.getElementById('row-' + nextRowIdx + '-col-0').focus();
                    });
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                        if (this.isTransportMode) this.distributeTransportCargo();
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

                selectProduct(index, product) {
                    this.items[index].product_id = product.id;
                    this.items[index].search = product.name + ' (' + product.sku + ')';
                    this.items[index].open = false;
                    this.items[index].error = false;
                    this.items[index].highlightedIndex = -1;

                    // Focus next column (Qty)
                    this.$nextTick(() => {
                        document.getElementById('row-' + index + '-col-1').focus();
                    });
                },

                handleKey(e, index, col) {
                    const maxCol = this.currency === 'RMB' ? 4 : 3;
                    const maxRow = this.items.length - 1;

                    // Search Dropdown Navigation (Arrow Up/Down/Enter)
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

                    // Grid Navigation
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

                calculatePkr(item) {
                    if (this.currency === 'RMB') {
                        item.price_pkr = (item.price_rmb * this.exchange_rate).toFixed(2);
                    }
                },

                calculateItemCargo() {
                    return this.items.reduce((sum, item) => sum + parseFloat(item.cargo_amount || 0), 0);
                },

                calculateSubtotal() {
                    const goodsTotal = this.items.reduce((sum, item) => sum + (item.qty * item.price_pkr), 0);
                    if (this.isTransportMode) return goodsTotal;
                    return goodsTotal + this.items.reduce((sum, item) => sum + parseFloat(item.cargo_amount || 0), 0);
                },

                calculateGrandTotal() {
                    let base = this.calculateSubtotal() - (this.discount_amount || 0);
                    if (this.isTransportMode) base += parseFloat(this.cargo_weight || 0) * parseFloat(this.cargo_rate || 0);
                    return Math.max(0, base);
                },

                calculateTotalQty() {
                    return this.items.reduce((sum, item) => sum + (parseFloat(item.qty) || 0), 0);
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

                submitForm(e) {
                    if (this.submitting) return;
                    let hasError = false;
                    this.items.forEach(item => {
                        if (!item.product_id) {
                            item.error = true;
                            hasError = true;
                        }
                        if (item.qty <= 0) hasError = true;
                        if (!item.price_pkr || item.price_pkr <= 0) hasError = true;
                    });

                    if (!this.party_id) {
                        this.formError = 'Please select a vendor before saving.';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    if (this.calculateItemCargo() > 0 && !this.cargo_party_id) {
                        this.formError = 'Per-item cargo is entered — please select a Cargo Party so the amount is recorded in their account.';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    if (hasError) {
                        this.formError = 'Please fix all errors: select valid products, enter qty and price for all rows.';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    // Validate unique Product + Rate combination
                    const combinations = new Set();
                    let duplicateFound = false;
                    this.items.forEach(item => {
                        if (item.product_id) {
                            const key = `${item.product_id}-${item.price_pkr}`;
                            if (combinations.has(key)) {
                                duplicateFound = true;
                            }
                            combinations.add(key);
                        }
                    });

                    if (duplicateFound) {
                        this.formError = 'The same product cannot be added twice with the same price. Please change the rate or remove the duplicate row.';
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

                    this.formError = '';

                    // Add vendor search to submission so it can be re-populated on error
                    const searchInput = document.createElement('input');
                    searchInput.type = 'hidden';
                    searchInput.name = 'vendor_search';
                    searchInput.value = this.vendorSearch;
                    e.target.appendChild(searchInput);

                    this.submitting = true;
                    e.target.submit();
                },

                getFilteredVendors() {
                    const s = this.vendorSearch.toLowerCase();
                    return this.vendors.filter(v => {
                        return v.name.toLowerCase().includes(s) || (v.phone && v.phone.toLowerCase().includes(s));
                    }).slice(0, 10);
                },

                selectVendor(vendor) {
                    this.party_id = vendor.id;
                    this.vendorSearch = vendor.name;
                    this.vendorOpen = false;
                    this.vendorHighlightedIndex = -1;
                },

                handleVendorKey(e) {
                    const filtered = this.getFilteredVendors();
                    if (this.vendorOpen && filtered.length > 0) {
                        if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            this.vendorHighlightedIndex = (this.vendorHighlightedIndex + 1) % filtered.length;
                        } else if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            this.vendorHighlightedIndex = (this.vendorHighlightedIndex - 1 + filtered.length) % filtered
                                .length;
                        } else if (e.key === 'Enter') {
                            e.preventDefault();
                            if (this.vendorHighlightedIndex >= 0) {
                                this.selectVendor(filtered[this.vendorHighlightedIndex]);
                            } else {
                                this.selectVendor(filtered[0]);
                            }
                        } else if (e.key === 'Escape') {
                            this.vendorOpen = false;
                        }
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        this.vendorOpen = true;
                    }
                },

                init() {
                    this.$watch('exchange_rate', () => {
                        if (this.currency === 'RMB') {
                            this.items.forEach(item => this.calculatePkr(item));
                        }
                    });
                    this.$watch('currency', (val) => {
                        if (val === 'PKR') {
                            this.items.forEach(item => item.price_rmb = 0);
                        } else {
                            this.items.forEach(item => this.calculatePkr(item));
                        }
                    });
                    this.$watch('cargo_weight', () => this.distributeTransportCargo());
                    this.$watch('cargo_rate', () => this.distributeTransportCargo());
                    this.$watch('items', () => {
                        if (!this.isTransportMode && this.hasItemCargo) {
                            this.cargo_weight = 0;
                            this.cargo_rate = 0;
                        }
                    }, { deep: true });
                }
            }
        }
    </script>
</x-app-layout>
