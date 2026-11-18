<x-app-layout>
    <div class="py-3 sm:py-5 max-w-[1700px] mx-auto px-2 sm:px-6 lg:px-8" x-data="repairEditForm()" @keydown.window.ctrl.enter="document.getElementById('repairSubmitBtn').click()">
        <form method="POST" action="{{ route('repairs.update', $repair->id) }}" @submit="validateAndSubmit($event)">
            @csrf
            @method('PUT')

            <!-- Header bar -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-black shadow-sm shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base sm:text-lg font-black text-gray-900 dark:text-white uppercase tracking-wide">
                                {{ __('Repair Order Tabdeel Karein (Edit Job Sheet)') }}
                            </h2>
                            <span class="px-2.5 py-0.5 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 font-mono font-bold text-xs border border-amber-200 dark:border-amber-800">
                                #{{ $repair->order_no }}
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                            Customer maloomat, mobile fault ya labor charges update karein.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-auto">
                    <a href="{{ route('repairs.show', $repair->id) }}" class="px-3 py-1.5 rounded-xl border border-gray-300 dark:border-gray-600 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        &larr; {{ __('View Order') }}
                    </a>
                </div>
            </div>

            <!-- Server & Exception Error Display -->
            @if(session('error'))
                <div class="mb-3 p-3 bg-rose-50 dark:bg-rose-950/40 border-l-4 border-rose-500 rounded-r-xl text-rose-800 dark:text-rose-200 text-xs font-bold">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-3 p-3 bg-rose-50 dark:bg-rose-950/40 border-l-4 border-rose-500 rounded-r-xl text-rose-800 dark:text-rose-200 text-xs space-y-1">
                    <div class="font-black">Meherbani farma kar yeh khamiyan theek karein:</div>
                    <ul class="list-disc pl-5 font-semibold">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Client-Side Real-Time Error Summary Banner -->
            <div id="formErrorSummary" x-show="formErrors.length > 0" x-cloak class="mb-3 p-3 bg-rose-50 dark:bg-rose-950/50 border-2 border-rose-400 dark:border-rose-800 rounded-xl text-rose-900 dark:text-rose-200 text-xs space-y-1">
                <div class="font-black flex items-center justify-between text-rose-700 dark:text-rose-300">
                    <span>⚠️ Form mein darj zail khamiyan theek karein:</span>
                    <button type="button" @click="formErrors = []" class="text-rose-500 hover:text-rose-700 font-black">&times;</button>
                </div>
                <ul class="list-disc pl-5 font-bold">
                    <template x-for="(err, idx) in formErrors" :key="idx">
                        <li x-text="err"></li>
                    </template>
                </ul>
            </div>

            <!-- Two-Column POS Layout: Left = Inputs, Right = Sticky Summary & Submit -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5 items-start">
                
                <!-- Left 8 Columns -->
                <div class="lg:col-span-8 space-y-4">
                    
                    <!-- 1. Customer & Intake Details (Ultra Compact 1-Row Card) -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200 dark:border-gray-700 shadow-xs space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="w-5 h-5 rounded-lg bg-amber-500 text-white flex items-center justify-center text-[10px] font-black">1</span>
                                Customer & Wapsi Ka Time
                            </h3>
                            <button type="button" @click="openQuickCustomerModal()" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold rounded-lg shadow-2xs transition flex items-center gap-1 active:scale-95 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                <span>+ Direct Customer Banayein</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                            <!-- Khata Search -->
                            <div class="relative" @click.away="customerOpen = false" @scroll.window="customerOpen = false">
                                <label class="block text-[11px] font-black uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                                    Search Customer (Khata)
                                </label>
                                <div class="relative">
                                    <input type="text" x-model="customerSearch" @focus="customerOpen = true"
                                        @input="customerOpen = true" @keydown="handleCustomerKey($event)"
                                        class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl text-xs font-bold transition"
                                        :class="{'border-rose-500 ring-1 ring-rose-500': errors.customerName}"
                                        placeholder="Search by name / phone..." autocomplete="off">

                                    <input type="hidden" name="party_id" :value="selectedPartyId">

                                    <!-- Dropdown List -->
                                    <div x-show="customerOpen && getFilteredCustomers().length > 0" x-cloak
                                        class="absolute z-30 mt-1 left-0 right-0 bg-white dark:bg-gray-800 shadow-xl max-h-56 rounded-xl py-1 text-xs border border-gray-200 dark:border-gray-700 overflow-auto divide-y dark:divide-gray-700">
                                        <div @click="selectCustomer(null)"
                                            class="cursor-pointer py-2 px-3 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 font-bold text-gray-500 italic">
                                            <span>-- Walk-in / Custom Customer --</span>
                                        </div>
                                        <template x-for="(c, cIdx) in getFilteredCustomers()" :key="c.id">
                                            <div @click="selectCustomer(c)"
                                                class="cursor-pointer py-2 px-3 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition flex items-center justify-between"
                                                :class="customerHighlightedIndex === cIdx ? 'bg-indigo-50 dark:bg-indigo-950/40' : ''">
                                                <div>
                                                    <span class="block truncate uppercase font-bold text-gray-900 dark:text-gray-100" x-text="c.name"></span>
                                                    <span class="text-gray-400 text-[10px]" x-text="c.phone || 'No Phone'"></span>
                                                </div>
                                                <span class="font-black text-[11px]" :class="c.current_balance >= 0 ? 'text-rose-500' : 'text-emerald-500'">
                                                    Rs. <span x-text="formatNumber(Math.abs(c.current_balance || 0))"></span>
                                                    <span class="text-[9px]" x-text="(c.current_balance || 0) >= 0 ? 'Dr' : 'Cr'"></span>
                                                </span>
                                            </div>
                                        </template>
                                        <div x-show="customerSearch && !getFilteredCustomers().some(c => c.name.toLowerCase() === customerSearch.trim().toLowerCase())"
                                             @click="openQuickCustomerModal(customerSearch)"
                                             class="px-3 py-2 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-bold cursor-pointer text-xs flex items-center gap-1.5 border-t border-emerald-100">
                                            <span>+ Add "</span><span x-text="customerSearch"></span><span>" as Direct New Customer</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Name -->
                            <div>
                                <label class="block text-[11px] font-black uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                                    Customer Name *
                                </label>
                                <input type="text" name="customer_name" x-model="customerName"
                                       @input="errors.customerName = false; removeFromErrors('Customer ka naam')"
                                       :class="errors.customerName ? 'border-rose-500 ring-1 ring-rose-400 bg-rose-50/40 dark:bg-rose-950/20' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900'"
                                       class="w-full py-2 px-3 rounded-xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 transition">
                                <span x-show="errors.customerName" x-cloak class="text-[10px] text-rose-500 font-bold block mt-1">⚠️ Customer ka naam lazmi hai.</span>
                            </div>

                            <!-- Customer Phone -->
                            <div>
                                <label class="block text-[11px] font-black uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                                    Phone / WhatsApp *
                                </label>
                                <input type="text" name="customer_phone" x-model="customerPhone"
                                       @input="errors.customerPhone = false; removeFromErrors('phone number')"
                                       :class="errors.customerPhone ? 'border-rose-500 ring-1 ring-rose-400 bg-rose-50/40 dark:bg-rose-950/20' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900'"
                                       class="w-full py-2 px-3 rounded-xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 transition">
                                <span x-show="errors.customerPhone" x-cloak class="text-[10px] text-rose-500 font-bold block mt-1">⚠️ Mobile number lazmi hai.</span>
                            </div>

                            <!-- Expected Delivery Date & Time (Wapsi Ka Time) -->
                            <div>
                                <label class="block text-[11px] font-black uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                                    Wapsi Ka Time
                                </label>
                                <input type="datetime-local" name="expected_delivery_date" value="{{ $repair->expected_delivery_date ? \Carbon\Carbon::parse($repair->expected_delivery_date)->format('Y-m-d\TH:i') : '' }}" class="w-full py-2 px-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <input type="hidden" name="received_date" value="{{ $repair->received_date ? \Carbon\Carbon::parse($repair->received_date)->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i') }}">
                        <input type="hidden" name="default_warehouse_id" value="{{ $repair->default_warehouse_id ?? ($warehouses->first()->id ?? 1) }}">
                    </div>

                    <!-- 2. Devices Section -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between px-1">
                            <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="w-5 h-5 rounded-lg bg-amber-500 text-white flex items-center justify-center text-[10px] font-black">2</span>
                                Devices to Repair
                            </h3>
                            <button type="button" @click="addDevice()" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 font-bold text-xs rounded-xl border border-amber-200 dark:border-amber-800 transition flex items-center gap-1 active:scale-95">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>+ Add Another Mobile</span>
                            </button>
                        </div>

                        <template x-for="(device, dIdx) in devices" :key="device.id || device.tempId">
                            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 sm:p-5 shadow-xs space-y-4 relative">
                                <input type="hidden" :name="'devices[' + dIdx + '][id]'" :value="device.id">

                                <!-- Device Header Bar -->
                                <div class="flex items-center justify-between pb-2.5 border-b border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 bg-amber-500 text-white font-black text-[11px] rounded-lg uppercase tracking-wider shadow-2xs" x-text="'Mobile #' + (dIdx + 1)"></span>
                                        <span class="text-[11px] font-mono font-bold text-gray-400" x-text="device.trackingNo || 'New Device'"></span>
                                    </div>
                                    <template x-if="devices.length > 1">
                                        <button type="button" @click="removeDevice(dIdx)" class="px-2.5 py-1 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg text-xs font-bold transition border border-rose-200 dark:border-rose-900/50">
                                            Remove Mobile
                                        </button>
                                    </template>
                                </div>

                                <!-- Brand & Model 2-Column Row -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                    <!-- Brand -->
                                    <div class="relative" @click.away="device.brandOpen = false" @scroll.window="device.brandOpen = false">
                                        <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                                            Brand / Company
                                        </label>
                                        <div class="relative">
                                            <input type="text"
                                                   :name="'devices[' + dIdx + '][brand]'"
                                                   x-model="device.brand"
                                                   @focus="device.brandOpen = true"
                                                   @input="device.brandOpen = true; device.selectedBrandId = null"
                                                   placeholder="Samsung, Infinix, Vivo..."
                                                   autocomplete="off"
                                                   class="w-full py-2 px-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                            
                                            <div x-show="device.brandOpen"
                                                 class="absolute left-0 right-0 mt-1 max-h-48 overflow-y-auto bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 z-30 text-xs"
                                                 style="display: none;">
                                                <template x-for="b in getFilteredBrands(device)" :key="b.id">
                                                    <div @click="selectBrand(device, b)"
                                                         class="px-3 py-2 hover:bg-indigo-50 dark:hover:bg-gray-700 cursor-pointer flex items-center justify-between border-b border-gray-50 dark:border-gray-700/50">
                                                        <span class="font-bold text-gray-800 dark:text-gray-200" x-text="b.name"></span>
                                                        <span class="text-[10px] text-gray-400 font-mono" x-text="(b.models ? b.models.length : 0) + ' models'"></span>
                                                    </div>
                                                </template>
                                                <div x-show="device.brand && !brandsList.some(b => b.name.toLowerCase() === device.brand.trim().toLowerCase())"
                                                     @click="addNewBrand(device, device.brand)"
                                                     class="px-3 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold cursor-pointer text-xs border-t border-indigo-100">
                                                    <span>+ Add "</span><span x-text="device.brand"></span><span>" as New Brand</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Model -->
                                    <div class="relative" @click.away="device.modelOpen = false" @scroll.window="device.modelOpen = false">
                                        <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                                            Model Name *
                                        </label>
                                        <div class="relative">
                                            <input type="text"
                                                   :name="'devices[' + dIdx + '][model]'"
                                                   x-model="device.model"
                                                   @focus="device.modelOpen = true"
                                                   @input="device.modelOpen = true; if(device.errors) device.errors.model = false"
                                                   placeholder="Model (e.g. A32, Hot 10, Y20)..."
                                                   autocomplete="off"
                                                   :class="device.errors && device.errors.model ? 'border-rose-500 ring-1 ring-rose-400 bg-rose-50/40 dark:bg-rose-950/20' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900'"
                                                   class="w-full py-2 px-3 rounded-xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 transition">
                                            <span x-show="device.errors && device.errors.model" x-cloak class="text-[10px] text-rose-500 font-bold block mt-1">Mobile model likhna lazmi hai.</span>
                                            
                                            <div x-show="device.modelOpen"
                                                 class="absolute left-0 right-0 mt-1 max-h-52 overflow-y-auto bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 z-30 text-xs"
                                                 style="display: none;">
                                                <template x-for="m in getFilteredModels(device)" :key="m.id">
                                                    <div @click="selectModel(device, m)"
                                                         class="px-3 py-2 hover:bg-indigo-50 dark:hover:bg-gray-700 cursor-pointer flex items-center justify-between border-b border-gray-50 dark:border-gray-700/50">
                                                        <span class="font-bold text-gray-800 dark:text-gray-200" x-text="m.name"></span>
                                                        <span class="text-[10px] text-gray-400" x-text="m.series || ''"></span>
                                                    </div>
                                                </template>
                                                <div x-show="device.model && !getFilteredModels(device).some(m => m.name.toLowerCase() === device.model.trim().toLowerCase())"
                                                     @click="addNewModel(device, device.model)"
                                                     class="px-3 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold cursor-pointer text-xs border-t border-indigo-100">
                                                    <span>+ Add "</span><span x-text="device.model"></span><span>" to Model List</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Predefined Services & Common Faults: Search & Select Only -->
                                <div class="p-3 bg-slate-50 dark:bg-gray-900/60 rounded-xl border border-slate-200 dark:border-gray-700 space-y-2.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-[11px] font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                            Services & Faults
                                        </span>

                                        <button type="button" @click="openQuickAddServiceModal(device)" class="px-2.5 py-1 bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 text-[11px] font-bold text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded-lg shrink-0 transition cursor-pointer">
                                            + New Service
                                        </button>
                                    </div>

                                    <!-- Search & Select Services Combobox -->
                                    <div class="relative" @click.away="device.serviceDropdownOpen = false" @scroll.window="device.serviceDropdownOpen = false">
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                            </div>
                                            <input type="text"
                                                   x-model="device.serviceSearch"
                                                   @focus="device.serviceDropdownOpen = true; device.serviceHighlightedIndex = 0"
                                                   @input="device.serviceDropdownOpen = true; device.serviceHighlightedIndex = 0"
                                                   @keydown.arrow-down.prevent="device.serviceHighlightedIndex = Math.min((device.serviceHighlightedIndex || 0) + 1, getFilteredServices(device).length - 1)"
                                                   @keydown.arrow-up.prevent="device.serviceHighlightedIndex = Math.max((device.serviceHighlightedIndex || 0) - 1, 0)"
                                                   @keydown.enter.prevent="selectHighlightedService(device)"
                                                   @keydown.escape="device.serviceDropdownOpen = false"
                                                   placeholder="Search service to select (Screen, Battery, Mic)..."
                                                   class="w-full text-xs font-bold py-2 pl-9 pr-8 bg-white dark:bg-gray-800 border border-slate-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 transition shadow-2xs">
                                            
                                            <button type="button"
                                                    x-show="device.serviceSearch"
                                                    @click="device.serviceSearch = ''; device.serviceDropdownOpen = false"
                                                    class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 text-xs font-bold">
                                                &times;
                                            </button>
                                        </div>

                                        <!-- Live Search Dropdown Menu (Only shows when searching) -->
                                        <div x-show="device.serviceDropdownOpen && (device.serviceSearch || '').trim().length > 0"
                                             x-cloak
                                             class="absolute z-30 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 py-1 divide-y divide-gray-100 dark:divide-gray-700/60">
                                            
                                            <template x-for="(srv, srvIdx) in getFilteredServices(device)" :key="srv.id">
                                                <button type="button"
                                                        @click="toggleService(device, srv); device.serviceDropdownOpen = false; device.serviceSearch = ''"
                                                        @mouseenter="device.serviceHighlightedIndex = srvIdx"
                                                        :class="(device.serviceHighlightedIndex || 0) === srvIdx ? 'bg-indigo-50 dark:bg-indigo-950/60' : ''"
                                                        class="w-full text-left px-3 py-2 text-xs hover:bg-indigo-50 dark:hover:bg-indigo-950/60 flex items-center justify-between cursor-pointer transition">
                                                    <div>
                                                        <span class="font-bold text-gray-800 dark:text-gray-100" x-text="srv.name"></span>
                                                        <span class="text-[10px] text-gray-400 ml-1.5" x-text="'(' + (srv.category || 'Repair') + ')'"></span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400" x-text="'Rs. ' + Number(srv.default_labor_cost).toFixed(0)"></span>
                                                        <span x-show="isServiceSelected(device, srv.name)" class="text-[11px] text-emerald-600 font-bold">
                                                            Selected
                                                        </span>
                                                        <span x-show="!isServiceSelected(device, srv.name)" class="text-[11px] text-indigo-600 font-bold">
                                                            Select
                                                        </span>
                                                    </div>
                                                </button>
                                            </template>

                                            <template x-if="getFilteredServices(device).length === 0">
                                                <div class="p-3 text-center text-xs text-gray-500">
                                                    Koi service match nahi hui: "<strong class="text-indigo-600" x-text="device.serviceSearch"></strong>"
                                                    <div class="mt-1.5">
                                                        <button type="button" @click="quickServiceForm.name = device.serviceSearch; openQuickAddServiceModal(device); device.serviceDropdownOpen = false" class="px-3 py-1 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700">
                                                            + Nayi service banayein
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Selected Services Active Pills (Only shows what user selected) -->
                                    <template x-if="device.services && device.services.length > 0">
                                        <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                            <span class="text-[10px] font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-wider mr-1">Selected:</span>
                                            <template x-for="(s, sIdx) in device.services" :key="sIdx">
                                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 rounded-lg text-xs font-bold shadow-2xs">
                                                    <span x-text="s.name"></span>
                                                    <span class="font-mono text-[10px] text-emerald-600 dark:text-emerald-400" x-text="'(Rs. ' + (s.rate || 0) + ')'"></span>
                                                    <button type="button" @click="device.services.splice(sIdx, 1); recalculateDeviceServices(device)" class="text-emerald-500 hover:text-rose-600 text-xs font-black ml-0.5 cursor-pointer" title="Remove">&times;</button>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                <!-- Problem / Fault Summary & Labor Charges -->
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4">
                                    <div class="sm:col-span-8">
                                        <label class="block text-[11px] font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                            Fault / Problem Summary (Print Slip Par Ayega) <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="text" :name="'devices[' + dIdx + '][problem_description]'"
                                               x-model="device.problem"
                                               @input="if(device.errors) device.errors.problem = false"
                                               placeholder="Upar service select karein ya masla type karein..."
                                               :class="device.errors && device.errors.problem ? 'border-rose-500 ring-1 ring-rose-400 bg-rose-50/40 dark:bg-rose-950/20' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900'"
                                               class="w-full py-2 px-3 rounded-xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 transition">
                                        <span x-show="device.errors && device.errors.problem" x-cloak class="text-[10px] text-rose-500 font-bold block mt-1">⚠️ Masla likhein ya service select karein.</span>
                                    </div>

                                    <div class="sm:col-span-4">
                                        <label class="block text-[11px] font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                            Repair / Labor (Mazdoori Rs)
                                        </label>
                                        <input type="number" step="any" :name="'devices[' + dIdx + '][labor_cost]'" x-model.number="device.labor" placeholder="0" class="w-full py-2 px-3 bg-white dark:bg-gray-900 border border-amber-300 dark:border-amber-700 rounded-xl text-sm font-black font-mono text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <!-- Customer Risk & Approval Bar -->
                                <div class="p-3 rounded-xl border transition-all"
                                     :class="device.approval_type === 'approval' 
                                        ? 'bg-rose-50 dark:bg-rose-950/40 border-rose-300 dark:border-rose-700' 
                                        : 'bg-slate-50 dark:bg-gray-900/40 border-slate-200 dark:border-gray-700'">
                                    
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full" :class="device.approval_type === 'approval' ? 'bg-rose-600' : 'bg-emerald-500'"></span>
                                            <span class="text-xs font-black uppercase tracking-wider" :class="device.approval_type === 'approval' ? 'text-rose-700 dark:text-rose-300' : 'text-gray-700 dark:text-gray-300'">
                                                Customer Risk & Approval:
                                            </span>
                                        </div>

                                        <div class="inline-flex rounded-lg p-0.5 bg-gray-200 dark:bg-gray-800 gap-1 self-start sm:self-auto">
                                            <button type="button"
                                                    @click="device.approval_type = 'without_approval'"
                                                    :class="device.approval_type === 'without_approval' 
                                                        ? 'bg-white dark:bg-gray-700 text-emerald-700 dark:text-emerald-300 font-black shadow-xs' 
                                                        : 'text-gray-600 dark:text-gray-400 font-bold'"
                                                    class="px-2.5 py-1 text-xs rounded-md transition flex items-center gap-1 cursor-pointer">
                                                <span>✓ Normal (Without Approval)</span>
                                            </button>
                                            <button type="button"
                                                    @click="device.approval_type = 'approval'"
                                                    :class="device.approval_type === 'approval' 
                                                        ? 'bg-rose-600 text-white font-black shadow-xs' 
                                                        : 'text-gray-600 dark:text-gray-400 hover:text-rose-600 font-bold'"
                                                    class="px-2.5 py-1 text-xs rounded-md transition flex items-center gap-1 cursor-pointer">
                                                <span>⚠️ Approval (Customer Risk)</span>
                                            </button>
                                        </div>
                                        <input type="hidden" :name="'devices[' + dIdx + '][approval_type]'" :value="device.approval_type">
                                    </div>

                                    <div x-show="device.approval_type === 'approval'" class="mt-2 pt-2 border-t border-rose-200 dark:border-rose-800 space-y-1">
                                        <div class="text-[10px] text-rose-700 dark:text-rose-300 font-bold">
                                            Customer Risk: Slip par print hoga ke bante waqt dead/kharab hone par dukan zimmedar nahi hogi.
                                        </div>
                                        <input type="text"
                                               :name="'devices[' + dIdx + '][approval_notes]'"
                                               x-model="device.approval_notes"
                                               placeholder="Risk Note (e.g. Glass crack tha, display lines ka risk customer ka hai)..."
                                               class="w-full py-1.5 px-3 bg-white dark:bg-gray-800 border border-rose-300 dark:border-rose-700 rounded-lg text-xs text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-rose-500">
                                    </div>
                                </div>

                                <!-- Hidden fields -->
                                <input type="hidden" :name="'devices[' + dIdx + '][warranty_days]'" :value="device.warrantyDays || 0">
                                <input type="hidden" :name="'devices[' + dIdx + '][security_type]'" :value="device.securityType || 'none'">
                                <input type="hidden" :name="'devices[' + dIdx + '][security_code]'" :value="device.securityCode || ''">
                                <input type="hidden" :name="'devices[' + dIdx + '][imei]'" :value="device.imei || ''">
                                <input type="hidden" :name="'devices[' + dIdx + '][color]'" :value="device.color || ''">
                                <input type="hidden" :name="'devices[' + dIdx + '][technician_id]'" :value="device.technicianId || ''">
                            </div>
                        </template>
                    </div>

                </div>

                <!-- Right 4 Columns: Sticky Summary Card -->
                <div class="lg:col-span-4 sticky top-4 space-y-4">
                    
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border-2 border-amber-200 dark:border-gray-700 shadow-md space-y-4">
                        
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="w-5 h-5 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-[10px] font-black">3</span>
                                Updated Bill & Peshgi
                            </h3>
                            <span class="text-xs font-mono font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/60 px-2.5 py-0.5 rounded-lg border border-amber-200 dark:border-amber-800">
                                {{ $repair->order_no }}
                            </span>
                        </div>

                        <!-- Grand Total -->
                        <div class="p-4 rounded-xl bg-gradient-to-br from-slate-900 to-indigo-950 text-white space-y-1 shadow-sm">
                            <span class="text-[10px] uppercase tracking-wider text-slate-400 font-black">Grand Total (Kul Bill)</span>
                            <div class="text-2xl sm:text-3xl font-black font-mono text-emerald-400">
                                Rs <span x-text="calculateGrandTotal()"></span>
                            </div>
                        </div>

                        <!-- Advance Received -->
                        <div>
                            <label class="block text-[11px] font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                Advance Received (Peshgi Raqam)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400">Rs</span>
                                <input type="number" step="any" name="advance_amount" x-model.number="advanceAmount" placeholder="0" class="w-full py-2.5 pl-9 pr-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-base font-black text-emerald-600 dark:text-emerald-400 focus:ring-2 focus:ring-emerald-400 font-mono">
                            </div>
                        </div>

                        <!-- Baqaya / Remaining Balance -->
                        <div class="p-3 bg-slate-50 dark:bg-gray-900/60 rounded-xl border border-slate-200 dark:border-gray-700 flex items-center justify-between">
                            <span class="text-xs text-gray-600 dark:text-gray-400 font-bold uppercase tracking-wider">Remaining (Baqaya):</span>
                            <span class="text-lg font-black font-mono" :class="calculateBalance() > 0 ? 'text-rose-600' : 'text-emerald-600'">
                                Rs <span x-text="calculateBalance()"></span>
                            </span>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-[11px] font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                Payment Method
                            </label>
                            <select name="payment_method" class="w-full py-2 px-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-emerald-400">
                                <option value="cash" {{ $repair->payment_method === 'cash' ? 'selected' : '' }}>Cash Counter</option>
                                <option value="bank" {{ $repair->payment_method === 'bank' ? 'selected' : '' }}>Bank Transfer / Online</option>
                                <option value="jazzcash" {{ $repair->payment_method === 'jazzcash' ? 'selected' : '' }}>JazzCash / EasyPaisa</option>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <div class="space-y-2 pt-2">
                            <button type="submit" id="repairSubmitBtn" class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm rounded-xl shadow-md shadow-emerald-600/30 transition transform active:scale-95 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Update Order & Save</span>
                            </button>
                            <div class="text-center text-[10px] text-gray-400 font-semibold">
                                Shortkey: <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded font-mono">Ctrl + Enter</kbd> dabayein
                            </div>
                        </div>

                        <div class="text-center pt-1">
                            <a href="{{ route('repairs.show', $repair->id) }}" class="text-xs font-bold text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 transition">
                                Cancel (Wapas Jayein)
                            </a>
                        </div>
                    </div>

                </div>

            </div>

        </form>

        <!-- Quick Add Service Modal -->
        <div x-show="quickServiceModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-sm w-full p-5 shadow-2xl border border-gray-200 dark:border-gray-700 space-y-3" @click.away="quickServiceModalOpen = false">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-2.5">
                    <h3 class="font-black text-xs text-gray-900 dark:text-white">
                        Add New Repair Service
                    </h3>
                    <button type="button" @click="quickServiceModalOpen = false" class="text-gray-400 hover:text-gray-600 font-black text-base">&times;</button>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Service / Fault Name *</label>
                        <input type="text" x-model="quickServiceForm.name" placeholder="e.g. Camera Glass, Audio IC..." class="w-full py-1.5 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Default Labor Rate (Rs)</label>
                        <input type="number" step="any" x-model.number="quickServiceForm.rate" placeholder="500" class="w-full py-1.5 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" @click="quickServiceModalOpen = false" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-xl">
                        Cancel
                    </button>
                    <button type="button" @click="saveQuickService()" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs">
                        Save & Apply
                    </button>
                </div>
            </div>
        </div>

        <!-- Direct New Customer Modal -->
        <div x-show="quickCustomerModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-sm w-full p-5 shadow-2xl border border-gray-200 dark:border-gray-700 space-y-4" @click.away="quickCustomerModalOpen = false">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-2.5">
                    <h3 class="font-black text-xs sm:text-sm text-gray-900 dark:text-white">
                        Naya Customer (Direct Khata) Banayein
                    </h3>
                    <button type="button" @click="quickCustomerModalOpen = false" class="text-gray-400 hover:text-gray-600 font-black text-base">&times;</button>
                </div>

                <form @submit.prevent="saveQuickCustomer()" class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 dark:text-gray-300 mb-1">Customer Name *</label>
                        <input type="text" x-model="quickCustomerForm.name" required placeholder="e.g. Haji Aslam / Usman Ali" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 dark:text-gray-300 mb-1">Phone / WhatsApp Number</label>
                        <input type="text" x-model="quickCustomerForm.phone" placeholder="0300-1234567" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 dark:text-gray-300 mb-1">Address / Shop / City (Optional)</label>
                        <input type="text" x-model="quickCustomerForm.address" placeholder="e.g. Main Bazar, Shop #4" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-600 dark:text-gray-400 mb-1">Opening Balance</label>
                            <input type="number" step="any" min="0" x-model.number="quickCustomerForm.opening_balance" placeholder="0" class="w-full py-1.5 px-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-mono font-bold text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-600 dark:text-gray-400 mb-1">Type</label>
                            <select x-model="quickCustomerForm.balance_type" class="w-full py-1.5 px-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-semibold text-gray-900 dark:text-white">
                                <option value="debit">Lena Hai (Debit)</option>
                                <option value="credit">Dena Hai (Credit)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="quickCustomerModalOpen = false" class="px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-xl">
                            Cancel
                        </button>
                        <button type="submit" :disabled="savingCustomer" class="px-5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-xs flex items-center gap-1.5 disabled:opacity-50">
                            <span x-show="!savingCustomer">Save Customer</span>
                            <span x-show="savingCustomer">Bana rahe hain...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function repairEditForm() {
            const rawBrands = {!! json_encode($brands ?? []) !!};
            const rawInitialDevices = {!! json_encode($initialDevices ?? []) !!};

            const initialDevices = rawInitialDevices.map(d => {
                const foundBrand = rawBrands.find(b => b.name.toLowerCase() === (d.brand || '').trim().toLowerCase());
                return {
                    ...d,
                    approval_type: d.approvalType || 'without_approval',
                    approval_notes: d.approvalNotes || '',
                    brandOpen: false,
                    modelOpen: false,
                    selectedBrandId: foundBrand ? foundBrand.id : null,
                    serviceSearch: '',
                    serviceDropdownOpen: false,
                    serviceHighlightedIndex: 0,
                    services: Array.isArray(d.services) ? d.services : [],
                    errors: { model: false, problem: false }
                };
            });

            return {
                customersList: {!! json_encode($customers) !!},
                productsList: {!! json_encode($products) !!},
                brandsList: rawBrands,
                servicesList: {!! json_encode($services ?? []) !!},

                customerSearch: '{{ addslashes($repair->customer_name) }}',
                customerOpen: false,
                customerHighlightedIndex: 0,
                selectedPartyId: '{{ $repair->party_id }}',
                customerName: '{{ addslashes($repair->customer_name) }}',
                customerPhone: '{{ addslashes($repair->customer_phone) }}',

                advanceAmount: {{ (float) $repair->advance_amount }},
                devices: initialDevices.length ? initialDevices : [],
                errors: {
                    customerName: false,
                    customerPhone: false
                },
                formErrors: [],

                formatNumber(val) {
                    return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
                },

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

                    try {
                        const res = await fetch("{{ route('repairs.service.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                name: name,
                                category: 'General Repair',
                                default_labor_cost: rate
                            })
                        });
                        const data = await res.json();
                        if (data.success && data.service) {
                            if (!this.servicesList.find(s => s.id === data.service.id)) {
                                this.servicesList.push(data.service);
                            }
                            if (this.quickServiceTargetDevice) {
                                this.toggleService(this.quickServiceTargetDevice, data.service);
                            }
                            this.quickServiceModalOpen = false;
                            return;
                        }
                    } catch (e) {
                        console.error(e);
                    }
                    this.quickServiceModalOpen = false;
                },

                // Direct New Customer Modal State & Methods
                quickCustomerModalOpen: false,
                savingCustomer: false,
                quickCustomerForm: {
                    name: '',
                    phone: '',
                    address: '',
                    opening_balance: 0,
                    balance_type: 'debit'
                },

                openQuickCustomerModal(initialName = '') {
                    this.quickCustomerForm = {
                        name: initialName || this.customerName || this.customerSearch || '',
                        phone: this.customerPhone || '',
                        address: '',
                        opening_balance: 0,
                        balance_type: 'debit'
                    };
                    this.quickCustomerModalOpen = true;
                },

                async saveQuickCustomer() {
                    const name = (this.quickCustomerForm.name || '').trim();
                    if (!name) {
                        alert('Customer ka naam likhna lazmi hai!');
                        return;
                    }
                    this.savingCustomer = true;
                    try {
                        const isOffline = !navigator.onLine || (window.OfflineSync && !window.OfflineSync.isOnline);
                        if (isOffline && window.OfflineSync) {
                            const offCust = window.OfflineSync.saveOfflineCustomer(this.quickCustomerForm);
                            this.customersList.unshift(offCust);
                            this.selectCustomer(offCust);
                            this.quickCustomerModalOpen = false;
                            return;
                        }

                        const res = await fetch("{{ route('repairs.customer.quick-store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.quickCustomerForm)
                        });
                        const data = await res.json();
                        if (data.success && data.customer) {
                            this.customersList.unshift(data.customer);
                            this.selectCustomer(data.customer);
                            this.quickCustomerModalOpen = false;
                            return;
                        } else {
                            alert(data.message || 'Customer shamil nahi ho saka.');
                        }
                    } catch (err) {
                        if (window.OfflineSync) {
                            const offCust = window.OfflineSync.saveOfflineCustomer(this.quickCustomerForm);
                            this.customersList.unshift(offCust);
                            this.selectCustomer(offCust);
                            this.quickCustomerModalOpen = false;
                        } else {
                            alert('Error: ' + err.message);
                        }
                    } finally {
                        this.savingCustomer = false;
                    }
                },

                getFilteredServices(device) {
                    const q = (device.serviceSearch || '').trim().toLowerCase();
                    if (!q) return this.servicesList;
                    return this.servicesList.filter(s => {
                        const nameMatch = s.name && s.name.toLowerCase().includes(q);
                        const catMatch = s.category && s.category.toLowerCase().includes(q);
                        const isSelected = this.isServiceSelected(device, s.name);
                        return nameMatch || catMatch || isSelected;
                    });
                },

                selectHighlightedService(device) {
                    const filtered = this.getFilteredServices(device);
                    const idx = device.serviceHighlightedIndex || 0;
                    if (filtered.length > 0 && filtered[idx]) {
                        this.toggleService(device, filtered[idx]);
                        device.serviceSearch = '';
                        device.serviceDropdownOpen = false;
                        device.serviceHighlightedIndex = 0;
                    }
                },

                selectFirstMatchingService(device) {
                    const filtered = this.getFilteredServices(device);
                    if (filtered.length > 0) {
                        this.toggleService(device, filtered[0]);
                        device.serviceSearch = '';
                        device.serviceDropdownOpen = false;
                    }
                },

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

                addDevice() {
                    this.devices.push({
                        tempId: Date.now() + Math.random(),
                        id: null,
                        trackingNo: '',
                        brand: '',
                        brandOpen: false,
                        selectedBrandId: null,
                        model: '',
                        modelOpen: false,
                        imei: '',
                        color: '',
                        problem: '',
                        approval_type: 'without_approval',
                        approval_notes: '',
                        serviceSearch: '',
                        serviceDropdownOpen: false,
                        serviceHighlightedIndex: 0,
                        services: [],
                        securityType: 'none',
                        securityCode: '',
                        labor: 0,
                        technicianId: '',
                        warrantyDays: 0,
                        parts: [],
                        errors: { model: false, problem: false }
                    });
                },

                removeDevice(index) {
                    if (this.devices.length > 1) {
                        this.devices.splice(index, 1);
                    }
                },

                calculateDeviceParts(device) {
                    return (device.parts || []).reduce((sum, p) => sum + ((p.qty || 0) * (p.price || 0)), 0);
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
                        this.formErrors.push('Kam az kam 1 mobile hona lazmi hai.');
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
