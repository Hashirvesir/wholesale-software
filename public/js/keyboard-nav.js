/**
 * Invenco Universal Spatial Keyboard Navigation Engine
 * Full 2D Spatial Arrow-Key Navigation across all buttons, inputs, links & cards:
 * - Up / Down / Left / Right arrows to move focus ring anywhere on screen
 * - Distinct Browser Focus Ring (Blue pulsing ring with glow)
 * - 1st Enter: Activates / Enters Edit Mode on inputs (Ring turns Green)
 * - 2nd Enter: Executes / Submits / Clicks
 * - Esc: Exits Edit Mode back to arrow-key navigation
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
        if (el.offsetParent === null && el.tagName !== 'BODY') return false;
        const style = window.getComputedStyle(el);
        if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') return false;
        const rect = el.getBoundingClientRect();
        if (rect.width === 0 || rect.height === 0) return false;
        // Check if inside a hidden or collapsed modal
        if (el.closest('[x-cloak]') || el.closest('[style*="display: none"]')) return false;
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

    function findNextSpatialTarget(direction) {
        const candidates = getAllCandidates();
        if (candidates.length === 0) return null;

        if (!currentTarget || !isVisible(currentTarget)) {
            // Find first visible primary button or input
            const firstInput = candidates.find(el => el.tagName === 'INPUT' || el.tagName === 'BUTTON');
            return firstInput || candidates[0];
        }

        const currRect = currentTarget.getBoundingClientRect();
        const currCenter = getCenter(currRect);

        let bestCandidate = null;
        let minScore = Infinity;

        candidates.forEach(cand => {
            if (cand === currentTarget) return;
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
                // Score formula: heavily penalize distance perpendicular to requested direction
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

        // Clear previous ring
        clearSpatialFocus();

        currentTarget = el;
        isEditingMode = false;

        currentTarget.classList.add('spatial-active-ring');

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

    function enterEditingMode() {
        if (!currentTarget) return;

        isEditingMode = true;
        currentTarget.classList.remove('spatial-active-ring');
        currentTarget.classList.add('spatial-editing-mode');

        // Focus natively into the input
        if (currentTarget.focus) {
            currentTarget.focus();
        }
        if (currentTarget.select && typeof currentTarget.select === 'function' && currentTarget.type !== 'date') {
            currentTarget.select();
        }

        updateHintBar();
    }

    function exitEditingMode() {
        if (!currentTarget) return;

        isEditingMode = false;
        currentTarget.classList.remove('spatial-editing-mode');
        currentTarget.classList.add('spatial-active-ring');

        // Blur native focus so arrows don't scroll text
        if (currentTarget.blur) {
            currentTarget.blur();
        }

        updateHintBar();
    }

    function executeCurrentTarget() {
        if (!currentTarget) return;

        const tag = currentTarget.tagName.toLowerCase();

        if (tag === 'button' || tag === 'a' || currentTarget.getAttribute('role') === 'button') {
            // Visual press animation
            currentTarget.classList.add('scale-95');
            setTimeout(() => currentTarget.classList.remove('scale-95'), 120);

            // Execute click
            currentTarget.click();
        } else if (tag === 'input' || tag === 'select' || tag === 'textarea') {
            // Second enter inside input executes action (submits form or filters)
            const form = currentTarget.closest('form');
            if (currentTarget.id === 'search' || currentTarget.name === 'search') {
                if (form) form.submit();
            } else if (form) {
                // Find next input or submit form
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
                        <span>Editing Mode Active</span>
                    </span>
                    <span class="text-gray-500">•</span>
                    <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-emerald-600 text-white rounded text-[11px] font-mono">Enter</kbd> Save / Next</span>
                    <span class="text-gray-500">•</span>
                    <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-gray-700 rounded text-[11px] font-mono">Esc</kbd> Exit Edit</span>
                </div>
            `;
        } else if (isInputField) {
            bar.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-primary-400"><kbd class="px-1.5 py-0.5 bg-gray-700 rounded text-[11px] font-mono">↑ ↓ ← →</kbd> Move</span>
                    <span class="text-gray-500">•</span>
                    <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-primary-600 text-white rounded text-[11px] font-mono">1st Enter</kbd> Type in Field</span>
                    <span class="text-gray-500">•</span>
                    <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-emerald-600 text-white rounded text-[11px] font-mono">2nd Enter</kbd> Execute</span>
                </div>
            `;
        } else {
            bar.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-primary-400"><kbd class="px-1.5 py-0.5 bg-gray-700 rounded text-[11px] font-mono">↑ ↓ ← →</kbd> Move</span>
                    <span class="text-gray-500">•</span>
                    <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-primary-600 text-white rounded text-[11px] font-mono">Enter</kbd> Click / Open</span>
                    <span class="text-gray-500">•</span>
                    <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-gray-700 rounded text-[11px] font-mono">?</kbd> Keys</span>
                </div>
            `;
        }
    }

    // Global Key Listener
    window.addEventListener('keydown', function (e) {
        // If actively typing inside an input in Editing Mode:
        if (isEditingMode) {
            if (e.key === 'Escape') {
                e.preventDefault();
                exitEditingMode();
                return;
            }
            if (e.key === 'Enter') {
                e.preventDefault();
                executeCurrentTarget();
                return;
            }
            // Allow normal typing and left/right cursor movement inside the text
            return;
        }

        // Check if user clicked natively into an input with mouse
        const active = document.activeElement;
        const isNativeTyping = active && (
            active.tagName === 'INPUT' ||
            active.tagName === 'TEXTAREA' ||
            active.tagName === 'SELECT' ||
            active.isContentEditable
        );

        if (isNativeTyping && !isEditingMode) {
            if (e.key === 'Escape') {
                active.blur();
                setSpatialFocus(active);
                e.preventDefault();
                return;
            }
            if (e.key === 'Enter') {
                // Enter-to-next within form
                return;
            }
        }

        // Global Shortcut: '?' for help
        if (!isNativeTyping && e.key === '?') {
            window.dispatchEvent(new CustomEvent('toggle-shortcut-help'));
            e.preventDefault();
            return;
        }

        // Spatial Arrow Keys Navigation
        let direction = null;
        if (e.key === 'ArrowRight') direction = 'right';
        else if (e.key === 'ArrowLeft') direction = 'left';
        else if (e.key === 'ArrowDown') direction = 'down';
        else if (e.key === 'ArrowUp') direction = 'up';

        if (direction) {
            // Prevent browser scroll
            e.preventDefault();

            const nextTarget = findNextSpatialTarget(direction);
            if (nextTarget) {
                setSpatialFocus(nextTarget);
            }
            return;
        }

        // Enter Key in Spatial Mode:
        if (e.key === 'Enter') {
            if (currentTarget) {
                e.preventDefault();
                const tag = currentTarget.tagName.toLowerCase();

                if (tag === 'input' || tag === 'select' || tag === 'textarea') {
                    // First Enter on input field: activates typing mode
                    enterEditingMode();
                } else {
                    // Enter on button or link: executes click
                    executeCurrentTarget();
                }
            }
            return;
        }

        // Escape Key in Spatial Mode:
        if (e.key === 'Escape') {
            if (currentTarget) {
                clearSpatialFocus();
                currentTarget = null;
                updateHintBar();
            }
            return;
        }
    }, true); // Capture phase to guarantee priority over standard defaults

    // Click synchronization: Clicking any element with mouse automatically sets spatial ring on it
    document.addEventListener('click', function (e) {
        const interactive = e.target.closest(FOCUSABLE_SELECTOR);
        if (interactive && isVisible(interactive)) {
            setSpatialFocus(interactive, false);
            if (interactive.tagName === 'INPUT' || interactive.tagName === 'TEXTAREA') {
                enterEditingMode();
            }
        }
    }, true);

    // Auto-initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function () {
        createHintBar();
        updateHintBar();

        // Initialize spatial ring on first primary element after load
        setTimeout(() => {
            const candidates = getAllCandidates();
            if (candidates.length > 0 && !currentTarget) {
                // Prioritize search input or primary create button
                const priority = candidates.find(el => el.id === 'search' || el.dataset.action === 'new' || el.tagName === 'BUTTON');
                setSpatialFocus(priority || candidates[0], false);
            }
        }, 300);
    });
})();
