<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('repairs.index') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Mobile Repairs</span>
                    </a>
                    <span class="text-gray-300 dark:text-gray-600">/</span>
                    <span class="text-xs font-semibold text-gray-500">Brands & Models</span>
                </div>
                <h2 class="font-black text-xl text-gray-900 dark:text-white leading-tight mt-1 flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">📱</span>
                    <span>Repair Mobile Brands & Models</span>
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Repairing lab ke tamam phone brands aur unke models ko manage karein.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('repair-services.index') }}" class="px-3.5 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <span>🔧 Repair Services & Rates</span>
                </a>
                <a href="{{ route('repair-brands.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add New Brand</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

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

        <!-- Quick Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Brands</span>
                    <span class="text-2xl font-black text-gray-900 dark:text-white">{{ number_format($totalBrands) }}</span>
                </div>
                <span class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-lg">🏷️</span>
            </div>

            <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Phone Models</span>
                    <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400 font-mono">{{ number_format($totalModels) }}</span>
                </div>
                <span class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-lg">📱</span>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs">
            <form method="GET" action="{{ route('repair-brands.index') }}" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="relative flex-1 max-w-md">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search brand name (e.g. Apple, Samsung, Infinix)..." class="w-full pl-9 pr-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="flex items-center gap-2">
                    @if(request('search'))
                        <a href="{{ route('repair-brands.index') }}" class="px-3 py-2 text-xs font-bold text-gray-500 hover:text-gray-700">Clear Search</a>
                    @endif
                    <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-bold text-xs rounded-xl transition">
                        Search
                    </button>
                </div>
            </form>
        </div>

        <!-- Brands Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-xs divide-y divide-gray-100 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/60 text-gray-500 uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-3 px-4">#</th>
                            <th class="py-3 px-4">Brand Name</th>
                            <th class="py-3 px-4 text-center">Models Count</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($brands as $index => $brand)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition">
                                <td class="py-3 px-4 text-gray-400 font-mono">{{ $brands->firstItem() + $index }}</td>
                                <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 font-bold flex items-center justify-center text-xs">
                                            {{ substr($brand->name, 0, 1) }}
                                        </span>
                                        <span class="text-sm">{{ $brand->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="{{ route('repair-brands.edit', $brand->id) }}" class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-mono font-bold bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 transition" title="View / Manage Models">
                                        <span>📱</span>
                                        <span>{{ $brand->models_count }} Models</span>
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($brand->status)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-500">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('repair-brands.edit', $brand->id) }}" class="px-3 py-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 rounded-lg transition flex items-center gap-1">
                                            <span>Manage Models & Edit</span>
                                        </a>
                                        <form action="{{ route('repair-brands.destroy', $brand->id) }}" method="POST" onsubmit="return confirm('Kiya aap waqai is brand aur iske tamam models ko delete karna chahte hain?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 text-xs font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 rounded-lg transition">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-xs text-gray-400 italic">
                                    Koi brand nahi mila. Naya brand add karne ke liye upar "+ Add New Brand" dabayein.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($brands->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $brands->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
