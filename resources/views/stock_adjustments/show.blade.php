<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-2xl sm:rounded-2xl border border-gray-200 dark:border-gray-700">
                <div class="p-8">
                    <div class="flex justify-between items-start mb-8 border-b dark:border-gray-700 pb-6">
                        <div>
                            <h2 class="text-3xl font-black text-gray-900 dark:text-gray-100 tracking-tight uppercase">
                                Stock Adjustment</h2>
                            <p class="text-sm text-gray-500 font-bold italic">Reference #{{ $stockAdjustment->id }}</p>
                        </div>
                        <div class="text-right">
                            <div class="font-black uppercase text-gray-400 mb-1 tracking-widest text-xs">
                                Adjustment Date</div>
                            <div class="text-lg font-bold text-indigo-600">{{ $stockAdjustment->date }}</div>
                        </div>
                    </div>

                    <div
                        class="mb-8 p-6 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl flex justify-between items-center">
                        <div>
                            <div class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Warehouse
                                Location</div>
                            <div class="text-xl font-bold text-gray-800 dark:text-gray-200">
                                {{ $stockAdjustment->warehouse->name }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Adjustment By
                            </div>
                            <div class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                {{ $stockAdjustment->user->name }}</div>
                        </div>
                    </div>

                    <div class="mb-12">
                        <h4 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-4">Adjusted Items</h4>
                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Product</th>
                                        <th
                                            class="px-6 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Type</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Quantity</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($stockAdjustment->items as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                                    {{ $item->product->name }}</div>
                                                <div class="text-[10px] text-gray-500 font-mono">
                                                    {{ $item->product->sku }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if ($item->type === 'addition')
                                                    <span
                                                        class="px-2 py-1 bg-green-100 text-green-800 text-[10px] font-black uppercase rounded-lg">Addition
                                                        (+)
                                                    </span>
                                                @else
                                                    <span
                                                        class="px-2 py-1 bg-red-100 text-red-800 text-[10px] font-black uppercase rounded-lg">Subtraction
                                                        (-)</span>
                                                @endif
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-right text-sm font-black {{ $item->type === 'addition' ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $item->type === 'addition' ? '+' : '-' }}{{ number_format($item->qty, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if ($stockAdjustment->note)
                        <div
                            class="mb-12 bg-indigo-50 dark:bg-indigo-950/20 p-6 rounded-2xl border border-indigo-100 dark:border-indigo-900/30">
                            <h4 class="text-xs font-black uppercase tracking-widest text-indigo-600 mb-2">Note / Reason
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-300 italic">"{{ $stockAdjustment->note }}"
                            </p>
                        </div>
                    @endif

                    <div class="flex justify-between items-center border-t dark:border-gray-700 pt-8 mt-12">
                        <a href="{{ route('stock-adjustments.index') }}"
                            class="px-6 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl text-gray-700 dark:text-gray-200 text-xs font-black uppercase tracking-widest transition shadow-sm">Back
                            to List</a>
                        <form action="{{ route('stock-adjustments.destroy', $stockAdjustment) }}" method="POST"
                            onsubmit="return confirm('Reverse this adjustment?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="px-6 py-2 bg-red-600 hover:bg-red-700 rounded-xl text-white text-xs font-black uppercase tracking-widest transition shadow-md">Reverse
                                & Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
