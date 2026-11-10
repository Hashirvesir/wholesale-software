@php
    $categories = \App\Models\Category::all(['id', 'name']);
    $units = \App\Models\Unit::all(['id', 'name']);
@endphp

<!-- Invenco AI Bill / Invoice OCR Scanner Modal -->
<div x-data="aiBillScanner(@js($categories), @js($units))"
     @open-bill-scanner.window="openModal()"
     class="select-none">

    <!-- Modal Backdrop -->
    <div x-show="modalOpen" x-cloak
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/80 backdrop-blur-xs z-50 flex items-center justify-center p-2 sm:p-4 md:p-6 overflow-y-auto">

        <!-- Modal Dialog -->
        <div @click.away="if (!scanning && !creatingProducts) modalOpen = false"
            class="bg-white dark:bg-gray-800 rounded-2xl max-w-4xl w-full shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden transform transition-all my-4">

            <!-- Modal Header -->
            <div class="px-5 py-3.5 bg-gradient-to-r from-violet-600 to-indigo-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center shadow-inner">
                        <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-black text-sm uppercase tracking-wide">AI Bill & PDF Scanner with Auto-Product Creator</h3>
                        <p class="text-[11px] text-indigo-100">Upload vendor bill image or PDF invoice: auto-detect, review & create missing products</p>
                    </div>
                </div>

                <button type="button" @click="modalOpen = false" :disabled="scanning || creatingProducts"
                    class="text-indigo-100 hover:text-white text-xl font-black p-1 leading-none cursor-pointer">&times;</button>
            </div>

            <!-- Modal Body -->
            <div class="p-4 sm:p-5 space-y-4 max-h-[78vh] overflow-y-auto">

                <!-- Upload Dropzone (When no image/pdf selected yet) -->
                <div x-show="!imagePreview && !scannedData"
                    class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl p-8 sm:p-10 text-center hover:border-indigo-500 dark:hover:border-indigo-400 transition cursor-pointer bg-gray-50/50 dark:bg-gray-900/30"
                    @click="$refs.billFileInput.click()"
                    @dragover.prevent=""
                    @drop.prevent="handleDrop($event)">

                    <input type="file" x-ref="billFileInput" accept="image/*,application/pdf,.pdf" capture="environment" class="hidden"
                        @change="handleFileSelect($event)" />

                    <div class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto mb-3 shadow-inner">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <div class="font-black text-sm text-gray-800 dark:text-gray-200">
                        Click to upload Bill Photo or PDF invoice (or drag & drop here)
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">
                        Supports <strong>JPG, PNG, WEBP & PDF</strong> up to 15MB. Scans handwritten & printed vendor bills.
                    </p>
                </div>

                <!-- File Selected & Scanning Preview State -->
                <div x-show="imagePreview && !scannedData" class="space-y-4">
                    <div class="relative max-h-72 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-900 flex items-center justify-center p-2">
                        <template x-if="isPdfFile">
                            <div class="py-12 text-center text-white">
                                <svg class="w-16 h-16 mx-auto text-rose-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="font-bold text-sm block" x-text="fileName"></span>
                                <span class="text-xs text-gray-400">PDF Invoice Document Selected</span>
                            </div>
                        </template>

                        <template x-if="!isPdfFile">
                            <img :src="imagePreview" alt="Bill Preview" class="max-h-72 max-w-full object-contain" />
                        </template>

                        <!-- Laser Scanning Animation Line -->
                        <div x-show="scanning" class="absolute inset-x-0 h-1 bg-gradient-to-r from-transparent via-emerald-400 to-transparent shadow-[0_0_15px_#34d399] animate-pulse"
                            style="animation: scanLaser 2s infinite alternate;"></div>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <button type="button" @click="resetImage()" :disabled="scanning"
                            class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                            Change File
                        </button>

                        <button type="button" @click="processScan()" :disabled="scanning"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white font-black text-xs uppercase tracking-wider transition shadow-lg flex items-center gap-2 cursor-pointer disabled:opacity-50">
                            <template x-if="scanning">
                                <span class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <span>AI Scanning & Matching with System DB...</span>
                                </span>
                            </template>
                            <template x-if="!scanning">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <span>Start AI Scan Now</span>
                                </span>
                            </template>
                        </button>
                    </div>

                    <div x-show="errorMessage" class="p-3 bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 text-xs font-bold rounded-xl" x-text="errorMessage"></div>
                </div>

                <!-- Scanned Results Preview & Auto-Creation Table -->
                <div x-show="scannedData" class="space-y-4">
                    <!-- Top Summary Card with Stats -->
                    <div class="p-4 rounded-xl bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-gray-900 dark:to-gray-800 border border-indigo-100 dark:border-gray-700 grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs shadow-xs">
                        <div>
                            <span class="text-[10px] font-black uppercase text-gray-500 dark:text-gray-400 block">Vendor / Supplier</span>
                            <div class="font-black text-gray-900 dark:text-gray-100 truncate text-sm" x-text="scannedData?.supplier?.raw_name || 'Counter Purchase'"></div>
                            <template x-if="scannedData?.supplier?.matched_id">
                                <span class="inline-block px-1.5 py-0.5 bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 rounded text-[10px] font-bold mt-0.5">
                                    ✓ Matched: <span x-text="scannedData.supplier.matched_name"></span>
                                </span>
                            </template>
                        </div>

                        <div>
                            <span class="text-[10px] font-black uppercase text-gray-500 dark:text-gray-400 block">Invoice / Date</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 text-xs" x-text="(scannedData?.invoice_number || 'N/A') + ' | ' + (scannedData?.date || '')"></span>
                        </div>

                        <div>
                            <span class="text-[10px] font-black uppercase text-gray-500 dark:text-gray-400 block">Bill Grand Total</span>
                            <span class="font-black text-emerald-600 dark:text-emerald-400 text-sm font-mono" x-text="'Rs. ' + Number(scannedData?.grand_total || 0).toLocaleString(undefined, {minimumFractionDigits: 2})"></span>
                        </div>

                        <!-- Product Match Health Chips -->
                        <div class="flex flex-col justify-center gap-1">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 w-fit">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span x-text="matchedCount() + ' Existing Products'"></span>
                            </span>
                            <template x-if="newCount() > 0">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 w-fit animate-pulse">
                                    <span>✨</span>
                                    <span x-text="newCount() + ' New Products to Auto-Create'"></span>
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Review Table Header Notice -->
                    <div class="flex items-center justify-between text-xs px-1">
                        <span class="font-bold text-gray-700 dark:text-gray-300">
                            Review Bill Items (Items not found in system will be automatically registered into Database):
                        </span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">
                            Total Items: <strong x-text="scannedData?.items?.length || 0"></strong>
                        </span>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-xl shadow-xs">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                            <thead class="bg-gray-100 dark:bg-gray-700/80 text-gray-600 dark:text-gray-300 font-bold uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="px-3 py-2.5 text-left">Bill Item Name</th>
                                    <th class="px-3 py-2.5 text-left">System Status</th>
                                    <th class="px-3 py-2.5 text-right w-16">Qty</th>
                                    <th class="px-3 py-2.5 text-right w-24">Purchase Rate</th>
                                    <th class="px-3 py-2.5 text-right w-24">Sale Price</th>
                                    <th class="px-3 py-2.5 text-right w-24">Total</th>
                                    <th class="px-2 py-2.5 text-center w-10">Del</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                <template x-for="(item, idx) in scannedData?.items || []" :key="idx">
                                    <tr :class="item.is_new ? 'bg-purple-50/40 dark:bg-purple-950/20' : ''">
                                        <!-- Item Name -->
                                        <td class="px-3 py-2">
                                            <template x-if="!item.is_new">
                                                <span class="font-bold text-gray-900 dark:text-gray-100 block" x-text="item.raw_name"></span>
                                            </template>
                                            <template x-if="item.is_new">
                                                <div>
                                                    <input type="text" x-model="item.raw_name"
                                                        class="w-full text-xs font-bold rounded-lg border-purple-300 dark:border-purple-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-2 py-1 focus:ring-purple-500" />
                                                    <span class="text-[10px] text-purple-600 dark:text-purple-400 font-bold block mt-0.5">Editable Product Name</span>
                                                </div>
                                            </template>
                                        </td>

                                        <!-- System Match Status -->
                                        <td class="px-3 py-2">
                                            <template x-if="!item.is_new">
                                                <div>
                                                    <span class="font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                        <span x-text="item.matched_product_name"></span>
                                                    </span>
                                                    <span class="text-[10px] text-gray-400 font-mono block" x-text="'Stock: ' + item.current_stock + ' | SKU: ' + (item.matched_product_sku || 'N/A')"></span>
                                                </div>
                                            </template>

                                            <template x-if="item.is_new">
                                                <div class="space-y-1">
                                                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                                        <input type="checkbox" x-model="item.auto_create" class="rounded text-purple-600 focus:ring-purple-500" />
                                                        <span class="text-[11px] font-black text-purple-700 dark:text-purple-300 uppercase tracking-tight">
                                                            ✨ Naya Product (Auto-Create)
                                                        </span>
                                                    </label>

                                                    <div class="flex items-center gap-1">
                                                        <!-- Category Select -->
                                                        <select x-model="item.category_id" class="text-[10px] py-0.5 px-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                                                            <template x-for="cat in categories" :key="cat.id">
                                                                <option :value="cat.id" x-text="cat.name"></option>
                                                            </template>
                                                        </select>

                                                        <!-- Unit Select -->
                                                        <select x-model="item.unit_id" class="text-[10px] py-0.5 px-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                                                            <template x-for="u in units" :key="u.id">
                                                                <option :value="u.id" x-text="u.name"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                </div>
                                            </template>
                                        </td>

                                        <!-- Quantity -->
                                        <td class="px-3 py-2 text-right">
                                            <input type="number" step="any" x-model.number="item.qty"
                                                class="w-16 text-right font-mono font-bold text-xs rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 py-1 px-1.5" />
                                        </td>

                                        <!-- Purchase Price (Rate) -->
                                        <td class="px-3 py-2 text-right">
                                            <input type="number" step="any" x-model.number="item.unit_price"
                                                class="w-20 text-right font-mono text-xs rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 py-1 px-1.5" />
                                        </td>

                                        <!-- Sale Price (For New Products) -->
                                        <td class="px-3 py-2 text-right">
                                            <template x-if="item.is_new">
                                                <input type="number" step="any" x-model.number="item.sale_price" placeholder="Sale"
                                                    class="w-20 text-right font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400 rounded border-purple-300 dark:border-purple-600 bg-white dark:bg-gray-900 py-1 px-1.5" />
                                            </template>
                                            <template x-if="!item.is_new">
                                                <span class="text-gray-400 text-[11px] font-mono" x-text="item.sale_price ? 'Rs ' + item.sale_price : '-'"></span>
                                            </template>
                                        </td>

                                        <!-- Subtotal -->
                                        <td class="px-3 py-2 text-right font-mono font-black text-gray-900 dark:text-gray-100"
                                            x-text="'Rs ' + ((item.qty * item.unit_price) || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})">
                                        </td>

                                        <!-- Delete Item Button -->
                                        <td class="px-2 py-2 text-center">
                                            <button type="button" @click="scannedData.items.splice(idx, 1)"
                                                class="text-red-400 hover:text-red-600 font-black p-1 text-base leading-none cursor-pointer">&times;</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Actions & Confirm Button -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                        <button type="button" @click="resetImage()" :disabled="creatingProducts"
                            class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                            Scan Another Bill
                        </button>

                        <button type="button" @click="importAndAutoCreate()" :disabled="creatingProducts || !scannedData?.items?.length"
                            class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-600 via-teal-600 to-indigo-600 hover:from-emerald-700 hover:to-indigo-700 text-white font-black text-xs uppercase tracking-wider transition shadow-lg flex items-center justify-center gap-2 active:scale-95 cursor-pointer disabled:opacity-50">
                            <template x-if="creatingProducts">
                                <span class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <span>Naye Products Create Ho Rahe Hain...</span>
                                </span>
                            </template>
                            <template x-if="!creatingProducts">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span x-text="newCount() > 0 ? ('🚀 ' + newCount() + ' Naye Products Create Karein & Form Mein Daalein') : '✓ Sabhi Items Purchase Form Mein Daalein'"></span>
                                </span>
                            </template>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
@keyframes scanLaser {
    0% { top: 0%; }
    100% { top: 98%; }
}
</style>

<script>
    function aiBillScanner(initialCategories = [], initialUnits = []) {
        return {
            modalOpen: false,
            imageFile: null,
            imagePreview: null,
            fileName: '',
            isPdfFile: false,
            scanning: false,
            creatingProducts: false,
            errorMessage: '',
            scannedData: null,
            categories: initialCategories,
            units: initialUnits,

            openModal() {
                this.modalOpen = true;
            },

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (file) this.loadFile(file);
            },

            handleDrop(event) {
                const file = event.dataTransfer.files[0];
                if (file) this.loadFile(file);
            },

            loadFile(file) {
                this.imageFile = file;
                this.fileName = file.name;
                this.isPdfFile = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
                this.errorMessage = '';
                this.scannedData = null;

                if (this.isPdfFile) {
                    this.imagePreview = 'pdf';
                } else {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.imagePreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            },

            resetImage() {
                this.imageFile = null;
                this.imagePreview = null;
                this.fileName = '';
                this.isPdfFile = false;
                this.scannedData = null;
                this.errorMessage = '';
                this.scanning = false;
                this.creatingProducts = false;
            },

            matchedCount() {
                if (!this.scannedData || !this.scannedData.items) return 0;
                return this.scannedData.items.filter(i => !i.is_new && i.matched_product_id).length;
            },

            newCount() {
                if (!this.scannedData || !this.scannedData.items) return 0;
                return this.scannedData.items.filter(i => i.is_new && i.auto_create).length;
            },

            async processScan() {
                if (!this.imageFile) return;

                this.scanning = true;
                this.errorMessage = '';

                const formData = new FormData();
                formData.append('bill_image', this.imageFile);

                try {
                    const response = await fetch("{{ route('ai.scan-bill') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: formData,
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        this.errorMessage = data.message || 'AI bill scan fail ho gaya.';
                        this.scanning = false;
                        return;
                    }

                    if (data.categories && data.categories.length) {
                        this.categories = data.categories;
                    }
                    if (data.units && data.units.length) {
                        this.units = data.units;
                    }

                    this.scannedData = data;
                } catch (err) {
                    this.errorMessage = 'Network error: ' + err.message;
                } finally {
                    this.scanning = false;
                }
            },

            async importAndAutoCreate() {
                if (!this.scannedData || !this.scannedData.items || !this.scannedData.items.length) return;

                const newItemsToCreate = this.scannedData.items.filter(i => i.is_new && i.auto_create);

                // 1. If there are new products to create in the database
                if (newItemsToCreate.length > 0) {
                    this.creatingProducts = true;
                    try {
                        const payload = {
                            products: newItemsToCreate.map(item => ({
                                name: item.raw_name,
                                unit_price: item.unit_price,
                                sale_price: item.sale_price || (item.unit_price * 1.25),
                                category_id: item.category_id,
                                unit_id: item.unit_id,
                            }))
                        };

                        const createRes = await fetch("{{ route('ai.create-scanned-products') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify(payload),
                        });

                        const createData = await createRes.json();

                        if (!createRes.ok || !createData.success) {
                            alert('Naye products create karne mein masla hua: ' + (createData.message || 'Error'));
                            this.creatingProducts = false;
                            return;
                        }

                        // Attach newly created products back to scannedData items
                        const createdList = createData.products || [];
                        this.scannedData.created_products = createdList;

                        createdList.forEach(newP => {
                            const matchedLine = this.scannedData.items.find(i => i.raw_name.trim().toLowerCase() === newP.name.trim().toLowerCase());
                            if (matchedLine) {
                                matchedLine.matched_product_id = newP.id;
                                matchedLine.matched_product_name = newP.name;
                                matchedLine.matched_product_sku = newP.sku;
                                matchedLine.is_new = false;
                            }
                        });

                    } catch (err) {
                        alert('Naye products create nahi ho sake: ' + err.message);
                        this.creatingProducts = false;
                        return;
                    } finally {
                        this.creatingProducts = false;
                    }
                }

                // 2. Dispatch event to parent purchase or purchase_order Alpine form
                window.dispatchEvent(new CustomEvent('ai-bill-imported', {
                    detail: this.scannedData,
                }));

                // 3. Close modal & show toast
                this.modalOpen = false;
            }
        };
    }
</script>
