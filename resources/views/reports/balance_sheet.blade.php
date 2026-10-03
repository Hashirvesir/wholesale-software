<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-3xl border border-gray-100 dark:border-gray-700 mb-8 mt-10">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-12 border-b dark:border-gray-700 pb-6">
                        <div>
                            <h2
                                class="text-2xl font-black text-gray-800 dark:text-white tracking-tighter uppercase italic">
                                Balance Sheet</h2>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Status as of
                                {{ $date }}</p>
                        </div>

                        <form action="{{ route('reports.balance-sheet') }}" method="GET" class="flex gap-4 items-end">
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
                                <a href="{{ route('reports.balance-sheet') }}"
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-16">
                        <!-- Assets Section -->
                        <div>
                            <div class="flex justify-between items-center mb-8">
                                <h3
                                    class="text-xl font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-tighter italic">
                                    Assets</h3>
                                <span
                                    class="bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 p-2 rounded-xl">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </span>
                            </div>

                            <div class="space-y-6">
                                @foreach ($assetsData as $item)
                                    <div
                                        class="flex justify-between items-end border-b border-dashed dark:border-gray-700 pb-2">
                                        <div>
                                            <span
                                                class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-1">Account</span>
                                            <span
                                                class="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-tight">{{ $item['name'] }}</span>
                                        </div>
                                        <span
                                            class="font-mono font-black text-gray-900 dark:text-white">{{ number_format($item['amount'], 2) }}</span>
                                    </div>
                                @endforeach

                                <div class="mt-8 pt-6 border-t-4 border-double border-gray-100 dark:border-gray-700">
                                    <div class="flex justify-between items-center">
                                        <span
                                            class="text-lg font-black text-gray-800 dark:text-white uppercase italic">Total
                                            Assets</span>
                                        <span
                                            class="text-2xl font-black font-mono text-indigo-600 border-b-4 border-indigo-600/20">{{ $settings->currency_symbol }}
                                            {{ number_format($totalAssets, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Liabilities & Equity Section -->
                        <div class="space-y-12">
                            <!-- Liabilities -->
                            <div>
                                <div class="flex justify-between items-center mb-8">
                                    <h3 class="text-xl font-black text-orange-600 uppercase tracking-tighter italic">
                                        Liabilities</h3>
                                    <span class="bg-orange-100 dark:bg-orange-900/30 text-orange-600 p-2 rounded-xl">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                        </svg>
                                    </span>
                                </div>
                                <div class="space-y-6">
                                    @foreach ($liabilitiesData as $item)
                                        <div
                                            class="flex justify-between items-end border-b border-dashed dark:border-gray-700 pb-2">
                                            <div>
                                                <span
                                                    class="text-xs font-black text-gray-400 uppercase tracking-widest block mb-1">Account</span>
                                                <span
                                                    class="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-tight">{{ $item['name'] }}</span>
                                            </div>
                                            <span
                                                class="font-mono font-black text-gray-900 dark:text-white">{{ number_format($item['amount'], 2) }}</span>
                                        </div>
                                    @endforeach
                                    <div class="flex justify-between items-center pt-4">
                                        <span
                                            class="text-sm font-black text-gray-800 dark:text-white uppercase italic">Total
                                            Liabilities</span>
                                        <span
                                            class="text-lg font-black font-mono text-orange-600">{{ number_format($totalLiabilities, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Equity -->
                            <div>
                                <div class="flex justify-between items-center mb-8">
                                    <h3 class="text-xl font-black text-green-600 uppercase tracking-tighter italic">
                                        Equity</h3>
                                    <span class="bg-green-100 dark:bg-green-900/30 text-green-600 p-2 rounded-xl">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                            </path>
                                        </svg>
                                    </span>
                                </div>
                                <div
                                    class="space-y-6 p-6 rounded-2xl bg-gray-50 dark:bg-gray-700/30 border border-gray-100 dark:border-gray-600">
                                    <div class="flex justify-between items-center">
                                        <span
                                            class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Retained
                                            Earnings & Capital</span>
                                        <span
                                            class="font-mono font-black text-green-600 text-lg">{{ number_format($equity, 2) }}</span>
                                    </div>
                                    <div class="pt-4 border-t dark:border-gray-600 flex justify-between items-center">
                                        <span
                                            class="text-sm font-black text-gray-800 dark:text-white uppercase italic">Total
                                            L & E</span>
                                        <span
                                            class="text-2xl font-black font-mono text-indigo-600 border-b-4 border-indigo-600/20">{{ $settings->currency_symbol }}
                                            {{ number_format($totalLiabilities + $equity, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Balance Verification -->
                    <div class="mt-16 flex justify-center">
                        <div
                            class="bg-indigo-600 px-10 py-4 rounded-full shadow-2xl shadow-indigo-200 dark:shadow-none">
                            <p
                                class="text-[10px] font-black uppercase tracking-[0.4em] text-indigo-200 text-center mb-1">
                                Accounting Equation Status</p>
                            <p class="text-white font-black uppercase text-sm tracking-widest flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                Assets = Liabilities + Equity
                            </p>
                        </div>
                    </div>

                    <div class="mt-12 flex justify-end">
                        <button onclick="window.print()"
                            class="text-xs font-black text-gray-500 hover:text-indigo-600 transition uppercase tracking-widest flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                </path>
                            </svg>
                            Print Statement
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
