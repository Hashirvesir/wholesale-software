<x-app-layout>
    <div class="py-8" x-data="{ activeTab: 'sales' }">
        <div class="max-w-[1750px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <div>
                            <h2 class="text-2xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tight flex items-center gap-2">
                                <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Salesman & Recovery Commission Report
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Salesman-wise sales, cash recovery tracking, commission calculation & route sheets</p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('reports.salesman-performance', array_merge(request()->query(), ['export' => 'pdf'])) }}"
                                class="inline-flex items-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs uppercase tracking-wider transition shadow-sm">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Commission PDF
                            </a>

                            <a href="{{ route('reports.salesman-performance', array_merge(request()->query(), ['export' => 'route_pdf'])) }}"
                                class="inline-flex items-center px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs uppercase tracking-wider transition shadow-sm">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Recovery Route PDF
                            </a>

                            <button type="button" onclick="window.print()"
                                class="inline-flex items-center px-3.5 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-700 dark:text-gray-200 rounded-xl font-bold text-xs uppercase tracking-wider transition">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                Print
                            </button>
                        </div>
                    </div>

                    <!-- Filter Bar -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 mb-6">
                        <form action="{{ route('reports.salesman-performance') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-500 mb-1 tracking-wider">Select Salesman</label>
                                <select name="salesman_id" class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:ring-indigo-500 dark:text-gray-100">
                                    <option value="">All Salesmen / Staff</option>
                                    @foreach ($salesmen as $sm)
                                        <option value="{{ $sm->id }}" {{ $selectedSalesmanId == $sm->id ? 'selected' : '' }}>
                                            {{ $sm->name }} ({{ (float)$sm->commission_rate }}% Comm.)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-500 mb-1 tracking-wider">Shop / Warehouse</label>
                                @if (auth()->user()?->warehouse_id)
                                    <div class="w-full bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs p-2.5 font-bold text-gray-700 dark:text-gray-300">
                                        {{ auth()->user()->warehouse->name ?? 'Assigned Shop' }}
                                    </div>
                                @else
                                    <select name="warehouse_id" class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:ring-indigo-500 dark:text-gray-100">
                                        <option value="">All Shops / Warehouses</option>
                                        @foreach ($warehouses as $wh)
                                            <option value="{{ $wh->id }}" {{ $selectedWarehouseId == $wh->id ? 'selected' : '' }}>
                                                {{ $wh->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-500 mb-1 tracking-wider">From Date</label>
                                <input type="date" name="date_from" value="{{ $dateFrom }}"
                                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:ring-indigo-500 dark:text-gray-100">
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-500 mb-1 tracking-wider">To Date</label>
                                <input type="date" name="date_to" value="{{ $dateTo }}"
                                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:ring-indigo-500 dark:text-gray-100">
                            </div>

                            <div class="flex items-end gap-2">
                                <button type="submit"
                                    class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs uppercase tracking-wider transition">
                                    Filter Report
                                </button>
                                <a href="{{ route('reports.salesman-performance') }}"
                                    class="py-2.5 px-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl font-bold text-xs uppercase tracking-wider transition">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Metrics Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="p-4 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-900/40">
                            <div class="text-[11px] font-black uppercase text-indigo-700 dark:text-indigo-400 tracking-wider">Total Sales Booked</div>
                            <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1 font-mono">
                                Rs. {{ number_format($totalSales, 2) }}
                            </div>
                            <div class="text-[10px] text-gray-500 mt-1">{{ $sales->count() }} Invoice(s)</div>
                        </div>

                        <div class="p-4 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/40">
                            <div class="text-[11px] font-black uppercase text-emerald-700 dark:text-emerald-400 tracking-wider">Cash Recoveries Collected</div>
                            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 font-mono">
                                Rs. {{ number_format($totalCollected, 2) }}
                            </div>
                            <div class="text-[10px] text-gray-500 mt-1">{{ $collections->count() }} Collection Voucher(s)</div>
                        </div>

                        <div class="p-4 rounded-2xl bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/40">
                            <div class="text-[11px] font-black uppercase text-amber-700 dark:text-amber-400 tracking-wider">Commission on Sales ({{ $commissionRate }}%)</div>
                            <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 font-mono">
                                Rs. {{ number_format($commissionOnSales, 2) }}
                            </div>
                            <div class="text-[10px] text-gray-500 mt-1">If commission is paid on sales volume</div>
                        </div>

                        <div class="p-4 rounded-2xl bg-purple-50/70 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900/40">
                            <div class="text-[11px] font-black uppercase text-purple-700 dark:text-purple-400 tracking-wider">Commission on Recovery ({{ $commissionRate }}%)</div>
                            <div class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-1 font-mono">
                                Rs. {{ number_format($commissionOnRecovery, 2) }}
                            </div>
                            <div class="text-[10px] text-gray-500 mt-1">If commission is paid on cash collected</div>
                        </div>
                    </div>

                    <!-- Tabs Switcher -->
                    <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6 gap-4 text-xs font-bold">
                        <button type="button" @click="activeTab = 'sales'"
                            class="pb-3 border-b-2 transition"
                            :class="activeTab === 'sales' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700'">
                            Sales Invoices ({{ $sales->count() }})
                        </button>

                        <button type="button" @click="activeTab = 'recoveries'"
                            class="pb-3 border-b-2 transition"
                            :class="activeTab === 'recoveries' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700'">
                            Cash / Payment Collections ({{ $collections->count() }})
                        </button>

                        <button type="button" @click="activeTab = 'route'"
                            class="pb-3 border-b-2 transition"
                            :class="activeTab === 'route' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700'">
                            Daily Recovery Route Sheet ({{ $routeCustomers->count() }} Outstanding Customers)
                        </button>
                    </div>

                    <!-- TAB 1: Sales Invoices -->
                    <div x-show="activeTab === 'sales'">
                        <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                                <thead class="bg-gray-50 dark:bg-gray-900/60 text-[10px] uppercase font-bold text-gray-500">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Date</th>
                                        <th class="px-4 py-3 text-left">Invoice #</th>
                                        <th class="px-4 py-3 text-left">Customer</th>
                                        <th class="px-4 py-3 text-left">Warehouse / Shop</th>
                                        <th class="px-4 py-3 text-right">Invoice Total</th>
                                        <th class="px-4 py-3 text-right">Paid Amount</th>
                                        <th class="px-4 py-3 text-right">Balance Due</th>
                                        <th class="px-4 py-3 text-right">Salesman</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @forelse ($sales as $sale)
                                        @php $due = max(0, $sale->total_amount - $sale->paid_amount); @endphp
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750 transition">
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-400">{{ $sale->date }}</td>
                                            <td class="px-4 py-3 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                                <a href="{{ route('sales.show', $sale) }}" class="hover:underline">Invoice #{{ $sale->id }}</a>
                                            </td>
                                            <td class="px-4 py-3 font-bold text-gray-900 dark:text-gray-100">{{ $sale->party->name ?? 'Walk-In' }}</td>
                                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $sale->warehouse->name ?? 'Default' }}</td>
                                            <td class="px-4 py-3 text-right font-black font-mono text-gray-900 dark:text-gray-100">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                                            <td class="px-4 py-3 text-right font-mono text-emerald-600">Rs. {{ number_format($sale->paid_amount, 2) }}</td>
                                            <td class="px-4 py-3 text-right font-black font-mono {{ $due > 0 ? 'text-rose-600' : 'text-gray-400' }}">Rs. {{ number_format($due, 2) }}</td>
                                            <td class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">{{ $sale->salesman->name ?? 'Unassigned' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-4 py-6 text-center text-gray-400">No sales recorded in this period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 2: Cash Collections -->
                    <div x-show="activeTab === 'recoveries'" style="display: none;">
                        <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                                <thead class="bg-gray-50 dark:bg-gray-900/60 text-[10px] uppercase font-bold text-gray-500">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Date</th>
                                        <th class="px-4 py-3 text-left">Voucher #</th>
                                        <th class="px-4 py-3 text-left">Customer</th>
                                        <th class="px-4 py-3 text-left">Deposited Into Account</th>
                                        <th class="px-4 py-3 text-right">Amount Collected</th>
                                        <th class="px-4 py-3 text-left">Notes</th>
                                        <th class="px-4 py-3 text-right">Collected By</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @forelse ($collections as $col)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750 transition">
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-400">{{ $col->date }}</td>
                                            <td class="px-4 py-3 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                                <a href="{{ route('payments.show', $col) }}" class="hover:underline">PV-{{ $col->id }}</a>
                                            </td>
                                            <td class="px-4 py-3 font-bold text-gray-900 dark:text-gray-100">{{ $col->party->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $col->account->name ?? 'Cash' }}</td>
                                            <td class="px-4 py-3 text-right font-black font-mono text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($col->amount, 2) }}</td>
                                            <td class="px-4 py-3 text-gray-500 text-[11px]">{{ $col->note ?? '-' }}</td>
                                            <td class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">{{ $col->salesman->name ?? 'Unassigned' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-6 text-center text-gray-400">No recovery payments collected in this period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 3: Route Sheet / Recovery List -->
                    <div x-show="activeTab === 'route'" style="display: none;">
                        <div class="mb-4 p-4 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div>
                                <div class="text-sm font-bold text-blue-900 dark:text-blue-200 flex items-center gap-2">
                                    <span>Daily Customer Recovery Route Sheet</span>
                                    @if ($selectedSalesman)
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-blue-200 dark:bg-blue-900 text-blue-900 dark:text-blue-100">
                                            {{ $selectedSalesman->name }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-blue-700 dark:text-blue-300 mt-0.5">
                                    Total Outstanding Target: <strong class="text-red-600 dark:text-red-400">Rs. {{ number_format($routeCustomers->sum('current_bal'), 2) }}</strong> across {{ $routeCustomers->count() }} customer(s)
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('reports.salesman-performance', array_merge(request()->query(), ['export' => 'route_pdf'])) }}"
                                    class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs uppercase tracking-wider transition shadow-sm">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Download Route Sheet (PDF)
                                </a>
                            </div>
                        </div>
                        <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                                <thead class="bg-gray-50 dark:bg-gray-900/60 text-[10px] uppercase font-bold text-gray-500">
                                    <tr>
                                        <th class="px-3 py-3 text-center w-12">#</th>
                                        <th class="px-4 py-3 text-left">Customer Name</th>
                                        <th class="px-4 py-3 text-left">Phone / Contact</th>
                                        <th class="px-4 py-3 text-left">Market Address</th>
                                        @if(!$selectedSalesman)
                                            <th class="px-4 py-3 text-left">Assigned Salesman</th>
                                        @endif
                                        <th class="px-4 py-3 text-right">Outstanding Receivable</th>
                                        <th class="px-4 py-3 text-center w-36">Amount Collected (Rs)</th>
                                        <th class="px-4 py-3 text-center w-36">Customer Signature</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @forelse ($routeCustomers as $index => $rc)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750 transition">
                                            <td class="px-3 py-3 text-center font-bold text-gray-400">{{ $index + 1 }}</td>
                                            <td class="px-4 py-3 font-bold text-gray-900 dark:text-gray-100">
                                                <div>{{ $rc->name }}</div>
                                                @if($rc->price_tier && $rc->price_tier !== 'wholesale')
                                                    <span class="text-[9px] uppercase font-extrabold text-gray-400">({{ $rc->price_tier }})</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 font-mono text-[11px]">{{ $rc->phone ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $rc->address ?? 'N/A' }}</td>
                                            @if(!$selectedSalesman)
                                                <td class="px-4 py-3 text-indigo-600 font-semibold">{{ $rc->salesman->name ?? 'Unassigned' }}</td>
                                            @endif
                                            <td class="px-4 py-3 text-right font-black font-mono text-red-600">Rs. {{ number_format($rc->current_bal, 2) }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <div class="border border-dashed border-gray-300 dark:border-gray-600 rounded h-7 bg-white dark:bg-gray-800"></div>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <div class="border border-dashed border-gray-300 dark:border-gray-600 rounded h-7 bg-white dark:bg-gray-800"></div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $selectedSalesman ? '7' : '8' }}" class="px-4 py-6 text-center text-gray-400">
                                                No pending recovery customers found for this selection.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-50 dark:bg-gray-900/60 font-bold border-t border-gray-200 dark:border-gray-700">
                                        <td colspan="{{ $selectedSalesman ? '4' : '5' }}" class="px-4 py-3 text-right uppercase text-[10px] text-gray-500">
                                            Total Target Due:
                                        </td>
                                        <td class="px-4 py-3 text-right font-black font-mono text-red-600">
                                            Rs. {{ number_format($routeCustomers->sum('current_bal'), 2) }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
