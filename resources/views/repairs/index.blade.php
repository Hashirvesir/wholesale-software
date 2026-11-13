<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12">
        <div class="max-w-[1750px] mx-auto px-2 sm:px-6 lg:px-8 space-y-6">

            <!-- Header bar matching purchases.index -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-gray-100 tracking-wide uppercase flex items-center gap-2">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg>
                        <span>{{ __('Mobile Repairing Management') }}</span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('Manage job sheets, multiple device intakes, live inventory spare parts, thermal stickers & receipts.') }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('repairs.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md transition-all active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>{{ __('New Repair / Mobile Jama Karein') }}</span>
                    </a>
                </div>
            </div>

            <!-- Flash Message -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-2xl flex items-center justify-between text-emerald-800 dark:text-emerald-200 text-sm font-semibold">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Quick Counter Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-4">
                <a href="{{ route('repairs.index', ['status' => 'all']) }}" class="p-4 rounded-2xl border transition-all duration-150 {{ request('status', 'all') === 'all' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-600/20' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:border-indigo-400' }}">
                    <div class="text-[11px] font-bold uppercase tracking-wider {{ request('status', 'all') === 'all' ? 'text-indigo-100' : 'text-gray-400' }}">All Orders</div>
                    <div class="text-2xl font-black mt-1">{{ number_format($counts['all']) }}</div>
                </a>
                <a href="{{ route('repairs.index', ['status' => 'pending']) }}" class="p-4 rounded-2xl border transition-all duration-150 {{ request('status') === 'pending' ? 'bg-amber-500 text-white border-amber-500 shadow-md shadow-amber-500/20' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:border-amber-400' }}">
                    <div class="text-[11px] font-bold uppercase tracking-wider {{ request('status') === 'pending' ? 'text-amber-100' : 'text-amber-500' }}">Pending</div>
                    <div class="text-2xl font-black mt-1">{{ number_format($counts['pending']) }}</div>
                </a>
                <a href="{{ route('repairs.index', ['status' => 'in_progress']) }}" class="p-4 rounded-2xl border transition-all duration-150 {{ request('status') === 'in_progress' ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-600/20' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:border-blue-400' }}">
                    <div class="text-[11px] font-bold uppercase tracking-wider {{ request('status') === 'in_progress' ? 'text-blue-100' : 'text-blue-500' }}">In Progress</div>
                    <div class="text-2xl font-black mt-1">{{ number_format($counts['in_progress']) }}</div>
                </a>
                <a href="{{ route('repairs.index', ['status' => 'completed']) }}" class="p-4 rounded-2xl border transition-all duration-150 {{ request('status') === 'completed' ? 'bg-emerald-600 text-white border-emerald-600 shadow-md shadow-emerald-600/20' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:border-emerald-400' }}">
                    <div class="text-[11px] font-bold uppercase tracking-wider {{ request('status') === 'completed' ? 'text-emerald-100' : 'text-emerald-500' }}">Ready / Completed</div>
                    <div class="text-2xl font-black mt-1">{{ number_format($counts['completed']) }}</div>
                </a>
                <a href="{{ route('repairs.index', ['status' => 'delivered']) }}" class="p-4 rounded-2xl border transition-all duration-150 {{ request('status') === 'delivered' ? 'bg-teal-600 text-white border-teal-600 shadow-md shadow-teal-600/20' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:border-teal-400' }}">
                    <div class="text-[11px] font-bold uppercase tracking-wider {{ request('status') === 'delivered' ? 'text-teal-100' : 'text-teal-500' }}">Delivered</div>
                    <div class="text-2xl font-black mt-1">{{ number_format($counts['delivered']) }}</div>
                </a>
            </div>

            <!-- Filters & Search Bar -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
                <form method="GET" action="{{ route('repairs.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                    <input type="hidden" name="status" value="{{ request('status', 'all') }}">

                    <div class="sm:col-span-6 relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search Order #, Customer Name, Phone, Model, IMEI, or Tracking #...') }}" class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div class="sm:col-span-2">
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="sm:col-span-2">
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="sm:col-span-2 flex items-center gap-2">
                        <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs sm:text-sm rounded-xl transition flex items-center justify-center gap-1.5 shadow">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            <span>{{ __('Filter') }}</span>
                        </button>
                        @if(request()->anyFilled(['search', 'date_from', 'date_to', 'status']))
                            <a href="{{ route('repairs.index') }}" class="p-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-xl" title="Reset">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Repairs List Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 dark:bg-gray-900/60 border-b border-gray-200 dark:border-gray-700 text-[11px] font-black text-gray-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4">Order / Token</th>
                                <th class="py-3.5 px-4">Customer</th>
                                <th class="py-3.5 px-4">Devices & Issues</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 text-right">Estimated</th>
                                <th class="py-3.5 px-4 text-right">Advance</th>
                                <th class="py-3.5 px-4 text-right">Balance</th>
                                <th class="py-3.5 px-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-xs sm:text-sm">
                            @forelse($repairs as $order)
                                <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-950/20 transition-colors">
                                    <!-- Order No & Date -->
                                    <td class="py-3.5 px-4 align-top">
                                        <a href="{{ route('repairs.show', $order->id) }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                            {{ $order->order_no }}
                                        </a>
                                        <div class="text-[11px] text-gray-400 mt-0.5">
                                            {{ $order->received_date->format('d M Y, h:i A') }}
                                        </div>
                                    </td>

                                    <!-- Customer Details -->
                                    <td class="py-3.5 px-4 align-top">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $order->customer_name }}</div>
                                        <div class="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            <span>{{ $order->customer_phone }}</span>
                                        </div>
                                    </td>

                                    <!-- Devices List (Multiple Support) -->
                                    <td class="py-3.5 px-4 align-top">
                                        <div class="space-y-1.5">
                                            @foreach($order->devices as $dev)
                                                <div class="p-2 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700/80 text-xs">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span class="font-bold text-gray-800 dark:text-gray-200">
                                                            {{ $dev->brand }} {{ $dev->model }}
                                                        </span>
                                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $dev->status_color }}">
                                                            {{ ucfirst(str_replace('_', ' ', $dev->status)) }}
                                                        </span>
                                                    </div>
                                                    <div class="text-[11px] text-gray-500 mt-1 line-clamp-1">
                                                        <span class="font-semibold text-gray-400">Issue:</span> {{ $dev->problem_description }}
                                                    </div>
                                                    @if($dev->parts->count() > 0)
                                                        <div class="text-[10px] text-indigo-500 font-medium mt-0.5">
                                                            {{ $dev->parts->count() }} spare part(s) selected
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="py-3.5 px-4 align-top">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $order->status_color }}">
                                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                        </span>
                                        @if($order->expected_delivery_date)
                                            <div class="text-[10px] text-gray-400 mt-1">
                                                Exp: {{ $order->expected_delivery_date->format('d M') }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Total Amount -->
                                    <td class="py-3.5 px-4 align-top text-right font-bold text-gray-900 dark:text-white">
                                        Rs {{ number_format($order->total_amount, 2) }}
                                    </td>

                                    <!-- Advance Amount -->
                                    <td class="py-3.5 px-4 align-top text-right text-emerald-600 dark:text-emerald-400 font-semibold">
                                        Rs {{ number_format($order->advance_amount, 2) }}
                                    </td>

                                    <!-- Balance Amount -->
                                    <td class="py-3.5 px-4 align-top text-right font-black {{ $order->balance_amount > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400' }}">
                                        Rs {{ number_format($order->balance_amount, 2) }}
                                    </td>

                                    <!-- Actions Menu -->
                                    <td class="py-3.5 px-4 align-top text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <!-- View Details -->
                                            <a href="{{ route('repairs.show', $order->id) }}" class="p-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-lg transition" title="View Details">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>

                                            <!-- Edit Order -->
                                            <a href="{{ route('repairs.edit', $order->id) }}" class="p-1.5 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 rounded-lg transition" title="Edit Order (Parts & Labor)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>

                                            <!-- Print Both Together (Sticker + Customer Invoice) -->
                                            <a href="{{ route('repairs.print-combo', $order->id) }}" target="_blank" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-lg transition" title="Print Both (Sticker + Customer Copy)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            </a>

                                            <!-- Print Stickers (Slip 1) -->
                                            <a href="{{ route('repairs.stickers', $order->id) }}" target="_blank" class="p-1.5 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 rounded-lg transition" title="Print Mobile Stickers (Slip 1)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                            </a>

                                            <!-- Print Intake Receipt (Slip 2) -->
                                            <a href="{{ route('repairs.receipt', $order->id) }}" target="_blank" class="p-1.5 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 rounded-lg transition" title="Print Customer Receipt (Slip 2)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg>
                                            <p class="font-medium text-sm">{{ __('No repair orders found.') }}</p>
                                            <a href="{{ route('repairs.create') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                                                {{ __('Receive first mobile now') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($repairs->hasPages())
                    <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                        {{ $repairs->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
