/**
 * Invenco Unified Keyboard Navigation Engine
 * Provides Tally / Busy ERP style keyboard-first workflow:
 * - Up / Down arrows to navigate table rows
 * - Enter to open/view highlighted invoice or voucher
 * - V, E, P, R for View, Edit, Print, Return actions
 * - / or F1 to jump to search
 * - Alt+N or Insert for New Invoice/Bill
 * - Enter-to-Next-Field on form entry
 * - Ctrl+Enter to save/submit
 */

(function () {
    'use strict';

    let currentRowIndex = -1;
    let cachedRows = [];
    let activeTable = null;
    let hintBar = null;

    function getTable() {
        return document.querySelector('[data-keyboard-table]') ||
            document.querySelector('.keyboard-nav-table') ||
            document.querySelector('table');
    }

    function getRows() {
        const table = getTable();
        if (!table) return [];
        const tbody = table.querySelector('tbody') || table;
        const rows = Array.from(tbody.querySelectorAll('tr')).filter(tr => {
            // Exclude empty state rows, headers or full-width alert rows
            if (tr.offsetParent === null) return false;
            if (tr.querySelector('td[colspan]')) return false;
            if (tr.classList.contains('empty-row') || tr.classList.contains('no-kb-nav')) return false;
            return true;
        });
        return rows;
    }

    function selectRow(index) {
        cachedRows = getRows();
        if (cachedRows.length === 0) return;

        // Clear previous selection
        cachedRows.forEach(r => r.classList.remove('kb-selected-row'));

        if (index < 0) index = 0;
        if (index >= cachedRows.length) index = cachedRows.length - 1;

        currentRowIndex = index;
        const row = cachedRows[currentRowIndex];
        if (!row) return;

        row.classList.add('kb-selected-row');

        // Smooth scroll into view if needed
        row.scrollIntoView({ block: 'nearest', behavior: 'smooth' });

        // Update bottom hint bar
        showHintBar(true);
    }

    function clearRowSelection() {
        cachedRows = getRows();
        cachedRows.forEach(r => r.classList.remove('kb-selected-row'));
        currentRowIndex = -1;
        showHintBar(false);
    }

    function createHintBar() {
        if (hintBar) return hintBar;
        const bar = document.createElement('div');
        bar.id = 'invenco-kb-hint-bar';
        bar.className = 'fixed bottom-4 left-1/2 -translate-x-1/2 z-40 bg-gray-900/95 dark:bg-gray-800/95 text-white backdrop-blur-md px-4 py-2 rounded-2xl shadow-2xl border border-gray-700/80 text-xs font-semibold flex items-center gap-3 transition-all duration-200 opacity-0 pointer-events-none transform translate-y-2';
        bar.innerHTML = `
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-gray-700 rounded text-[11px] font-mono">↑</kbd><kbd class="px-1.5 py-0.5 bg-gray-700 rounded text-[11px] font-mono">↓</kbd> Rows</span>
                <span class="text-gray-500">•</span>
                <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-primary-600 rounded text-[11px] font-mono text-white">Enter</kbd> Open</span>
                <span class="text-gray-500">•</span>
                <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-gray-700 rounded text-[11px] font-mono">E</kbd> Edit</span>
                <span class="text-gray-500">•</span>
                <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-gray-700 rounded text-[11px] font-mono">P</kbd> Print</span>
                <span class="text-gray-500">•</span>
                <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-gray-700 rounded text-[11px] font-mono">/</kbd> Search</span>
                <span class="text-gray-500">•</span>
                <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-gray-700 rounded text-[11px] font-mono">Alt+N</kbd> New</span>
            </div>
        `;
        document.body.appendChild(bar);
        hintBar = bar;
        return bar;
    }

    function showHintBar(show) {
        const bar = createHintBar();
        if (show && cachedRows.length > 0) {
            bar.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-2');
            bar.classList.add('opacity-100', 'translate-y-0');
        } else {
            bar.classList.remove('opacity-100', 'translate-y-0');
            bar.classList.add('opacity-0', 'pointer-events-none', 'translate-y-2');
        }
    }

    function triggerRowAction(action) {
        if (currentRowIndex < 0 || currentRowIndex >= cachedRows.length) return;
        const row = cachedRows[currentRowIndex];
        if (!row) return;

        let target = null;
        if (action === 'view') {
            target = row.querySelector('[data-action="view"]') ||
                row.querySelector('a[href*="/sales/"]:not([href*="/edit"]):not([href*="/pdf"]):not([href*="/thermal"]), a[href*="/purchases/"]:not([href*="/edit"]):not([href*="/pdf"]), a[href*="/vouchers/"], a[href*="/ledger"]') ||
                row.querySelector('a');
        } else if (action === 'edit') {
            target = row.querySelector('[data-action="edit"]') ||
                row.querySelector('a[href*="/edit"]');
        } else if (action === 'print') {
            target = row.querySelector('[data-action="print"]') ||
                row.querySelector('a[href*="/thermal"]') ||
                row.querySelector('a[href*="/pdf"]') ||
                row.querySelector('a[href*="/print"]');
        } else if (action === 'return') {
            target = row.querySelector('[data-action="return"]') ||
                row.querySelector('a[href*="/return"]');
        }

        if (target) {
            target.click();
        } else if (action === 'view' && row.dataset.href) {
            window.location.href = row.dataset.href;
        }
    }

    // Enter-to-Next-Field on Forms
    function setupEnterToNext(e) {
        if (e.key !== 'Enter' || e.shiftKey || e.ctrlKey || e.altKey || e.metaKey) return;

        const target = e.target;
        if (!target) return;

        // Skip textareas, submit buttons, and search inputs with custom enter handlers
        if (target.tagName === 'TEXTAREA' || target.type === 'submit' || target.type === 'button') return;
        if (target.id === 'search' || target.name === 'search' || target.classList.contains('main-search-input')) return;

        // Skip inputs that have their own custom keyboard navigation or Alpine keydown listeners
        if (target.classList.contains('keyboard-nav') || target.hasAttribute('x-on:keydown') || target.hasAttribute('@keydown')) return;

        const form = target.closest('form');
        if (!form) return;

        // Collect all focusable fields
        const focusables = Array.from(form.querySelectorAll(
            'input:not([type="hidden"]):not([disabled]):not([readonly]), select:not([disabled]):not([readonly]), textarea:not([disabled]):not([readonly]), button[type="submit"]:not([disabled])'
        )).filter(el => el.offsetParent !== null);

        const currentIndex = focusables.indexOf(target);
        if (currentIndex !== -1 && currentIndex < focusables.length - 1) {
            e.preventDefault();
            const nextEl = focusables[currentIndex + 1];
            nextEl.focus();
            if (nextEl.select && typeof nextEl.select === 'function' && nextEl.type !== 'date') {
                nextEl.select();
            }
        }
    }

    // Global Key Listener
    window.addEventListener('keydown', function (e) {
        const active = document.activeElement;
        const isTyping = active && (
            active.tagName === 'INPUT' ||
            active.tagName === 'TEXTAREA' ||
            active.tagName === 'SELECT' ||
            active.isContentEditable
        );

        // 1. Enter-to-Next in forms (when typing in form elements)
        if (isTyping && e.key === 'Enter') {
            setupEnterToNext(e);
            return;
        }

        // 2. Quick Search: '/' or 'F1' (Only when not already typing)
        if (!isTyping && (e.key === '/' || e.key === 'F1')) {
            const searchInput = document.querySelector('#search, input[name="search"], input[type="search"], .main-search-input');
            if (searchInput) {
                e.preventDefault();
                searchInput.focus();
                if (searchInput.select) searchInput.select();
                return;
            }
        }

        // 3. New Invoice / Record: 'Alt + N' or 'Insert'
        if ((e.altKey && (e.key.toLowerCase() === 'n')) || e.key === 'Insert') {
            const newBtn = document.querySelector('[data-action="new"]') ||
                document.querySelector('a[href*="/sales/create"], a[href*="/purchases/create"], a[href*="/parties/create"], a[href*="/create"]');
            if (newBtn) {
                e.preventDefault();
                newBtn.click();
                return;
            }
        }

        // 4. Escape: Clear selection or blur input
        if (e.key === 'Escape') {
            if (isTyping) {
                active.blur();
                e.preventDefault();
            } else if (currentRowIndex !== -1) {
                clearRowSelection();
                e.preventDefault();
            }
            return;
        }

        // 5. Help Modal: '?' or 'F12'
        if (!isTyping && e.key === '?') {
            window.dispatchEvent(new CustomEvent('toggle-shortcut-help'));
            e.preventDefault();
            return;
        }

        // 6. Table Navigation (Arrow Up, Arrow Down, Home, End, PageUp/Down)
        if (!isTyping) {
            const rows = getRows();
            if (rows.length > 0) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectRow(currentRowIndex + 1);
                    return;
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectRow(currentRowIndex - 1);
                    return;
                } else if (e.key === 'Home') {
                    e.preventDefault();
                    selectRow(0);
                    return;
                } else if (e.key === 'End') {
                    e.preventDefault();
                    selectRow(rows.length - 1);
                    return;
                } else if (e.key === 'Enter') {
                    if (currentRowIndex >= 0) {
                        e.preventDefault();
                        triggerRowAction('view');
                        return;
                    }
                } else if (e.key.toLowerCase() === 'v') {
                    e.preventDefault();
                    triggerRowAction('view');
                    return;
                } else if (e.key.toLowerCase() === 'e') {
                    e.preventDefault();
                    triggerRowAction('edit');
                    return;
                } else if (e.key.toLowerCase() === 'p') {
                    e.preventDefault();
                    triggerRowAction('print');
                    return;
                } else if (e.key.toLowerCase() === 'r') {
                    e.preventDefault();
                    triggerRowAction('return');
                    return;
                } else if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
                    const prevPage = document.querySelector('a[rel="prev"]');
                    if (prevPage) {
                        e.preventDefault();
                        prevPage.click();
                        return;
                    }
                } else if (e.key === 'ArrowRight' || e.key === 'PageDown') {
                    const nextPage = document.querySelector('a[rel="next"]');
                    if (nextPage) {
                        e.preventDefault();
                        nextPage.click();
                        return;
                    }
                }
            }
        }
    });

    // Auto-initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function () {
        const table = getTable();
        if (table) {
            createHintBar();
            // Highlight first row on index pages automatically after small delay
            setTimeout(() => {
                const rows = getRows();
                if (rows.length > 0 && currentRowIndex === -1) {
                    selectRow(0);
                }
            }, 300);
        }
    });
})();
