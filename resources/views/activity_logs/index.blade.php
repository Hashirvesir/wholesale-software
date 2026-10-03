<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-black text-gray-900 dark:text-gray-100 italic uppercase tracking-widest">
                    Audit Logs & Activity Tracking</h3>
            </div>

            <!-- Search & Filter Bar -->
            <div
                class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 mb-6 transition-all duration-300">
                <form action="{{ route('activity-logs.index') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- User Filter -->
                    <div>
                        <label for="user_id"
                            class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">User</label>
                        <select name="user_id" id="user_id"
                            class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100">
                            <option value="">All Users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Module Filter -->
                    <div>
                        <label for="module"
                            class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">Module</label>
                        <select name="module" id="module"
                            class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100">
                            <option value="">All Modules</option>
                            @foreach ($modules as $module)
                                <option value="{{ $module }}"
                                    {{ request('module') == $module ? 'selected' : '' }}>
                                    {{ $module }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Filter -->
                    <div>
                        <label for="date"
                            class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-widest">Date</label>
                        <input type="date" name="date" id="date" value="{{ request('date') }}"
                            class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500 dark:text-gray-100">
                    </div>

                    <!-- Actions -->
                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="flex-1 bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded-lg text-xs uppercase tracking-widest transition shadow-lg">
                            Filter
                        </button>
                        <a href="{{ route('activity-logs.index') }}"
                            class="flex-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold py-2 px-4 rounded-lg text-xs text-center uppercase tracking-widest transition">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 dark:border-gray-700 transition-all duration-300">
                <div class="p-0 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto text-sm">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr
                                    class="bg-gray-50 dark:bg-gray-700 uppercase tracking-widest text-[10px] font-black text-gray-500 dark:text-gray-300">
                                    <th class="px-6 py-4 text-left">Timestamp</th>
                                    <th class="px-6 py-4 text-left">User</th>
                                    <th class="px-6 py-4 text-left">Action</th>
                                    <th class="px-6 py-4 text-left">Module</th>
                                    <th class="px-6 py-4 text-left">Device Information</th>
                                    <th class="px-6 py-4 text-center">Detail</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($logs as $log)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-bold text-gray-900 dark:text-gray-100">
                                                {{ $log->created_at->format('d-M-Y') }}</div>
                                            <div class="text-[10px] text-gray-400 font-mono">
                                                {{ $log->created_at->format('h:i:s A') }}</div>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap italic text-primary-600 dark:text-primary-400 font-medium">
                                            {{ $log->user->name ?? 'System' }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap font-black uppercase text-[11px] tracking-widest">
                                            @php
                                                $color = 'text-gray-600';
                                                if (
                                                    str_contains($log->activity, 'Create') ||
                                                    str_contains($log->activity, 'Add')
                                                ) {
                                                    $color = 'text-green-600';
                                                } elseif (
                                                    str_contains($log->activity, 'Update') ||
                                                    str_contains($log->activity, 'Edit')
                                                ) {
                                                    $color = 'text-blue-600';
                                                } elseif (str_contains($log->activity, 'Delete')) {
                                                    $color = 'text-red-500';
                                                }
                                            @endphp
                                            <span class="{{ $color }}">{{ $log->activity }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 rounded text-[10px] font-bold uppercase bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                                {{ $log->module }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-[10px] text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col">
                                                <span>{{ $log->ip_address }}</span>
                                                <span class="truncate max-w-[150px]">{{ $log->user_agent }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <a href="{{ route('activity-logs.show', $log->id) }}"
                                                class="inline-flex items-center p-2 bg-primary-100 hover:bg-primary-200 text-primary-700 dark:bg-primary-900 dark:text-primary-300 rounded-full transition transform hover:scale-110">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6"
                                            class="px-6 py-10 text-center text-gray-500 italic uppercase font-black text-xs tracking-widest">
                                            No Activity Recorded Yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($logs->hasPages())
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50">
                            {{ $logs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
