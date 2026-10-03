<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 italic">Inventory Adjustments</h3>
                <a href="{{ route('stock-adjustments.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    {{ __('New Adjustment') }}
                </a>
            </div>

            @if (session('success'))
                <div
                    class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 dark:bg-green-900 dark:text-green-300 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Search & Filter Bar -->
            <div
                class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 mb-6">
                <form action="{{ route('stock-adjustments.index') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="warehouse_id"
                            class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">Warehouse</label>
                        <select name="warehouse_id" id="warehouse_id"
                            class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:text-gray-100">
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
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                            class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:text-gray-100">
                    </div>
                    <div>
                        <label for="end_date"
                            class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">End
                            Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                            class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:text-gray-100">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-xs uppercase tracking-widest transition shadow-lg">Filter</button>
                        <a href="{{ route('stock-adjustments.index') }}"
                            class="flex-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold py-2 px-4 rounded-lg text-xs text-center uppercase tracking-widest transition">Reset</a>
                    </div>
                </form>
            </div>

            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-0 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th
                                        class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Date</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Warehouse</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Items</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Adjusted By</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-right text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($adjustments as $adj)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $adj->date }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-700 dark:text-gray-300">
                                            {{ $adj->warehouse->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                                {{ $adj->items->count() }} Items
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $adj->user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <x-dropdown align="right" width="48">
                                                <x-slot name="trigger">
                                                    <button
                                                        class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition duration-150 ml-auto">
                                                        <svg class="h-5 w-5 fill-current" viewBox="0 0 20 20">
                                                            <path
                                                                d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                                        </svg>
                                                    </button>
                                                </x-slot>
                                                <x-slot name="content">
                                                    <x-dropdown-link :href="route('stock-adjustments.show', $adj)" class="flex items-center">
                                                        <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        {{ __('View Adjustment') }}
                                                    </x-dropdown-link>
                                                    <div class="border-t border-gray-100 dark:border-gray-700"></div>
                                                    <form action="{{ route('stock-adjustments.destroy', $adj) }}"
                                                        method="POST" id="delete-adj-{{ $adj->id }}">
                                                        @csrf @method('DELETE')
                                                        <x-dropdown-link :href="route('stock-adjustments.destroy', $adj)"
                                                            onclick="event.preventDefault(); if(confirm('Reverse/Delete this adjustment?')) { document.getElementById('delete-adj-{{ $adj->id }}').submit(); }"
                                                            class="text-red-600 flex items-center">
                                                            <svg class="w-4 h-4 mr-2" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                            {{ __('Delete/Reverse') }}
                                                        </x-dropdown-link>
                                                    </form>
                                                </x-slot>
                                            </x-dropdown>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 italic">No
                                            adjustments found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @if ($adjustments->hasPages())
                <div class="mt-4">{{ $adjustments->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
