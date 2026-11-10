<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3
                        class="text-lg font-medium mb-6 text-gray-800 dark:text-gray-200 border-b dark:border-gray-700 pb-4">
                        {{ __('Edit Party') }}: {{ $party->name }}</h3>

                    <form action="{{ route('parties.update', $party) }}" method="POST" x-data="{ partyType: '{{ old('type', $party->type) }}' }">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <x-input-label for="name" :value="__('Party Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                    :value="old('name', $party->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <!-- Type -->
                            <div>
                                <x-input-label for="type" :value="__('Party Type')" />
                                <select id="type" name="type" x-model="partyType"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150"
                                    required>
                                    <option value="customer"
                                        {{ old('type', $party->type) == 'customer' ? 'selected' : '' }}>Customer
                                    </option>
                                    <option value="vendor"
                                        {{ old('type', $party->type) == 'vendor' ? 'selected' : '' }}>Vendor</option>
                                    <option value="cargo" {{ old('type', $party->type) == 'cargo' ? 'selected' : '' }}>
                                        Cargo</option>
                                </select>
                                <x-input-error :messages="$errors->get('type')" class="mt-2" />
                            </div>

                            <!-- Phone -->
                            <div>
                                <x-input-label for="phone" :value="__('Phone Number')" />
                                <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone"
                                    :value="old('phone', $party->phone)" />
                                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                            </div>

                            <!-- Opening Balance -->
                            <div>
                                <x-input-label for="opening_balance" :value="__('Opening Balance (Rs.)')" />
                                <div class="flex mt-1">
                                    <x-text-input id="opening_balance" class="block w-full" type="number"
                                        step="0.01" name="opening_balance" :value="old('opening_balance', $party->opening_balance)" required />
                                    <select name="balance_type"
                                        class="ml-2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150">
                                        <option value="debit"
                                            {{ old('balance_type', $party->balance_type) == 'debit' ? 'selected' : '' }}>
                                            Debit</option>
                                        <option value="credit"
                                            {{ old('balance_type', $party->balance_type) == 'credit' ? 'selected' : '' }}>
                                            Credit</option>
                                    </select>
                                </div>
                                <x-input-error :messages="$errors->get('opening_balance')" class="mt-2" />
                            </div>

                            <!-- Customer Credit Settings -->
                            <div x-show="partyType === 'customer'" x-transition class="md:col-span-2 p-4 rounded-xl bg-blue-50/70 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-900/50">
                                <div class="flex items-center gap-2 mb-3">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    <h4 class="text-sm font-bold text-blue-900 dark:text-blue-200 uppercase tracking-wider">Credit Terms & Overdue Control</h4>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <x-input-label for="price_tier" :value="__('Default Pricing Tier')" />
                                        <select id="price_tier" name="price_tier"
                                            class="block mt-1 w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150">
                                            <option value="wholesale" {{ old('price_tier', $party->price_tier ?? 'wholesale') == 'wholesale' ? 'selected' : '' }}>Wholesale Rate (Normal)</option>
                                            <option value="retail" {{ old('price_tier', $party->price_tier ?? 'wholesale') == 'retail' ? 'selected' : '' }}>Retail Rate (Aam Grahak)</option>
                                            <option value="dealer" {{ old('price_tier', $party->price_tier ?? 'wholesale') == 'dealer' ? 'selected' : '' }}>Dealer / Bulk Rate</option>
                                        </select>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Rates will automatically apply when billing this customer.</p>
                                        <x-input-error :messages="$errors->get('price_tier')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="credit_limit" :value="__('Credit Limit (Rs.)')" />
                                        <x-text-input id="credit_limit" class="block mt-1 w-full bg-white dark:bg-gray-900" type="number"
                                            step="0.01" min="0" name="credit_limit" :value="old('credit_limit', $party->credit_limit ?? '0.00')" placeholder="0.00 (0 = No limit)" />
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Maximum allowed outstanding debt before alerts are triggered.</p>
                                        <x-input-error :messages="$errors->get('credit_limit')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="credit_days" :value="__('Payment Due Period (Days)')" />
                                        <x-text-input id="credit_days" class="block mt-1 w-full bg-white dark:bg-gray-900" type="number"
                                            step="1" min="0" name="credit_days" :value="old('credit_days', $party->credit_days ?? '0')" placeholder="0 (0 = Immediate / No due days)" />
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Invoices remaining unpaid beyond this number of days will be marked overdue.</p>
                                        <x-input-error :messages="$errors->get('credit_days')" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="md:col-span-2">
                                <x-input-label for="address" :value="__('Address')" />
                                <textarea id="address" name="address" rows="3"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150">{{ old('address', $party->address) }}</textarea>
                                <x-input-error :messages="$errors->get('address')" class="mt-2" />
                            </div>

                            <!-- Status -->
                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150">
                                    <option value="1" {{ old('status', $party->status) == '1' ? 'selected' : '' }}>
                                        Active</option>
                                    <option value="0"
                                        {{ old('status', $party->status) == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 border-t dark:border-gray-700 pt-6">
                            <a href="{{ route('parties.index') }}"
                                class="mr-4 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 italic transition duration-150">Cancel</a>
                            <x-primary-button>
                                {{ __('Update Party') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
