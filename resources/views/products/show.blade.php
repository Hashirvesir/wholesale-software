<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('products.index') }}" class="text-gray-500 hover:text-gray-700 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 italic">{{ $product->name }}
                        ({{ $product->sku }})</h3>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('products.edit', $product) }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition shadow-md">
                        {{ __('Edit Product') }}
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Product Details -->
                <div class="md:col-span-2 space-y-6">
                    <div
                        class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-6">
                            <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-4">Product
                                Information</h4>
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <p class="text-xs text-gray-500 font-bold uppercase">Category</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $product->category->name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-bold uppercase">Brand</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $product->brand->name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-bold uppercase">Sale Price</p>
                                    <p class="text-lg font-black text-primary-600">Rs.
                                        {{ number_format($product->sale_price, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-bold uppercase">Unit</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $product->unit->name }} ({{ $product->unit->short_name }})</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-bold uppercase">Stock Alert Quantity</p>
                                    <p class="text-sm font-black text-yellow-500">{{ $product->alert_quantity }}
                                        {{ $product->unit->short_name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-bold uppercase">Status</p>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $product->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <div class="col-span-2 mt-4 pt-4 border-t dark:border-gray-700">
                                    <p class="text-xs text-gray-500 font-bold uppercase mb-2">Product Barcode</p>
                                    <div class="flex items-center space-x-6">
                                        <div id="product-barcode-container"
                                            class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm inline-block">
                                            {!! $product->getBarcodeSVG() !!}
                                            <p
                                                class="text-center text-[10px] font-mono mt-1 text-gray-900 tracking-[0.2em]">
                                                {{ $product->barcode ?? $product->sku }}</p>
                                        </div>
                                        <button onclick="printBarcode()"
                                            class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md text-xs font-bold uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-600 transition shadow-sm">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                                </path>
                                            </svg>
                                            Print Label
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        function printBarcode() {
                            const barcodeHtml = document.getElementById('product-barcode-container').innerHTML;
                            const win = window.open('', 'Print Barcode', 'height=400,width=600');
                            win.document.write('<html><head><title>Print Barcode</title>');
                            win.document.write(`
                                <style>
                                    @page { 
                                        size: 1.5in 1in; 
                                        margin: 0; 
                                    }
                                    body { 
                                        margin: 0; 
                                        display: flex; 
                                        justify-content: center; 
                                        align-items: center; 
                                        height: 1in; 
                                        width: 1.5in;
                                        font-family: 'Courier New', Courier, monospace;
                                        overflow: hidden;
                                    }
                                    .label { 
                                        text-align: center; 
                                        width: 1.5in; 
                                        height: 1in; 
                                        display: flex; 
                                        flex-direction: column; 
                                        justify-content: center; 
                                        align-items: center; 
                                        padding: 2px;
                                        box-sizing: border-box;
                                    }
                                    .product-name { 
                                        font-size: 8px; 
                                        font-weight: bold; 
                                        white-space: nowrap; 
                                        overflow: hidden; 
                                        text-overflow: ellipsis; 
                                        width: 100%;
                                        margin-bottom: 2px;
                                        text-transform: uppercase;
                                    }
                                    .barcode-svg svg { 
                                        width: 1.3in; 
                                        height: 0.4in; 
                                    }
                                    .price { 
                                        font-size: 10px; 
                                        font-weight: 900; 
                                        margin-top: 2px;
                                    }
                                    .sku-text {
                                        font-size: 7px;
                                        letter-spacing: 1px;
                                    }
                                </style>
                            `);
                            win.document.write('</head><body>');
                            win.document.write('<div class="label">');
                            win.document.write('<div class="product-name">{{ $product->name }}</div>');
                            win.document.write('<div class="barcode-svg">' + barcodeHtml + '</div>');
                            win.document.write('<div class="price">Rs. {{ number_format($product->sale_price, 0) }}</div>');
                            win.document.write('</div>');
                            win.document.write('</body></html>');
                            win.document.close();
                            win.focus();
                            setTimeout(() => {
                                win.print();
                                win.close();
                            }, 500);
                        }
                    </script>

                    <!-- Stock Stats -->
                    <div
                        class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-6">
                            <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-4">Stock Breakdown
                                by Warehouse</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr>
                                            <th
                                                class="text-left text-xs font-black text-gray-500 uppercase tracking-widest py-3">
                                                Warehouse</th>
                                            <th
                                                class="text-right text-xs font-black text-gray-500 uppercase tracking-widest py-3">
                                                Current Stock</th>
                                            <th
                                                class="text-right text-xs font-black text-gray-500 uppercase tracking-widest py-3">
                                                Avg. Cost</th>
                                            <th
                                                class="text-right text-xs font-black text-gray-500 uppercase tracking-widest py-3">
                                                Inventory Value</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @php
                                            $totalQty = 0;
                                            $totalValue = 0;
                                        @endphp
                                        @forelse($product->stocks as $stock)
                                            @php
                                                $totalQty += $stock->qty;
                                                $value = $stock->qty * $stock->avg_cost;
                                                $totalValue += $value;
                                            @endphp
                                            <tr>
                                                <td class="py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $stock->warehouse->name }}</td>
                                                <td class="py-4 text-right text-sm font-black text-indigo-600">
                                                    {{ number_format($stock->qty, 2) }}</td>
                                                <td
                                                    class="py-4 text-right text-sm text-gray-500 uppercase font-mono italic">
                                                    Rs. {{ number_format($stock->avg_cost, 2) }}</td>
                                                <td
                                                    class="py-4 text-right text-sm font-bold text-gray-900 dark:text-gray-100 uppercase font-mono italic">
                                                    Rs. {{ number_format($value, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4"
                                                    class="py-4 text-center text-sm text-gray-500 italic uppercase">No
                                                    stock found in any warehouse.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="bg-gray-50 dark:bg-gray-900/50">
                                        <tr>
                                            <td class="py-3 px-4 text-sm font-black text-gray-900 dark:text-gray-100">
                                                TOTAL</td>
                                            <td class="py-3 text-right text-sm font-black text-indigo-600">
                                                {{ number_format($totalQty, 2) }}</td>
                                            <td class="py-3"></td>
                                            <td
                                                class="py-3 text-right text-sm font-black text-gray-900 dark:text-gray-100 uppercase font-mono">
                                                Rs. {{ number_format($totalValue, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Purchase History -->
                    <div
                        class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-6">
                            <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-4">Purchase History</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                                    <thead>
                                        <tr>
                                            <th class="text-left text-xs font-black text-gray-500 uppercase tracking-widest py-3 pr-4">Date</th>
                                            <th class="text-left text-xs font-black text-gray-500 uppercase tracking-widest py-3 pr-4">Invoice</th>
                                            <th class="text-left text-xs font-black text-gray-500 uppercase tracking-widest py-3 pr-4">Vendor</th>
                                            <th class="text-left text-xs font-black text-gray-500 uppercase tracking-widest py-3 pr-4">Warehouse</th>
                                            <th class="text-right text-xs font-black text-gray-500 uppercase tracking-widest py-3 pr-4">Qty</th>
                                            <th class="text-right text-xs font-black text-gray-500 uppercase tracking-widest py-3 pr-4">Price/Unit</th>
                                            <th class="text-right text-xs font-black text-orange-500 uppercase tracking-widest py-3 pr-4">Cargo</th>
                                            <th class="text-right text-xs font-black text-gray-500 uppercase tracking-widest py-3 pr-4">Unit Cost</th>
                                            <th class="text-right text-xs font-black text-gray-500 uppercase tracking-widest py-3">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @forelse($purchaseItems as $pi)
                                            @php
                                                $unitCost = $pi->qty > 0
                                                    ? $pi->price_pkr + ($pi->cargo_amount / $pi->qty)
                                                    : $pi->price_pkr;
                                            @endphp
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                                <td class="py-3 pr-4 text-gray-600 dark:text-gray-400">{{ $pi->purchase->date }}</td>
                                                <td class="py-3 pr-4">
                                                    <a href="{{ route('purchases.show', $pi->purchase_id) }}"
                                                        class="text-primary-600 hover:underline font-bold">
                                                        #{{ $pi->purchase_id }}
                                                    </a>
                                                </td>
                                                <td class="py-3 pr-4 text-gray-700 dark:text-gray-300">{{ $pi->purchase->party->name }}</td>
                                                <td class="py-3 pr-4 text-gray-600 dark:text-gray-400">{{ $pi->purchase->warehouse->name }}</td>
                                                <td class="py-3 pr-4 text-right font-bold text-indigo-600">{{ number_format($pi->qty, 2) }}</td>
                                                <td class="py-3 pr-4 text-right text-gray-600 dark:text-gray-400 font-mono">Rs. {{ number_format($pi->price_pkr, 2) }}</td>
                                                <td class="py-3 pr-4 text-right font-mono {{ $pi->cargo_amount > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-300 dark:text-gray-600' }}">
                                                    {{ $pi->cargo_amount > 0 ? 'Rs. ' . number_format($pi->cargo_amount, 2) : '—' }}
                                                </td>
                                                <td class="py-3 pr-4 text-right font-mono font-bold text-gray-700 dark:text-gray-300">Rs. {{ number_format($unitCost, 2) }}</td>
                                                <td class="py-3 text-right font-black text-gray-900 dark:text-gray-100 font-mono">Rs. {{ number_format($pi->total, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="py-6 text-center text-sm text-gray-400 italic uppercase">No purchase history found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Card -->
                <div class="space-y-6">
                    <div
                        class="bg-gradient-to-br from-primary-600 to-indigo-700 rounded-3xl p-8 shadow-2xl text-white relative overflow-hidden">
                        <div class="relative z-10">
                            <h4 class="text-xs font-black uppercase tracking-[0.2em] opacity-80 mb-2 italic">Total On
                                Hand Stock</h4>
                            <p class="text-5xl font-black tracking-tighter mb-1">
                                {{ number_format($product->getCurrentStock(), 2) }}</p>
                            <p class="text-sm font-bold opacity-80 uppercase tracking-widest italic">
                                {{ $product->unit->name }}</p>

                            <div class="mt-8 pt-8 border-t border-white/20">
                                <h4 class="text-xs font-black uppercase tracking-[0.2em] opacity-80 mb-2 italic">
                                    Estimated Inventory Value</h4>
                                <p class="text-2xl font-black uppercase font-mono italic">Rs.
                                    {{ number_format($totalValue, 2) }}</p>
                            </div>
                        </div>
                        <div class="absolute -right-10 -bottom-10 opacity-10">
                            <svg class="w-48 h-48" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z">
                                </path>
                            </svg>
                        </div>
                    </div>

                    @if ($product->getCurrentStock() <= $product->alert_quantity)
                        <div
                            class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-6">
                            <div class="flex items-center space-x-3 text-red-600 dark:text-red-400 mb-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                    </path>
                                </svg>
                                <span class="text-sm font-black uppercase tracking-widest italic">Low Stock Alert</span>
                            </div>
                            <p class="text-xs text-red-500 font-medium">This product has reached or fallen below the
                                alert threshold of {{ $product->alert_quantity }} {{ $product->unit->short_name }}.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
