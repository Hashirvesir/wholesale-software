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
                    <span class="text-xs font-semibold text-gray-500">Edit</span>
                </div>
                <h2 class="font-black text-xl text-gray-900 dark:text-white leading-tight mt-1 flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600">✏️</span>
                    <span>Edit Service: {{ $service->name }}</span>
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

            <form action="{{ route('repair-services.update', $service->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label for="name" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                        Service / Fault Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $service->name) }}" required autofocus
                           class="w-full py-2.5 px-3.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                            Category / Group
                        </label>
                        <input type="text" id="category" name="category" value="{{ old('category', $service->category) }}" list="categoriesList"
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
                            <input type="number" step="any" min="0" id="default_labor_cost" name="default_labor_cost" value="{{ old('default_labor_cost', $service->default_labor_cost) }}" required
                                   class="w-full pl-10 pr-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-mono font-bold text-indigo-600 dark:text-indigo-400 focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                            Status
                        </label>
                        <select id="status" name="status" class="w-full py-2.5 px-3.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-semibold text-gray-900 dark:text-white">
                            <option value="1" {{ old('status', $service->status ? '1' : '0') == '1' ? 'selected' : '' }}>Active (Show on intake form)</option>
                            <option value="0" {{ old('status', $service->status ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive (Hide)</option>
                        </select>
                    </div>

                    <!-- Sort Order -->
                    <div>
                        <label for="sort_order" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                            Sort Order (Tartib)
                        </label>
                        <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $service->sort_order) }}"
                               class="w-full py-2.5 px-3.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-semibold text-gray-900 dark:text-white">
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                        Description / Notes (Optional)
                    </label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full py-2.5 px-3.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">{{ old('description', $service->description) }}</textarea>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700">
                    <form action="{{ route('repair-services.destroy', $service->id) }}" method="POST" onsubmit="return confirm('Kiya aap waqai is service ko delete karna chahte hain?');">
                        @csrf
                        @method('DELETE')
                        <button type="button" onclick="if(confirm('Kiya aap waqai is service ko delete karna chahte hain?')) { document.getElementById('delete-form').submit(); }" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs rounded-xl transition">
                            Delete Service
                        </button>
                    </form>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('repair-services.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-xl transition">
                            Cancel
                        </a>
                        <button type="submit" class="px-7 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-600/30 transition">
                            Update Service
                        </button>
                    </div>
                </div>

            </form>

            <form id="delete-form" action="{{ route('repair-services.destroy', $service->id) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</x-app-layout>
