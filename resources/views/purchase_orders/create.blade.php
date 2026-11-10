<x-app-layout>
    <div class="py-8" x-data="{
        po_number: '{{ old('po_number', $suggestedPoNumber) }}',
        party_id: '{{ old('party_id', '') }}',
        warehouse_id: '{{ old('warehouse_id', $settings->shop_warehouse_id ?? '') }}',
        order_date: '{{ old('order_date', now()->toDateString()) }}',
        expected_delivery_date: '{{ old('expected_delivery_date', now()->addDays(7)->toDateString()) }}',
        currency: '{{ old('currency', 'PKR') }}',
        exchange_rate: {{ old('exchange_rate', 1.0) }},
        items: [
            { product_id: '', qty: 1, price: 0 }
        ],
        products: @json($products),

        addItem() {
            this.items.push({ product_id: '', qty: 1, price: 0 });
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },

        onProductChange(index) {
            const p = this.products.find(prod => prod.id == this.items[index].product_id);
            if (p) {
                this.items[index].price = parseFloat(p.purchase_price) || 0;
            }
        },

        getTotalQty() {
            return this.items.reduce((sum, item) => sum + (parseFloat(item.qty) || 0), 0);
        },

        getTotalAmount() {
            return this.items.reduce((sum, item) => sum + ((parseFloat(item.qty) || 0) * (parseFloat(item.price) || 0)), 0);
        }
    }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="p-6 sm:p-8 text-gray-900 dark:text-gray-100">

                    <div class="flex items-center justify-between pb-6 border-b border-gray-100 dark:border-gray-700 mb-6">
                        <div>
                            <h2 class="text-xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tight">
                                Create Purchase Order (PO)
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Book an order with your supplier. Goods can be received later in partial shipments.
                            </p>
                        </div>
                        <a href="{{ route('purchase-orders.index') }}"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            Back
                        </a>
                    </div>

                    @if ($errors->any())
                        <div class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 text-xs font-bold">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('purchase-orders.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Top Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">PO Number *</label>
                                <input type="text" name="po_number" x-model="po_number" required
                                    class="w-full text-xs font-mono font-bold rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Supplier / Vendor *</label>
                                <select name="party_id" x-model="party_id" required
                                    class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-indigo-500">
                                    <option value="">Select Supplier</option>
                                    @foreach ($vendors as $v)
                                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Destination Warehouse *</label>
                                <select name="warehouse_id" x-model="warehouse_id" required
                                    class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-indigo-500">
                                    <option value="">Select Warehouse</option>
                                    @foreach ($warehouses as $wh)
                                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Order Date *</label>
                                <input type="date" name="order_date" x-model="order_date" required
                                    class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Expected Delivery Date</label>
                                <input type="date" name="expected_delivery_date" x-model="expected_delivery_date"
                                    class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Currency</label>
                                <select name="currency" x-model="currency"
                                    class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-indigo-500">
                                    <option value="PKR">PKR (Pakistani Rupee)</option>
                                    <option value="RMB">RMB (Chinese Yuan)</option>
                                </select>
                            </div>

                            <div x-show="currency === 'RMB'">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Exchange Rate (PKR per RMB)</label>
                                <input type="number" step="0.01" name="exchange_rate" x-model.number="exchange_rate"
                                    class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-indigo-500">
                            </div>
                        </div>

                        <!-- Ordered Items Table -->
                        <div class="border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden mt-6">
                            <div class="bg-gray-50 dark:bg-gray-900/60 p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <h3 class="text-xs font-black uppercase text-gray-600 dark:text-gray-400 tracking-wider">Ordered Items</h3>
                                <button type="button" @click="addItem()"
                                    class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold flex items-center gap-1 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add Item
                                </button>
                            </div>

                            <div class="p-4 overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                                    <thead>
                                        <tr class="text-[10px] uppercase font-bold text-gray-400">
                                            <th class="text-left py-2 px-3 w-1/2">Product *</th>
                                            <th class="text-right py-2 px-3 w-32">Order Qty *</th>
                                            <th class="text-right py-2 px-3 w-36">Rate / Price *</th>
                                            <th class="text-right py-2 px-3 w-36">Line Total</th>
                                            <th class="w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <template x-for="(item, idx) in items" :key="idx">
                                            <tr>
                                                <td class="py-2.5 px-3">
                                                    <select :name="'items[' + idx + '][product_id]'" x-model="item.product_id" @change="onProductChange(idx)" required
                                                        class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-indigo-500">
                                                        <option value="">Select Product</option>
                                                        @foreach ($products as $p)
                                                            <option value="{{ $p->id }}">{{ $p->name }} (SKU: {{ $p->sku }})</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    <input type="number" :name="'items[' + idx + '][qty]'" x-model.number="item.qty" step="0.01" min="0.01" required
                                                        class="w-full text-right text-xs font-bold rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-indigo-500">
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    <input type="number" :name="'items[' + idx + '][price]'" x-model.number="item.price" step="0.01" min="0" required
                                                        class="w-full text-right text-xs font-bold rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-indigo-500">
                                                </td>
                                                <td class="py-2.5 px-3 text-right font-black font-mono text-gray-900 dark:text-gray-100">
                                                    Rs. <span x-text="((item.qty || 0) * (item.price || 0)).toFixed(2)"></span>
                                                </td>
                                                <td class="py-2.5 px-3 text-center">
                                                    <button type="button" @click="removeItem(idx)" :disabled="items.length === 1"
                                                        class="text-gray-400 hover:text-red-500 disabled:opacity-30 transition">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-900/60 p-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center text-xs font-bold">
                                <div>
                                    Total Ordered Items: <span class="text-indigo-600" x-text="items.length"></span> |
                                    Total Quantity: <span class="text-indigo-600" x-text="getTotalQty()"></span>
                                </div>
                                <div class="text-sm font-black text-gray-900 dark:text-gray-100 font-mono">
                                    Total Order Value: Rs. <span x-text="getTotalAmount().toFixed(2)"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Notes / Instructions</label>
                            <textarea name="note" rows="2" placeholder="Delivery timeline, payment terms, quality requirements..."
                                class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-indigo-500">{{ old('note') }}</textarea>
                        </div>

                        <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-700">
                            <a href="{{ route('purchase-orders.index') }}"
                                class="px-5 py-2.5 text-xs font-bold rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Cancel
                            </a>
                            <button type="submit"
                                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-md">
                                Save Purchase Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
