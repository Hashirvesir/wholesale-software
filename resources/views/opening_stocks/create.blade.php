<x-app-layout>
    <div class="py-12" x-data="openingStockForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3
                        class="text-lg font-medium mb-6 text-gray-800 dark:text-gray-200 border-b dark:border-gray-700 pb-4">
                        {{ __('Add Opening Stock') }}
                    </h3>

                    <form action="{{ route('opening-stocks.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Date -->
                            <div>
                                <x-input-label for="date" :value="__('Date')" />
                                <x-text-input id="date" class="block mt-1 w-full" type="date" name="date"
                                    :value="old('date', date('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('date')" class="mt-2" />
                            </div>

                            <!-- Product (Searchable Dropdown) -->
                            <div class="relative" @click.away="open = false">
                                <x-input-label for="product_search" :value="__('Search Product (Name or SKU)')" />
                                <div class="mt-1 relative">
                                    <input type="text" id="product_search" x-model="search" @focus="open = true"
                                        @input="open = true"
                                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150"
                                        placeholder="Type to search..." autocomplete="off" required>

                                    <input type="hidden" name="product_id"
                                        :value="selectedProduct ? selectedProduct.id : ''">

                                    <!-- Dropdown List -->
                                    <div x-show="open && filteredProducts.length > 0"
                                        class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border dark:border-gray-700">
                                        <template x-for="product in filteredProducts" :key="product.id">
                                            <div @click="selectProduct(product)"
                                                class="cursor-pointer select-none relative py-2 pl-3 pr-9 border-b last:border-0 dark:border-gray-700 hover:bg-primary-50 dark:hover:bg-primary-900 transition">
                                                <div class="flex items-center">
                                                    <span class="font-normal block truncate"
                                                        x-text="product.name"></span>
                                                    <span class="ml-2 truncate text-gray-500 dark:text-gray-400 text-xs"
                                                        x-text="'[' + product.sku + ']'"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    <div x-show="open && search !== '' && filteredProducts.length === 0"
                                        class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg rounded-md py-2 px-3 text-sm text-gray-500 dark:text-gray-400 border dark:border-gray-700 italic">
                                        No products found matching "<span x-text="search"></span>"
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('product_id')" class="mt-2" />
                            </div>

                            <!-- Warehouse -->
                            <div>
                                <x-input-label for="warehouse_id" :value="__('Warehouse')" />
                                <select id="warehouse_id" name="warehouse_id"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150"
                                    required>
                                    <option value="">-- Select Warehouse --</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}"
                                            {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('warehouse_id')" class="mt-2" />
                            </div>

                            <!-- Qty -->
                            <div>
                                <x-input-label for="qty" :value="__('Quantity')" />
                                <x-text-input id="qty" x-model.number="qty" class="block mt-1 w-full"
                                    type="number" step="0.01" name="qty" required />
                                <x-input-error :messages="$errors->get('qty')" class="mt-2" />
                            </div>

                            <!-- Rate -->
                            <div>
                                <x-input-label for="rate" :value="__('Rate (Cost Per Unit)')" />
                                <x-text-input id="rate" x-model.number="rate" class="block mt-1 w-full"
                                    type="number" step="0.01" name="rate" required />
                                <x-input-error :messages="$errors->get('rate')" class="mt-2" />
                            </div>

                            <!-- Cargo Amount -->
                            <div>
                                <x-input-label for="cargo_amount" :value="__('Cargo Charges (Total, Optional)')" />
                                <x-text-input id="cargo_amount" x-model.number="cargoAmount" class="block mt-1 w-full"
                                    type="number" step="0.01" name="cargo_amount" />
                                <x-input-error :messages="$errors->get('cargo_amount')" class="mt-2" />
                            </div>

                            <!-- Total -->
                            <div>
                                <x-input-label for="total" :value="__('Total Value (Landed Cost)')" />
                                <div
                                    class="mt-1 block w-full px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-md text-primary-600 dark:text-primary-400 font-bold">
                                    Rs. <span x-text="(qty * rate + (cargoAmount || 0)).toFixed(2)">0.00</span>
                                </div>
                            </div>

                            <!-- Note -->
                            <div class="md:col-span-2">
                                <x-input-label for="note" :value="__('Note (Optional)')" />
                                <textarea id="note" name="note" rows="3"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150">{{ old('note') }}</textarea>
                                <x-input-error :messages="$errors->get('note')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 border-t dark:border-gray-700 pt-6">
                            <a href="{{ route('opening-stocks.index') }}"
                                class="mr-4 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 italic transition duration-150">Cancel</a>
                            <x-primary-button>
                                {{ __('Save Opening Stock') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openingStockForm() {
            return {
                open: false,
                search: '',
                selectedProduct: null,
                qty: '',
                rate: '',
                cargoAmount: '',
                products: @json($products),

                get filteredProducts() {
                    if (this.search === '') return [];
                    return this.products.filter(p => {
                        return p.name.toLowerCase().includes(this.search.toLowerCase()) ||
                            p.sku.toLowerCase().includes(this.search.toLowerCase());
                    }).slice(0, 10); // Show top 10 results
                },

                selectProduct(product) {
                    this.selectedProduct = product;
                    this.search = product.name + ' (' + product.sku + ')';
                    this.open = false;
                }
            }
        }
    </script>
</x-app-layout>
