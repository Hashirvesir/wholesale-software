<x-app-layout>
    <div class="py-12" x-data="returnForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6 border-b dark:border-gray-700 pb-4">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">
                            {{ __('Process Return for Invoice') }}: {{ $settings->invoice_prefix }}{{ $sale->id }}
                        </h3>
                        <div class="text-xs text-gray-500 font-bold uppercase tracking-widest italic">
                            Adjust quantities to reflect the items KEPT. The difference will be RETURNED.
                        </div>
                    </div>

                    <form action="{{ route('sales.return.store', $sale) }}" method="POST" @submit.prevent="submitForm">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <!-- Customer Details -->
                            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                                <label class="block text-xs font-bold text-gray-500 uppercase">Customer</label>
                                <div class="text-lg font-bold">{{ $sale->party->name }}</div>
                            </div>

                            <!-- Warehouse Details -->
                            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                                <label class="block text-xs font-bold text-gray-500 uppercase">Warehouse</label>
                                <div class="text-lg font-bold">{{ $sale->warehouse->name }}</div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="mb-6 overflow-x-auto pb-10">
                            <table
                                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border dark:border-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/3">
                                            Product</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Price</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Original Qty</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-red-600 dark:text-red-400 uppercase tracking-wider">
                                            Return Qty</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">
                                            New Qty (Kept)</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Refund Amount</th>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-10">
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr class="transition duration-75"
                                            :class="item.qty > 0 ? 'bg-red-50 dark:bg-red-900/20' : ''">
                                            <td class="px-4 py-3 text-sm font-bold">
                                                <span x-text="item.search"></span>
                                                <input type="hidden" :name="'items[' + index + '][product_id]'"
                                                    :value="item.product_id">
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <span x-text="currency + ' ' + formatNumber(item.price)"></span>
                                                <input type="hidden" :name="'items[' + index + '][price]'"
                                                    :value="item.price">
                                            </td>
                                            <td class="px-4 py-3 text-sm font-bold text-gray-500">
                                                <span x-text="item.original_qty"></span>
                                            </td>
                                            <td class="px-2 py-3">
                                                <input type="number" x-model.number="item.qty"
                                                    :name="'items[' + index + '][qty]'"
                                                    class="block w-24 text-sm font-bold border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                                    required step="0.01" min="0" :max="item.original_qty"
                                                    @input="validateQty(item)">
                                            </td>
                                            <td
                                                class="px-4 py-3 text-sm font-bold text-indigo-600 dark:text-indigo-400">
                                                <span x-text="calculateKeptQty(index)"></span>
                                            </td>
                                            <td
                                                class="px-4 py-3 text-sm font-bold whitespace-nowrap text-red-600 dark:text-red-400">
                                                <span
                                                    x-text="currency + ' ' + formatNumber(calculateRefund(index))"></span>
                                            </td>
                                            <td class="px-2 py-3 text-center">
                                                <button type="button" @click="returnAll(index)"
                                                    class="text-xs bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 px-2 py-1 rounded hover:bg-red-200">
                                                    Return All
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary -->
                        <div class="flex justify-end border-t dark:border-gray-700 pt-6">
                            <div
                                class="bg-red-50 dark:bg-red-900/30 p-6 rounded-2xl border border-red-100 dark:border-red-800/50 w-full md:w-1/3">
                                <div class="flex justify-between items-center mb-4">
                                    <span
                                        class="text-gray-600 dark:text-gray-400 font-bold uppercase tracking-widest text-xs">Total
                                        Refund Amount:</span>
                                    <span class="text-2xl font-black text-red-600 dark:text-red-400"
                                        x-text="currency + ' ' + formatNumber(calculateTotalRefund())"></span>
                                </div>
                                <div class="text-xs text-gray-500 italic text-center">
                                    This amount will be credited to the customer's account.
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 border-t dark:border-gray-700 pt-8">
                            <a href="{{ route('sales.index') }}"
                                class="mr-6 text-sm text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 font-bold transition italic tracking-widest uppercase">Cancel</a>
                            <button type="submit"
                                class="inline-flex items-center px-6 py-3 bg-red-600 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 shadow-lg">
                                Proceed with Return
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function returnForm() {
            return {
                currency: '{{ $settings->currency_symbol }}',
                items: @json($mappedItems),

                validateQty: function(item) {
                    if (item.qty < 0) item.qty = 0;
                    if (item.qty > item.original_qty) {
                        alert('You cannot return more than original quantity!');
                        item.qty = item.original_qty;
                    }
                },

                calculateKeptQty: function(index) {
                    const item = this.items[index];
                    return (item.original_qty - (item.qty || 0)).toFixed(2);
                },

                calculateRefund: function(index) {
                    const item = this.items[index];
                    const returnQty = (item.qty || 0); // Input is Return Qty
                    return (returnQty * item.price);
                },

                returnAll: function(index) {
                    this.items[index].qty = this.items[index].original_qty; // Return everything
                },

                calculateTotalRefund: function() {
                    return this.items.reduce((sum, item, index) => sum + this.calculateRefund(index), 0);
                },

                formatNumber: function(num) {
                    const parsed = parseFloat(num);
                    if (isNaN(parsed)) return '0.00';
                    return parsed.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },

                submitForm: function(e) {
                    const totalRefund = this.calculateTotalRefund();
                    if (totalRefund <= 0) {
                        alert('Please enter a return quantity for at least one item.');
                        return;
                    } else {
                        if (!confirm('This will create a Sales Return of ' + this.currency + ' ' + this.formatNumber(
                                totalRefund) + ' and update the original invoice. Continue?')) {
                            return;
                        }
                    }
                    e.target.submit();
                }
            };
        }
    </script>
</x-app-layout>
