<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl p-8 mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <!-- Invoice Header -->
                <!-- Invoice Header -->
                <div class="p-8 border-b dark:border-gray-700">
                    <!-- Company Info (Visible on Print) -->
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            @if ($settings->company_logo)
                                <img src="{{ asset($settings->company_logo) }}" alt="Company Logo"
                                    class="h-16 w-auto mb-2">
                            @endif
                            <h1 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tighter">
                                {{ $settings->company_name }}</h1>
                            <p class="text-sm text-gray-500">{{ $settings->company_address }}</p>
                            <p class="text-sm text-gray-500">{{ $settings->company_phone }} |
                                {{ $settings->company_email }}</p>
                        </div>
                        <div class="text-right">
                            <h2 class="text-4xl font-black text-gray-200 dark:text-gray-700 uppercase tracking-tighter">
                                PURCHASE</h2>
                        </div>
                    </div>

                    <div class="flex justify-between items-start">
                        <div>
                            <h2
                                class="text-xl font-bold text-primary-600 dark:text-primary-400 italic tracking-tighter uppercase">
                                Purchase Invoice</h2>
                            <p class="text-sm text-gray-500 mt-1">Invoice ID: <span
                                    class="font-bold text-gray-700 dark:text-gray-300">{{ $settings->invoice_prefix }}{{ $purchase->id }}</span>
                            </p>
                            <p class="text-sm text-gray-500">Date: <span
                                    class="font-bold text-gray-700 dark:text-gray-300">{{ $purchase->date }}</span></p>
                        </div>
                        <div class="text-right">
                            <span
                                class="px-3 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full text-xs font-bold uppercase tracking-widest italic">Stock
                                Received</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-8 mt-8">
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Vendor
                                Information</h4>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $purchase->party->name }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $purchase->party->phone }}</p>
                            <p class="text-sm text-gray-500 italic">{{ $purchase->party->address }}</p>
                        </div>
                        <div class="text-right">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Stock Destination
                            </h4>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                {{ $purchase->warehouse->name }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $purchase->warehouse->location }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Invoice Body -->
                <div class="p-0">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        @php $hasCargoItems = $purchase->items->sum('cargo_amount') > 0; @endphp
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th
                                    class="px-8 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">
                                    Product Details</th>
                                <th
                                    class="px-8 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">
                                    Qty</th>
                                <th
                                    class="px-8 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">
                                    Price ({{ $settings->currency_symbol }})</th>
                                @if ($hasCargoItems)
                                    <th
                                        class="px-8 py-4 text-right text-xs font-bold text-orange-500 dark:text-orange-400 uppercase">
                                        Cargo</th>
                                @endif
                                <th
                                    class="px-8 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">
                                    Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($purchase->items as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-8 py-4">
                                        <div class="font-bold text-gray-900 dark:text-gray-100">
                                            {{ $item->product->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->product->sku }}</div>
                                    </td>
                                    <td class="px-8 py-4 text-center font-bold text-gray-700 dark:text-gray-300">
                                        {{ number_format($item->qty, 2) }}
                                    </td>
                                    <td class="px-8 py-4 text-right text-gray-600 dark:text-gray-400 font-medium">
                                        {{ $settings->currency_symbol }} {{ number_format($item->price_pkr, 2) }}
                                    </td>
                                    @if ($hasCargoItems)
                                        <td class="px-8 py-4 text-right text-orange-600 dark:text-orange-400 font-medium">
                                            {{ $item->cargo_amount > 0 ? $settings->currency_symbol . ' ' . number_format($item->cargo_amount, 2) : '—' }}
                                        </td>
                                    @endif
                                    <td class="px-8 py-4 text-right font-black text-gray-900 dark:text-gray-100">
                                        {{ $settings->currency_symbol }} {{ number_format($item->total, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Invoice Footer -->
                <div class="p-8 bg-gray-50 dark:bg-gray-900/50 rounded-b-lg">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
                        <div class="max-w-sm">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Notes</h4>
                            <p class="text-sm text-gray-700 dark:text-gray-300 italic">
                                {{ $purchase->note ?? 'No additional notes provided.' }}</p>
                        </div>
                        <div class="w-full md:w-64 space-y-2">
                            <div
                                class="flex justify-between text-sm text-gray-500 font-bold border-b dark:border-gray-700 pb-2 mb-2">
                                <span>Total Product Quantity:</span>
                                <span
                                    class="text-primary-600 dark:text-primary-400 font-black">{{ number_format($purchase->items->sum('qty'), 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Sub-Total:</span>
                                <span>{{ $settings->currency_symbol }}
                                    {{ number_format($purchase->total_amount, 2) }}</span>
                            </div>
                            @if ($purchase->currency == 'RMB')
                                <div class="flex justify-between text-xs text-primary-600 italic">
                                    <span>Exchange Rate (RMB):</span>
                                    <span>{{ $purchase->exchange_rate }}</span>
                                </div>
                            @endif
                            @if ($purchase->cargoCharge)
                                <div class="flex justify-between text-sm text-indigo-600 dark:text-indigo-400 font-medium border-t dark:border-gray-700 pt-2">
                                    <span>Cargo Charge ({{ $purchase->cargoCharge->cargoParty->name }}):</span>
                                    <span>{{ $settings->currency_symbol }} {{ number_format($purchase->cargoCharge->total_amount, 2) }}</span>
                                </div>
                                @if ($purchase->cargoCharge->total_weight > 0)
                                    <div class="flex justify-between text-xs text-gray-400 italic">
                                        <span>{{ number_format($purchase->cargoCharge->total_weight, 2) }} kg × {{ $settings->currency_symbol }} {{ number_format($purchase->cargoCharge->rate_per_kg, 2) }}/kg</span>
                                    </div>
                                @else
                                    <div class="text-xs text-gray-400 italic">Per-item cargo</div>
                                @endif
                            @endif
                            @php
                                $grandTotal = $purchase->total_amount;
                                if ($purchase->cargoCharge && $purchase->cargoCharge->total_weight > 0) {
                                    $grandTotal += $purchase->cargoCharge->total_amount;
                                }
                            @endphp
                            <div
                                class="flex justify-between text-xl font-black text-primary-600 dark:text-primary-400 pt-2 border-t dark:border-gray-700">
                                <span>Total:</span>
                                <span>{{ $settings->currency_symbol }}
                                    {{ number_format($grandTotal, 2) }}</span>
                            </div>
                            @php
                                $prevBalance = $purchase->party->getCurrentBalance() + ($grandTotal - $purchase->paid_amount);
                            @endphp
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Prev. Balance:</span>
                                <span class="font-bold">
                                    {{ $settings->currency_symbol }} {{ number_format(abs($prevBalance), 2) }}
                                    {{ $prevBalance >= 0 ? '(Rec)' : '(Pay)' }}
                                </span>
                            </div>
                            @if ($purchase->paid_amount > 0)
                                <div class="flex justify-between text-sm text-green-600 dark:text-green-400 font-bold pt-2 border-t dark:border-gray-700">
                                    <span>Paid:</span>
                                    <div class="text-right">
                                        <span>{{ $settings->currency_symbol }}
                                            {{ number_format($purchase->paid_amount, 2) }}</span>
                                        @if ($purchase->paymentSplits->isNotEmpty())
                                            @foreach ($purchase->paymentSplits as $split)
                                                <br><span class="text-[10px] text-gray-400 uppercase tracking-widest">
                                                    {{ $split->account->name ?? 'Unknown' }}: {{ $settings->currency_symbol }} {{ number_format($split->amount, 2) }}
                                                </span>
                                            @endforeach
                                        @elseif ($purchase->account)
                                            <br><span class="text-[10px] text-gray-400 uppercase tracking-widest">({{ $purchase->account->name }})</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex justify-between text-sm font-black {{ $grandTotal - $purchase->paid_amount > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                    <span>Balance Due:</span>
                                    <span>{{ $settings->currency_symbol }}
                                        {{ number_format($grandTotal - $purchase->paid_amount, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div
                    class="mb-4 p-4 bg-green-100 dark:bg-green-900 border-l-4 border-green-500 text-green-700 dark:text-green-300 rounded-r-xl font-bold text-sm no-print">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mt-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 px-4 no-print">
                <a href="{{ route('purchases.index') }}"
                    class="text-sm text-gray-500 hover:text-gray-800 dark:hover:text-gray-300 font-bold flex items-center transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to List
                </a>
                <div class="flex flex-wrap gap-2 sm:justify-end">
                    <button onclick="window.print()"
                        class="px-4 sm:px-6 py-2 bg-green-600 text-white rounded-lg font-bold shadow-lg hover:shadow-xl transition transform hover:-translate-y-1 flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                            </path>
                        </svg>
                        Print Invoice
                    </button>
                    @unless ($purchase->cargoCharge)
                        <a href="{{ route('cargo-charges.create', ['purchase_id' => $purchase->id]) }}"
                            class="px-4 sm:px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold shadow-lg hover:shadow-xl transition transform hover:-translate-y-1 flex items-center text-sm">
                            Add Cargo Charges
                        </a>
                    @endunless
                    <a href="{{ route('purchases.pdf', $purchase->id) }}" target="_blank"
                        class="px-4 sm:px-6 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-lg font-bold shadow-lg hover:shadow-xl transition transform hover:-translate-y-1 flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                            </path>
                        </svg>
                        Download PDF
                    </a>
                    @php
                        $rawPhone = preg_replace('/\D/', '', $purchase->party->phone ?? '');
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
                        $publicPdfUrl = \Illuminate\Support\Facades\URL::signedRoute('purchases.public-pdf', ['purchase' => $purchase->id]);
                        $purchaseDue = $grandTotal - $purchase->paid_amount;
                        $prevBalance = $purchase->party->getCurrentBalance() + $purchaseDue;
                        $template = $settings->whatsapp_purchase_template ?: "Assalam-o-Alaikum {vendor_name}, Purchase Invoice #{invoice_id} tayyar hai.\nDate: {date} {time}\nStatus: {status}\nTotal: {total}\n{discount}Previous Balance: {previous_balance}\nBalance Due: {balance_due}\n\nInvoice PDF: {invoice_link}\n\nShukriya,\n{company_name}";
                        $whatsappMessage = strtr($template, [
                            '{vendor_name}' => $purchase->party->name,
                            '{invoice_id}' => $settings->invoice_prefix . $purchase->id,
                            '{date}' => $purchase->date,
                            '{time}' => $purchase->created_at->timezone(config('app.timezone'))->format('h:i A'),
                            '{status}' => $purchaseDue > 0 ? 'Balance Due' : 'Paid',
                            '{total}' => $settings->currency_symbol . ' ' . number_format($grandTotal, 2),
                            '{discount}' => $purchase->discount_amount > 0 ? ('Discount: ' . $settings->currency_symbol . ' ' . number_format($purchase->discount_amount, 2) . "\n") : '',
                            '{previous_balance}' => $settings->currency_symbol . ' ' . number_format(abs($prevBalance), 2) . ' ' . ($prevBalance >= 0 ? '(Rec)' : '(Pay)'),
                            '{balance_due}' => $settings->currency_symbol . ' ' . number_format($purchaseDue, 2),
                            '{invoice_link}' => $publicPdfUrl,
                            '{company_name}' => $settings->company_name,
                        ]);
                        $whatsappMessage .= "\n\nDeveloped by Invenco | Tel: +92 324 3980274";
                    @endphp
                    @if ($whatsappPhone)
                        <a href="https://wa.me/{{ $whatsappPhone }}?text={{ urlencode($whatsappMessage) }}"
                            target="_blank" rel="noopener"
                            class="px-4 sm:px-6 py-2 bg-[#25D366] text-white rounded-lg font-bold shadow-lg hover:shadow-xl transition transform hover:-translate-y-1 flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                                <path
                                    d="M12.001 2C6.478 2 2 6.477 2 12c0 1.905.529 3.686 1.446 5.202L2.05 22l4.926-1.362A9.951 9.951 0 0012.001 22c5.523 0 10-4.477 10-10S17.524 2 12.001 2zm0 18.099a8.075 8.075 0 01-4.116-1.126l-.295-.176-3.001.83.822-2.965-.192-.304A8.076 8.076 0 013.9 12c0-4.472 3.627-8.099 8.1-8.099 4.472 0 8.099 3.627 8.099 8.1 0 4.472-3.627 8.098-8.099 8.098z" />
                            </svg>
                            Send on WhatsApp
                        </a>
                    @else
                        <span class="px-4 sm:px-6 py-2 bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-lg font-bold flex items-center gap-2 cursor-not-allowed text-sm"
                            title="Vendor's phone number is not set">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12.001 2C6.478 2 2 6.477 2 12c0 1.905.529 3.686 1.446 5.202L2.05 22l4.926-1.362A9.951 9.951 0 0012.001 22c5.523 0 10-4.477 10-10S17.524 2 12.001 2zm0 18.099a8.075 8.075 0 01-4.116-1.126l-.295-.176-3.001.83.822-2.965-.192-.304A8.076 8.076 0 013.9 12c0-4.472 3.627-8.099 8.1-8.099 4.472 0 8.099 3.627 8.099 8.1 0 4.472-3.627 8.098-8.099 8.098z" />
                            </svg>
                            No Phone Number
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .py-12 {
                padding: 0;
            }

            nav,
            .no-print {
                display: none !important;
            }

            .shadow-xl {
                box-shadow: none !important;
                border: 1px solid #eee;
            }

            body {
                background: white !important;
            }
        }
    </style>
</x-app-layout>
