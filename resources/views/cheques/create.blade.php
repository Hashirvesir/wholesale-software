<x-app-layout>
    <div class="py-8" x-data="{
        type: '{{ old('type', $type ?? 'received') }}',
        partyId: '{{ old('party_id', '') }}',
        parties: @json($parties),

        getSelectedParty() {
            return this.parties.find(p => p.id == this.partyId);
        }
    }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="p-6 sm:p-8 text-gray-900 dark:text-gray-100">
                    
                    <div class="flex items-center justify-between pb-6 border-b border-gray-100 dark:border-gray-700 mb-6">
                        <div>
                            <h2 class="text-xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tight">
                                Register Cheque / PDC
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Record post-dated or in-hand cheque for future bank clearance
                            </p>
                        </div>
                        <a href="{{ route('cheques.index') }}"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            Back to Cheques
                        </a>
                    </div>

                    @if ($errors->any())
                        <div class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 text-xs font-bold">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('cheques.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Cheque Type Selector -->
                        <div>
                            <label class="block text-xs font-black uppercase text-gray-500 mb-2 tracking-wider">Cheque Type *</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center p-4 rounded-xl border-2 cursor-pointer transition"
                                    :class="type === 'received' ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/30 text-emerald-900 dark:text-emerald-200' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300'">
                                    <input type="radio" name="type" value="received" x-model="type" class="sr-only" @change="partyId = ''">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold"
                                            :class="type === 'received' ? 'bg-emerald-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                                        </div>
                                        <div>
                                            <div class="font-black text-sm">Received Cheque</div>
                                            <div class="text-[11px] opacity-75">From Customer (In-Hand custody)</div>
                                        </div>
                                    </div>
                                </label>

                                <label class="flex items-center p-4 rounded-xl border-2 cursor-pointer transition"
                                    :class="type === 'issued' ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/30 text-indigo-900 dark:text-indigo-200' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300'">
                                    <input type="radio" name="type" value="issued" x-model="type" class="sr-only" @change="partyId = ''">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold"
                                            :class="type === 'issued' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                                        </div>
                                        <div>
                                            <div class="font-black text-sm">Issued Cheque</div>
                                            <div class="text-[11px] opacity-75">Given to Supplier / Vendor</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Party & Shop -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                    <span x-text="type === 'received' ? 'Customer *' : 'Vendor / Supplier *'"></span>
                                </label>
                                <select name="party_id" x-model="partyId" required
                                    class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500">
                                    <option value="">Select Party</option>
                                    @foreach ($parties as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ ucfirst($p->type) }} - {{ $p->phone ?? 'No Phone' }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Shop / Warehouse *</label>
                                <select name="warehouse_id" required
                                    class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500">
                                    @foreach ($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ (old('warehouse_id', $settings->shop_warehouse_id ?? '') == $wh->id) ? 'selected' : '' }}>
                                            {{ $wh->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Cheque Details -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Cheque Number *</label>
                                <input type="text" name="cheque_number" value="{{ old('cheque_number') }}" required placeholder="e.g. 7849120"
                                    class="w-full text-xs font-mono font-bold rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Drawer Bank Name</label>
                                <input type="text" name="drawer_bank" value="{{ old('drawer_bank') }}" placeholder="e.g. Meezan Bank, HBL"
                                    class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Amount (Rs.) *</label>
                                <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount') }}" required placeholder="0.00"
                                    class="w-full text-xs font-mono font-bold rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500">
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Cheque Issue / Received Date *</label>
                                <input type="date" name="issue_date" value="{{ old('issue_date', now()->toDateString()) }}" required
                                    class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Due / Maturity Date (Clearing Date) *</label>
                                <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(15)->toDateString()) }}" required
                                    class="w-full text-xs font-bold rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500">
                                <p class="text-[11px] text-gray-500 mt-1">Date when this cheque can be presented to the bank.</p>
                            </div>
                        </div>

                        <!-- Optional Bank Account -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Target Bank Account (Optional)</label>
                            <select name="bank_account_id"
                                class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500">
                                <option value="">Select Later upon Deposit/Clearance</option>
                                @foreach ($bankAccounts as $acc)
                                    <option value="{{ $acc->id }}" {{ old('bank_account_id') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->name }} (Rs. {{ number_format($acc->getBalance(), 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Note -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Notes / Remarks</label>
                            <textarea name="note" rows="2" placeholder="Any comments, invoice references, or guarantees..."
                                class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-emerald-500">{{ old('note') }}</textarea>
                        </div>

                        <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-700">
                            <a href="{{ route('cheques.index') }}"
                                class="px-5 py-2.5 text-xs font-bold rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Cancel
                            </a>
                            <button type="submit"
                                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-md">
                                Save Cheque
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
