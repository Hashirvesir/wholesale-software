<x-app-layout>
    <div class="py-12" x-data="productForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3
                        class="text-lg font-medium mb-6 text-gray-800 dark:text-gray-200 border-b dark:border-gray-700 pb-4">
                        {{ __('Edit Product') }}: {{ $product->name }}</h3>

                    <form action="{{ route('products.update', $product) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <x-input-label for="name" :value="__('Product Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                    :value="old('name', $product->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <!-- SKU -->
                            <div>
                                <x-input-label for="sku" :value="__('SKU (Unique)')" />
                                <x-text-input id="sku" class="block mt-1 w-full bg-gray-50 dark:bg-gray-700"
                                    type="text" name="sku" :value="old('sku', $product->sku)" required readonly />
                                <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                            </div>

                            <!-- Barcode -->
                            <div>
                                <x-input-label for="barcode" :value="__('Barcode (Optional)')" />
                                <x-text-input id="barcode" class="block mt-1 w-full font-mono" type="text"
                                    name="barcode" :value="old('barcode', $product->barcode)" placeholder="Leave empty to use SKU" />
                                <x-input-error :messages="$errors->get('barcode')" class="mt-2" />
                            </div>

                            <!-- Category -->
                            <div>
                                <x-input-label for="category_id" :value="__('Category')" />
                                <div class="flex mt-1">
                                    <select id="category_id" name="category_id"
                                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150">
                                        <option value="">-- Select Category --</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}"
                                                {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" @click="openModal('category')"
                                        class="ml-2 p-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 shadow-md flex items-center justify-center aspect-square"
                                        title="Quick Add Category">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                            </div>

                            <!-- Brand -->
                            <div>
                                <x-input-label for="brand_id" :value="__('Brand')" />
                                <div class="flex mt-1">
                                    <select id="brand_id" name="brand_id"
                                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150">
                                        <option value="">-- Select Brand --</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}"
                                                {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" @click="openModal('brand')"
                                        class="ml-2 p-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 shadow-md flex items-center justify-center aspect-square"
                                        title="Quick Add Brand">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('brand_id')" class="mt-2" />
                            </div>

                            <!-- Unit -->
                            <div>
                                <x-input-label for="unit_id" :value="__('Unit')" />
                                <select id="unit_id" name="unit_id"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150">
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}"
                                            {{ old('unit_id', $product->unit_id) == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }} ({{ $unit->short_name }})</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('unit_id')" class="mt-2" />
                            </div>

                            <!-- Sale Price -->
                            <div>
                                <x-input-label for="sale_price" :value="__('Sale Price (Rs.)')" />
                                <x-text-input id="sale_price" class="block mt-1 w-full" type="number" step="0.01"
                                    name="sale_price" :value="old('sale_price', $product->sale_price)" required />
                                <x-input-error :messages="$errors->get('sale_price')" class="mt-2" />
                            </div>

                            <!-- Alert Quantity -->
                            <div>
                                <x-input-label for="alert_quantity" :value="__('Stock Alert Quantity')" />
                                <x-text-input id="alert_quantity" class="block mt-1 w-full" type="number"
                                    step="0.01" name="alert_quantity" :value="old('alert_quantity', $product->alert_quantity)" required />
                                <x-input-error :messages="$errors->get('alert_quantity')" class="mt-2" />
                            </div>

                            <!-- Status -->
                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm transition duration-150">
                                    <option value="1"
                                        {{ old('status', $product->status) == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0"
                                        {{ old('status', $product->status) == '0' ? 'selected' : '' }}>Inactive
                                    </option>
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 border-t dark:border-gray-700 pt-6">
                            <a href="{{ route('products.index') }}"
                                class="mr-4 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 italic transition duration-150">Cancel</a>
                            <x-primary-button>
                                {{ __('Update Product') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Quick Add Modal -->
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 font-bold" x-transition:enter-end="opacity-100"
                    class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showModal = false">
                    <div class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-middle bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4 border-b dark:border-gray-700 pb-3">
                            <h3 class="text-xl leading-6 font-bold text-primary-600 dark:text-primary-400 italic flex items-center"
                                x-text="'Quick Add ' + modalType.charAt(0).toUpperCase() + modalType.slice(1)">
                            </h3>
                            <button @click="showModal = false"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="mt-4">
                            <label for="quick_name"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                            <input type="text" x-model="quickName" id="quick_name"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                            <span class="text-red-500 text-xs mt-1" x-text="modalError"></span>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="submitQuickAdd()"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Add
                            Item</button>
                        <button type="button" @click="showModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function productForm() {
            return {
                showModal: false,
                modalType: '',
                quickName: '',
                modalError: '',

                openModal(type) {
                    this.modalType = type;
                    this.quickName = '';
                    this.modalError = '';
                    this.showModal = true;
                },

                submitQuickAdd() {
                    if (!this.quickName) {
                        this.modalError = 'Name is required';
                        return;
                    }

                    const url = this.modalType === 'category' ? '{{ route('categories.quick-add') }}' :
                        '{{ route('brands.quick-add') }}';
                    const data = {
                        name: this.quickName,
                        status: 1, // Default active for brand
                        _token: '{{ csrf_token() }}'
                    };

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(data)
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.id) {
                                const select = document.getElementById(this.modalType + '_id');
                                const option = new Option(result.name, result.id, true, true);
                                select.add(option);
                                this.showModal = false;
                            } else {
                                this.modalError = 'Something went wrong';
                            }
                        })
                        .catch(error => {
                            this.modalError = 'Error saving item';
                            console.error(error);
                        });
                }
            }
        }
    </script>
</x-app-layout>
