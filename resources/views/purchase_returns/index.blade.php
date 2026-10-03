<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">{{ __('Purchase Returns') }}
                        </h3>
                        <a href="{{ route('purchase-returns.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Purchase Return
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
                        <form action="{{ route('purchase-returns.index') }}" method="GET"
                            class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label for="search"
                                    class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">Vendor
                                    Name</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                    placeholder="Search..."
                                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-orange-500 focus:border-orange-500 dark:text-gray-100 italic">
                            </div>

                            <div>
                                <label for="warehouse_id"
                                    class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">Warehouse</label>
                                <select name="warehouse_id" id="warehouse_id"
                                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-orange-500 focus:border-orange-500 dark:text-gray-100">
                                    <option value="">All Warehouses</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}"
                                            {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="start_date"
                                    class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">Start
                                    Date</label>
                                <input type="date" name="start_date" id="start_date"
                                    value="{{ request('start_date') }}"
                                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-orange-500 focus:border-orange-500 dark:text-gray-100">
                            </div>

                            <div>
                                <label for="end_date"
                                    class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">End
                                    Date</label>
                                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-orange-500 focus:border-orange-500 dark:text-gray-100">
                            </div>

                            <div class="flex items-end gap-2">
                                <button type="submit"
                                    class="flex-1 bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded-lg text-xs uppercase tracking-widest transition shadow-lg">
                                    Filter
                                </button>
                                <a href="{{ route('purchase-returns.index') }}"
                                    class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-bold py-2 px-4 rounded-lg text-xs uppercase tracking-widest transition">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        ID</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Date</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Original Purchase</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Vendor</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Warehouse</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Total Amount</th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($purchaseReturns as $return)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            #{{ $return->id }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($return->date)->format('d M, Y') }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <a href="{{ route('purchases.show', $return->purchase_id) }}"
                                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                                Purchase #{{ $return->purchase_id }}
                                            </a>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $return->party->name }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $return->warehouse->name }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-orange-600 dark:text-orange-400">
                                            {{ $settings->currency_symbol }}
                                            {{ number_format($return->total_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <div class="flex justify-center gap-2">
                                                <a href="{{ route('purchase-returns.show', $return) }}"
                                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                                                        </path>
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                        </path>
                                                    </svg>
                                                </a>
                                                <form action="{{ route('purchase-returns.destroy', $return) }}"
                                                    method="POST" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-red-600 hover:text-red-900 dark:text-red-400">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7"
                                            class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            No purchase returns found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $purchaseReturns->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
