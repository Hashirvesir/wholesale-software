<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-xl border border-gray-200 dark:border-gray-700">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a9 9 0 01-12.728 0m0 0l2.829-2.829m-2.829 2.829L3 21m2.828-12.728a5 5 0 017.072 0m0 0l-2.829 2.829"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-2">You are Currently Offline</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
                    Internet connection is not available right now. However, you can still use cached modules and create sales or POS invoices locally!
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-lg mx-auto mb-8">
                    <a href="{{ route('pos.index') }}" class="p-4 rounded-2xl bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/50 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200 transition text-left">
                        <div class="font-black text-sm flex items-center justify-between">
                            <span>POS Counter</span>
                            <span class="text-xs bg-emerald-200 dark:bg-emerald-800 px-2 py-0.5 rounded font-bold">Offline Ready</span>
                        </div>
                        <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-1">Make sales & print thermal receipts offline</p>
                    </a>

                    <a href="{{ route('sales.create') }}" class="p-4 rounded-2xl bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 dark:hover:bg-blue-900/50 border border-blue-200 dark:border-blue-800 text-blue-900 dark:text-blue-200 transition text-left">
                        <div class="font-black text-sm flex items-center justify-between">
                            <span>New Sale Invoice</span>
                            <span class="text-xs bg-blue-200 dark:bg-blue-800 px-2 py-0.5 rounded font-bold">Offline Ready</span>
                        </div>
                        <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">Create wholesale invoices locally</p>
                    </a>

                    <a href="{{ route('payments.create') }}" class="p-4 rounded-2xl bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/40 dark:hover:bg-purple-900/50 border border-purple-200 dark:border-purple-800 text-purple-900 dark:text-purple-200 transition text-left">
                        <div class="font-black text-sm flex items-center justify-between">
                            <span>New Payment</span>
                            <span class="text-xs bg-purple-200 dark:bg-purple-800 px-2 py-0.5 rounded font-bold">Offline Ready</span>
                        </div>
                        <p class="text-xs text-purple-700 dark:text-purple-300 mt-1">Record party collections offline</p>
                    </a>

                    <a href="{{ route('dashboard') }}" class="p-4 rounded-2xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-900/60 dark:hover:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-200 transition text-left">
                        <div class="font-black text-sm flex items-center justify-between">
                            <span>Dashboard</span>
                            <span class="text-xs bg-gray-200 dark:bg-gray-800 px-2 py-0.5 rounded font-bold">Cached</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">View cached metrics & quick statistics</p>
                    </a>
                </div>

                <div class="pt-6 border-t dark:border-gray-700 flex items-center justify-center gap-4">
                    <button type="button" onclick="window.location.reload()"
                        class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition shadow-md">
                        Retry Connection
                    </button>
                    <button type="button" onclick="window.history.back()"
                        class="px-6 py-2.5 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-bold uppercase tracking-wider transition">
                        Go Back
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
