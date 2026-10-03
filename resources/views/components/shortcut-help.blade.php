@props(['open' => false])

<div x-data="{ shortcutHelpOpen: false }" @toggle-shortcut-help.window="shortcutHelpOpen = !shortcutHelpOpen"
    @keydown.window.ctrl.slash.prevent="shortcutHelpOpen = !shortcutHelpOpen"
    @keydown.escape.window="shortcutHelpOpen = false">
    <!-- Modal -->
    <div x-show="shortcutHelpOpen" x-cloak class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" @click="shortcutHelpOpen = false">
        </div>

        <!-- Content -->
        <div class="flex min-h-screen items-center justify-center p-4">
            <div
                class="relative w-full max-w-lg transform rounded-2xl bg-white dark:bg-gray-800 p-8 shadow-2xl ring-1 ring-black/5 transition-all">
                <div class="flex justify-between items-center mb-6 border-b dark:border-gray-700 pb-4">
                    <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-widest">Keyboard
                        Shortcuts</h3>
                    <button @click="shortcutHelpOpen = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <!-- Navigation -->
                    <div>
                        <h4 class="text-xs font-black text-primary-600 uppercase tracking-widest mb-3">Global Navigation
                        </h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div
                                class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2 rounded-lg">
                                <span class="text-sm font-bold dark:text-gray-300">Quick Search</span>
                                <kbd
                                    class="px-2 py-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-xs font-mono">Ctrl
                                    + K</kbd>
                            </div>
                            <div
                                class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2 rounded-lg">
                                <span class="text-sm font-bold dark:text-gray-300">Dashboard</span>
                                <kbd
                                    class="px-2 py-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-xs font-mono">Alt
                                    + B</kbd>
                            </div>
                            <div
                                class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2 rounded-lg">
                                <span class="text-sm font-bold dark:text-gray-300">Shortcuts Help</span>
                                <kbd
                                    class="px-2 py-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-xs font-mono">Ctrl
                                    + /</kbd>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div>
                        <h4 class="text-xs font-black text-green-600 uppercase tracking-widest mb-3">Quick Actions</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div
                                class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2 rounded-lg">
                                <span class="text-sm font-bold dark:text-gray-300">New Sale</span>
                                <kbd
                                    class="px-2 py-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-xs font-mono">Alt
                                    + S</kbd>
                            </div>
                            <div
                                class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2 rounded-lg">
                                <span class="text-sm font-bold dark:text-gray-300">New Purchase</span>
                                <kbd
                                    class="px-2 py-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-xs font-mono">Alt
                                    + P</kbd>
                            </div>
                            <div
                                class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2 rounded-lg">
                                <span class="text-sm font-bold dark:text-gray-300">Add Product</span>
                                <kbd
                                    class="px-2 py-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-xs font-mono">Alt
                                    + Q</kbd>
                            </div>
                            <div
                                class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2 rounded-lg">
                                <span class="text-sm font-bold dark:text-gray-300">Add Category</span>
                                <kbd
                                    class="px-2 py-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-xs font-mono">Alt
                                    + C</kbd>
                            </div>
                        </div>
                    </div>

                    <!-- Forms -->
                    <div>
                        <h4 class="text-xs font-black text-orange-600 uppercase tracking-widest mb-3">Form Control</h4>
                        <div class="grid grid-cols-1 gap-4">
                            <div
                                class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2 rounded-lg">
                                <span class="text-sm font-bold dark:text-gray-300">Submit / Save Form</span>
                                <kbd
                                    class="px-2 py-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-xs font-mono">Ctrl
                                    + Enter</kbd>
                            </div>
                            <div
                                class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2 rounded-lg">
                                <span class="text-sm font-bold dark:text-gray-300">Close Modals/Search</span>
                                <kbd
                                    class="px-2 py-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-xs font-mono">ESC</kbd>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 text-center text-xs text-gray-500 italic">
                    All shortcuts work globally from any page.
                </div>
            </div>
        </div>
    </div>
</div>
