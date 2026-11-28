<x-app-layout>
    <div class="py-4 sm:py-6" x-data="bulkProductSheet({
        categories: {{ Js::from($categories) }},
        brands: {{ Js::from($brands) }},
        units: {{ Js::from($units) }},
        warehouses: {{ Js::from($warehouses) }},
        defaultCategory: '{{ $categories->first()->id ?? '' }}',
        defaultBrand: '{{ $brands->first()->id ?? '' }}',
        defaultUnit: '{{ $units->first()->id ?? '' }}',
        defaultWarehouse: '{{ $warehouses->first()->id ?? '' }}',
        currencySymbol: '{{ $settings->currency_symbol ?? 'Rs' }}'
    })"
    @keydown.window="handleGlobalKeys($event)">
        <div class="max-w-[1900px] mx-auto px-2 sm:px-4 lg:px-6">

            <!-- Header Card -->
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-2xl border border-gray-200 dark:border-gray-700 p-4 sm:p-5 mb-4">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-emerald-100 dark:bg-emerald-950/60 rounded-xl text-emerald-700 dark:text-emerald-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-black text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                    <span>Bulk Product Quick Entry</span>
                                    <span class="text-xs uppercase font-extrabold px-2.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                        Excel Sheet Mode
                                    </span>
                                </h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Quickly type or paste dozens of products. Use <kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-[10px] font-mono">Arrow Keys</kbd>, <kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-[10px] font-mono">Tab</kbd>, and <kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-[10px] font-mono">Enter</kbd> to move across cells. Paste from Excel directly with <kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-[10px] font-mono">Ctrl+V</kbd>.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="addRow()"
                            class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-bold uppercase tracking-wider transition">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span>Add Row</span>
                            <span class="ml-1.5 text-[10px] text-gray-400 font-mono">Alt+A</span>
                        </button>

                        <button type="button" @click="addRows(5)"
                            class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-bold uppercase tracking-wider transition">
                            <span>+5 Rows</span>
                        </button>

                        <button type="button" @click="generateAllMissingSkus()"
                            class="inline-flex items-center px-3 py-2 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded-lg text-xs font-bold uppercase tracking-wider transition">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            <span>Auto SKUs</span>
                        </button>

                        <button type="button" @click="clearEmptyRows()"
                            class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-bold uppercase tracking-wider transition"
                            title="Remove blank rows">
                            <span>Clean Blanks</span>
                        </button>

                        <a href="{{ route('products.index') }}"
                            class="inline-flex items-center px-3 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-bold uppercase tracking-wider transition">
                            <span>Cancel</span>
                        </a>

                        <button type="button" @click="submitForm()"
                            class="inline-flex items-center px-5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-lg text-xs font-black uppercase tracking-wider transition shadow-lg shadow-emerald-600/30">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Save All Products</span>
                            <span class="ml-2 px-1.5 py-0.5 bg-emerald-700 text-white rounded text-[10px] font-mono">Alt+S</span>
                        </button>
                    </div>
                </div>

                <!-- Default Row Presets Toolbar -->
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 bg-gray-50/70 dark:bg-gray-900/40 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-gray-500 tracking-wider mb-1">Preset Category</label>
                        <select x-model="defaults.category_id" @change="applyDefault('category_id')"
                            class="w-full text-xs font-semibold rounded-lg bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 py-1.5 focus:ring-emerald-500 focus:border-emerald-500 dark:text-gray-200">
                            <template x-for="c in categories" :key="c.id">
                                <option :value="c.id" x-text="c.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-gray-500 tracking-wider mb-1">Preset Brand</label>
                        <select x-model="defaults.brand_id" @change="applyDefault('brand_id')"
                            class="w-full text-xs font-semibold rounded-lg bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 py-1.5 focus:ring-emerald-500 focus:border-emerald-500 dark:text-gray-200">
                            <template x-for="b in brands" :key="b.id">
                                <option :value="b.id" x-text="b.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-gray-500 tracking-wider mb-1">Preset Unit</label>
                        <select x-model="defaults.unit_id" @change="applyDefault('unit_id')"
                            class="w-full text-xs font-semibold rounded-lg bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 py-1.5 focus:ring-emerald-500 focus:border-emerald-500 dark:text-gray-200">
                            <template x-for="u in units" :key="u.id">
                                <option :value="u.id" x-text="u.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase text-gray-500 tracking-wider mb-1">Preset Warehouse (Stock)</label>
                        <select x-model="defaults.warehouse_id" @change="applyDefault('warehouse_id')"
                            class="w-full text-xs font-semibold rounded-lg bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 py-1.5 focus:ring-emerald-500 focus:border-emerald-500 dark:text-gray-200">
                            <template x-for="w in warehouses" :key="w.id">
                                <option :value="w.id" x-text="w.name"></option>
                            </template>
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="button" @click="applyDefaultsToAll()"
                            class="w-full py-1.5 px-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg text-xs font-bold transition">
                            Apply Presets To All Rows
                        </button>
                    </div>
                </div>
            </div>

            <!-- Error Notifications -->
            @if ($errors->any())
                <div class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-950/50 border border-red-300 dark:border-red-800 text-red-800 dark:text-red-300 shadow-sm">
                    <div class="font-bold text-sm mb-1 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Validation Errors in Bulk Submission:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Spreadsheet Grid Container -->
            <form id="bulkForm" action="{{ route('products.bulk-store') }}" method="POST">
                @csrf
                <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl border border-gray-300 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto max-h-[72vh] overflow-y-auto" @paste="handlePaste($event)">
                        <table class="w-full border-collapse text-left text-xs">
                            <!-- Sticky Spreadsheet Headers -->
                            <thead class="sticky top-0 z-20 bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-200 uppercase font-black tracking-wider text-[11px] shadow-sm">
                                <tr class="divide-x divide-gray-300 dark:divide-gray-700 border-b-2 border-gray-300 dark:border-gray-700">
                                    <th class="w-12 px-2 py-3 text-center bg-gray-200/80 dark:bg-gray-800">#</th>
                                    <th class="min-w-[220px] px-3 py-3">Product Name <span class="text-red-500">*</span></th>
                                    <th class="min-w-[140px] px-3 py-3">SKU <span class="text-red-500">*</span></th>
                                    <th class="min-w-[130px] px-3 py-3">Barcode</th>
                                    <th class="min-w-[140px] px-3 py-3">Category <span class="text-red-500">*</span></th>
                                    <th class="min-w-[130px] px-3 py-3">Brand <span class="text-red-500">*</span></th>
                                    <th class="min-w-[100px] px-3 py-3">Unit <span class="text-red-500">*</span></th>
                                    <th class="min-w-[110px] px-3 py-3 text-right">Sale Price <span class="text-red-500">*</span></th>
                                    <th class="min-w-[110px] px-3 py-3 text-right">Wholesale Rate</th>
                                    <th class="min-w-[110px] px-3 py-3 text-right">Retail Rate</th>
                                    <th class="min-w-[110px] px-3 py-3 text-right">Dealer Rate</th>
                                    <th class="min-w-[95px] px-3 py-3 text-center bg-emerald-50 dark:bg-emerald-950/30">Opening Qty</th>
                                    <th class="min-w-[110px] px-3 py-3 text-right bg-emerald-50 dark:bg-emerald-950/30">Opening Cost</th>
                                    <th class="min-w-[130px] px-3 py-3 bg-emerald-50 dark:bg-emerald-950/30">Warehouse</th>
                                    <th class="min-w-[85px] px-2 py-3 text-center">Alert</th>
                                    <th class="w-10 px-2 py-3 text-center bg-gray-100 dark:bg-gray-900">Del</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                <template x-for="(row, rIndex) in rows" :key="row._key">
                                    <tr class="divide-x divide-gray-200 dark:divide-gray-700 hover:bg-emerald-50/20 dark:hover:bg-gray-700/30 transition-colors"
                                        :class="{ 'bg-emerald-50/40 dark:bg-emerald-950/20': row.name && row.sku }">
                                        
                                        <!-- Row Number -->
                                        <td class="px-2 py-1 text-center font-mono font-bold text-gray-400 bg-gray-50/70 dark:bg-gray-900/50" x-text="rIndex + 1"></td>

                                        <!-- 1. Product Name -->
                                        <td class="p-0">
                                            <input type="text" :name="`rows[${rIndex}][name]`" x-model="row.name"
                                                @keydown="handleCellKey($event, rIndex, 0)"
                                                placeholder="e.g. Type-C Fast Charger 65W"
                                                :data-cell="`${rIndex}-0`"
                                                class="w-full h-full px-2.5 py-2 text-xs font-medium border-0 focus:ring-2 focus:ring-emerald-500 focus:bg-white dark:focus:bg-gray-900 bg-transparent dark:text-gray-100 placeholder-gray-400">
                                        </td>

                                        <!-- 2. SKU -->
                                        <td class="p-0">
                                            <div class="relative flex items-center">
                                                <input type="text" :name="`rows[${rIndex}][sku]`" x-model="row.sku"
                                                    @keydown="handleCellKey($event, rIndex, 1)"
                                                    placeholder="SKU-XXXX"
                                                    :data-cell="`${rIndex}-1`"
                                                    class="w-full h-full px-2.5 py-2 pr-7 text-xs font-mono font-bold border-0 focus:ring-2 focus:ring-emerald-500 focus:bg-white dark:focus:bg-gray-900 bg-transparent text-emerald-700 dark:text-emerald-400 placeholder-gray-400">
                                                <button type="button" @click="generateRowSku(rIndex)"
                                                    title="Auto Generate SKU"
                                                    tabindex="-1"
                                                    class="absolute right-1 text-gray-400 hover:text-emerald-600 p-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                </button>
                                            </div>
                                        </td>

                                        <!-- 3. Barcode -->
                                        <td class="p-0">
                                            <input type="text" :name="`rows[${rIndex}][barcode]`" x-model="row.barcode"
                                                @keydown="handleCellKey($event, rIndex, 2)"
                                                placeholder="Scan or leave empty"
                                                :data-cell="`${rIndex}-2`"
                                                class="w-full h-full px-2.5 py-2 text-xs font-mono border-0 focus:ring-2 focus:ring-emerald-500 focus:bg-white dark:focus:bg-gray-900 bg-transparent dark:text-gray-200 placeholder-gray-400">
                                        </td>

                                        <!-- 4. Category -->
                                        <td class="p-0">
                                            <select :name="`rows[${rIndex}][category_id]`" x-model="row.category_id"
                                                @keydown="handleCellKey($event, rIndex, 3)"
                                                :data-cell="`${rIndex}-3`"
                                                class="w-full h-full px-2 py-2 text-xs border-0 focus:ring-2 focus:ring-emerald-500 focus:bg-white dark:focus:bg-gray-900 bg-transparent dark:text-gray-200 font-semibold">
                                                <template x-for="c in categories" :key="c.id">
                                                    <option :value="c.id" x-text="c.name" :selected="c.id == row.category_id"></option>
                                                </template>
                                            </select>
                                        </td>

                                        <!-- 5. Brand -->
                                        <td class="p-0">
                                            <select :name="`rows[${rIndex}][brand_id]`" x-model="row.brand_id"
                                                @keydown="handleCellKey($event, rIndex, 4)"
                                                :data-cell="`${rIndex}-4`"
                                                class="w-full h-full px-2 py-2 text-xs border-0 focus:ring-2 focus:ring-emerald-500 focus:bg-white dark:focus:bg-gray-900 bg-transparent dark:text-gray-200 font-semibold">
                                                <template x-for="b in brands" :key="b.id">
                                                    <option :value="b.id" x-text="b.name" :selected="b.id == row.brand_id"></option>
                                                </template>
                                            </select>
                                        </td>

                                        <!-- 6. Unit -->
                                        <td class="p-0">
                                            <select :name="`rows[${rIndex}][unit_id]`" x-model="row.unit_id"
                                                @keydown="handleCellKey($event, rIndex, 5)"
                                                :data-cell="`${rIndex}-5`"
                                                class="w-full h-full px-2 py-2 text-xs border-0 focus:ring-2 focus:ring-emerald-500 focus:bg-white dark:focus:bg-gray-900 bg-transparent dark:text-gray-200 font-semibold">
                                                <template x-for="u in units" :key="u.id">
                                                    <option :value="u.id" x-text="u.name" :selected="u.id == row.unit_id"></option>
                                                </template>
                                            </select>
                                        </td>

                                        <!-- 7. Sale Price -->
                                        <td class="p-0">
                                            <input type="number" step="0.01" min="0" :name="`rows[${rIndex}][sale_price]`" x-model="row.sale_price"
                                                @keydown="handleCellKey($event, rIndex, 6)"
                                                placeholder="0.00"
                                                :data-cell="`${rIndex}-6`"
                                                class="w-full h-full px-2.5 py-2 text-xs text-right font-mono font-bold text-gray-900 dark:text-gray-100 border-0 focus:ring-2 focus:ring-emerald-500 focus:bg-white dark:focus:bg-gray-900 bg-transparent">
                                        </td>

                                        <!-- 8. Wholesale Rate -->
                                        <td class="p-0">
                                            <input type="number" step="0.01" min="0" :name="`rows[${rIndex}][wholesale_price]`" x-model="row.wholesale_price"
                                                @keydown="handleCellKey($event, rIndex, 7)"
                                                placeholder="="
                                                :data-cell="`${rIndex}-7`"
                                                class="w-full h-full px-2.5 py-2 text-xs text-right font-mono border-0 focus:ring-2 focus:ring-emerald-500 focus:bg-white dark:focus:bg-gray-900 bg-transparent dark:text-gray-200">
                                        </td>

                                        <!-- 9. Retail Rate -->
                                        <td class="p-0">
                                            <input type="number" step="0.01" min="0" :name="`rows[${rIndex}][retail_price]`" x-model="row.retail_price"
                                                @keydown="handleCellKey($event, rIndex, 8)"
                                                placeholder="="
                                                :data-cell="`${rIndex}-8`"
                                                class="w-full h-full px-2.5 py-2 text-xs text-right font-mono border-0 focus:ring-2 focus:ring-emerald-500 focus:bg-white dark:focus:bg-gray-900 bg-transparent dark:text-gray-200">
                                        </td>

                                        <!-- 10. Dealer Rate -->
                                        <td class="p-0">
                                            <input type="number" step="0.01" min="0" :name="`rows[${rIndex}][dealer_price]`" x-model="row.dealer_price"
                                                @keydown="handleCellKey($event, rIndex, 9)"
                                                placeholder="="
                                                :data-cell="`${rIndex}-9`"
                                                class="w-full h-full px-2.5 py-2 text-xs text-right font-mono border-0 focus:ring-2 focus:ring-emerald-500 focus:bg-white dark:focus:bg-gray-900 bg-transparent dark:text-gray-200">
                                        </td>

                                        <!-- 11. Opening Stock Qty -->
                                        <td class="p-0 bg-emerald-50/20 dark:bg-emerald-950/10">
                                            <input type="number" step="1" min="0" :name="`rows[${rIndex}][opening_stock_qty]`" x-model="row.opening_stock_qty"
                                                @keydown="handleCellKey($event, rIndex, 10)"
                                                placeholder="0"
                                                :data-cell="`${rIndex}-10`"
                                                class="w-full h-full px-2 py-2 text-xs text-center font-mono font-bold text-emerald-800 dark:text-emerald-300 border-0 focus:ring-2 focus:ring-emerald-500 focus:bg-white dark:focus:bg-gray-900 bg-transparent">
                                        </td>

                                        <!-- 12. Opening Stock Rate / Cost -->
                                        <td class="p-0 bg-emerald-50/20 dark:bg-emerald-950/10">
                                            <input type="number" step="0.01" min="0" :name="`rows[${rIndex}][opening_stock_rate]`" x-model="row.opening_stock_rate"
                                                @keydown="handleCellKey($event, rIndex, 11)"
                                                placeholder="Rate"
                                                :data-cell="`${rIndex}-11`"
                                                class="w-full h-full px-2 py-2 text-xs text-right font-mono border-0 focus:ring-2 focus:ring-emerald-500 focus:bg-white dark:focus:bg-gray-900 bg-transparent dark:text-gray-200">
                                        </td>

                                        <!-- 13. Warehouse -->
                                        <td class="p-0 bg-emerald-50/20 dark:bg-emerald-950/10">
                                            <select :name="`rows[${rIndex}][warehouse_id]`" x-model="row.warehouse_id"
                                                @keydown="handleCellKey($event, rIndex, 12)"
                                                :data-cell="`${rIndex}-12`"
                                                class="w-full h-full px-2 py-2 text-xs border-0 focus:ring-2 focus:ring-emerald-500 focus:bg-white dark:focus:bg-gray-900 bg-transparent dark:text-gray-200">
                                                <template x-for="w in warehouses" :key="w.id">
                                                    <option :value="w.id" x-text="w.name" :selected="w.id == row.warehouse_id"></option>
                                                </template>
                                            </select>
                                        </td>

                                        <!-- 14. Alert Quantity -->
                                        <td class="p-0">
                                            <input type="number" min="0" :name="`rows[${rIndex}][alert_quantity]`" x-model="row.alert_quantity"
                                                @keydown="handleCellKey($event, rIndex, 13)"
                                                :data-cell="`${rIndex}-13`"
                                                class="w-full h-full px-2 py-2 text-xs text-center font-mono border-0 focus:ring-2 focus:ring-emerald-500 focus:bg-white dark:focus:bg-gray-900 bg-transparent text-gray-500">
                                        </td>

                                        <!-- 15. Delete Row Button -->
                                        <td class="px-1 py-1 text-center bg-gray-50/40 dark:bg-gray-900/30">
                                            <button type="button" @click="removeRow(rIndex)"
                                                tabindex="-1"
                                                title="Delete Row"
                                                class="p-1 text-gray-400 hover:text-red-500 rounded transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Summary & Quick Action Bar -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/90 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-4 text-xs">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">Total Rows:</span>
                                <span class="font-black text-gray-900 dark:text-gray-100 ml-1 text-sm" x-text="rows.length"></span>
                            </div>
                            <div class="h-4 w-px bg-gray-300 dark:bg-gray-700"></div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">Valid Products:</span>
                                <span class="font-black text-emerald-600 dark:text-emerald-400 ml-1 text-sm" x-text="validProductCount()"></span>
                            </div>
                            <div class="h-4 w-px bg-gray-300 dark:bg-gray-700"></div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider">Est. Opening Stock:</span>
                                <span class="font-black text-indigo-600 dark:text-indigo-400 ml-1 text-sm">
                                    <span x-text="currencySymbol"></span> <span x-text="calculateTotalOpeningValuation()"></span>
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="addRow()"
                                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg text-xs font-bold uppercase tracking-wider transition">
                                + Add Next Row (Enter)
                            </button>

                            <button type="button" @click="submitForm()"
                                class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-black uppercase tracking-wider transition shadow-md">
                                Save Products (Alt+S)
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Alpine.js Spreadsheet Component Logic -->
    <script>
        function bulkProductSheet(config) {
            return {
                categories: config.categories,
                brands: config.brands,
                units: config.units,
                warehouses: config.warehouses,
                currencySymbol: config.currencySymbol,
                defaults: {
                    category_id: config.defaultCategory,
                    brand_id: config.defaultBrand,
                    unit_id: config.defaultUnit,
                    warehouse_id: config.defaultWarehouse,
                },
                rows: [],
                TOTAL_COLUMNS: 14, // 0 to 13

                init() {
                    // Populate initial 8 empty rows
                    for (let i = 0; i < 8; i++) {
                        this.addRow(false);
                    }
                    this.$nextTick(() => {
                        this.focusCell(0, 0);
                    });
                },

                createRowObject() {
                    return {
                        _key: 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6),
                        name: '',
                        sku: '',
                        barcode: '',
                        category_id: this.defaults.category_id,
                        brand_id: this.defaults.brand_id,
                        unit_id: this.defaults.unit_id,
                        sale_price: '',
                        wholesale_price: '',
                        retail_price: '',
                        dealer_price: '',
                        opening_stock_qty: '',
                        opening_stock_rate: '',
                        warehouse_id: this.defaults.warehouse_id,
                        alert_quantity: 5,
                    };
                },

                addRow(autoFocus = true) {
                    const newRow = this.createRowObject();
                    this.rows.push(newRow);
                    if (autoFocus) {
                        this.$nextTick(() => {
                            this.focusCell(this.rows.length - 1, 0);
                        });
                    }
                },

                addRows(count = 5) {
                    for (let i = 0; i < count; i++) {
                        this.rows.push(this.createRowObject());
                    }
                },

                removeRow(index) {
                    if (this.rows.length > 1) {
                        this.rows.splice(index, 1);
                    } else {
                        // Reset the only row
                        this.rows[0] = this.createRowObject();
                    }
                },

                clearEmptyRows() {
                    const cleaned = this.rows.filter(r => r.name.trim() !== '');
                    if (cleaned.length === 0) {
                        this.rows = [this.createRowObject()];
                    } else {
                        this.rows = cleaned;
                    }
                },

                applyDefault(field) {
                    const val = this.defaults[field];
                    this.rows.forEach(r => {
                        if (!r[field]) {
                            r[field] = val;
                        }
                    });
                },

                applyDefaultsToAll() {
                    this.rows.forEach(r => {
                        r.category_id = this.defaults.category_id;
                        r.brand_id = this.defaults.brand_id;
                        r.unit_id = this.defaults.unit_id;
                        r.warehouse_id = this.defaults.warehouse_id;
                    });
                },

                generateRowSku(index) {
                    const row = this.rows[index];
                    const prefix = row.name ? row.name.replace(/[^a-zA-Z0-9]/g, '').substr(0, 3).toUpperCase() : 'PRD';
                    const rand = Math.floor(1000 + Math.random() * 9000);
                    row.sku = (prefix || 'SKU') + '-' + rand;
                },

                generateAllMissingSkus() {
                    this.rows.forEach((r, idx) => {
                        if (r.name.trim() !== '' && !r.sku.trim()) {
                            this.generateRowSku(idx);
                        }
                    });
                },

                focusCell(rowIndex, colIndex) {
                    const el = document.querySelector(`[data-cell="${rowIndex}-${colIndex}"]`);
                    if (el) {
                        el.focus();
                        if (el.select) el.select();
                    }
                },

                handleCellKey(e, rowIndex, colIndex) {
                    // ArrowDown: Move to same column in row below
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (rowIndex < this.rows.length - 1) {
                            this.focusCell(rowIndex + 1, colIndex);
                        } else {
                            this.addRow();
                            this.$nextTick(() => this.focusCell(rowIndex + 1, colIndex));
                        }
                        return;
                    }

                    // ArrowUp: Move to same column in row above
                    if (e.key === 'ArrowUp') {
                        if (rowIndex > 0) {
                            e.preventDefault();
                            this.focusCell(rowIndex - 1, colIndex);
                        }
                        return;
                    }

                    // Enter: Move down or create new row at end
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (rowIndex === this.rows.length - 1) {
                            this.addRow();
                            this.$nextTick(() => this.focusCell(rowIndex + 1, 0));
                        } else {
                            this.focusCell(rowIndex + 1, colIndex);
                        }
                        return;
                    }

                    // Tab at last cell of row -> wrap to first cell of next row
                    if (e.key === 'Tab' && !e.shiftKey && colIndex === this.TOTAL_COLUMNS - 1) {
                        if (rowIndex === this.rows.length - 1) {
                            e.preventDefault();
                            this.addRow();
                            this.$nextTick(() => this.focusCell(rowIndex + 1, 0));
                        }
                    }
                },

                handleGlobalKeys(e) {
                    // Alt + S -> Submit Form
                    if (e.altKey && (e.key.toLowerCase() === 's')) {
                        e.preventDefault();
                        this.submitForm();
                        return;
                    }

                    // Alt + A -> Add Row
                    if (e.altKey && (e.key.toLowerCase() === 'a')) {
                        e.preventDefault();
                        this.addRow();
                        return;
                    }
                },

                handlePaste(e) {
                    const clipboardData = e.clipboardData || window.clipboardData;
                    if (!clipboardData) return;

                    const text = clipboardData.getData('text');
                    if (!text || (!text.includes('\t') && !text.includes('\n'))) {
                        return; // Normal text paste, allow default
                    }

                    e.preventDefault();

                    const activeEl = document.activeElement;
                    let startRow = 0;
                    let startCol = 0;

                    if (activeEl && activeEl.dataset && activeEl.dataset.cell) {
                        const parts = activeEl.dataset.cell.split('-');
                        startRow = parseInt(parts[0], 10);
                        startCol = parseInt(parts[1], 10);
                    }

                    const lines = text.split(/\r\n|\n|\r/).filter(line => line.length > 0);
                    if (lines.length === 0) return;

                    lines.forEach((line, rOffset) => {
                        const targetRowIdx = startRow + rOffset;
                        while (this.rows.length <= targetRowIdx) {
                            this.rows.push(this.createRowObject());
                        }

                        const cells = line.split('\t');
                        const row = this.rows[targetRowIdx];

                        // Mapping columns from current startCol
                        cells.forEach((val, cOffset) => {
                            const targetColIdx = startCol + cOffset;
                            const trimmed = val.trim();

                            switch (targetColIdx) {
                                case 0: row.name = trimmed; break;
                                case 1: row.sku = trimmed; break;
                                case 2: row.barcode = trimmed; break;
                                case 3: 
                                    // Match Category Name or ID
                                    const cat = this.categories.find(c => c.name.toLowerCase() === trimmed.toLowerCase() || c.id == trimmed);
                                    if (cat) row.category_id = cat.id;
                                    break;
                                case 4:
                                    const br = this.brands.find(b => b.name.toLowerCase() === trimmed.toLowerCase() || b.id == trimmed);
                                    if (br) row.brand_id = br.id;
                                    break;
                                case 5:
                                    const un = this.units.find(u => u.name.toLowerCase() === trimmed.toLowerCase() || u.id == trimmed);
                                    if (un) row.unit_id = un.id;
                                    break;
                                case 6: row.sale_price = trimmed; break;
                                case 7: row.wholesale_price = trimmed; break;
                                case 8: row.retail_price = trimmed; break;
                                case 9: row.dealer_price = trimmed; break;
                                case 10: row.opening_stock_qty = trimmed; break;
                                case 11: row.opening_stock_rate = trimmed; break;
                                case 12:
                                    const wh = this.warehouses.find(w => w.name.toLowerCase() === trimmed.toLowerCase() || w.id == trimmed);
                                    if (wh) row.warehouse_id = wh.id;
                                    break;
                                case 13: row.alert_quantity = trimmed; break;
                            }
                        });
                    });

                    this.$nextTick(() => {
                        this.focusCell(startRow, startCol);
                    });
                },

                validProductCount() {
                    return this.rows.filter(r => r.name && r.name.trim() !== '').length;
                },

                calculateTotalOpeningValuation() {
                    let total = 0;
                    this.rows.forEach(r => {
                        const qty = parseFloat(r.opening_stock_qty) || 0;
                        const rate = parseFloat(r.opening_stock_rate) || parseFloat(r.wholesale_price) || parseFloat(r.sale_price) || 0;
                        if (qty > 0 && rate > 0) {
                            total += (qty * rate);
                        }
                    });
                    return total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                submitForm() {
                    const validRows = this.rows.filter(r => r.name.trim() !== '');
                    if (validRows.length === 0) {
                        alert('Please enter at least one product name before saving.');
                        return;
                    }

                    // Check for missing SKUs
                    const missingSku = validRows.some(r => !r.sku || !r.sku.trim());
                    if (missingSku) {
                        if (confirm('Some products do not have SKUs. Would you like to automatically generate unique SKUs for them?')) {
                            this.generateAllMissingSkus();
                        } else {
                            return;
                        }
                    }

                    // Check for missing Sale Prices
                    const missingPrice = validRows.some(r => !r.sale_price || parseFloat(r.sale_price) < 0);
                    if (missingPrice) {
                        alert('Please provide a valid Sale Price for all entered products.');
                        return;
                    }

                    document.getElementById('bulkForm').submit();
                }
            };
        }
    </script>
</x-app-layout>