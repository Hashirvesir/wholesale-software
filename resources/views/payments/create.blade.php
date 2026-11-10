<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12" x-data="paymentForm()">
        <div class="max-w-5xl mx-auto px-2 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-xl sm:rounded-2xl border border-gray-200 dark:border-gray-700">
                <div class="p-3.5 sm:p-6 md:p-8 text-gray-900 dark:text-gray-100">

                    <!-- Header -->
                    <div class="flex justify-between items-center mb-4 sm:mb-6 border-b dark:border-gray-700 pb-3 sm:pb-4">
                        <div>
                            <h3 class="text-base sm:text-xl font-black text-gray-800 dark:text-gray-100 tracking-wide uppercase flex items-center gap-2">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>{{ __('Record Payment Voucher') }}</span>
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Record incoming customer collections or outgoing supplier/expense disbursements.</p>
                        </div>
                        <a href="{{ route('payments.index') }}"
                            class="px-3.5 py-1.5 rounded-xl border border-gray-300 dark:border-gray-600 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            Back to List
                        </a>
                    </div>

                    <!-- Errors -->
                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-300 rounded-r-xl">
                            <ul class="list-disc list-inside text-xs font-bold space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-300 rounded-r-xl font-bold text-xs">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('payments.store') }}" method="POST" @submit.prevent="submitForm">
                        @csrf

                        <!-- Top Row: Payment Type Selection -->
                        <div class="mb-6 p-4 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700">
                            <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                                Transaction Direction / Type *
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="cursor-pointer relative">
                                    <input type="radio" name="payment_type" value="received" x-model="type" class="sr-only">
                                    <div class="p-3.5 text-center rounded-xl border-2 transition flex items-center justify-center gap-2"
                                        :class="type === 'received' ?
                                            'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-black shadow-sm' :
                                            'border-gray-200 dark:border-gray-700 text-gray-400 hover:border-gray-300'">
                                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                        </svg>
                                        <span class="text-xs uppercase tracking-wider">Payment Received (Customer / Inflow)</span>
                                    </div>
                                </label>

                                <label class="cursor-pointer relative">
                                    <input type="radio" name="payment_type" value="paid" x-model="type" class="sr-only">
                                    <div class="p-3.5 text-center rounded-xl border-2 transition flex items-center justify-center gap-2"
                                        :class="type === 'paid' ?
                                            'border-rose-500 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 font-black shadow-sm' :
                                            'border-gray-200 dark:border-gray-700 text-gray-400 hover:border-gray-300'">
                                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                        </svg>
                                        <span class="text-xs uppercase tracking-wider">Payment Paid (Vendor / Outflow)</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Basic Info Form Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
                            <!-- Party Search -->
                            <div class="relative md:col-span-2" @click.away="partySearchOpen = false">
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                    Party (Customer / Vendor / Cargo) *
                                </label>
                                <div class="relative">
                                    <input type="text" x-model="partySearch" @focus="partySearchOpen = true"
                                        @input="partySearchOpen = true; party_id = ''; highlightedPartyIndex = -1"
                                        @keydown="handlePartySearchKey($event)"
                                        class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm pl-9"
                                        :class="{ 'border-rose-500 ring-1 ring-rose-500': errors.party_id }"
                                        placeholder="Search customer, vendor, or cargo agent..." autocomplete="off" required>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                </div>
                                <input type="hidden" name="party_id" :value="party_id">

                                <!-- Party Dropdown Results -->
                                <div x-show="partySearchOpen && getFilteredParties().length > 0" x-cloak
                                    class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-h-56 overflow-y-auto divide-y dark:divide-gray-700 border border-gray-200 dark:border-gray-700">
                                    <template x-for="(party, index) in getFilteredParties()" :key="party.id">
                                        <div @click="selectParty(party)"
                                            class="p-2.5 cursor-pointer hover:bg-primary-50 dark:hover:bg-primary-950/40 flex justify-between items-center text-xs transition"
                                            :class="highlightedPartyIndex === index ? 'bg-primary-50 dark:bg-primary-950/40' : ''">
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold uppercase text-gray-900 dark:text-gray-100" x-text="party.name"></span>
                                                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase font-black" x-text="party.type"></span>
                                                </div>
                                                <span class="text-gray-500 text-[10px] block" x-text="party.phone || 'No Phone'"></span>
                                            </div>
                                            <span class="font-black text-xs" :class="party.balance >= 0 ? 'text-red-500' : 'text-emerald-500'">
                                                {{ $settings->currency_symbol ?? 'Rs' }} <span x-text="Math.abs(party.balance).toLocaleString()"></span>
                                                <span x-text="party.balance >= 0 ? '(Due)' : '(Adv)'" class="text-[10px]"></span>
                                            </span>
                                        </div>
                                    </template>
                                </div>

                                <template x-if="party_id">
                                    <div class="mt-1 text-[11px] font-bold text-gray-500">
                                        Current Ledger Balance:
                                        <span :class="selectedPartyBalance >= 0 ? 'text-red-500' : 'text-emerald-500'">
                                            {{ $settings->currency_symbol ?? 'Rs' }} <span x-text="Math.abs(selectedPartyBalance).toLocaleString()"></span>
                                            <span x-text="selectedPartyBalance >= 0 ? '(Receivable / Due)' : '(Advance / Payable)'"></span>
                                        </span>
                                    </div>
                                </template>
                            </div>

                            <!-- Transaction Date -->
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                    Transaction Date *
                                </label>
                                <input type="date" name="date" x-model="date" min="2020-01-01" max="{{ date('Y-m-d') }}" required
                                    class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm" />
                            </div>
                        </div>

                        <!-- Row 2: Account, Warehouse, Amount -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
                            <!-- Account Search (Cash/Bank) -->
                            <div class="relative" @click.away="accountSearchOpen = false">
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                    Account (Cash / Bank) *
                                </label>
                                <div class="relative">
                                    <input type="text" x-model="accountSearch" @focus="accountSearchOpen = true"
                                        @input="accountSearchOpen = true; from_account_id = ''; highlightedAccountIndex = -1"
                                        @keydown="handleAccountSearchKey($event)"
                                        class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm pl-9"
                                        :class="{ 'border-rose-500 ring-1 ring-rose-500': errors.from_account_id }"
                                        placeholder="Search Cash or Bank..." autocomplete="off" required>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                    </div>
                                </div>
                                <input type="hidden" name="from_account_id" :value="from_account_id">

                                <div x-show="accountSearchOpen && getFilteredAccounts().length > 0" x-cloak
                                    class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-h-52 overflow-y-auto divide-y dark:divide-gray-700 border border-gray-200 dark:border-gray-700">
                                    <template x-for="(account, index) in getFilteredAccounts()" :key="account.id">
                                        <div @click="selectAccount(account)"
                                            class="p-2.5 cursor-pointer hover:bg-primary-50 dark:hover:bg-primary-950/40 flex justify-between items-center text-xs transition"
                                            :class="highlightedAccountIndex === index ? 'bg-primary-50 dark:bg-primary-950/40' : ''">
                                            <span class="font-bold uppercase text-gray-900 dark:text-gray-100" x-text="account.name"></span>
                                            <span class="text-[10px] text-gray-400 uppercase font-black" x-text="account.type"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Shop / Warehouse -->
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                    Shop / Warehouse *
                                </label>
                                <select name="warehouse_id" id="warehouse_id" required
                                    class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm">
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ (old('warehouse_id', $settings->shop_warehouse_id ?? '') == $warehouse->id) ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Amount -->
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                    Amount ({{ $settings->currency_symbol ?? 'Rs' }}) *
                                </label>
                                <input type="number" step="0.01" min="0.01" name="amount" x-model.number="amount" required
                                    class="w-full text-xs font-black rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm font-mono"
                                    :class="{ 'border-rose-500 ring-1 ring-rose-500': errors.amount }"
                                    placeholder="0.00" />
                            </div>
                        </div>

                        <!-- Remarks / Narration -->
                        <div class="mb-6">
                            <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                Note / Narration (Check #, Slip #, Online Reference)
                            </label>
                            <input type="text" name="note" x-model="note"
                                class="w-full text-xs font-medium rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-primary-500 shadow-sm"
                                placeholder="e.g. Received via Meezan Bank Online Transfer #123456" />
                        </div>

                        <!-- Payment Summary Banner & Submit -->
                        <div class="bg-gray-50 dark:bg-gray-900/40 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 flex flex-col md:flex-row justify-between items-center gap-6">
                            <div>
                                <h4 class="text-xs font-black uppercase tracking-wider text-gray-400 mb-1">
                                    Voucher Summary
                                </h4>
                                <div class="text-3xl font-black font-mono text-primary-600 dark:text-primary-400">
                                    {{ $settings->currency_symbol ?? 'Rs' }}
                                    <span x-text="amount ? Number(amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'"></span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-show="party_id && from_account_id">
                                    Recording <span class="font-bold uppercase" :class="type === 'received' ? 'text-emerald-600' : 'text-rose-600'" x-text="type"></span>
                                    <span x-text="type === 'received' ? 'from' : 'to'"></span> <span class="font-bold text-gray-900 dark:text-gray-100" x-text="partySearch"></span>
                                    via <span class="font-bold text-gray-900 dark:text-gray-100" x-text="accountSearch"></span>
                                </p>
                            </div>

                            <div class="flex items-center gap-3">
                                <a href="{{ route('payments.index') }}"
                                    class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-bold hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                    Cancel
                                </a>
                                <button type="submit" :disabled="submitting"
                                    class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition shadow-lg flex items-center gap-2">
                                    <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    <span>Save Payment Voucher</span>
                                </button>
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

                    // Check if offline: Save payment in local offline queue
                    if (!navigator.onLine || (window.OfflineSync && !window.OfflineSync.isOnline)) {
                        const offlinePayment = {
                            party_id: this.party_id,
                            warehouse_id: document.querySelector('select[name="warehouse_id"]')?.value || null,
                            from_account_id: this.from_account_id,
                            amount: parseFloat(this.amount) || 0,
                            payment_type: this.type,
                            date: document.querySelector('input[name="date"]')?.value || new Date().toISOString().split('T')[0],
                            note: document.querySelector('textarea[name="note"]')?.value || ''
                        };

                        if (window.OfflineSync) {
                            window.OfflineSync.saveOfflinePayment(offlinePayment);
                        }

                        alert('Offline Mode: Payment saved to local queue! It will automatically sync to the server when connection is restored.');
                        window.location.href = '{{ route("payments.index") }}';
                        return;
                    }

                    this.submitting = true;
                    e.target.submit();
                }
            }
        }
    </script>
</x-app-layout>
