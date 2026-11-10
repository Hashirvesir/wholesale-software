<x-app-layout>
    <div class="py-12" x-data="quotationForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-6 border-b dark:border-gray-700 pb-4">
                        <div>
                            <h3 class="text-xl font-black text-gray-800 dark:text-gray-100 tracking-wide uppercase flex items-center gap-2">
                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>{{ __('Create Quotation / Estimate (Kacha Bill)') }}</span>
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Quotations do not deduct inventory or create ledger accounting entries until converted to a Sale.</p>
                        </div>
                        <a href="{{ route('quotations.index') }}"
                            class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            Back to Quotations
                        </a>
                    </div>

                    <form action="{{ route('quotations.store') }}" method="POST" @submit.prevent="submitForm">
                        @csrf

                        <!-- Validation Errors -->
                        @if ($errors->any())
                            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-300 rounded-r-lg">
                                <ul class="list-disc list-inside text-xs font-bold">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div x-show="formError" x-cloak
                            class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-300 rounded-r-lg flex justify-between items-center text-xs font-bold">
                            <span x-text="formError"></span>
                            <button type="button" @click="formError = ''" class="text-red-500 text-base">&times;</button>
                        </div>

                        <!-- Basic Info Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <!-- Customer -->
                            <div class="relative md:col-span-2" @click.away="customerOpen = false">
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                    Customer *
                                </label>
                                <input type="text" x-model="customerSearch" @focus="customerOpen = true" @input="customerOpen = true"
                                    class="w-full text-xs font-bold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm"
                                    placeholder="Search customer by name or phone..." autocomplete="off" required />

                                <input type="hidden" name="party_id" :value="party_id" />

                                <div x-show="customerOpen && filteredCustomers().length > 0" x-cloak
                                    class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 max-h-56 overflow-y-auto divide-y dark:divide-gray-700">
                                    <template x-for="c in filteredCustomers()" :key="c.id">
                                        <div @click="selectCustomer(c)"
                                            class="p-2.5 cursor-pointer hover:bg-primary-50 dark:hover:bg-primary-950/40 flex justify-between items-center text-xs transition">
                                            <div>
                                                <span class="font-bold uppercase text-gray-900 dark:text-gray-100" x-text="c.name"></span>
                                                <span class="text-gray-500 text-[10px] block" x-text="c.phone || 'No Phone'"></span>
                                            </div>
                                            <span class="font-black text-xs" :class="c.current_balance >= 0 ? 'text-red-500' : 'text-emerald-500'">
                                                <span x-text="currency"></span> <span x-text="formatNumber(Math.abs(c.current_balance))"></span>
                                            </span>
                                        </div>
                                    </template>
                                </div>

                                <template x-if="selectedCustomer">
                                    <div class="mt-1 text-[11px] font-bold text-gray-500">
                                        Current Ledger Balance:
                                        <span :class="selectedCustomer.current_balance >= 0 ? 'text-red-500' : 'text-emerald-500'">
                                            <span x-text="currency"></span> <span x-text="formatNumber(Math.abs(selectedCustomer.current_balance))"></span>
                                            <span x-text="selectedCustomer.current_balance >= 0 ? '(Due)' : '(Advance)'"></span>
                                        </span>
                                    </div>
                                </template>
                            </div>

                            <!-- Warehouse -->
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                    Warehouse (Stock Check) *
                                </label>
                                <select name="warehouse_id" x-model="warehouse_id" required
                                    class="w-full text-xs font-bold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm">
                                    @foreach ($warehouses as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Date & Validity -->
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                        Date *
                                    </label>
                                    <input type="date" name="date" x-model="date" required
                                        class="w-full text-xs font-bold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                        Valid Until
                                    </label>
                                    <input type="date" name="valid_until" x-model="valid_until"
                                        class="w-full text-xs font-bold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm" />
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="mb-6 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700 text-left">
                                <thead class="bg-gray-50 dark:bg-gray-900/50 text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    <tr>
                                        <th class="px-4 py-3 w-1/3">Product (Name / SKU)</th>
                                        <th class="px-3 py-3 text-center">Available Stock</th>
                                        <th class="px-3 py-3 text-center w-28">Quantity</th>
                                        <th class="px-3 py-3 text-right w-32">Price (PKR)</th>
                                        <th class="px-4 py-3 text-right">Total</th>
                                        <th class="px-3 py-3 text-center w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-xs bg-white dark:bg-gray-800">
                                    <template x-for="(item, idx) in items" :key="idx">
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                            <!-- Product Search -->
                                            <td class="px-3 py-2.5 relative" @click.away="item.open = false">
                                                <input type="text" x-model="item.search" @focus="item.open = true" @input="item.open = true"
                                                    class="w-full text-xs font-semibold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-primary-500 focus:border-primary-500"
                                                    placeholder="Type product name or SKU..." autocomplete="off" required />

                                                <input type="hidden" :name="'items[' + idx + '][product_id]'" :value="item.product_id" />

                                                <div x-show="item.open && getFilteredProducts(item.search).length > 0" x-cloak
                                                    class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 max-h-48 overflow-y-auto divide-y dark:divide-gray-700">
                                                    <template x-for="p in getFilteredProducts(item.search)" :key="p.id">
                                                        <div @click="selectProduct(idx, p)"
                                                            class="p-2 cursor-pointer hover:bg-primary-50 dark:hover:bg-gray-700 flex justify-between items-center text-xs transition">
                                                            <div>
                                                                <span class="font-bold uppercase" x-text="p.name"></span>
                                                                <span class="text-gray-400 text-[10px] block font-mono" x-text="'SKU: ' + p.sku"></span>
                                                            </div>
                                                            <div class="text-right">
                                                                <span class="font-bold block" x-text="currency + ' ' + formatNumber(p.sale_price)"></span>
                                                                <span class="text-[10px] text-emerald-600 font-bold" x-text="'Stock: ' + (p.warehouse_stocks[warehouse_id] || 0)"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </td>

                                            <!-- Stock -->
                                            <td class="px-3 py-2.5 text-center font-bold">
                                                <span class="px-2 py-0.5 rounded text-[11px]"
                                                    :class="getItemStock(idx) >= item.qty ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600' : 'bg-amber-100 text-amber-700'">
                                                    <span x-text="formatNumber(getItemStock(idx))"></span>
                                                </span>
                                            </td>

                                            <!-- Quantity -->
                                            <td class="px-3 py-2.5">
                                                <input type="number" x-model.number="item.qty" step="0.01" min="0.01"
                                                    :name="'items[' + idx + '][qty]'" required
                                                    class="w-full text-center py-1.5 px-2 text-xs font-bold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-primary-500 focus:border-primary-500" />
                                            </td>

                                            <!-- Price -->
                                            <td class="px-3 py-2.5 text-right">
                                                <input type="number" x-model.number="item.price" step="0.01" min="0"
                                                    :name="'items[' + idx + '][price]'" required
                                                    class="w-full text-right py-1.5 px-2 text-xs font-bold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-primary-500 focus:border-primary-500" />
                                            </td>

                                            <!-- Subtotal -->
                                            <td class="px-4 py-2.5 text-right font-black text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                                <span x-text="currency"></span> <span x-text="formatNumber(item.qty * item.price)"></span>
                                            </td>

                                            <!-- Remove Row -->
                                            <td class="px-3 py-2.5 text-center">
                                                <button type="button" @click="removeItem(idx)" x-show="items.length > 1"
                                                    class="text-gray-400 hover:text-red-500 transition p-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>

                            <div class="p-3 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                                <button type="button" @click="addItem"
                                    class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-800 border border-primary-600 text-primary-600 dark:text-primary-400 rounded-lg hover:bg-primary-600 hover:text-white transition text-xs font-black uppercase tracking-wider shadow-sm">
                                    + Add Item Row
                                </button>
                            </div>
                        </div>

                        <!-- Footer: Notes & Grand Totals -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                    Terms & Notes (Appears on PDF Estimate)
                                </label>
                                <textarea name="note" x-model="note" rows="4"
                                    class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="e.g. Validity 7 days. Rates subject to stock availability..."></textarea>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-900/60 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-3">
                                <div class="flex justify-between items-center text-xs font-bold text-gray-600 dark:text-gray-300">
                                    <span>Subtotal:</span>
                                    <span class="font-black text-gray-900 dark:text-gray-100" x-text="currency + ' ' + formatNumber(calculateSubtotal())"></span>
                                </div>

                                <div class="flex justify-between items-center text-xs font-bold text-rose-500">
                                    <span>Discount (Flat):</span>
                                    <div class="w-32">
                                        <input type="number" name="discount_amount" x-model.number="discount_amount" step="0.01" min="0"
                                            class="w-full text-right py-1 px-2 text-xs font-bold text-rose-600 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800"
                                            placeholder="0.00" />
                                    </div>
                                </div>

                                <div class="border-t border-gray-200 dark:border-gray-700 pt-3 flex justify-between items-baseline">
                                    <span class="text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Quotation Total:</span>
                                    <span class="text-2xl font-black text-primary-600 font-mono"
                                        x-text="currency + ' ' + formatNumber(calculateGrandTotal())"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-8 pt-4 border-t dark:border-gray-700 flex justify-end gap-3">
                            <a href="{{ route('quotations.index') }}"
                                class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" :disabled="isSubmitting"
                                class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-lg transition active:scale-95 disabled:opacity-50">
                                <span x-show="!isSubmitting">Save Quotation</span>
                                <span x-show="isSubmitting">Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function quotationForm() {
            return {
                currency: '{{ $settings->currency_symbol ?? "Rs" }}',
                customers: @json($customers),
                products: @json($products),
                party_id: '{{ $settings->walk_in_customer_id ?? ($customers->first()->id ?? "") }}',
                customerSearch: '{{ $settings->walkInCustomer->name ?? ($customers->first()->name ?? "") }}',
                selectedCustomer: null,
                customerOpen: false,
                warehouse_id: '{{ $settings->shop_warehouse_id ?? ($warehouses->first()->id ?? "") }}',
                date: '{{ date("Y-m-d") }}',
                valid_until: '{{ date("Y-m-d", strtotime("+7 days")) }}',
                discount_amount: 0,
                note: '',
                formError: '',
                isSubmitting: false,
                items: [
                    {
                        product_id: '',
                        search: '',
                        open: false,
                        qty: 1,
                        price: 0
                    }
                ],

                init() {
                    this.selectedCustomer = this.customers.find(c => c.id == this.party_id) || null;
                },

                filteredCustomers() {
                    if (!this.customerSearch) return this.customers.slice(0, 10);
                    const q = this.customerSearch.toLowerCase().trim();
                    return this.customers.filter(c =>
                        c.name.toLowerCase().includes(q) || (c.phone && c.phone.includes(q))
                    ).slice(0, 10);
                },

                selectCustomer(c) {
                    this.party_id = c.id;
                    this.selectedCustomer = c;
                    this.customerSearch = c.name;
                    this.customerOpen = false;
                },

                getFilteredProducts(search) {
                    if (!search) return this.products.slice(0, 10);
                    const q = search.toLowerCase().trim();
                    return this.products.filter(p =>
                        p.name.toLowerCase().includes(q) || (p.sku && p.sku.toLowerCase().includes(q))
                    ).slice(0, 10);
                },

                selectProduct(idx, product) {
                    this.items[idx].product_id = product.id;
                    this.items[idx].search = product.name + ' (' + (product.sku || '') + ')';
                    this.items[idx].price = parseFloat(product.sale_price) || 0;
                    this.items[idx].open = false;
                },

                getItemStock(idx) {
                    const item = this.items[idx];
                    if (!item.product_id) return 0;
                    const p = this.products.find(prod => prod.id == item.product_id);
                    if (!p || !p.warehouse_stocks) return 0;
                    return p.warehouse_stocks[this.warehouse_id] || 0;
                },

                addItem() {
                    this.items.push({
                        product_id: '',
                        search: '',
                        open: false,
                        qty: 1,
                        price: 0
                    });
                },

                removeItem(idx) {
                    this.items.splice(idx, 1);
                },

                calculateSubtotal() {
                    return this.items.reduce((sum, item) => sum + Math.round((parseFloat(item.qty) || 0) * (parseFloat(item.price) || 0) * 100) / 100, 0);
                },

                calculateGrandTotal() {
                    return Math.max(0, this.calculateSubtotal() - (parseFloat(this.discount_amount) || 0));
                },

                submitForm() {
                    if (!this.party_id) {
                        this.formError = 'Please select a customer.';
                        return;
                    }
                    if (this.items.some(i => !i.product_id)) {
                        this.formError = 'Please select a valid product for every item row.';
                        return;
                    }
                    this.isSubmitting = true;
                    this.$el.submit();
                },

                formatNumber(val) {
                    return new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(val || 0);
                }
            };
        }
    </script>
</x-app-layout>
