/**
 * InfoKosMin - Delete Confirmation Modal Controller
 *
 * Academic requirements covered:
 *   J2 - confirm() before delete (implemented via Bootstrap Modal)
 *   J3 - DOM manipulation: injects item name and form action into modal
 *   B3 - Bootstrap Modal component
 *
 * Usage:
 *   Add to any delete button:
 *     class="btn btn-danger btn-delete"
 *     data-name="Item Name"
 *     data-action="/path/to/delete.php?id=X"
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {

    const deleteModal    = document.getElementById('deleteModal');
    const modalNameEl    = document.getElementById('modalDeleteName');
    const modalFormEl    = document.getElementById('modalDeleteForm');

    if (!deleteModal || !modalNameEl || !modalFormEl) {
        return; // Modal not present on this page, skip
    }

    const bsModal = new bootstrap.Modal(deleteModal);

    // Bind click handler to ALL delete buttons on the page
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            // Read data attributes from the clicked button
            const itemName   = this.dataset.name   || 'item ini';
            const formAction = this.dataset.action  || '#';

            // DOM manipulation: inject item name into modal body
            modalNameEl.textContent = itemName;

            // DOM manipulation: set the form's POST action URL
            modalFormEl.setAttribute('action', formAction);

            // Show the Bootstrap modal
            bsModal.show();
        });
    });

    // When modal is hidden, reset its content (cleanup)
    deleteModal.addEventListener('hidden.bs.modal', function () {
        modalNameEl.textContent    = '—';
        modalFormEl.setAttribute('action', '#');
    });

});
