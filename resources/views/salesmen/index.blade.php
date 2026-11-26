<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12">
        <div class="max-w-[1750px] mx-auto px-2 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 space-y-4 md:space-y-0">
                <div>
                    <h3 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter italic">
                        Salesmen Management
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-medium">
                        Manage sales representatives, track customer assignment, and monitor sales
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('salesmen.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-900 focus:outline-none transition ease-in-out duration-150 shadow-lg shadow-primary-200 dark:shadow-none">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add New Salesman
                    </a>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm mb-6 border border-gray-200 dark:border-gray-700">
                <form action="{{ route('salesmen.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <x-text-input name="search" placeholder="Search by name, phone or email..." class="w-full text-xs font-bold"
                            :value="request('search')" />
                    </div>
                    <div>
                        <select name="status"
                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 rounded-xl text-xs font-bold shadow-sm">
                            <option value="">All Statuses</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="flex space-x-2">
                        <x-primary-button type="submit" class="w-full justify-center text-xs">Filter</x-primary-button>
                        <a href="{{ route('salesmen.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-xl font-bold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 dark:bg-green-900/40 dark:text-green-300 rounded-r-xl shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Salesmen Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50/75 dark:bg-gray-900/50">
                            <tr class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                <th class="px-6 py-4 text-left">Salesman Name</th>
                                <th class="px-6 py-4 text-left">Phone & Email</th>
                                <th class="px-6 py-4 text-center">Assigned Customers</th>
                                <th class="px-6 py-4 text-center">Total Invoices</th>
                                <th class="px-6 py-4 text-center">Commission %</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm font-medium">
                            @forelse ($salesmen as $salesman)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition duration-75">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 font-black flex items-center justify-center mr-3 text-xs uppercase shadow-sm">
                                                {{ substr($salesman->name, 0, 2) }}
                                            </div>
                                            <div>
                                                <span class="font-bold text-gray-900 dark:text-white">{{ $salesman->name }}</span>
                                                @if($salesman->address)
                                                    <p class="text-[10px] text-gray-400 truncate max-w-xs">{{ $salesman->address }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs">
                                        <div class="font-bold text-gray-700 dark:text-gray-300">{{ $salesman->phone ?? '-' }}</div>
                                        <div class="text-[10px] text-gray-400">{{ $salesman->email ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2.5 py-1 text-xs font-black rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800">
                                            {{ $salesman->customers_count }} Customers
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2.5 py-1 text-xs font-black rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                            {{ $salesman->sales_count }} Sales
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-xs font-bold text-gray-800 dark:text-gray-200">
                                        {{ number_format($salesman->commission_rate, 2) }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if ($salesman->status)
                                            <span class="px-2 py-0.5 text-[10px] font-black uppercase rounded-full bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">Active</span>
                                        @else
                                            <span class="px-2 py-0.5 text-[10px] font-black uppercase rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-bold space-x-2">
                                        <a href="{{ route('salesmen.edit', $salesman) }}"
                                            class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 font-bold">
                                            Edit
                                        </a>
                                        <form action="{{ route('salesmen.destroy', $salesman) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Are you sure you want to delete salesman {{ $salesman->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 font-bold ml-2">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-400 italic">
                                        No salesmen found. Click "Add New Salesman" above to create one.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($salesmen->hasPages())
                    <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                        {{ $salesmen->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
