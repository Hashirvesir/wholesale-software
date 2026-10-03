<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between mb-6">
                        <h2
                            class="text-2xl font-black text-gray-800 dark:text-gray-200 italic tracking-tighter uppercase">
                            Payments</h2>
                        <a href="{{ route('payments.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-lg shadow-indigo-200 dark:shadow-none">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            Record Payment
                        </a>
                    </div>

                    @if (session('success'))
                        <div
                            class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-r-xl">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Search & Filter Bar -->
                    <div
                        class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 mb-6">
                        <form action="{{ route('payments.index') }}" method="GET"
                            class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <!-- Search -->
                            <div class="md:col-span-2">
                                <label for="search"
                                    class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">Party
                                    Name</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                    placeholder="Search..."
                                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100 italic">
                            </div>

                            <!-- Payment Type -->
                            <div>
                                <label for="payment_type"
                                    class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">Type</label>
                                <select name="payment_type" id="payment_type"
                                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100">
                                    <option value="">All</option>
                                    <option value="received"
                                        {{ request('payment_type') == 'received' ? 'selected' : '' }}>Received</option>
                                    <option value="paid" {{ request('payment_type') == 'paid' ? 'selected' : '' }}>
                                        Paid</option>
                                </select>
                            </div>

                            <!-- Start Date -->
                            <div>
                                <label for="start_date"
                                    class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">Start
                                    Date</label>
                                <input type="date" name="start_date" id="start_date"
                                    value="{{ request('start_date') }}"
                                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100 italic">
                            </div>

                            <!-- Actions -->
                            <div class="flex items-end gap-2">
                                <button type="submit"
                                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-xl text-xs uppercase tracking-widest transition shadow-lg">
                                    Filter
                                </button>
                                <a href="{{ route('payments.index') }}"
                                    class="flex-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold py-2 px-4 rounded-xl text-xs text-center uppercase tracking-widest transition">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 font-black tracking-widest">
                                <tr>
                                    <th class="px-6 py-4">Voucher #</th>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4">Type</th>
                                    <th class="px-6 py-4">Party</th>
                                    <th class="px-6 py-4">Account</th>
                                    <th class="px-6 py-4 text-right">Amount</th>
                                    <th class="px-6 py-4">Note</th>
                                    <th class="px-6 py-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($payments as $payment)
                                    <tr
                                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                        <td class="px-6 py-4 font-black text-gray-400 text-xs">#{{ $payment->id }}</td>
                                        <td class="px-6 py-4 font-bold italic">{{ $payment->date }}</td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-tighter {{ $payment->payment_type === 'received' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                                                {{ $payment->payment_type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-bold">
                                            <a href="{{ route('payments.show', $payment) }}"
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition hover:underline">
                                                {{ $payment->party->name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 italic text-gray-500 dark:text-gray-400">
                                            {{ $payment->account->name }}</td>
                                        <td
                                            class="px-6 py-4 text-right font-black text-indigo-600 dark:text-indigo-400 font-mono">
                                            Rs. {{ number_format($payment->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 text-xs">{{ $payment->note }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <x-dropdown align="right" width="48">
                                                <x-slot name="trigger">
                                                    <button
                                                        class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition duration-150 ml-auto">
                                                        <svg class="h-5 w-5 fill-current" viewBox="0 0 20 20">
                                                            <path
                                                                d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                                        </svg>
                                                    </button>
                                                </x-slot>
                                                <x-slot name="content">
                                                    <x-dropdown-link :href="route('payments.show', $payment)" class="flex items-center">
                                                        <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        {{ __('View Payment') }}
                                                    </x-dropdown-link>
                                                    <x-dropdown-link :href="route('payments.pdf', $payment)" class="flex items-center">
                                                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                        </svg>
                                                        {{ __('Download PDF') }}
                                                    </x-dropdown-link>
                                                    <x-dropdown-link :href="route('payments.thermal', $payment)" target="_blank"
                                                        class="flex items-center">
                                                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                        </svg>
                                                        {{ __('Thermal Receipt') }}
                                                    </x-dropdown-link>
                                                    <div class="border-t border-gray-100 dark:border-gray-700"></div>
                                                    <form action="{{ route('payments.destroy', $payment) }}"
                                                        method="POST" id="delete-payment-{{ $payment->id }}">
                                                        @csrf @method('DELETE')
                                                        <x-dropdown-link :href="route('payments.destroy', $payment)"
                                                            onclick="event.preventDefault(); if(confirm('Delete this payment?')) { document.getElementById('delete-payment-{{ $payment->id }}').submit(); }"
                                                            class="text-red-600 flex items-center">
                                                            <svg class="w-4 h-4 mr-2" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                            {{ __('Delete') }}
                                                        </x-dropdown-link>
                                                    </form>
                                                </x-slot>
                                            </x-dropdown>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7"
                                            class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 italic"> No
                                            payments recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $payments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
