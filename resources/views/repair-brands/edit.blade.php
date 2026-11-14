<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('repair-brands.index') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Repair Brands</span>
                    </a>
                    <span class="text-gray-300 dark:text-gray-600">/</span>
                    <span class="text-xs font-semibold text-gray-500">Edit & Models</span>
                </div>
                <h2 class="font-black text-xl text-gray-900 dark:text-white leading-tight mt-1 flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600">📱</span>
                    <span>Brand: {{ $brand->name }} ({{ $models->count() }} Models)</span>
                </h2>
            </div>
            <a href="{{ route('repair-brands.index') }}" class="px-3.5 py-1.5 rounded-xl border border-gray-300 dark:border-gray-600 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition">
                &larr; Back to Brands
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Flash Success Message -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-2xl flex items-center justify-between text-emerald-800 dark:text-emerald-200 text-xs font-bold">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left: Brand Details Edit Card -->
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm space-y-6">
                    <div class="border-b border-gray-100 dark:border-gray-700 pb-3 flex items-center justify-between">
                        <h3 class="font-bold text-sm text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <span>🏷️ Brand Information</span>
                        </h3>
                        <span class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 px-2.5 py-0.5 rounded-full">
                            {{ $models->count() }} Models
                        </span>
                    </div>

                    <form action="{{ route('repair-brands.update', $brand->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="name" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">
                                Brand Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name', $brand->name) }}" required
                                   class="w-full py-2.5 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="status" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">
                                    Status
                                </label>
                                <select id="status" name="status" class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-semibold text-gray-900 dark:text-white">
                                    <option value="1" {{ old('status', $brand->status ? '1' : '0') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status', $brand->status ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div>
                                <label for="sort_order" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">
                                    Sort Order
                                </label>
                                <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $brand->sort_order) }}"
                                       class="w-full py-2 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-semibold text-gray-900 dark:text-white">
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                Update Brand Name
                            </button>
                        </div>
                    </form>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <span class="text-xs text-gray-400">Brand aur tamam models delete karein:</span>
                        <form action="{{ route('repair-brands.destroy', $brand->id) }}" method="POST" onsubmit="return confirm('Kiya aap waqai is brand aur iske tamam models ko delete karna chahte hain?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs rounded-xl transition">
                                Delete Brand
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right: Models List & Add Model Card -->
            <div class="lg:col-span-7 space-y-4">
                
                <!-- Quick Add Model Form -->
                <div class="bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-200/80 dark:border-indigo-900/40 rounded-2xl p-5 shadow-xs">
                    <h4 class="font-bold text-xs text-indigo-900 dark:text-indigo-200 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <span>➕ Add New Phone Model under {{ $brand->name }}</span>
                    </h4>

                    <form action="{{ route('repair-brands.models.store', $brand->id) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        @csrf
                        <div class="sm:col-span-4">
                            <label class="block text-[10px] font-bold text-indigo-800 dark:text-indigo-300 uppercase mb-1">Series / Group</label>
                            <input type="text" name="series" list="seriesDataList" placeholder="e.g. S Series, Note, 15 Pro..."
                                   class="w-full py-2 px-3 bg-white dark:bg-gray-900 border border-indigo-200 dark:border-indigo-800 rounded-xl text-xs font-semibold text-gray-900 dark:text-white">
                            <datalist id="seriesDataList">
                                @foreach($seriesList as $ser)
                                    <option value="{{ $ser }}">
                                @endforeach
                            </datalist>
                        </div>

                        <div class="sm:col-span-5">
                            <label class="block text-[10px] font-bold text-indigo-800 dark:text-indigo-300 uppercase mb-1">Model Name *</label>
                            <input type="text" name="name" required placeholder="e.g. Galaxy S26 Ultra, iPhone 19..."
                                   class="w-full py-2 px-3 bg-white dark:bg-gray-900 border border-indigo-200 dark:border-indigo-800 rounded-xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="sm:col-span-3 flex items-end">
                            <button type="submit" class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                + Add Model
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Existing Models List -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-gray-100 dark:border-gray-700">
                        <h4 class="font-black text-sm text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <span>Phone Models List</span>
                            <span class="text-xs text-gray-400 font-mono font-normal">({{ $models->count() }} Models)</span>
                        </h4>

                        <!-- Filter inside brand models -->
                        <form method="GET" action="{{ route('repair-brands.edit', $brand->id) }}" class="flex items-center gap-2">
                            <input type="text" name="model_search" value="{{ request('model_search') }}" placeholder="Filter models..." class="py-1.5 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                            <button type="submit" class="px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-bold rounded-xl">
                                Filter
                            </button>
                            @if(request('model_search'))
                                <a href="{{ route('repair-brands.edit', $brand->id) }}" class="text-xs text-gray-400 hover:text-gray-600">Reset</a>
                            @endif
                        </form>
                    </div>

                    <div class="max-h-[500px] overflow-y-auto space-y-2 pr-1 divide-y divide-gray-50 dark:divide-gray-700/50">
                        @forelse($models as $m)
                            <div class="pt-2 flex items-center justify-between text-xs hover:bg-gray-50/50 dark:hover:bg-gray-900/30 p-2 rounded-xl transition">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-6 h-6 rounded bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 font-bold text-[11px] flex items-center justify-center">📱</span>
                                    <div>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $m->name }}</span>
                                        @if($m->series)
                                            <span class="text-[10px] text-gray-400 ml-1">({{ $m->series }})</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <form action="{{ route('repair-models.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Kiya aap waqai is model ko delete karna chahte hain?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-gray-400 hover:text-rose-600 font-bold text-sm transition" title="Delete Model">
                                        &times;
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="py-8 text-center text-xs text-gray-400 italic">
                                Koi model nahi mila. Upar diye gaye form se naya phone model add karein.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-app-layout>
