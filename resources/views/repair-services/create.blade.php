<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('repair-services.index') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Repair Services</span>
                    </a>
                    <span class="text-gray-300 dark:text-gray-600">/</span>
                    <span class="text-xs font-semibold text-gray-500">Create</span>
                </div>
                <h2 class="font-black text-xl text-gray-900 dark:text-white leading-tight mt-1 flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600">➕</span>
                    <span>Add New Repair Service</span>
                </h2>
            </div>
            <a href="{{ route('repair-services.index') }}" class="px-3.5 py-1.5 rounded-xl border border-gray-300 dark:border-gray-600 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition">
                &larr; Cancel
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 sm:p-8 shadow-sm">
            
            @if ($errors->any())
                <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-950/40 border-l-4 border-rose-500 rounded-r-xl text-rose-800 dark:text-rose-200 text-xs">
                    <ul class="list-disc list-inside space-y-1 font-semibold">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('repair-services.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                        Service / Fault Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                           placeholder="e.g. Back Glass Broken / Change, Face ID Repair, Battery Replacement..."
                           class="w-full py-2.5 px-3.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <p class="text-[11px] text-gray-400 mt-1">Yeh naam repair form par clickable badge ke tor par show hoga.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                            Category / Group
                        </label>
                        <input type="text" id="category" name="category" value="{{ old('category', 'General Repair') }}" list="categoriesList"
                               placeholder="e.g. Screen & Glass, Battery, Camera..."
                               class="w-full py-2.5 px-3.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-semibold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <datalist id="categoriesList">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    </div>

                    <!-- Default Labor Cost -->
                    <div>
                        <label for="default_labor_cost" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                            Default Labor Rate (Rs) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-2.5 text-gray-400 font-bold text-sm">Rs</span>
                            <input type="number" step="any" min="0" id="default_labor_cost" name="default_labor_cost" value="{{ old('default_labor_cost', 500) }}" required
                                   class="w-full pl-10 pr-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-mono font-bold text-indigo-600 dark:text-indigo-400 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Phone jama karte waqt yeh rate khud fill hoga (jise aap change bhi kar sakte hain).</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                            Status
                        </label>
                        <select id="status" name="status" class="w-full py-2.5 px-3.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-semibold text-gray-900 dark:text-white">
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active (Show on intake form)</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive (Hide)</option>
                        </select>
                    </div>

                    <!-- Sort Order -->
                    <div>
                        <label for="sort_order" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                            Sort Order (Tartib)
                        </label>
                        <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 1) }}"
                               class="w-full py-2.5 px-3.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-semibold text-gray-900 dark:text-white">
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                        Description / Notes (Optional)
                    </label>
                    <textarea id="description" name="description" rows="3" placeholder="Is service ya fault ke bare mein mazeed maloomat..."
                              class="w-full py-2.5 px-3.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('repair-services.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-xl transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-7 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-600/30 transition">
                        Save Service
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
