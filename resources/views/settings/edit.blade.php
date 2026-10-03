<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-black text-gray-800 dark:text-gray-200 italic tracking-tighter uppercase">
                    System Settings</h3>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Global configurations for your
                    business</span>
            </div>

            @if (session('success'))
                <div
                    class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 dark:bg-green-900/30 dark:text-green-300 rounded-r-xl shadow-sm">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <!-- Left Column: Company Profile -->
                    <div class="lg:col-span-2 space-y-6">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div
                                class="p-6 border-b border-gray-50 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                                <h4 class="text-sm font-black uppercase tracking-widest text-gray-400">Company
                                    Information</h4>
                            </div>
                            <div class="p-8 space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <x-input-label for="company_name" value="Company Name"
                                            class="text-xs font-bold uppercase text-gray-500" />
                                        <x-text-input id="company_name" name="company_name" type="text"
                                            class="mt-1 block w-full border-gray-200 dark:border-gray-700 rounded-xl focus:ring-primary-500"
                                            :value="old('company_name', $setting->company_name)" required />
                                        <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                                    </div>
                                    <div>
                                        <x-input-label for="company_phone" value="Phone Number"
                                            class="text-xs font-bold uppercase text-gray-500" />
                                        <x-text-input id="company_phone" name="company_phone" type="text"
                                            class="mt-1 block w-full border-gray-200 dark:border-gray-700 rounded-xl focus:ring-primary-500"
                                            :value="old('company_phone', $setting->company_phone)" />
                                        <x-input-error class="mt-2" :messages="$errors->get('company_phone')" />
                                    </div>
                                    <div>
                                        <x-input-label for="company_email" value="Email Address"
                                            class="text-xs font-bold uppercase text-gray-500" />
                                        <x-text-input id="company_email" name="company_email" type="email"
                                            class="mt-1 block w-full border-gray-200 dark:border-gray-700 rounded-xl focus:ring-primary-500"
                                            :value="old('company_email', $setting->company_email)" />
                                        <x-input-error class="mt-2" :messages="$errors->get('company_email')" />
                                    </div>
                                    <div>
                                        <x-input-label for="company_logo" value="Company Logo"
                                            class="text-xs font-bold uppercase text-gray-500" />
                                        <input id="company_logo" name="company_logo" type="file"
                                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer" />
                                        <x-input-error class="mt-2" :messages="$errors->get('company_logo')" />
                                        @if ($setting->company_logo)
                                            <div class="mt-2 p-2 bg-gray-50 dark:bg-gray-900 rounded-xl inline-block">
                                                <img src="{{ asset($setting->company_logo) }}" alt="Logo"
                                                    class="h-12 w-auto">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <x-input-label for="company_address" value="Company Address"
                                        class="text-xs font-bold uppercase text-gray-500" />
                                    <textarea id="company_address" name="company_address" rows="3"
                                        class="mt-1 block w-full border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 italic">{{ old('company_address', $setting->company_address) }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('company_address')" />
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div
                                class="p-6 border-b border-gray-50 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                                <h4 class="text-sm font-black uppercase tracking-widest text-gray-400">Invoice Settings
                                </h4>
                            </div>
                            <div class="p-8 space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <x-input-label for="invoice_prefix" value="Invoice Prefix"
                                            class="text-xs font-bold uppercase text-gray-500" />
                                        <x-text-input id="invoice_prefix" name="invoice_prefix" type="text"
                                            class="mt-1 block w-full border-gray-200 dark:border-gray-700 rounded-xl focus:ring-primary-500"
                                            :value="old('invoice_prefix', $setting->invoice_prefix)" required />
                                        <x-input-error class="mt-2" :messages="$errors->get('invoice_prefix')" />
                                    </div>
                                </div>
                                <div>
                                    <x-input-label for="invoice_footer_note" value="Invoice Footer Note"
                                        class="text-xs font-bold uppercase text-gray-500" />
                                    <textarea id="invoice_footer_note" name="invoice_footer_note" rows="3"
                                        class="mt-1 block w-full border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 italic"
                                        placeholder="Add custom note for your invoices...">{{ old('invoice_footer_note', $setting->invoice_footer_note) }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('invoice_footer_note')" />
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-100 dark:border-gray-700">
                                    <div>
                                        <p class="text-sm font-bold text-gray-700 dark:text-gray-300">Show Sale Time on Invoice</p>
                                        <p class="text-[11px] text-gray-400 italic mt-0.5">Show time alongside date on invoices and bills</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="show_sale_time" id="show_sale_time" value="1"
                                            class="sr-only peer"
                                            {{ old('show_sale_time', $setting->show_sale_time) ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:peer-focus:ring-primary-600 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div
                                class="p-6 border-b border-gray-50 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                                <h4 class="text-sm font-black uppercase tracking-widest text-gray-400">Walk-In
                                    Customer Defaults</h4>
                            </div>
                            <div class="p-8 space-y-6">
                                <p class="text-[11px] text-gray-400 italic -mt-2">When this customer is selected on
                                    the Sale form, the stock source auto-selects to the warehouse below and the sale
                                    must be paid in full (no credit/balance due allowed).</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <x-input-label for="walk_in_customer_id" value="Walk-In Customer"
                                            class="text-xs font-bold uppercase text-gray-500" />
                                        <select id="walk_in_customer_id" name="walk_in_customer_id"
                                            class="mt-1 block w-full border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                                            <option value="">None</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}"
                                                    {{ old('walk_in_customer_id', $setting->walk_in_customer_id) == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }}</option>
                                            @endforeach
                                        </select>
                                        <x-input-error class="mt-2" :messages="$errors->get('walk_in_customer_id')" />
                                    </div>
                                    <div>
                                        <x-input-label for="shop_warehouse_id" value="Shop Warehouse"
                                            class="text-xs font-bold uppercase text-gray-500" />
                                        <select id="shop_warehouse_id" name="shop_warehouse_id"
                                            class="mt-1 block w-full border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                                            <option value="">None</option>
                                            @foreach ($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}"
                                                    {{ old('shop_warehouse_id', $setting->shop_warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                                    {{ $warehouse->name }}</option>
                                            @endforeach
                                        </select>
                                        <x-input-error class="mt-2" :messages="$errors->get('shop_warehouse_id')" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div
                                class="p-6 border-b border-gray-50 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                                <h4 class="text-sm font-black uppercase tracking-widest text-gray-400">WhatsApp
                                    Message Templates</h4>
                            </div>
                            <div class="p-8 space-y-6">
                                <div class="flex flex-wrap gap-2">
                                    @foreach (['{customer_name}', '{vendor_name}', '{party_name}', '{invoice_id}', '{date}', '{time}', '{status}', '{total}', '{discount}', '{previous_balance}', '{balance}', '{balance_due}', '{start_date}', '{end_date}', '{invoice_link}', '{ledger_link}', '{company_name}'] as $placeholder)
                                        <code
                                            class="px-2 py-1 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 rounded-lg text-[11px] font-bold">{{ $placeholder }}</code>
                                    @endforeach
                                </div>
                                <p class="text-[11px] text-gray-400 italic -mt-4">Use these placeholders anywhere in
                                    the templates below — they'll be replaced with the invoice's real values before
                                    sending.</p>
                                <div>
                                    <x-input-label for="whatsapp_sale_template" value="Sale Invoice Message"
                                        class="text-xs font-bold uppercase text-gray-500" />
                                    <textarea id="whatsapp_sale_template" name="whatsapp_sale_template" rows="6"
                                        class="mt-1 block w-full border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 font-mono text-sm">{{ old('whatsapp_sale_template', $setting->whatsapp_sale_template) }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('whatsapp_sale_template')" />
                                </div>
                                <div>
                                    <x-input-label for="whatsapp_purchase_template" value="Purchase Invoice Message"
                                        class="text-xs font-bold uppercase text-gray-500" />
                                    <textarea id="whatsapp_purchase_template" name="whatsapp_purchase_template" rows="6"
                                        class="mt-1 block w-full border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 font-mono text-sm">{{ old('whatsapp_purchase_template', $setting->whatsapp_purchase_template) }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('whatsapp_purchase_template')" />
                                </div>
                                <div>
                                    <x-input-label for="whatsapp_ledger_template" value="Party Ledger Message"
                                        class="text-xs font-bold uppercase text-gray-500" />
                                    <textarea id="whatsapp_ledger_template" name="whatsapp_ledger_template" rows="6"
                                        class="mt-1 block w-full border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500 font-mono text-sm">{{ old('whatsapp_ledger_template', $setting->whatsapp_ledger_template) }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('whatsapp_ledger_template')" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: App Settings -->
                    <div class="space-y-6">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div
                                class="p-6 border-b border-gray-50 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                                <h4 class="text-sm font-black uppercase tracking-widest text-gray-400">Localization &
                                    Alerts</h4>
                            </div>
                            <div class="p-8 space-y-6">
                                <div>
                                    <x-input-label for="timezone" value="Time Zone"
                                        class="text-xs font-bold uppercase text-gray-500" />
                                    <select id="timezone" name="timezone"
                                        class="mt-1 block w-full border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                                        <option value="Asia/Karachi"
                                            {{ old('timezone', $setting->timezone) == 'Asia/Karachi' ? 'selected' : '' }}>
                                            Pakistan (UTC+5)</option>
                                        <option value="UTC"
                                            {{ old('timezone', $setting->timezone) == 'UTC' ? 'selected' : '' }}>UTC
                                        </option>
                                        <option value="Asia/Dubai"
                                            {{ old('timezone', $setting->timezone) == 'Asia/Dubai' ? 'selected' : '' }}>
                                            Dubai (UTC+4)</option>
                                        <option value="Asia/Riyadh"
                                            {{ old('timezone', $setting->timezone) == 'Asia/Riyadh' ? 'selected' : '' }}>
                                            Riyadh (UTC+3)</option>
                                        <option value="Asia/Shanghai"
                                            {{ old('timezone', $setting->timezone) == 'Asia/Shanghai' ? 'selected' : '' }}>
                                            China (UTC+8)</option>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
                                </div>
                                <div>
                                    <x-input-label for="currency_symbol" value="Currency Symbol"
                                        class="text-xs font-bold uppercase text-gray-500" />
                                    <x-text-input id="currency_symbol" name="currency_symbol" type="text"
                                        class="mt-1 block w-full border-gray-200 dark:border-gray-700 rounded-xl focus:ring-primary-500"
                                        :value="old('currency_symbol', $setting->currency_symbol)" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('currency_symbol')" />
                                </div>
                                <div>
                                    <x-input-label for="low_stock_threshold" value="Low Stock Alert Threshold"
                                        class="text-xs font-bold uppercase text-gray-500" />
                                    <x-text-input id="low_stock_threshold" name="low_stock_threshold" type="number"
                                        class="mt-1 block w-full border-gray-200 dark:border-gray-700 rounded-xl focus:ring-primary-500"
                                        :value="old('low_stock_threshold', $setting->low_stock_threshold)" required />
                                    <p class="mt-1 text-[10px] text-gray-400 italic">Alert will trigger when stock
                                        falls below this quantity.</p>
                                    <x-input-error class="mt-2" :messages="$errors->get('low_stock_threshold')" />
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div
                                class="p-6 border-b border-gray-50 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                                <h4 class="text-sm font-black uppercase tracking-widest text-gray-400">System
                                    Maintenance</h4>
                            </div>
                            <div class="p-8 space-y-4">
                                <p class="text-[10px] text-gray-500 italic leading-relaxed">If the system shows old
                                    data or has performance issues, try clearing the cache.</p>
                                <div class="grid grid-cols-2 gap-4">
                                    <form action="{{ route('settings.clear-cache') }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="w-full inline-flex justify-center items-center px-4 py-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-xl font-bold text-xs text-orange-700 dark:text-orange-400 uppercase tracking-widest hover:bg-orange-100 transition duration-150">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                            Clear Cache
                                        </button>
                                    </form>

                                    <a href="{{ route('settings.backup-download') }}"
                                        class="w-full inline-flex justify-center items-center px-4 py-3 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-xl font-bold text-xs text-indigo-700 dark:text-indigo-400 uppercase tracking-widest hover:bg-indigo-100 transition duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                                            </path>
                                        </svg>
                                        Backup DB
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit"
                                class="w-full bg-primary-600 hover:bg-primary-700 text-white font-black py-4 px-6 rounded-2xl text-sm uppercase tracking-widest transition shadow-2xl shadow-primary-200 dark:shadow-none transform hover:-translate-y-1">
                                Save All Settings
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</x-app-layout>
