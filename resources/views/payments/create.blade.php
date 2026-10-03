<x-app-layout>
    <div class="py-12" x-data="paymentForm()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between mb-8 border-b dark:border-gray-700 pb-4">
                        <h3
                            class="text-2xl font-black text-gray-800 dark:text-gray-200 italic tracking-tighter uppercase">
                            Record Payment</h3>
                        <a href="{{ route('payments.index') }}"
                            class="text-sm font-bold text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 transition uppercase tracking-widest italic">Back
                            to List</a>
                    </div>

                    @if ($errors->any())
                        <div
                            class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded-r-xl">
                            <ul class="list-disc list-inside text-xs font-medium space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('error'))
                        <div
                            class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded-r-xl font-bold text-sm">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('payments.store') }}" method="POST" @submit.prevent="submitForm">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                            <!-- Payment Type -->
                            <div>
                                <x-input-label for="payment_type" :value="__('Payment Type')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <div class="grid grid-cols-2 gap-4 mt-2">
                                    <label class="cursor-pointer relative">
                                        <input type="radio" name="payment_type" value="received" x-model="type"
                                            class="sr-only">
                                        <div class="p-3 text-center rounded-xl border-2 transition"
                                            :class="type === 'received' ?
                                                'border-green-500 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 font-black' :
                                                'border-gray-100 dark:border-gray-700 text-gray-400'">
                                            RECEIVED
                                        </div>
                                    </label>
                                    <label class="cursor-pointer relative">
                                        <input type="radio" name="payment_type" value="paid" x-model="type"
                                            class="sr-only">
                                        <div class="p-3 text-center rounded-xl border-2 transition"
                                            :class="type === 'paid' ?
                                                'border-red-500 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 font-black' :
                                                'border-gray-100 dark:border-gray-700 text-gray-400'">
                                            PAID
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Date -->
                            <div>
                                <x-input-label for="date" :value="__('Transaction Date')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <x-text-input id="date" class="block mt-1 w-full !rounded-xl text-xs"
                                    type="date" name="date" x-model="date" min="2020-01-01" max="{{ date('Y-m-d') }}" required />
                            </div>

                            <!-- Warehouse (Optional) -->
                            <div>
                                <x-input-label for="warehouse_id" :value="__('Warehouse / Shop')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <select name="warehouse_id" id="warehouse_id"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-xl shadow-sm text-xs">
                                    <option value="">Default (Head Office)</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}"
                                            {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Party Search -->
                            <div class="relative" @click.away="partySearchOpen = false">
                                <x-input-label for="party_search" :value="__('Search Party (Customer/Vendor/Cargo)')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <div class="relative">
                                    <input type="text" x-model="partySearch" @focus="partySearchOpen = true"
                                        @input="partySearchOpen = true; party_id = ''; highlightedPartyIndex = -1"
                                        @keydown="handlePartySearchKey($event)"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-xl shadow-sm transition pl-10 text-xs"
                                        :class="{ 'border-red-500 ring-1 ring-red-500': errors.party_id }"
                                        placeholder="Search by Name..." autocomplete="off">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <input type="hidden" name="party_id" :value="party_id">
                                <div x-show="partySearchOpen && getFilteredParties().length > 0"
                                    class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-40 rounded-md py-1 text-xs ring-1 ring-black ring-opacity-5 overflow-auto border dark:border-gray-700 transition-all">
                                    <template x-for="(party, index) in getFilteredParties()" :key="party.id">
                                        <div @click="selectParty(party)"
                                            class="cursor-pointer select-none relative py-2 pl-3 pr-9 border-b last:border-0 dark:border-gray-700 hover:bg-primary-50 dark:hover:bg-primary-900 transition flex flex-col group"
                                            :class="highlightedPartyIndex === index ? 'bg-primary-100 dark:bg-primary-900' : ''">
                                            <div class="flex justify-between items-center">
                                                <div class="flex items-center">
                                                    <span
                                                        class="block truncate uppercase font-bold text-gray-900 dark:text-gray-100"
                                                        x-text="party.name"></span>
                                                    <span class="ml-2 text-[10px] text-gray-500 uppercase font-black"
                                                        x-text="'(' + party.type + ')'"></span>
                                                </div>
                                                <span class="text-[10px] font-bold"
                                                    :class="party.balance >= 0 ? 'text-green-600' : 'text-red-600'"
                                                    x-text="(party.balance >= 0 ? 'DR: ' : 'CR: ') + Math.abs(party.balance).toLocaleString()"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Account Search (Cash/Bank) -->
                            <div class="relative" @click.away="accountSearchOpen = false">
                                <x-input-label for="account_search" :value="__('Select Account (Cash/Bank)')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <div class="relative">
                                    <input type="text" x-model="accountSearch" @focus="accountSearchOpen = true"
                                        @input="accountSearchOpen = true; from_account_id = ''; highlightedAccountIndex = -1"
                                        @keydown="handleAccountSearchKey($event)"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-xl shadow-sm transition pl-10 text-xs"
                                        :class="{ 'border-red-500 ring-1 ring-red-500': errors.from_account_id }"
                                        placeholder="Search Cash or Bank..." autocomplete="off">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <input type="hidden" name="from_account_id" :value="from_account_id">
                                <div x-show="accountSearchOpen && getFilteredAccounts().length > 0"
                                    class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-40 rounded-md py-1 text-xs ring-1 ring-black ring-opacity-5 overflow-auto border dark:border-gray-700 transition-all">
                                    <template x-for="(account, index) in getFilteredAccounts()" :key="account.id">
                                        <div @click="selectAccount(account)"
                                            class="cursor-pointer select-none relative py-2 pl-3 pr-9 border-b last:border-0 dark:border-gray-700 hover:bg-primary-50 dark:hover:bg-primary-900 transition flex justify-between items-center group"
                                            :class="highlightedAccountIndex === index ?
                                                'bg-primary-100 dark:bg-primary-900' : ''">
                                            <span
                                                class="block truncate uppercase font-bold text-gray-900 dark:text-gray-100"
                                                x-text="account.name"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Amount -->
                            <div>
                                <x-input-label for="amount" :value="__('Amount (PKR)')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <x-text-input id="amount" x-model.number="amount"
                                    class="block mt-1 w-full !rounded-xl text-xs font-mono font-bold" type="number"
                                    step="0.01" name="amount" required ::class="{ 'border-red-500 ring-1 ring-red-500': errors.amount }" />
                            </div>

                            <!-- Note -->
                            <div>
                                <x-input-label for="note" :value="__('Note / Remarks')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <x-text-input id="note" x-model="note"
                                    class="block mt-1 w-full !rounded-xl text-xs" type="text" name="note"
                                    placeholder="Write any detail here..." />
                            </div>
                        </div>

                        <!-- Result Section -->
                        <div
                            class="bg-indigo-50 dark:bg-indigo-900/20 p-8 rounded-3xl border border-indigo-100 dark:border-indigo-800 mb-8 relative overflow-hidden">
                            <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
                                <div>
                                    <h4
                                        class="text-xs font-black text-indigo-400 uppercase tracking-[0.2em] mb-1 italic">
                                        Payment Summary</h4>
                                    <div class="flex items-baseline space-x-2">
                                        <span
                                            class="text-4xl font-black text-indigo-600 dark:text-indigo-400 font-mono">{{ $settings->currency_symbol }}
                                            <span
                                                x-text="amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></span>
                                    </div>
                                    <p class="text-[10px] text-indigo-400 font-bold mt-2 uppercase tracking-widest italic"
                                        x-show="party_id && from_account_id">
                                        Effect: <span x-text="type.toUpperCase()"></span> <span
                                            x-text="type === 'received' ? 'FROM' : 'TO'"></span> <span
                                            class="font-black underline" x-text="partySearch"></span> VIA <span
                                            class="font-black underline" x-text="accountSearch"></span>
                                    </p>
                                    <div x-show="party_id"
                                        class="mt-2 p-2 bg-white/50 dark:bg-black/20 rounded-lg inline-block border border-indigo-100 dark:border-indigo-800">
                                        <span class="text-[10px] uppercase font-black text-indigo-500">Previous
                                            Balance:</span>
                                        <span class="text-xs font-black ml-1"
                                            :class="selectedPartyBalance >= 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ $settings->currency_symbol }} <span
                                                x-text="Math.abs(selectedPartyBalance).toLocaleString()"></span>
                                            <span x-text="selectedPartyBalance >= 0 ? '(DR)' : '(CR)'"></span>
                                        </span>
                                    </div>
                                </div>
                                <x-primary-button x-bind:disabled="submitting"
                                    x-bind:class="submitting ? 'opacity-50 cursor-not-allowed' : ''"
                                    class="!rounded-2xl !px-12 !py-4 shadow-2xl border-0 bg-indigo-600 hover:bg-indigo-700 transform transition hover:scale-105 active:scale-95 text-sm font-bold uppercase tracking-widest">
                                    <svg x-show="!submitting" class="w-5 h-5 mr-2" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                        </path>
                                    </svg>
                                    <span x-show="!submitting">{{ __('Save Payment') }}</span>
                                    <span x-show="submitting">Processing...</span>
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function paymentForm() {
            return {
                submitting: false,
                parties: @json($parties),
                accounts: @json($accounts),
                partySearch: '',
                accountSearch: '',
                partySearchOpen: false,
                accountSearchOpen: false,
                party_id: '',
                selectedPartyBalance: 0,
                from_account_id: '',
                amount: 0,
                type: 'received',
                date: new Date().toISOString().split('T')[0],
                note: '',
                highlightedPartyIndex: -1,
                highlightedAccountIndex: -1,
                errors: {
                    party_id: false,
                    from_account_id: false,
                    amount: false
                },

                getFilteredParties() {
                    if (!this.partySearch) return this.parties.slice(0, 10);
                    const search = this.partySearch.toLowerCase();
                    return this.parties.filter(p => p.name.toLowerCase().includes(search)).slice(0, 10);
                },

                getFilteredAccounts() {
                    if (!this.accountSearch) return this.accounts.slice(0, 10);
                    const search = this.accountSearch.toLowerCase();
                    return this.accounts.filter(a => a.name.toLowerCase().includes(search)).slice(0, 10);
                },

                handlePartySearchKey(e) {
                    const items = this.getFilteredParties();
                    if (items.length === 0) return;
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.highlightedPartyIndex = (this.highlightedPartyIndex + 1) % items.length;
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.highlightedPartyIndex = (this.highlightedPartyIndex - 1 + items.length) % items.length;
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (this.highlightedPartyIndex >= 0) this.selectParty(items[this.highlightedPartyIndex]);
                    } else if (e.key === 'Escape' || e.key === 'Tab') {
                        this.partySearchOpen = false;
                    }
                },

                handleAccountSearchKey(e) {
                    const items = this.getFilteredAccounts();
                    if (items.length === 0) return;
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.highlightedAccountIndex = (this.highlightedAccountIndex + 1) % items.length;
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.highlightedAccountIndex = (this.highlightedAccountIndex - 1 + items.length) % items.length;
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (this.highlightedAccountIndex >= 0) this.selectAccount(items[this.highlightedAccountIndex]);
                    } else if (e.key === 'Escape' || e.key === 'Tab') {
                        this.accountSearchOpen = false;
                    }
                },

                selectParty(p) {
                    this.party_id = p.id;
                    this.partySearch = p.name;
                    this.selectedPartyBalance = p.balance;
                    this.partySearchOpen = false;
                    this.errors.party_id = false;
                    this.highlightedPartyIndex = -1;
                },

                selectAccount(a) {
                    this.from_account_id = a.id;
                    this.accountSearch = a.name;
                    this.accountSearchOpen = false;
                    this.errors.from_account_id = false;
                    this.highlightedAccountIndex = -1;
                },

                submitForm(e) {
                    if (this.submitting) return;
                    this.errors = {
                        party_id: !this.party_id,
                        from_account_id: !this.from_account_id,
                        amount: this.amount <= 0
                    };
                    if (Object.values(this.errors).some(v => v)) {
                        alert('Please fill all required fields correctly.');
                        return;
                    }
                    this.submitting = true;
                    e.target.submit();
                }
            }
        }
    </script>
</x-app-layout>
