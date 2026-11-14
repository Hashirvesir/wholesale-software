<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('repair-brands.index') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Repair Brands</span>
                    </a>
                    <span class="text-gray-300 dark:text-gray-600">/</span>
                    <span class="text-xs font-semibold text-gray-500">Create</span>
                </div>
                <h2 class="font-black text-xl text-gray-900 dark:text-white leading-tight mt-1 flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600">🏷️</span>
                    <span>Add New Repair Brand</span>
                </h2>
            </div>
            <a href="{{ route('repair-brands.index') }}" class="px-3.5 py-1.5 rounded-xl border border-gray-300 dark:border-gray-600 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition">
                &larr; Cancel
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
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

            <form action="{{ route('repair-brands.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Brand Name -->
                <div>
                    <label for="name" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                        Brand Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                           placeholder="e.g. Motorola, Asus ROG, OnePlus, Sony..."
                           class="w-full py-2.5 px-3.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    <p class="text-[11px] text-gray-400 mt-1">Brand save hone ke baad aap iske phone models add kar sakenge.</p>
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

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('repair-brands.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-xl transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-7 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/30 transition">
                        Save Brand & Add Models &rarr;
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
