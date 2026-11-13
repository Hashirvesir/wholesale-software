<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border-l-4 border-emerald-500 text-emerald-800 dark:text-emerald-300 rounded-r-xl text-xs font-bold shadow-sm no-print">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 bg-red-50 dark:bg-red-950/40 border-l-4 border-red-500 text-red-800 dark:text-red-300 rounded-r-xl text-xs font-bold shadow-sm no-print">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Main Payment Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-xl sm:rounded-2xl border border-gray-200 dark:border-gray-700">
                <div class="p-3.5 sm:p-6 md:p-8 text-gray-900 dark:text-gray-100">

                    <!-- Header & Top Actions -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-4 sm:pb-6 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                <h2 class="text-lg sm:text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">
                                    Payment Voucher #{{ $payment->id }}
                                </h2>
                                @if ($payment->payment_type === 'received')
                                    <span class="px-3 py-1 text-[11px] font-black uppercase tracking-wider rounded-full border bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-300 dark:border-emerald-700 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Payment Received
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-[11px] font-black uppercase tracking-wider rounded-full border bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border-rose-300 dark:border-rose-700 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Payment Paid
                                    </span>
                                @endif

                                <span class="px-3 py-1 text-[11px] font-black uppercase tracking-wider rounded-full border bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-300 dark:border-blue-700 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span>Shop: {{ $payment->warehouse->name ?? 'Default (Head Office)' }}</span>
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Voucher Date: <span class="font-bold text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($payment->date)->format('d M, Y') }}</span>
                                @if ($payment->created_at)
                                    &bull; Recorded at {{ $payment->created_at->timezone(config('app.timezone'))->format('h:i A') }}
                                @endif
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap items-center gap-2 no-print">
                            <a href="{{ route('payments.pdf', $payment) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                PDF
                            </a>

                            <a href="{{ route('payments.thermal', $payment) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-800 dark:bg-gray-700 hover:bg-black text-white text-xs font-bold rounded-xl shadow transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Thermal
                            </a>

                            @php
                                $rawPhone = preg_replace('/\D/', '', $payment->party->phone ?? '');
                                $waPhone = null;
                                if ($rawPhone !== '') {
                                    if (str_starts_with($rawPhone, '0')) {
                                        $waPhone = '92' . substr($rawPhone, 1);
                                    } elseif (str_starts_with($rawPhone, '92')) {
                                        $waPhone = $rawPhone;
                                    } else {
                                        $waPhone = '92' . $rawPhone;
                                    }
                                }
                                $partyBal = $payment->party->getCurrentBalance();
                                $publicPdfUrl = \Illuminate\Support\Facades\URL::signedRoute('payments.public-pdf', ['payment' => $payment->id]);
                                $waText = "Assalam-o-Alaikum {$payment->party->name},\nPayment Voucher #{$payment->id} recorded:\nType: " . ($payment->payment_type === 'received' ? 'Received' : 'Paid') . "\nAmount: " . ($settings->currency_symbol ?? 'Rs') . " " . number_format($payment->amount, 2) . "\nAccount: {$payment->account->name}\nDate: {$payment->date}\nCurrent Balance: " . ($settings->currency_symbol ?? 'Rs') . " " . number_format(abs($partyBal), 2) . " " . ($partyBal >= 0 ? '(Receivable)' : '(Payable)') . "\n\nVoucher PDF: {$publicPdfUrl}\n\nShukriya,\n{$settings->company_name}";
                            @endphp

                            <button type="button"
                                onclick="window.shareDoc({
                                    title: 'Payment Voucher #{{ $payment->id }} - {{ addslashes($payment->party->name) }}',
                                    text: '{{ addslashes(str_replace("\n", '\n', $waText)) }}',
                                    url: '{{ $publicPdfUrl }}'
                                })"
                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-[#252ebf] hover:bg-[#1e2599] text-white text-xs font-bold rounded-xl shadow transition cursor-pointer"
                                title="Share Voucher via Android/System Share">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                                Share
                            </button>

                            <a href="{{ $waPhone ? 'https://wa.me/' . $waPhone . '?text=' . urlencode($waText) : 'https://api.whatsapp.com/send?text=' . urlencode($waText) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-[#25D366] hover:bg-[#1ebe5b] text-white text-xs font-bold rounded-xl shadow transition"
                                title="{{ $waPhone ? 'Send to ' . $waPhone : 'Share to any WhatsApp contact' }}">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                </svg>
                                WhatsApp
                            </a>

                            <button type="button" onclick="window.print()"
                                class="inline-flex items-center gap-1.5 px-3 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print
                            </button>

                            <a href="{{ route('payments.index') }}"
                                class="px-3 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Back to List
                            </a>
                        </div>
                    </div>

                    <!-- Meta Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 my-6">
                        <!-- Party Info Card -->
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Party Details
                                </h4>
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-primary-100 text-primary-800 dark:bg-primary-950/60 dark:text-primary-300">
                                    {{ $payment->party->type }}
                                </span>
                            </div>
                            <div class="text-sm font-bold text-gray-900 dark:text-gray-100 uppercase">{{ $payment->party->name }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">Phone: {{ $payment->party->phone ?? 'N/A' }}</div>
                            @if ($payment->party->address)
                                <div class="text-xs text-gray-500 mt-0.5">{{ $payment->party->address }}</div>
                            @endif

                            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-600 dark:text-gray-400 flex justify-between items-center">
                                <span>Current Balance:</span>
                                <span class="{{ $partyBal >= 0 ? 'text-red-500' : 'text-emerald-500' }}">
                                    {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format(abs($partyBal), 2) }}
                                    {{ $partyBal >= 0 ? '(Due)' : '(Advance)' }}
                                </span>
                            </div>
                        </div>

                        <!-- Account & Location Card -->
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700">
                            <h4 class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                                Payment Account & Origin
                            </h4>
                            <div class="text-xs text-gray-700 dark:text-gray-300 flex justify-between py-1 border-b border-gray-200/50 dark:border-gray-700/50">
                                <span class="text-gray-500">Account:</span>
                                <span class="font-bold text-gray-900 dark:text-gray-100">{{ $payment->account->name }}</span>
                            </div>
                            <div class="text-xs text-gray-700 dark:text-gray-300 flex justify-between py-1 border-b border-gray-200/50 dark:border-gray-700/50">
                                <span class="text-gray-500">Account Type:</span>
                                <span class="font-bold uppercase text-[11px]">{{ $payment->account->type }}</span>
                            </div>
                            <div class="text-xs text-gray-700 dark:text-gray-300 flex justify-between py-1.5 border-b border-gray-200/50 dark:border-gray-700/50 bg-blue-50/50 dark:bg-blue-950/20 px-2 rounded-lg my-1">
                                <span class="font-bold text-blue-800 dark:text-blue-300 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    Shop / Branch:
                                </span>
                                <span class="font-black text-blue-900 dark:text-blue-200">{{ $payment->warehouse->name ?? 'Default (Head Office)' }}</span>
                            </div>
                            <div class="text-xs text-gray-700 dark:text-gray-300 flex justify-between py-1">
                                <span class="text-gray-500">Voucher Type:</span>
                                <span class="font-bold uppercase {{ $payment->payment_type === 'received' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $payment->payment_type }}
                                </span>
                            </div>
                        </div>

                        <!-- Note / Remarks Card -->
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
                            <div>
                                <h4 class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                                    Remarks / Narration
                                </h4>
                                <div class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 text-xs italic text-gray-600 dark:text-gray-300 min-h-[60px]">
                                    {{ $payment->note ?: 'No specific remarks entered for this voucher.' }}
                                </div>
                            </div>
                            <div class="mt-2 text-[10px] text-gray-400 text-right">
                                System Entry #{{ $payment->id }}
                            </div>
                        </div>
                    </div>

                    <!-- Payment Amount Highlight Banner -->
                    <div class="p-4 sm:p-6 rounded-xl sm:rounded-2xl border mb-6
                        @if ($payment->payment_type === 'received')
                            bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800
                        @else
                            bg-rose-50/50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-800
                        @endif flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider
                                @if ($payment->payment_type === 'received') text-emerald-700 dark:text-emerald-400 @else text-rose-700 dark:text-rose-400 @endif">
                                Total Transacted Amount
                            </span>
                            <div class="text-2xl sm:text-3xl md:text-4xl font-black font-mono mt-1
                                @if ($payment->payment_type === 'received') text-emerald-600 dark:text-emerald-300 @else text-rose-600 dark:text-rose-300 @endif">
                                {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($payment->amount, 2) }}
                            </div>
                        </div>
                        <div class="text-right text-xs text-gray-500 dark:text-gray-400 max-w-sm">
                            @if ($payment->payment_type === 'received')
                                Inflow credited to party ledger and debited to cash/bank account.
                            @else
                                Outflow debited to party ledger and credited from cash/bank account.
                            @endif
                        </div>
                    </div>

                    <!-- Invoice / Order Settle Allocation Details -->
                    @if($payment->allocations->count() > 0)
                        <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                            <div class="p-4 bg-gray-50 dark:bg-gray-900/60 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <h4 class="text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                    <span>Settled Invoices & Repair Dues (Allocations)</span>
                                </h4>
                                <span class="text-[11px] font-bold text-gray-500">
                                    Total Settled: <strong class="text-emerald-600">{{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($payment->allocations->sum('amount'), 2) }}</strong>
                                </span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                                    <thead class="bg-gray-50 dark:bg-gray-900/40 text-[10px] uppercase font-black tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                                        <tr>
                                            <th class="px-4 py-2.5">Type</th>
                                            <th class="px-4 py-2.5">Reference / Order #</th>
                                            <th class="px-4 py-2.5 text-right">Bill / Total</th>
                                            <th class="px-4 py-2.5 text-right">Amount Settled</th>
                                            <th class="px-4 py-2.5 text-right">Remaining Due</th>
                                            <th class="px-4 py-2.5 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 font-medium">
                                        @foreach($payment->allocations as $alloc)
                                            @php
                                                $item = $alloc->allocatable;
                                                $isRepair = $alloc->allocatable_type === \App\Models\RepairOrder::class;
                                                $isSale = $alloc->allocatable_type === \App\Models\Sale::class;
                                                $isPurchase = $alloc->allocatable_type === \App\Models\Purchase::class;
                                                
                                                $refNo = $isRepair ? ($item->order_no ?? 'REP #'.$alloc->allocatable_id) : ($isSale ? ('INV-'.str_pad($item->id ?? $alloc->allocatable_id, 5, '0', STR_PAD_LEFT)) : ('BILL-'.str_pad($item->id ?? $alloc->allocatable_id, 5, '0', STR_PAD_LEFT)));
                                                $total = $isRepair ? ($item->total_amount ?? 0) : ($item ? ($item->total_amount - $item->discount_amount) : 0);
                                                $remDue = $isRepair ? ($item->balance_amount ?? 0) : ($item ? max(0, ($item->total_amount - $item->discount_amount) - $item->paid_amount) : 0);
                                                $viewUrl = $isRepair ? route('repairs.show', $alloc->allocatable_id) : ($isSale ? route('sales.show', $alloc->allocatable_id) : route('purchases.show', $alloc->allocatable_id));
                                            @endphp
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                                                <td class="px-4 py-3">
                                                    @if($isRepair)
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">Mobile Repair</span>
                                                    @elseif($isSale)
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">Sale Invoice</span>
                                                    @else
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">Purchase Bill</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 font-bold text-gray-900 dark:text-gray-100">
                                                    {{ $refNo }}
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($total, 2) }}
                                                </td>
                                                <td class="px-4 py-3 text-right font-black text-emerald-600 dark:text-emerald-400">
                                                    {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($alloc->amount, 2) }}
                                                </td>
                                                <td class="px-4 py-3 text-right font-bold {{ $remDue > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                                    {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($remDue, 2) }}
                                                    @if($remDue <= 0)
                                                        <span class="text-[10px] font-normal block text-emerald-600">✓ Fully Cleared</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <a href="{{ $viewUrl }}" target="_blank"
                                                       class="px-2.5 py-1 text-[11px] font-bold rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">
                                                        View Order &rarr;
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Double-entry Accounting Ledger Notice -->
                    <div class="p-4 rounded-xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 text-blue-900 dark:text-blue-200 text-xs flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <span class="font-black uppercase tracking-wider block mb-0.5">Automated Double-Entry Accounting Posted:</span>
                            @if ($payment->payment_type === 'received')
                                Cash/Bank Account <strong>[{{ $payment->account->name }}]</strong> has been <span class="text-emerald-600 font-bold">DEBITED</span>, and Party <strong>[{{ $payment->party->name }}]</strong> has been <span class="text-emerald-600 font-bold">CREDITED</span>.
                            @else
                                Party <strong>[{{ $payment->party->name }}]</strong> has been <span class="text-rose-600 font-bold">DEBITED</span>, and Cash/Bank Account <strong>[{{ $payment->account->name }}]</strong> has been <span class="text-rose-600 font-bold">CREDITED</span>.
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .py-12 { padding: 0 !important; }
            nav, .no-print { display: none !important; }
            .shadow-xl { box-shadow: none !important; border: 1px solid #ddd !important; }
            body { background: white !important; }
        }
    </style>
</x-app-layout>
