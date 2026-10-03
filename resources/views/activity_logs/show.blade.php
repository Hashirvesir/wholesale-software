<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 text-sm">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-4">
                    <a href="{{ route('activity-logs.index') }}"
                        class="p-2 bg-white dark:bg-gray-800 rounded-full hover:bg-gray-100 transition shadow-md border border-gray-200 dark:border-gray-700">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h3 class="text-lg text-gray-900 dark:text-gray-100 italic font-black uppercase tracking-widest">
                        Action Details</h3>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Info Card -->
                <div class="md:col-span-1 space-y-4">
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 transition-all duration-300">
                        <h4 class="text-xs font-black uppercase text-gray-400 mb-4 tracking-widest border-b pb-2">Log
                            Information</h4>
                        <div class="space-y-4">
                            <div>
                                <p class="text-[10px] font-black uppercase text-gray-500 tracking-tighter">Activity</p>
                                <p class="font-bold text-primary-600 dark:text-primary-400">{{ $activityLog->activity }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase text-gray-500 tracking-tighter">Module /
                                    Record ID</p>
                                <p class="font-bold text-gray-900 dark:text-gray-100">{{ $activityLog->module }}
                                    #{{ $activityLog->record_id }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase text-gray-500 tracking-tighter">Performed By
                                </p>
                                <p class="font-bold text-gray-900 dark:text-gray-100">
                                    {{ $activityLog->user->name ?? 'System' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase text-gray-500 tracking-tighter">Timestamp</p>
                                <p class="font-bold text-gray-900 dark:text-gray-100">
                                    {{ $activityLog->created_at->format('d-M-Y h:i:s A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comparison Section -->
                <div class="md:col-span-2">
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 transition-all duration-300 h-full">
                        <h4 class="text-xs font-black uppercase text-gray-400 mb-4 tracking-widest border-b pb-2">
                            Payload Comparison</h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Old State -->
                            <div>
                                <h5
                                    class="text-[10px] font-black uppercase text-red-500 tracking-widest mb-2 border-l-2 border-red-500 pl-2">
                                    Previous State</h5>
                                <div
                                    class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl font-mono text-[11px] overflow-auto max-h-[400px] border border-gray-100 dark:border-gray-800">
                                    @if ($activityLog->old_payload)
                                        <div class="space-y-1">
                                            @foreach ($activityLog->old_payload as $key => $value)
                                                @if (!is_array($value))
                                                    <div
                                                        class="flex justify-between border-b border-gray-200 dark:border-gray-800 pb-1">
                                                        <span class="text-gray-500">{{ $key }}:</span>
                                                        <span
                                                            class="font-medium text-gray-700 dark:text-gray-300">{{ $value }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="italic text-gray-400">No previous state recorded</span>
                                    @endif
                                </div>
                            </div>

                            <!-- New State -->
                            <div>
                                <h5
                                    class="text-[10px] font-black uppercase text-green-500 tracking-widest mb-2 border-l-2 border-green-500 pl-2">
                                    Current State</h5>
                                <div
                                    class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl font-mono text-[11px] overflow-auto max-h-[400px] border border-gray-100 dark:border-gray-800">
                                    @if ($activityLog->new_payload)
                                        <div class="space-y-1">
                                            @foreach ($activityLog->new_payload as $key => $value)
                                                @if (!is_array($value))
                                                    <div
                                                        class="flex justify-between border-b border-gray-200 dark:border-gray-800 pb-1">
                                                        <span class="text-gray-500">{{ $key }}:</span>
                                                        <span
                                                            class="font-medium text-gray-700 dark:text-gray-300">{{ $value }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="italic text-gray-400">No new state recorded</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Full JSON Toggle -->
                        <div x-data="{ open: false }" class="mt-6 border-t pt-4">
                            <button @click="open = !open"
                                class="text-[10px] font-black uppercase text-primary-500 tracking-widest hover:underline flex items-center gap-2">
                                <span x-text="open ? 'Hide RAW JSON' : 'Show RAW JSON'"></span>
                                <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" x-collapse
                                class="mt-4 p-4 bg-gray-900 text-green-400 rounded-xl font-mono text-[9px] overflow-x-auto shadow-inner">
                                <pre>Old: {{ json_encode($activityLog->old_payload, JSON_PRETTY_PRINT) }}</pre>
                                <hr class="my-4 border-gray-800">
                                <pre>New: {{ json_encode($activityLog->new_payload, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
