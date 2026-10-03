<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div
                class="bg-white dark:bg-gray-800 overflow-visible shadow-xl sm:rounded-3xl border border-gray-100 dark:border-gray-700 mb-8 mt-10">
                <div class="p-8">
                    <h2 class="text-2xl font-black text-gray-800 dark:text-white mb-6 tracking-tighter uppercase italic">
                        Party Ledger Report</h2>

                    <form action="{{ route('reports.party-ledger') }}" method="GET"
                        class="grid grid-cols-1 md:grid-cols-5 gap-6 items-end">
                        <div class="md:col-span-1" x-data="{
                            open: false,
                            selectedIndex: -1,
                            search: {{ json_encode($selectedPartyId ? $parties->find($selectedPartyId)->name : '') }},
                            partyId: {{ json_encode($selectedPartyId) }},
                            parties: {{ json_encode($parties) }},
                            get filteredParties() {
                                if (!this.search) return this.parties.slice(0, 10);
                                const s = this.search.toLowerCase();
                                return this.parties.filter(p =>
                                    p.name.toLowerCase().includes(s) ||
                                    (p.type && p.type.toLowerCase().includes(s))
                                ).slice(0, 20);
                            },
                            selectParty(p) {
                                this.partyId = p.id;
                                this.search = p.name;
                                this.open = false;
                                this.selectedIndex = -1;
                            },
                            onArrowDown() {
                                if (!this.open) { this.open = true; return; }
                                if (this.selectedIndex < this.filteredParties.length - 1) {
                                    this.selectedIndex++;
                                    this.scrollToActive();
                                }
                            },
                            onArrowUp() {
                                if (this.selectedIndex > 0) {
                                    this.selectedIndex--;
                                    this.scrollToActive();
                                }
                            },
                            onEnter() {
                                if (this.selectedIndex > -1 && this.filteredParties[this.selectedIndex]) {
                                    this.selectParty(this.filteredParties[this.selectedIndex]);
                                }
                            },
                            scrollToActive() {
                                this.$nextTick(() => {
                                    const container = this.$refs.dropdown;
                                    if (container) {
                                        const active = container.querySelector('.active-item');
                                        if (active) {
                                            active.scrollIntoView({ block: 'nearest' });
                                        }
                                    }
                                });
                            }
                        }">
                            <x-input-label for="party_search" :value="__('Select Party')"
                                class="text-xs font-black uppercase tracking-widest mb-1" />
                            <div class="relative">
                                <input type="hidden" name="party_id" x-model="partyId">
                                <input type="text" id="party_search" x-model="search" @focus="open = true"
                                    @click.away="open = false" @keydown.down.prevent="onArrowDown()"
                                    @keydown.up.prevent="onArrowUp()" @keydown.enter.prevent="onEnter()"
                                    placeholder="Search Party..."
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-xl text-xs uppercase font-bold"
                                    autocomplete="off" required>

                                <div x-show="open && filteredParties.length > 0" x-ref="dropdown"
                                    class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto">
                                    <template x-for="(p, index) in filteredParties" :key="p.id">
                                        <div @click="selectParty(p)"
                                            :class="{
                                                'bg-indigo-50 dark:bg-indigo-900/40 active-item': selectedIndex ===
                                                    index
                                            }"
                                            class="px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 cursor-pointer border-b last:border-0 dark:border-gray-700 transition">
                                            <div class="flex flex-col">
                                                <span class="text-xs font-black uppercase text-gray-900 dark:text-white"
                                                    x-text="p.name"></span>
                                                <span
                                                    class="text-[9px] text-indigo-500 font-bold uppercase tracking-widest mt-0.5"
                                                    x-text="p.type"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div>
                            <x-input-label for="start_date" :value="__('Start Date')"
                                class="text-xs font-black uppercase tracking-widest mb-1" />
                            <x-text-input type="date" name="start_date" id="start_date"
                                class="block mt-1 w-full !rounded-xl text-xs" :value="$startDate" />
                        </div>
                        <div>
                            <x-input-label for="end_date" :value="__('End Date')"
                                class="text-xs font-black uppercase tracking-widest mb-1" />
                            <x-text-input type="date" name="end_date" id="end_date"
                                class="block mt-1 w-full !rounded-xl text-xs" :value="$endDate" />
                        </div>
                        <div class="flex space-x-2">
                            <x-primary-button
                                class="flex-1 !rounded-xl !py-3 bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-200 dark:shadow-none justify-center">
                                {{ __('Generate') }}
                            </x-primary-button>
                            <a href="{{ route('reports.party-ledger') }}"
                                class="inline-flex items-center px-4 py-3 bg-gray-100 dark:bg-gray-700 border border-transparent rounded-xl font-black text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if ($selectedPartyId)
                @php
                    $finalBalance = $openingBalance + ($totalDebit - $totalCredit);
                @endphp

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700">
                        <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-1">Opening
                            Balance</span>
                        <span
                            class="text-xl font-black {{ $openingBalance >= 0 ? 'text-indigo-600' : 'text-orange-600' }}">
                            {{ number_format(abs($openingBalance), 2) }} {{ $openingBalance >= 0 ? 'Dr' : 'Cr' }}
                        </span>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 border-l-4 border-l-red-500">
                        <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-1">Total
                            Debit (Period)</span>
                        <span class="text-xl font-black text-red-600 uppercase italic tracking-tighter">
                            {{ number_format($totalDebit, 2) }}
                        </span>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 border-l-4 border-l-green-500">
                        <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-1">Total
                            Credit (Period)</span>
                        <span class="text-xl font-black text-green-600 uppercase italic tracking-tighter">
                            {{ number_format($totalCredit, 2) }}
                        </span>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 bg-indigo-50/50">
                        <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-1">Final
                            Net Balance</span>
                        <span
                            class="text-xl font-black {{ $finalBalance >= 0 ? 'text-indigo-600' : 'text-orange-600' }}">
                            {{ number_format(abs($finalBalance), 2) }} {{ $finalBalance >= 0 ? 'Dr' : 'Cr' }}
                        </span>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-3xl border border-gray-100 dark:border-gray-700">
                    <div class="p-8">
                        <div class="flex justify-between items-center mb-8 border-b dark:border-gray-700 pb-4">
                            <div>
                                <h3
                                    class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tighter italic">
                                    {{ $parties->find($selectedPartyId)->name }}
                                </h3>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">
                                    Statement from <span class="text-indigo-500">{{ $startDate }}</span> to <span
                                        class="text-indigo-500">{{ $endDate }}</span>
                                </p>
                            </div>
                            <div class="text-right flex items-center gap-2">
                                <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" target="_blank"
                                    class="group p-3 rounded-xl bg-gray-50 dark:bg-gray-700 hover:bg-red-600 hover:text-white transition-all text-gray-500 flex items-center shadow-inner">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                        </path>
                                    </svg>
                                    <span class="text-xs font-black uppercase tracking-widest">Download PDF</span>
                                </a>
                                @php
                                    $ledgerParty = $parties->find($selectedPartyId);
                                    $rawPhone = preg_replace('/\D/', '', $ledgerParty->phone ?? '');
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
                                    $ledgerPdfUrl = \Illuminate\Support\Facades\URL::signedRoute('reports.party-ledger.public-pdf', [
                                        'party_id' => $selectedPartyId,
                                        'start_date' => $startDate,
                                        'end_date' => $endDate,
                                        'pdf' => 1,
                                    ]);
                                    $template = $settings->whatsapp_ledger_template ?: "Assalam-o-Alaikum {party_name}, aapka statement bhej raha hun.\nPeriod: {start_date} to {end_date}\nBalance: {balance}\n\nLedger PDF: {ledger_link}\n\nShukriya,\n{company_name}";
                                    $whatsappMessage = strtr($template, [
                                        '{party_name}' => $ledgerParty->name,
                                        '{start_date}' => $startDate,
                                        '{end_date}' => $endDate,
                                        '{balance}' => $settings->currency_symbol . ' ' . number_format(abs($finalBalance), 2) . ' ' . ($finalBalance >= 0 ? 'Dr' : 'Cr'),
                                        '{ledger_link}' => $ledgerPdfUrl,
                                        '{company_name}' => $settings->company_name,
                                    ]);
                                    $whatsappMessage .= "\n\nDeveloped by Invenco | Tel: +92 324 3980274";
                                @endphp
                                @if ($whatsappPhone)
                                    <a href="https://wa.me/{{ $whatsappPhone }}?text={{ urlencode($whatsappMessage) }}"
                                        target="_blank" rel="noopener"
                                        class="group p-3 rounded-xl bg-gray-50 dark:bg-gray-700 hover:bg-[#25D366] hover:text-white transition-all text-gray-500 flex items-center shadow-inner">
                                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                                            <path
                                                d="M12.001 2C6.478 2 2 6.477 2 12c0 1.905.529 3.686 1.446 5.202L2.05 22l4.926-1.362A9.951 9.951 0 0012.001 22c5.523 0 10-4.477 10-10S17.524 2 12.001 2zm0 18.099a8.075 8.075 0 01-4.116-1.126l-.295-.176-3.001.83.822-2.965-.192-.304A8.076 8.076 0 013.9 12c0-4.472 3.627-8.099 8.1-8.099 4.472 0 8.099 3.627 8.099 8.1 0 4.472-3.627 8.098-8.099 8.098z" />
                                        </svg>
                                        <span class="text-xs font-black uppercase tracking-widest">Send on WhatsApp</span>
                                    </a>
                                @else
                                    <span class="p-3 rounded-xl bg-gray-100 dark:bg-gray-900 text-gray-400 flex items-center shadow-inner cursor-not-allowed"
                                        title="Party's phone number is not set">
                                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12.001 2C6.478 2 2 6.477 2 12c0 1.905.529 3.686 1.446 5.202L2.05 22l4.926-1.362A9.951 9.951 0 0012.001 22c5.523 0 10-4.477 10-10S17.524 2 12.001 2zm0 18.099a8.075 8.075 0 01-4.116-1.126l-.295-.176-3.001.83.822-2.965-.192-.304A8.076 8.076 0 013.9 12c0-4.472 3.627-8.099 8.1-8.099 4.472 0 8.099 3.627 8.099 8.1 0 4.472-3.627 8.098-8.099 8.098z" />
                                        </svg>
                                        <span class="text-xs font-black uppercase tracking-widest">No Phone</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left border-collapse">
                                <thead>
                                    <tr
                                        class="text-[10px] text-gray-400 uppercase bg-gray-50/50 dark:bg-gray-700/50 font-black tracking-widest">
                                        <th class="px-6 py-4 border-b dark:border-gray-600">Date</th>
                                        <th class="px-6 py-4 border-b dark:border-gray-600">Details / Reference</th>
                                        <th class="px-6 py-4 border-b dark:border-gray-600 text-right">Debit</th>
                                        <th class="px-6 py-4 border-b dark:border-gray-600 text-right">Credit</th>
                                        <th class="px-6 py-4 border-b dark:border-gray-600 text-right">Balance</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <!-- Opening Balance Row -->
                                    <tr class="bg-indigo-50/20 dark:bg-indigo-900/10">
                                        <td class="px-6 py-4 text-xs font-bold text-gray-400">{{ $startDate }}</td>
                                        <td
                                            class="px-6 py-4 uppercase font-black text-[10px] tracking-widest italic text-indigo-400">
                                            Opening Balance Brought Forward</td>
                                        <td class="px-6 py-4 text-right">-</td>
                                        <td class="px-6 py-4 text-right">-</td>
                                        <td
                                            class="px-6 py-4 text-right text-indigo-600 dark:text-indigo-400 font-mono font-black">
                                            {{ number_format(abs($openingBalance), 2) }}
                                            {{ $openingBalance >= 0 ? 'Dr' : 'Cr' }}
                                        </td>
                                    </tr>

                                    @php $currentBalance = $openingBalance; @endphp
                                    @foreach ($entries as $entry)
                                        @php $currentBalance += ($entry->debit - $entry->credit); @endphp
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition group">
                                            <td class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400">
                                                {{ date('d M, Y', strtotime($entry->date)) }}
                                            </td>
                                            <td class="px-6 py-4 text-xs">
                                                <div
                                                    class="font-black text-gray-800 dark:text-gray-200 uppercase tracking-tight">
                                                    {{ $entry->note }}</div>
                                                <div
                                                    class="text-[9px] text-gray-400 uppercase tracking-tighter mt-0.5 font-bold flex flex-wrap gap-2 items-center">
                                                    @php
                                                        $route = null;
                                                        $params = [$entry->reference_id];

                                                        switch (strtolower($entry->reference_type)) {
                                                            case 'sale':
                                                                $route = 'sales.show';
                                                                break;
                                                            case 'purchase':
                                                                $route = 'purchases.show';
                                                                break;
                                                            case 'cargo_charge':
                                                                $route = 'purchases.show';
                                                                break;
                                                            case 'expense':
                                                                $route = 'expenses.index';
                                                                break;
                                                            case 'payment':
                                                                $route = 'payments.show';
                                                                break;
                                                            case 'opening_stock':
                                                                $route = 'opening-stocks.index';
                                                                break;
                                                            case 'transfer':
                                                                $route = 'account-transfers.index';
                                                                break;
                                                        }
                                                    @endphp

                                                    <span>
                                                        @if ($route)
                                                            <a href="{{ route($route, $params) }}"
                                                                class="text-indigo-400 hover:text-indigo-600 hover:underline">
                                                                {{ $entry->reference_type }}
                                                                @if (in_array(strtolower($entry->reference_type), ['sale', 'purchase']))
                                                                    {{ $settings->invoice_prefix }}{{ $entry->reference_id }}
                                                                @else
                                                                    #{{ $entry->reference_id }}
                                                                @endif
                                                            </a>
                                                        @else
                                                            <span class="text-indigo-400">
                                                                {{ $entry->reference_type }}
                                                                #{{ $entry->reference_id }}
                                                            </span>
                                                        @endif
                                                    </span>
                                                    @if ($entry->warehouse)
                                                        <span
                                                            class="bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 px-1.5 py-0.5 rounded font-black tracking-tighter">
                                                            📦 {{ $entry->warehouse->name }}
                                                        </span>
                                                    @endif
                                                    @if ($entry->contra_account_name)
                                                        <span
                                                            class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-1.5 py-0.5 rounded font-black tracking-tighter">
                                                            A/C: {{ $entry->contra_account_name }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td
                                                class="px-6 py-4 text-right text-red-600 dark:text-red-400 font-mono font-black italic">
                                                {{ $entry->debit > 0 ? number_format($entry->debit, 2) : '-' }}
                                            </td>
                                            <td
                                                class="px-6 py-4 text-right text-green-600 dark:text-green-400 font-mono font-black italic">
                                                {{ $entry->credit > 0 ? number_format($entry->credit, 2) : '-' }}
                                            </td>
                                            <td
                                                class="px-6 py-4 text-right font-mono font-black {{ $currentBalance >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-orange-600' }}">
                                                {{ number_format(abs($currentBalance), 2) }}
                                                {{ $currentBalance >= 0 ? 'Dr' : 'Cr' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-100 dark:bg-gray-700 font-black">
                                        <td colspan="2"
                                            class="px-6 py-4 text-right uppercase tracking-widest text-[10px]">Total
                                            for
                                            Period</td>
                                        <td class="px-6 py-4 text-right font-mono text-red-600 italic">
                                            {{ number_format($totalDebit, 2) }}</td>
                                        <td class="px-6 py-4 text-right font-mono text-green-600 italic">
                                            {{ number_format($totalCredit, 2) }}</td>
                                        <td
                                            class="px-6 py-4 text-right font-mono text-lg {{ $currentBalance >= 0 ? 'text-indigo-600' : 'text-orange-600' }}">
                                            {{ $settings->currency_symbol }}
                                            {{ number_format(abs($currentBalance), 2) }}
                                            {{ $currentBalance >= 0 ? 'Dr' : 'Cr' }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div
                    class="bg-white dark:bg-gray-800 p-20 text-center rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700">
                    <p class="text-gray-400 italic font-bold">Please select a party and date range to view the ledger.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
