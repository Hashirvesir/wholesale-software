<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border-l-4 border-emerald-500 text-emerald-800 dark:text-emerald-300 rounded-r-xl text-xs font-bold shadow-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('info'))
                <div class="p-4 bg-sky-50 dark:bg-sky-950/40 border-l-4 border-sky-500 text-sky-800 dark:text-sky-300 rounded-r-xl text-xs font-bold shadow-sm">
                    {{ session('info') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 bg-red-50 dark:bg-red-950/40 border-l-4 border-red-500 text-red-800 dark:text-red-300 rounded-r-xl text-xs font-bold shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Conversion Banner if Converted -->
            @if ($quotation->isConverted())
                <div class="p-5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-xl flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-black text-sm uppercase tracking-wide">Quotation Converted to Sale Invoice!</h4>
                            <p class="text-xs text-white/90">This quotation has been officially finalized into Sale #{{ $quotation->converted_sale_id }}. Stock and customer ledgers have been updated.</p>
                        </div>
                    </div>
                    <a href="{{ route('sales.show', $quotation->converted_sale_id) }}"
                        class="px-5 py-2.5 bg-white text-emerald-700 hover:bg-emerald-50 rounded-xl text-xs font-black uppercase tracking-wider transition shadow-lg shrink-0">
                        View Sale Invoice #{{ $quotation->converted_sale_id }} &rarr;
                    </a>
                </div>
            @endif

            <!-- Main Quotation Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">

                    <!-- Header & Top Actions -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-6 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">
                                    Quotation #{{ $quotation->id }}
                                </h2>
                                <span class="px-3 py-1 text-[11px] font-black uppercase tracking-wider rounded-full border
                                    @if ($quotation->status === 'draft') bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-300 dark:border-amber-700
                                    @elseif ($quotation->status === 'sent') bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-400 border-sky-300 dark:border-sky-700
                                    @elseif ($quotation->status === 'accepted') bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-300 dark:border-emerald-700
                                    @elseif ($quotation->status === 'converted') bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 border-indigo-300 dark:border-indigo-700
                                    @else bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-400 border-red-300 dark:border-red-700 @endif">
                                    {{ $quotation->status_label }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Estimate / Kacha Bill &bull; Generated on {{ \Carbon\Carbon::parse($quotation->date)->format('d M, Y') }}
                            </p>
                        </div>

                        <!-- Top Action Buttons -->
                        <div class="flex flex-wrap items-center gap-2">
                            @if (!$quotation->isConverted())
                                <a href="{{ route('quotations.convert', $quotation) }}"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-md transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                    Convert to Sale
                                </a>

                                <a href="{{ route('quotations.edit', $quotation) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </a>
                            @endif

                            <a href="{{ route('quotations.pdf', $quotation) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                PDF
                            </a>

                            @if (!empty($whatsappUrl))
                                <a href="{{ $whatsappUrl }}" target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-[#25D366] hover:bg-[#1ebe5b] text-white text-xs font-bold rounded-xl shadow transition">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                    </svg>
                                    WhatsApp
                                </a>
                            @endif

                            <button type="button" onclick="window.print()"
                                class="inline-flex items-center gap-1.5 px-3 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print
                            </button>

                            <a href="{{ route('quotations.index') }}"
                                class="px-3 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Back to List
                            </a>
                        </div>
                    </div>

                    <!-- Meta Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 my-6">
                        <!-- Customer Info -->
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700">
                            <h4 class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Customer Details</h4>
                            <div class="text-sm font-bold text-gray-900 dark:text-gray-100 uppercase">{{ $quotation->party->name }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">Phone: {{ $quotation->party->phone ?? 'N/A' }}</div>
                            @if ($quotation->party->address)
                                <div class="text-xs text-gray-500 mt-0.5">{{ $quotation->party->address }}</div>
                            @endif
                            <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-600 dark:text-gray-400">
                                Current Ledger Balance:
                                <span class="{{ $currentBalance >= 0 ? 'text-red-500' : 'text-emerald-500' }}">
                                    {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format(abs($currentBalance), 2) }}
                                    {{ $currentBalance >= 0 ? '(Due)' : '(Advance)' }}
                                </span>
                            </div>
                        </div>

                        <!-- Warehouse & Quote Meta -->
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700">
                            <h4 class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Quotation Info</h4>
                            <div class="text-xs text-gray-700 dark:text-gray-300 flex justify-between py-0.5">
                                <span class="text-gray-500">Warehouse:</span>
                                <span class="font-bold">{{ $quotation->warehouse->name }}</span>
                            </div>
                            <div class="text-xs text-gray-700 dark:text-gray-300 flex justify-between py-0.5">
                                <span class="text-gray-500">Quote Date:</span>
                                <span class="font-bold">{{ \Carbon\Carbon::parse($quotation->date)->format('d M, Y') }}</span>
                            </div>
                            <div class="text-xs text-gray-700 dark:text-gray-300 flex justify-between py-0.5">
                                <span class="text-gray-500">Valid Until:</span>
                                <span class="font-bold {{ $quotation->valid_until && \Carbon\Carbon::parse($quotation->valid_until)->isPast() ? 'text-red-500' : '' }}">
                                    {{ $quotation->valid_until ? \Carbon\Carbon::parse($quotation->valid_until)->format('d M, Y') : 'No Expiry' }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-700 dark:text-gray-300 flex justify-between py-0.5">
                                <span class="text-gray-500">Created By:</span>
                                <span class="font-bold">{{ $quotation->creator->name ?? 'System' }}</span>
                            </div>
                        </div>

                        <!-- Status Management -->
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
                            <div>
                                <h4 class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Status Control</h4>
                                @if (!$quotation->isConverted())
                                    <form action="{{ route('quotations.status', $quotation) }}" method="POST" class="space-y-2">
                                        @csrf
                                        <div class="flex gap-2">
                                            <select name="status" class="text-xs font-bold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 flex-1">
                                                <option value="draft" {{ $quotation->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                                <option value="sent" {{ $quotation->status === 'sent' ? 'selected' : '' }}>Sent to Customer</option>
                                                <option value="accepted" {{ $quotation->status === 'accepted' ? 'selected' : '' }}>Accepted</option>
                                                <option value="cancelled" {{ $quotation->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                            <button type="submit" class="px-3 py-1.5 bg-gray-800 dark:bg-gray-700 text-white rounded-lg text-xs font-bold hover:bg-black transition">
                                                Save
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                        Status locked: Converted to Invoice #{{ $quotation->converted_sale_id }}
                                    </div>
                                @endif
                            </div>

                            @if ($quotation->note)
                                <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <span class="font-bold text-gray-700 dark:text-gray-300">Terms / Notes:</span> {{ $quotation->note }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden mb-6">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-100 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="py-3 px-4 w-12 text-center">#</th>
                                    <th class="py-3 px-4">Product</th>
                                    <th class="py-3 px-4 w-32">SKU</th>
                                    <th class="py-3 px-4 w-28 text-center">Unit</th>
                                    <th class="py-3 px-4 w-32 text-right">Quantity</th>
                                    <th class="py-3 px-4 w-36 text-right">Unit Price</th>
                                    <th class="py-3 px-4 w-36 text-right">Line Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y dark:divide-gray-700 bg-white dark:bg-gray-800">
                                @foreach ($quotation->items as $index => $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                        <td class="py-3 px-4 text-center font-bold text-gray-400">{{ $index + 1 }}</td>
                                        <td class="py-3 px-4 font-bold text-gray-900 dark:text-gray-100">
                                            {{ $item->product->name ?? 'Deleted Product' }}
                                        </td>
                                        <td class="py-3 px-4 font-mono text-gray-500 text-[11px]">
                                            {{ $item->product->sku ?? '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-center text-gray-500 text-[11px]">
                                            {{ $item->product->unit->name ?? 'Unit' }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-black text-gray-900 dark:text-gray-100">
                                            {{ number_format($item->qty, 2) }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-bold text-gray-700 dark:text-gray-300">
                                            {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($item->unit_price, 2) }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-black text-gray-900 dark:text-gray-100">
                                            {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($item->total_price, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Financial Summary -->
                    <div class="flex justify-end">
                        <div class="w-full md:w-80 bg-gray-50 dark:bg-gray-900/40 p-4 rounded-xl border border-gray-200 dark:border-gray-700 space-y-2.5">
                            <div class="flex justify-between items-center text-xs font-bold text-gray-600 dark:text-gray-400">
                                <span>Subtotal:</span>
                                <span class="font-black text-gray-900 dark:text-gray-100">
                                    {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($quotation->subtotal, 2) }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center text-xs font-bold text-gray-600 dark:text-gray-400">
                                <span>Discount:</span>
                                <span class="font-bold text-red-500">
                                    - {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($quotation->discount_amount, 2) }}
                                </span>
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-2.5 flex justify-between items-center">
                                <span class="text-sm font-black uppercase text-gray-900 dark:text-gray-100">Quotation Total:</span>
                                <span class="text-xl font-black text-primary-600 dark:text-primary-400">
                                    {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($quotation->total_amount, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
