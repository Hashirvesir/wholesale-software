<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter">
                        Daily Collection / Payment Report
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium italic">
                        Cash & bank movement across all payment accounts
                    </p>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700 border-l-4 border-l-green-500">
                    <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Total Received</span>
                    <div class="text-2xl font-black text-green-600 dark:text-green-400 mt-1">
                        {{ $settings->currency_symbol }} {{ number_format($totalReceived, 2) }}
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700 border-l-4 border-l-red-500">
                    <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Total Paid</span>
                    <div class="text-2xl font-black text-red-600 dark:text-red-400 mt-1">
                        {{ $settings->currency_symbol }} {{ number_format($totalPaid, 2) }}
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700 bg-indigo-50/50">
                    <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Net Movement</span>
                    <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">
                        {{ $settings->currency_symbol }} {{ number_format($totalReceived - $totalPaid, 2) }}
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700 mb-6">
                <form action="{{ route('reports.daily-collection') }}" method="GET" class="flex flex-wrap items-end gap-4">
                    <div class="w-full md:w-48">
                        <x-input-label for="start_date" :value="__('Start Date')" />
                        <x-text-input id="start_date" name="start_date" type="date" class="block mt-1 w-full" :value="$startDate" />
                    </div>
                    <div class="w-full md:w-48">
                        <x-input-label for="end_date" :value="__('End Date')" />
                        <x-text-input id="end_date" name="end_date" type="date" class="block mt-1 w-full" :value="$endDate" />
                    </div>
                    <div class="w-full md:w-64">
                        <x-input-label for="account_id" :value="__('Account (Optional)')" />
                        <select id="account_id" name="account_id"
                            class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">All Accounts</option>
                            @foreach ($paymentAccounts as $account)
                                <option value="{{ $account->id }}" {{ $selectedAccountId == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex space-x-2">
                        <x-primary-button>{{ __('Generate Report') }}</x-primary-button>
                        <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" target="_blank"
                            class="inline-flex items-center px-4 py-2 bg-red-600 dark:bg-red-700 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-widest hover:bg-red-700 dark:hover:bg-red-600 transition shadow-lg shadow-red-200 dark:shadow-none">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Download PDF
                        </a>
                        <a href="{{ route('reports.daily-collection') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none transition ease-in-out duration-150">
                            {{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Per-Account Summary -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-2xl sm:rounded-3xl border border-gray-200 dark:border-gray-700 mb-8">
                <div class="px-6 py-4 border-b dark:border-gray-700">
                    <h4 class="text-sm font-black uppercase tracking-widest text-gray-700 dark:text-gray-300">Account Summary</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                <th class="px-6 py-4 text-left">Account</th>
                                <th class="px-6 py-4 text-right">Received</th>
                                <th class="px-6 py-4 text-right">Paid</th>
                                <th class="px-6 py-4 text-right">Net</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            @forelse ($accountSummary as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition duration-75">
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $row['account']->name ?? 'Unknown' }}</td>
                                    <td class="px-6 py-4 text-right text-green-600 dark:text-green-400">{{ number_format($row['received'], 2) }}</td>
                                    <td class="px-6 py-4 text-right text-red-600 dark:text-red-400">{{ number_format($row['paid'], 2) }}</td>
                                    <td class="px-6 py-4 text-right font-black">{{ number_format($row['received'] - $row['paid'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 italic">No transactions found for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detail -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-2xl sm:rounded-3xl border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b dark:border-gray-700">
                    <h4 class="text-sm font-black uppercase tracking-widest text-gray-700 dark:text-gray-300">Transaction Detail</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                <th class="px-6 py-4 text-left">Date</th>
                                <th class="px-6 py-4 text-left">Account</th>
                                <th class="px-6 py-4 text-left">Reference</th>
                                <th class="px-6 py-4 text-right">Received</th>
                                <th class="px-6 py-4 text-right">Paid</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            @forelse ($entries as $entry)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition duration-75">
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ date('d M, Y', strtotime($entry->date)) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900 dark:text-white">{{ $entry->account->name ?? 'Unknown' }}</td>
                                    <td class="px-6 py-4 text-xs">
                                        <div class="font-bold uppercase tracking-tight text-gray-700 dark:text-gray-300">{{ $entry->note }}</div>
                                        <div class="text-[10px] text-indigo-400 uppercase">{{ $entry->reference_type }} #{{ $entry->reference_id }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-green-600 dark:text-green-400">{{ $entry->debit > 0 ? number_format($entry->debit, 2) : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-red-600 dark:text-red-400">{{ $entry->credit > 0 ? number_format($entry->credit, 2) : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 italic">No transactions found for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-100 dark:bg-gray-700 font-black text-sm">
                                <td colspan="3" class="px-6 py-4 text-right uppercase tracking-widest text-[10px]">Total</td>
                                <td class="px-6 py-4 text-right">{{ number_format($totalReceived, 2) }}</td>
                                <td class="px-6 py-4 text-right">{{ number_format($totalPaid, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
