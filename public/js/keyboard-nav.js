/**
 * Invenco Universal Spatial Keyboard Navigation Engine
 * Full 2D Spatial Arrow-Key Navigation across all buttons, inputs, links & cards:
 * - Up / Down / Left / Right arrows to move focus ring anywhere on screen (including POS)
 * - Distinct Browser Focus Ring (Blue pulsing ring with glow)
 * - Navigation & Action Dropdowns: Enter opens, Up/Down cycles menu items, Enter navigates, Esc closes
 * - 1st Enter: Activates Search/Edit Mode on inputs/selects (Ring turns Green, opens search dropdowns)
 * - 2nd Enter: Executes / Submits / Selects option / Advances to next field
 * - Esc: Immediately closes open dropdowns, exits edit mode (Ring turns Blue), arrow keys roam freely everywhere
 */

(function () {
    'use strict';

    let currentTarget = null;
    let isEditingMode = false;
    let hintBar = null;

    const FOCUSABLE_SELECTOR = `
        a[href]:not([disabled]):not([tabindex="-1"]),
        button:not([disabled]):not([tabindex="-1"]),
        input:not([type="hidden"]):not([disabled]):not([tabindex="-1"]),
        select:not([disabled]):not([tabindex="-1"]),
        textarea:not([disabled]):not([tabindex="-1"]),
        [tabindex="0"],
        [role="button"]
    `;

    function isVisible(el) {
        if (!el) return false;
        if (el.offsetParent === null && el.tagName !== 'BODY') {
            const style = window.getComputedStyle(el);
            if (style.position !== 'fixed' && style.position !== 'sticky') return false;
        }
        const style = window.getComputedStyle(el);
        if (style.display === 'none' || style.visibility === 'hidden') return false;
        const rect = el.getBoundingClientRect();
        if (rect.width === 0 || rect.height === 0) return false;
        if (el.closest('[x-cloak]')) return false;
        return true;
    }

    function getAllCandidates() {
        const all = Array.from(document.querySelectorAll(FOCUSABLE_SELECTOR));
        return all.filter(isVisible);
    }

    function getCenter(rect) {
        return {
            x: rect.left + rect.width / 2,
            y: rect.top + rect.height / 2
        };
    }

    // Helper: Detect if element is an <x-dropdown> trigger button
    function getDropdownForTrigger(el) {
        if (!el) return null;
        const tag = el.tagName.toLowerCase();
        if (tag !== 'button' && tag !== 'a' && el.getAttribute('role') !== 'button') return null;

        // Container is the closest relative container with x-data that has an open property
        const container = el.closest('[x-data]');
        if (!container || !window.Alpine) return null;
        try {
            const data = window.Alpine.$data(container);
            if (data && 'open' in data) {
                const menu = container.querySelector('[x-show="open"]');
                if (menu) {
                    return { container, data, menu, trigger: el };
                }
            }
        } catch (e) {}
        return null;
    }

    // Helper: Detect if element is an item inside an <x-dropdown> menu
    function getDropdownForMenuItem(el) {
        if (!el) return null;
        const menu = el.closest('[x-show="open"]');
        if (!menu) return null;
        const container = menu.closest('[x-data]');
        if (!container || !window.Alpine) return null;
        try {
            const data = window.Alpine.$data(container);
            const trigger = container.querySelector('button') || container.querySelector('a');
            return { container, data, menu, trigger };
        } catch (e) {}
        return null;
    }

    function findNextSpatialTarget(direction) {
        // 1. If currently inside an open <x-dropdown> menu, navigate strictly up/down through its items
        const dropdownItem = getDropdownForMenuItem(currentTarget);
        if (dropdownItem) {
            const items = Array.from(dropdownItem.menu.querySelectorAll('a[href], button')).filter(el => {
                const s = window.getComputedStyle(el);
                return s.display !== 'none' && s.visibility !== 'hidden';
            });

            if (items.length > 0) {
                const currentIdx = items.indexOf(currentTarget);
                if (direction === 'down') {
                    const nextIdx = (currentIdx + 1) % items.length;
                    return items[nextIdx];
                } else if (direction === 'up') {
                    if (currentIdx === 0 && dropdownItem.trigger) {
                        return dropdownItem.trigger;
                    }
                    const prevIdx = (currentIdx - 1 + items.length) % items.length;
                    return items[prevIdx];
                } else if (direction === 'left' || direction === 'right') {
                    dropdownItem.data.open = false;
                }
            }
        }

        // 2. If on a dropdown trigger button and its dropdown is open, pressing down enters the menu
        const dropdownTrigger = getDropdownForTrigger(currentTarget);
        if (dropdownTrigger && dropdownTrigger.data.open) {
            if (direction === 'down') {
                const items = Array.from(dropdownTrigger.menu.querySelectorAll('a[href], button')).filter(el => {
                    const s = window.getComputedStyle(el);
                    return s.display !== 'none' && s.visibility !== 'hidden';
                });
                if (items.length > 0) {
                    return items[0];
                }
            }
        }

        const candidates = getAllCandidates();
        if (candidates.length === 0) return null;

        if (!currentTarget || !isVisible(currentTarget)) {
            const firstInput = candidates.find(el => el.id === 'search' || el.tagName === 'INPUT' || el.tagName === 'BUTTON');
            return firstInput || candidates[0];
        }

        const currRect = currentTarget.getBoundingClientRect();
        const currCenter = getCenter(currRect);

        let bestCandidate = null;
        let minScore = Infinity;

        candidates.forEach(cand => {
            if (cand === currentTarget) return;

            // Ignore items in closed dropdowns
            const closedMenu = cand.closest('[x-show="open"]');
            if (closedMenu && window.getComputedStyle(closedMenu).display === 'none') return;

            const candRect = cand.getBoundingClientRect();
            const candCenter = getCenter(candRect);

            let primaryDist = 0;
            let secondaryDist = 0;
            let isValidDirection = false;

            switch (direction) {
                case 'right':
                    primaryDist = candCenter.x - currCenter.x;
                    secondaryDist = Math.abs(candCenter.y - currCenter.y);
                    isValidDirection = candRect.left >= currRect.left + 5;
                    break;
                case 'left':
                    primaryDist = currCenter.x - candCenter.x;
                    secondaryDist = Math.abs(candCenter.y - currCenter.y);
                    isValidDirection = candRect.right <= currRect.right - 5;
                    break;
                case 'down':
                    primaryDist = candCenter.y - currCenter.y;
                    secondaryDist = Math.abs(candCenter.x - currCenter.x);
                    isValidDirection = candRect.top >= currRect.top + 5;
                    break;
                case 'up':
                    primaryDist = currCenter.y - candCenter.y;
                    secondaryDist = Math.abs(candCenter.x - currCenter.x);
                    isValidDirection = candRect.bottom <= currRect.bottom - 5;
                    break;
            }

            if (isValidDirection && primaryDist > 0) {
                const score = primaryDist + (secondaryDist * 2.2);
                if (score < minScore) {
                    minScore = score;
                    bestCandidate = cand;
                }
            }
        });

        return bestCandidate;
    }

    function setSpatialFocus(el, smoothScroll = true) {
        if (!el) return;

        // Blur any previously focused element so native cursor doesn't trap arrow keys
        if (document.activeElement && document.activeElement !== el && document.activeElement.blur) {
            document.activeElement.blur();
        }

        clearSpatialFocus();

        currentTarget = el;
        isEditingMode = false;

        currentTarget.classList.add('spatial-active-ring');

        // Only give native focus to buttons and links in roaming mode
        // Inputs & selects stay unfocused until user presses Enter to activate Search/Edit Mode
        const tag = el.tagName.toLowerCase();
        if (tag === 'button' || tag === 'a' || el.getAttribute('role') === 'button') {
            if (el.focus) el.focus({ preventScroll: true });
        }

        if (smoothScroll) {
            currentTarget.scrollIntoView({
                block: 'nearest',
                inline: 'nearest',
                behavior: 'smooth'
            });
        }

        updateHintBar();
    }

    function clearSpatialFocus() {
        if (currentTarget) {
            currentTarget.classList.remove('spatial-active-ring', 'spatial-editing-mode');
        }
    }

    function closeAllDropdowns() {
        if (window.Alpine) {
            try {
                if (currentTarget) {
                    const data = window.Alpine.$data(currentTarget);
                    if (data) {
                        if ('customerOpen' in data) data.customerOpen = false;
                        if ('vendorOpen' in data) data.vendorOpen = false;
                        if ('productDropdownOpen' in data) data.productDropdownOpen = false;
                        if ('customerDropdownOpen' in data) data.customerDropdownOpen = false;
                        if ('items' in data && Array.isArray(data.items)) {
                            data.items.forEach(it => {
                                if (it && typeof it === 'object' && 'open' in it) it.open = false;
                            });
                        }
                    }
                    if (typeof window.Alpine.evaluate === 'function') {
                        try { window.Alpine.evaluate(currentTarget, 'item.open = false'); } catch (e) {}
                    }
                }
            } catch (err) {}
        }

        // Close any open <x-dropdown> menus
        document.querySelectorAll('[x-show="open"]:not([style*="display: none"])').forEach(menu => {
            const container = menu.closest('[x-data]');
            if (container && window.Alpine) {
                try {
                    const data = window.Alpine.$data(container);
                    if (data && 'open' in data) {
                        data.open = false;
                    }
                } catch (e) {}
            }
        });
    }

    function enterEditingMode() {
        if (!currentTarget) return;

        isEditingMode = true;
        currentTarget.classList.remove('spatial-active-ring');
        currentTarget.classList.add('spatial-editing-mode');

        // Give native browser focus and select text
        if (currentTarget.focus) {
            currentTarget.focus();
        }
        if (currentTarget.select && typeof currentTarget.select === 'function' && currentTarget.type !== 'date') {
            try { currentTarget.select(); } catch (e) {}
        }

        // Open native select picker if supported
        if (currentTarget.tagName.toLowerCase() === 'select') {
            if (typeof currentTarget.showPicker === 'function') {
                try { currentTarget.showPicker(); } catch (e) {}
            }
        }

        // Open Alpine dropdowns (customer, vendor, product, etc.)
        if (window.Alpine) {
            try {
                const data = window.Alpine.$data(currentTarget);
                if (data) {
                    if ('customerOpen' in data) data.customerOpen = true;
                    if ('vendorOpen' in data) data.vendorOpen = true;
                    if ('productDropdownOpen' in data) data.productDropdownOpen = true;
                    if ('customerDropdownOpen' in data) data.customerDropdownOpen = true;
                }
                if (typeof window.Alpine.evaluate === 'function') {
                    try { window.Alpine.evaluate(currentTarget, 'item.open = true'); } catch (e) {}
                }
            } catch (err) {}
        }

        // Trigger input / focus events so reactive search lists show up immediately
        currentTarget.dispatchEvent(new Event('input', { bubbles: true }));
        currentTarget.dispatchEvent(new Event('focus', { bubbles: true }));

        updateHintBar();
    }

    function exitEditingMode() {
        closeAllDropdowns();
        isEditingMode = false;

        if (currentTarget) {
            currentTarget.classList.remove('spatial-editing-mode');
            currentTarget.classList.add('spatial-active-ring');
        }

        // Blur native focus so arrow keys roam freely across all elements
        if (document.activeElement && document.activeElement.blur) {
            document.activeElement.blur();
        }

        updateHintBar();
    }

    function executeCurrentTarget() {
        if (!currentTarget) return;

        // 1. Check if clicking an <x-dropdown> trigger button (3-dots or navbar dropdown)
        const dropdownTrigger = getDropdownForTrigger(currentTarget);
        if (dropdownTrigger) {
            if (!dropdownTrigger.data.open) {
                // Open dropdown
                dropdownTrigger.data.open = true;
                setTimeout(() => {
                    const items = Array.from(dropdownTrigger.menu.querySelectorAll('a[href], button')).filter(el => {
                        const s = window.getComputedStyle(el);
                        return s.display !== 'none' && s.visibility !== 'hidden';
                    });
                    if (items.length > 0) {
                        setSpatialFocus(items[0], true);
                    }
                }, 40);
            } else {
                dropdownTrigger.data.open = false;
            }
            return;
        }

        // 2. Check if clicking an item inside an open <x-dropdown> menu
        const dropdownItem = getDropdownForMenuItem(currentTarget);
        if (dropdownItem) {
            const targetEl = currentTarget;
            dropdownItem.data.open = false;

            if (targetEl.tagName.toLowerCase() === 'a' && targetEl.href) {
                targetEl.click();
                if (targetEl.href && !targetEl.href.startsWith('javascript:')) {
                    window.location.href = targetEl.href;
                }
                return;
            }

            targetEl.click();
            return;
        }

        const tag = currentTarget.tagName.toLowerCase();

        if (tag === 'button' || tag === 'a' || currentTarget.getAttribute('role') === 'button') {
            currentTarget.classList.add('scale-95');
            setTimeout(() => currentTarget.classList.remove('scale-95'), 120);

            // Execute click
            currentTarget.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true, view: window }));
            currentTarget.click();
        } else if (tag === 'input' || tag === 'select' || tag === 'textarea') {
            const form = currentTarget.closest('form');
            if (currentTarget.id === 'search' || currentTarget.name === 'search') {
                if (form) form.submit();
            } else if (form) {
                const candidates = getAllCandidates().filter(c => form.contains(c));
                const idx = candidates.indexOf(currentTarget);
                if (idx !== -1 && idx < candidates.length - 1) {
                    const nextEl = candidates[idx + 1];
                    exitEditingMode();
                    setSpatialFocus(nextEl);
                    if (nextEl.tagName === 'INPUT' || nextEl.tagName === 'SELECT') {
                        enterEditingMode();
                    }
                } else {
                    form.dispatchEvent(new Event('submit', { cancelable: true }));
                    form.submit();
                }
            }
        }
    }

    function createHintBar() {
        if (hintBar) return hintBar;
        const bar = document.createElement('div');
        bar.id = 'invenco-spatial-hint-bar';
        bar.className = 'fixed bottom-4 left-1/2 -translate-x-1/2 z-50 bg-gray-900/95 dark:bg-gray-800/95 text-white backdrop-blur-md px-4 py-2 rounded-2xl shadow-2xl border border-gray-700/80 text-xs font-semibold flex items-center gap-3 transition-all duration-200 opacity-90';
        document.body.appendChild(bar);
        hintBar = bar;
        return bar;
    }

    function updateHintBar() {
        const bar = createHintBar();
        if (!currentTarget) {
            bar.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-primary-400 font-bold"><kbd class="px-1.5 py-0.5 bg-gray-700 rounded text-[11px] font-mono">↑ ↓ ← →</kbd> Spatial Nav</span>
                    <span class="text-gray-500">•</span>
                    <span>Press any arrow key to navigate</span>
                </div>
            `;
            return;
        }

        const tag = currentTarget.tagName.toLowerCase();
        const isInputField = tag === 'input' || tag === 'select' || tag === 'textarea';

        if (isEditingMode) {
            bar.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 text-emerald-400 font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        <span>Search / Edit Mode Active</span>
                    </span>
                    <span class="text-gray-500">•</span>
                    <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-emerald-600 text-white rounded text-[11px] font-mono">Enter</kbd> Select / Submit</span>
                    <span class="text-gray-500">•</span>
                    <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-red-600 text-white rounded text-[11px] font-mono">Esc</kbd> Exit & Roam Everywhere</span>
                </div>
            `;
        } else if (isInputField) {
            bar.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-primary-400 font-bold"><kbd class="px-1.5 py-0.5 bg-gray-700 rounded text-[11px] font-mono">↑ ↓ ← →</kbd> Roam</span>
                    <span class="text-gray-500">•</span>
                    <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-primary-600 text-white rounded text-[11px] font-mono">Enter</kbd> Activate Search / Select</span>
                </div>
            `;
        } else {
            bar.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-primary-400 font-bold"><kbd class="px-1.5 py-0.5 bg-gray-700 rounded text-[11px] font-mono">↑ ↓ ← →</kbd> Roam</span>
                    <span class="text-gray-500">•</span>
                    <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-primary-600 text-white rounded text-[11px] font-mono">Enter</kbd> Click / Open</span>
                    <span class="text-gray-500">•</span>
                    <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-gray-700 rounded text-[11px] font-mono">?</kbd> Keys</span>
                </div>
            `;
        }
    }

    // Global Key Listener (Bubble Phase - allows input autocomplete to handle keys first)
    window.addEventListener('keydown', function (e) {
        if (e.defaultPrevented) return;

        const active = document.activeElement;
        const isInputActive = active && (
            active.tagName === 'INPUT' ||
            active.tagName === 'TEXTAREA' ||
            active.tagName === 'SELECT' ||
            active.isContentEditable
        );

        // 1. ESCAPE KEY (Highest Priority):
        // "or phr escape karo to wo har har joga ghuma samajy"
        if (e.key === 'Escape') {
            e.preventDefault();
            e.stopPropagation();

            // If inside an open <x-dropdown> menu item, close menu and restore focus to trigger
            const dropdownItem = getDropdownForMenuItem(currentTarget);
            if (dropdownItem) {
                dropdownItem.data.open = false;
                if (dropdownItem.trigger) {
                    setSpatialFocus(dropdownItem.trigger, true);
                }
                return;
            }

            // If on a dropdown trigger whose menu is open, close it
            const dropdownTrigger = getDropdownForTrigger(currentTarget);
            if (dropdownTrigger && dropdownTrigger.data.open) {
                dropdownTrigger.data.open = false;
                return;
            }

            if (isEditingMode || isInputActive) {
                exitEditingMode();
                return;
            }

            closeAllDropdowns();
            return;
        }

        // 2. WHILE IN EDITING / SEARCHING MODE:
        if (isEditingMode || isInputActive) {
            // Check if an autocomplete dropdown list is currently visible
            const openDropdown = document.querySelector(
                '[x-show*="customerOpen"]:not([style*="display: none"]), ' +
                '[x-show*="vendorOpen"]:not([style*="display: none"]), ' +
                '[x-show*="item.open"]:not([style*="display: none"]), ' +
                '[x-show*="productDropdown"]:not([style*="display: none"]), ' +
                '[x-show*="customerDropdownOpen"]:not([style*="display: none"]), ' +
                '[x-show*="partyOpen"]:not([style*="display: none"])'
            );

            // If Enter is pressed inside an input/select
            if (e.key === 'Enter') {
                const hasCustomHandler = active && (
                    active.hasAttribute('@keydown') ||
                    active.hasAttribute('x-on:keydown') ||
                    active.classList.contains('keyboard-nav')
                );

                // If dropdown is open and input has custom Alpine handler (e.g. handleCustomerKey), let Alpine select the item
                if (hasCustomHandler && openDropdown) {
                    return;
                }

                e.preventDefault();
                e.stopPropagation();
                executeCurrentTarget();
                return;
            }

            // Arrow keys inside open dropdown or native select
            if (openDropdown || (active && active.tagName === 'SELECT')) {
                if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                    return; // Let native select or Alpine autocomplete navigate suggestions
                }
            }

            // ArrowLeft & ArrowRight move cursor inside the text input
            if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                return;
            }

            // If user presses ArrowUp or ArrowDown on a standard input with no dropdown open:
            // Exit editing mode and navigate spatially!
            if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                exitEditingMode();
            } else {
                return;
            }
        }

        // 3. GLOBAL SHORTCUT '?' FOR HELP
        if (e.key === '?' && !isEditingMode && !isInputActive) {
            window.dispatchEvent(new CustomEvent('toggle-shortcut-help'));
            e.preventDefault();
            e.stopPropagation();
            return;
        }

        // 4. SPATIAL ARROW KEYS NAVIGATION (ROAMING MODE):
        let direction = null;
        if (e.key === 'ArrowRight') direction = 'right';
        else if (e.key === 'ArrowLeft') direction = 'left';
        else if (e.key === 'ArrowDown') direction = 'down';
        else if (e.key === 'ArrowUp') direction = 'up';

        if (direction) {
            e.preventDefault();
            e.stopPropagation();

            const nextTarget = findNextSpatialTarget(direction);
            if (nextTarget) {
                setSpatialFocus(nextTarget);
            }
            return;
        }

        // 5. ENTER KEY IN ROAMING MODE:
        if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();

            if (currentTarget) {
                const tag = currentTarget.tagName.toLowerCase();
                if (tag === 'input' || tag === 'select' || tag === 'textarea') {
                    // Enter activates typing / searching / opens dropdown
                    enterEditingMode();
                } else {
                    // Enter on button or link executes click / opens menu
                    executeCurrentTarget();
                }
            }
            return;
        }
    }, false);

    // Focus synchronization: When an element receives browser focus (via click, tab, or code)
    document.addEventListener('focusin', function (e) {
        const interactive = e.target.closest(FOCUSABLE_SELECTOR);
        if (interactive && isVisible(interactive)) {
            if (currentTarget !== interactive) {
                clearSpatialFocus();
                currentTarget = interactive;
            }
            const tag = interactive.tagName.toLowerCase();
            if (tag === 'input' || tag === 'select' || tag === 'textarea') {
                isEditingMode = true;
                interactive.classList.remove('spatial-active-ring');
                interactive.classList.add('spatial-editing-mode');
            } else {
                isEditingMode = false;
                interactive.classList.add('spatial-active-ring');
            }
            updateHintBar();
        }
    }, true);

    // Auto-initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function () {
        createHintBar();
        updateHintBar();

        setTimeout(() => {
            const candidates = getAllCandidates();
            if (candidates.length > 0 && !currentTarget) {
                const priority = candidates.find(el => el.dataset.action === 'new' || el.id === 'search' || el.tagName === 'BUTTON');
                setSpatialFocus(priority || candidates[0], false);
            }
        }, 200);
    });
})();
