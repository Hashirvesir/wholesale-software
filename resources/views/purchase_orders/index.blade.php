<x-app-layout>
    <div class="py-8">
        <div class="max-w-[1750px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <div>
                            <h2 class="text-2xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tight flex items-center gap-2">
                                <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Purchase Orders (PO) & GRN
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Supplier orders booking, partial shipments tracking & Goods Receipt Notes</p>
                        </div>
                        <a href="{{ route('purchase-orders.create') }}"
                            class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl font-bold text-xs uppercase tracking-wider transition shadow-sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Create Purchase Order
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="mb-4 p-4 bg-emerald-50 dark:bg-emerald-950/40 border-l-4 border-emerald-500 text-emerald-800 dark:text-emerald-300 rounded-r-xl text-sm font-semibold">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 p-4 bg-rose-50 dark:bg-rose-950/40 border-l-4 border-rose-500 text-rose-800 dark:text-rose-300 rounded-r-xl text-sm font-semibold">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Stat Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="p-4 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-900/40 flex items-center justify-between">
                            <div>
                                <div class="text-[11px] font-black uppercase text-indigo-700 dark:text-indigo-400 tracking-wider">Total Orders</div>
                                <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1 font-mono">
                                    {{ $totalOrdersCount }}
                                </div>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/40 flex items-center justify-between">
                            <div>
                                <div class="text-[11px] font-black uppercase text-amber-700 dark:text-amber-400 tracking-wider">Pending Orders</div>
                                <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 font-mono">
                                    {{ $pendingCount }}
                                </div>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center text-amber-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-blue-50/70 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-900/40 flex items-center justify-between">
                            <div>
                                <div class="text-[11px] font-black uppercase text-blue-700 dark:text-blue-400 tracking-wider">Partial Receiving</div>
                                <div class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-1 font-mono">
                                    {{ $partialCount }}
                                </div>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/40 flex items-center justify-between">
                            <div>
                                <div class="text-[11px] font-black uppercase text-emerald-700 dark:text-emerald-400 tracking-wider">Completed / Fully Received</div>
                                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 font-mono">
                                    {{ $completedCount }}
                                </div>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Bar -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 mb-6">
                        <form action="{{ route('purchase-orders.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-500 mb-1 tracking-wider">Search</label>
                                <input type="text" name="search" value="{{ request('search') }}"
                                    placeholder="PO #, Supplier Name..."
                                    class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:ring-indigo-500 dark:text-gray-100">
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-500 mb-1 tracking-wider">Status</label>
                                <select name="status" class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:ring-indigo-500 dark:text-gray-100">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial Received</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-500 mb-1 tracking-wider">Supplier</label>
                                <select name="party_id" class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:ring-indigo-500 dark:text-gray-100">
                                    <option value="">All Suppliers</option>
                                    @foreach ($vendors as $v)
                                        <option value="{{ $v->id }}" {{ request('party_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-500 mb-1 tracking-wider">Destination Warehouse</label>
                                <select name="warehouse_id" class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl text-xs focus:ring-indigo-500 dark:text-gray-100">
                                    <option value="">All Warehouses</option>
                                    @foreach ($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-end gap-2">
                                <button type="submit"
                                    class="w-full py-2 px-3 bg-gray-900 dark:bg-gray-700 hover:bg-black text-white rounded-xl font-bold text-xs uppercase tracking-wider transition">
                                    Filter
                                </button>
                                <a href="{{ route('purchase-orders.index') }}"
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
                                    <th class="px-4 py-3 text-left">PO #</th>
                                    <th class="px-4 py-3 text-left">Date</th>
                                    <th class="px-4 py-3 text-left">Supplier</th>
                                    <th class="px-4 py-3 text-left">Warehouse</th>
                                    <th class="px-4 py-3 text-right">Order Value</th>
                                    <th class="px-4 py-3 text-center">Fulfillment Progress</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                                @forelse ($purchaseOrders as $po)
                                    @php
                                        $ordered = $po->getTotalOrderedQty();
                                        $received = $po->getTotalReceivedQty();
                                        $pct = $ordered > 0 ? min(100, round(($received / $ordered) * 100)) : 0;
                                    @endphp
                                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-750 transition">
                                        <td class="px-4 py-3.5 whitespace-nowrap">
                                            <a href="{{ route('purchase-orders.show', $po) }}" class="font-mono font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                                {{ $po->po_number }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3.5 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                            {{ $po->order_date->format('d M, Y') }}
                                        </td>
                                        <td class="px-4 py-3.5 font-bold text-gray-900 dark:text-gray-100">
                                            {{ $po->party->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3.5 text-gray-600 dark:text-gray-400">
                                            {{ $po->warehouse->name ?? 'Default' }}
                                        </td>
                                        <td class="px-4 py-3.5 text-right font-black font-mono text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                            Rs. {{ number_format($po->total_amount, 2) }}
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <div class="w-36 mx-auto">
                                                <div class="flex justify-between text-[10px] font-bold mb-0.5">
                                                    <span>{{ number_format($received, 0) }} / {{ number_format($ordered, 0) }}</span>
                                                    <span>{{ $pct }}%</span>
                                                </div>
                                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                                    <div class="h-1.5 rounded-full {{ $pct == 100 ? 'bg-emerald-500' : ($pct > 0 ? 'bg-blue-500' : 'bg-gray-400') }}" style="width: {{ $pct }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                            @if ($po->status === 'completed')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                    Completed
                                                </span>
                                            @elseif ($po->status === 'partial')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                                    Partial
                                                </span>
                                            @elseif ($po->status === 'cancelled')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                                                    Cancelled
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <a href="{{ route('purchase-orders.show', $po) }}"
                                                    class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-[10px] font-bold uppercase hover:bg-indigo-100 transition">
                                                    View / GRN
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                            No Purchase Orders found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $purchaseOrders->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
