<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12" x-data="repairIntakeForm()">
        <form method="POST" action="{{ route('repairs.store') }}" @submit="validateAndSubmit($event)" class="max-w-[1750px] mx-auto px-2 sm:px-6 lg:px-8 space-y-6">
            @csrf

            <!-- Header bar matching purchases.index / create style -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-2 border-b dark:border-gray-700 pb-4">
                <div>
                    <h3 class="text-xl font-black text-gray-800 dark:text-gray-100 tracking-wide uppercase flex items-center gap-2">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>{{ __('Mobile Jama Karein (Receive Mobile Job Sheet)') }}</span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Order Token: <strong class="text-indigo-600 dark:text-indigo-400 font-mono">{{ $orderNo }}</strong> &bull; Multiple devices, live stock spare parts, and Code128 thermal sticker slips.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('repairs.index') }}" class="px-3.5 py-1.5 rounded-xl border border-gray-300 dark:border-gray-600 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        &larr; {{ __('Back to Repairs') }}
                    </a>
                </div>
            </div>

            <!-- Helpful Step-by-Step Instructions Banner -->
            <div class="p-4 bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 rounded-2xl flex flex-col md:flex-row items-start md:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 text-base font-bold shadow-md shadow-indigo-600/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="font-black text-indigo-900 dark:text-indigo-200 text-sm">
                            Hidayat / Quick Guide:
                        </div>
                        <div class="text-indigo-700 dark:text-indigo-300 mt-0.5">
                            1. Customer search karein (Khata / Name / Phone) &bull; 
                            2. Mobile Model aur Masla likhein &bull; 
                            3. Spare Parts search karke select karein (Live stock auto-minus hoga) &bull; 
                            4. Save karein aur Sticker/Receipt print karein.
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 self-end md:self-auto shrink-0 font-bold text-indigo-600 dark:text-indigo-400 bg-white dark:bg-gray-900 px-3 py-1.5 rounded-xl border border-indigo-200 dark:border-indigo-800">
                    <span>Order No:</span>
                    <span class="font-mono">{{ $orderNo }}</span>
                </div>
            </div>

            <!-- Server Validation Errors Display -->
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
                        <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">1</span>
                        Customer & Intake Details (Grahak Ki Maloomat)
                    </h3>
                    <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold">
                        {{ $customers->count() }} Customers Loaded
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Searchable Customer Dropdown (Matches Purchases/Sales pattern) -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Search Customer (Khata List) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative" @click.away="customerOpen = false">
                            <input type="text" x-model="customerSearch" @focus="customerOpen = true"
                                @input="customerOpen = true" @keydown="handleCustomerKey($event)"
                                class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm text-xs font-bold transition"
                                :class="{'border-rose-500 ring-1 ring-rose-500': errors.customerName}"
                                placeholder="Type name or phone to search..." autocomplete="off">

                            <input type="hidden" name="party_id" :value="selectedPartyId">

                            <!-- Search Results Dropdown -->
                            <div x-show="customerOpen && getFilteredCustomers().length > 0" x-cloak
                                class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-2xl max-h-60 rounded-xl py-1 text-xs border border-gray-200 dark:border-gray-700 overflow-auto divide-y dark:divide-gray-700">
                                <div @click="selectCustomer(null)"
                                    class="cursor-pointer select-none relative py-2.5 px-3 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition font-bold text-gray-500 italic">
                                    <span>-- Walk-in / Naya Customer (Type Name Below) --</span>
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
                            Customer Name (Naam) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="customer_name" x-model="customerName" required placeholder="e.g. Muhammad Ali" :class="{'border-rose-500 ring-1 ring-rose-500': errors.customerName}" class="w-full py-2.5 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <span x-show="errors.customerName" class="text-[10px] text-rose-500 font-bold block mt-1">Customer ka naam likhna lazmi hai.</span>
                    </div>

                    <!-- Customer Phone -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Phone / WhatsApp No <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="customer_phone" x-model="customerPhone" required placeholder="0300-1234567" :class="{'border-rose-500 ring-1 ring-rose-500': errors.customerPhone}" class="w-full py-2.5 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <span x-show="errors.customerPhone" class="text-[10px] text-rose-500 font-bold block mt-1">Mobile / WhatsApp number lazmi hai.</span>
                    </div>

                    <!-- Intake Date & Time -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Received Date & Time <span class="text-rose-500">*</span>
                        </label>
                        <input type="datetime-local" name="received_date" value="{{ now()->format('Y-m-d\TH:i') }}" required class="w-full py-2.5 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <!-- Expected Delivery Date -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Expected Delivery Date & Time (Wapsi Ka Time)
                        </label>
                        <input type="datetime-local" name="expected_delivery_date" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Default Warehouse -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            Default Warehouse (Stock Deduction)
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
                        <input type="text" name="notes" placeholder="e.g. Urgent customer, call before replacement" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- 2. Devices Intake Section (Support for Multiple Devices) -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">2</span>
                            Devices To Repair (Jama Kiye Gaye Mobiles)
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Ek ya ek se zyada mobile add karein. Har mobile ka alag barcode sticker print hoga.</p>
                    </div>
                    <button type="button" @click="addDevice()" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Add Another Mobile (Mazeed Phone)</span>
                    </button>
                </div>

                <!-- Devices Cards -->
                <template x-for="(device, dIdx) in devices" :key="device.id">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border-2 border-indigo-100 dark:border-gray-700 p-6 shadow-sm space-y-4 relative">
                        <!-- Device Header Bar -->
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 bg-indigo-600 text-white font-black text-xs rounded-lg uppercase tracking-wider" x-text="'Mobile #' + (dIdx + 1)"></span>
                                <span class="text-xs font-mono font-bold text-gray-400" x-text="'Tracking Slip Token: {{ $orderNo }}-' + (dIdx + 1)"></span>
                            </div>
                            <div class="flex items-center gap-2">
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
                            <!-- Brand -->
                            <div class="md:col-span-3">
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">Brand / Company</label>
                                <input type="text" :name="'devices[' + dIdx + '][brand]'" x-model="device.brand" placeholder="e.g. Samsung, Apple, Infinix" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <!-- Model -->
                            <div class="md:col-span-4">
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">
                                    Model Name <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" :name="'devices[' + dIdx + '][model]'" x-model="device.model" required placeholder="e.g. Galaxy A52, iPhone 11 Pro" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <!-- IMEI / Serial -->
                            <div class="md:col-span-3">
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">IMEI / Serial No</label>
                                <input type="text" :name="'devices[' + dIdx + '][imei]'" x-model="device.imei" placeholder="15-digit IMEI (Optional)" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-mono text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <!-- Color -->
                            <div class="md:col-span-2">
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">Color (Rang)</label>
                                <input type="text" :name="'devices[' + dIdx + '][color]'" x-model="device.color" placeholder="e.g. Black, Blue" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <!-- Fault / Problem Description & Security Code -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                            <!-- Problem Description -->
                            <div class="md:col-span-8">
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">
                                    Customer Stated Problem / Fault (Masla) <span class="text-rose-500">*</span>
                                </label>
                                <div class="space-y-1.5">
                                    <input type="text" :name="'devices[' + dIdx + '][problem_description]'" x-model="device.problem" required placeholder="e.g. Battery timing issue, Touch glass broken, Not charging" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                    <!-- Quick Tags -->
                                    <div class="flex flex-wrap gap-1">
                                        <button type="button" @click="device.problem = (device.problem ? device.problem + ', ' : '') + 'Battery Change'" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded text-[10px] font-medium">+ Battery</button>
                                        <button type="button" @click="device.problem = (device.problem ? device.problem + ', ' : '') + 'Display / Touch Broken'" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded text-[10px] font-medium">+ Glass/LCD</button>
                                        <button type="button" @click="device.problem = (device.problem ? device.problem + ', ' : '') + 'Charging Port / Base'" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded text-[10px] font-medium">+ Charging</button>
                                        <button type="button" @click="device.problem = (device.problem ? device.problem + ', ' : '') + 'No Power / Dead'" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded text-[10px] font-medium">+ Dead / Power</button>
                                        <button type="button" @click="device.problem = (device.problem ? device.problem + ', ' : '') + 'Software / Flash'" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded text-[10px] font-medium">+ Flash/Software</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Screen Security -->
                            <div class="md:col-span-4">
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase mb-1">Screen Lock / PIN</label>
                                <div class="flex items-center gap-2">
                                    <select :name="'devices[' + dIdx + '][security_type]'" x-model="device.securityType" class="py-2 px-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                                        <option value="none">No Lock</option>
                                        <option value="pin">PIN / Pass</option>
                                        <option value="pattern">Pattern</option>
                                    </select>
                                    <input type="text" :name="'devices[' + dIdx + '][security_code]'" x-model="device.securityCode" placeholder="Code (e.g. 1234 or L-Pattern)" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <span class="text-[10px] text-gray-400 block mt-1">Sticker par print hoga taake karigar phone khol sakay.</span>
                            </div>
                        </div>

                        <!-- Physical Condition Checklist -->
                        <div class="p-3 bg-gray-50/70 dark:bg-gray-900/40 rounded-2xl border border-gray-200/80 dark:border-gray-700/80">
                            <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">
                                Device Condition at Intake (Dispute se bachne ke liye tick karein):
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-xs">
                                <label class="inline-flex items-center gap-2 cursor-pointer bg-white dark:bg-gray-800 p-2 rounded-xl border border-gray-100 dark:border-gray-700">
                                    <input type="checkbox" :name="'devices[' + dIdx + '][condition_checklist][power_on]'" value="1" class="rounded text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-gray-700 dark:text-gray-300 font-semibold">Power On</span>
                                </label>
                                <label class="inline-flex items-center gap-2 cursor-pointer bg-white dark:bg-gray-800 p-2 rounded-xl border border-gray-100 dark:border-gray-700">
                                    <input type="checkbox" :name="'devices[' + dIdx + '][condition_checklist][touch_working]'" value="1" class="rounded text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-gray-700 dark:text-gray-300 font-semibold">Touch OK</span>
                                </label>
                                <label class="inline-flex items-center gap-2 cursor-pointer bg-white dark:bg-gray-800 p-2 rounded-xl border border-gray-100 dark:border-gray-700">
                                    <input type="checkbox" :name="'devices[' + dIdx + '][condition_checklist][camera_ok]'" value="1" class="rounded text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-gray-700 dark:text-gray-300 font-semibold">Cameras OK</span>
                                </label>
                                <label class="inline-flex items-center gap-2 cursor-pointer bg-white dark:bg-gray-800 p-2 rounded-xl border border-gray-100 dark:border-gray-700">
                                    <input type="checkbox" :name="'devices[' + dIdx + '][condition_checklist][body_scratch]'" value="1" class="rounded text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-gray-700 dark:text-gray-300 font-semibold">Body Scratched</span>
                                </label>
                                <label class="inline-flex items-center gap-2 cursor-pointer bg-white dark:bg-gray-800 p-2 rounded-xl border border-gray-100 dark:border-gray-700">
                                    <input type="checkbox" :name="'devices[' + dIdx + '][condition_checklist][back_broken]'" value="1" class="rounded text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-gray-700 dark:text-gray-300 font-semibold">Back Broken</span>
                                </label>
                            </div>
                        </div>

                        <!-- 3. Spare Parts Selection with SEARCHABLE INVENTORY DROPDOWN -->
                        <div class="p-4 bg-indigo-50/50 dark:bg-indigo-950/30 rounded-2xl border border-indigo-200/70 dark:border-indigo-900/50 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-black text-indigo-900 dark:text-indigo-300 uppercase tracking-wider flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        <span>Spare Parts from Inventory (Searchable Dropdown jaise Purchase mein hai)</span>
                                    </div>
                                    <span class="text-[10px] text-gray-500">Part search karein (Battery, Glass, LCD). Delivery ke waqt inventory khud ba khud deduct hogi.</span>
                                </div>
                                <button type="button" @click="addPart(device)" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
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
                                                Current Stock
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

                                                <!-- Current Stock -->
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
                                                    Koi spare part shamil nahi kiya gaya. Agar battery, display wagera lagani ho to <strong>"+ Add Spare Part"</strong> dabayein.
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
                                <input type="number" step="any" :name="'devices[' + dIdx + '][labor_cost]'" x-model.number="device.labor" placeholder="0" class="w-full py-2 px-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-bold font-mono text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div class="flex flex-col justify-center items-end text-right">
                                <span class="text-[10px] text-gray-400 uppercase font-bold">This Device Estimated Total</span>
                                <span class="text-lg font-black text-indigo-600 dark:text-indigo-400 font-mono mt-0.5">
                                    Rs <span x-text="calculateDeviceTotal(device)"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- 3. Grand Total & Advance Payment Summary Card (Clean & Light) -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">3</span>
                        Billing Estimate & Advance Payment (Kul Hisaab & Peshgi)
                    </h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                    <!-- Total Spare Parts -->
                    <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700">
                        <div class="text-[10px] text-gray-500 uppercase tracking-wider font-bold">Total Spare Parts</div>
                        <div class="text-lg font-black text-gray-900 dark:text-white mt-1">
                            Rs <span x-text="calculateGrandParts()"></span>
                        </div>
                    </div>

                    <!-- Total Labor -->
                    <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700">
                        <div class="text-[10px] text-gray-500 uppercase tracking-wider font-bold">Total Labor (Mazdoori)</div>
                        <div class="text-lg font-black text-gray-900 dark:text-white mt-1">
                            Rs <span x-text="calculateGrandLabor()"></span>
                        </div>
                    </div>

                    <!-- Grand Total Estimate -->
                    <div class="p-4 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800">
                        <div class="text-[10px] text-indigo-700 dark:text-indigo-300 uppercase tracking-wider font-bold">Grand Total (Kul Bill)</div>
                        <div class="text-xl font-black text-indigo-900 dark:text-indigo-100 mt-1">
                            Rs <span x-text="calculateGrandTotal()"></span>
                        </div>
                    </div>

                    <!-- Advance Received Amount -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">
                            Advance Received (Peshgi Raqam)
                        </label>
                        <input type="number" step="any" name="advance_amount" x-model.number="advanceAmount" placeholder="0" class="w-full py-2.5 px-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-black text-emerald-600 dark:text-emerald-400 focus:ring-2 focus:ring-emerald-400">
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">
                            Payment Method (Peshgi)
                        </label>
                        <select name="payment_method" class="w-full py-2.5 px-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-semibold text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-emerald-400">
                            <option value="cash">Cash Counter</option>
                            <option value="bank">Bank Transfer / Online</option>
                            <option value="jazzcash">JazzCash / EasyPaisa</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700">
                    <div>
                        <div class="text-[10px] text-gray-500 uppercase tracking-wider font-bold">Remaining Balance (Baqaya)</div>
                        <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-0.5">
                            Rs <span x-text="calculateBalance()"></span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <label class="flex items-center gap-2 cursor-pointer bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 px-3 py-2 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 transition select-none border border-gray-200 dark:border-gray-700">
                            <input type="checkbox" name="auto_print" value="1" checked class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <span>🖨️ Auto-Open Slips on Submit</span>
                        </label>
                        <a href="{{ route('repairs.index') }}" class="px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-xl transition">
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Save & Print Slips (Sticker + Customer Copy)</span>
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <script>
        function repairIntakeForm() {
            return {
                customersList: {!! json_encode($customers) !!},
                productsList: {!! json_encode($products) !!},

                // Customer Search State
                customerSearch: '',
                customerOpen: false,
                customerHighlightedIndex: 0,
                selectedPartyId: '',
                customerName: '',
                customerPhone: '',
                customerBalance: null,

                advanceAmount: 0,
                errors: {
                    customerName: false,
                    customerPhone: false
                },
                devices: [
                    {
                        id: Date.now(),
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
                    }
                ],

                formatNumber(val) {
                    return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
                },

                // Filter Customers (Search by name or phone)
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
                        this.customerBalance = c.current_balance;
                    } else {
                        this.selectedPartyId = '';
                        this.customerSearch = '';
                        this.customerName = '';
                        this.customerPhone = '';
                        this.customerBalance = null;
                    }
                    this.errors.customerName = false;
                    this.errors.customerPhone = false;
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
                        id: Date.now() + Math.random(),
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
                    this.errors.customerName = false;
                    this.errors.customerPhone = false;

                    if (!this.customerName.trim()) {
                        this.errors.customerName = true;
                        hasError = true;
                    }
                    if (!this.customerPhone.trim()) {
                        this.errors.customerPhone = true;
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
