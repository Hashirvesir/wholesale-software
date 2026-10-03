<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter">
                        Receivable / Payable Aging Report
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium italic">
                        Outstanding invoices grouped by how overdue they are
                    </p>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700 border-l-4 border-l-red-500">
                    <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Total Receivable</span>
                    <div class="text-2xl font-black text-red-600 dark:text-red-400 mt-1">
                        {{ $settings->currency_symbol }} {{ number_format($receivables->sum('total'), 2) }}
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700 border-l-4 border-l-orange-500">
                    <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Total Payable</span>
                    <div class="text-2xl font-black text-orange-600 dark:text-orange-400 mt-1">
                        {{ $settings->currency_symbol }} {{ number_format($payables->sum('total'), 2) }}
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700 mb-6">
                <form action="{{ route('reports.aging') }}" method="GET" class="flex flex-wrap items-end gap-4">
                    <div class="w-full md:w-64">
                        <x-input-label for="as_of_date" :value="__('As Of Date')" />
                        <x-text-input id="as_of_date" name="as_of_date" type="date"
                            class="block mt-1 w-full" :value="$asOfDate" />
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
                        <a href="{{ route('reports.aging') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none transition ease-in-out duration-150">
                            {{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            @foreach ([['title' => 'Receivables (Customers)', 'rows' => $receivables, 'color' => 'red'], ['title' => 'Payables (Vendors & Cargo)', 'rows' => $payables, 'color' => 'orange']] as $section)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-2xl sm:rounded-3xl border border-gray-200 dark:border-gray-700 mb-8">
                    <div class="px-6 py-4 border-b dark:border-gray-700">
                        <h4 class="text-sm font-black uppercase tracking-widest text-gray-700 dark:text-gray-300">{{ $section['title'] }}</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                    <th class="px-6 py-4 text-left">Party</th>
                                    <th class="px-6 py-4 text-right">0-30 Days</th>
                                    <th class="px-6 py-4 text-right">31-60 Days</th>
                                    <th class="px-6 py-4 text-right">61-90 Days</th>
                                    <th class="px-6 py-4 text-right">90+ Days</th>
                                    <th class="px-6 py-4 text-right">Total Due</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                @forelse ($section['rows'] as $row)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition duration-75">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-bold text-gray-900 dark:text-white">{{ $row['party']->name ?? 'Unknown' }}</div>
                                            <div class="text-xs text-gray-500">{{ $row['party']->phone ?? '' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-gray-600 dark:text-gray-400">{{ number_format($row['bucket_0_30'], 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-gray-600 dark:text-gray-400">{{ number_format($row['bucket_31_60'], 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-orange-600 dark:text-orange-400 font-bold">{{ number_format($row['bucket_61_90'], 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-red-600 dark:text-red-400 font-black">{{ number_format($row['bucket_90_plus'], 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right font-black text-gray-900 dark:text-gray-100">{{ number_format($row['total'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 italic">
                                            No outstanding balances found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($section['rows']->isNotEmpty())
                                <tfoot>
                                    <tr class="bg-gray-100 dark:bg-gray-700 font-black text-sm">
                                        <td class="px-6 py-4 text-right uppercase tracking-widest text-[10px]">Total</td>
                                        <td class="px-6 py-4 text-right">{{ number_format($section['rows']->sum('bucket_0_30'), 2) }}</td>
                                        <td class="px-6 py-4 text-right">{{ number_format($section['rows']->sum('bucket_31_60'), 2) }}</td>
                                        <td class="px-6 py-4 text-right">{{ number_format($section['rows']->sum('bucket_61_90'), 2) }}</td>
                                        <td class="px-6 py-4 text-right">{{ number_format($section['rows']->sum('bucket_90_plus'), 2) }}</td>
                                        <td class="px-6 py-4 text-right">{{ $settings->currency_symbol }} {{ number_format($section['rows']->sum('total'), 2) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
