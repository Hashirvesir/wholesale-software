<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <!-- Order Title & Status -->
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="font-bold text-xl text-gray-900 dark:text-gray-100 leading-tight">
                        {{ $repair->order_no }}
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $repair->status_color }}">
                        {{ ucfirst(str_replace('_', ' ', $repair->status)) }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Received: {{ $repair->received_date->format('d M Y, h:i A') }}
                    @if($repair->expected_delivery_date)
                        &bull; Exp. Delivery: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $repair->expected_delivery_date->format('d M Y, h:i A') }}</span>
                    @endif
                </p>
            </div>

            <!-- Clean Top Action Controls (Print & Edit) -->
            <div class="flex items-center gap-2" x-data="{ printDropdown: false }">
                
                <!-- Main 1-Click Print Button (Both Sticker + Customer Slip) -->
                <a href="{{ route('repairs.print-combo', $repair->id) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Print Slips (Sticker + Copy)</span>
                </a>

                <!-- Dropdown for Individual Slips (Sticker Only / Slip Only / Invoice) -->
                <div class="relative" @click.away="printDropdown = false">
                    <button type="button" @click="printDropdown = !printDropdown"
                            class="px-2.5 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1"
                            title="More Print Options">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="printDropdown"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-1.5 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50 text-xs"
                         style="display: none;">
                        <a href="{{ route('repairs.stickers', $repair->id) }}" target="_blank"
                           class="flex items-center gap-2 px-3.5 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <span>🏷️</span>
                            <span>Mobile Sticker Only (Slip 1)</span>
                        </a>
                        <a href="{{ route('repairs.receipt', $repair->id) }}" target="_blank"
                           class="flex items-center gap-2 px-3.5 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <span>🧾</span>
                            <span>Customer Slip Only (Slip 2)</span>
                        </a>
                        <a href="{{ route('repairs.invoice', $repair->id) }}" target="_blank"
                           class="flex items-center gap-2 px-3.5 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 border-t border-gray-100 dark:border-gray-700">
                            <span>📄</span>
                            <span>Final Delivery Invoice</span>
                        </a>
                    </div>
                </div>

                <!-- Edit Order -->
                <a href="{{ route('repairs.edit', $repair->id) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold text-xs rounded-xl shadow-sm transition">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>Edit</span>
                </a>

                <!-- Back to Repairs List -->
                <a href="{{ route('repairs.index') }}"
                   class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold text-xs rounded-xl shadow-sm transition">
                    &larr; Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ deliverModal: false }">
        <div class="max-w-[1750px] mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            <!-- Clean Light Success Flash Notice -->
            @if(session('success'))
                <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 rounded-xl flex items-center justify-between text-emerald-800 dark:text-emerald-200 text-xs font-semibold">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- 3 Clean Light Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                
                <!-- 1. Customer Info Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm space-y-2.5">
                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Customer Details</div>
                    <div class="text-base font-bold text-gray-900 dark:text-white">{{ $repair->customer_name }}</div>
                    <div class="text-xs text-gray-600 dark:text-gray-300 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $repair->customer_phone) }}" target="_blank"
                           class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                            {{ $repair->customer_phone }} (WhatsApp)
                        </a>
                    </div>
                    @if($repair->notes)
                        <div class="text-xs text-gray-500 bg-gray-50 dark:bg-gray-900/60 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700/60 mt-1">
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Notes:</span> {{ $repair->notes }}
                        </div>
                    @endif
                </div>

                <!-- 2. Payment & Balance Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm space-y-2">
                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Payment & Balance</div>
                    <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-300">
                        <span>Total Estimated:</span>
                        <strong class="text-xs font-bold text-gray-900 dark:text-white">Rs {{ number_format($repair->total_amount, 2) }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-300">
                        <span>Advance Paid:</span>
                        <strong class="text-xs font-bold text-emerald-600 dark:text-emerald-400">Rs {{ number_format($repair->advance_amount, 2) }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-xs border-t border-gray-100 dark:border-gray-700 pt-2 mt-1">
                        <span class="font-bold text-gray-700 dark:text-gray-300">Remaining Balance:</span>
                        <strong class="text-base font-black {{ $repair->balance_amount > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            Rs {{ number_format($repair->balance_amount, 2) }}
                        </strong>
                    </div>
                </div>

                <!-- 3. Delivery Action Card (Light & Clean) -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Delivery & Settlement</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Collect remaining balance, mark order completed, and deduct spare parts from inventory.
                        </div>
                    </div>
                    <div class="mt-3">
                        @if($repair->status !== 'delivered')
                            <button type="button" @click="deliverModal = true"
                                    class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Deliver Devices & Settle Payment</span>
                            </button>
                        @else
                            <div class="py-2 px-3 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-emerald-600 dark:text-emerald-400 text-center text-xs font-bold flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Delivered & Closed</span>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Received Devices List -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider flex items-center gap-2">
                        <span>Received Devices</span>
                        <span class="px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold">{{ $repair->devices->count() }}</span>
                    </h3>
                </div>

                @foreach($repair->devices as $index => $device)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
                        
                        <!-- Top Info Bar -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-bold text-xs flex items-center justify-center">
                                    {{ $index + 1 }}
                                </span>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        <span>{{ $device->brand }} {{ $device->model }}</span>
                                        <span class="text-xs font-mono font-semibold text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">
                                            {{ $device->device_tracking_no }}
                                        </span>
                                    </h4>
                                    <div class="text-xs text-gray-500 mt-0.5 flex flex-wrap items-center gap-3">
                                        @if($device->imei)
                                            <span><strong>IMEI:</strong> {{ $device->imei }}</span>
                                        @endif
                                        @if($device->color)
                                            <span><strong>Color:</strong> {{ $device->color }}</span>
                                        @endif
                                        @if($device->security_type !== 'none')
                                            <span class="text-amber-700 dark:text-amber-400 font-medium">
                                                <strong>Lock:</strong> {{ ucfirst($device->security_type) }} ({{ $device->security_code }})
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Device Status Changer -->
                            <form method="POST" action="{{ route('repairs.devices.status', $device->id) }}" class="flex items-center gap-2">
                                @csrf
                                <select name="status" class="py-1 px-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-medium text-gray-800 dark:text-gray-200">
                                    <option value="pending" {{ $device->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="diagnosing" {{ $device->status === 'diagnosing' ? 'selected' : '' }}>Diagnosing / In Progress</option>
                                    <option value="waiting_parts" {{ $device->status === 'waiting_parts' ? 'selected' : '' }}>Waiting for Parts</option>
                                    <option value="completed" {{ $device->status === 'completed' ? 'selected' : '' }}>Completed / Ready</option>
                                    <option value="delivered" {{ $device->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="unrepairable" {{ $device->status === 'unrepairable' ? 'selected' : '' }}>Unrepairable</option>
                                </select>
                                <button type="submit" class="px-2.5 py-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-semibold text-xs rounded-lg transition">
                                    Update
                                </button>
                            </form>
                        </div>

                        <!-- Problem Description & Condition (Light Soft Panels) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                            <div class="p-3 bg-amber-50/50 dark:bg-amber-950/20 rounded-xl border border-amber-200/50 dark:border-amber-900/30">
                                <span class="font-bold text-amber-900 dark:text-amber-300 uppercase tracking-wider block mb-1">Problem Reported:</span>
                                <p class="text-gray-800 dark:text-gray-200 font-medium">{{ $device->problem_description }}</p>
                            </div>

                            <div class="p-3 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-gray-200/70 dark:border-gray-700/60">
                                <span class="font-bold text-gray-500 uppercase tracking-wider block mb-1">Intake Condition:</span>
                                @if(!empty($device->condition_checklist))
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($device->condition_checklist as $key => $val)
                                            <span class="px-2 py-0.5 rounded bg-gray-200/70 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-[11px]">
                                                {{ ucwords(str_replace('_', ' ', $key)) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400">Normal intake condition</span>
                                @endif
                            </div>
                        </div>

                        <!-- Parts Used & Labor Charges Table -->
                        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-gray-50 dark:bg-gray-900/60 text-gray-500 uppercase text-[10px]">
                                    <tr>
                                        <th class="py-2 px-3">Item / Service</th>
                                        <th class="py-2 px-3 text-center">Qty</th>
                                        <th class="py-2 px-3 text-right">Price</th>
                                        <th class="py-2 px-3 text-right">Total</th>
                                        <th class="py-2 px-3 text-center">Stock Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @forelse($device->parts as $part)
                                        <tr>
                                            <td class="py-2 px-3 font-medium text-gray-900 dark:text-white">
                                                {{ $part->product->name }}
                                            </td>
                                            <td class="py-2 px-3 text-center">{{ $part->quantity }}</td>
                                            <td class="py-2 px-3 text-right">Rs {{ number_format($part->unit_price, 2) }}</td>
                                            <td class="py-2 px-3 text-right font-semibold">Rs {{ number_format($part->subtotal, 2) }}</td>
                                            <td class="py-2 px-3 text-center">
                                                @if($part->is_deducted)
                                                    <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded border border-emerald-200">Deducted</span>
                                                @else
                                                    <span class="text-[10px] font-semibold text-amber-600 bg-amber-50 dark:bg-amber-950/60 px-2 py-0.5 rounded border border-amber-200">Pending Delivery</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-2 px-3 text-gray-400 text-center">No spare parts attached.</td>
                                        </tr>
                                    @endforelse
                                    <!-- Labor Row -->
                                    <tr class="bg-gray-50/40 dark:bg-gray-900/20 font-semibold">
                                        <td colspan="3" class="py-2 px-3 text-gray-600 dark:text-gray-300">Labor / Repair Service Charges:</td>
                                        <td class="py-2 px-3 text-right text-gray-900 dark:text-white">Rs {{ number_format($device->labor_cost, 2) }}</td>
                                        <td></td>
                                    </tr>
                                    <!-- Subtotal Row -->
                                    <tr class="bg-gray-100/60 dark:bg-gray-800/80 font-bold text-xs">
                                        <td colspan="3" class="py-2 px-3 text-gray-900 dark:text-white">Device Total:</td>
                                        <td class="py-2 px-3 text-right text-gray-900 dark:text-white">Rs {{ number_format($device->total_cost, 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                @endforeach
            </div>

        </div>

        <!-- Deliver & Settle Modal -->
        <div x-show="deliverModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-200 dark:border-gray-700 space-y-4" @click.away="deliverModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">&check;</span>
                        Deliver Devices & Receive Payment
                    </h3>
                    <button type="button" @click="deliverModal = false" class="text-gray-400 hover:text-gray-600">
                        &times;
                    </button>
                </div>

                <form method="POST" action="{{ route('repairs.deliver', $repair->id) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">
                            Warehouse for Spare Parts Stock Deduction <span class="text-rose-500">*</span>
                        </label>
                        <select name="default_warehouse_id" required class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white">
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">
                            Amount Paid Now by Customer (Rs) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="any" name="paid_amount" value="{{ $repair->balance_amount }}" required class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-900 dark:text-white">
                        <div class="text-[11px] text-gray-400 mt-1">Remaining balance due: Rs {{ number_format($repair->balance_amount, 2) }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">
                            Payment Method
                        </label>
                        <select name="payment_method" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white">
                            <option value="cash">Cash Counter</option>
                            <option value="bank">Bank Transfer / Online</option>
                            <option value="jazzcash">JazzCash / EasyPaisa</option>
                        </select>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button type="button" @click="deliverModal = false" class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold text-xs rounded-xl">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-sm">
                            Confirm Delivery
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @if(session('auto_print') || request('auto_print'))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const comboUrl = "{{ session('auto_print_combo') ?? route('repairs.print-combo', $repair->id) }}";
                try {
                    window.open(comboUrl, '_blank', 'width=950,height=900');
                } catch (e) {
                    console.error('Auto-print error:', e);
                }
            });
        </script>
    @endif
</x-app-layout>
