<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <!-- Main Invoice Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">

                    <!-- Header & Top Actions -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-6 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">
                                    Sale Invoice #{{ $settings->invoice_prefix }}{{ $sale->id }}
                                </h2>
                                @php $isPaid = ($sale->total_amount - $sale->paid_amount) <= 0.001; @endphp
                                @if ($isPaid)
                                    <span class="px-3 py-1 text-[11px] font-black uppercase tracking-wider rounded-full border bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-300 dark:border-emerald-700 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Paid in Full
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-[11px] font-black uppercase tracking-wider rounded-full border bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border-rose-300 dark:border-rose-700 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Balance Due
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Invoice Date: <span class="font-bold text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($sale->date)->format('d M, Y') }}</span>
                                @if ($sale->created_at)
                                    &bull; Recorded at {{ $sale->created_at->timezone(config('app.timezone'))->format('h:i A') }}
                                @endif
                            </p>
                        </div>

                        <!-- Top Action Buttons -->
                        <div class="flex flex-wrap items-center gap-2 no-print">
                            @can('return sales')
                                <a href="{{ route('sales.return', $sale->id) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                    Sales Return
                                </a>
                            @endcan

                            <a href="{{ route('sales.pdf', $sale->id) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                PDF
                            </a>

                            <a href="{{ route('sales.thermal', $sale->id) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-800 dark:bg-gray-700 hover:bg-black text-white text-xs font-bold rounded-xl shadow transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Thermal
                            </a>

                            @php
                                $rawPhone = preg_replace('/\D/', '', $sale->party->phone ?? '');
                                $whatsappPhone = null;
                                if ($rawPhone !== '') {
                                    if (str_starts_with($rawPhone, '0')) {
                                        $whatsappPhone = '92' . substr($rawPhone, 1);
                                    } elseif (str_starts_with($rawPhone, '92')) {
                                        $whatsappPhone = $rawPhone;
                                    } else {
                                        $whatsappPhone = '92' . $rawPhone;
                                    }
                                }
                                $publicPdfUrl = \Illuminate\Support\Facades\URL::signedRoute('sales.public-pdf', ['sale' => $sale->id]);
                                $saleDue = $sale->total_amount - $sale->paid_amount;
                                $prevBalance = $sale->party->getCurrentBalance() - $saleDue;
                                $template = $settings->whatsapp_sale_template ?: "Assalam-o-Alaikum {customer_name}, aapka Invoice #{invoice_id} taiyar hai.\nDate: {date} {time}\nStatus: {status}\nTotal: {total}\n{discount}Previous Balance: {previous_balance}\nBalance Due: {balance_due}\n\nInvoice PDF: {invoice_link}\n\nShukriya,\n{company_name}";
                                $whatsappMessage = strtr($template, [
                                    '{customer_name}' => $sale->party->name,
                                    '{invoice_id}' => $settings->invoice_prefix . $sale->id,
                                    '{date}' => $sale->date,
                                    '{time}' => $sale->created_at->timezone(config('app.timezone'))->format('h:i A'),
                                    '{status}' => $saleDue > 0 ? 'Balance Due' : 'Paid',
                                    '{total}' => ($settings->currency_symbol ?? 'Rs') . ' ' . number_format($sale->total_amount, 2),
                                    '{discount}' => $sale->discount_amount > 0 ? ('Discount: ' . ($settings->currency_symbol ?? 'Rs') . ' ' . number_format($sale->discount_amount, 2) . "\n") : '',
                                    '{previous_balance}' => ($settings->currency_symbol ?? 'Rs') . ' ' . number_format(abs($prevBalance), 2) . ' ' . ($prevBalance >= 0 ? '(Rec)' : '(Pay)'),
                                    '{balance_due}' => ($settings->currency_symbol ?? 'Rs') . ' ' . number_format($saleDue, 2),
                                    '{invoice_link}' => $publicPdfUrl,
                                    '{company_name}' => $settings->company_name,
                                ]);
                            @endphp

                            @if ($whatsappPhone)
                                <a href="https://wa.me/{{ $whatsappPhone }}?text={{ urlencode($whatsappMessage) }}" target="_blank"
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

                            <a href="{{ route('sales.index') }}"
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
                            <div class="text-sm font-bold text-gray-900 dark:text-gray-100 uppercase">{{ $sale->party->name }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">Phone: {{ $sale->party->phone ?? 'N/A' }}</div>
                            @if ($sale->party->address)
                                <div class="text-xs text-gray-500 mt-0.5">{{ $sale->party->address }}</div>
                            @endif
                            <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-600 dark:text-gray-400 flex justify-between items-center">
                                <span>Current Balance:</span>
                                <span class="{{ $sale->party->getCurrentBalance() >= 0 ? 'text-red-500' : 'text-emerald-500' }}">
                                    {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format(abs($sale->party->getCurrentBalance()), 2) }}
                                    {{ $sale->party->getCurrentBalance() >= 0 ? '(Due)' : '(Advance)' }}
                                </span>
                            </div>
                        </div>

                        <!-- Stock Source & Invoice Meta -->
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700">
                            <h4 class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Warehouse & Dispatch</h4>
                            <div class="text-xs text-gray-700 dark:text-gray-300 flex justify-between py-0.5">
                                <span class="text-gray-500">Stock Source:</span>
                                <span class="font-bold text-gray-900 dark:text-gray-100">{{ $sale->warehouse->name }}</span>
                            </div>
                            @if ($sale->warehouse->location)
                                <div class="text-xs text-gray-700 dark:text-gray-300 flex justify-between py-0.5">
                                    <span class="text-gray-500">Location:</span>
                                    <span class="font-medium text-gray-600 dark:text-gray-400">{{ $sale->warehouse->location }}</span>
                                </div>
                            @endif
                            <div class="text-xs text-gray-700 dark:text-gray-300 flex justify-between py-0.5">
                                <span class="text-gray-500">Sale Date:</span>
                                <span class="font-bold">{{ \Carbon\Carbon::parse($sale->date)->format('d M, Y') }}</span>
                            </div>
                            <div class="text-xs text-gray-700 dark:text-gray-300 flex justify-between py-0.5">
                                <span class="text-gray-500">Total Units:</span>
                                <span class="font-black text-primary-600">{{ number_format($sale->items->sum('qty'), 2) }}</span>
                            </div>
                        </div>

                        <!-- Settlement & Remarks -->
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
                            <div>
                                <h4 class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Payment Settlement</h4>
                                <div class="space-y-1">
                                    @if ($sale->paymentSplits->isNotEmpty())
                                        @foreach ($sale->paymentSplits as $split)
                                            <div class="text-xs flex justify-between items-center bg-white dark:bg-gray-800 p-1.5 rounded-lg border border-gray-200 dark:border-gray-700">
                                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $split->account->name ?? 'Account' }}</span>
                                                <span class="font-black text-emerald-600 dark:text-emerald-400 font-mono">{{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($split->amount, 2) }}</span>
                                            </div>
                                        @endforeach
                                    @elseif ($sale->account)
                                        <div class="text-xs flex justify-between items-center bg-white dark:bg-gray-800 p-1.5 rounded-lg border border-gray-200 dark:border-gray-700">
                                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ $sale->account->name }}</span>
                                            <span class="font-black text-emerald-600 dark:text-emerald-400 font-mono">{{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($sale->paid_amount, 2) }}</span>
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-500 italic">No immediate cash payment (Full Credit).</div>
                                    @endif
                                </div>
                            </div>
                            @if ($sale->note)
                                <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <span class="font-bold text-gray-700 dark:text-gray-300">Note:</span> {{ $sale->note }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Desktop Items Table -->
                    <div class="hidden md:block border border-gray-200 dark:border-gray-700 rounded-xl overflow-x-auto mb-6">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-100 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="py-3 px-4 w-12 text-center">#</th>
                                    <th class="py-3 px-4">Product Details</th>
                                    <th class="py-3 px-4 w-32">SKU</th>
                                    <th class="py-3 px-4 w-28 text-center">Unit</th>
                                    <th class="py-3 px-4 w-32 text-right">Quantity</th>
                                    <th class="py-3 px-4 w-36 text-right">Price ({{ $settings->currency_symbol ?? 'Rs' }})</th>
                                    <th class="py-3 px-4 w-36 text-right">Line Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y dark:divide-gray-700 bg-white dark:bg-gray-800">
                                @foreach ($sale->items as $index => $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                        <td class="py-3 px-4 text-center font-bold text-gray-400">{{ $index + 1 }}</td>
                                        <td class="py-3 px-4 font-bold text-gray-900 dark:text-gray-100">
                                            {{ $item->product->name ?? 'N/A' }}
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
                                        <td class="py-3 px-4 text-right font-bold text-gray-700 dark:text-gray-300 font-mono">
                                            {{ number_format($item->price, 2) }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-black text-gray-900 dark:text-gray-100 font-mono">
                                            {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($item->qty * $item->price, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Items Cards View -->
                    <div class="block md:hidden space-y-2.5 mb-6">
                        @foreach ($sale->items as $index => $item)
                            <div class="bg-gray-50 dark:bg-gray-900/60 p-3 rounded-xl border border-gray-200 dark:border-gray-700 space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 flex items-center justify-center text-[10px] font-black">
                                            {{ $index + 1 }}
                                        </span>
                                        <div>
                                            <h4 class="text-xs font-bold text-gray-900 dark:text-gray-100">{{ $item->product->name ?? 'N/A' }}</h4>
                                            <span class="text-[10px] text-gray-400 font-mono">{{ $item->product->sku ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-black font-mono text-gray-900 dark:text-gray-100">
                                        {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($item->qty * $item->price, 2) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-gray-500 pt-1 border-t border-gray-200/60 dark:border-gray-700/60">
                                    <span>Qty: <strong class="text-gray-800 dark:text-gray-200">{{ number_format($item->qty, 2) }} {{ $item->product->unit->short_name ?? '' }}</strong></span>
                                    <span>Rate: <strong class="text-gray-800 dark:text-gray-200">{{ number_format($item->price, 2) }}</strong></span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Financial Summary -->
                    <div class="flex justify-end">
                        <div class="w-full md:w-88 bg-gray-50 dark:bg-gray-900/40 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-2.5">
                            <div class="flex justify-between items-center text-xs font-bold text-gray-600 dark:text-gray-400">
                                <span>Sub-Total:</span>
                                <span class="font-black text-gray-900 dark:text-gray-100 font-mono">
                                    {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($sale->total_amount, 2) }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center text-xs font-bold text-gray-600 dark:text-gray-400">
                                <span>Previous Balance:</span>
                                <span class="font-mono font-bold {{ $prevBalance >= 0 ? 'text-red-500' : 'text-emerald-500' }}">
                                    {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format(abs($prevBalance), 2) }}
                                    {{ $prevBalance >= 0 ? '(Due)' : '(Adv)' }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                <span>Paid in this Bill:</span>
                                <span class="font-black font-mono">
                                    {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($sale->paid_amount, 2) }}
                                </span>
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-2.5 flex justify-between items-center">
                                <span class="text-sm font-black uppercase text-gray-900 dark:text-gray-100">Bill Balance:</span>
                                <span class="text-xl font-black font-mono {{ $saleDue > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format($saleDue, 2) }}
                                </span>
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-2 flex justify-between items-center text-xs font-bold">
                                <span class="text-gray-600 dark:text-gray-400">Net Customer Balance:</span>
                                <span class="font-mono {{ $sale->party->getCurrentBalance() >= 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ $settings->currency_symbol ?? 'Rs' }} {{ number_format(abs($sale->party->getCurrentBalance()), 2) }}
                                    {{ $sale->party->getCurrentBalance() >= 0 ? '(Rec)' : '(Pay)' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if ($settings->invoice_footer_note)
                        <div class="mt-6 p-4 rounded-xl bg-gray-50 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
                            <span class="font-bold uppercase text-gray-700 dark:text-gray-300">Terms & Conditions:</span>
                            <p class="mt-1">{{ $settings->invoice_footer_note }}</p>
                        </div>
                    @endif

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
