<x-app-layout>
    <div class="py-4 sm:py-8 lg:py-12" x-data="repairIntakeForm()">
        <form method="POST" action="{{ route('repairs.store') }}" @submit="validateAndSubmit($event)" class="max-w-[1750px] mx-auto px-2 sm:px-6 lg:px-8 space-y-8">
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
                    <a href="{{ route('repairs.catalog') }}" target="_blank" class="px-3.5 py-1.5 rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-950/60 text-xs font-bold text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Manage Catalog & Services</span>
                    </a>
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

            <!-- Server & Exception Error Display -->
            @if(session('error'))
                <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border-l-4 border-rose-500 rounded-r-xl text-rose-800 dark:text-rose-200 text-xs">
                    <div class="font-bold flex items-center gap-2 text-sm text-rose-700 dark:text-rose-300">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

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

            <!-- Client-Side Real-Time Error Summary Banner -->
            <div id="formErrorSummary" x-show="formErrors.length > 0" x-cloak class="p-4 bg-rose-50 dark:bg-rose-950/50 border-2 border-rose-400 dark:border-rose-800 rounded-2xl text-rose-900 dark:text-rose-200 text-xs shadow-md space-y-2">
                <div class="font-black flex items-center justify-between text-sm text-rose-700 dark:text-rose-300">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Form mein darj zail khamiyan theek karein:</span>
                    </div>
                    <button type="button" @click="formErrors = []" class="text-rose-500 hover:text-rose-700 font-black text-sm">&times;</button>
                </div>
                <ul class="list-disc pl-6 space-y-1 font-bold">
                    <template x-for="(err, idx) in formErrors" :key="idx">
                        <li x-text="err"></li>
                    </template>
                </ul>
            </div>

            <!-- 1. Customer & Intake Details -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 sm:p-8 border border-gray-200 dark:border-gray-700 shadow-sm space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xs font-black shadow-xs">1</span>
                        Customer & Intake Details (Grahak Ki Maloomat)
                    </h3>
                    <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1 rounded-lg border border-emerald-200 dark:border-emerald-800">
                        {{ $customers->count() }} Customers Loaded
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
                    <!-- Searchable Customer Dropdown (Matches Purchases/Sales pattern) -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-2">
                            Search Customer (Khata List) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative" @click.away="customerOpen = false">
                            <input type="text" x-model="customerSearch" @focus="customerOpen = true"
                                @input="customerOpen = true" @keydown="handleCustomerKey($event)"
                                class="block w-full py-2.5 sm:py-3 px-3.5 border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm text-xs sm:text-sm font-bold transition"
                                :class="{'border-rose-500 ring-1 ring-rose-500': errors.customerName}"
                                placeholder="Type name or phone to search..." autocomplete="off">

                            <input type="hidden" name="party_id" :value="selectedPartyId">

                            <!-- Search Results Dropdown -->
                            <div x-show="customerOpen && getFilteredCustomers().length > 0" x-cloak
                                class="absolute z-50 mt-1.5 w-full bg-white dark:bg-gray-800 shadow-2xl max-h-60 rounded-xl py-1 text-xs border border-gray-200 dark:border-gray-700 overflow-auto divide-y dark:divide-gray-700">
                                <div @click="selectCustomer(null)"
                                    class="cursor-pointer select-none relative py-2.5 px-3 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition font-bold text-gray-500 italic">
                                    <span>-- Walk-in / Naya Customer (Type Name Below) --</span>
                                </div>
                                <template x-for="(c, cIdx) in getFilteredCustomers()" :key="c.id">
                                    <div @click="selectCustomer(c)"
                                        class="cursor-pointer select-none relative py-2.5 px-3.5 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition"
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
                                <div class="mt-1.5 text-xs font-bold"
                                    :class="getCustomerBalance() >= 0 ? 'text-rose-500' : 'text-emerald-500'">
                                    Khata Balance: <span>Rs. </span><span x-text="formatNumber(Math.abs(getCustomerBalance()))"></span>
                                    <span x-text="getCustomerBalance() >= 0 ? '(Receivable / Lena hai)' : '(Advance / Dena hai)'"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Customer Name -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-2">
                            Customer Name (Naam) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="customer_name" x-model="customerName"
                               @input="errors.customerName = false; removeFromErrors('Customer ka naam')"
                               placeholder="e.g. Muhammad Ali"
                               :class="errors.customerName ? 'border-rose-500 ring-2 ring-rose-400 bg-rose-50/40 dark:bg-rose-950/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900'"
                               class="w-full py-2.5 sm:py-3 px-3.5 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 transition">
                        <span x-show="errors.customerName" x-cloak class="text-[11px] text-rose-500 font-bold block mt-1.5">⚠️ Customer ka naam likhna lazmi hai.</span>
                    </div>

                    <!-- Customer Phone -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-2">
                            Phone / WhatsApp No <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="customer_phone" x-model="customerPhone"
                               @input="errors.customerPhone = false; removeFromErrors('phone number')"
                               placeholder="0300-1234567"
                               :class="errors.customerPhone ? 'border-rose-500 ring-2 ring-rose-400 bg-rose-50/40 dark:bg-rose-950/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900'"
                               class="w-full py-2.5 sm:py-3 px-3.5 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 transition">
                        <span x-show="errors.customerPhone" x-cloak class="text-[11px] text-rose-500 font-bold block mt-1.5">⚠️ Valid mobile / WhatsApp number lazmi hai (min 7 digits).</span>
                    </div>

                    <!-- Intake Date & Time -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-2">
                            Received Date & Time <span class="text-rose-500">*</span>
                        </label>
                        <input type="datetime-local" name="received_date" value="{{ now()->format('Y-m-d\TH:i') }}" required class="w-full py-2.5 sm:py-3 px-3.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 sm:gap-6 pt-2">
                    <!-- Expected Delivery Date -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-2">
                            Expected Delivery Date & Time (Wapsi Ka Time)
                        </label>
                        <input type="datetime-local" name="expected_delivery_date" class="w-full py-2.5 sm:py-3 px-3.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Default Warehouse -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-2">
                            Default Warehouse (Stock Deduction)
                        </label>
                        <select name="default_warehouse_id" class="w-full py-2.5 sm:py-3 px-3.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-2">
                            Order General Note / Special Instructions
                        </label>
                        <input type="text" name="notes" placeholder="e.g. Urgent customer, call before replacement" class="w-full py-2.5 sm:py-3 px-3.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- 2. Devices Intake Section (Support for Multiple Devices) -->
            <div class="space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-1">
                    <div>
                        <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xs font-black shadow-xs">2</span>
                            Devices To Repair (Jama Kiye Gaye Mobiles)
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">Ek ya ek se zyada mobile add karein. Har mobile ka alag barcode sticker print hoga.</p>
                    </div>
                    <button type="button" @click="addDevice()" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md transition flex items-center gap-2 active:scale-95 self-start sm:self-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Add Another Mobile (Mazeed Phone)</span>
                    </button>
                </div>

                <!-- Devices Cards -->
                <template x-for="(device, dIdx) in devices" :key="device.id">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border-2 border-indigo-100 dark:border-gray-700 p-6 sm:p-8 shadow-sm space-y-6 relative">
                        <!-- Device Header Bar -->
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1.5 bg-indigo-600 text-white font-black text-xs rounded-xl uppercase tracking-wider shadow-2xs" x-text="'Mobile #' + (dIdx + 1)"></span>
                                <span class="text-xs font-mono font-bold text-gray-400" x-text="'Tracking Slip Token: {{ $orderNo }}-' + (dIdx + 1)"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <template x-if="devices.length > 1">
                                    <button type="button" @click="removeDevice(dIdx)" class="px-3 py-1.5 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-rose-200 dark:border-rose-900/50">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Remove Mobile</span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Device Primary Fields: Brand & Model Dropdowns -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-5 sm:gap-6">
                            <!-- Searchable Brand Dropdown -->
                            <div class="lg:col-span-3 relative" @click.away="device.brandOpen = false">
                                <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-2">
                                    Brand / Company
                                </label>
                                <div class="relative">
                                    <input type="text"
                                           :name="'devices[' + dIdx + '][brand]'"
                                           x-model="device.brand"
                                           @focus="device.brandOpen = true"
                                           @input="device.brandOpen = true; device.selectedBrandId = null"
                                           placeholder="Type or select Brand..."
                                           autocomplete="off"
                                           class="w-full py-2.5 sm:py-3 px-3.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs sm:text-sm font-semibold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                    
                                    <!-- Brand Dropdown Menu -->
                                    <div x-show="device.brandOpen"
                                         class="absolute left-0 right-0 mt-1.5 max-h-56 overflow-y-auto bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 z-50 text-xs"
                                         style="display: none;">
                                        <template x-for="b in getFilteredBrands(device)" :key="b.id">
                                            <div @click="selectBrand(device, b)"
                                                 class="px-3.5 py-2.5 hover:bg-indigo-50 dark:hover:bg-gray-700 cursor-pointer flex items-center justify-between border-b border-gray-50 dark:border-gray-700/50">
                                                <span class="font-bold text-gray-800 dark:text-gray-200 text-xs sm:text-sm" x-text="b.name"></span>
                                                <span class="text-[10px] text-gray-400 font-mono" x-text="(b.models ? b.models.length : 0) + ' models'"></span>
                                            </div>
                                        </template>
                                        <!-- Add Brand if not found -->
                                        <div x-show="device.brand && !brandsList.some(b => b.name.toLowerCase() === device.brand.trim().toLowerCase())"
                                             @click="addNewBrand(device, device.brand)"
                                             class="px-3.5 py-3 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-bold cursor-pointer text-xs flex items-center gap-2 border-t border-indigo-100">
                                            <span>➕ Add "</span><span x-text="device.brand"></span><span>" as New Brand</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cascading Searchable Model Dropdown -->
                            <div class="lg:col-span-4 relative" @click.away="device.modelOpen = false">
                                <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-2">
                                    Model Name <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text"
                                           :name="'devices[' + dIdx + '][model]'"
                                           x-model="device.model"
                                           @focus="device.modelOpen = true"
                                           @input="device.modelOpen = true; if(device.errors) device.errors.model = false"
                                           placeholder="Select or type Model..."
                                           autocomplete="off"
                                           :class="device.errors && device.errors.model ? 'border-rose-500 ring-2 ring-rose-400 bg-rose-50/40 dark:bg-rose-950/20' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900'"
                                           class="w-full py-2.5 sm:py-3 px-3.5 rounded-xl text-xs sm:text-sm font-semibold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 transition">
                                    <span x-show="device.errors && device.errors.model" x-cloak class="text-[11px] text-rose-500 font-bold block mt-1.5">⚠️ Mobile model likhna lazmi hai.</span>
                                    
                                    <!-- Model Dropdown Menu -->
                                    <div x-show="device.modelOpen"
                                         class="absolute left-0 right-0 mt-1.5 max-h-60 overflow-y-auto bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 z-50 text-xs"
                                         style="display: none;">
                                        <template x-for="m in getFilteredModels(device)" :key="m.id">
                                            <div @click="selectModel(device, m)"
                                                 class="px-3.5 py-2.5 hover:bg-indigo-50 dark:hover:bg-gray-700 cursor-pointer flex items-center justify-between border-b border-gray-50 dark:border-gray-700/50">
                                                <span class="font-bold text-gray-800 dark:text-gray-200 text-xs sm:text-sm" x-text="m.name"></span>
                                                <span class="text-[10px] text-gray-400 font-normal" x-text="m.series || ''"></span>
                                            </div>
                                        </template>
                                        <!-- Add Model if not found -->
                                        <div x-show="device.model && !getFilteredModels(device).some(m => m.name.toLowerCase() === device.model.trim().toLowerCase())"
                                             @click="addNewModel(device, device.model)"
                                             class="px-3.5 py-3 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-bold cursor-pointer text-xs flex items-center gap-2 border-t border-indigo-100">
                                            <span>➕ Add "</span><span x-text="device.model"></span><span>" to Model List</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- IMEI / Serial -->
                            <div class="lg:col-span-3">
                                <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-2">IMEI / Serial No</label>
                                <input type="text" :name="'devices[' + dIdx + '][imei]'" x-model="device.imei" placeholder="15-digit IMEI (Optional)" class="w-full py-2.5 sm:py-3 px-3.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs sm:text-sm font-mono text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <!-- Color -->
                            <div class="lg:col-span-2">
                                <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-2">Color (Rang)</label>
                                <input type="text" :name="'devices[' + dIdx + '][color]'" x-model="device.color" placeholder="e.g. Black, Blue" class="w-full py-2.5 sm:py-3 px-3.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <!-- Predefined Services & Faults Multi-Select Section -->
                        <div class="p-5 sm:p-6 bg-slate-50/80 dark:bg-gray-900/50 rounded-2xl border border-slate-200/90 dark:border-gray-700 space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <div class="text-xs sm:text-sm font-black text-gray-800 dark:text-gray-200 uppercase tracking-wider flex items-center gap-2">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>Select Services & Faults (Multiple Services Select Karein)</span>
                                    </div>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 block">Service par click karein, rate khud fill ho jayega. Aap rate apni marzi se kam ya zyada bhi kar sakte hain.</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="openQuickAddServiceModal(device)" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-bold text-xs rounded-xl transition flex items-center gap-1.5 border border-indigo-200 dark:border-indigo-800">
                                        <span>➕ Add New Service</span>
                                    </button>
                                    <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-white dark:bg-gray-800 px-2.5 py-1 rounded-lg border border-indigo-100 dark:border-indigo-900">
                                        Selected: <span x-text="device.services ? device.services.length : 0"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Clickable Services Badges Grid with better gaps and padding -->
                            <div class="flex flex-wrap gap-2 sm:gap-2.5 pt-1.5">
                                <template x-for="srv in servicesList" :key="srv.id">
                                    <button type="button"
                                            @click="toggleService(device, srv)"
                                            :class="isServiceSelected(device, srv.name) 
                                                ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' 
                                                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-100'"
                                            class="px-3.5 py-2 rounded-xl border text-xs font-semibold transition flex items-center gap-2 cursor-pointer shadow-2xs hover:shadow-xs active:scale-95">
                                        <span x-show="isServiceSelected(device, srv.name)" class="font-bold">✓</span>
                                        <span x-text="srv.name"></span>
                                        <span class="opacity-75 font-mono text-[11px]" x-text="'(Rs ' + Number(srv.default_labor_cost).toFixed(0) + ')'"></span>
                                    </button>
                                </template>
                            </div>

                            <!-- Selected Services Table with Editable Rates -->
                            <div x-show="device.services && device.services.length > 0" class="pt-3">
                                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-2xs">
                                    <table class="min-w-full text-xs sm:text-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-900/60 text-gray-500 uppercase text-[10px] tracking-wider">
                                            <tr>
                                                <th class="py-2.5 px-4 text-left">Selected Service / Work</th>
                                                <th class="py-2.5 px-4 text-right w-52">Service Rate / Mazdoori (Editable)</th>
                                                <th class="py-2.5 px-3 text-center w-14">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                            <template x-for="(s, sIdx) in device.services" :key="sIdx">
                                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750 transition">
                                                    <td class="py-2.5 px-4 font-semibold text-gray-800 dark:text-gray-200">
                                                        <span x-text="s.name"></span>
                                                        <input type="hidden" :name="'devices[' + dIdx + '][services][' + sIdx + '][name]'" :value="s.name">
                                                    </td>
                                                    <td class="py-2 px-4 text-right">
                                                        <div class="flex items-center justify-end gap-1.5">
                                                            <span class="text-gray-400 font-bold text-xs">Rs</span>
                                                            <input type="number" step="any"
                                                                   :name="'devices[' + dIdx + '][services][' + sIdx + '][rate]'"
                                                                   x-model.number="s.rate"
                                                                   @input="recalculateDeviceServices(device)"
                                                                   class="w-28 py-1.5 px-2.5 text-right bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl font-black text-xs sm:text-sm text-indigo-600 dark:text-indigo-400 focus:ring-2 focus:ring-indigo-500">
                                                        </div>
                                                    </td>
                                                    <td class="py-2 px-3 text-center">
                                                        <button type="button" @click="removeService(device, sIdx)" class="text-rose-500 hover:text-rose-700 p-1 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg font-bold text-base transition" title="Remove Service">
                                                            &times;
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Problem Description (Auto-fills from services or custom text) & Screen Security -->
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">
                            <!-- Problem Description -->
                            <div class="lg:col-span-8">
                                <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-2">
                                    Problem / Fault Summary (Print Slip par yeh aayega) <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" :name="'devices[' + dIdx + '][problem_description]'"
                                       x-model="device.problem"
                                       @input="if(device.errors) device.errors.problem = false"
                                       placeholder="Select services above or type fault details..."
                                       :class="device.errors && device.errors.problem ? 'border-rose-500 ring-2 ring-rose-400 bg-rose-50/40 dark:bg-rose-950/20' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900'"
                                       class="w-full py-2.5 sm:py-3 px-3.5 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 transition">
                                <span x-show="device.errors && device.errors.problem" x-cloak class="text-[11px] text-rose-500 font-bold block mt-1.5">⚠️ Masla likhein ya upar di gayi services mein se select karein.</span>
                            </div>

                            <!-- Screen Security -->
                            <div class="lg:col-span-4">
                                <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-2">Screen Lock / PIN</label>
                                <div class="flex items-center gap-2.5">
                                    <select :name="'devices[' + dIdx + '][security_type]'" x-model="device.securityType" class="py-2.5 sm:py-3 px-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                        <option value="none">No Lock</option>
                                        <option value="pin">PIN / Pass</option>
                                        <option value="pattern">Pattern</option>
                                    </select>
                                    <input type="text" :name="'devices[' + dIdx + '][security_code]'" x-model="device.securityCode" placeholder="Code (e.g. 1234 or L-Pattern)" class="w-full py-2.5 sm:py-3 px-3.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>

                        <!-- Physical Condition Checklist -->
                        <div class="p-4 sm:p-5 bg-slate-50/70 dark:bg-gray-900/40 rounded-2xl border border-slate-200/80 dark:border-gray-700/80 space-y-3">
                            <div class="text-[11px] font-black text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                Device Condition at Intake (Dispute se bachne ke liye tick karein):
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 text-xs sm:text-sm">
                                <label class="inline-flex items-center gap-2.5 cursor-pointer bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-indigo-400 dark:hover:border-indigo-600 transition shadow-2xs">
                                    <input type="checkbox" :name="'devices[' + dIdx + '][condition_checklist][power_on]'" value="1" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="text-gray-700 dark:text-gray-300 font-bold">Power On</span>
                                </label>
                                <label class="inline-flex items-center gap-2.5 cursor-pointer bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-indigo-400 dark:hover:border-indigo-600 transition shadow-2xs">
                                    <input type="checkbox" :name="'devices[' + dIdx + '][condition_checklist][touch_working]'" value="1" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="text-gray-700 dark:text-gray-300 font-bold">Touch OK</span>
                                </label>
                                <label class="inline-flex items-center gap-2.5 cursor-pointer bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-indigo-400 dark:hover:border-indigo-600 transition shadow-2xs">
                                    <input type="checkbox" :name="'devices[' + dIdx + '][condition_checklist][camera_ok]'" value="1" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="text-gray-700 dark:text-gray-300 font-bold">Cameras OK</span>
                                </label>
                                <label class="inline-flex items-center gap-2.5 cursor-pointer bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-indigo-400 dark:hover:border-indigo-600 transition shadow-2xs">
                                    <input type="checkbox" :name="'devices[' + dIdx + '][condition_checklist][body_scratch]'" value="1" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="text-gray-700 dark:text-gray-300 font-bold">Body Scratched</span>
                                </label>
                                <label class="inline-flex items-center gap-2.5 cursor-pointer bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-indigo-400 dark:hover:border-indigo-600 transition shadow-2xs">
                                    <input type="checkbox" :name="'devices[' + dIdx + '][condition_checklist][back_broken]'" value="1" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="text-gray-700 dark:text-gray-300 font-bold">Back Broken</span>
                                </label>
                            </div>
                        </div>

                        <!-- 3. Spare Parts Selection with SEARCHABLE INVENTORY DROPDOWN -->
                        <div class="p-5 sm:p-6 bg-indigo-50/40 dark:bg-indigo-950/20 rounded-2xl border border-indigo-200/60 dark:border-indigo-900/50 space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <div class="text-xs sm:text-sm font-black text-indigo-900 dark:text-indigo-300 uppercase tracking-wider flex items-center gap-2">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        <span>Spare Parts from Inventory (Searchable Dropdown jaise Purchase mein hai)</span>
                                    </div>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 block">Part search karein (Battery, Glass, LCD). Delivery ke waqt inventory khud ba khud deduct hogi.</span>
                                </div>
                                <button type="button" @click="addPart(device)" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5 self-start sm:self-auto">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>+ Add Spare Part</span>
                                </button>
                            </div>

                            <!-- Parts Table -->
                            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-2xs">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/2">
                                                Spare Part (Search Name / SKU / Barcode)
                                            </th>
                                            <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-28">
                                                Current Stock
                                            </th>
                                            <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-24">
                                                Qty
                                            </th>
                                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-36">
                                                Rate (Rs)
                                            </th>
                                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-36">
                                                Subtotal (Rs)
                                            </th>
                                            <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-12">
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <template x-for="(part, pIdx) in device.parts" :key="pIdx">
                                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-750 transition">
                                                <!-- Searchable Product Input -->
                                                <td class="px-3.5 py-3 relative" @click.away="part.open = false">
                                                    <input type="text" x-model="part.search" @focus="part.open = true"
                                                        @input="part.open = true" @keydown="handlePartKey($event, part)"
                                                        class="block w-full text-xs sm:text-sm py-2 px-3 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-bold"
                                                        placeholder="Type to search part by name, SKU, or barcode..." autocomplete="off">

                                                    <input type="hidden" :name="'devices[' + dIdx + '][parts][' + pIdx + '][product_id]'"
                                                        :value="part.productId">

                                                    <!-- Product Results Dropdown -->
                                                    <div x-show="part.open && getFilteredProducts(part.search).length > 0" x-cloak
                                                        class="absolute z-50 mt-1.5 w-full bg-white dark:bg-gray-800 shadow-2xl max-h-60 rounded-xl py-1 text-xs border border-gray-200 dark:border-gray-700 overflow-auto divide-y dark:divide-gray-700">
                                                        <template x-for="(prod, pIdx2) in getFilteredProducts(part.search)" :key="prod.id">
                                                            <div @click="selectProductForPart(part, prod)"
                                                                class="cursor-pointer select-none relative py-2.5 px-3.5 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition"
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
                                                                        <span class="block font-black text-gray-900 dark:text-gray-100 text-xs sm:text-sm"
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
                                                <td class="px-3 py-3 text-center text-xs font-black">
                                                    <span :class="part.stock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500'"
                                                        x-text="part.stock !== null ? part.stock : '--'"></span>
                                                </td>

                                                <!-- Quantity -->
                                                <td class="px-3 py-3">
                                                    <input type="number" min="1" :name="'devices[' + dIdx + '][parts][' + pIdx + '][quantity]'"
                                                        x-model.number="part.qty"
                                                        class="block w-full text-xs sm:text-sm py-2 text-center border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-black"
                                                        required>
                                                </td>

                                                <!-- Rate -->
                                                <td class="px-3 py-3">
                                                    <input type="number" step="any" :name="'devices[' + dIdx + '][parts][' + pIdx + '][unit_price]'"
                                                        x-model.number="part.price"
                                                        class="block w-full text-xs sm:text-sm py-2 text-right border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-black font-mono"
                                                        required>
                                                </td>

                                                <!-- Subtotal -->
                                                <td class="px-4 py-3 text-right text-xs sm:text-sm font-black font-mono text-gray-900 dark:text-gray-100">
                                                    Rs. <span x-text="formatNumber((part.qty || 0) * (part.price || 0))"></span>
                                                </td>

                                                <!-- Remove Row -->
                                                <td class="px-3 py-3 text-center">
                                                    <button type="button" @click="removePart(device, pIdx)"
                                                        class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition"
                                                        title="Remove part">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="device.parts.length === 0">
                                            <tr>
                                                <td colspan="6" class="py-5 text-center text-xs text-gray-400 italic">
                                                    Koi spare part shamil nahi kiya gaya. Agar battery, display wagera lagani ho to <strong>"+ Add Spare Part"</strong> dabayein.
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Device Labor Charges & Summary -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6 p-5 sm:p-6 bg-slate-50/90 dark:bg-gray-900/50 rounded-2xl border border-slate-200 dark:border-gray-700">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-2">
                                    Assigned Technician (Karigar)
                                </label>
                                <select :name="'devices[' + dIdx + '][technician_id]'" x-model="device.technicianId" class="w-full py-2.5 sm:py-3 px-3.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Assign Later --</option>
                                    @foreach($technicians as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-2">
                                    Repair Warranty (Days)
                                </label>
                                <input type="number" :name="'devices[' + dIdx + '][warranty_days]'" x-model.number="device.warrantyDays" placeholder="7" class="w-full py-2.5 sm:py-3 px-3.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-2">
                                    Labor / Service Charges (Mazdoori)
                                </label>
                                <input type="number" step="any" :name="'devices[' + dIdx + '][labor_cost]'" x-model.number="device.labor" placeholder="0" class="w-full py-2.5 sm:py-3 px-3.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs sm:text-sm font-black font-mono text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div class="flex flex-col justify-center items-start sm:items-end text-left sm:text-right bg-white dark:bg-gray-800/80 p-3.5 sm:p-4 rounded-xl border border-indigo-100 dark:border-indigo-950/50">
                                <span class="text-[10px] text-gray-400 uppercase font-black tracking-wider">This Device Estimated Total</span>
                                <span class="text-xl font-black text-indigo-600 dark:text-indigo-400 font-mono mt-0.5">
                                    Rs <span x-text="calculateDeviceTotal(device)"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- 3. Grand Total & Advance Payment Summary Card (Spacious & Clean) -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 sm:p-8 border border-gray-200 dark:border-gray-700 shadow-sm space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-xs font-black shadow-xs">3</span>
                        Billing Estimate & Advance Payment (Kul Hisaab & Peshgi)
                    </h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
                    <!-- Total Spare Parts -->
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-gray-900/60 border border-slate-200/80 dark:border-gray-700">
                        <div class="text-[11px] text-gray-500 uppercase tracking-wider font-bold">Total Spare Parts</div>
                        <div class="text-xl font-black text-gray-900 dark:text-white mt-1.5">
                            Rs <span x-text="calculateGrandParts()"></span>
                        </div>
                    </div>

                    <!-- Total Labor -->
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-gray-900/60 border border-slate-200/80 dark:border-gray-700">
                        <div class="text-[11px] text-gray-500 uppercase tracking-wider font-bold">Total Labor (Mazdoori)</div>
                        <div class="text-xl font-black text-gray-900 dark:text-white mt-1.5">
                            Rs <span x-text="calculateGrandLabor()"></span>
                        </div>
                    </div>

                    <!-- Grand Total Estimate -->
                    <div class="p-5 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800">
                        <div class="text-[11px] text-indigo-700 dark:text-indigo-300 uppercase tracking-wider font-bold">Grand Total (Kul Bill)</div>
                        <div class="text-2xl font-black text-indigo-900 dark:text-indigo-100 mt-1.5 font-mono">
                            Rs <span x-text="calculateGrandTotal()"></span>
                        </div>
                    </div>

                    <!-- Advance Received Amount -->
                    <div>
                        <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-2">
                            Advance Received (Peshgi)
                        </label>
                        <input type="number" step="any" name="advance_amount" x-model.number="advanceAmount" placeholder="0" class="w-full py-3 px-4 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-base font-black text-emerald-600 dark:text-emerald-400 focus:ring-2 focus:ring-emerald-400 font-mono">
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-2">
                            Payment Method
                        </label>
                        <select name="payment_method" class="w-full py-3 px-4 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-emerald-400">
                            <option value="cash">Cash Counter</option>
                            <option value="bank">Bank Transfer / Online</option>
                            <option value="jazzcash">JazzCash / EasyPaisa</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5 p-5 sm:p-6 rounded-2xl bg-slate-50 dark:bg-gray-900/60 border border-slate-200/90 dark:border-gray-700">
                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wider font-bold">Remaining Balance (Baqaya)</div>
                        <div class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1 font-mono">
                            Rs <span x-text="calculateBalance()"></span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3.5">
                        <label class="flex items-center gap-2.5 cursor-pointer bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 px-4 py-2.5 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 transition select-none border border-gray-200 dark:border-gray-700 shadow-2xs">
                            <input type="checkbox" name="auto_print" value="1" checked class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <span>🖨️ Auto-Open Slips on Submit</span>
                        </label>
                        <a href="{{ route('repairs.index') }}" class="px-5 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs sm:text-sm rounded-xl transition">
                            Cancel
                        </a>
                        <button type="submit" class="px-7 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md transition flex items-center gap-2 active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Save & Print Slips (Sticker + Customer Copy)</span>
                        </button>
                    </div>
                </div>
            </div>

        </form>

        <!-- Quick Add Service Modal -->
        <div x-show="quickServiceModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-700 space-y-4" @click.away="quickServiceModalOpen = false">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="font-black text-sm text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">🔧</span>
                        <span>Add New Repair Service</span>
                    </h3>
                    <button type="button" @click="quickServiceModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Service / Fault Name *</label>
                        <input type="text" x-model="quickServiceForm.name" placeholder="e.g. Face ID Repair, Sensor Issue..." class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Category</label>
                        <select x-model="quickServiceForm.category" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-semibold text-gray-900 dark:text-white">
                            <option value="Screen & Glass">Screen & Glass</option>
                            <option value="Battery & Charging">Battery & Charging</option>
                            <option value="Camera & Audio">Camera & Audio</option>
                            <option value="Board & Motherboard">Board & Motherboard</option>
                            <option value="Software & OS">Software & OS</option>
                            <option value="General Repair">General Repair</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Default Labor Rate (Rs)</label>
                        <input type="number" step="any" x-model.number="quickServiceForm.rate" placeholder="500" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" @click="quickServiceModalOpen = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-xl">
                        Cancel
                    </button>
                    <button type="button" @click="saveQuickService()" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs">
                        Save & Apply
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function repairIntakeForm() {
            const rawBrands = {!! json_encode($brands) !!};
            const rawServices = {!! json_encode($services) !!};
            const oldDevices = {!! json_encode(old('devices') ?? []) !!};

            let initialDevices = [];
            if (oldDevices && oldDevices.length > 0) {
                initialDevices = oldDevices.map((d, idx) => {
                    const foundBrand = rawBrands.find(b => b.name.toLowerCase() === (d.brand || '').trim().toLowerCase());
                    return {
                        id: Date.now() + idx,
                        brand: d.brand || '',
                        brandOpen: false,
                        selectedBrandId: foundBrand ? foundBrand.id : null,
                        model: d.model || '',
                        modelOpen: false,
                        imei: d.imei || '',
                        color: d.color || '',
                        problem: d.problem_description || d.problem || '',
                        services: Array.isArray(d.services) ? d.services : [],
                        securityType: d.security_type || 'none',
                        securityCode: d.security_code || '',
                        labor: parseFloat(d.labor_cost || d.labor) || 0,
                        technicianId: d.technician_id || '',
                        warrantyDays: parseInt(d.warranty_days) || 7,
                        parts: Array.isArray(d.parts) ? d.parts : [],
                        errors: { model: false, problem: false }
                    };
                });
            } else {
                initialDevices = [
                    {
                        id: Date.now(),
                        brand: '',
                        brandOpen: false,
                        selectedBrandId: null,
                        model: '',
                        modelOpen: false,
                        imei: '',
                        color: '',
                        problem: '',
                        services: [],
                        securityType: 'none',
                        securityCode: '',
                        labor: 0,
                        technicianId: '',
                        warrantyDays: 7,
                        parts: [],
                        errors: { model: false, problem: false }
                    }
                ];
            }

            return {
                customersList: {!! json_encode($customers) !!},
                productsList: {!! json_encode($products) !!},
                brandsList: rawBrands,
                servicesList: rawServices,

                // Customer Search State
                customerSearch: '{{ addslashes(old('customer_name', '')) }}',
                customerOpen: false,
                customerHighlightedIndex: 0,
                selectedPartyId: '{{ old('party_id', '') }}',
                customerName: '{{ addslashes(old('customer_name', '')) }}',
                customerPhone: '{{ addslashes(old('customer_phone', '')) }}',
                customerBalance: null,

                advanceAmount: {{ (float) old('advance_amount', 0) }},
                errors: {
                    customerName: false,
                    customerPhone: false
                },
                formErrors: [],
                devices: initialDevices,

                formatNumber(val) {
                    return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
                },

                // Filter Brands
                getFilteredBrands(device) {
                    const q = (device.brand || '').trim().toLowerCase();
                    if (!q) return this.brandsList;
                    return this.brandsList.filter(b => b.name.toLowerCase().includes(q));
                },

                selectBrand(device, b) {
                    device.brand = b.name;
                    device.selectedBrandId = b.id;
                    device.brandOpen = false;
                    device.model = '';
                    device.modelOpen = true;
                },

                async addNewBrand(device, brandName) {
                    const name = (brandName || '').trim();
                    if (!name) return;
                    try {
                        const res = await fetch("{{ route('repairs.brand.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ name })
                        });
                        const data = await res.json();
                        if (data.success) {
                            if (!this.brandsList.find(b => b.id === data.brand.id)) {
                                this.brandsList.push(data.brand);
                            }
                            this.selectBrand(device, data.brand);
                            return;
                        }
                    } catch (e) {
                        console.error(e);
                    }
                    device.brand = name;
                    device.brandOpen = false;
                },

                // Filter Models by selected Brand
                getFilteredModels(device) {
                    let models = [];
                    if (device.selectedBrandId) {
                        const brand = this.brandsList.find(b => b.id == device.selectedBrandId);
                        if (brand && brand.models) {
                            models = brand.models;
                        }
                    } else if (device.brand) {
                        const brand = this.brandsList.find(b => b.name.toLowerCase() === device.brand.trim().toLowerCase());
                        if (brand && brand.models) {
                            models = brand.models;
                        }
                    } else {
                        // All models flattened
                        models = this.brandsList.flatMap(b => b.models || []);
                    }

                    const q = (device.model || '').trim().toLowerCase();
                    if (!q) return models.slice(0, 40);
                    return models.filter(m => m.name.toLowerCase().includes(q)).slice(0, 40);
                },

                selectModel(device, m) {
                    device.model = m.name;
                    device.modelOpen = false;
                },

                async addNewModel(device, modelName) {
                    const name = (modelName || '').trim();
                    if (!name) return;
                    if (device.selectedBrandId) {
                        try {
                            const res = await fetch("{{ route('repairs.model.store') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    repair_brand_id: device.selectedBrandId,
                                    name: name,
                                    series: 'Custom Models'
                                })
                            });
                            const data = await res.json();
                            if (data.success) {
                                const brand = this.brandsList.find(b => b.id == device.selectedBrandId);
                                if (brand) {
                                    if (!brand.models) brand.models = [];
                                    brand.models.push(data.model);
                                }
                                this.selectModel(device, data.model);
                                return;
                            }
                        } catch (e) {
                            console.error(e);
                        }
                    }
                    device.model = name;
                    device.modelOpen = false;
                },

                // Quick Add Service Modal State
                quickServiceModalOpen: false,
                quickServiceTargetDevice: null,
                quickServiceForm: {
                    name: '',
                    category: 'General Repair',
                    rate: 500
                },

                openQuickAddServiceModal(device) {
                    this.quickServiceTargetDevice = device;
                    this.quickServiceForm = {
                        name: '',
                        category: 'General Repair',
                        rate: 500
                    };
                    this.quickServiceModalOpen = true;
                },

                async saveQuickService() {
                    const name = (this.quickServiceForm.name || '').trim();
                    if (!name) {
                        alert('Service ka naam likhna lazmi hai!');
                        return;
                    }
                    const rate = parseFloat(this.quickServiceForm.rate) || 0;
                    const cat = this.quickServiceForm.category || 'General Repair';

                    try {
                        const res = await fetch("{{ route('repairs.service.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                name: name,
                                category: cat,
                                default_labor_cost: rate
                            })
                        });
                        const data = await res.json();
                        if (data.success && data.service) {
                            if (!this.servicesList.find(s => s.id === data.service.id)) {
                                this.servicesList.push(data.service);
                            }
                            if (this.quickServiceTargetDevice) {
                                if (!this.quickServiceTargetDevice.services) this.quickServiceTargetDevice.services = [];
                                const alreadySelected = this.quickServiceTargetDevice.services.find(s => s.name === data.service.name);
                                if (!alreadySelected) {
                                    this.quickServiceTargetDevice.services.push({
                                        name: data.service.name,
                                        rate: parseFloat(data.service.default_labor_cost) || rate
                                    });
                                    this.recalculateDeviceServices(this.quickServiceTargetDevice);
                                }
                            }
                            this.quickServiceModalOpen = false;
                            return;
                        }
                    } catch (e) {
                        console.error(e);
                    }
                    this.quickServiceModalOpen = false;
                },

                // Services & Faults Management
                toggleService(device, srv) {
                    if (!device.services) device.services = [];
                    const existsIdx = device.services.findIndex(s => s.name === srv.name);
                    if (existsIdx >= 0) {
                        device.services.splice(existsIdx, 1);
                    } else {
                        device.services.push({
                            name: srv.name,
                            rate: parseFloat(srv.default_labor_cost) || 0
                        });
                    }
                    this.recalculateDeviceServices(device);
                },

                removeService(device, sIdx) {
                    device.services.splice(sIdx, 1);
                    this.recalculateDeviceServices(device);
                },

                isServiceSelected(device, srvName) {
                    if (!device.services) return false;
                    return device.services.some(s => s.name === srvName);
                },

                recalculateDeviceServices(device) {
                    const sum = device.services.reduce((total, s) => total + (parseFloat(s.rate) || 0), 0);
                    device.labor = sum;

                    const serviceNames = device.services.map(s => s.name).join(', ');
                    if (serviceNames) {
                        device.problem = serviceNames;
                    }
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
                        brandOpen: false,
                        selectedBrandId: null,
                        model: '',
                        modelOpen: false,
                        imei: '',
                        color: '',
                        problem: '',
                        services: [],
                        securityType: 'none',
                        securityCode: '',
                        labor: 0,
                        technicianId: '',
                        warrantyDays: 7,
                        parts: [],
                        errors: { model: false, problem: false }
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

                removeFromErrors(keyword) {
                    this.formErrors = this.formErrors.filter(err => !err.toLowerCase().includes(keyword.toLowerCase()));
                },

                validateAndSubmit(e) {
                    this.formErrors = [];
                    this.errors.customerName = false;
                    this.errors.customerPhone = false;

                    const trimmedName = (this.customerName || '').trim();
                    const trimmedPhone = (this.customerPhone || '').trim();

                    if (!trimmedName || trimmedName.length < 2) {
                        this.errors.customerName = true;
                        this.formErrors.push('Customer ka naam likhna lazmi hai (kam az kam 2 huroof).');
                    }

                    if (!trimmedPhone) {
                        this.errors.customerPhone = true;
                        this.formErrors.push('Customer ka mobile / WhatsApp number lazmi hai.');
                    } else if (trimmedPhone.replace(/[^0-9]/g, '').length < 7) {
                        this.errors.customerPhone = true;
                        this.formErrors.push('Valid phone number darj karein (kam az kam 7 digits).');
                    }

                    if (!this.devices || this.devices.length === 0) {
                        this.formErrors.push('Kam az kam 1 mobile add karna lazmi hai.');
                    }

                    for (let i = 0; i < this.devices.length; i++) {
                        const dev = this.devices[i];
                        if (!dev.errors) dev.errors = {};
                        dev.errors.model = false;
                        dev.errors.problem = false;

                        const modelTrim = (dev.model || '').trim();
                        const problemTrim = (dev.problem || '').trim();

                        if (!modelTrim) {
                            dev.errors.model = true;
                            this.formErrors.push(`Mobile #${i + 1}: Phone model ka naam select ya type karein.`);
                        }

                        if (!problemTrim && (!dev.services || dev.services.length === 0)) {
                            dev.errors.problem = true;
                            this.formErrors.push(`Mobile #${i + 1}: Masla / fault likhein ya services select karein.`);
                        }

                        if (dev.parts && dev.parts.length > 0) {
                            for (let p = 0; p < dev.parts.length; p++) {
                                const part = dev.parts[p];
                                if (!part.productId && !part.search) {
                                    this.formErrors.push(`Mobile #${i + 1}: Spare part row #${p + 1} mein product search karke select karein.`);
                                } else if ((part.qty || 0) <= 0) {
                                    this.formErrors.push(`Mobile #${i + 1}: Spare part "${part.search}" ki quantity kam az kam 1 honi chahiye.`);
                                }
                            }
                        }
                    }

                    if (this.formErrors.length > 0) {
                        e.preventDefault();
                        this.$nextTick(() => {
                            const errorEl = document.getElementById('formErrorSummary') || document.querySelector('.border-rose-500');
                            if (errorEl) {
                                errorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        });
                        return false;
                    }
                }
            }
        }
    </script>
</x-app-layout>
