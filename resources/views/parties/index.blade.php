<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12">
        <div class="max-w-[1750px] mx-auto px-2 sm:px-6 lg:px-8">
            <div
                class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 space-y-4 md:space-y-0">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 italic">Manage Parties
                    (Customers/Vendors/Cargo)</h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route('parties.export.pdf', request()->query()) }}" target="_blank"
                        class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition shadow-md"
                        title="Download PDF">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                            </path>
                        </svg>
                        PDF
                    </a>
                    <a href="{{ route('parties.export.excel', request()->query()) }}"
                        class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition shadow-md"
                        title="Download Excel">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Excel
                    </a>
                    <a href="{{ route('parties.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-900 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        {{ __('Add New Party') }}
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div
                class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm mb-6 border border-gray-200 dark:border-gray-700">
                <form action="{{ route('parties.index') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-text-input name="search" placeholder="Search by name or phone..." class="w-full"
                            :value="request('search')" />
                    </div>
                    <div>
                        <select name="type"
                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150">
                            <option value="">All Types</option>
                            <option value="customer" {{ request('type') == 'customer' ? 'selected' : '' }}>Customer
                            </option>
                            <option value="vendor" {{ request('type') == 'vendor' ? 'selected' : '' }}>Vendor</option>
                            <option value="cargo" {{ request('type') == 'cargo' ? 'selected' : '' }}>Cargo</option>
                        </select>
                    </div>
                    <div class="flex space-x-2">
                        <x-primary-button type="submit" class="w-full justify-center">Filter</x-primary-button>
                        <a href="{{ route('parties.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 active:bg-gray-400 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-sm">Reset</a>
                    </div>
                </form>
            </div>

            @if (session('success'))
                <div
                    class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 dark:bg-green-900 dark:text-green-300 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-0 text-gray-900 dark:text-gray-100">
                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th
                                        class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Name / Phone</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Type</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Opening Bal.</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-bold text-gray-700 dark:text-gray-100 uppercase tracking-wider">
                                        Current Balance</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Credit Terms</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($parties as $party)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150 cursor-pointer"
                                        ondblclick="window.location='{{ route('reports.party-ledger', ['party_id' => $party->id]) }}'"
                                        title="Double-click to view ledger">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            <div class="flex flex-col">
                                                <span>{{ $party->name }}</span>
                                                <span
                                                    class="text-xs text-gray-400">{{ $party->phone ?? 'No Phone' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="flex flex-col gap-1 items-start">
                                                <span
                                                    class="px-2 py-0.5 rounded text-xs font-bold uppercase {{ $party->type == 'customer'
                                                        ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                                                        : ($party->type == 'vendor'
                                                            ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
                                                            : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200') }}">
                                                    {{ $party->type }}
                                                </span>
                                                @if ($party->type === 'customer')
                                                    @if (($party->price_tier ?? 'wholesale') === 'retail')
                                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300">Retail Rate</span>
                                                    @elseif (($party->price_tier ?? 'wholesale') === 'dealer')
                                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-indigo-100 dark:bg-indigo-900/40 text-indigo-800 dark:text-indigo-300">Dealer Rate</span>
                                                    @else
                                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">Wholesale</span>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="text-gray-500">Rs.
                                                {{ number_format($party->opening_balance, 2) }}</span>
                                            <span
                                                class="text-[10px] uppercase font-bold {{ $party->balance_type == 'credit' ? 'text-red-500' : 'text-green-500' }}">({{ $party->balance_type }})</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @php $currentBal = $party->getCurrentBalance(); @endphp
                                            <span
                                                class="font-black text-lg {{ $currentBal >= 0 ? 'text-indigo-600' : 'text-orange-600' }}">
                                                Rs. {{ number_format(abs($currentBal), 2) }}
                                                <span
                                                    class="text-xs uppercase">{{ $currentBal >= 0 ? 'Dr' : 'Cr' }}</span>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if ($party->type === 'customer')
                                                @if ($party->credit_limit > 0)
                                                    <div class="flex flex-col gap-0.5">
                                                        <div class="flex items-center gap-1.5">
                                                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Rs. {{ number_format($party->credit_limit, 0) }}</span>
                                                            @if ($party->isCreditLimitExceeded())
                                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300">Exceeded</span>
                                                            @else
                                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">Left: {{ number_format($party->getRemainingCredit(), 0) }}</span>
                                                            @endif
                                                        </div>
                                                        <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                                            Due: {{ $party->credit_days > 0 ? $party->credit_days . 'd' : 'Immediate' }}
                                                            @if ($party->hasOverdueInvoices())
                                                                <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-black bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200">Overdue</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-xs text-gray-400 italic">No limit</span>
                                                    @if ($party->credit_days > 0 && $party->hasOverdueInvoices())
                                                        <div class="mt-0.5">
                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200">Overdue</span>
                                                        </div>
                                                    @endif
                                                @endif
                                            @else
                                                <span class="text-xs text-gray-400 italic">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if ($party->status)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Active</span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Inactive</span>
                                            @endif
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
                                                    <x-dropdown-link :href="route('reports.party-ledger', [
                                                        'party_id' => $party->id,
                                                    ])" class="flex items-center">
                                                        <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 17v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2m3.293-7.707a1 1 0 111.414-1.414L9 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4z" />
                                                        </svg>
                                                        {{ __('View Ledger') }}
                                                    </x-dropdown-link>
                                                    <x-dropdown-link :href="route('parties.edit', $party)" class="flex items-center">
                                                        <svg class="w-4 h-4 mr-2 text-yellow-500" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        {{ __('Edit Party') }}
                                                    </x-dropdown-link>
                                                    <div class="border-t border-gray-100 dark:border-gray-700"></div>
                                                    <form action="{{ route('parties.destroy', $party) }}"
                                                        method="POST" id="delete-party-{{ $party->id }}">
                                                        @csrf @method('DELETE')
                                                        <x-dropdown-link :href="route('parties.destroy', $party)"
                                                            onclick="event.preventDefault(); if(confirm('Delete this party?')) { document.getElementById('delete-party-{{ $party->id }}').submit(); }"
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
                                                </x-slot>
                                            </x-dropdown>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400 italic">
                                            No parties found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Parties Card View -->
                    <div class="block md:hidden p-3 space-y-3">
                        @forelse ($parties as $party)
                            @php
                                $balance = $party->current_balance;
                                $rawPhone = preg_replace('/[^0-9]/', '', $party->phone ?? '');
                                $waPhone = null;
                                if ($rawPhone) {
                                    if (str_starts_with($rawPhone, '0')) {
                                        $waPhone = '92' . substr($rawPhone, 1);
                                    } elseif (str_starts_with($rawPhone, '92')) {
                                        $waPhone = $rawPhone;
                                    } else {
                                        $waPhone = '92' . $rawPhone;
                                    }
                                }
                                $waText = "Assalam-o-Alaikum {$party->name}, aapka baqaya balance Rs. " . number_format(abs($balance), 2) . " hai.\nShukriya!";
                            @endphp
                            <div class="bg-gray-50 dark:bg-gray-900/60 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $party->name }}</h4>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $party->phone ?? 'No Phone' }}</span>
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $party->type == 'customer' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : ($party->type == 'vendor' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200') }}">
                                            {{ $party->type }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between py-2 px-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 text-xs">
                                    <span class="text-gray-500 font-medium">Current Balance:</span>
                                    <span class="font-extrabold text-sm {{ $balance > 0 ? 'text-red-600 dark:text-red-400' : ($balance < 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-500') }}">
                                        Rs. {{ number_format(abs($balance), 2) }}
                                        <span class="text-[10px] uppercase font-bold">{{ $balance > 0 ? '(Dr)' : ($balance < 0 ? '(Cr)' : '') }}</span>
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    <a href="{{ route('reports.party-ledger', ['party_id' => $party->id]) }}" 
                                       class="flex-1 py-2 px-3 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 font-bold text-xs rounded-xl text-center hover:bg-indigo-100 transition">
                                        Ledger
                                    </a>

                                    @if ($waPhone)
                                        <a href="https://wa.me/{{ $waPhone }}?text={{ urlencode($waText) }}" target="_blank"
                                           class="flex items-center justify-center gap-1 py-2 px-3 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-bold text-xs rounded-xl hover:bg-emerald-100 transition">
                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                                            WhatsApp
                                        </a>
                                    @endif

                                    @can('edit parties')
                                        <a href="{{ route('parties.edit', $party) }}" 
                                           class="py-2 px-3 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-xl hover:bg-gray-200 transition">
                                            Edit
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-gray-500 dark:text-gray-400 italic bg-gray-50 dark:bg-gray-900/40 rounded-xl">
                                No parties found.
                            </div>
                        @endforelse
                    </div>
                    @if ($parties->hasPages())
                        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                            {{ $parties->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
