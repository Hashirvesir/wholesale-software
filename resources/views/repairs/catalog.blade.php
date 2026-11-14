<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('repairs.index') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Mobile Repairs Lab</span>
                    </a>
                    <span class="text-gray-300 dark:text-gray-600">/</span>
                    <span class="text-xs font-semibold text-gray-500">Catalog & Settings</span>
                </div>
                <h2 class="font-black text-xl text-gray-900 dark:text-white leading-tight mt-1 flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <span>Repair Catalog (Services, Brands & Models)</span>
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Tamam predefined mobile services/faults, default rates, brands aur phone models yahan se manage karein.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('repairs.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Receive Phone (+ Intake)</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6" x-data="repairCatalogManager()">

        <!-- Flash Session Messages -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-2xl flex items-center justify-between text-emerald-800 dark:text-emerald-200 text-xs font-bold">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
            </div>
        @endif

        <!-- Quick Stats Banner -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Predefined Services</span>
                    <span class="text-2xl font-black text-gray-900 dark:text-white">{{ $services->count() }}</span>
                </div>
                <span class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-lg">🔧</span>
            </div>

            <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Supported Brands</span>
                    <span class="text-2xl font-black text-gray-900 dark:text-white">{{ $brands->count() }}</span>
                </div>
                <span class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-lg">🏷️</span>
            </div>

            <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Seeded Phone Models</span>
                    <span class="text-2xl font-black text-gray-900 dark:text-white">{{ $brands->sum('models_count') }}</span>
                </div>
                <span class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-lg">📱</span>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <button type="button"
                        @click="activeTab = 'services'"
                        :class="activeTab === 'services' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 font-black' : 'border-transparent text-gray-500 hover:text-gray-700 font-semibold'"
                        class="py-3 px-4 border-b-2 text-sm flex items-center gap-2 transition cursor-pointer">
                    <span>🔧 Services & Faults</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">{{ $services->count() }}</span>
                </button>

                <button type="button"
                        @click="activeTab = 'brands'"
                        :class="activeTab === 'brands' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 font-black' : 'border-transparent text-gray-500 hover:text-gray-700 font-semibold'"
                        class="py-3 px-4 border-b-2 text-sm flex items-center gap-2 transition cursor-pointer">
                    <span>📱 Brands & Models Catalog</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">{{ $brands->count() }} Brands</span>
                </button>
            </div>
        </div>

        <!-- ==========================================
             TAB 1: SERVICES & FAULTS MANAGEMENT
             ========================================== -->
        <div x-show="activeTab === 'services'" class="space-y-4">
            
            <!-- Controls Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs">
                <div class="flex flex-1 items-center gap-2">
                    <div class="relative flex-1 max-w-md">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" x-model="serviceSearch" placeholder="Search services (e.g. Back Glass, Battery, Camera)..." class="w-full pl-9 pr-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <select x-model="selectedCategory" class="py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="button" @click="openAddServiceModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Add New Service</span>
                </button>
            </div>

            <!-- Services Table -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-xs divide-y divide-gray-100 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/60 text-gray-500 uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="py-3 px-4">Service / Fault Name</th>
                                <th class="py-3 px-4">Category</th>
                                <th class="py-3 px-4 text-right">Default Labor Rate (Rs)</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="srv in filteredServices()" :key="srv.id">
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition">
                                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs">🔧</span>
                                            <span x-text="srv.name"></span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300" x-text="srv.category || 'General Repair'"></span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                        Rs <span x-text="Number(srv.default_labor_cost || 0).toLocaleString()"></span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                            Active
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" @click="openEditServiceModal(srv)" class="px-2.5 py-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 rounded-lg transition">
                                                Edit
                                            </button>
                                            <form :action="'/repair-catalog/service/' + srv.id" method="POST" onsubmit="return confirm('Kiya aap waqai is service ko delete karna chahte hain?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1 text-xs font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 rounded-lg transition">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ==========================================
             TAB 2: BRANDS & PHONE MODELS CATALOG
             ========================================== -->
        <div x-show="activeTab === 'brands'" class="space-y-4" style="display: none;">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Left: Brands List -->
                <div class="lg:col-span-4 space-y-3">
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">Brands ({{ $brands->count() }})</h3>
                            <button type="button" @click="brandModalOpen = true" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition flex items-center gap-1">
                                <span>+ Add Brand</span>
                            </button>
                        </div>

                        <input type="text" x-model="brandSearch" placeholder="Search Brand..." class="w-full py-1.5 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">

                        <div class="space-y-1 max-h-[500px] overflow-y-auto pr-1">
                            <template x-for="b in filteredBrands()" :key="b.id">
                                <div @click="selectBrand(b)"
                                     :class="selectedBrand && selectedBrand.id === b.id ? 'bg-indigo-600 text-white shadow-sm' : 'hover:bg-gray-50 dark:hover:bg-gray-900/50 text-gray-800 dark:text-gray-200 border border-transparent'"
                                     class="p-2.5 rounded-xl cursor-pointer flex items-center justify-between transition">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-lg flex items-center justify-center font-bold text-xs" :class="selectedBrand && selectedBrand.id === b.id ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'">
                                            <span x-text="b.name.charAt(0)"></span>
                                        </span>
                                        <span class="font-bold text-xs" x-text="b.name"></span>
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-mono font-bold" :class="selectedBrand && selectedBrand.id === b.id ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500'" x-text="(b.models ? b.models.length : (b.models_count || 0)) + ' models'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Right: Models under Selected Brand -->
                <div class="lg:col-span-8 space-y-3">
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs space-y-4">
                        <template x-if="selectedBrand">
                            <div>
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-gray-100 dark:border-gray-700">
                                    <div>
                                        <h3 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                                            <span x-text="selectedBrand.name"></span>
                                            <span class="text-xs font-semibold text-gray-400 font-mono">
                                                (<span x-text="selectedBrand.models ? selectedBrand.models.length : 0"></span> Models)
                                            </span>
                                        </h3>
                                        <p class="text-[11px] text-gray-400">Select kiye gaye brand ke tamam phone models aur series.</p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="openAddModelModal(selectedBrand)" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                                            <span>+ Add Model under <span x-text="selectedBrand.name"></span></span>
                                        </button>

                                        <form :action="'/repair-catalog/brand/' + selectedBrand.id" method="POST" onsubmit="return confirm('Kiya aap waqai is brand aur iske tamam models ko delete karna chahte hain?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 text-xs font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 rounded-xl transition" title="Delete Brand">
                                                Delete Brand
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="pt-3">
                                    <input type="text" x-model="modelSearch" placeholder="Filter models under this brand (e.g. S24, 15 Pro, Flip)..." class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white mb-3">

                                    <div class="max-h-[500px] overflow-y-auto space-y-2 pr-1">
                                        <template x-for="m in filteredBrandModels()" :key="m.id">
                                            <div class="p-2.5 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900/40 flex items-center justify-between text-xs transition">
                                                <div class="flex items-center gap-2.5">
                                                    <span class="w-5 h-5 rounded bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 font-bold text-[10px] flex items-center justify-center">📱</span>
                                                    <span class="font-bold text-gray-900 dark:text-white" x-text="m.name"></span>
                                                    <span class="text-[10px] text-gray-400 font-medium" x-text="m.series ? '(' + m.series + ')' : ''"></span>
                                                </div>
                                                <form :action="'/repair-catalog/model/' + m.id" method="POST" onsubmit="return confirm('Is model ko delete karein?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-rose-500 hover:text-rose-700 font-bold text-xs p-1" title="Delete Model">
                                                        &times;
                                                    </button>
                                                </form>
                                            </div>
                                        </template>

                                        <template x-if="filteredBrandModels().length === 0">
                                            <div class="py-8 text-center text-xs text-gray-400">
                                                Koi model nahi mila. Naya model add karne ke liye upar "+ Add Model" dabayein.
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

        </div>

        <!-- ==========================================
             MODALS
             ========================================== -->

        <!-- 1. Add / Edit Service Modal -->
        <div x-show="serviceModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-700 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="font-black text-sm text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">🔧</span>
                        <span x-text="isEditingService ? 'Edit Repair Service' : 'Add New Repair Service'"></span>
                    </h3>
                    <button type="button" @click="serviceModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
                </div>

                <form :action="isEditingService ? '/repair-catalog/service/' + serviceForm.id : '{{ route('repairs.service.store') }}'" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="isEditingService">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Service / Fault Name *</label>
                        <input type="text" name="name" x-model="serviceForm.name" required placeholder="e.g. Back Glass Change, Face ID Repair..." class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Category</label>
                        <input type="text" name="category" x-model="serviceForm.category" placeholder="e.g. Screen & Glass, Hardware, Camera..." list="categoryList" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-semibold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <datalist id="categoryList">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Default Labor Rate (Rs)</label>
                        <input type="number" step="any" name="default_labor_cost" x-model.number="serviceForm.default_labor_cost" placeholder="0" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="serviceModalOpen = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-xl">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs">
                            <span x-text="isEditingService ? 'Update Service' : 'Save Service'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. Add Brand Modal -->
        <div x-show="brandModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-700 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="font-black text-sm text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">🏷️</span>
                        <span>Add New Brand</span>
                    </h3>
                    <button type="button" @click="brandModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
                </div>

                <form action="{{ route('repairs.brand.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Brand Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Motorola, Asus, Sony..." class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="brandModalOpen = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-xl">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs">
                            Save Brand
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. Add Model Modal -->
        <div x-show="modelModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-700 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="font-black text-sm text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">📱</span>
                        <span>Add New Model</span>
                    </h3>
                    <button type="button" @click="modelModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
                </div>

                <form action="{{ route('repairs.model.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Brand *</label>
                        <select name="repair_brand_id" x-model="newModelForm.brandId" required class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-900 dark:text-white">
                            @foreach($brands as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Series / Category</label>
                        <input type="text" name="series" x-model="newModelForm.series" placeholder="e.g. S Series, Note Series, Pro Max..." class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Model Name *</label>
                        <input type="text" name="name" x-model="newModelForm.name" required placeholder="e.g. Galaxy S26 Ultra, iPhone 19..." class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="modelModalOpen = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-xl">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs">
                            Save Model
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        function repairCatalogManager() {
            const rawBrands = {!! json_encode($brands) !!};
            const rawServices = {!! json_encode($services) !!};

            return {
                activeTab: 'services',
                brands: rawBrands,
                services: rawServices,

                // Services tab state
                serviceSearch: '',
                selectedCategory: '',
                serviceModalOpen: false,
                isEditingService: false,
                serviceForm: {
                    id: null,
                    name: '',
                    category: '',
                    default_labor_cost: 0
                },

                // Brands tab state
                brandSearch: '',
                modelSearch: '',
                selectedBrand: rawBrands.length > 0 ? rawBrands[0] : null,
                brandModalOpen: false,
                modelModalOpen: false,
                newModelForm: {
                    brandId: rawBrands.length > 0 ? rawBrands[0].id : '',
                    series: '',
                    name: ''
                },

                filteredServices() {
                    const q = (this.serviceSearch || '').trim().toLowerCase();
                    const cat = this.selectedCategory;
                    return this.services.filter(s => {
                        const matchQ = !q || s.name.toLowerCase().includes(q) || (s.category && s.category.toLowerCase().includes(q));
                        const matchCat = !cat || s.category === cat;
                        return matchQ && matchCat;
                    });
                },

                openAddServiceModal() {
                    this.isEditingService = false;
                    this.serviceForm = {
                        id: null,
                        name: '',
                        category: '',
                        default_labor_cost: 0
                    };
                    this.serviceModalOpen = true;
                },

                openEditServiceModal(s) {
                    this.isEditingService = true;
                    this.serviceForm = {
                        id: s.id,
                        name: s.name,
                        category: s.category || '',
                        default_labor_cost: s.default_labor_cost || 0
                    };
                    this.serviceModalOpen = true;
                },

                filteredBrands() {
                    const q = (this.brandSearch || '').trim().toLowerCase();
                    if (!q) return this.brands;
                    return this.brands.filter(b => b.name.toLowerCase().includes(q));
                },

                selectBrand(b) {
                    this.selectedBrand = b;
                    this.modelSearch = '';
                },

                filteredBrandModels() {
                    if (!this.selectedBrand || !this.selectedBrand.models) return [];
                    const q = (this.modelSearch || '').trim().toLowerCase();
                    if (!q) return this.selectedBrand.models;
                    return this.selectedBrand.models.filter(m => {
                        return m.name.toLowerCase().includes(q) || (m.series && m.series.toLowerCase().includes(q));
                    });
                },

                openAddModelModal(brand) {
                    this.newModelForm.brandId = brand ? brand.id : (this.brands[0] ? this.brands[0].id : '');
                    this.newModelForm.series = '';
                    this.newModelForm.name = '';
                    this.modelModalOpen = true;
                }
            };
        }
    </script>
</x-app-layout>
