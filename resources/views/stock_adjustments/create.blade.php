<x-app-layout>
    <div class="py-12" x-data="adjustmentForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6 border-b dark:border-gray-700 pb-4">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">
                            {{ __('New Stock Adjustment') }}
                        </h3>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('stock-adjustments.store') }}" method="POST" @submit.prevent="submitForm">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div>
                                <x-input-label for="date" :value="__('Adjustment Date')" />
                                <x-text-input id="date" class="block mt-1 w-full" type="date" name="date"
                                    :value="date('Y-m-d')" required />
                            </div>
                            <div>
                                <x-input-label for="warehouse_id" :value="__('Warehouse')" />
                                <select id="warehouse_id" name="warehouse_id" x-model="warehouse_id"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm"
                                    required>
                                    <option value="">-- Select Warehouse --</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
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
                                            Product</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/4">
                                            Current Stock</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/4">
                                            Adj. Qty</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/6">
                                            Type</th>
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
                                                    class="block w-full text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
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
                                                            class="cursor-pointer select-none relative py-2 pl-3 pr-9 border-b last:border-0 dark:border-gray-700 hover:bg-indigo-50 dark:hover:bg-indigo-950 transition"
                                                            :class="item.highlightedIndex === pIdx ?
                                                                'bg-indigo-100 dark:bg-indigo-900' : ''">
                                                            <div class="flex items-center">
                                                                <div>
                                                                    <span
                                                                        class="block truncate uppercase font-bold text-gray-900 dark:text-white"
                                                                        x-text="product.name"></span>
                                                                    <div class="flex items-center gap-2 mt-0.5">
                                                                        <span
                                                                            class="truncate text-gray-500 dark:text-gray-400 text-[10px]"
                                                                            x-text="'SKU: ' + product.sku"></span>
                                                                        <template x-if="product.barcode">
                                                                            <span
                                                                                class="text-gray-500 dark:text-gray-400 text-[9px] font-mono tracking-tighter"
                                                                                x-text="'| BAR: ' + product.barcode"></span>
                                                                        </template>
                                                                        <span
                                                                            class="text-[10px] text-indigo-500 font-bold"
                                                                            x-text="'• ' + product.category_name"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </td>
                                            <td class="px-2 py-3 text-xs font-bold text-gray-600 dark:text-gray-400">
                                                <span x-text="getItemStock(index)"></span>
                                            </td>
                                            <td class="px-2 py-3">
                                                <input type="number" x-model.number="item.qty"
                                                    :name="'items[' + index + '][qty]'"
                                                    @keydown="handleKey($event, index, 1)"
                                                    :id="'row-' + index + '-col-1'"
                                                    class="block w-full text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                    required step="0.01" min="0.01">
                                            </td>
                                            <td class="px-2 py-3">
                                                <select x-model="item.type" :name="'items[' + index + '][type]'"
                                                    class="block w-full text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                                    <option value="addition">(+) Addition</option>
                                                    <option value="subtraction">(-) Subtraction</option>
                                                </select>
                                            </td>
                                            <td class="px-2 py-3 text-center">
                                                <button type="button" @click="removeItem(index)"
                                                    class="text-red-500 hover:text-red-700 transition transform hover:scale-110">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path
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
                                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-indigo-600 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition shadow-sm text-xs font-black uppercase tracking-widest">
                                    Add Another Product
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-t dark:border-gray-700 pt-6">
                            <div>
                                <x-input-label for="note" :value="__('Reason / Note (Optional)')" />
                                <textarea id="note" name="note" rows="3"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm transition"></textarea>
                            </div>
                            <div class="flex items-center justify-end">
                                <a href="{{ route('stock-adjustments.index') }}"
                                    class="mr-6 text-sm text-gray-500 hover:text-gray-800 font-bold tracking-widest uppercase">Cancel</a>
                                <x-primary-button class="!px-12 !py-4 !text-sm !rounded-xl !shadow-2xl">
                                    {{ __('Finalize Adjustment') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function adjustmentForm() {
            return {
                warehouse_id: '',
                items: [{
                    product_id: '',
                    search: '',
                    open: false,
                    qty: 1,
                    type: 'subtraction',
                    error: false,
                    highlightedIndex: -1
                }],
                products: @json($productsData),

                addItem() {
                    this.items.push({
                        product_id: '',
                        search: '',
                        open: false,
                        qty: 1,
                        type: 'subtraction',
                        error: false,
                        highlightedIndex: -1
                    });
                },

                removeItem(index) {
                    if (this.items.length > 1) this.items.splice(index, 1);
                },

                getFilteredProducts(search) {
                    if (!search || search === '') return [];
                    const s = search.toLowerCase();
                    return this.products.filter(p =>
                        p.name.toLowerCase().includes(s) ||
                        p.sku.toLowerCase().includes(s) ||
                        (p.barcode && p.barcode.toLowerCase().includes(s)) ||
                        (p.category_name && p.category_name.toLowerCase().includes(s))
                    ).slice(0, 10);
                },

                selectProduct(index, product) {
                    this.items[index].product_id = product.id;
                    this.items[index].search = product.name + ' (' + product.sku + ')';
                    this.items[index].open = false;
                    this.items[index].error = false;
                    this.items[index].highlightedIndex = -1;
                    this.$nextTick(() => document.getElementById('row-' + index + '-col-1').focus());
                },

                getItemStock(index) {
                    const item = this.items[index];
                    if (!item.product_id || !this.warehouse_id) return 0;
                    const product = this.products.find(p => p.id == item.product_id);
                    return (product && product.warehouse_stocks) ? (product.warehouse_stocks[this.warehouse_id] || 0) : 0;
                },

                handleKey(e, index, col) {
                    const maxCol = 1;
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

                    if (e.key === 'ArrowDown' && index < maxRow) {
                        e.preventDefault();
                        document.getElementById('row-' + (index + 1) + '-col-' + col).focus();
                    } else if (e.key === 'ArrowUp' && index > 0) {
                        e.preventDefault();
                        document.getElementById('row-' + (index - 1) + '-col-' + col).focus();
                    } else if (e.key === 'Enter' && col === 1) {
                        e.preventDefault();
                        if (index === maxRow) this.addItem();
                        else document.getElementById('row-' + (index + 1) + '-col-0').focus();
                    }
                },

                submitForm(e) {
                    if (!this.warehouse_id) {
                        alert('Please select a warehouse.');
                        return;
                    }

                    let hasError = false;
                    this.items.forEach((item, idx) => {
                        if (!item.product_id) {
                            item.error = true;
                            hasError = true;
                        }
                        if (item.type === 'subtraction' && item.qty > this.getItemStock(idx)) {
                            // We allow it but warn
                            if (!confirm('Subtraction quantity is greater than current stock for ' + item.search +
                                    '. Proceed?')) {
                                hasError = true;
                            }
                        }
                    });

                    if (hasError) return;

                    e.target.submit();
                }
            }
        }
    </script>
</x-app-layout>
