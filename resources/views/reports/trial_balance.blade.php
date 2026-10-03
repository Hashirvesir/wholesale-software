<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-3xl border border-gray-100 dark:border-gray-700 mb-8 mt-10">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-black text-gray-800 dark:text-white tracking-tighter uppercase italic">
                            Trial Balance</h2>

                        <form action="{{ route('reports.trial-balance') }}" method="GET" class="flex gap-4 items-end">
                            <div>
                                <x-input-label for="date" :value="__('As of Date')"
                                    class="text-xs font-black uppercase tracking-widest mb-1" />
                                <x-text-input type="date" name="date" id="date"
                                    class="block mt-1 w-full !rounded-xl text-xs" :value="$date" />
                            </div>
                            <div class="flex space-x-2">
                                <button class="!rounded-xl !py-3 bg-indigo-600 hover:bg-indigo-700">
                                    {{ __('Refresh') }}
                                </button>
                                <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" target="_blank"
                                    class="inline-flex items-center px-4 py-3 bg-red-600 dark:bg-red-700 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-widest hover:bg-red-700 dark:hover:bg-red-600 transition shadow-lg shadow-red-200 dark:shadow-none">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                        </path>
                                    </svg>
                                    PDF
                                </a>
                                <a href="{{ route('reports.trial-balance') }}"
                                    class="inline-flex items-center px-4 py-3 bg-gray-100 dark:bg-gray-700 border border-transparent rounded-xl font-black text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                        </path>
                                    </svg>
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead>
                                <tr
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700/50 dark:text-gray-400 font-black tracking-widest">
                                    <th class="px-6 py-4 border-b dark:border-gray-600">Account Name</th>
                                    <th class="px-6 py-4 border-b dark:border-gray-600 text-right">Debit
                                        ({{ $settings->currency_symbol }})</th>
                                    <th class="px-6 py-4 border-b dark:border-gray-600 text-right">Credit
                                        ({{ $settings->currency_symbol }})</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($reportData as $row)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                        <td
                                            class="px-6 py-4 font-bold text-gray-800 dark:text-gray-200 uppercase tracking-tight">
                                            {{ $row['account_name'] }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-right font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                            {{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-mono font-bold text-orange-600">
                                            {{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-10 text-center text-gray-400 italic">No
                                            transactions found for this period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50 dark:bg-gray-700/50 font-black">
                                    <td class="px-6 py-6 uppercase tracking-widest text-xs">Total Balance</td>
                                    <td
                                        class="px-6 py-6 text-right font-mono text-lg text-indigo-600 border-t-2 border-gray-200 dark:border-gray-600">
                                        {{ number_format($totalDebit, 2) }}
                                    </td>
                                    <td
                                        class="px-6 py-6 text-right font-mono text-lg text-orange-600 border-t-2 border-gray-200 dark:border-gray-600">
                                        {{ number_format($totalCredit, 2) }}
                                    </td>
                                </tr>
                                @if (number_format($totalDebit, 2) !== number_format($totalCredit, 2))
                                    <tr class="bg-red-50 dark:bg-red-900/20">
                                        <td colspan="3"
                                            class="px-6 py-2 text-center text-red-600 dark:text-red-400 text-xs font-bold uppercase tracking-widest">
                                            Warning: Trial Balance is not equal! Difference:
                                            {{ number_format(abs($totalDebit - $totalCredit), 2) }}
                                        </td>
                                    </tr>
                                @else
                                    <tr class="bg-green-50 dark:bg-green-900/20">
                                        <td colspan="3"
                                            class="px-6 py-2 text-center text-green-600 dark:text-green-400 text-xs font-bold uppercase tracking-widest">
                                            Trial Balance is Balanced
                                        </td>
                                    </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button onclick="window.print()"
                            class="text-xs font-black text-gray-500 hover:text-indigo-600 transition uppercase tracking-widest flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                </path>
                            </svg>
                            Print Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
