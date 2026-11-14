<x-app-layout>
    <div class="py-6 sm:py-10" x-data="{
        search: '',
        selected: [],
        selectAll() {
            const allBoxes = document.querySelectorAll('input[name=\'permissions[]\']');
            this.selected = Array.from(allBoxes).map(b => b.value);
        },
        deselectAll() {
            this.selected = [];
        },
        toggleGroup(names) {
            const allSelected = names.every(n => this.selected.includes(n));
            if (allSelected) {
                this.selected = this.selected.filter(n => !names.includes(n));
            } else {
                const toAdd = names.filter(n => !this.selected.includes(n));
                this.selected = this.selected.concat(toAdd);
            }
        },
        isGroupSelected(names) {
            return names.length > 0 && names.every(n => this.selected.includes(n));
        },
        matchesSearch(name, moduleTitle) {
            if (!this.search.trim()) return true;
            const q = this.search.toLowerCase();
            return name.toLowerCase().includes(q) || moduleTitle.toLowerCase().includes(q);
        }
    }">
        <div class="max-w-[1750px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 dark:border-gray-700 pb-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('roles.index') }}"
                        class="p-2.5 bg-white dark:bg-gray-800 rounded-xl shadow-xs border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-wide">
                            Create New Role
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Define role name and assign granular permissions across all system modules.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="px-3.5 py-1.5 bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 rounded-xl text-xs font-bold text-indigo-700 dark:text-indigo-300">
                        Selected: <span class="font-mono text-sm font-black" x-text="selected.length"></span> Permissions
                    </div>
                </div>
            </div>

            <form action="{{ route('roles.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Role Name Card -->
                <div class="bg-white dark:bg-gray-800 p-6 sm:p-8 rounded-2xl shadow-xs border border-gray-200 dark:border-gray-700 space-y-4">
                    <div class="max-w-xl">
                        <label for="name" class="block text-xs font-black uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-2">
                            Role Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            placeholder="e.g. Technician, Senior Cashier, Floor Manager..."
                            class="w-full py-3 px-4 bg-gray-50 dark:bg-gray-900 border-gray-300 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        @error('name')
                            <p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Permissions Section Header & Controls -->
                <div class="bg-white dark:bg-gray-800 p-6 sm:p-8 rounded-2xl shadow-xs border border-gray-200 dark:border-gray-700 space-y-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-gray-100 dark:border-gray-700 pb-5">
                        <div>
                            <h4 class="text-base font-black uppercase text-gray-900 dark:text-white tracking-wider flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>Module-Wise Permission Assignment</span>
                            </h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Check the privileges this role should be granted.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Quick Filter Search Input -->
                            <div class="relative w-full sm:w-64">
                                <input type="text" x-model="search" placeholder="Search permissions..."
                                    class="w-full py-2 px-3 pl-8 text-xs bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-1 focus:ring-indigo-500 font-semibold">
                                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>

                            <button type="button" @click="selectAll()" class="px-3.5 py-2 text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 rounded-xl border border-indigo-200 dark:border-indigo-800 transition">
                                Select All (100%)
                            </button>
                            <button type="button" @click="deselectAll()" class="px-3.5 py-2 text-xs font-bold bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-700 dark:text-gray-300 rounded-xl transition">
                                Deselect All
                            </button>
                        </div>
                    </div>

                    <!-- Module Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($groupedPermissions as $moduleName => $group)
                            @php
                                $permNames = $group['permissions']->pluck('name')->toArray();
                            @endphp
                            <div class="bg-gray-50/70 dark:bg-gray-900/40 p-5 rounded-2xl border border-gray-200 dark:border-gray-700/80 space-y-4 hover:border-indigo-300 dark:hover:border-indigo-700 transition flex flex-col justify-between"
                                 x-show="@js($permNames).some(p => matchesSearch(p, @js($moduleName)))">
                                <div>
                                    <!-- Module Header -->
                                    <div class="flex items-start justify-between gap-2 pb-3 border-b border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center gap-2">
                                            <span class="text-lg">{{ $group['icon'] }}</span>
                                            <div>
                                                <h5 class="text-xs sm:text-sm font-black uppercase text-gray-900 dark:text-white tracking-wide">
                                                    {{ $moduleName }}
                                                </h5>
                                                <span class="text-[10px] text-gray-400 block">{{ $group['description'] }}</span>
                                            </div>
                                        </div>
                                        <button type="button"
                                                @click="toggleGroup(@js($permNames))"
                                                class="text-[11px] font-bold px-2 py-0.5 rounded-lg border transition shrink-0"
                                                :class="isGroupSelected(@js($permNames)) ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-100'">
                                            <span x-text="isGroupSelected(@js($permNames)) ? 'All ✓' : 'Toggle'"></span>
                                        </button>
                                    </div>

                                    <!-- Permissions Checkbox List -->
                                    <div class="space-y-2.5 pt-3">
                                        @foreach ($group['permissions'] as $permission)
                                            <label class="flex items-start group cursor-pointer p-1.5 rounded-xl hover:bg-white dark:hover:bg-gray-800/60 transition"
                                                   x-show="matchesSearch(@js($permission->name), @js($moduleName))">
                                                <div class="flex items-center h-5 mt-0.5">
                                                    <input type="checkbox" name="permissions[]"
                                                        value="{{ $permission->name }}"
                                                        x-model="selected"
                                                        class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:bg-gray-700 dark:border-gray-600 transition">
                                                </div>
                                                <div class="ml-3 text-xs">
                                                    <span class="font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition uppercase tracking-tight block">
                                                        {{ $permission->name }}
                                                    </span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="pt-2 text-right">
                                    <span class="text-[10px] font-mono text-gray-400 font-bold">
                                        {{ count($group['permissions']) }} privileges
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-6 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <a href="{{ route('roles.index') }}" class="text-xs font-bold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            &larr; Cancel and go back
                        </a>
                        <button type="submit"
                            class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-sm rounded-xl shadow-md transition flex items-center gap-2 active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Save Role & Assign Permissions</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
