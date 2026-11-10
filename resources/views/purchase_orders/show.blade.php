<x-app-layout>
    <div class="py-8" x-data="poShowData()">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="p-6 sm:p-8 text-gray-900 dark:text-gray-100">

                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100 dark:border-gray-700 mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-xl font-black text-gray-900 dark:text-gray-100 font-mono tracking-tight">
                                        {{ $purchaseOrder->po_number }}
                                    </h2>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider
                                        {{ $purchaseOrder->status === 'completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' :
                                           ($purchaseOrder->status === 'partial' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' :
                                           'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300') }}">
                                        {{ $purchaseOrder->status }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Ordered on {{ $purchaseOrder->order_date->format('d M, Y') }} — Expected Delivery: {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('d M, Y') : 'Immediate' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if ($purchaseOrder->status !== 'completed' && $purchaseOrder->status !== 'cancelled')
                                <button type="button" @click="receiveModalOpen = true"
                                    class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl font-bold text-xs uppercase tracking-wider transition shadow-sm">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Receive Goods (GRN)
                                </button>
                            @endif
                            <a href="{{ route('purchase-orders.index') }}"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Back
                            </a>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="mb-4 p-4 bg-emerald-50 dark:bg-emerald-950/40 border-l-4 border-emerald-500 text-emerald-800 dark:text-emerald-300 rounded-r-xl text-sm font-semibold">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 p-4 bg-rose-50 dark:bg-rose-950/40 border-l-4 border-rose-500 text-rose-800 dark:text-rose-300 rounded-r-xl text-sm font-semibold">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Details Banner -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700 mb-6 text-xs">
                        <div>
                            <span class="text-gray-400 block mb-0.5">Supplier / Vendor</span>
                            <span class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ $purchaseOrder->party->name ?? 'N/A' }}</span>
                            <span class="text-gray-500 block">{{ $purchaseOrder->party->phone ?? '' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block mb-0.5">Destination Warehouse</span>
                            <span class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ $purchaseOrder->warehouse->name ?? 'Default' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block mb-0.5">Total Order Value</span>
                            <span class="font-black font-mono text-gray-900 dark:text-gray-100 text-base">
                                Rs. {{ number_format($purchaseOrder->total_amount, 2) }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400 block mb-0.5">Fulfillment Status</span>
                            @php
                                $ordered = $purchaseOrder->getTotalOrderedQty();
                                $received = $purchaseOrder->getTotalReceivedQty();
                                $pct = $ordered > 0 ? min(100, round(($received / $ordered) * 100)) : 0;
                            @endphp
                            <div class="font-bold text-gray-900 dark:text-gray-100">
                                {{ number_format($received, 0) }} of {{ number_format($ordered, 0) }} items ({{ $pct }}%)
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-1 overflow-hidden">
                                <div class="h-1.5 rounded-full {{ $pct == 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Ordered Items Table -->
                    <div class="border border-gray-100 dark:border-gray-700 rounded-2xl overflow-hidden mb-8">
                        <div class="bg-gray-50 dark:bg-gray-900/60 p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                            <h3 class="text-xs font-black uppercase text-gray-600 dark:text-gray-400 tracking-wider">Ordered Items Breakdown</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                                <thead class="bg-gray-50/50 dark:bg-gray-900/30 text-[10px] uppercase font-bold text-gray-500">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Product</th>
                                        <th class="px-4 py-3 text-right">Ordered Qty</th>
                                        <th class="px-4 py-3 text-right">Received Qty</th>
                                        <th class="px-4 py-3 text-right">Remaining</th>
                                        <th class="px-4 py-3 text-right">Unit Price</th>
                                        <th class="px-4 py-3 text-right">Total Price</th>
                                        <th class="px-4 py-3 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach ($purchaseOrder->items as $item)
                                        @php
                                            $rem = $item->getRemainingQty();
                                            $isDone = ($item->received_qty >= $item->ordered_qty);
                                        @endphp
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750 transition">
                                            <td class="px-4 py-3">
                                                <div class="font-bold text-gray-900 dark:text-gray-100">{{ $item->product->name ?? 'N/A' }}</div>
                                                <div class="text-[10px] font-mono text-gray-400">SKU: {{ $item->product->sku ?? '' }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-right font-bold text-gray-800 dark:text-gray-200">
                                                {{ number_format($item->ordered_qty, 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-right font-black font-mono text-emerald-600 dark:text-emerald-400">
                                                {{ number_format($item->received_qty, 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-right font-black font-mono {{ $rem > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                                                {{ number_format($rem, 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-right font-mono text-gray-700 dark:text-gray-300">
                                                Rs. {{ number_format($item->unit_price, 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-right font-black font-mono text-gray-900 dark:text-gray-100">
                                                Rs. {{ number_format($item->total_price, 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                                @if ($isDone)
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                        Received
                                                    </span>
                                                @elseif ($item->received_qty > 0)
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                                        Partial
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                                        Pending
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Linked Goods Receipts / Purchases History -->
                    <div class="border border-gray-100 dark:border-gray-700 rounded-2xl overflow-hidden mb-6">
                        <div class="bg-gray-50 dark:bg-gray-900/60 p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                            <h3 class="text-xs font-black uppercase text-gray-600 dark:text-gray-400 tracking-wider">Goods Receipt Notes (Shipments Received)</h3>
                            <span class="text-xs text-gray-500 font-bold">{{ $purchaseOrder->purchases->count() }} Shipment(s)</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                                <thead class="bg-gray-50/50 dark:bg-gray-900/30 text-[10px] uppercase font-bold text-gray-500">
                                    <tr>
                                        <th class="px-4 py-3 text-left">GRN / Purchase Invoice</th>
                                        <th class="px-4 py-3 text-left">Date Received</th>
                                        <th class="px-4 py-3 text-left">Received Warehouse</th>
                                        <th class="px-4 py-3 text-right">Received Amount</th>
                                        <th class="px-4 py-3 text-center">Items Received</th>
                                        <th class="px-4 py-3 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @forelse ($purchaseOrder->purchases as $p)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750 transition">
                                            <td class="px-4 py-3 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                                Purchase #{{ $p->id }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                                {{ $p->date }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">
                                                {{ $p->warehouse->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3 text-right font-black font-mono text-gray-900 dark:text-gray-100">
                                                Rs. {{ number_format($p->total_amount, 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-center font-bold">
                                                {{ $p->items->sum('qty') }} units
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <a href="{{ route('purchases.show', $p) }}"
                                                    class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold text-xs">
                                                    View Invoice
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-6 text-center text-gray-400">
                                                No goods have been received yet against this Purchase Order.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Footer Delete if no goods received -->
                    @if ($purchaseOrder->getTotalReceivedQty() == 0)
                        <div class="pt-4 flex justify-end border-t border-gray-100 dark:border-gray-700">
                            <form action="{{ route('purchase-orders.destroy', $purchaseOrder) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this Purchase Order?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 rounded-xl border border-rose-300 dark:border-rose-800 text-rose-600 hover:bg-rose-50 text-xs font-bold uppercase tracking-wider transition">
                                    Delete PO
                                </button>
                            </form>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        <!-- RECEIVE GOODS (GRN) MODAL -->
        <div x-show="receiveModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/75 backdrop-blur-sm p-4">
            <div @click.away="receiveModalOpen = false" class="bg-white dark:bg-gray-800 rounded-2xl max-w-3xl w-full shadow-2xl p-6 border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700 mb-4">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-gray-100 uppercase">Receive Goods (GRN Entry)</h3>
                        <p class="text-xs text-gray-500">Record incoming shipment/truck quantities for PO {{ $purchaseOrder->po_number }}</p>
                    </div>
                    <button type="button" @click="receiveModalOpen = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
                </div>

                <form action="{{ route('purchase-orders.receive', $purchaseOrder) }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Receiving Date *</label>
                            <input type="date" name="receiving_date" x-model="receivingDate" required
                                class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Destination Warehouse *</label>
                            <select name="warehouse_id" x-model="warehouseId" required
                                class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500">
                                @foreach ($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Quantities Table -->
                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden mt-3">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900/60 text-[10px] uppercase font-bold text-gray-500">
                                <tr>
                                    <th class="px-3 py-2 text-left">Product</th>
                                    <th class="px-3 py-2 text-right">Ordered</th>
                                    <th class="px-3 py-2 text-right">Received</th>
                                    <th class="px-3 py-2 text-right">Remaining</th>
                                    <th class="px-3 py-2 text-right w-32">Receiving Now *</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <template x-for="(item, idx) in receivingItems" :key="item.po_item_id">
                                    <tr>
                                        <td class="px-3 py-2 font-bold text-gray-900 dark:text-gray-100" x-text="item.product_name"></td>
                                        <td class="px-3 py-2 text-right text-gray-500" x-text="item.ordered_qty"></td>
                                        <td class="px-3 py-2 text-right text-emerald-600 font-bold" x-text="item.already_received"></td>
                                        <td class="px-3 py-2 text-right text-amber-600 font-bold" x-text="item.remaining_qty"></td>
                                        <td class="px-3 py-2 text-right">
                                            <input type="hidden" :name="'items[' + idx + '][po_item_id]'" :value="item.po_item_id">
                                            <input type="number" :name="'items[' + idx + '][receive_qty]'" x-model.number="item.receive_qty"
                                                step="0.01" min="0" :max="item.remaining_qty"
                                                class="w-full text-right text-xs font-bold rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-between items-center text-xs font-bold px-1 pt-1">
                        <div>Receiving Total Qty: <span class="text-emerald-600 font-black" x-text="getTotalReceivingQty()"></span></div>
                        <div class="text-sm font-black font-mono text-gray-900 dark:text-gray-100">
                            Shipment Value: Rs. <span x-text="getTotalReceivingValue().toFixed(2)"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Shipment / Truck / Bilty Note</label>
                        <input type="text" name="note" placeholder="e.g. Truck #LEA-9920, Cargo driver receipt"
                            class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500">
                    </div>

                    <div class="pt-4 flex justify-end gap-2 border-t dark:border-gray-700">
                        <button type="button" @click="receiveModalOpen = false" class="px-4 py-2 text-xs font-bold rounded-xl border dark:border-gray-600">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white uppercase tracking-wider">
                            Confirm Goods Receipt
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @php
        $receivingItemsData = [];
        foreach ($purchaseOrder->items as $item) {
            if ($item->getRemainingQty() > 0) {
                $receivingItemsData[] = [
                    'po_item_id' => $item->id,
                    'product_name' => $item->product->name ?? 'Product',
                    'ordered_qty' => (float)$item->ordered_qty,
                    'already_received' => (float)$item->received_qty,
                    'remaining_qty' => (float)$item->getRemainingQty(),
                    'unit_price' => (float)$item->unit_price,
                    'receive_qty' => (float)$item->getRemainingQty(),
                ];
            }
        }
    @endphp

    <script>
        function poShowData() {
            return {
                receiveModalOpen: false,
                receivingDate: '{{ now()->toDateString() }}',
                warehouseId: '{{ $purchaseOrder->warehouse_id }}',
                receivingItems: @json($receivingItemsData),

                getTotalReceivingQty() {
                    return this.receivingItems.reduce((sum, i) => sum + (parseFloat(i.receive_qty) || 0), 0);
                },

                getTotalReceivingValue() {
                    return this.receivingItems.reduce((sum, i) => sum + ((parseFloat(i.receive_qty) || 0) * (parseFloat(i.unit_price) || 0)), 0);
                }
            };
        }
    </script>
</x-app-layout>
