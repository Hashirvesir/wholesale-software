<!-- Invenco AI Bill / Invoice OCR Scanner Modal -->
<div x-data="aiBillScanner()"
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
        class="fixed inset-0 bg-gray-900/75 backdrop-blur-xs z-50 flex items-center justify-center p-3 sm:p-6 overflow-y-auto">

        <!-- Modal Dialog -->
        <div @click.away="if (!scanning) modalOpen = false"
            class="bg-white dark:bg-gray-800 rounded-2xl max-w-3xl w-full shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden transform transition-all my-8">

            <!-- Modal Header -->
            <div class="px-5 py-4 bg-gradient-to-r from-violet-600 to-indigo-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center shadow-inner">
                        <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-black text-sm uppercase tracking-wide">AI Bill / Invoice Scanner</h3>
                        <p class="text-[11px] text-indigo-100">Upload vendor bill image or snap photo & auto-fill purchase items</p>
                    </div>
                </div>

                <button type="button" @click="modalOpen = false" :disabled="scanning"
                    class="text-indigo-100 hover:text-white text-xl font-black p-1 leading-none">&times;</button>
            </div>

            <!-- Modal Body -->
            <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto">

                <!-- Upload Dropzone (When no image selected yet) -->
                <div x-show="!imagePreview && !scannedData"
                    class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl p-8 text-center hover:border-indigo-500 dark:hover:border-indigo-400 transition cursor-pointer bg-gray-50/50 dark:bg-gray-900/30"
                    @click="$refs.billFileInput.click()"
                    @dragover.prevent=""
                    @drop.prevent="handleDrop($event)">

                    <input type="file" x-ref="billFileInput" accept="image/*,application/pdf" capture="environment" class="hidden"
                        @change="handleFileSelect($event)" />

                    <div class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto mb-3 shadow-inner">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="font-black text-sm text-gray-800 dark:text-gray-200">
                        Click to upload or snap bill photo (or drag & drop)
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Supports JPG, PNG, WEBP & PDF invoices up to 10MB</p>
                </div>

                <!-- Image Selected & Scanning State -->
                <div x-show="imagePreview && !scannedData" class="space-y-4">
                    <div class="relative max-h-72 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-900 flex items-center justify-center">
                        <img :src="imagePreview" alt="Bill Preview" class="max-h-72 max-w-full object-contain" />

                        <!-- Laser Scanning Animation Line -->
                        <div x-show="scanning" class="absolute inset-x-0 h-1 bg-gradient-to-r from-transparent via-emerald-400 to-transparent shadow-[0_0_15px_#34d399] animate-pulse"
                            style="animation: scanLaser 2s infinite alternate;"></div>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <button type="button" @click="resetImage()" :disabled="scanning"
                            class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            Change Photo
                        </button>

                        <button type="button" @click="processScan()" :disabled="scanning"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white font-black text-xs uppercase tracking-wider transition shadow-lg flex items-center gap-2 cursor-pointer disabled:opacity-50">
                            <template x-if="scanning">
                                <span class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <span>Scanning with GPT-4o Vision...</span>
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

                <!-- Scanned Results Preview Table -->
                <div x-show="scannedData" class="space-y-4">
                    <!-- Top Summary Card -->
                    <div class="p-3.5 rounded-xl bg-indigo-50 dark:bg-gray-900/60 border border-indigo-100 dark:border-gray-700 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div>
                            <span class="text-[10px] font-black uppercase text-gray-400 block">Extracted Supplier</span>
                            <div class="font-black text-gray-900 dark:text-gray-100 truncate" x-text="scannedData?.supplier?.raw_name || 'N/A'"></div>
                            <template x-if="scannedData?.supplier?.matched_id">
                                <span class="inline-block px-1.5 py-0.2 bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 rounded text-[9px] font-bold">
                                    ✓ Matched in DB: <span x-text="scannedData.supplier.matched_name"></span>
                                </span>
                            </template>
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase text-gray-400 block">Invoice / Date</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200" x-text="(scannedData?.invoice_number || 'N/A') + ' | ' + (scannedData?.date || '')"></span>
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase text-gray-400 block">Grand Total</span>
                            <span class="font-black text-emerald-600 dark:text-emerald-400 text-sm font-mono" x-text="'Rs. ' + Number(scannedData?.grand_total || 0).toLocaleString()"></span>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-xl">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-700/60 text-gray-500 dark:text-gray-300 font-bold uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="px-3 py-2 text-left">Bill Item Name</th>
                                    <th class="px-3 py-2 text-left">Matched Product in Stock</th>
                                    <th class="px-3 py-2 text-right">Qty</th>
                                    <th class="px-3 py-2 text-right">Rate</th>
                                    <th class="px-3 py-2 text-right">Subtotal</th>
                                    <th class="px-2 py-2 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                <template x-for="(item, idx) in scannedData?.items || []" :key="idx">
                                    <tr>
                                        <td class="px-3 py-2 font-bold text-gray-900 dark:text-gray-100" x-text="item.raw_name"></td>
                                        <td class="px-3 py-2">
                                            <template x-if="item.matched_product_id">
                                                <div>
                                                    <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="item.matched_product_name"></span>
                                                    <span class="text-[10px] text-gray-400 block font-mono" x-text="'Stock: ' + item.current_stock + ' (' + item.confidence + '% match)'"></span>
                                                </div>
                                            </template>
                                            <template x-if="!item.matched_product_id">
                                                <span class="text-amber-500 font-bold text-[11px]">Unmatched (Will use raw name)</span>
                                            </template>
                                        </td>
                                        <td class="px-3 py-2 text-right font-mono font-bold" x-text="item.qty"></td>
                                        <td class="px-3 py-2 text-right font-mono" x-text="item.unit_price"></td>
                                        <td class="px-3 py-2 text-right font-mono font-black text-gray-900 dark:text-gray-100" x-text="item.subtotal || (item.qty * item.unit_price)"></td>
                                        <td class="px-2 py-2 text-center">
                                            <button type="button" @click="scannedData.items.splice(idx, 1)" class="text-red-400 hover:text-red-600 font-black">&times;</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-2">
                        <button type="button" @click="resetImage()"
                            class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            Scan Another Bill
                        </button>

                        <button type="button" @click="importToForm()"
                            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wider transition shadow-lg flex items-center gap-2 active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span>Import All Items into Purchase Form</span>
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
    function aiBillScanner() {
        return {
            modalOpen: false,
            imageFile: null,
            imagePreview: null,
            scanning: false,
            errorMessage: '',
            scannedData: null,

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
                this.errorMessage = '';
                this.scannedData = null;

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            },

            resetImage() {
                this.imageFile = null;
                this.imagePreview = null;
                this.scannedData = null;
                this.errorMessage = '';
                this.scanning = false;
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

                    this.scannedData = data;
                } catch (err) {
                    this.errorMessage = 'Network error: ' + err.message;
                } finally {
                    this.scanning = false;
                }
            },

            importToForm() {
                if (!this.scannedData) return;

                // Dispatch global event so parent Alpine components can populate rows
                window.dispatchEvent(new CustomEvent('ai-bill-imported', {
                    detail: this.scannedData,
                }));

                this.modalOpen = false;
            }
        };
    }
</script>
