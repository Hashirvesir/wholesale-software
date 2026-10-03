<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-2xl sm:rounded-2xl border border-gray-200 dark:border-gray-700">
                <div class="p-8">
                    <div class="flex justify-between items-start mb-8 border-b dark:border-gray-700 pb-6">
                        <div>
                            <h2 class="text-3xl font-black text-gray-900 dark:text-gray-100 tracking-tight uppercase">
                                Stock Transfer</h2>
                            <p class="text-sm text-gray-500 font-bold italic">Reference #{{ $stockTransfer->id }}</p>
                        </div>
                        <div class="text-right">
                            <div class="font-black uppercase text-gray-400 mb-1 tracking-widest text-xs">Transfer
                                Date</div>
                            <div class="text-lg font-bold text-primary-600">{{ $stockTransfer->date }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-12">
                        <div
                            class="bg-red-50 dark:bg-red-950/20 p-6 rounded-2xl border border-red-100 dark:border-red-900/30">
                            <h4
                                class="text-xs font-black uppercase tracking-widest text-red-600 dark:text-red-400 mb-4 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Source (From)
                            </h4>
                            <div class="text-xl font-black text-gray-800 dark:text-gray-200">
                                {{ $stockTransfer->fromWarehouse->name }}</div>
                            <div class="text-sm text-gray-500 mt-1">{{ $stockTransfer->fromWarehouse->address }}</div>
                        </div>

                        <div
                            class="bg-green-50 dark:bg-green-950/20 p-6 rounded-2xl border border-green-100 dark:border-green-900/30">
                            <h4
                                class="text-xs font-black uppercase tracking-widest text-green-600 dark:text-green-400 mb-4 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Destination (To)
                            </h4>
                            <div class="text-xl font-black text-gray-800 dark:text-gray-200">
                                {{ $stockTransfer->toWarehouse->name }}</div>
                            <div class="text-sm text-gray-500 mt-1">{{ $stockTransfer->toWarehouse->address }}</div>
                        </div>
                    </div>

                    <div class="mb-12">
                        <h4 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-4">Transferred Items
                        </h4>
                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Product</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Quantity</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($stockTransfer->items as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                                    {{ $item->product->name }}</div>
                                                <div class="text-[10px] text-gray-500 font-mono">
                                                    {{ $item->product->sku }}</div>
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-indigo-600 dark:text-indigo-400">
                                                {{ number_format($item->qty, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if ($stockTransfer->note)
                        <div
                            class="mb-12 bg-gray-50 dark:bg-gray-900/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-800">
                            <h4 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-2">Note</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-300 italic">"{{ $stockTransfer->note }}"</p>
                        </div>
                    @endif

                    <div class="flex justify-between items-center border-t dark:border-gray-700 pt-8 mt-12">
                        <div class="text-xs text-gray-400 font-bold uppercase tracking-tighter">
                            Processed by: <span
                                class="text-gray-600 dark:text-gray-300">{{ $stockTransfer->user->name }}</span>
                        </div>
                        <div class="flex gap-4">
                            <a href="{{ route('stock-transfers.index') }}"
                                class="px-6 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl text-gray-700 dark:text-gray-200 text-xs font-black uppercase tracking-widest transition shadow-sm">Back</a>
                            <a href="{{ route('stock-transfers.edit', $stockTransfer) }}"
                                class="px-6 py-2 bg-primary-600 hover:bg-primary-700 rounded-xl text-white text-xs font-black uppercase tracking-widest transition shadow-md">Edit</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
