<x-app-layout>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-medium">Edit Expense #{{ $expense->id }}</h3>
                        <a href="{{ route('expenses.index') }}"
                            class="text-sm font-bold text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 transition uppercase tracking-widest italic">Back
                            to List</a>
                    </div>

                    @if ($errors->any())
                        <div
                            class="mb-4 p-4 bg-red-100 dark:bg-red-900 border-l-4 border-red-500 text-red-700 dark:text-red-300">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('error'))
                        <div
                            class="mb-4 p-4 bg-red-100 dark:bg-red-900 border-l-4 border-red-500 text-red-700 dark:text-red-300 font-bold">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('expenses.update', $expense) }}" method="POST" x-data="{ submitting: false }"
                        @submit.prevent="if(submitting) return; submitting = true; $el.submit();">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="expense_category_id" :value="__('Expense Category')" />
                            <select name="expense_category_id" id="expense_category_id"
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('expense_category_id', $expense->expense_category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="mt-2 text-sm">
                                <a href="{{ route('expense-categories.index') }}"
                                    class="text-indigo-600 hover:text-indigo-900">
                                    + Manage Categories
                                </a>
                            </div>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="warehouse_id" :value="__('Warehouse (Optional)')" />
                            <select name="warehouse_id" id="warehouse_id"
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Select Warehouse</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}"
                                        {{ old('warehouse_id', $expense->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="amount" :value="__('Amount')" />
                            <x-text-input id="amount" class="block mt-1 w-full" type="number" step="0.01"
                                name="amount" :value="old('amount', $expense->amount)" required />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="paid_from_account_id" :value="__('Paid From (Account)')" />
                            <select name="paid_from_account_id" id="paid_from_account_id"
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                required>
                                <option value="">Select Account</option>
                                @foreach ($paymentAccounts as $account)
                                    <option value="{{ $account->id }}"
                                        {{ old('paid_from_account_id', $expense->paid_from_account_id) == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }} ({{ $account->type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="date" :value="__('Date')" />
                            <x-text-input id="date" class="block mt-1 w-full" type="date" name="date"
                                :value="old('date', $expense->date)" min="2020-01-01" max="{{ date('Y-m-d') }}" required />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="note" :value="__('Note')" />
                            <textarea id="note" name="note"
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                rows="3">{{ old('note', $expense->note) }}</textarea>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ml-4" x-bind:disabled="submitting"
                                x-bind:class="submitting ? 'opacity-50 cursor-not-allowed' : ''">
                                <span x-show="!submitting">{{ __('Update Expense') }}</span>
                                <span x-show="submitting">Processing...</span>
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
