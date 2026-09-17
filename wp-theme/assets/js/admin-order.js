/**
 * BIS Admin Drag-and-Drop Order Manager
 * Lightweight native HTML5 Drag and Drop for ordering categories, pages, and services.
 * Based on the proven ECG architecture, styled for BIS.
 */
(function () {
    'use strict';

    /**
     * Initializes HTML5 Drag-and-Drop on a list container
     * @param {HTMLElement} wrapper The container holding draggable rows
     * @param {string} rowSel Selector for rows, e.g. '.bis-order-row'
     */
    function initDragSort(wrapper, rowSel) {
        if (!wrapper) return;

        var dragSrc = null;

        // 1. Enable draggable only on mousedown on handle to avoid text selection conflicts
        wrapper.addEventListener('mousedown', function (e) {
            var handle = e.target.closest ? e.target.closest('.bis-drag-handle') : null;
            if (!handle) return;
            var row = handle.closest(rowSel);
            if (row) {
                row.setAttribute('draggable', 'true');
            }
        });

        // 2. Clear draggable on mouseup anywhere
        document.addEventListener('mouseup', function () {
            var rows = wrapper.querySelectorAll(rowSel);
            rows.forEach(function (r) {
                r.removeAttribute('draggable');
            });
        });

        // 3. Drag start
        wrapper.addEventListener('dragstart', function (e) {
            var row = e.target.closest ? e.target.closest(rowSel) : null;
            if (!row || row.getAttribute('draggable') !== 'true') {
                e.preventDefault();
                return;
            }

            dragSrc = row;
            row.classList.add('bis-row-dragging');
            if (e.dataTransfer) {
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', '');
            }
        });

        // 4. Drag over with midpoint calculation
        wrapper.addEventListener('dragover', function (e) {
            if (!dragSrc) return;
            e.preventDefault();
            if (e.dataTransfer) {
                e.dataTransfer.dropEffect = 'move';
            }

            var target = e.target.closest ? e.target.closest(rowSel) : null;
            if (!target || target === dragSrc) return;

            var rect = target.getBoundingClientRect();
            var mid = rect.top + rect.height / 2;

            if (e.clientY < mid) {
                wrapper.insertBefore(dragSrc, target);
            } else {
                if (target.nextSibling) {
                    wrapper.insertBefore(dragSrc, target.nextSibling);
                } else {
                    wrapper.appendChild(dragSrc);
                }
            }
        });

        // 5. Drag end & update visual indices
        wrapper.addEventListener('dragend', function () {
            if (dragSrc) {
                dragSrc.classList.remove('bis-row-dragging');
                dragSrc.removeAttribute('draggable');
                dragSrc = null;
            }
            updateRowIndices(wrapper, rowSel);
        });
    }

    /**
     * Updates visible 1..N order counter badges after reordering
     */
    function updateRowIndices(wrapper, rowSel) {
        var rows = wrapper.querySelectorAll(rowSel);
        rows.forEach(function (row, idx) {
            var numEl = row.querySelector('.bis-order-num');
            if (numEl) {
                numEl.textContent = (idx + 1);
            }
        });
    }

    // Auto-init on DOM ready
    document.addEventListener('DOMContentLoaded', function () {
        var sortableLists = document.querySelectorAll('[data-drag-sort]');
        sortableLists.forEach(function (list) {
            var rowSel = list.getAttribute('data-row-selector') || '.bis-order-row';
            initDragSort(list, rowSel);
            updateRowIndices(list, rowSel);
        });

        // Category filter switcher (for popular pages and subservices)
        var categorySelect = document.querySelector('[data-bis-category-filter]');
        if (categorySelect) {
            categorySelect.addEventListener('change', function () {
                var url = this.getAttribute('data-base-url') || window.location.href;
                var paramName = this.getAttribute('data-param-name') || 'category_id';
                var parsedUrl = new URL(url, window.location.origin);
                parsedUrl.searchParams.set(paramName, this.value);
                // Remove success notices on manual change
                parsedUrl.searchParams.delete('saved');
                window.location.href = parsedUrl.toString();
            });
        }
    });
})();
