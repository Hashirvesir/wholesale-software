<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter">
                        Cargo Charges Report
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium italic">
                        Cargo/transport charges by party for the selected period
                    </p>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700 border-l-4 border-l-indigo-500">
                    <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Total Cargo Charges</span>
                    <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">
                        {{ $settings->currency_symbol }} {{ number_format($totalCargo, 2) }}
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700 mb-6">
                <form action="{{ route('reports.cargo-charges') }}" method="GET" class="flex flex-wrap items-end gap-4">
                    <div class="w-full md:w-48">
                        <x-input-label for="start_date" :value="__('Start Date')" />
                        <x-text-input id="start_date" name="start_date" type="date" class="block mt-1 w-full" :value="$startDate" />
                    </div>
                    <div class="w-full md:w-48">
                        <x-input-label for="end_date" :value="__('End Date')" />
                        <x-text-input id="end_date" name="end_date" type="date" class="block mt-1 w-full" :value="$endDate" />
                    </div>
                    <div class="w-full md:w-56">
                        <x-input-label for="cargo_party_id" :value="__('Cargo Party (Optional)')" />
                        <select id="cargo_party_id" name="cargo_party_id"
                            class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">All Cargo Parties</option>
                            @foreach ($cargoParties as $party)
                                <option value="{{ $party->id }}" {{ $cargoPartyId == $party->id ? 'selected' : '' }}>{{ $party->name }}</option>
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
                        <a href="{{ route('reports.cargo-charges') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none transition ease-in-out duration-150">
                            {{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Party Summary -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-2xl sm:rounded-3xl border border-gray-200 dark:border-gray-700 mb-8">
                <div class="px-6 py-4 border-b dark:border-gray-700">
                    <h4 class="text-sm font-black uppercase tracking-widest text-gray-700 dark:text-gray-300">By Cargo Party</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                <th class="px-6 py-4 text-left">Cargo Party</th>
                                <th class="px-6 py-4 text-right">Charges</th>
                                <th class="px-6 py-4 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            @forelse ($partySummary as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition duration-75">
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $row['party']->name ?? 'Unknown' }}</td>
                                    <td class="px-6 py-4 text-right text-gray-500">{{ $row['count'] }}</td>
                                    <td class="px-6 py-4 text-right font-black text-indigo-600 dark:text-indigo-400">{{ number_format($row['total'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 italic">No cargo charges found for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detail -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-2xl sm:rounded-3xl border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b dark:border-gray-700">
                    <h4 class="text-sm font-black uppercase tracking-widest text-gray-700 dark:text-gray-300">Detail</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                <th class="px-6 py-4 text-left">Date</th>
                                <th class="px-6 py-4 text-left">Cargo Party</th>
                                <th class="px-6 py-4 text-left">Purchase</th>
                                <th class="px-6 py-4 text-right">Weight</th>
                                <th class="px-6 py-4 text-right">Rate/Kg</th>
                                <th class="px-6 py-4 text-right">Amount</th>
                                <th class="px-6 py-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            @forelse ($cargoCharges as $charge)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition duration-75">
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $charge->purchase ? date('d M, Y', strtotime($charge->purchase->date)) : '-' }}</td>
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $charge->cargoParty->name ?? 'Unknown' }}</td>
                                    <td class="px-6 py-4">
                                        @if ($charge->purchase)
                                            <a href="{{ route('purchases.show', $charge->purchase_id) }}" class="text-indigo-500 hover:underline">{{ $settings->invoice_prefix }}{{ $charge->purchase_id }}</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-gray-500">{{ $charge->total_weight > 0 ? number_format($charge->total_weight, 2) . ' kg' : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-gray-500">{{ $charge->rate_per_kg > 0 ? number_format($charge->rate_per_kg, 2) : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-black text-indigo-600 dark:text-indigo-400">{{ number_format($charge->total_amount, 2) }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($charge->is_paid)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Paid</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">Unpaid</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 italic">No cargo charges found for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-100 dark:bg-gray-700 font-black text-sm">
                                <td colspan="5" class="px-6 py-4 text-right uppercase tracking-widest text-[10px]">Total</td>
                                <td class="px-6 py-4 text-right">{{ $settings->currency_symbol }} {{ number_format($totalCargo, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
