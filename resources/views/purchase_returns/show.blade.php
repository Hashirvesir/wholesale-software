<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl p-8 mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <!-- Header -->
                <div class="p-8 border-b dark:border-gray-700">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h1
                                class="text-2xl font-black text-orange-600 dark:text-orange-400 uppercase tracking-tighter">
                                PURCHASE RETURN
                            </h1>
                            <p class="text-sm text-gray-500 mt-1">Return ID: <span
                                    class="font-bold text-gray-700 dark:text-gray-300">#{{ $purchaseReturn->id }}</span>
                            </p>
                            <p class="text-sm text-gray-500">Date: <span
                                    class="font-bold text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($purchaseReturn->date)->format('d M, Y') }}</span>
                            </p>
                        </div>
                        <div class="text-right">
                            <a href="{{ route('purchase-returns.index') }}"
                                class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                                ← Back to List
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-8">
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Vendor
                                Information</h4>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                {{ $purchaseReturn->party->name }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $purchaseReturn->party->phone }}</p>
                            <p class="text-sm text-gray-500 italic">{{ $purchaseReturn->party->address }}</p>
                        </div>
                        <div class="text-right">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Original Purchase
                            </h4>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                <a href="{{ route('purchases.show', $purchaseReturn->purchase_id) }}"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                    Purchase #{{ $purchaseReturn->purchase_id }}
                                </a>
                            </p>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 mt-4">Warehouse
                            </h4>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                {{ $purchaseReturn->warehouse->name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="p-0">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-orange-50 dark:bg-orange-900/20">
                            <tr>
                                <th
                                    class="px-8 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">
                                    Product Details</th>
                                <th
                                    class="px-8 py-4 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">
                                    Qty Returned</th>
                                <th
                                    class="px-8 py-4 text-right text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">
                                    Cost</th>
                                <th
                                    class="px-8 py-4 text-right text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">
                                    Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($purchaseReturn->items as $item)
                                <tr>
                                    <td class="px-8 py-4">
                                        <div class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            {{ $item->product->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">SKU:
                                            {{ $item->product->sku }}</div>
                                    </td>
                                    <td class="px-8 py-4 text-center text-sm text-gray-700 dark:text-gray-300">
                                        {{ number_format($item->qty, 2) }} {{ $item->product->unit->name ?? '' }}
                                    </td>
                                    <td class="px-8 py-4 text-right text-sm text-gray-700 dark:text-gray-300">
                                        Rs. {{ number_format($item->cost, 2) }}
                                    </td>
                                    <td class="px-8 py-4 text-right text-sm font-bold text-gray-900 dark:text-gray-100">
                                        Rs. {{ number_format($item->total, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <td colspan="3"
                                    class="px-8 py-4 text-right text-sm font-black text-gray-900 dark:text-gray-100 uppercase">
                                    Total Return Amount:
                                </td>
                                <td
                                    class="px-8 py-4 text-right text-lg font-black text-orange-600 dark:text-orange-400">
                                    Rs. {{ number_format($purchaseReturn->total_amount, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Note -->
                @if ($purchaseReturn->note)
                    <div class="p-8 border-t dark:border-gray-700">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Note</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $purchaseReturn->note }}</p>
                    </div>
                @endif

                <!-- Actions -->
                <div class="p-8 border-t dark:border-gray-700 flex justify-between items-center print:hidden">
                    <form action="{{ route('purchase-returns.destroy', $purchaseReturn) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this purchase return? This will reverse the stock and accounting entries.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                            Delete Return
                        </button>
                    </form>

                    <button onclick="window.print()"
                        class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                            </path>
                        </svg>
                        Print
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
