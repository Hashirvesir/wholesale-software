<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12" x-data="repairEditForm()">
        <form method="POST" action="{{ route('repairs.update', $repair->id) }}" @submit="validateAndSubmit($event)" class="max-w-[1750px] mx-auto px-2 sm:px-6 lg:px-8 space-y-6">
            @csrf
            @method('PUT')

            <!-- Header bar matching purchases.index / create style -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-2 border-b dark:border-gray-700 pb-4">
                <div>
                    <h3 class="text-xl font-black text-gray-800 dark:text-gray-100 tracking-wide uppercase flex items-center gap-2">
                        <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>{{ __('Edit Repair Order / Parts & Labor Rate Change Karein') }}</span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Editing Order: <strong class="text-indigo-600 dark:text-indigo-400 font-mono">{{ $repair->order_no }}</strong> &bull; Update labor charges, add/remove spare parts, and adjust bill.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('repairs.show', $repair->id) }}" class="px-3.5 py-1.5 rounded-xl border border-gray-300 dark:border-gray-600 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        &larr; {{ __('Back to Order') }}
                    </a>
                </div>
            </div>

            <!-- Instruction Box -->
            <div class="p-4 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-2xl flex items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 text-base font-bold shadow-md shadow-amber-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </div>
                    <div>
                        <div class="font-black text-amber-900 dark:text-amber-200 text-sm">
                            Edit Instructions (Estimate / Rate Tabdeeli):
                        </div>
                        <div class="text-amber-700 dark:text-amber-300 mt-0.5">
                            Yahan se aap kisi bhi mobile ke <strong>Labor / Service Charges</strong> barha ya kam kar sakte hain, naye <strong>Spare Parts (Battery, Glass)</strong> add kar sakte hain, aur Advance update kar sakte hain.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Server Errors Display -->
            @if ($errors->any())
                <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border-l-4 border-rose-500 rounded-r-xl text-rose-800 dark:text-rose-200 text-xs">
                    <div class="font-bold mb-1.5 flex items-center gap-1.5 text-sm text-rose-700 dark:text-rose-300">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Meherbani farma kar yeh khamiyan theek karein:</span>
                    </div>
                    <ul class="list-disc pl-6 space-y-1 font-semibold">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- 1. Customer & Intake Details -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-amber-500 text-white flex items-center justify-center text-xs font-bold">1</span>
                        Customer & Order Information
                    </h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Searchable Customer Dropdown (Matches Purchases/Sales pattern) -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Search Customer (Khata List)
                        </label>
                        <div class="relative" @click.away="customerOpen = false">
                            <input type="text" x-model="customerSearch" @focus="customerOpen = true"
                                @input="customerOpen = true" @keydown="handleCustomerKey($event)"
                                class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm text-xs font-bold transition"
                                placeholder="Type name or phone to search..." autocomplete="off">

                            <input type="hidden" name="party_id" :value="selectedPartyId">

                            <!-- Search Results Dropdown -->
                            <div x-show="customerOpen && getFilteredCustomers().length > 0" x-cloak
                                class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-2xl max-h-60 rounded-xl py-1 text-xs border border-gray-200 dark:border-gray-700 overflow-auto divide-y dark:divide-gray-700">
                                <div @click="selectCustomer(null)"
                                    class="cursor-pointer select-none relative py-2.5 px-3 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition font-bold text-gray-500 italic">
                                    <span>-- Walk-in / Naya Customer (Keep Current Name) --</span>
                                </div>
                                <template x-for="(c, cIdx) in getFilteredCustomers()" :key="c.id">
                                    <div @click="selectCustomer(c)"
                                        class="cursor-pointer select-none relative py-2.5 px-3 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition"
                                        :class="customerHighlightedIndex === cIdx ? 'bg-indigo-50 dark:bg-indigo-950/40' : ''">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="block truncate uppercase font-bold text-gray-900 dark:text-gray-100"
                                                    x-text="c.name"></span>
                                                <span class="text-gray-500 text-[10px]"
                                                    x-text="c.phone || 'No Phone'"></span>
                                            </div>
                                            <span class="font-black whitespace-nowrap ml-2 text-xs"
                                                :class="c.current_balance >= 0 ? 'text-rose-500' : 'text-emerald-500'">
                                                <span>Rs. </span><span x-text="formatNumber(Math.abs(c.current_balance || 0))"></span>
                                                <span class="text-[9px]" x-text="(c.current_balance || 0) >= 0 ? 'Dr' : 'Cr'"></span>
                                            </span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <template x-if="selectedPartyId && getCustomerBalance() !== null">
                                <div class="mt-1 text-[11px] font-bold"
                                    :class="getCustomerBalance() >= 0 ? 'text-rose-500' : 'text-emerald-500'">
                                    Khata Balance: <span>Rs. </span><span x-text="formatNumber(Math.abs(getCustomerBalance()))"></span>
                                    <span x-text="getCustomerBalance() >= 0 ? '(Receivable / Lena hai)' : '(Advance / Dena hai)'"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Customer Name -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Customer Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="customer_name" x-model="customerName" required class="w-full py-2.5 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Customer Phone -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Customer Phone <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="customer_phone" x-model="customerPhone" required class="w-full py-2.5 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Received Date -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Received Date
                        </label>
                        <input type="datetime-local" name="received_date" value="{{ $repair->received_date ? \Carbon\Carbon::parse($repair->received_date)->format('Y-m-d\TH:i') : '' }}" class="w-full py-2.5 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <!-- Expected Delivery Date -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Expected Delivery Date & Time
                        </label>
                        <input type="datetime-local" name="expected_delivery_date" value="{{ $repair->expected_delivery_date ? \Carbon\Carbon::parse($repair->expected_delivery_date)->format('Y-m-d\TH:i') : '' }}" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Default Warehouse -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Warehouse for Stock Deduction
                        </label>
                        <select name="default_warehouse_id" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Order General Note / Special Instructions
                        </label>
                        <input type="text" name="notes" value="{{ $repair->notes }}" placeholder="e.g. Rate negotiated with customer" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- 2. Devices, Parts & Labor Edit Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-amber-500 text-white flex items-center justify-center text-xs font-bold">2</span>
                            Devices & Parts Under Repair (Rates & Charges Tabdeel Karein)
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Labor Charges change karein ya naye Spare Parts select karein.</p>
                    </div>
                    <button type="button" @click="addDevice()" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Add Another Mobile</span>
                    </button>
                </div>

                <template x-for="(device, dIdx) in devices" :key="device.id || device.tempId">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border-2 border-amber-200 dark:border-gray-700 p-6 shadow-sm space-y-4 relative">
                        <input type="hidden" :name="'devices[' + dIdx + '][id]'" :value="device.id">

                        <!-- Device Header Bar -->
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 bg-amber-500 text-white font-black text-xs rounded-lg uppercase tracking-wider" x-text="'Mobile #' + (dIdx + 1)"></span>
                                <span class="text-xs font-mono font-bold text-gray-500" x-text="device.trackingNo || 'New Device'"></span>
                            </div>
                            <div>
                                <template x-if="devices.length > 1">
                                    <button type="button" @click="removeDevice(dIdx)" class="px-2.5 py-1 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg text-xs font-bold transition flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Remove Mobile</span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Device Primary Fields -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3">
                            <div class="md:col-span-3">
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">Brand</label>
                                <input type="text" :name="'devices[' + dIdx + '][brand]'" x-model="device.brand" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">Model Name *</label>
                                <input type="text" :name="'devices[' + dIdx + '][model]'" x-model="device.model" required class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">IMEI</label>
                                <input type="text" :name="'devices[' + dIdx + '][imei]'" x-model="device.imei" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-mono text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">Color</label>
                                <input type="text" :name="'devices[' + dIdx + '][color]'" x-model="device.color" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <!-- Problem & Security -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                            <div class="md:col-span-8">
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">Fault / Stated Problem *</label>
                                <input type="text" :name="'devices[' + dIdx + '][problem_description]'" x-model="device.problem" required class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">Screen Lock / PIN</label>
                                <div class="flex items-center gap-2">
                                    <select :name="'devices[' + dIdx + '][security_type]'" x-model="device.securityType" class="py-2 px-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                                        <option value="none">No Lock</option>
                                        <option value="pin">PIN / Pass</option>
                                        <option value="pattern">Pattern</option>
                                    </select>
                                    <input type="text" :name="'devices[' + dIdx + '][security_code]'" x-model="device.securityCode" placeholder="Code" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>

                        <!-- Spare Parts with SEARCHABLE DROPDOWN -->
                        <div class="p-4 bg-amber-50/50 dark:bg-amber-950/20 rounded-2xl border border-amber-200/70 dark:border-amber-900/40 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-black text-amber-900 dark:text-amber-300 uppercase tracking-wider flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        <span>Spare Parts from Inventory (Searchable Dropdown jaise Purchase mein hai)</span>
                                    </div>
                                    <span class="text-[10px] text-gray-500">Part search karke rate aur quantity adjust karein.</span>
                                </div>
                                <button type="button" @click="addPart(device)" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>+ Add Spare Part</span>
                                </button>
                            </div>

                            <!-- Parts Table -->
                            <div class="overflow-visible">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border dark:border-gray-700 rounded-xl">
                                    <thead class="bg-gray-100 dark:bg-gray-800">
                                        <tr>
                                            <th class="px-3 py-2.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/2">
                                                Spare Part (Search Name / SKU / Barcode)
                                            </th>
                                            <th class="px-3 py-2.5 text-center text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-28">
                                                Stock
                                            </th>
                                            <th class="px-3 py-2.5 text-center text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-24">
                                                Qty
                                            </th>
                                            <th class="px-3 py-2.5 text-right text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-36">
                                                Rate (Rs)
                                            </th>
                                            <th class="px-3 py-2.5 text-right text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-36">
                                                Subtotal (Rs)
                                            </th>
                                            <th class="px-3 py-2.5 text-center text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-12">
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <template x-for="(part, pIdx) in device.parts" :key="pIdx">
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition">
                                                <input type="hidden" :name="'devices[' + dIdx + '][parts][' + pIdx + '][id]'" :value="part.id">

                                                <!-- Searchable Product Input -->
                                                <td class="px-2 py-2 relative" @click.away="part.open = false">
                                                    <input type="text" x-model="part.search" @focus="part.open = true"
                                                        @input="part.open = true" @keydown="handlePartKey($event, part)"
                                                        class="block w-full text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-bold"
                                                        placeholder="Type to search part by name, SKU, or barcode..." autocomplete="off">

                                                    <input type="hidden" :name="'devices[' + dIdx + '][parts][' + pIdx + '][product_id]'"
                                                        :value="part.productId">

                                                    <!-- Product Results Dropdown -->
                                                    <div x-show="part.open && getFilteredProducts(part.search).length > 0" x-cloak
                                                        class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-2xl max-h-60 rounded-xl py-1 text-xs border border-gray-200 dark:border-gray-700 overflow-auto divide-y dark:divide-gray-700">
                                                        <template x-for="(prod, pIdx2) in getFilteredProducts(part.search)" :key="prod.id">
                                                            <div @click="selectProductForPart(part, prod)"
                                                                class="cursor-pointer select-none relative py-2 px-3 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition"
                                                                :class="part.highlightedIndex === pIdx2 ? 'bg-indigo-50 dark:bg-indigo-950/40' : ''">
                                                                <div class="flex items-center justify-between">
                                                                    <div>
                                                                        <span class="block truncate uppercase font-bold text-gray-900 dark:text-gray-100"
                                                                            x-text="prod.name"></span>
                                                                        <div class="flex items-center gap-2 mt-0.5">
                                                                            <span class="text-gray-500 dark:text-gray-400 text-[9px] font-mono tracking-tighter"
                                                                                x-text="'SKU: ' + prod.sku"></span>
                                                                            <template x-if="prod.barcode">
                                                                                <span class="text-gray-500 dark:text-gray-400 text-[9px] font-mono tracking-tighter"
                                                                                    x-text="'| BAR: ' + prod.barcode"></span>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-right ml-2 shrink-0">
                                                                        <span class="block font-black text-gray-900 dark:text-gray-100 text-xs"
                                                                            x-text="'Rs. ' + formatNumber(prod.sale_price)"></span>
                                                                        <span class="font-bold text-[10px] px-1.5 py-0.5 rounded"
                                                                            :class="prod.total_stock > 0 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400'"
                                                                            x-text="'Stock: ' + prod.total_stock"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </td>

                                                <!-- Stock -->
                                                <td class="px-2 py-2 text-center text-xs font-black">
                                                    <span :class="part.stock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500'"
                                                        x-text="part.stock !== null ? part.stock : '--'"></span>
                                                </td>

                                                <!-- Quantity -->
                                                <td class="px-2 py-2">
                                                    <input type="number" min="1" :name="'devices[' + dIdx + '][parts][' + pIdx + '][quantity]'"
                                                        x-model.number="part.qty"
                                                        class="block w-full text-xs text-center border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-bold"
                                                        required>
                                                </td>

                                                <!-- Rate -->
                                                <td class="px-2 py-2">
                                                    <input type="number" step="any" :name="'devices[' + dIdx + '][parts][' + pIdx + '][unit_price]'"
                                                        x-model.number="part.price"
                                                        class="block w-full text-xs text-right border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-bold font-mono"
                                                        required>
                                                </td>

                                                <!-- Subtotal -->
                                                <td class="px-2 py-2 text-right text-xs font-black font-mono text-gray-900 dark:text-gray-100">
                                                    Rs. <span x-text="formatNumber((part.qty || 0) * (part.price || 0))"></span>
                                                </td>

                                                <!-- Remove Row -->
                                                <td class="px-2 py-2 text-center">
                                                    <button type="button" @click="removePart(device, pIdx)"
                                                        class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition"
                                                        title="Remove part">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="device.parts.length === 0">
                                            <tr>
                                                <td colspan="6" class="py-4 text-center text-xs text-gray-400 italic">
                                                    Koi spare part shamil nahi kiya gaya. Agar naya part lagana ho to <strong>"+ Add Spare Part"</strong> dabayein.
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Device Labor Charges & Summary -->
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-200 dark:border-gray-700">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">
                                    Assigned Technician (Karigar)
                                </label>
                                <select :name="'devices[' + dIdx + '][technician_id]'" x-model="device.technicianId" class="w-full py-2 px-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                                    <option value="">-- Assign Later --</option>
                                    @foreach($technicians as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">
                                    Repair Warranty (Days)
                                </label>
                                <input type="number" :name="'devices[' + dIdx + '][warranty_days]'" x-model.number="device.warrantyDays" placeholder="7" class="w-full py-2 px-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-bold text-gray-900 dark:text-white">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">
                                    Labor / Service Charges (Mazdoori)
                                </label>
                                <input type="number" step="any" :name="'devices[' + dIdx + '][labor_cost]'" x-model.number="device.labor" class="w-full py-2 px-3 bg-white dark:bg-gray-800 border border-amber-300 dark:border-amber-600 rounded-xl text-xs font-bold font-mono text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div class="flex flex-col justify-center items-end text-right">
                                <span class="text-[10px] text-gray-400 uppercase font-bold">This Device Estimated Total</span>
                                <span class="text-lg font-black text-amber-600 dark:text-amber-400 font-mono mt-0.5">
                                    Rs <span x-text="calculateDeviceTotal(device)"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- 4. Grand Total & Advance Payment Summary Card -->
            <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white rounded-3xl p-6 shadow-xl border border-indigo-900 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-white/10">
                    <h3 class="text-sm font-black uppercase tracking-wider flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-xs font-bold">3</span>
                        Updated Billing Estimate & Advance
                    </h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Total Spare Parts</div>
                        <div class="text-xl font-black text-indigo-300 mt-1">
                            Rs <span x-text="calculateGrandParts()"></span>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Total Labor (Mazdoori)</div>
                        <div class="text-xl font-black text-indigo-300 mt-1">
                            Rs <span x-text="calculateGrandLabor()"></span>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Grand Total (Kul Bill)</div>
                        <div class="text-2xl font-black text-white mt-1">
                            Rs <span x-text="calculateGrandTotal()"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                            Advance Received (Peshgi)
                        </label>
                        <input type="number" step="any" name="advance_amount" x-model.number="advanceAmount" class="w-full py-2.5 px-3 bg-slate-800/90 border border-indigo-400/40 rounded-xl text-sm font-black text-emerald-400 focus:ring-2 focus:ring-emerald-400">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">
                            Payment Method
                        </label>
                        <select name="payment_method" class="w-full py-2.5 px-3 bg-slate-800/90 border border-indigo-400/40 rounded-xl text-sm font-semibold text-white focus:ring-2 focus:ring-emerald-400">
                            <option value="cash" {{ $repair->payment_method === 'cash' ? 'selected' : '' }}>Cash Counter</option>
                            <option value="bank" {{ $repair->payment_method === 'bank' ? 'selected' : '' }}>Bank Transfer / Online</option>
                            <option value="jazzcash" {{ $repair->payment_method === 'jazzcash' ? 'selected' : '' }}>JazzCash / EasyPaisa</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 rounded-2xl bg-white/5 border border-white/10">
                    <div>
                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Remaining Balance (Baqaya)</div>
                        <div class="text-2xl font-black text-rose-400 mt-0.5">
                            Rs <span x-text="calculateBalance()"></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('repairs.show', $repair->id) }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs rounded-xl transition">
                            Cancel
                        </a>
                        <button type="submit" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-black text-sm rounded-xl shadow-lg shadow-emerald-600/50 transition transform active:scale-95 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Update Order & Recalculate Bill</span>
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <script>
        function repairEditForm() {
            const initialDevices = {!! json_encode($initialDevices ?? []) !!};

            return {
                customersList: {!! json_encode($customers) !!},
                productsList: {!! json_encode($products) !!},

                // Customer Search State
                customerSearch: '{{ addslashes($repair->customer_name) }}',
                customerOpen: false,
                customerHighlightedIndex: 0,
                selectedPartyId: '{{ $repair->party_id }}',
                customerName: '{{ addslashes($repair->customer_name) }}',
                customerPhone: '{{ addslashes($repair->customer_phone) }}',

                advanceAmount: {{ (float) $repair->advance_amount }},
                devices: initialDevices.length ? initialDevices : [],

                formatNumber(val) {
                    return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
                },

                // Filter Customers
                getFilteredCustomers() {
                    const q = (this.customerSearch || '').trim().toLowerCase();
                    if (!q) return this.customersList.slice(0, 20);
                    return this.customersList.filter(c => {
                        return (c.name && c.name.toLowerCase().includes(q)) ||
                               (c.phone && c.phone.toLowerCase().includes(q));
                    }).slice(0, 20);
                },

                selectCustomer(c) {
                    if (c) {
                        this.selectedPartyId = c.id;
                        this.customerSearch = c.name;
                        this.customerName = c.name;
                        this.customerPhone = c.phone || '';
                    } else {
                        this.selectedPartyId = '';
                    }
                    this.customerOpen = false;
                    this.customerHighlightedIndex = 0;
                },

                getCustomerBalance() {
                    if (!this.selectedPartyId) return null;
                    const c = this.customersList.find(x => x.id == this.selectedPartyId);
                    return c ? c.current_balance : null;
                },

                handleCustomerKey(e) {
                    const filtered = this.getFilteredCustomers();
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (this.customerHighlightedIndex < filtered.length - 1) {
                            this.customerHighlightedIndex++;
                        }
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (this.customerHighlightedIndex > 0) {
                            this.customerHighlightedIndex--;
                        }
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (this.customerOpen && filtered.length > 0) {
                            this.selectCustomer(filtered[this.customerHighlightedIndex]);
                        }
                    } else if (e.key === 'Escape') {
                        this.customerOpen = false;
                    }
                },

                // Product Search Methods
                getFilteredProducts(searchQuery) {
                    const q = (searchQuery || '').trim().toLowerCase();
                    if (!q) return this.productsList.slice(0, 20);
                    return this.productsList.filter(p => {
                        return (p.name && p.name.toLowerCase().includes(q)) ||
                               (p.sku && p.sku.toLowerCase().includes(q)) ||
                               (p.barcode && p.barcode.toLowerCase().includes(q));
                    }).slice(0, 20);
                },

                selectProductForPart(part, prod) {
                    part.productId = prod.id;
                    part.search = prod.name;
                    part.price = parseFloat(prod.sale_price) || 0;
                    part.stock = prod.total_stock;
                    part.open = false;
                    part.highlightedIndex = 0;
                },

                handlePartKey(e, part) {
                    const filtered = this.getFilteredProducts(part.search);
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (part.highlightedIndex < filtered.length - 1) {
                            part.highlightedIndex++;
                        }
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (part.highlightedIndex > 0) {
                            part.highlightedIndex--;
                        }
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (part.open && filtered.length > 0) {
                            this.selectProductForPart(part, filtered[part.highlightedIndex]);
                        }
                    } else if (e.key === 'Escape') {
                        part.open = false;
                    }
                },

                addDevice() {
                    this.devices.push({
                        tempId: Date.now() + Math.random(),
                        id: null,
                        trackingNo: '',
                        brand: '',
                        model: '',
                        imei: '',
                        color: '',
                        problem: '',
                        securityType: 'none',
                        securityCode: '',
                        labor: 0,
                        technicianId: '',
                        warrantyDays: 7,
                        parts: []
                    });
                },

                removeDevice(index) {
                    if (this.devices.length > 1) {
                        this.devices.splice(index, 1);
                    }
                },

                addPart(device) {
                    device.parts.push({
                        id: null,
                        productId: '',
                        search: '',
                        open: false,
                        highlightedIndex: 0,
                        qty: 1,
                        price: 0,
                        stock: null
                    });
                },

                removePart(device, partIndex) {
                    device.parts.splice(partIndex, 1);
                },

                calculateDeviceParts(device) {
                    return device.parts.reduce((sum, p) => sum + ((p.qty || 0) * (p.price || 0)), 0);
                },

                calculateDeviceTotal(device) {
                    const parts = this.calculateDeviceParts(device);
                    const labor = parseFloat(device.labor) || 0;
                    return (parts + labor).toFixed(2);
                },

                calculateGrandParts() {
                    return this.devices.reduce((sum, d) => sum + this.calculateDeviceParts(d), 0).toFixed(2);
                },

                calculateGrandLabor() {
                    return this.devices.reduce((sum, d) => sum + (parseFloat(d.labor) || 0), 0).toFixed(2);
                },

                calculateGrandTotal() {
                    const parts = parseFloat(this.calculateGrandParts()) || 0;
                    const labor = parseFloat(this.calculateGrandLabor()) || 0;
                    return (parts + labor).toFixed(2);
                },

                calculateBalance() {
                    const grand = parseFloat(this.calculateGrandTotal()) || 0;
                    const adv = parseFloat(this.advanceAmount) || 0;
                    return Math.max(0, grand - adv).toFixed(2);
                },

                validateAndSubmit(e) {
                    let hasError = false;

                    if (!this.customerName.trim()) {
                        alert('Customer Name is required!');
                        hasError = true;
                    }
                    if (!this.customerPhone.trim()) {
                        alert('Customer Phone number is required!');
                        hasError = true;
                    }

                    for (let i = 0; i < this.devices.length; i++) {
                        if (!this.devices[i].model || !this.devices[i].model.trim()) {
                            alert(`Mobile #${i + 1} ka Model Name likhna lazmi hai!`);
                            hasError = true;
                            break;
                        }
                        if (!this.devices[i].problem || !this.devices[i].problem.trim()) {
                            alert(`Mobile #${i + 1} ka Masla / Fault likhna lazmi hai!`);
                            hasError = true;
                            break;
                        }
                    }

                    if (hasError) {
                        e.preventDefault();
                    }
                }
            }
        }
    </script>
</x-app-layout>
