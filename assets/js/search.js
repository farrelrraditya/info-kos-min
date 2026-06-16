/**
 * InfoKosMin - Live Search & Filter UI
 *
 * Academic requirements covered:
 *   J3 - DOM manipulation: updates #result-count text content dynamically
 *   J4 - addEventListener types used: 'DOMContentLoaded', 'input', 'change'
 *
 * This script enhances the search UX by:
 *   1. Showing a live character count / result label while the user types
 *   2. Auto-submitting the filter form when dropdowns change
 *   3. Clearing the search input with a clear button
 */

'use strict';

// ─────────────────────────────────────────────
// addEventListener Type 1: DOMContentLoaded
// All bindings happen after DOM is fully loaded
// ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {

    // ─────────────────────────────────────────
    // addEventListener Type 3: 'input' event
    // Fires on every keystroke in search box
    // ─────────────────────────────────────────
    const searchInput = document.getElementById('searchInput');
    const resultCount = document.getElementById('result-count');
    const clearBtn    = document.getElementById('clearSearchBtn');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.trim();

            // DOM manipulation: update result count label
            if (resultCount) {
                if (query.length === 0) {
                    resultCount.textContent = '';
                    resultCount.classList.add('d-none');
                } else {
                    resultCount.textContent = 'Mencari: "' + query + '"';
                    resultCount.classList.remove('d-none');
                }
            }

            // Show/hide clear button
            if (clearBtn) {
                if (query.length > 0) {
                    clearBtn.classList.remove('d-none');
                } else {
                    clearBtn.classList.add('d-none');
                }
            }
        });
    }

    // Clear button: reset search input and hide label
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();

                // Trigger input event to reset UI state
                searchInput.dispatchEvent(new Event('input'));
            }
        });
    }

    // ─────────────────────────────────────────
    // addEventListener Type 4: 'change' event
    // Auto-submit filter dropdowns on change
    // ─────────────────────────────────────────
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        const selects = filterForm.querySelectorAll('select');
        selects.forEach(function (select) {
            select.addEventListener('change', function () {
                // DOM manipulation: show loading indicator
                const indicator = document.getElementById('filterLoading');
                if (indicator) {
                    indicator.classList.remove('d-none');
                }
                filterForm.submit();
            });
        });
    }

    // ─────────────────────────────────────────
    // Price range display update
    // Shows live price value as range input moves
    // ─────────────────────────────────────────
    const priceRange  = document.getElementById('max_price');
    const priceDisplay = document.getElementById('price-display');

    if (priceRange && priceDisplay) {
        priceRange.addEventListener('input', function () {
            // DOM manipulation: update price label
            const formatted = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(this.value);
            priceDisplay.textContent = '≤ ' + formatted;
        });
    }

    // ─────────────────────────────────────────
    // Highlight active nav link
    // DOM manipulation: add 'active' class to
    // the nav link matching the current URL
    // ─────────────────────────────────────────
    const currentPath = window.location.pathname;
    document.querySelectorAll('.navbar-nav .nav-link').forEach(function (link) {
        const href = link.getAttribute('href');
        if (href && currentPath.endsWith(href.split('?')[0].split('/').pop())) {
            link.classList.add('active');
            link.setAttribute('aria-current', 'page');
        }
    });

});
