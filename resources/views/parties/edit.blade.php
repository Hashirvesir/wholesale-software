<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3
                        class="text-lg font-medium mb-6 text-gray-800 dark:text-gray-200 border-b dark:border-gray-700 pb-4">
                        {{ __('Edit Party') }}: {{ $party->name }}</h3>

                    <form action="{{ route('parties.update', $party) }}" method="POST">
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
                                <select id="type" name="type"
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
