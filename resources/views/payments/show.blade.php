<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl p-8 mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <!-- Payment Header -->
                <div class="p-8 border-b dark:border-gray-700">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            @if ($settings->company_logo)
                                <img src="{{ asset($settings->company_logo) }}" alt="Company Logo"
                                    class="h-16 w-auto mb-2">
                            @endif
                            <h1 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tighter">
                                {{ $settings->company_name }}</h1>
                            <p class="text-sm text-gray-500">{{ $settings->company_address }}</p>
                            <p class="text-sm text-gray-500">{{ $settings->company_phone }} |
                                {{ $settings->company_email }}</p>
                        </div>
                        <div class="text-right">
                            <h2 class="text-4xl font-black text-gray-200 dark:text-gray-700 uppercase tracking-tighter">
                                PAYMENT</h2>
                        </div>
                    </div>

                    <div class="flex justify-between items-start">
                        <div>
                            <h2
                                class="text-xl font-bold text-primary-600 dark:text-primary-400 italic tracking-tighter uppercase">
                                Payment Details</h2>
                            <p class="text-sm text-gray-500 mt-1">Payment ID: <span
                                    class="font-bold text-gray-700 dark:text-gray-300">#{{ $payment->id }}</span>
                            </p>
                            <p class="text-sm text-gray-500">Date: <span
                                    class="font-bold text-gray-700 dark:text-gray-300">{{ $payment->date }}</span></p>
                        </div>
                        <div class="text-right">
                            @if ($payment->payment_type === 'received')
                                <span
                                    class="px-3 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full text-xs font-bold uppercase tracking-widest italic">Payment
                                    Received</span>
                            @else
                                <span
                                    class="px-3 py-1 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 rounded-full text-xs font-bold uppercase tracking-widest italic">Payment
                                    Paid</span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-8 mt-8">
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Party
                                Information</h4>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $payment->party->name }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $payment->party->phone }}</p>
                            <p class="text-sm text-gray-500 italic">{{ $payment->party->address }}</p>
                            <span
                                class="mt-2 inline-block px-2 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded text-xs font-bold uppercase">
                                {{ $payment->party->type }}
                            </span>
                        </div>
                        <div class="text-right">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Payment Method
                            </h4>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $payment->account->name }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $payment->account->type }} Account
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Payment Amount Section -->
                <div class="p-8 bg-gray-50 dark:bg-gray-900/50">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
                        <div class="max-w-sm w-full">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Payment Note</h4>
                            <p class="text-sm text-gray-700 dark:text-gray-300 italic">
                                {{ $payment->note ?? 'No additional notes provided.' }}</p>
                        </div>
                        <div class="w-full md:w-64 space-y-3">
                            <div
                                class="flex justify-between items-center py-4 border-y-4 border-gray-300 dark:border-gray-600">
                                <span
                                    class="text-lg font-black text-gray-700 dark:text-gray-300 uppercase tracking-tight">Payment
                                    Amount:</span>
                                <span
                                    class="text-3xl font-black {{ $payment->payment_type === 'received' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $settings->currency_symbol }}
                                    {{ number_format($payment->amount, 2) }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm font-bold pt-2">
                                <span class="text-gray-600 dark:text-gray-400">Current Party Balance:</span>
                                <span
                                    class="{{ $payment->party->getCurrentBalance() >= 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $settings->currency_symbol }}
                                    {{ number_format(abs($payment->party->getCurrentBalance()), 2) }}
                                    {{ $payment->party->getCurrentBalance() >= 0 ? '(Receivable)' : '(Payable)' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction Type Explanation -->
                <div class="px-8 py-4 bg-blue-50 dark:bg-blue-900/20 border-t border-blue-200 dark:border-blue-800">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-xs text-blue-700 dark:text-blue-300">
                            <p class="font-bold uppercase tracking-wide mb-1">Transaction Type:</p>
                            @if ($payment->payment_type === 'received')
                                <p><span class="font-bold">Payment Received</span> - Cash/Bank account has been <span
                                        class="text-green-600 font-bold">Debited</span>, and the party's account has
                                    been <span class="text-green-600 font-bold">Credited</span> (reducing their
                                    receivable balance).</p>
                            @else
                                <p><span class="font-bold">Payment Paid</span> - Party's account has been <span
                                        class="text-red-600 font-bold">Debited</span> (reducing their payable balance),
                                    and Cash/Bank account has been <span class="text-red-600 font-bold">Credited</span>.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div
                    class="mb-4 p-4 bg-green-100 dark:bg-green-900 border-l-4 border-green-500 text-green-700 dark:text-green-300 rounded-r-xl font-bold text-sm no-print">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mt-8 flex justify-between items-center px-4 no-print">
                <a href="{{ route('payments.index') }}"
                    class="text-sm text-gray-500 hover:text-gray-800 dark:hover:text-gray-300 font-bold flex items-center transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to List
                </a>
                <div class="flex gap-2">
                    <button onclick="window.print()"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg font-bold shadow-lg hover:shadow-xl transition transform hover:-translate-y-1 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                            </path>
                        </svg>
                        Print Receipt
                    </button>
                    <a href="{{ route('payments.pdf', $payment) }}" target="_blank"
                        class="px-6 py-2 bg-red-600 text-white rounded-lg font-bold shadow-lg hover:shadow-xl transition transform hover:-translate-y-1 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                            </path>
                        </svg>
                        Download PDF
                    </a>
                    <a href="{{ route('payments.thermal', $payment) }}" target="_blank"
                        class="px-6 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-lg font-bold shadow-lg hover:shadow-xl transition transform hover:-translate-y-1 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                            </path>
                        </svg>
                        Thermal Receipt (72mm)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .py-12 {
                padding: 0;
            }

            nav,
            .no-print {
                display: none !important;
            }

            .shadow-xl {
                box-shadow: none !important;
                border: 1px solid #eee;
            }

            body {
                background: white !important;
            }
        }
    </style>
</x-app-layout>
