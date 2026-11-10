<x-app-layout>
    <div class="py-8" x-data="{
        depositModalOpen: false,
        clearModalOpen: false,
        bounceModalOpen: false,
        selectedBankId: '{{ $cheque->bank_account_id ?? '' }}',
        depositDate: '{{ now()->toDateString() }}',
        clearDate: '{{ now()->toDateString() }}',
        bounceDate: '{{ now()->toDateString() }}',
        bounceReason: ''
    }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="p-6 sm:p-8 text-gray-900 dark:text-gray-100">

                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100 dark:border-gray-700 mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center {{ $cheque->type === 'received' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600' : 'bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600' }}">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-xl font-black text-gray-900 dark:text-gray-100 font-mono tracking-tight">
                                        Cheque #{{ $cheque->cheque_number }}
                                    </h2>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider
                                        {{ $cheque->status === 'cleared' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' :
                                           ($cheque->status === 'bounced' ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' :
                                           ($cheque->status === 'deposited' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' :
                                           'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300')) }}">
                                        {{ $cheque->status }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $cheque->type === 'received' ? 'Customer Cheque (In-Hand Custody)' : 'Issued Supplier Cheque' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('cheques.index') }}"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Back
                            </a>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="mb-4 p-4 bg-emerald-50 dark:bg-emerald-950/40 border-l-4 border-emerald-500 text-emerald-800 dark:text-emerald-300 rounded-r-xl text-sm font-semibold">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('warning'))
                        <div class="mb-4 p-4 bg-amber-50 dark:bg-amber-950/40 border-l-4 border-amber-500 text-amber-800 dark:text-amber-300 rounded-r-xl text-sm font-semibold">
                            {{ session('warning') }}
                        </div>
                    @endif

                    <!-- Main Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- Cheque Details Card -->
                        <div class="p-5 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700 space-y-4">
                            <h3 class="text-xs font-black uppercase text-gray-400 tracking-wider">Cheque Information</h3>
                            
                            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-xs text-gray-500">Amount</span>
                                <span class="text-xl font-black text-gray-900 dark:text-gray-100 font-mono">
                                    Rs. {{ number_format($cheque->amount, 2) }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-xs text-gray-500">Drawer Bank</span>
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                    {{ $cheque->drawer_bank ?? 'N/A' }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-xs text-gray-500">Issue Date</span>
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                    {{ $cheque->issue_date->format('d M, Y') }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-xs text-gray-500">Maturity / Due Date</span>
                                <span class="text-xs font-bold font-mono {{ $cheque->isOverdue() ? 'text-red-600' : ($cheque->isDueToday() ? 'text-amber-600' : 'text-gray-800 dark:text-gray-200') }}">
                                    {{ $cheque->due_date->format('d M, Y') }}
                                    @if ($cheque->isOverdue()) (Overdue) @endif
                                </span>
                            </div>

                            <div class="flex justify-between items-center py-2">
                                <span class="text-xs text-gray-500">Shop / Branch</span>
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                    {{ $cheque->warehouse->name ?? 'Default Shop' }}
                                </span>
                            </div>
                        </div>

                        <!-- Party & Bank Card -->
                        <div class="p-5 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700 space-y-4">
                            <h3 class="text-xs font-black uppercase text-gray-400 tracking-wider">Party & Bank Account</h3>

                            <div class="py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-xs text-gray-500 block mb-0.5">Party Name</span>
                                <span class="text-base font-bold text-gray-900 dark:text-gray-100">
                                    {{ $cheque->party->name ?? 'N/A' }}
                                </span>
                                <div class="text-xs text-gray-400 mt-0.5">{{ $cheque->party->phone ?? 'No Phone' }}</div>
                            </div>

                            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-xs text-gray-500">Current Party Balance</span>
                                @if ($cheque->party)
                                    @php $bal = $cheque->party->getCurrentBalance(); @endphp
                                    <span class="text-xs font-mono font-bold {{ $bal >= 0 ? 'text-red-500' : 'text-emerald-500' }}">
                                        Rs. {{ number_format(abs($bal), 2) }} {{ $bal >= 0 ? '(Receivable)' : '(Payable)' }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-xs text-gray-500">Depositing Bank Account</span>
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                    {{ $cheque->bankAccount->name ?? 'Not selected' }}
                                </span>
                            </div>

                            @if ($cheque->payment_id)
                                <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                    <span class="text-xs text-gray-500">Linked Payment</span>
                                    <a href="{{ route('payments.show', $cheque->payment_id) }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                        View Payment #{{ $cheque->payment_id }}
                                    </a>
                                </div>
                            @endif

                            @if ($cheque->note)
                                <div class="py-2">
                                    <span class="text-xs text-gray-500 block mb-1">Remarks</span>
                                    <p class="text-xs text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 p-2.5 rounded-xl border border-gray-200 dark:border-gray-700">
                                        {{ $cheque->note }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Bounce details if bounced -->
                    @if ($cheque->status === 'bounced')
                        <div class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 text-red-800 dark:text-red-200">
                            <h4 class="text-xs font-black uppercase tracking-wider flex items-center gap-1.5 mb-1">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                Cheque Bounced on {{ $cheque->bounced_date ? $cheque->bounced_date->format('d M, Y') : 'N/A' }}
                            </h4>
                            <p class="text-xs">Reason: <strong>{{ $cheque->bounce_reason }}</strong></p>
                        </div>
                    @endif

                    <!-- Action Bar -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-6 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-2">
                            @if ($cheque->status === 'pending')
                                <button type="button" @click="depositModalOpen = true"
                                    class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm">
                                    Deposit to Bank
                                </button>
                            @endif

                            @if (in_array($cheque->status, ['pending', 'deposited']))
                                <button type="button" @click="clearModalOpen = true"
                                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm">
                                    Clear & Post to Ledger
                                </button>

                                <button type="button" @click="bounceModalOpen = true"
                                    class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm">
                                    Mark Bounced
                                </button>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            @if ($cheque->status !== 'cleared')
                                <form action="{{ route('cheques.destroy', $cheque) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this cheque record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2.5 rounded-xl border border-rose-300 dark:border-rose-800 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-xs font-bold uppercase tracking-wider transition">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- DEPOSIT MODAL -->
        <div x-show="depositModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/75 backdrop-blur-sm p-4">
            <div @click.away="depositModalOpen = false" class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full shadow-2xl p-6 border border-gray-100 dark:border-gray-700">
                <h3 class="text-base font-black text-gray-900 dark:text-gray-100 uppercase mb-3">Deposit Cheque to Bank</h3>
                <form action="{{ route('cheques.deposit', $cheque) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Select Bank Account *</label>
                        <select name="bank_account_id" x-model="selectedBankId" required
                            class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500">
                            <option value="">Choose Bank Account</option>
                            @foreach ($bankAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} (Rs. {{ number_format($acc->getBalance(), 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Deposited Date *</label>
                        <input type="date" name="deposited_date" x-model="depositDate" required
                            class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500">
                    </div>
                    <div class="pt-3 flex justify-end gap-2 border-t dark:border-gray-700">
                        <button type="button" @click="depositModalOpen = false" class="px-4 py-2 text-xs font-bold rounded-xl border dark:border-gray-600">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl bg-blue-600 hover:bg-blue-700 text-white">Deposit to Bank</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- CLEAR MODAL -->
        <div x-show="clearModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/75 backdrop-blur-sm p-4">
            <div @click.away="clearModalOpen = false" class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full shadow-2xl p-6 border border-gray-100 dark:border-gray-700">
                <h3 class="text-base font-black text-gray-900 dark:text-gray-100 uppercase mb-1">Clear Cheque & Post Ledger</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Clearing this cheque will officially update customer ledger and bank accounts.</p>
                <form action="{{ route('cheques.clear', $cheque) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Bank Account *</label>
                        <select name="bank_account_id" x-model="selectedBankId" required
                            class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500">
                            <option value="">Choose Bank Account</option>
                            @foreach ($bankAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Clearance Date *</label>
                        <input type="date" name="cleared_date" x-model="clearDate" required
                            class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500">
                    </div>
                    <div class="pt-3 flex justify-end gap-2 border-t dark:border-gray-700">
                        <button type="button" @click="clearModalOpen = false" class="px-4 py-2 text-xs font-bold rounded-xl border dark:border-gray-600">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white">Confirm Clearance</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- BOUNCE MODAL -->
        <div x-show="bounceModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/75 backdrop-blur-sm p-4">
            <div @click.away="bounceModalOpen = false" class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full shadow-2xl p-6 border border-gray-100 dark:border-gray-700">
                <h3 class="text-base font-black text-rose-600 uppercase mb-2">Mark Cheque as Bounced</h3>
                <form action="{{ route('cheques.bounce', $cheque) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Bounced Date *</label>
                        <input type="date" name="bounced_date" x-model="bounceDate" required
                            class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-rose-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Reason for Bounce *</label>
                        <textarea name="bounce_reason" x-model="bounceReason" required rows="3"
                            placeholder="e.g. Insufficient funds, Signature mismatch, Account closed"
                            class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-rose-500"></textarea>
                    </div>
                    <div class="pt-3 flex justify-end gap-2 border-t dark:border-gray-700">
                        <button type="button" @click="bounceModalOpen = false" class="px-4 py-2 text-xs font-bold rounded-xl border dark:border-gray-600">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl bg-rose-600 hover:bg-rose-700 text-white">Confirm Bounce</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
