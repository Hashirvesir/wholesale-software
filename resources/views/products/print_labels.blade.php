<x-app-layout>
    <div class="py-12" x-data="barcodeManager()">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-2xl sm:rounded-3xl border border-gray-200 dark:border-gray-700">
                <div class="p-8">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-8 border-b dark:border-gray-700 pb-6">
                        <div>
                            <h3 class="text-3xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter">
                                Professional Bulk Barcode Generator</h3>
                            <p class="text-sm text-gray-500 mt-1 italic">Add multiple products, set quantities, and print
                                and multiple sizes.</p>
                        </div>
                        <div class="flex space-x-4">
                            <div
                                class="bg-primary-50 dark:bg-primary-900/20 px-4 py-2 rounded-2xl border border-primary-100 dark:border-primary-800">
                                <span
                                    class="text-xs font-bold text-primary-600 block uppercase tracking-widest">Selected
                                    Items</span>
                                <span class="text-xl font-black text-primary-700" x-text="items.length">0</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Left Panel: Search & Settings -->
                        <div class="lg:col-span-1 space-y-8">
                            <!-- Product Search -->
                            <div class="relative">
                                <label
                                    class="block text-xs font-black uppercase text-gray-500 mb-2 tracking-[0.2em]">Quick
                                    Add Product (Keyboard Friendly)</label>
                                <div class="relative">
                                    <input type="text" x-model="search"
                                        @keydown.down.prevent="selectedIndex = (selectedIndex + 1) % filteredProducts.length"
                                        @keydown.up.prevent="selectedIndex = (selectedIndex - 1 + filteredProducts.length) % filteredProducts.length"
                                        @keydown.enter.prevent="addItemFromSearch()" placeholder="Type name or SKU..."
                                        class="w-full bg-gray-50 dark:bg-gray-900 border-2 border-gray-100 dark:border-gray-700 rounded-2xl px-5 py-4 text-sm focus:ring-4 focus:ring-primary-500/20 focus:border-primary-500 dark:text-gray-100 transition-all font-medium">

                                    <div x-show="search.length > 0"
                                        class="absolute z-[100] mt-2 w-full bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                        <div class="max-h-64 overflow-y-auto">
                                            <template x-for="(product, index) in filteredProducts"
                                                :key="product.id">
                                                <button type="button" @click="addItem(product)"
                                                    :class="{ 'bg-primary-50 dark:bg-primary-900/40': selectedIndex === index }"
                                                    class="w-full text-left px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition duration-150 border-b last:border-0 dark:border-gray-700 flex justify-between items-center">
                                                    <div>
                                                        <div class="font-black text-sm uppercase text-gray-900 dark:text-gray-100"
                                                            x-text="product.name"></div>
                                                        <div class="flex items-center gap-2 mt-0.5">
                                                            <div class="text-[10px] text-gray-500 font-mono tracking-tighter"
                                                                x-text="'SKU: ' + product.sku"></div>
                                                            <template x-if="product.barcode">
                                                                <div class="text-[10px] text-gray-500 font-mono tracking-tighter"
                                                                    x-text="'| BAR: ' + product.barcode"></div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                    <span
                                                        class="text-[10px] font-bold bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md text-gray-600 dark:text-gray-400"
                                                        x-text="'Rs. ' + product.sale_price"></span>
                                                </button>
                                            </template>
                                            <div x-show="filteredProducts.length === 0"
                                                class="p-6 text-center text-xs text-gray-400 italic">
                                                No matches found.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-2 italic flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z">
                                        </path>
                                    </svg>
                                    Use <span class="font-bold mx-1">↑↓</span> to navigate and <span
                                        class="font-bold mx-1">Enter</span> to add.
                                </p>
                            </div>

                            <!-- Label Size Selector -->
                            <div>
                                <label
                                    class="block text-xs font-black uppercase text-gray-500 mb-3 tracking-[0.2em]">Choose
                                    Sticker Size</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <template x-for="size in sizes" :key="size.id">
                                        <button @click="labelSize = size"
                                            :class="labelSize.id === size.id ?
                                                'border-primary-500 bg-primary-50 dark:bg-primary-900/20 ring-2 ring-primary-500/20' :
                                                'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-primary-400'"
                                            class="p-4 border-2 rounded-2xl text-left transition-all relative group">
                                            <div class="font-black text-sm text-gray-900 dark:text-gray-100"
                                                x-text="size.name"></div>
                                            <div class="text-[10px] text-gray-500 font-medium uppercase tracking-widest mt-1"
                                                x-text="size.label"></div>
                                            <!-- Checkmark on active -->
                                            <div x-show="labelSize.id === size.id"
                                                class="absolute top-2 right-2 text-primary-600">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Options -->
                            <div
                                class="p-6 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-700">
                                <h4 class="text-xs font-black uppercase text-gray-400 mb-4 tracking-widest">Printing
                                    Options</h4>
                                <label class="flex items-center space-x-3 cursor-pointer group mb-3">
                                    <input type="checkbox" x-model="showPrice"
                                        class="rounded text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600">
                                    <span
                                        class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider group-hover:text-gray-900 dark:group-hover:text-gray-200 transition">Show
                                        Price on Label</span>
                                </label>
                                <label class="flex items-center space-x-3 cursor-pointer group">
                                    <input type="checkbox" x-model="showName"
                                        class="rounded text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600">
                                    <span
                                        class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider group-hover:text-gray-900 dark:group-hover:text-gray-200 transition">Show
                                        Name on Label</span>
                                </label>
                            </div>
                        </div>

                        <!-- Right Panel: Items Table -->
                        <div class="lg:col-span-2">
                            <div
                                class="bg-gray-50 dark:bg-gray-900/30 rounded-3xl border border-gray-100 dark:border-gray-700 overflow-hidden flex flex-col min-h-[500px]">
                                <div
                                    class="px-6 py-4 border-b dark:border-gray-700 bg-white dark:bg-gray-800 flex justify-between items-center">
                                    <h4 class="text-xs font-black uppercase text-gray-500 tracking-[0.2em]">Printing
                                        Queue</h4>
                                    <button @click="items = []" x-show="items.length > 0"
                                        class="text-[10px] font-black uppercase text-red-500 hover:text-red-700 transition tracking-widest">Clear
                                        All</button>
                                </div>

                                <div class="flex-1 overflow-y-auto max-h-[600px]">
                                    <table class="w-full text-left border-collapse">
                                        <thead class="sticky top-0 bg-gray-50 dark:bg-gray-800 z-10 shadow-sm">
                                            <tr>
                                                <th
                                                    class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest w-1/2">
                                                    Product Details</th>
                                                <th
                                                    class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest text-center">
                                                    Copies</th>
                                                <th
                                                    class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest text-right">
                                                    Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y dark:divide-gray-700">
                                            <template x-for="(item, index) in items" :key="index">
                                                <tr
                                                    class="bg-white dark:bg-gray-800/40 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition group">
                                                    <td class="px-6 py-5">
                                                        <div class="font-black text-sm uppercase text-gray-900 dark:text-gray-100 group-hover:text-primary-600 transition"
                                                            x-text="item.name"></div>
                                                        <div class="text-[10px] text-gray-500 font-mono mt-0.5"
                                                            x-text="item.sku"></div>
                                                    </td>
                                                    <td class="px-6 py-5">
                                                        <div class="flex justify-center">
                                                            <input type="number" x-model.number="item.qty"
                                                                min="1" max="500"
                                                                class="w-20 bg-gray-50 dark:bg-gray-900 border-none rounded-xl text-center text-sm font-black focus:ring-2 focus:ring-primary-500 dark:text-gray-100 shadow-inner">
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-5 text-right">
                                                        <button @click="removeItem(index)"
                                                            class="p-2 text-gray-300 hover:text-red-500 transition-all rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                            <tr x-show="items.length === 0">
                                                <td colspan="3" class="px-6 py-24 text-center">
                                                    <div class="opacity-10 dark:opacity-20 mb-4">
                                                        <svg class="w-20 h-20 mx-auto text-gray-900 dark:text-gray-100"
                                                            fill="currentColor" viewBox="0 0 20 20">
                                                            <path
                                                                d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                    <p class="text-gray-400 font-medium italic">Your printing queue is
                                                        empty.</p>
                                                    <button @click="document.querySelector('input[type=text]').focus()"
                                                        class="mt-4 text-xs font-black uppercase text-primary-600 hover:underline tracking-widest">Search
                                                        a product to add</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="p-8 bg-white dark:bg-gray-800 border-t dark:border-gray-700">
                                    <button @click="executePrintJob()" :disabled="items.length === 0"
                                        class="w-full py-5 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-200 disabled:cursor-not-allowed text-white rounded-3xl font-black uppercase tracking-[0.3em] text-sm transition-all shadow-2xl shadow-primary-500/30 flex items-center justify-center group overflow-hidden relative">
                                        <div
                                            class="absolute inset-x-0 bottom-0 h-1 bg-primary-800/50 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-500">
                                        </div>
                                        <svg class="w-6 h-6 mr-4 group-hover:rotate-12 transition-transform"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                            </path>
                                        </svg>
                                        Start Bulk Printing Job
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function barcodeManager() {
            return {
                search: '',
                selectedIndex: 0,
                allProducts: @json($products),
                items: [],
                showPrice: true,
                showName: true,
                labelSize: {
                    id: 's1',
                    name: 'Standard (1.5" x 1")',
                    width: '1.5in',
                    height: '1in',
                    productFS: '8px',
                    priceFS: '10px',
                    svgW: '1.3in',
                    svgH: '0.4in',
                    label: '1.5x1'
                },
                sizes: [{
                        id: 's1',
                        name: 'Small Label',
                        width: '1.5in',
                        height: '1in',
                        productFS: '8px',
                        priceFS: '10px',
                        svgW: '1.3in',
                        svgH: '0.4in',
                        label: '1.5x1 Inch'
                    },
                    {
                        id: 's2',
                        name: 'Medium Label',
                        width: '2in',
                        height: '1in',
                        productFS: '10px',
                        priceFS: '12px',
                        svgW: '1.8in',
                        svgH: '0.5in',
                        label: '2x1 Inch'
                    },
                    {
                        id: 's3',
                        name: 'Large Label',
                        width: '3in',
                        height: '1.5in',
                        productFS: '12px',
                        priceFS: '14px',
                        svgW: '2.5in',
                        svgH: '0.7in',
                        label: '3x1.5 Inch'
                    },
                    {
                        id: 's4',
                        name: 'Narrow Label',
                        width: '2in',
                        height: '0.75in',
                        productFS: '7px',
                        priceFS: '9px',
                        svgW: '1.8in',
                        svgH: '0.35in',
                        label: '2x0.75 Inch'
                    },
                ],

                get filteredProducts() {
                    const q = this.search.toLowerCase();
                    if (!q) return [];
                    return this.allProducts.filter(p =>
                        p.name.toLowerCase().includes(q) ||
                        p.sku.toLowerCase().includes(q) ||
                        (p.barcode && p.barcode.toLowerCase().includes(q))
                    ).slice(0, 10);
                },

                async addItem(product) {
                    // Check if already in list
                    const existing = this.items.find(i => i.id === product.id);
                    if (existing) {
                        existing.qty++;
                    } else {
                        const response = await fetch(`/products/${product.id}/get-barcode`);
                        const svg = await response.text();
                        this.items.push({
                            ...product,
                            qty: 1,
                            barcode_svg: svg
                        });
                    }
                    this.search = '';
                    this.selectedIndex = 0;
                },

                addItemFromSearch() {
                    if (this.filteredProducts.length > 0) {
                        this.addItem(this.filteredProducts[this.selectedIndex]);
                    }
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                executePrintJob() {
                    if (this.items.length === 0) return;

                    const win = window.open('', 'Bulk Printing', 'height=600,width=800');
                    win.document.write('<html><head><title>Print Job</title>');
                    win.document.write(`
                        <style>
                            @page { 
                                size: ${this.labelSize.width} ${this.labelSize.height}; 
                                margin: 0; 
                            }
                            body { margin: 0; padding: 0; font-family: 'Courier New', Courier, monospace; overflow: hidden; }
                            .sticker {
                                width: ${this.labelSize.width};
                                height: ${this.labelSize.height};
                                display: flex;
                                flex-direction: column;
                                justify-content: center;
                                align-items: center;
                                text-align: center;
                                page-break-after: always;
                                box-sizing: border-box;
                                padding: 2px;
                            }
                            .name { 
                                font-size: ${this.labelSize.productFS}; 
                                font-weight: bold; 
                                width: 95%; 
                                white-space: nowrap; 
                                overflow: hidden; 
                                text-overflow: ellipsis; 
                                margin-bottom: 1px; 
                                text-transform: uppercase; 
                            }
                            .barcode svg { 
                                width: ${this.labelSize.svgW}; 
                                height: ${this.labelSize.svgH}; 
                            }
                            .sku-text { font-size: calc(${this.labelSize.productFS} - 1px); margin-bottom: 2px; }
                            .price { 
                                font-size: ${this.labelSize.priceFS}; 
                                font-weight: 900; 
                                margin-top: 2px; 
                            }
                        </style>
                    `);
                    win.document.write('</head><body>');

                    this.items.forEach(item => {
                        for (let i = 0; i < item.qty; i++) {
                            win.document.write('<div class="sticker">');
                            if (this.showName) win.document.write('<div class="name">' + item.name + '</div>');
                            win.document.write('<div class="barcode">' + item.barcode_svg + '</div>');
                            win.document.write('<div class="sku-text">' + (item.barcode || item.sku) + '</div>');
                            if (this.showPrice) win.document.write('<div class="price">Rs. ' + Number(item
                                .sale_price).toLocaleString('en-PK', {
                                minimumFractionDigits: 0
                            }) + '</div>');
                            win.document.write('</div>');
                        }
                    });

                    win.document.write('</body></html>');
                    win.document.close();
                    win.focus();
                    setTimeout(() => {
                        win.print();
                        win.close();
                    }, 500);
                }
            }
        }
    </script>
</x-app-layout>
