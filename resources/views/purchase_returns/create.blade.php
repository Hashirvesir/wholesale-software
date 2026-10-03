<x-app-layout>
    <div class="py-12" x-data="purchaseReturnForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6 border-b dark:border-gray-700 pb-4">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">{{ __('Create Purchase Return') }}</h3>
                        <a href="{{ route('purchase-returns.index') }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                            ← Back to List
                        </a>
                    </div>

                    <form action="{{ route('purchase-returns.store') }}" method="POST" @submit.prevent="submitForm">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <!-- Purchase Selection -->
                            <div>
                                <x-input-label for="purchase_id" :value="__('Original Purchase Bill')" />
                                <select id="purchase_id" name="purchase_id" x-model="purchase_id" @change="loadPurchaseData" required
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-orange-500 dark:focus:border-orange-600 focus:ring-orange-500 dark:focus:ring-orange-600 rounded-md shadow-sm">
                                    <option value="">-- Select Purchase --</option>
                                    @foreach ($purchases as $purchase)
                                        <option value="{{ $purchase->id }}" data-party="{{ $purchase->party->name }}" data-items='@json($purchase->items)'>
                                            Purchase #{{ $purchase->id }} - {{ $purchase->party->name }} ({{ \Carbon\Carbon::parse($purchase->date)->format('d M, Y') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Warehouse -->
                            <div>
                                <x-input-label for="warehouse_id" :value="__('Return from Warehouse')" />
                                <select id="warehouse_id" name="warehouse_id" x-model="warehouse_id" required
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-orange-500 dark:focus:border-orange-600 focus:ring-orange-500 dark:focus:ring-orange-600 rounded-md shadow-sm">
                                    <option value="">-- Select Warehouse --</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Date -->
                            <div>
                                <x-input-label for="date" :value="__('Return Date')" />
                                <x-text-input id="date" class="block mt-1 w-full" type="date" name="date" :value="date('Y-m-d')" required />
                            </div>
                        </div>

                        <!-- Vendor Info -->
                        <div x-show="vendorName" class="mb-6 p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg">
                            <p class="text-sm font-bold text-orange-800 dark:text-orange-300">
                                Vendor: <span x-text="vendorName"></span>
                            </p>
                        </div>

                        <!-- Items Table -->
                        <div class="mb-6 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border dark:border-gray-700">
                                <thead class="bg-orange-50 dark:bg-orange-900/20">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Select</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Product</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Original Qty</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Return Qty</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Cost</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="(item, index) in availableItems" :key="index">
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                            <td class="px-4 py-3">
                                                <input type="checkbox" x-model="item.selected" @change="updateTotals"
                                                    class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                            </td>
                                            <td class="px-4 py-3 text-sm" x-text="item.product_name"></td>
                                            <td class="px-4 py-3 text-sm" x-text="item.original_qty"></td>
                                            <td class="px-4 py-3">
                                                <input type="number" x-model.number="item.return_qty" @input="updateTotals" step="0.01" min="0" :max="item.original_qty"
                                                    :disabled="!item.selected"
                                                    class="w-24 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md text-sm"
                                                    :class="{'opacity-50': !item.selected}">
                                            </td>
                                            <td class="px-4 py-3 text-sm" x-text="formatNumber(item.cost)"></td>
                                            <td class="px-4 py-3 text-sm font-bold text-orange-600" x-text="formatNumber(item.selected ? item.return_qty * item.cost : 0)"></td>
                                        </tr>
                                    </template>
                                    <tr x-show="availableItems.length === 0">
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                            Please select a purchase bill to load items
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Note -->
                        <div class="mb-6">
                            <x-input-label for="note" :value="__('Note (Optional)')" />
                            <textarea id="note" name="note" rows="3"
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-orange-500 dark:focus:border-orange-600 focus:ring-orange-500 dark:focus:ring-orange-600 rounded-md shadow-sm"></textarea>
                        </div>

                        <!-- Total Summary -->
                        <div class="flex justify-end mb-6">
                            <div class="w-full md:w-1/3 bg-orange-50 dark:bg-orange-900/20 p-6 rounded-lg border-2 border-orange-200 dark:border-orange-800">
                                <div class="flex justify-between items-center text-2xl font-black">
                                    <span class="text-gray-700 dark:text-gray-300">Total Return:</span>
                                    <span class="text-orange-600 dark:text-orange-400">Rs. <span x-text="formatNumber(totalAmount)"></span></span>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden inputs for selected items -->
                        <template x-for="(item, index) in selectedItems" :key="index">
                            <div>
                                <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.product_id">
                                <input type="hidden" :name="'items[' + index + '][qty]'" :value="item.return_qty">
                                <input type="hidden" :name="'items[' + index + '][cost]'" :value="item.cost">
                            </div>
                        </template>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('purchase-returns.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                Cancel
                            </a>
                            <button type="submit" :disabled="selectedItems.length === 0"
                                class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                Create Purchase Return
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function purchaseReturnForm() {
            return {
                purchase_id: '',
                warehouse_id: '',
                vendorName: '',
                availableItems: [],
                totalAmount: 0,

                get selectedItems() {
                    return this.availableItems.filter(item => item.selected && item.return_qty > 0);
                },

                loadPurchaseData() {
                    const select = document.getElementById('purchase_id');
                    const option = select.options[select.selectedIndex];
                    
                    if (!option.value) {
                        this.availableItems = [];
                        this.vendorName = '';
                        return;
                    }

                    this.vendorName = option.dataset.party;
                    const items = JSON.parse(option.dataset.items);
                    
                    this.availableItems = items.map(item => ({
                        product_id: item.product_id,
                        product_name: item.product.name,
                        original_qty: parseFloat(item.qty),
                        return_qty: parseFloat(item.qty),
                        cost: parseFloat(item.cost),
                        selected: false
                    }));
                    
                    this.updateTotals();
                },

                updateTotals() {
                    this.totalAmount = this.selectedItems.reduce((sum, item) => {
                        return sum + (item.return_qty * item.cost);
                    }, 0);
                },

                formatNumber(num) {
                    return parseFloat(num || 0).toFixed(2);
                },

                submitForm(e) {
                    if (this.selectedItems.length === 0) {
                        alert('Please select at least one item to return');
                        return;
                    }
                    e.target.submit();
                }
            }
        }
    </script>
</x-app-layout>
