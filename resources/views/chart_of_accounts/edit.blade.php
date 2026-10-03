<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3
                        class="text-lg font-medium mb-6 text-gray-800 dark:text-gray-200 border-b dark:border-gray-700 pb-4">
                        {{ __('Edit Account') }}: {{ $chartOfAccount->name }}</h3>

                    <form action="{{ route('chart-of-accounts.update', $chartOfAccount) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <x-input-label for="name" :value="__('Account Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                    :value="old('name', $chartOfAccount->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <!-- Type -->
                            <div>
                                <x-input-label for="type" :value="__('Account Type')" />
                                <select id="type" name="type"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150"
                                    required>
                                    @foreach ($types as $type)
                                        <option value="{{ $type }}"
                                            {{ old('type', $chartOfAccount->type) == $type ? 'selected' : '' }}>
                                            {{ $type }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('type')" class="mt-2" />
                            </div>

                            <!-- Parent Account -->
                            <div>
                                <x-input-label for="parent_id" :value="__('Parent Account (Optional)')" />
                                <select id="parent_id" name="parent_id"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150">
                                    <option value="">-- No Parent (Root Account) --</option>
                                    @foreach ($parentAccounts as $parent)
                                        <option value="{{ $parent->id }}"
                                            {{ old('parent_id', $chartOfAccount->parent_id) == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->name }} ({{ $parent->type }})</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('parent_id')" class="mt-2" />
                            </div>

                            <!-- Opening Balance -->
                            <div>
                                <x-input-label for="opening_balance" :value="__('Opening Balance')" />
                                <x-text-input id="opening_balance" class="block mt-1 w-full" type="number"
                                    step="0.01" name="opening_balance" :value="old('opening_balance', $chartOfAccount->opening_balance)" required
                                    placeholder="0.00" />
                                <x-input-error :messages="$errors->get('opening_balance')" class="mt-2" />
                            </div>

                            <!-- Balance Type -->
                            <div>
                                <x-input-label for="balance_type" :value="__('Balance Type (Dr/Cr)')" />
                                <select id="balance_type" name="balance_type"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150"
                                    required>
                                    <option value="debit"
                                        {{ old('balance_type', $chartOfAccount->balance_type) == 'debit' ? 'selected' : '' }}>
                                        Debit (Receivable/Asset)</option>
                                    <option value="credit"
                                        {{ old('balance_type', $chartOfAccount->balance_type) == 'credit' ? 'selected' : '' }}>
                                        Credit (Payable/Liability)</option>
                                </select>
                                <x-input-error :messages="$errors->get('balance_type')" class="mt-2" />
                            </div>

                            <!-- Status -->
                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150">
                                    <option value="1"
                                        {{ old('status', $chartOfAccount->status) == '1' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="0"
                                        {{ old('status', $chartOfAccount->status) == '0' ? 'selected' : '' }}>Inactive
                                    </option>
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 border-t dark:border-gray-700 pt-6">
                            <a href="{{ route('chart-of-accounts.index') }}"
                                class="mr-4 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 italic transition duration-150">Cancel</a>
                            <x-primary-button>
                                {{ __('Update Account') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
