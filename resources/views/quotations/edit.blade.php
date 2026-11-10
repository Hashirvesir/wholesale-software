<x-app-layout>
    <div class="py-12" x-data="quotationEditForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-6 border-b dark:border-gray-700 pb-4">
                        <div>
                            <h3 class="text-xl font-black text-gray-800 dark:text-gray-100 tracking-wide uppercase flex items-center gap-2">
                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span>{{ __('Edit Quotation / Estimate') }} #{{ $quotation->id }}</span>
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Update quotation details, pricing, or product lines.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('quotations.show', $quotation) }}"
                                class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                View Quotation
                            </a>
                            <a href="{{ route('quotations.index') }}"
                                class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Back to List
                            </a>
                        </div>
                    </div>

                    <form action="{{ route('quotations.update', $quotation) }}" method="POST" @submit.prevent="submitForm">
                        @csrf
                        @method('PUT')

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
                                        <option value="{{ $w->id }}" {{ $quotation->warehouse_id == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
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

                        <!-- Status & Notes Row -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                    Status
                                </label>
                                <select name="status" x-model="status"
                                    class="w-full text-xs font-bold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm">
                                    <option value="draft">Draft (Draft Bill)</option>
                                    <option value="sent">Sent to Customer</option>
                                    <option value="accepted">Accepted / Approved</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                    Notes / Quotation Terms (Printed on Quote)
                                </label>
                                <input type="text" name="note" x-model="notes" placeholder="e.g. Prices valid for 7 days. Subject to stock availability."
                                    class="w-full text-xs font-medium rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm" />
                            </div>
                        </div>

                        <!-- Quotation Items Table -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-visible mb-6 bg-gray-50/50 dark:bg-gray-900/30 p-4">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Estimate Items & Pricing
                                </h4>
                                <button type="button" @click="addItem"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-primary-600 text-white font-bold text-xs rounded-lg hover:bg-primary-700 transition shadow">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Item Row
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="border-b dark:border-gray-700 text-gray-500 dark:text-gray-400 uppercase text-[10px] tracking-wider">
                                            <th class="py-2 px-3 w-12 text-center">#</th>
                                            <th class="py-2 px-3 min-w-[260px]">Product *</th>
                                            <th class="py-2 px-3 w-28 text-center">Stock</th>
                                            <th class="py-2 px-3 w-32 text-right">Qty *</th>
                                            <th class="py-2 px-3 w-36 text-right">Rate / Price *</th>
                                            <th class="py-2 px-3 w-36 text-right">Total</th>
                                            <th class="py-2 px-3 w-16 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y dark:divide-gray-700">
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr class="hover:bg-white dark:hover:bg-gray-800/50 transition">
                                                <td class="py-2.5 px-3 text-center font-bold text-gray-400" x-text="index + 1"></td>
                                                
                                                <!-- Product Search / Select -->
                                                <td class="py-2.5 px-3 relative" @click.away="item.open = false">
                                                    <input type="text" x-model="item.search" @focus="item.open = true" @input="item.open = true"
                                                        placeholder="Type product name or SKU..."
                                                        class="w-full text-xs font-bold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm"
                                                        required />
                                                    <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id" />

                                                    <div x-show="item.open && getFilteredProducts(item.search).length > 0" x-cloak
                                                        class="absolute z-50 left-3 right-3 mt-1 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 max-h-56 overflow-y-auto divide-y dark:divide-gray-700">
                                                        <template x-for="p in getFilteredProducts(item.search)" :key="p.id">
                                                            <div @click="selectProduct(index, p)"
                                                                class="p-2.5 cursor-pointer hover:bg-primary-50 dark:hover:bg-primary-950/40 flex justify-between items-center text-xs transition">
                                                                <div>
                                                                    <span class="font-bold uppercase text-gray-900 dark:text-gray-100" x-text="p.name"></span>
                                                                    <span class="text-gray-500 text-[10px] block" x-text="'SKU: ' + (p.sku || 'N/A') + ' | Stock: ' + (p.warehouse_stocks ? (p.warehouse_stocks[warehouse_id] || 0) : 0)"></span>
                                                                </div>
                                                                <span class="font-black text-primary-600">
                                                                    <span x-text="currency"></span> <span x-text="formatNumber(p.sale_price)"></span>
                                                                </span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </td>

                                                <!-- Stock in Warehouse -->
                                                <td class="py-2.5 px-3 text-center">
                                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full"
                                                        :class="getItemStock(index) > 0 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-400'"
                                                        x-text="getItemStock(index)">
                                                    </span>
                                                </td>

                                                <!-- Quantity -->
                                                <td class="py-2.5 px-3 text-right">
                                                    <input type="number" step="any" min="0.001" :name="`items[${index}][qty]`" x-model.number="item.qty"
                                                        class="w-full text-right text-xs font-black rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm"
                                                        required />
                                                </td>

                                                <!-- Price -->
                                                <td class="py-2.5 px-3 text-right">
                                                    <input type="number" step="0.01" min="0" :name="`items[${index}][price]`" x-model.number="item.price"
                                                        class="w-full text-right text-xs font-black rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm"
                                                        required />
                                                </td>

                                                <!-- Line Total -->
                                                <td class="py-2.5 px-3 text-right font-black text-gray-900 dark:text-gray-100">
                                                    <span x-text="currency"></span>
                                                    <span x-text="formatNumber(item.qty * item.price)"></span>
                                                </td>

                                                <!-- Remove Action -->
                                                <td class="py-2.5 px-3 text-center">
                                                    <button type="button" @click="removeItem(index)" :disabled="items.length === 1"
                                                        class="text-red-500 hover:text-red-700 disabled:opacity-30 disabled:cursor-not-allowed transition p-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Totals & Actions -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                            <div>
                                <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-xs">
                                    <div class="font-bold flex items-center gap-1.5 mb-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Notice
                                    </div>
                                    <p>Saving changes updates the quotation/estimate record. Once customer accepts, you can convert it to a finalized Sale invoice with 1 click.</p>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-900/40 p-4 rounded-xl border border-gray-200 dark:border-gray-700 space-y-3">
                                <div class="flex justify-between items-center text-xs font-bold text-gray-600 dark:text-gray-400">
                                    <span>Subtotal:</span>
                                    <span class="text-sm font-black text-gray-800 dark:text-gray-200">
                                        <span x-text="currency"></span> <span x-text="formatNumber(calculateSubtotal())"></span>
                                    </span>
                                </div>

                                <div class="flex justify-between items-center text-xs font-bold text-gray-600 dark:text-gray-400">
                                    <span>Discount Amount:</span>
                                    <div class="w-36">
                                        <input type="number" step="0.01" min="0" name="discount_amount" x-model.number="discount_amount"
                                            class="w-full text-right text-xs font-black rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm" />
                                    </div>
                                </div>

                                <div class="border-t dark:border-gray-700 pt-3 flex justify-between items-center">
                                    <span class="text-sm font-black uppercase text-gray-900 dark:text-gray-100">Grand Total:</span>
                                    <span class="text-xl font-black text-primary-600 dark:text-primary-400">
                                        <span x-text="currency"></span> <span x-text="formatNumber(calculateGrandTotal())"></span>
                                    </span>
                                </div>

                                <div class="pt-4 flex justify-end gap-3">
                                    <a href="{{ route('quotations.show', $quotation) }}"
                                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        Cancel
                                    </a>
                                    <button type="submit" :disabled="isSubmitting"
                                        class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-black uppercase tracking-wider transition shadow-lg flex items-center gap-2">
                                        <svg x-show="isSubmitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <span>Update Quotation</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function quotationEditForm() {
            return {
                currency: '{{ $settings->currency_symbol ?? "Rs" }}',
                customers: @json($customers),
                products: @json($products),
                customerSearch: '{{ addslashes($quotation->party->name) }}',
                customerOpen: false,
                party_id: '{{ $quotation->party_id }}',
                selectedCustomer: null,
                warehouse_id: '{{ $quotation->warehouse_id }}',
                date: '{{ $quotation->date }}',
                valid_until: '{{ $quotation->valid_until }}',
                status: '{{ $quotation->status }}',
                notes: '{{ addslashes($quotation->note ?? "") }}',
                discount_amount: {{ (float) $quotation->discount_amount }},
                formError: '',
                isSubmitting: false,
                items: [
                    @foreach ($quotation->items as $item)
                    {
                        product_id: '{{ $item->product_id }}',
                        search: '{{ addslashes($item->product->name . " (" . ($item->product->sku ?? "") . ")") }}',
                        open: false,
                        qty: {{ (float) $item->qty }},
                        price: {{ (float) $item->price }}
                    },
                    @endforeach
                ],

                init() {
                    if (this.party_id) {
                        this.selectedCustomer = this.customers.find(c => c.id == this.party_id) || null;
                    }
                    if (this.items.length === 0) {
                        this.addItem();
                    }
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
                    this.customerSearch = c.name + (c.phone ? ' (' + c.phone + ')' : '');
                    this.selectedCustomer = c;
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
