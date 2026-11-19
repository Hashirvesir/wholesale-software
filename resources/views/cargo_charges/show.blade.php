<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12">
        <div class="max-w-6xl mx-auto px-3 sm:px-6 lg:px-8 space-y-5">

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border-l-4 border-emerald-500 text-emerald-800 dark:text-emerald-300 rounded-r-xl text-xs font-bold shadow-sm no-print">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Main Cargo Voucher Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-xl sm:rounded-2xl border border-gray-200 dark:border-gray-700">
                <div class="p-4 sm:p-6 md:p-8 text-gray-900 dark:text-gray-100">

                    <!-- Header & Actions -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-5 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                <h2 class="text-lg sm:text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight flex items-center gap-2">
                                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                    <span>Cargo Voucher #CRG-{{ str_pad($cargoCharge->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </h2>

                                @if ($cargoCharge->is_paid)
                                    <span class="px-3 py-1 text-[11px] font-black uppercase tracking-wider rounded-full border bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-300 dark:border-emerald-700 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Paid
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-[11px] font-black uppercase tracking-wider rounded-full border bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-300 dark:border-amber-700 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Unpaid / On Account
                                    </span>
                                @endif

                                @if ($cargoCharge->purchase_id)
                                    <span class="px-3 py-1 text-[11px] font-black uppercase tracking-wider rounded-full border bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-300 dark:border-blue-700">
                                        Purchase #{{ $cargoCharge->purchase_id }}
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-[11px] font-black uppercase tracking-wider rounded-full border bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-300 dark:border-purple-700">
                                        Direct Cargo Shipment
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Recorded Date: <span class="font-bold text-gray-700 dark:text-gray-300">{{ $cargoCharge->created_at->format('d M, Y - h:i A') }}</span>
                            </p>
                        </div>

                        <!-- Top Action Buttons -->
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('cargo-charges.thermal', $cargoCharge) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gray-900 hover:bg-black text-white text-xs font-black uppercase tracking-wider rounded-xl shadow transition">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                <span>Thermal Print</span>
                            </a>

                            <a href="{{ route('cargo-charges.pdf', $cargoCharge) }}"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Download PDF</span>
                            </a>

                            <a href="{{ route('cargo-charges.edit', $cargoCharge) }}"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </a>

                            <a href="{{ route('cargo-charges.index') }}"
                                class="px-3.5 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Back to List
                            </a>
                        </div>
                    </div>

                    <!-- Meta Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 my-6">
                        <!-- Cargo Party Card -->
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Cargo Agent / Transporter
                                </h4>
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300">
                                    Cargo Party
                                </span>
                            </div>
                            <div class="text-base font-black text-gray-900 dark:text-gray-100 uppercase">
                                {{ $cargoCharge->cargoParty->name ?? 'N/A' }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Phone: {{ $cargoCharge->cargoParty->phone ?? 'N/A' }}</div>
                            @if ($cargoCharge->cargoParty->address)
                                <div class="text-xs text-gray-500 mt-0.5">{{ $cargoCharge->cargoParty->address }}</div>
                            @endif

                            @if($cargoCharge->cargoParty)
                                @php $cargoBal = $cargoCharge->cargoParty->getCurrentBalance(); @endphp
                                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-600 dark:text-gray-400 flex justify-between items-center">
                                    <span>Current Party Balance:</span>
                                    <span class="{{ $cargoBal >= 0 ? 'text-red-500' : 'text-emerald-500' }}">
                                        {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format(abs($cargoBal), 2) }}
                                        {{ $cargoBal >= 0 ? '(Due / Receivable)' : '(Payable)' }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Cargo & Weight Calculation -->
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700">
                            <h4 class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                                Weight & Rate Breakdown
                            </h4>
                            <div class="text-xs text-gray-700 dark:text-gray-300 flex justify-between py-1.5 border-b border-gray-200/50 dark:border-gray-700/50">
                                <span class="text-gray-500">Total Shipment Weight:</span>
                                <span class="font-black text-gray-900 dark:text-gray-100 font-mono">{{ number_format($cargoCharge->total_weight, 2) }} kg</span>
                            </div>
                            <div class="text-xs text-gray-700 dark:text-gray-300 flex justify-between py-1.5 border-b border-gray-200/50 dark:border-gray-700/50">
                                <span class="text-gray-500">Freight Rate / kg:</span>
                                <span class="font-black text-gray-900 dark:text-gray-100 font-mono">{{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($cargoCharge->rate_per_kg, 2) }}</span>
                            </div>
                            <div class="text-xs text-gray-700 dark:text-gray-300 flex justify-between py-1.5 pt-2">
                                <span class="font-black uppercase text-gray-700 dark:text-gray-300">Total Cargo Amount:</span>
                                <span class="text-base font-black text-rose-600 dark:text-rose-400 font-mono">
                                    {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($cargoCharge->total_amount, 2) }}
                                </span>
                            </div>
                        </div>

                        <!-- Linked Purchase or Direct Shipment Card -->
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700">
                            <h4 class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                                Linked Bill / Shipment
                            </h4>
                            @if ($cargoCharge->purchase)
                                <div class="text-xs text-gray-700 dark:text-gray-300 flex justify-between py-1 border-b border-gray-200/50 dark:border-gray-700/50">
                                    <span class="text-gray-500">Purchase Bill:</span>
                                    <a href="{{ route('purchases.show', $cargoCharge->purchase_id) }}" class="font-black text-primary-600 hover:underline">
                                        PUR-#{{ $cargoCharge->purchase_id }}
                                    </a>
                                </div>
                                <div class="text-xs text-gray-700 dark:text-gray-300 flex justify-between py-1 border-b border-gray-200/50 dark:border-gray-700/50">
                                    <span class="text-gray-500">Supplier / Vendor:</span>
                                    <span class="font-bold text-gray-900 dark:text-gray-100 uppercase">{{ $cargoCharge->purchase->party->name ?? 'N/A' }}</span>
                                </div>
                                <div class="text-xs text-gray-700 dark:text-gray-300 flex justify-between py-1">
                                    <span class="text-gray-500">Purchase Date:</span>
                                    <span class="font-bold text-gray-900 dark:text-gray-100">{{ $cargoCharge->purchase->date }}</span>
                                </div>
                            @else
                                <div class="py-2 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-black bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300">
                                        Standalone Direct Cargo
                                    </span>
                                    <p class="text-[11px] text-gray-500 mt-2">This cargo charge is not attached to any purchase order bill.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Items Table if linked to purchase -->
                    @if ($cargoCharge->purchase && $cargoCharge->purchase->items->count() > 0)
                        <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="p-3.5 bg-gray-50 dark:bg-gray-900/60 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <h4 class="text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <span>Products in Linked Purchase Bill (PUR-#{{ $cargoCharge->purchase_id }})</span>
                                </h4>
                                <span class="text-xs text-gray-500 font-bold">{{ $cargoCharge->purchase->items->count() }} Items</span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                                    <thead class="bg-gray-50 dark:bg-gray-900/40 text-[10px] uppercase font-black tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                                        <tr>
                                            <th class="px-4 py-2.5">#</th>
                                            <th class="px-4 py-2.5">Product Name</th>
                                            <th class="px-4 py-2.5">SKU / Code</th>
                                            <th class="px-4 py-2.5 text-right">Quantity</th>
                                            <th class="px-4 py-2.5 text-right">Unit Price</th>
                                            <th class="px-4 py-2.5 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 font-medium">
                                        @foreach ($cargoCharge->purchase->items as $idx => $item)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                                                <td class="px-4 py-2.5 text-gray-400 font-mono">{{ $idx + 1 }}</td>
                                                <td class="px-4 py-2.5 font-bold text-gray-900 dark:text-gray-100">{{ $item->product->name ?? 'N/A' }}</td>
                                                <td class="px-4 py-2.5 font-mono text-gray-500">{{ $item->product->sku ?? '-' }}</td>
                                                <td class="px-4 py-2.5 text-right font-black">{{ number_format($item->quantity) }}</td>
                                                <td class="px-4 py-2.5 text-right font-mono">{{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($item->unit_price, 2) }}</td>
                                                <td class="px-4 py-2.5 text-right font-black font-mono">{{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($item->subtotal, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Accounting Ledger Entries -->
                    <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-3.5 bg-gray-50 dark:bg-gray-900/60 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                            <h4 class="text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Double-Entry General Ledger Impact</span>
                            </h4>
                            <span class="text-[11px] font-bold text-gray-500">Ref: cargo #{{ $cargoCharge->id }}</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                                <thead class="bg-gray-50 dark:bg-gray-900/40 text-[10px] uppercase font-black tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                                    <tr>
                                        <th class="px-4 py-2.5">Date</th>
                                        <th class="px-4 py-2.5">Account</th>
                                        <th class="px-4 py-2.5">Narration / Note</th>
                                        <th class="px-4 py-2.5 text-right">Debit</th>
                                        <th class="px-4 py-2.5 text-right">Credit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 font-medium">
                                    @forelse ($ledgerEntries as $entry)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                                            <td class="px-4 py-2.5 text-gray-500 font-mono">{{ $entry->date }}</td>
                                            <td class="px-4 py-2.5 font-bold text-gray-900 dark:text-gray-100">
                                                {{ $entry->account->name ?? 'N/A' }}
                                                @if ($entry->party_id)
                                                    <span class="text-[10px] text-gray-500">({{ $cargoCharge->cargoParty->name ?? '' }})</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2.5 text-xs text-gray-500">{{ $entry->note }}</td>
                                            <td class="px-4 py-2.5 text-right font-black font-mono text-emerald-600">
                                                {{ $entry->debit > 0 ? ($settings->currency_symbol ?? 'Rs') . ' ' . number_format($entry->debit, 2) : '-' }}
                                            </td>
                                            <td class="px-4 py-2.5 text-right font-black font-mono text-rose-600">
                                                {{ $entry->credit > 0 ? ($settings->currency_symbol ?? 'Rs') . ' ' . number_format($entry->credit, 2) : '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-3 text-center text-gray-400 italic">No ledger entries found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Bottom Action Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('cargo-charges.thermal', $cargoCharge) }}" target="_blank"
                            class="px-5 py-2.5 bg-gray-900 hover:bg-black text-white rounded-xl text-xs font-black uppercase tracking-wider transition shadow flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Thermal Print Receipt</span>
                        </a>

                        <a href="{{ route('cargo-charges.pdf', $cargoCharge) }}"
                            class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition shadow flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Download PDF</span>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
