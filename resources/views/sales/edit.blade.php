<x-app-layout>
    <div class="py-12" x-data="saleForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6 border-b dark:border-gray-700 pb-4">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">
                            {{ __('Edit Sale Invoice') }}: {{ $settings->invoice_prefix }}{{ $sale->id }}
                        </h3>
                        <div class="text-xs text-gray-500 font-bold uppercase tracking-widest italic">
                            Keyboard Shortcuts: <span class="text-primary-600">Arrows</span> to Navigate | <span
                                class="text-primary-600">Enter</span> on last field for New Row
                        </div>
                    </div>

                    <form action="{{ route('sales.update', $sale) }}" method="POST" @submit.prevent="submitForm">
                        @csrf
                        @method('PATCH')

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
                                        <div class="mt-1 text-xs font-bold"
                                            :class="getCustomerBalance() >= 0 ? 'text-red-500' : 'text-green-500'">
                                            Prev. Balance: <span x-text="currency"></span> <span
                                                x-text="formatNumber(Math.abs(getCustomerBalance()))"></span>
                                            <span
                                                x-text="getCustomerBalance() >= 0 ? '(Receivable)' : '(Payable)'"></span>
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
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">
                                            {{ $warehouse->name }}</option>
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
                                    :value="old('date', $sale->date)" required />
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
                                                <span x-text="currency + ' ' + getItemCost(index)"></span>
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

                        <!-- Notes, Payment and Grand Total -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-t dark:border-gray-700 pt-6">
                            <div class="space-y-6">
                                <div>
                                    <x-input-label for="note" :value="__('Invoice Note (Optional)')" />
                                    <textarea id="note" name="note" rows="3"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150">{{ $sale->note }}</textarea>
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
                                <div class="flex justify-between items-center mb-4 text-red-500">
                                    <span
                                        class="font-black uppercase tracking-widest text-xs opacity-70">Discount:</span>
                                    <span class="text-sm font-bold"
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

                <div class="flex items-center justify-end mt-12 border-t dark:border-gray-700 pt-8">
                    <a href="{{ route('sales.index') }}"
                        class="mr-6 text-sm text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 font-bold transition italic tracking-widest uppercase">Discard</a>
                    <x-primary-button class="!px-12 !py-4 !text-sm !rounded-xl !shadow-2xl" ::disabled="isSubmitting">
                        <span x-show="!isSubmitting">{{ __('Update Sale Invoice') }}</span>
                        <span x-show="isSubmitting">{{ __('Processing...') }}</span>
                    </x-primary-button>
                </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    <script>
        function saleForm() {
            return {
                isSubmitting: false,
                formError: '',
                party_id: '{{ $sale->party_id }}',
                customerSearch: '{{ $sale->party->name }}',
                customerOpen: false,
                customerHighlightedIndex: -1,
                warehouse_id: '{{ $sale->warehouse_id }}',
                walkInCustomerId: {{ $settings->walk_in_customer_id ?? 'null' }},
                shopWarehouseId: {{ $settings->shop_warehouse_id ?? 'null' }},
                isWalkIn: function() {
                    return this.walkInCustomerId && this.party_id == this.walkInCustomerId;
                },
                payments: @json(count($mappedPayments) ? $mappedPayments : [['account_id' => '', 'amount' => 0]]),
                discount_amount: {{ $sale->discount_amount ?? 0 }},
                items: @json($mappedItems),
                products: @json($products),
                customers: @json($customers),
                currency: '{{ $settings->currency_symbol }}',

                addItem: function() {
                    this.items.push({
                        product_id: '',
                        search: '',
                        open: false,
                        qty: 1,
                        price: 0,
                        error: false,
                        highlightedIndex: -1
                    });
                    this.$nextTick(() => {
                        const nextRowIdx = this.items.length - 1;
                        const el = document.getElementById('row-' + nextRowIdx + '-col-0');
                        if (el) el.focus();
                    });
                },

                removeItem: function(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },

                paidAmount: function() {
                    return this.payments.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
                },

                addPayment: function() {
                    this.payments.push({
                        account_id: '',
                        amount: 0
                    });
                },

                removePayment: function(index) {
                    if (this.payments.length > 1) {
                        this.payments.splice(index, 1);
                    }
                },

                getFilteredProducts: function(search) {
                    if (!search || search === '') return [];
                    const s = search.toLowerCase();
                    return this.products.filter(p => {
                        return p.name.toLowerCase().includes(s) ||
                            p.sku.toLowerCase().includes(s) ||
                            (p.barcode && p.barcode.toLowerCase().includes(s));
                    }).slice(0, 10);
                },

                selectProduct: function(index, product) {
                    // Check if SAME product with SAME price already exists
                    const exists = this.items.some((item, i) => i !== index && item.product_id === product.id && item
                        .price === (product.sale_price || 0));
                    if (exists) {
                        alert('Product "' + product.name + '" with price ' + (product.sale_price || 0) +
                            ' is already added!');
                        this.items[index].search = '';
                        this.items[index].product_id = '';
                        return;
                    }

                    this.items[index].product_id = product.id;
                    this.items[index].search = product.name + ' (' + product.sku + ')';
                    this.items[index].price = product.sale_price || 0;
                    this.items[index].open = false;
                    this.items[index].error = false;
                    this.items[index].highlightedIndex = -1;

                    this.$nextTick(() => {
                        const el = document.getElementById('row-' + index + '-col-1');
                        if (el) el.focus();
                    });
                },

                handleKey: function(e, index, col) {
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
                                const pIdx = this.items[index].highlightedIndex;
                                if (pIdx >= 0) {
                                    this.selectProduct(index, filtered[pIdx]);
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
                            const el = document.getElementById('row-' + index + '-col-' + (col + 1));
                            if (el) el.focus();
                        }
                    } else if (e.key === 'ArrowLeft') {
                        if (col > 0) {
                            e.preventDefault();
                            const el = document.getElementById('row-' + index + '-col-' + (col - 1));
                            if (el) el.focus();
                        }
                    } else if (e.key === 'ArrowDown') {
                        if (index < maxRow) {
                            e.preventDefault();
                            const el = document.getElementById('row-' + (index + 1) + '-col-' + col);
                            if (el) el.focus();
                        }
                    } else if (e.key === 'ArrowUp') {
                        if (index > 0) {
                            e.preventDefault();
                            const el = document.getElementById('row-' + (index - 1) + '-col-' + col);
                            if (el) el.focus();
                        }
                    } else if (e.key === 'Enter') {
                        if (col === maxCol) {
                            e.preventDefault();
                            if (index === maxRow) {
                                this.addItem();
                            } else {
                                const el = document.getElementById('row-' + (index + 1) + '-col-0');
                                if (el) el.focus();
                            }
                        }
                    }
                },

                validateNumber: function(item, field) {
                    if (item[field] < 0) {
                        alert('Negative values are not allowed!');
                        item[field] = field === 'qty' ? 1 : 0;
                    }
                },

                calculateSubtotal: function() {
                    return this.items.reduce((sum, item) => sum + (item.qty * item.price), 0);
                },

                calculateGrandTotal: function() {
                    return Math.max(0, this.calculateSubtotal() - (this.discount_amount || 0));
                },

                calculateTotalQty: function() {
                    return this.items.reduce((sum, item) => sum + (parseFloat(item.qty) || 0), 0);
                },

                getCustomerBalance: function() {
                    if (!this.party_id) return 0;
                    const customer = this.customers.find(c => c.id == this.party_id);
                    return customer ? parseFloat(customer.current_balance) : 0;
                },

                formatNumber: function(num) {
                    const parsed = parseFloat(num);
                    if (isNaN(parsed)) return '0.00';
                    return parsed.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },

                getItemStock: function(index) {
                    const item = this.items[index];
                    if (!item.product_id || !this.warehouse_id) return '0.00';
                    const product = this.products.find(p => p.id == item.product_id);
                    if (!product || !product.warehouse_stocks) return '0.00';
                    return product.warehouse_stocks[this.warehouse_id] || '0.00';
                },

                // Backorder is allowed (stock can go negative) — this only flags it in the UI
                // and asks for confirmation before saving, it never blocks the sale.
                isOverstock: function(index) {
                    const item = this.items[index];
                    if (!item.product_id || !item.qty) return false;
                    return item.qty > parseFloat(this.getItemStock(index));
                },

                getOverstockItems: function() {
                    return this.items.filter((item, index) => this.isOverstock(index));
                },

                getItemCost: function(index) {
                    const item = this.items[index];
                    if (!item.product_id || !this.warehouse_id) return '0.00';
                    const product = this.products.find(p => p.id == item.product_id);
                    if (!product || !product.warehouse_costs) return '0.00';
                    return this.formatNumber(product.warehouse_costs[this.warehouse_id] || 0);
                },

                getFilteredCustomers: function() {
                    const s = this.customerSearch.toLowerCase();
                    return this.customers.filter(c => {
                        return c.name.toLowerCase().includes(s) ||
                            (c.phone && c.phone.toLowerCase().includes(s));
                    }).slice(0, 10);
                },

                selectCustomer: function(customer) {
                    this.party_id = customer.id;
                    this.customerSearch = customer.name;
                    this.customerOpen = false;
                    this.customerHighlightedIndex = -1;

                    if (this.isWalkIn() && this.shopWarehouseId) {
                        this.warehouse_id = this.shopWarehouseId;
                    }
                },

                handleCustomerKey: function(e) {
                    const filtered = this.getFilteredCustomers();
                    if (this.customerOpen && filtered.length > 0) {
                        if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            this.customerHighlightedIndex = (this.customerHighlightedIndex + 1) % filtered.length;
                        } else if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            this.customerHighlightedIndex = (this.customerHighlightedIndex - 1 + filtered.length) %
                                filtered.length;
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

                submitForm: function(e) {
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

                    this.formError = '';
                    this.isSubmitting = true;
                    e.target.submit();
                }
            };
        }
    </script>
</x-app-layout>
