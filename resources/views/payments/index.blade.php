<x-app-layout>
    <div class="py-12">
        <div class="max-w-[1750px] mx-auto px-4 sm:px-6 lg:px-8">
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
                            class="grid grid-cols-1 md:grid-cols-6 gap-4">
                            <!-- Search -->
                            <div class="md:col-span-2">
                                <label for="search"
                                    class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">Party
                                    Name</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                    placeholder="Search..."
                                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100 italic">
                            </div>

                            <!-- Shop / Warehouse -->
                            <div>
                                <label for="warehouse_id"
                                    class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">Shop / Warehouse</label>
                                <select name="warehouse_id" id="warehouse_id"
                                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100">
                                    <option value="">All Shops</option>
                                    @foreach ($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                            {{ $wh->name }}
                                        </option>
                                    @endforeach
                                </select>
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

                    <!-- Offline Pending Payments Banner -->
                    <div x-data="{
                        pendingPayments: [],
                        init() {
                            const update = () => {
                                const q = localStorage.getItem('invenco_offline_payments_queue');
                                this.pendingPayments = q ? JSON.parse(q) : [];
                            };
                            update();
                            window.addEventListener('offline-status-changed', update);
                            window.addEventListener('offline-sync-completed', update);
                        }
                    }" x-show="pendingPayments.length > 0" x-cloak
                        class="mb-6 p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-200 shadow-sm">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="w-3 h-3 rounded-full bg-amber-500 animate-ping"></span>
                                <div>
                                    <div class="text-xs font-black uppercase tracking-wider">
                                        <span x-text="pendingPayments.length"></span> Offline Payment(s) In Local Queue
                                    </div>
                                    <div class="text-[11px] text-amber-700 dark:text-amber-400">
                                        Recorded offline. Will automatically upload once online connection is detected.
                                    </div>
                                </div>
                            </div>
                            <button type="button" @click="window.OfflineSync && window.OfflineSync.syncAll()"
                                class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition shadow-sm">
                                Sync Now
                            </button>
                        </div>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 font-black tracking-widest">
                                <tr>
                                    <th class="px-6 py-4">Voucher #</th>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4">Type</th>
                                    <th class="px-6 py-4">Party</th>
                                    <th class="px-6 py-4">Shop / Branch</th>
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
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                <span>{{ $payment->warehouse->name ?? 'Default (Shop)' }}</span>
                                            </span>
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
                                                        @php
                                                            $idxPaySignedUrl = \Illuminate\Support\Facades\URL::signedRoute('payments.public-pdf', ['payment' => $payment->id]);
                                                            $idxPayText = "Payment Voucher #{$payment->id}\nParty: {$payment->party->name}\nType: " . ($payment->payment_type === 'received' ? 'Received' : 'Paid') . "\nAmount: Rs " . number_format($payment->amount, 2) . "\nVoucher PDF: {$idxPaySignedUrl}";
                                                        @endphp
                                                        <button type="button"
                                                            onclick="window.shareDoc({
                                                                title: 'Payment Voucher #{{ $payment->id }} - {{ addslashes($payment->party->name) }}',
                                                                text: '{{ addslashes(str_replace("\n", '\n', $idxPayText)) }}',
                                                                url: '{{ $idxPaySignedUrl }}'
                                                            })"
                                                            class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center cursor-pointer">
                                                            <svg class="w-4 h-4 mr-2 text-[#252ebf]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                                            </svg>
                                                            {{ __('Share Voucher') }}
                                                        </button>
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
                                        <td colspan="9"
                                            class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 italic"> No
                                            payments recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Payment Cards View -->
                    <div class="block md:hidden space-y-3">
                        @forelse ($payments as $payment)
                            <div class="bg-gray-50 dark:bg-gray-900/60 p-4 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-sm space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-mono font-black text-gray-400">#{{ $payment->id }}</span>
                                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $payment->date }}</span>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $payment->payment_type === 'received' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                        {{ $payment->payment_type }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            {{ $payment->party->name }}
                                        </h4>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Via: {{ $payment->account->name }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-base font-black font-mono {{ $payment->payment_type === 'received' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            Rs. {{ number_format($payment->amount, 2) }}
                                        </span>
                                    </div>
                                </div>

                                @if($payment->note)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 italic bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-100 dark:border-gray-700/60">
                                        {{ $payment->note }}
                                    </p>
                                @endif

                                <div class="flex items-center gap-2 pt-1 border-t border-gray-200/60 dark:border-gray-700/60">
                                    <a href="{{ route('payments.show', $payment) }}" 
                                       class="flex-1 py-2 px-3 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 font-bold text-xs rounded-xl text-center transition">
                                        View
                                    </a>
                                    <a href="{{ route('payments.thermal', $payment) }}" target="_blank"
                                       class="py-2 px-3 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-bold text-xs rounded-xl transition">
                                        Thermal
                                    </a>
                                    <a href="{{ route('payments.pdf', $payment) }}" target="_blank"
                                       class="py-2 px-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-xl transition">
                                        PDF
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-gray-500 dark:text-gray-400 italic bg-gray-50 dark:bg-gray-900/40 rounded-xl">
                                No payments recorded yet.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        {{ $payments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
