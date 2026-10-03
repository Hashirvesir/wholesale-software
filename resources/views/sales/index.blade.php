<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">{{ __('Sales Invoices') }}</h3>
                        <a href="{{ route('sales.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add New Sale
                        </a>
                    </div>

                    @if (session('success'))
                        <div
                            class="mb-4 px-4 py-3 rounded-lg bg-green-100 dark:bg-green-900 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-200">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Search & Filter Bar -->
                    <div
                        class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-100 dark:border-gray-700 mb-6">
                        <form action="{{ route('sales.index') }}" method="GET"
                            class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <!-- Search -->
                            <div>
                                <label for="search"
                                    class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">Customer
                                    Name</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                    placeholder="Search..."
                                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100 italic">
                            </div>

                            <!-- Warehouse Filter -->
                            <div>
                                <label for="warehouse_id"
                                    class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">Warehouse</label>
                                <select name="warehouse_id" id="warehouse_id"
                                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100">
                                    <option value="">All Warehouses</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}"
                                            {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Start Date -->
                            <div>
                                <label for="start_date"
                                    class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">Start
                                    Date</label>
                                <input type="date" name="start_date" id="start_date"
                                    value="{{ request('start_date') }}"
                                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100">
                            </div>

                            <!-- End Date -->
                            <div>
                                <label for="end_date"
                                    class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">End
                                    Date</label>
                                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100">
                            </div>

                            <!-- Actions -->
                            <div class="flex items-end gap-2">
                                <button type="submit"
                                    class="flex-1 bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded-lg text-xs uppercase tracking-widest transition shadow-lg">
                                    Filter
                                </button>
                                <a href="{{ route('sales.index') }}"
                                    class="flex-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold py-2 px-4 rounded-lg text-xs text-center uppercase tracking-widest transition">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        ID</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Date</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Customer</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Warehouse</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Total Qty</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Total</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Paid</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Payment Method</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Balance</th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($sales as $sale)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-bold text-primary-600 dark:text-primary-400">
                                            {{ $settings->invoice_prefix }}{{ $sale->id }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $sale->date }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $sale->party->name }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $sale->warehouse->name }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-700 dark:text-gray-300">
                                            {{ number_format($sale->items->sum('qty'), 2) }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-gray-100">
                                            {{ $settings->currency_symbol }}
                                            {{ number_format($sale->total_amount, 2) }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-green-600 dark:text-green-400 font-bold">
                                            {{ $settings->currency_symbol }}
                                            {{ number_format($sale->paid_amount, 2) }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 italic">
                                            {{ $sale->account->name ?? 'N/A' }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $sale->total_amount - $sale->paid_amount > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-500' }}">
                                            {{ $settings->currency_symbol }}
                                            {{ number_format($sale->total_amount - $sale->paid_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <x-dropdown align="right" width="48">
                                                <x-slot name="trigger">
                                                    <button
                                                        class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition duration-150 ml-auto">
                                                        <svg class="h-5 w-5 fill-current" viewBox="0 0 20 20">
                                                            <path
                                                                d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                                        </svg>
                                                    </button>
                                                </x-slot>
                                                <x-slot name="content">
                                                    @can('view sales')
                                                        <x-dropdown-link :href="route('sales.show', $sale)" class="flex items-center">
                                                            <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                            {{ __('View Invoice') }}
                                                        </x-dropdown-link>
                                                    @endcan

                                                    @can('edit sales')
                                                        <x-dropdown-link :href="route('sales.edit', $sale)" class="flex items-center">
                                                            <svg class="w-4 h-4 mr-2 text-yellow-500" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                            {{ __('Edit Invoice') }}
                                                        </x-dropdown-link>
                                                    @endcan
                                                    @can('return sales')
                                                        <x-dropdown-link :href="route('sales.return', $sale)" class="flex items-center">
                                                            <svg class="w-4 h-4 mr-2 text-red-500" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                            </svg>
                                                            {{ __('Return') }}
                                                        </x-dropdown-link>
                                                    @endcan

                                                    @can('view sales')
                                                        <x-dropdown-link :href="route('sales.pdf', $sale)" class="flex items-center">
                                                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                            </svg>
                                                            {{ __('Download PDF') }}
                                                        </x-dropdown-link>
                                                        <x-dropdown-link :href="route('sales.thermal', $sale)" target="_blank"
                                                            class="flex items-center">
                                                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                            </svg>
                                                            {{ __('Thermal Receipt') }}
                                                        </x-dropdown-link>
                                                    @endcan
                                                    @can('delete sales')
                                                        <div class="border-t border-gray-100 dark:border-gray-700"></div>
                                                        <form action="{{ route('sales.destroy', $sale) }}" method="POST"
                                                            id="delete-sale-{{ $sale->id }}">
                                                            @csrf @method('DELETE')
                                                            <x-dropdown-link :href="route('sales.destroy', $sale)"
                                                                onclick="event.preventDefault(); if(confirm('Delete this sale?')) { document.getElementById('delete-sale-{{ $sale->id }}').submit(); }"
                                                                class="text-red-600 flex items-center">
                                                                <svg class="w-4 h-4 mr-2" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                                {{ __('Delete') }}
                                                            </x-dropdown-link>
                                                        </form>
                                                    @endcan
                                                </x-slot>
                                            </x-dropdown>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9"
                                            class="px-6 py-8 text-center text-gray-500 dark:text-gray-400 italic">
                                            No sales found. Create your first sale!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($sales->hasPages())
                        <div class="mt-4">
                            {{ $sales->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
