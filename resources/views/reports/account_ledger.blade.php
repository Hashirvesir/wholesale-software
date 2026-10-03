<?php
/**
 * Account Ledger Report View
 * Displays detailed transactions for a specific Chart of Account entry.
 */
?>
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div
                class="bg-white dark:bg-gray-800 overflow-visible shadow-xl sm:rounded-3xl border border-gray-100 dark:border-gray-700 mb-8 mt-10">
                <div class="p-8">
                    <h2 class="text-2xl font-black text-gray-800 dark:text-white mb-6 tracking-tighter uppercase italic">
                        Account General Ledger</h2>

                    <form action="{{ route('reports.account-ledger') }}" method="GET"
                        class="grid grid-cols-1 md:grid-cols-5 gap-6 items-end">
                        <div class="md:col-span-1" x-data="{
                            open: false,
                            selectedIndex: -1,
                            search: {{ json_encode($selectedAccountId ? $accounts->find($selectedAccountId)->name : '') }},
                            accountId: {{ json_encode($selectedAccountId) }},
                            accounts: {{ json_encode($accounts) }},
                            get filteredAccounts() {
                                if (!this.search) return this.accounts.slice(0, 10);
                                const s = this.search.toLowerCase();
                                return this.accounts.filter(a =>
                                    a.name.toLowerCase().includes(s) ||
                                    (a.type && a.type.toLowerCase().includes(s))
                                ).slice(0, 20);
                            },
                            selectAccount(a) {
                                this.accountId = a.id;
                                this.search = a.name;
                                this.open = false;
                                this.selectedIndex = -1;
                            },
                            onArrowDown() {
                                if (!this.open) { this.open = true; return; }
                                if (this.selectedIndex < this.filteredAccounts.length - 1) {
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
                                if (this.selectedIndex > -1 && this.filteredAccounts[this.selectedIndex]) {
                                    this.selectAccount(this.filteredAccounts[this.selectedIndex]);
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
                            <x-input-label for="account_search" :value="__('Select Account')"
                                class="text-xs font-black uppercase tracking-widest mb-1" />
                            <div class="relative">
                                <input type="hidden" name="account_id" x-model="accountId">
                                <input type="text" id="account_search" x-model="search" @focus="open = true"
                                    @click.away="open = false" @keydown.down.prevent="onArrowDown()"
                                    @keydown.up.prevent="onArrowUp()" @keydown.enter.prevent="onEnter()"
                                    placeholder="Search Account..."
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-xl text-xs uppercase font-bold"
                                    autocomplete="off" required>

                                <div x-show="open && filteredAccounts.length > 0" x-ref="dropdown"
                                    class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-xl shadow-2xl max-h-60 overflow-y-auto">
                                    <template x-for="(a, index) in filteredAccounts" :key="a.id">
                                        <div @click="selectAccount(a)"
                                            :class="{
                                                'bg-indigo-50 dark:bg-indigo-900/40 active-item': selectedIndex ===
                                                    index
                                            }"
                                            class="px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 cursor-pointer border-b last:border-0 dark:border-gray-700 transition">
                                            <div class="flex flex-col">
                                                <span class="text-xs font-black uppercase text-gray-900 dark:text-white"
                                                    x-text="a.name"></span>
                                                <span
                                                    class="text-[9px] text-indigo-500 font-bold uppercase tracking-widest mt-0.5"
                                                    x-text="a.type"></span>
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
                            <a href="{{ route('reports.account-ledger') }}"
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

            @if ($selectedAccountId)
                @php
                    $account = $accounts->find($selectedAccountId);
                    $isDebitType = in_array($account->type, ['Asset', 'Expense']);
                    $finalBalance =
                        $openingBalance + ($isDebitType ? $totalDebit - $totalCredit : $totalCredit - $totalDebit);
                @endphp

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700">
                        <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-1">Opening
                            Balance</span>
                        <span
                            class="text-xl font-black {{ $openingBalance >= 0 ? 'text-indigo-600' : 'text-orange-600' }}">
                            {{ number_format(abs($openingBalance), 2) }}
                            {{ $openingBalance >= 0 ? ($isDebitType ? 'Dr' : 'Cr') : ($isDebitType ? 'Cr' : 'Dr') }}
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
                            Balance</span>
                        <span
                            class="text-xl font-black {{ $finalBalance >= 0 ? 'text-indigo-600' : 'text-orange-600' }}">
                            {{ number_format(abs($finalBalance), 2) }}
                            {{ $finalBalance >= 0 ? ($isDebitType ? 'Dr' : 'Cr') : ($isDebitType ? 'Cr' : 'Dr') }}
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
                                    {{ $account->name }}
                                </h3>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">
                                    Account Ledger from <span class="text-indigo-500">{{ $startDate }}</span> to
                                    <span class="text-indigo-500">{{ $endDate }}</span>
                                </p>
                            </div>
                            <div class="text-right">
                                <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" target="_blank"
                                    class="group p-3 rounded-xl bg-gray-50 dark:bg-gray-700 hover:bg-red-600 hover:text-white transition-all text-gray-500 flex items-center shadow-inner">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                        </path>
                                    </svg>
                                    <span class="text-xs font-black uppercase tracking-widest">Download PDF</span>
                                </a>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left border-collapse">
                                <thead>
                                    <tr
                                        class="text-[10px] text-gray-400 uppercase bg-gray-50/50 dark:bg-gray-700/50 font-black tracking-widest">
                                        <th class="px-6 py-4 border-b dark:border-gray-600">Date</th>
                                        <th class="px-6 py-4 border-b dark:border-gray-600">Details / Party</th>
                                        <th class="px-6 py-4 border-b dark:border-gray-600">Reference</th>
                                        <th class="px-6 py-4 border-b dark:border-gray-600 text-right">Debit</th>
                                        <th class="px-6 py-4 border-b dark:border-gray-600 text-right">Credit</th>
                                        <th class="px-6 py-4 border-b dark:border-gray-600 text-right">Balance</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <!-- Opening Balance Row -->
                                    <tr class="bg-indigo-50/20 dark:bg-indigo-900/10">
                                        <td class="px-6 py-4 text-xs font-bold text-gray-400">{{ $startDate }}</td>
                                        <td colspan="2"
                                            class="px-6 py-4 uppercase font-black text-[10px] tracking-widest italic text-indigo-400">
                                            Opening Balance Brought Forward</td>
                                        <td class="px-6 py-4 text-right">-</td>
                                        <td class="px-6 py-4 text-right">-</td>
                                        <td
                                            class="px-6 py-4 text-right text-indigo-600 dark:text-indigo-400 font-mono font-black">
                                            {{ number_format(abs($openingBalance), 2) }}
                                            {{ $openingBalance >= 0 ? ($isDebitType ? 'Dr' : 'Cr') : ($isDebitType ? 'Cr' : 'Dr') }}
                                        </td>
                                    </tr>

                                    @php $currentBalance = $openingBalance; @endphp
                                    @foreach ($entries as $entry)
                                        @php
                                            $impact = $isDebitType
                                                ? $entry->debit - $entry->credit
                                                : $entry->credit - $entry->debit;
                                            $currentBalance += $impact;
                                        @endphp
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition group">
                                            <td class="px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400">
                                                {{ date('d M, Y', strtotime($entry->date)) }}
                                            </td>
                                            <td class="px-6 py-4 text-xs">
                                                <div
                                                    class="font-black text-gray-800 dark:text-gray-200 uppercase tracking-tight">
                                                    {{ $entry->note }}</div>
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    @if ($entry->contra_account_name)
                                                        <span
                                                            class="text-[9px] bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-1.5 py-0.5 rounded font-black uppercase tracking-tighter">
                                                            A/C: {{ $entry->contra_account_name }}
                                                        </span>
                                                    @endif
                                                    @if ($entry->party)
                                                        <span
                                                            class="text-[9px] bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 px-1.5 py-0.5 rounded font-black uppercase tracking-tighter">
                                                            Party: {{ $entry->party->name }}
                                                        </span>
                                                    @endif
                                                    @if ($entry->warehouse)
                                                        <span
                                                            class="text-[9px] bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 px-1.5 py-0.5 rounded font-black uppercase tracking-tighter">
                                                            📦 {{ $entry->warehouse->name }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-[10px] uppercase font-bold text-gray-400">
                                                @php
                                                    $route = null;
                                                    $params = ['id' => $entry->reference_id];

                                                    switch (strtolower($entry->reference_type)) {
                                                        case 'sale':
                                                            $route = 'sales.show';
                                                            $params = [$entry->reference_id];
                                                            break;
                                                        case 'purchase':
                                                            $route = 'purchases.show';
                                                            $params = [$entry->reference_id];
                                                            break;
                                                        case 'cargo_charge':
                                                            $route = 'purchases.show';
                                                            $params = [$entry->reference_id];
                                                            break;
                                                        case 'expense':
                                                            $route = 'expenses.index';
                                                            break;
                                                        case 'payment':
                                                            $route = 'payments.show';
                                                            $params = [$entry->reference_id];
                                                            break;
                                                        case 'opening_stock':
                                                            $route = 'opening-stocks.index';
                                                            break;
                                                        case 'transfer':
                                                            $route = 'account-transfers.index';
                                                            break;
                                                    }
                                                @endphp

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
                                                    {{ $entry->reference_type }}
                                                    @if (in_array(strtolower($entry->reference_type), ['sale', 'purchase']))
                                                        {{ $settings->invoice_prefix }}{{ $entry->reference_id }}
                                                    @else
                                                        #{{ $entry->reference_id }}
                                                    @endif
                                                @endif
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
                                                {{ $currentBalance >= 0 ? ($isDebitType ? 'Dr' : 'Cr') : ($isDebitType ? 'Cr' : 'Dr') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-100 dark:bg-gray-700 font-black">
                                        <td colspan="3"
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
                                            {{ $currentBalance >= 0 ? ($isDebitType ? 'Dr' : 'Cr') : ($isDebitType ? 'Cr' : 'Dr') }}
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
                    <p class="text-gray-400 italic font-bold">Please select an account and date range to view the
                        ledger.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
