<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between mb-8 border-b dark:border-gray-700 pb-4">
                        <h3
                            class="text-2xl font-black text-gray-800 dark:text-gray-200 italic tracking-tighter uppercase">
                            Transfer Money</h3>
                        <a href="{{ route('account-transfers.index') }}"
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

                    <form action="{{ route('account-transfers.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                            <!-- Date -->
                            <div class="md:col-span-2">
                                <x-input-label for="date" :value="__('Transaction Date')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <x-text-input id="date" class="block mt-1 w-full !rounded-xl text-xs"
                                    type="date" name="date" :value="old('date', date('Y-m-d'))" required />
                            </div>

                            <!-- Warehouse -->
                            <div class="md:col-span-2">
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

                            <!-- From Account -->
                            <div>
                                <x-input-label for="from_account_id" :value="__('From Account (Source)')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <select name="from_account_id" id="from_account_id"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-xl shadow-sm transition text-xs uppercase font-bold">
                                    <option value="">Select Source Account</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}"
                                            {{ old('from_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- To Account -->
                            <div>
                                <x-input-label for="to_account_id" :value="__('To Account (Destination)')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <select name="to_account_id" id="to_account_id"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-xl shadow-sm transition text-xs uppercase font-bold">
                                    <option value="">Select Destination Account</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}"
                                            {{ old('to_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Amount -->
                            <div>
                                <x-input-label for="amount" :value="__('Amount to Transfer')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <x-text-input id="amount"
                                    class="block mt-1 w-full !rounded-xl text-xs font-mono font-bold" type="number"
                                    step="0.01" name="amount" :value="old('amount')" required placeholder="0.00" />
                            </div>

                            <!-- Note -->
                            <div>
                                <x-input-label for="note" :value="__('Transfer Note')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <x-text-input id="note" class="block mt-1 w-full !rounded-xl text-xs"
                                    type="text" name="note" :value="old('note')"
                                    placeholder="e.g. Daily Cash Deposit" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button
                                class="!rounded-2xl !px-12 !py-4 shadow-2xl border-0 bg-indigo-600 hover:bg-indigo-700 transform transition hover:scale-105 active:scale-95 text-sm font-bold uppercase tracking-widest">
                                {{ __('Confirm Transfer') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
