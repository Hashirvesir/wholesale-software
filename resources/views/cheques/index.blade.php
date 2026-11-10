<x-app-layout>
    <div class="py-8" x-data="{
        submitting: false,
        depositModalOpen: false,
        clearModalOpen: false,
        bounceModalOpen: false,
        activeCheque: null,
        selectedBankId: '',
        depositDate: '{{ now()->toDateString() }}',
        clearDate: '{{ now()->toDateString() }}',
        bounceDate: '{{ now()->toDateString() }}',
        bounceReason: '',

        openDeposit(cheque) {
            this.submitting = false;
            this.activeCheque = cheque;
            this.selectedBankId = cheque.bank_account_id || '';
            this.depositModalOpen = true;
        },
        openClear(cheque) {
            this.submitting = false;
            this.activeCheque = cheque;
            this.selectedBankId = cheque.bank_account_id || '';
            this.clearModalOpen = true;
        },
        openBounce(cheque) {
            this.submitting = false;
            this.activeCheque = cheque;
            this.bounceReason = '';
            this.bounceModalOpen = true;
        }
    }">
        <div class="max-w-[1750px] mx-auto px-2 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="p-3.5 sm:p-6 text-gray-900 dark:text-gray-100">
                    
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <div>
                            <h2 class="text-2xl font-black text-gray-900 dark:text-gray-100 italic tracking-tight uppercase flex items-center gap-2">
                                <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                Cheque & PDC Management
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Post-Dated Cheques (Customer In-Hand & Supplier Issued)</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('cheques.create', ['type' => 'received']) }}"
                                class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl font-bold text-xs uppercase tracking-wider transition shadow-sm">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Receive Cheque
                            </a>
                            <a href="{{ route('cheques.create', ['type' => 'issued']) }}"
                                class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl font-bold text-xs uppercase tracking-wider transition shadow-sm">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Issue Cheque
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
                    @if (session('error'))
                        <div class="mb-4 p-4 bg-rose-50 dark:bg-rose-950/40 border-l-4 border-rose-500 text-rose-800 dark:text-rose-300 rounded-r-xl text-sm font-semibold">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Stat Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="p-4 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/40 flex items-center justify-between">
                            <div>
                                <div class="text-[11px] font-black uppercase text-emerald-700 dark:text-emerald-400 tracking-wider">Customer Cheques In-Hand</div>
                                <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1 font-mono">
                                    Rs. {{ number_format($totalReceivedPending, 2) }}
                                </div>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-900/40 flex items-center justify-between">
                            <div>
                                <div class="text-[11px] font-black uppercase text-indigo-700 dark:text-indigo-400 tracking-wider">Issued Cheques Pending</div>
                                <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1 font-mono">
                                    Rs. {{ number_format($totalIssuedPending, 2) }}
                                </div>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/40 flex items-center justify-between">
                            <div>
                                <div class="text-[11px] font-black uppercase text-amber-700 dark:text-amber-400 tracking-wider">Due Today</div>
                                <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 font-mono">
                                    {{ $dueTodayCount }} Cheque{{ $dueTodayCount === 1 ? '' : 's' }}
                                </div>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-rose-50/70 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/40 flex items-center justify-between">
                            <div>
                                <div class="text-[11px] font-black uppercase text-rose-700 dark:text-rose-400 tracking-wider">Overdue / Past Due</div>
                                <div class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1 font-mono">
                                    {{ $overdueCount }} Cheque{{ $overdueCount === 1 ? '' : 's' }}
                                </div>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-900/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Search & Filter Bar -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 mb-6">
                        <form action="{{ route('cheques.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-3">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black uppercase text-gray-500 mb-1 tracking-wider">Search</label>
                                <input type="text" name="search" value="{{ request('search') }}"
                                    placeholder="Cheque #, Drawer Bank, Party..."
                                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:ring-emerald-500 focus:border-emerald-500 dark:text-gray-100">
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-500 mb-1 tracking-wider">Type</label>
                                <select name="type" class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:ring-emerald-500 focus:border-emerald-500 dark:text-gray-100">
                                    <option value="">All Types</option>
                                    <option value="received" {{ request('type') == 'received' ? 'selected' : '' }}>Received (Customer)</option>
                                    <option value="issued" {{ request('type') == 'issued' ? 'selected' : '' }}>Issued (Vendor)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-500 mb-1 tracking-wider">Status</label>
                                <select name="status" class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:ring-emerald-500 focus:border-emerald-500 dark:text-gray-100">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending In-Hand</option>
                                    <option value="deposited" {{ request('status') == 'deposited' ? 'selected' : '' }}>Deposited to Bank</option>
                                    <option value="cleared" {{ request('status') == 'cleared' ? 'selected' : '' }}>Cleared</option>
                                    <option value="bounced" {{ request('status') == 'bounced' ? 'selected' : '' }}>Bounced</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-500 mb-1 tracking-wider">Due Date (From)</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}"
                                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:ring-emerald-500 focus:border-emerald-500 dark:text-gray-100">
                            </div>

                            <div class="flex items-end gap-2">
                                <button type="submit"
                                    class="w-full py-2 px-3 bg-gray-900 dark:bg-gray-700 hover:bg-black text-white rounded-xl font-bold text-xs uppercase tracking-wider transition">
                                    Filter
                                </button>
                                <a href="{{ route('cheques.index') }}"
                                    class="py-2 px-3 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 text-gray-700 dark:text-gray-200 rounded-xl font-bold text-xs uppercase tracking-wider transition">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/60 text-[10px] uppercase font-black tracking-wider text-gray-500 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3 text-left">Cheque # & Bank</th>
                                    <th class="px-4 py-3 text-left">Type</th>
                                    <th class="px-4 py-3 text-left">Party</th>
                                    <th class="px-4 py-3 text-right">Amount</th>
                                    <th class="px-4 py-3 text-left">Due Date</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-left">Bank / Account</th>
                                    <th class="px-4 py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                                @forelse ($cheques as $cheque)
                                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-750 transition">
                                        <td class="px-4 py-3.5">
                                            <a href="{{ route('cheques.show', $cheque) }}" class="font-mono font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                                                #{{ $cheque->cheque_number }}
                                            </a>
                                            <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                                {{ $cheque->drawer_bank ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 whitespace-nowrap">
                                            @if ($cheque->type === 'received')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                    Received
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                    Issued
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="font-bold text-gray-900 dark:text-gray-100">{{ $cheque->party->name ?? 'N/A' }}</div>
                                            <div class="text-[10px] text-gray-400">{{ $cheque->party->phone ?? '' }}</div>
                                        </td>
                                        <td class="px-4 py-3.5 text-right font-black font-mono text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                            Rs. {{ number_format($cheque->amount, 2) }}
                                        </td>
                                        <td class="px-4 py-3.5 whitespace-nowrap">
                                            <div class="font-bold {{ $cheque->isOverdue() ? 'text-red-500' : ($cheque->isDueToday() ? 'text-amber-500' : 'text-gray-700 dark:text-gray-300') }}">
                                                {{ $cheque->due_date->format('d M, Y') }}
                                            </div>
                                            @if ($cheque->isOverdue())
                                                <span class="text-[10px] font-bold text-red-600 uppercase">Overdue</span>
                                            @elseif ($cheque->isDueToday())
                                                <span class="text-[10px] font-bold text-amber-600 uppercase">Due Today!</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                            @if ($cheque->status === 'pending')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                                    Pending
                                                </span>
                                            @elseif ($cheque->status === 'deposited')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                                    Deposited
                                                </span>
                                            @elseif ($cheque->status === 'cleared')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                    Cleared
                                                </span>
                                            @elseif ($cheque->status === 'bounced')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                                                    Bounced
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                                    {{ $cheque->status }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                            {{ $cheque->bankAccount->name ?? 'Not deposited yet' }}
                                            @if ($cheque->cleared_date)
                                                <div class="text-[10px] text-emerald-600">Cleared: {{ $cheque->cleared_date->format('d/m/Y') }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <a href="{{ route('cheques.show', $cheque) }}" title="View Details"
                                                    class="p-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-600 dark:text-gray-300 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                </a>

                                                @if ($cheque->status === 'pending')
                                                    <button type="button" @click="openDeposit({{ $cheque }})" title="Deposit to Bank"
                                                        class="px-2 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 text-[10px] font-bold uppercase hover:bg-blue-100 transition">
                                                        Deposit
                                                    </button>
                                                @endif

                                                @if (in_array($cheque->status, ['pending', 'deposited']))
                                                    <button type="button" @click="openClear({{ $cheque }})" title="Mark Cleared"
                                                        class="px-2 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[10px] font-bold uppercase hover:bg-emerald-100 transition">
                                                        Clear
                                                    </button>

                                                    <button type="button" @click="openBounce({{ $cheque }})" title="Mark Bounced"
                                                        class="px-2 py-1 rounded-lg bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800 text-[10px] font-bold uppercase hover:bg-red-100 transition">
                                                        Bounce
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                            No cheques found matching the criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $cheques->links() }}
                    </div>

                </div>
            </div>
        </div>

        <!-- DEPOSIT MODAL -->
        <div x-show="depositModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/75 backdrop-blur-sm p-4">
            <div @click.away="depositModalOpen = false" class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full shadow-2xl p-6 border border-gray-100 dark:border-gray-700">
                <h3 class="text-base font-black text-gray-900 dark:text-gray-100 uppercase mb-3">Deposit Cheque to Bank</h3>
                <template x-if="activeCheque">
                    <form :action="'/cheques/' + activeCheque.id + '/deposit'" method="POST" @submit="if(submitting){ $event.preventDefault(); return; } submitting = true;" class="space-y-4">
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
                            <button type="submit" :disabled="submitting" :class="submitting ? 'opacity-50 cursor-not-allowed' : ''" class="px-4 py-2 text-xs font-bold rounded-xl bg-blue-600 hover:bg-blue-700 text-white">
                                <span x-show="!submitting">Deposit to Bank</span>
                                <span x-show="submitting">Processing...</span>
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

        <!-- CLEAR MODAL -->
        <div x-show="clearModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/75 backdrop-blur-sm p-4">
            <div @click.away="clearModalOpen = false" class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full shadow-2xl p-6 border border-gray-100 dark:border-gray-700">
                <h3 class="text-base font-black text-gray-900 dark:text-gray-100 uppercase mb-1">Clear Cheque & Post Ledger</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Clearing this cheque will officially update customer ledger and bank accounts.</p>
                <template x-if="activeCheque">
                    <form :action="'/cheques/' + activeCheque.id + '/clear'" method="POST" @submit="if(submitting){ $event.preventDefault(); return; } submitting = true;" class="space-y-4">
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
                            <button type="submit" :disabled="submitting" :class="submitting ? 'opacity-50 cursor-not-allowed' : ''" class="px-4 py-2 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white">
                                <span x-show="!submitting">Confirm Clearance</span>
                                <span x-show="submitting">Processing...</span>
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

        <!-- BOUNCE MODAL -->
        <div x-show="bounceModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/75 backdrop-blur-sm p-4">
            <div @click.away="bounceModalOpen = false" class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full shadow-2xl p-6 border border-gray-100 dark:border-gray-700">
                <h3 class="text-base font-black text-rose-600 uppercase mb-2">Mark Cheque as Bounced</h3>
                <template x-if="activeCheque">
                    <form :action="'/cheques/' + activeCheque.id + '/bounce'" method="POST" @submit="if(submitting){ $event.preventDefault(); return; } submitting = true;" class="space-y-4">
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
                            <button type="submit" :disabled="submitting" :class="submitting ? 'opacity-50 cursor-not-allowed' : ''" class="px-4 py-2 text-xs font-bold rounded-xl bg-rose-600 hover:bg-rose-700 text-white">
                                <span x-show="!submitting">Confirm Bounce</span>
                                <span x-show="submitting">Processing...</span>
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

    </div>
</x-app-layout>
