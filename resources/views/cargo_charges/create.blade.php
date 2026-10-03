<x-app-layout>
    <div class="py-12" x-data="cargoForm()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between mb-8 border-b dark:border-gray-700 pb-4">
                        <h3
                            class="text-2xl font-black text-gray-800 dark:text-gray-200 italic tracking-tighter uppercase">
                            Record Cargo Charges</h3>
                        <a href="{{ route('cargo-charges.index') }}"
                            class="text-sm font-bold text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 transition uppercase tracking-widest italic">Back
                            to List</a>
                    </div>

                    @if ($errors->any())
                        <div
                            class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded-r-xl">
                            <div class="flex items-center mb-2">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <span class="font-bold uppercase tracking-widest text-xs">Validation Errors</span>
                            </div>
                            <ul class="list-disc list-inside text-xs font-medium space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('cargo-charges.store') }}" method="POST" @submit.prevent="submitForm">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                            <!-- Purchase Search -->
                            <div class="relative" @click.away="purchaseSearchOpen = false">
                                <x-input-label for="purchase_search" :value="__('Search Purchase Invoice')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <div class="relative">
                                    <input type="text" x-model="purchaseSearch" @focus="purchaseSearchOpen = true"
                                        @input="purchaseSearchOpen = true; purchase_id = ''; highlightedPurchaseIndex = -1"
                                        @keydown="handlePurchaseSearchKey($event)"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-xl shadow-sm transition pl-10 text-xs"
                                        :class="{ 'border-red-500 ring-1 ring-red-500': errors.purchase_id }"
                                        placeholder="Search by ID or Vendor..." autocomplete="off">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                </div>

                                <input type="hidden" name="purchase_id" :value="purchase_id">

                                <div x-show="purchaseSearchOpen && getFilteredPurchases().length > 0"
                                    class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-40 rounded-md py-1 text-xs ring-1 ring-black ring-opacity-5 overflow-auto border dark:border-gray-700 transition-all">
                                    <template x-for="(p, index) in getFilteredPurchases()" :key="p.id">
                                        <div @click="selectPurchase(p)"
                                            class="cursor-pointer select-none relative py-2 pl-3 pr-9 border-b last:border-0 dark:border-gray-700 hover:bg-primary-50 dark:hover:bg-primary-900 transition flex justify-between items-center group"
                                            :class="highlightedPurchaseIndex === index ? 'bg-primary-100 dark:bg-primary-900' :
                                                ''">
                                            <div class="flex items-center">
                                                <span
                                                    class="block truncate uppercase font-bold text-gray-900 dark:text-gray-100"
                                                    x-text="p.search"></span>
                                                <span class="ml-2 truncate text-gray-500 dark:text-gray-400 text-[10px]"
                                                    x-text="'[' + p.date + ']'"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Cargo Party Search -->
                            <div class="relative" @click.away="partySearchOpen = false">
                                <x-input-label for="party_search" :value="__('Search Cargo Party')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <div class="relative">
                                    <input type="text" x-model="cargoPartySearch" @focus="partySearchOpen = true"
                                        @input="partySearchOpen = true; cargo_party_id = ''; highlightedPartyIndex = -1"
                                        @keydown="handlePartySearchKey($event)"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-xl shadow-sm transition pl-10 text-xs"
                                        :class="{ 'border-red-500 ring-1 ring-red-500': errors.cargo_party_id }"
                                        placeholder="Search by Name or Phone..." autocomplete="off">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                </div>

                                <input type="hidden" name="cargo_party_id" :value="cargo_party_id">

                                <div x-show="partySearchOpen && getFilteredParties().length > 0"
                                    class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-40 rounded-md py-1 text-xs ring-1 ring-black ring-opacity-5 overflow-auto border dark:border-gray-700 transition-all">
                                    <template x-for="(party, index) in getFilteredParties()" :key="party.id">
                                        <div @click="selectParty(party)"
                                            class="cursor-pointer select-none relative py-2 pl-3 pr-9 border-b last:border-0 dark:border-gray-700 hover:bg-primary-50 dark:hover:bg-primary-900 transition flex justify-between items-center group"
                                            :class="highlightedPartyIndex === index ? 'bg-primary-100 dark:bg-primary-900' : ''">
                                            <div class="flex items-center">
                                                <span
                                                    class="block truncate uppercase font-bold text-gray-900 dark:text-gray-100"
                                                    x-text="party.name"></span>
                                                <span class="ml-2 truncate text-gray-500 dark:text-gray-400 text-[10px]"
                                                    x-text="party.phone ? '[' + party.phone + ']' : ''"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Weight -->
                            <div>
                                <x-input-label for="total_weight" :value="__('Total Weight (kg)')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <x-text-input id="total_weight" x-model.number="weight"
                                    class="block mt-1 w-full !rounded-xl text-xs" type="number" step="0.01"
                                    name="total_weight" required ::class="{ 'border-red-500 ring-1 ring-red-500': errors.weight }" />
                            </div>

                            <!-- Rate -->
                            <div>
                                <x-input-label for="rate_per_kg" :value="__('Rate per kg (PKR)')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <x-text-input id="rate_per_kg" x-model.number="rate"
                                    class="block mt-1 w-full !rounded-xl text-xs" type="number" step="0.01"
                                    name="rate_per_kg" required ::class="{ 'border-red-500 ring-1 ring-red-500': errors.rate }" />
                            </div>
                        </div>

                        <!-- Result Section -->
                        <div
                            class="bg-indigo-50 dark:bg-indigo-900/20 p-8 rounded-3xl border border-indigo-100 dark:border-indigo-800 mb-8 relative overflow-hidden">
                            <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
                                <div>
                                    <h4
                                        class="text-xs font-black text-indigo-400 uppercase tracking-[0.2em] mb-1 italic">
                                        Calculated Total</h4>
                                    <div class="flex items-baseline space-x-2">
                                        <span
                                            class="text-4xl font-black text-indigo-600 dark:text-indigo-400 font-mono">Rs.
                                            <span
                                                x-text="total_amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></span>
                                    </div>
                                    <p class="text-[10px] text-indigo-400 font-bold mt-2 uppercase tracking-widest italic"
                                        x-show="weight > 0 && rate > 0">
                                        Calculation: <span x-text="weight"></span> kg x <span x-text="rate"></span>
                                        PKR
                                    </p>
                                </div>
                                <input type="hidden" name="total_amount" :value="total_amount">
                                <x-primary-button
                                    class="!rounded-2xl !px-12 !py-4 shadow-2xl border-0 bg-indigo-600 hover:bg-indigo-700 transform transition hover:scale-105 active:scale-95 text-sm font-bold uppercase tracking-widest">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                                        </path>
                                    </svg>
                                    {{ __('Save Cargo Charges') }}
                                </x-primary-button>
                            </div>
                            <!-- Background Decor -->
                            <div class="absolute -right-10 -bottom-10 opacity-10 text-indigo-500">
                                <svg class="w-48 h-48" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z">
                                    </path>
                                    <path
                                        d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10V8a1 1 0 011-1h5a1 1 0 011 1v1h2l1 4v3.1a1.5 1.5 0 01-1.5 1.5H16.4a2.5 2.5 0 01-4.9 0H3z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function cargoForm() {
            return {
                purchases: @json($purchases),
                cargoParties: @json($cargoParties),
                purchaseSearch: '',
                cargoPartySearch: '',
                purchaseSearchOpen: false,
                partySearchOpen: false,
                purchase_id: '{{ $selectedPurchaseId ?? '' }}',
                cargo_party_id: '',
                weight: 0,
                rate: 0,
                highlightedPurchaseIndex: -1,
                highlightedPartyIndex: -1,
                errors: {
                    purchase_id: false,
                    cargo_party_id: false,
                    weight: false,
                    rate: false
                },

                init() {
                    if (this.purchase_id) {
                        const p = this.purchases.find(x => x.id == this.purchase_id);
                        if (p) this.purchaseSearch = p.search;
                    }
                },

                getFilteredPurchases() {
                    if (!this.purchaseSearch) return this.purchases.slice(0, 10);
                    const search = this.purchaseSearch.toLowerCase();
                    return this.purchases.filter(p =>
                        p.search.toLowerCase().includes(search) ||
                        p.party_name.toLowerCase().includes(search)
                    ).slice(0, 10);
                },

                getFilteredParties() {
                    if (!this.cargoPartySearch) return this.cargoParties.slice(0, 10);
                    const search = this.cargoPartySearch.toLowerCase();
                    return this.cargoParties.filter(p =>
                        p.name.toLowerCase().includes(search) ||
                        (p.phone && p.phone.toLowerCase().includes(search))
                    ).slice(0, 10);
                },

                handlePurchaseSearchKey(e) {
                    const items = this.getFilteredPurchases();
                    if (items.length === 0) return;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.highlightedPurchaseIndex = (this.highlightedPurchaseIndex + 1) % items.length;
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.highlightedPurchaseIndex = (this.highlightedPurchaseIndex - 1 + items.length) % items.length;
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (this.highlightedPurchaseIndex >= 0 && this.highlightedPurchaseIndex < items.length) {
                            this.selectPurchase(items[this.highlightedPurchaseIndex]);
                        }
                    } else if (e.key === 'Escape' || e.key === 'Tab') {
                        this.purchaseSearchOpen = false;
                    }
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
                        }
                    } else if (e.key === 'Escape' || e.key === 'Tab') {
                        this.partySearchOpen = false;
                    }
                },

                selectPurchase(p) {
                    this.purchase_id = p.id;
                    this.purchaseSearch = p.search;
                    this.purchaseSearchOpen = false;
                    this.errors.purchase_id = false;
                    this.highlightedPurchaseIndex = -1;
                },

                selectParty(p) {
                    this.cargo_party_id = p.id;
                    this.cargoPartySearch = p.name;
                    this.partySearchOpen = false;
                    this.errors.cargo_party_id = false;
                    this.highlightedPartyIndex = -1;
                },

                get total_amount() {
                    return (parseFloat(this.weight) || 0) * (parseFloat(this.rate) || 0);
                },

                submitForm(e) {
                    // Reset errors
                    this.errors = {
                        purchase_id: !this.purchase_id,
                        cargo_party_id: !this.cargo_party_id,
                        weight: this.weight <= 0,
                        rate: this.rate <= 0
                    };

                    if (Object.values(this.errors).some(v => v)) {
                        const firstError = Object.keys(this.errors).find(k => this.errors[k]);
                        alert('Please fill all required fields correctly: ' + firstError.replace('_', ' '));
                        return;
                    }

                    e.target.submit();
                }
            }
        }
    </script>
</x-app-layout>
