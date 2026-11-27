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
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-primary-100 dark:bg-primary-950/60 rounded-xl text-primary-600 dark:text-primary-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-wider">Keyboard Shortcuts</h3>
                            <p class="text-xs text-gray-500">Navigate tables, forms, and POS without a mouse</p>
                        </div>
                    </div>
                    <button @click="shortcutHelpOpen = false" class="text-gray-400 hover:text-gray-500 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <!-- Universal Spatial Navigation (NEW) -->
                    <div>
                        <h4 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                            <span>Universal Spatial Navigation (Any Button / Input / Link)</span>
                        </h4>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-bold dark:text-gray-300">Move Focus Ring</span>
                                <div class="flex gap-1">
                                    <kbd class="px-1 py-0.5 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-[10px] font-mono">↑</kbd>
                                    <kbd class="px-1 py-0.5 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-[10px] font-mono">↓</kbd>
                                    <kbd class="px-1 py-0.5 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-[10px] font-mono">←</kbd>
                                    <kbd class="px-1 py-0.5 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-[10px] font-mono">→</kbd>
                                </div>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-bold dark:text-gray-300">Open Dropdown / Menu</span>
                                <kbd class="px-2 py-0.5 bg-primary-600 text-white rounded text-[11px] font-mono font-bold">Enter</kbd>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-bold dark:text-gray-300">Cycle Menu Items</span>
                                <div class="flex gap-1">
                                    <kbd class="px-1 py-0.5 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-[10px] font-mono">↑</kbd>
                                    <kbd class="px-1 py-0.5 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-[10px] font-mono">↓</kbd>
                                </div>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-bold dark:text-gray-300">1st Enter: Search / Type</span>
                                <kbd class="px-2 py-0.5 bg-emerald-600 text-white rounded text-[11px] font-mono font-bold">1st Enter</kbd>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-bold dark:text-gray-300">2nd Enter: Submit / Next</span>
                                <kbd class="px-2 py-0.5 bg-emerald-600 text-white rounded text-[11px] font-mono font-bold">2nd Enter</kbd>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-bold dark:text-gray-300">Close Dropdown / Roam</span>
                                <kbd class="px-2 py-0.5 bg-rose-600 text-white rounded text-[11px] font-mono font-bold">Esc</kbd>
                            </div>
                        </div>
                    </div>

                    <!-- Global Navigation -->
                    <div>
                        <h4 class="text-xs font-black text-primary-600 uppercase tracking-widest mb-3">Global Navigation
                        </h4>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-bold dark:text-gray-300">Dashboard</span>
                                <kbd class="px-2 py-0.5 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-[11px] font-mono">Alt + B</kbd>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-bold dark:text-gray-300">POS Counter</span>
                                <kbd class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-700 rounded text-[11px] font-mono font-bold">Alt + X</kbd>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-bold dark:text-gray-300">Sales Invoices</span>
                                <kbd class="px-2 py-0.5 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-[11px] font-mono">Alt + L</kbd>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-bold dark:text-gray-300">Purchases List</span>
                                <kbd class="px-2 py-0.5 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-[11px] font-mono">Alt + U</kbd>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-bold dark:text-gray-300">Products Catalog</span>
                                <kbd class="px-2 py-0.5 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-[11px] font-mono">Alt + I</kbd>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-bold dark:text-gray-300">Reports Center</span>
                                <kbd class="px-2 py-0.5 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-[11px] font-mono">Alt + R</kbd>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-bold dark:text-gray-300">Mobile Repairs</span>
                                <kbd class="px-2 py-0.5 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-[11px] font-mono">Alt + M</kbd>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-bold dark:text-gray-300">Quick Search</span>
                                <kbd class="px-2 py-0.5 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-[11px] font-mono">Ctrl + K</kbd>
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

                    <!-- POS Screen Shortcuts -->
                    <div>
                        <h4 class="text-xs font-black text-emerald-600 uppercase tracking-widest mb-3">POS Counter Shortcuts</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2 rounded-lg">
                                <span class="text-sm font-bold dark:text-gray-300">Scan / Search</span>
                                <kbd class="px-2 py-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-xs font-mono">F1</kbd>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2 rounded-lg">
                                <span class="text-sm font-bold dark:text-gray-300">Customer Select</span>
                                <kbd class="px-2 py-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-xs font-mono">F2</kbd>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2 rounded-lg">
                                <span class="text-sm font-bold dark:text-gray-300">Pay & Thermal Print</span>
                                <kbd class="px-2 py-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-xs font-mono">F4</kbd>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2 rounded-lg">
                                <span class="text-sm font-bold dark:text-gray-300">Hold Sale</span>
                                <kbd class="px-2 py-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-xs font-mono">F7</kbd>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2 rounded-lg">
                                <span class="text-sm font-bold dark:text-gray-300">Discount Field</span>
                                <kbd class="px-2 py-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-xs font-mono">F8</kbd>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2 rounded-lg">
                                <span class="text-sm font-bold dark:text-gray-300">Tender Cash</span>
                                <kbd class="px-2 py-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-xs font-mono">F9</kbd>
                            </div>
                        </div>
                    </div>

                    <!-- Forms -->
                    <div>
                        <h4 class="text-xs font-black text-orange-600 uppercase tracking-widest mb-3">Form Entry Control</h4>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-bold dark:text-gray-300">Jump to Next Field</span>
                                <kbd class="px-2 py-0.5 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-[11px] font-mono font-bold">Enter</kbd>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-bold dark:text-gray-300">Submit / Save Form</span>
                                <kbd class="px-2 py-0.5 bg-primary-600 text-white rounded text-[11px] font-mono font-bold">Ctrl + Enter</kbd>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-bold dark:text-gray-300">Unfocus / Clear</span>
                                <kbd class="px-2 py-0.5 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-[11px] font-mono">Esc</kbd>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-bold dark:text-gray-300">Toggle Shortcuts</span>
                                <kbd class="px-2 py-0.5 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded text-[11px] font-mono">?</kbd>
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
