<x-app-layout>
    <div class="py-8" x-data="chequeForm()">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="p-6 sm:p-8 text-gray-900 dark:text-gray-100">
                    
                    <div class="flex items-center justify-between pb-6 border-b border-gray-100 dark:border-gray-700 mb-6">
                        <div>
                            <h2 class="text-xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tight flex items-center gap-2">
                                <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <span>Register Cheque / PDC</span>
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Record post-dated or in-hand cheque for future bank clearance
                            </p>
                        </div>
                        <a href="{{ route('cheques.index') }}"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            Back to Cheques
                        </a>
                    </div>

                    @if ($errors->any())
                        <div class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 text-xs font-bold">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('cheques.store') }}" method="POST" @submit.prevent="submitForm($event)" class="space-y-6">
                        @csrf

                        <!-- Cheque Type Selector -->
                        <div>
                            <label class="block text-xs font-black uppercase text-gray-500 dark:text-gray-400 mb-2 tracking-wider">Cheque Type *</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center p-4 rounded-xl border-2 cursor-pointer transition"
                                    :class="type === 'received' ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/30 text-emerald-900 dark:text-emerald-200 font-bold' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300'">
                                    <input type="radio" name="type" value="received" x-model="type" @change="switchType('received')" class="sr-only">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold"
                                            :class="type === 'received' ? 'bg-emerald-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                                        </div>
                                        <div>
                                            <div class="font-black text-sm">Received Cheque</div>
                                            <div class="text-[11px] opacity-75 font-normal">From Customer (In-Hand custody)</div>
                                        </div>
                                    </div>
                                </label>

                                <label class="flex items-center p-4 rounded-xl border-2 cursor-pointer transition"
                                    :class="type === 'issued' ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/30 text-indigo-900 dark:text-indigo-200 font-bold' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300'">
                                    <input type="radio" name="type" value="issued" x-model="type" @change="switchType('issued')" class="sr-only">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold"
                                            :class="type === 'issued' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                                        </div>
                                        <div>
                                            <div class="font-black text-sm">Issued Cheque</div>
                                            <div class="text-[11px] opacity-75 font-normal">Given to Supplier / Vendor</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Party Autocomplete Search & Shop -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Party Search (Like Payments) -->
                            <div class="relative" @click.away="partySearchOpen = false">
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                    <span x-text="type === 'received' ? 'Customer (From) *' : 'Vendor / Supplier (To) *'"></span>
                                </label>
                                <div class="relative">
                                    <input type="text" x-model="partySearch" @focus="partySearchOpen = true"
                                        @input="partySearchOpen = true; party_id = ''; highlightedPartyIndex = -1"
                                        @keydown="handlePartySearchKey($event)"
                                        class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm pl-9"
                                        :class="{ 'border-rose-500 ring-1 ring-rose-500': errors.party_id }"
                                        :placeholder="type === 'received' ? 'Search customer by name or phone...' : 'Search vendor/supplier by name or phone...'"
                                        autocomplete="off" required>
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
                                            class="p-2.5 cursor-pointer hover:bg-emerald-50 dark:hover:bg-emerald-950/40 flex justify-between items-center text-xs transition"
                                            :class="highlightedPartyIndex === index ? 'bg-emerald-50 dark:bg-emerald-950/40' : ''">
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold uppercase text-gray-900 dark:text-gray-100" x-text="party.name"></span>
                                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase font-black" x-text="party.type"></span>
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

                                <div x-show="partySearchOpen && getFilteredParties().length === 0" x-cloak
                                    class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 rounded-xl shadow-lg p-3 text-center text-xs text-gray-500 border border-gray-200 dark:border-gray-700">
                                    No <span x-text="type === 'received' ? 'customers' : 'vendors'"></span> found matching "<span x-text="partySearch"></span>"
                                </div>

                                <template x-if="party_id">
                                    <div class="mt-1.5 text-[11px] font-bold text-gray-500 flex items-center justify-between">
                                        <span>Party: <span class="text-gray-800 dark:text-gray-200" x-text="selectedPartyName"></span></span>
                                        <span>
                                            Balance: 
                                            <span :class="selectedPartyBalance >= 0 ? 'text-red-500' : 'text-emerald-500'">
                                                {{ $settings->currency_symbol ?? 'Rs' }} <span x-text="Math.abs(selectedPartyBalance).toLocaleString()"></span>
                                                <span x-text="selectedPartyBalance >= 0 ? '(Due / Rec)' : '(Adv / Pay)'"></span>
                                            </span>
                                        </span>
                                    </div>
                                </template>
                            </div>

                            <!-- Shop / Warehouse -->
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Shop / Warehouse *</label>
                                <select name="warehouse_id" required
                                    class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                    @foreach ($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ (old('warehouse_id', $settings->shop_warehouse_id ?? '') == $wh->id) ? 'selected' : '' }}>
                                            {{ $wh->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Cheque Details -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Cheque Number *</label>
                                <input type="text" name="cheque_number" value="{{ old('cheque_number') }}" required placeholder="e.g. 7849120"
                                    class="w-full text-xs font-mono font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Drawer Bank Name</label>
                                <input type="text" name="drawer_bank" value="{{ old('drawer_bank') }}" placeholder="e.g. Meezan Bank, HBL"
                                    class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Amount (Rs.) *</label>
                                <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount') }}" required placeholder="0.00"
                                    class="w-full text-xs font-mono font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Cheque Issue / Received Date *</label>
                                <input type="date" name="issue_date" value="{{ old('issue_date', now()->toDateString()) }}" required
                                    class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Due / Maturity Date (Clearing Date) *</label>
                                <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(15)->toDateString()) }}" required
                                    class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                <p class="text-[11px] text-gray-500 mt-1">Date when this cheque can be presented to the bank.</p>
                            </div>
                        </div>

                        <!-- Optional Bank Account -->
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Target Bank Account (Optional)</label>
                            <select name="bank_account_id"
                                class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                <option value="">Select Later upon Deposit/Clearance</option>
                                @foreach ($bankAccounts as $acc)
                                    <option value="{{ $acc->id }}" {{ old('bank_account_id') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->name }} (Rs. {{ number_format($acc->getBalance(), 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Note -->
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Notes / Remarks</label>
                            <textarea name="note" rows="2" placeholder="Any comments, invoice references, or guarantees..."
                                class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">{{ old('note') }}</textarea>
                        </div>

                        <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-700">
                            <a href="{{ route('cheques.index') }}"
                                class="px-5 py-2.5 text-xs font-bold rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Cancel
                            </a>
                            <button type="submit"
                                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-md flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Save Cheque</span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function chequeForm() {
            return {
                type: '{{ old('type', $type ?? 'received') }}',
                party_id: '{{ old('party_id', '') }}',
                parties: @json($parties),
                partySearch: '',
                selectedPartyName: '',
                selectedPartyBalance: 0,
                partySearchOpen: false,
                highlightedPartyIndex: -1,
                errors: {
                    party_id: false
                },

                init() {
                    if (this.party_id) {
                        const existing = this.parties.find(p => p.id == this.party_id);
                        if (existing) {
                            this.partySearch = existing.name;
                            this.selectedPartyName = existing.name;
                            this.selectedPartyBalance = existing.balance;
                        }
                    }
                },

                switchType(newType) {
                    this.type = newType;
                    this.party_id = '';
                    this.partySearch = '';
                    this.selectedPartyName = '';
                    this.selectedPartyBalance = 0;
                    this.partySearchOpen = false;
                    this.highlightedPartyIndex = -1;
                    this.errors.party_id = false;
                },

                getFilteredParties() {
                    const isReceived = (this.type === 'received');
                    const filteredByType = this.parties.filter(p => {
                        return isReceived ? (p.type === 'customer') : (p.type !== 'customer');
                    });

                    if (!this.partySearch || !this.partySearch.trim()) {
                        return filteredByType.slice(0, 12);
                    }

                    const search = this.partySearch.toLowerCase().trim();
                    return filteredByType.filter(p => {
                        const nameMatch = p.name && p.name.toLowerCase().includes(search);
                        const phoneMatch = p.phone && p.phone.toLowerCase().includes(search);
                        return nameMatch || phoneMatch;
                    }).slice(0, 15);
                },

                selectParty(party) {
                    this.party_id = party.id;
                    this.partySearch = party.name;
                    this.selectedPartyName = party.name;
                    this.selectedPartyBalance = party.balance;
                    this.partySearchOpen = false;
                    this.errors.party_id = false;
                    this.highlightedPartyIndex = -1;
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
                        if (this.highlightedPartyIndex >= 0 && this.highlightedPartyIndex < items.length) {
                            this.selectParty(items[this.highlightedPartyIndex]);
                        } else if (items.length > 0) {
                            this.selectParty(items[0]);
                        }
                    } else if (e.key === 'Escape' || e.key === 'Tab') {
                        this.partySearchOpen = false;
                    }
                },

                submitForm(e) {
                    if (!this.party_id) {
                        this.errors.party_id = true;
                        this.partySearchOpen = true;
                        alert('Please select a valid ' + (this.type === 'received' ? 'Customer' : 'Vendor / Supplier') + ' from the list.');
                        return;
                    }
                    e.target.submit();
                }
            };
        }
    </script>
</x-app-layout>
