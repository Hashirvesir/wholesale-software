<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12">
        <div class="max-w-[1750px] mx-auto px-2 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 italic">Purchase Invoices</h3>
                <a href="{{ route('purchases.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-900 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    {{ __('Create Purchase') }}
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
                <form action="{{ route('purchases.index') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <!-- Search -->
                    <div>
                        <label for="search"
                            class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">Vendor
                            Name</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                            placeholder="Search..."
                            class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100 italic">
                    </div>

                    <!-- Warehouse Filter -->
                    <div>
                        <label for="warehouse_id"
                            class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">Warehouse</label>
                        <select name="warehouse_id" id="warehouse_id"
                            class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100">
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
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                            class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100">
                    </div>

                    <!-- End Date -->
                    <div>
                        <label for="end_date"
                            class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">End
                            Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                            class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100">
                    </div>

                    <!-- Actions -->
                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="flex-1 bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded-lg text-xs uppercase tracking-widest transition shadow-lg">
                            Filter
                        </button>
                        <a href="{{ route('purchases.index') }}"
                            class="flex-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold py-2 px-4 rounded-lg text-xs text-center uppercase tracking-widest transition">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

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
                                        Invoice Info</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Vendor</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Warehouse</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Total Qty</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Amount</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($purchases as $p)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex flex-col">
                                                <span
                                                    class="text-gray-900 dark:text-gray-100 font-bold">{{ $settings->invoice_prefix }}{{ $p->id }}</span>
                                                <span class="text-xs text-gray-500">{{ $p->date }}</span>
                                            </div>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $p->party->name }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $p->warehouse->name }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-700 dark:text-gray-300">
                                            {{ number_format($p->items->sum('qty'), 2) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-primary-600 dark:text-primary-400 font-bold">
                                            <div class="flex flex-col">
                                                <span>{{ $settings->currency_symbol }}
                                                    {{ number_format($p->total_amount, 2) }}</span>
                                                @if ($p->total_amount - $p->paid_amount > 0)
                                                    <span class="text-[10px] text-red-500 font-bold italic">Due:
                                                        {{ $settings->currency_symbol }}
                                                        {{ number_format($p->total_amount - $p->paid_amount, 2) }}</span>
                                                @else
                                                    <span
                                                        class="text-[10px] text-green-500 font-black italic uppercase">Paid</span>
                                                @endif
                                            </div>
                                            @if ($p->currency == 'RMB')
                                                <div class="text-[10px] text-gray-400 italic">(via RMB @
                                                    {{ $p->exchange_rate }})</div>
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
                                                    <x-dropdown-link :href="route('purchases.show', $p)" class="flex items-center">
                                                        <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        {{ __('View Purchase') }}
                                                    </x-dropdown-link>
                                                    <x-dropdown-link :href="route('purchases.edit', $p)" class="flex items-center">
                                                        <svg class="w-4 h-4 mr-2 text-yellow-500" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        {{ __('Edit Purchase') }}
                                                    </x-dropdown-link>
                                                    @can('return purchases')
                                                        <x-dropdown-link :href="route('purchases.return', $p)" class="flex items-center">
                                                            <svg class="w-4 h-4 mr-2 text-red-500" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                            </svg>
                                                            {{ __('Return') }}
                                                        </x-dropdown-link>
                                                    @endcan

                                                    @can('view purchases')
                                                        @php
                                                            $idxPurSignedUrl = \Illuminate\Support\Facades\URL::signedRoute('purchases.public-pdf', ['purchase' => $p->id]);
                                                            $idxPurText = "Purchase Invoice #" . ($p->invoice_number ?? $p->id) . "\nSupplier: {$p->party->name}\nTotal: Rs " . number_format($p->total_amount, 2) . "\nPDF Link: {$idxPurSignedUrl}";
                                                        @endphp
                                                        <button type="button"
                                                            onclick="window.shareDoc({
                                                                title: 'Purchase Invoice #{{ $p->invoice_number ?? $p->id }} - {{ addslashes($p->party->name) }}',
                                                                text: '{{ addslashes(str_replace("\n", '\n', $idxPurText)) }}',
                                                                url: '{{ $idxPurSignedUrl }}'
                                                            })"
                                                            class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center cursor-pointer">
                                                            <svg class="w-4 h-4 mr-2 text-[#252ebf]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                                            </svg>
                                                            {{ __('Share Invoice') }}
                                                        </button>
                                                        <x-dropdown-link :href="route('purchases.pdf', $p)" class="flex items-center">
                                                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                            </svg>
                                                            {{ __('Download PDF') }}
                                                        </x-dropdown-link>
                                                    @endcan
                                                    @can('delete purchases')
                                                        <div class="border-t border-gray-100 dark:border-gray-700"></div>
                                                        <form action="{{ route('purchases.destroy', $p) }}"
                                                            method="POST" id="delete-purchase-{{ $p->id }}">
                                                            @csrf @method('DELETE')
                                                            <x-dropdown-link :href="route('purchases.destroy', $p)"
                                                                onclick="event.preventDefault(); if(confirm('Delete this purchase? Stock will be reversed.')) { document.getElementById('delete-purchase-{{ $p->id }}').submit(); }"
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
                                        <td colspan="6"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400 italic">
                                            No purchase records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Purchase Cards View -->
                    <div class="block md:hidden p-3 space-y-3">
                        @forelse ($purchases as $p)
                            @php
                                $due = $p->total_amount - $p->paid_amount;
                                $rawPhone = preg_replace('/[^0-9]/', '', $p->party->phone ?? '');
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
                                $waText = "Assalam-o-Alaikum {$p->party->name}, Purchase Invoice #{$settings->invoice_prefix}{$p->id} Total: {$settings->currency_symbol}" . number_format($p->total_amount, 2) . ($due > 0 ? " Due: {$settings->currency_symbol}" . number_format($due, 2) : " (Paid)") . "\nShukriya!";
                            @endphp
                            <div class="bg-gray-50 dark:bg-gray-900/60 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-black text-primary-600 dark:text-primary-400 font-mono">
                                            {{ $settings->invoice_prefix }}{{ $p->id }}
                                        </span>
                                        <span class="text-xs text-gray-400">{{ $p->date }}</span>
                                    </div>
                                    <div>
                                        @if ($due <= 0)
                                            <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full bg-green-100 text-green-700 dark:bg-green-900/60 dark:text-green-300">Paid</span>
                                        @elseif ($p->paid_amount > 0)
                                            <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/60 dark:text-yellow-300">Partial</span>
                                        @else
                                            <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full bg-red-100 text-red-700 dark:bg-red-900/60 dark:text-red-300">Unpaid</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-bold text-gray-900 dark:text-gray-100 truncate max-w-[210px]">
                                        {{ $p->party->name }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-200/60 dark:bg-gray-800 px-2 py-0.5 rounded">
                                        {{ $p->warehouse->name }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-3 gap-2 py-2 px-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 text-xs">
                                    <div>
                                        <span class="block text-[10px] uppercase font-bold text-gray-400">Total</span>
                                        <span class="font-extrabold text-gray-900 dark:text-gray-100">
                                            {{ $settings->currency_symbol }}{{ number_format($p->total_amount, 2) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] uppercase font-bold text-gray-400">Paid</span>
                                        <span class="font-extrabold text-green-600 dark:text-green-400">
                                            {{ $settings->currency_symbol }}{{ number_format($p->paid_amount, 2) }}
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <span class="block text-[10px] uppercase font-bold text-gray-400">Balance</span>
                                        <span class="font-extrabold {{ $due > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }}">
                                            {{ $settings->currency_symbol }}{{ number_format($due, 2) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    @can('view purchases')
                                        <a href="{{ route('purchases.show', $p) }}" 
                                           class="flex-1 py-2 px-3 bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 text-indigo-600 dark:text-indigo-400 font-bold text-xs rounded-xl text-center transition">
                                            View
                                        </a>
                                    @endcan

                                    @if ($waPhone)
                                        <a href="https://wa.me/{{ $waPhone }}?text={{ urlencode($waText) }}" target="_blank"
                                           class="flex items-center justify-center gap-1 py-2 px-3 bg-emerald-50 dark:bg-emerald-950/50 hover:bg-emerald-100 text-emerald-600 dark:text-emerald-400 font-bold text-xs rounded-xl transition">
                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                                            WhatsApp
                                        </a>
                                    @endif

                                    @can('edit purchases')
                                        <a href="{{ route('purchases.edit', $p) }}" 
                                           class="py-2 px-3 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-xl transition">
                                            Edit
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-gray-500 dark:text-gray-400 italic bg-gray-50 dark:bg-gray-900/40 rounded-xl">
                                No purchase records found.
                            </div>
                        @endforelse
                    </div>
                    @if ($purchases->hasPages())
                        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                            {{ $purchases->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
