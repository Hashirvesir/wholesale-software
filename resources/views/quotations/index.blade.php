<x-app-layout>
    <div class="py-12">
        <div class="max-w-[1750px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Header -->
                    <div class="flex flex-wrap justify-between items-center gap-4 mb-6 pb-4 border-b dark:border-gray-700">
                        <div>
                            <h3 class="text-xl font-black text-gray-800 dark:text-gray-100 tracking-wide uppercase flex items-center gap-2">
                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>{{ __('Quotations & Estimates (Kacha Bill)') }}</span>
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Manage price estimates, customer quotations, and convert them to Sales in 1-click.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('quotations.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md transition transform active:scale-95">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                </svg>
                                New Quotation
                            </a>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm font-bold flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="mb-4 px-4 py-3 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200 text-sm font-bold flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>{{ session('info') }}</span>
                        </div>
                    @endif

                    <!-- Search & Filter Bar -->
                    <div class="bg-gray-50 dark:bg-gray-900/60 p-4 rounded-xl border border-gray-100 dark:border-gray-700 mb-6">
                        <form action="{{ route('quotations.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3 items-end">
                            <!-- Search -->
                            <div class="md:col-span-2">
                                <label for="search" class="block text-[11px] font-black uppercase text-gray-500 tracking-wider mb-1">Search Customer / Bill #</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                    placeholder="Customer name or ID..."
                                    class="w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded-lg text-xs font-semibold focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100 shadow-sm">
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label for="status" class="block text-[11px] font-black uppercase text-gray-500 tracking-wider mb-1">Status</label>
                                <select name="status" id="status"
                                    class="w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded-lg text-xs font-semibold focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100 shadow-sm">
                                    <option value="">All Statuses</option>
                                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                                    <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepted</option>
                                    <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Converted to Sale</option>
                                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>

                            <!-- Warehouse Filter -->
                            <div>
                                <label for="warehouse_id" class="block text-[11px] font-black uppercase text-gray-500 tracking-wider mb-1">Warehouse</label>
                                <select name="warehouse_id" id="warehouse_id"
                                    class="w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded-lg text-xs font-semibold focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100 shadow-sm">
                                    <option value="">All Warehouses</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Date Range -->
                            <div>
                                <label for="from_date" class="block text-[11px] font-black uppercase text-gray-500 tracking-wider mb-1">From Date</label>
                                <input type="date" name="from_date" id="from_date" value="{{ request('from_date') }}"
                                    class="w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded-lg text-xs font-semibold focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100 shadow-sm">
                            </div>

                            <!-- Filter & Reset Buttons -->
                            <div class="flex gap-2">
                                <button type="submit"
                                    class="w-full bg-gray-900 hover:bg-black dark:bg-gray-700 dark:hover:bg-gray-600 text-white font-bold py-2 px-3 rounded-lg text-xs uppercase tracking-wider transition shadow-sm">
                                    Filter
                                </button>
                                <a href="{{ route('quotations.index') }}"
                                    class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 py-2 px-3 rounded-lg text-xs font-bold transition flex items-center justify-center">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">#</th>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3">Customer</th>
                                    <th class="px-4 py-3">Warehouse</th>
                                    <th class="px-4 py-3 text-center">Items</th>
                                    <th class="px-4 py-3 text-right">Total Amount</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-xs bg-white dark:bg-gray-800">
                                @forelse ($quotations as $quotation)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                        <td class="px-4 py-3 font-mono font-bold text-gray-500">
                                            #{{ $quotation->id }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-300 font-semibold">
                                            {{ $quotation->date->format('d M, Y') }}
                                            @if ($quotation->valid_until)
                                                <span class="block text-[10px] text-gray-400">Valid: {{ $quotation->valid_until->format('d M, Y') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-gray-900 dark:text-gray-100 uppercase">{{ $quotation->party->name }}</div>
                                            <div class="text-[10px] text-gray-500 dark:text-gray-400">{{ $quotation->party->phone ?? 'No Phone' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300 font-semibold">
                                            {{ $quotation->warehouse->name }}
                                        </td>
                                        <td class="px-4 py-3 text-center font-bold text-gray-700 dark:text-gray-300">
                                            <span class="px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-[11px]">
                                                {{ $quotation->items->count() }} items
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-black text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                            {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($quotation->total_amount, 2) }}
                                            @if ($quotation->discount_amount > 0)
                                                <span class="block text-[10px] text-rose-500 font-bold">-{{ number_format($quotation->discount_amount, 2) }} disc</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            @if ($quotation->status === 'converted')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                                    Converted
                                                </span>
                                            @elseif ($quotation->status === 'accepted')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                                                    Accepted
                                                </span>
                                            @elseif ($quotation->status === 'sent')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                                    Sent
                                                </span>
                                            @elseif ($quotation->status === 'cancelled')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300 border border-red-300 dark:border-red-800">
                                                    Cancelled
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">
                                                    Draft
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <!-- View -->
                                                <a href="{{ route('quotations.show', $quotation) }}"
                                                    class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition"
                                                    title="View Details">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                </a>

                                                <!-- Convert to Sale Button -->
                                                @if (!$quotation->isConverted())
                                                    <a href="{{ route('quotations.convert', $quotation) }}"
                                                        class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] uppercase tracking-wider transition flex items-center gap-1 shadow-sm"
                                                        title="Convert to Sale Invoice">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                                        <span>To Sale</span>
                                                    </a>
                                                @else
                                                    <a href="{{ route('sales.show', $quotation->converted_sale_id) }}"
                                                        class="px-2 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-600 dark:text-gray-300 font-bold text-[10px] uppercase font-mono"
                                                        title="View Linked Sale Invoice">
                                                        Sale #{{ $quotation->converted_sale_id }}
                                                    </a>
                                                @endif

                                                <!-- PDF -->
                                                <a href="{{ route('quotations.pdf', $quotation) }}" target="_blank"
                                                    class="p-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:hover:bg-indigo-900 text-indigo-600 dark:text-indigo-400 transition"
                                                    title="Download PDF">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="font-bold text-sm">No quotations found.</p>
                                            <p class="text-xs text-gray-400 mt-1">Create your first customer quotation / estimate above.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Quotation Cards View -->
                    <div class="block md:hidden space-y-3">
                        @forelse ($quotations as $quotation)
                            <div class="bg-gray-50 dark:bg-gray-900/60 p-4 rounded-xl border border-gray-100 dark:border-gray-700/80 shadow-sm space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-black text-primary-600 dark:text-primary-400 font-mono">
                                            #{{ $quotation->id }}
                                        </span>
                                        <span class="text-xs text-gray-400">{{ $quotation->date->format('d M, Y') }}</span>
                                    </div>
                                    @if ($quotation->status === 'converted')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300">Converted</span>
                                    @elseif ($quotation->status === 'accepted')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-300">Accepted</span>
                                    @elseif ($quotation->status === 'sent')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300">Sent</span>
                                    @elseif ($quotation->status === 'cancelled')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300 border border-red-300">Cancelled</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border border-gray-300">Draft</span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-bold text-gray-900 dark:text-gray-100 truncate max-w-[200px]">
                                        {{ $quotation->party->name }}
                                    </span>
                                    <span class="text-base font-black font-mono text-gray-900 dark:text-gray-100">
                                        {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($quotation->total_amount, 2) }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between pt-1 border-t border-gray-200/60 dark:border-gray-700/60 text-xs">
                                    <span class="text-gray-400">{{ $quotation->warehouse->name }}</span>
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('quotations.show', $quotation) }}" 
                                           class="py-1 px-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-700 dark:text-gray-200 font-bold text-xs rounded-lg transition">
                                            View
                                        </a>
                                        @if (!$quotation->isConverted())
                                            <a href="{{ route('quotations.convert', $quotation) }}" 
                                               class="py-1 px-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition">
                                                To Sale
                                            </a>
                                        @endif
                                        <a href="{{ route('quotations.pdf', $quotation) }}" target="_blank"
                                           class="py-1 px-2 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 font-bold text-xs rounded-lg transition">
                                            PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-gray-500 dark:text-gray-400 italic bg-gray-50 dark:bg-gray-900/40 rounded-xl">
                                No quotations found.
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $quotations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
