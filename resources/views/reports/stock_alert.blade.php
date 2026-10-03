<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter">
                        Stock Alert Report
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium italic">
                        Monitor inventory levels and reorder requirements
                    </p>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700">
                    <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Low Stock Items</span>
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">
                        {{ $products->count() }} <span class="text-sm text-gray-500 font-medium">Products</span>
                    </div>
                </div>
                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-red-200 dark:border-red-900/30 bg-red-50/30 dark:bg-red-900/10">
                    <span class="text-xs font-black text-red-600 dark:text-red-400 uppercase tracking-widest">Out of
                        Stock</span>
                    <div class="text-2xl font-black text-red-700 dark:text-red-400 mt-1">
                        {{ $outOfStock->count() }} <span class="text-sm text-gray-500 font-medium">Products</span>
                    </div>
                </div>
                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700">
                    <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Alert Threshold</span>
                    <div class="text-2xl font-black text-gray-800 dark:text-gray-100 mt-1">
                        {{ $threshold }} <span
                            class="text-sm text-gray-500 font-medium">{{ is_numeric($threshold) ? 'Units' : '' }}</span>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div
                class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700 mb-6">
                <form action="{{ route('reports.stock-alert') }}" method="GET" class="flex flex-wrap items-end gap-4">
                    <div class="w-full md:w-64">
                        <x-input-label for="threshold" :value="__('Override Global Threshold (Optional)')" />
                        <x-text-input id="threshold" name="threshold" type="number" min="1"
                            placeholder="Using Product Settings" class="block mt-1 w-full" :value="$customThreshold" />
                    </div>
                    <div class="flex space-x-2">
                        <x-primary-button>
                            {{ __('Update Report') }}
                        </x-primary-button>
                        <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" target="_blank"
                            class="inline-flex items-center px-4 py-2 bg-red-600 dark:bg-red-700 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-widest hover:bg-red-700 dark:hover:bg-red-600 transition shadow-lg shadow-red-200 dark:shadow-none">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                </path>
                            </svg>
                            Download PDF
                        </a>
                        <a href="{{ route('reports.stock-alert') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none transition ease-in-out duration-150">
                            {{ __('Reset to Defaults') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Low Stock Products Table -->
            @if ($products->count() > 0)
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-2xl sm:rounded-3xl border border-amber-200 dark:border-amber-900/30 mb-6">
                    <div
                        class="p-6 bg-amber-50/50 dark:bg-amber-900/10 border-b border-amber-100 dark:border-amber-900/20">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <h3
                                class="text-lg font-black uppercase italic tracking-tighter text-amber-700 dark:text-amber-300">
                                Low Stock Alert
                            </h3>
                        </div>
                    </div>
                    <div class="p-0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr
                                        class="bg-gray-50 dark:bg-gray-700/50 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                        <th class="px-6 py-4 text-left">Product</th>
                                        <th class="px-6 py-4 text-left">Category / Brand</th>
                                        <th class="px-6 py-4 text-right">Current Stock</th>
                                        <th class="px-6 py-4 text-right">Alert Qty</th>
                                        <th class="px-6 py-4 text-right">Suggested Reorder</th>
                                        <th class="px-6 py-4 text-right">Last Purchase</th>
                                        <th class="px-6 py-4 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                    @foreach ($products as $product)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition duration-75">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="font-bold text-gray-900 dark:text-white">
                                                    {{ $product->name }}</div>
                                                <div class="text-xs text-gray-500 font-mono">{{ $product->sku }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                <div class="text-xs font-bold uppercase">
                                                    {{ $product->category->name ?? '-' }}</div>
                                                <div class="text-[10px] uppercase">{{ $product->brand->name ?? '-' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <span
                                                    class="font-black text-lg {{ $product->status_color === 'red' ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400' }}">
                                                    {{ number_format($product->current_stock) }}
                                                </span>
                                                <div class="text-xs text-gray-500">{{ $product->unit->name ?? '' }}
                                                </div>
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-600 dark:text-gray-400">
                                                {{ number_format($product->alert_quantity) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <span class="font-black text-indigo-600 dark:text-indigo-400">
                                                    {{ number_format($product->suggested_reorder) }}
                                                </span>
                                                <div class="text-xs text-gray-500">{{ $product->unit->name ?? '' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-gray-500">
                                                @if ($product->last_purchase_date)
                                                    <div class="text-xs">
                                                        {{ date('d M, Y', strtotime($product->last_purchase_date)) }}
                                                    </div>
                                                    <div class="text-xs font-bold">
                                                        {{ $settings->currency_symbol }}{{ number_format($product->last_purchase_price, 2) }}
                                                    </div>
                                                @else
                                                    <span class="text-xs italic">No purchases</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if ($product->status_color === 'red')
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                                        {{ $product->status_label }}
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                                        {{ $product->status_label }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div
                    class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl p-12 text-center border border-gray-200 dark:border-gray-700 mb-6">
                    <svg class="mx-auto h-16 w-16 text-green-500 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-xl font-black text-gray-800 dark:text-gray-200 mb-2">All Stock Levels Normal</h3>
                    <p class="text-gray-500 dark:text-gray-400">No products below the alert threshold</p>
                </div>
            @endif

            <!-- Out of Stock Products -->
            @if ($outOfStock->count() > 0)
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-2xl sm:rounded-3xl border border-red-200 dark:border-red-900/30">
                    <div class="p-6 bg-red-50/50 dark:bg-red-900/10 border-b border-red-100 dark:border-red-900/20">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3
                                class="text-lg font-black uppercase italic tracking-tighter text-red-700 dark:text-red-300">
                                Out of Stock - Immediate Action Required
                            </h3>
                        </div>
                    </div>
                    <div class="p-0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr
                                        class="bg-gray-50 dark:bg-gray-700/50 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                        <th class="px-6 py-4 text-left">Product</th>
                                        <th class="px-6 py-4 text-left">Category / Brand</th>
                                        <th class="px-6 py-4 text-right">Alert Qty</th>
                                        <th class="px-6 py-4 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                    @foreach ($outOfStock as $product)
                                        <tr class="hover:bg-red-50 dark:hover:bg-red-900/5 transition duration-75">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="font-bold text-gray-900 dark:text-white">
                                                    {{ $product->name }}</div>
                                                <div class="text-xs text-gray-500 font-mono">{{ $product->sku }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                <div class="text-xs font-bold uppercase">
                                                    {{ $product->category->name ?? '-' }}</div>
                                                <div class="text-[10px] uppercase">{{ $product->brand->name ?? '-' }}
                                                </div>
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-right font-bold text-gray-600 dark:text-gray-400">
                                                {{ number_format($product->alert_quantity) }}
                                                {{ $product->unit->name ?? '' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <a href="{{ route('purchases.create') }}"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-lg transition">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                    Reorder Now
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
