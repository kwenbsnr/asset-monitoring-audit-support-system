/**
 * Behavior for the Asset Add/Edit form (app/Views/assets/form.php).
 * Exposed as window.initAssetForm() so it can be called both on a normal
 * full-page load (non-AJAX fallback) AND after public/js/modal-forms.js
 * injects the form fragment into the shared modal.
 *
 * Safe to call more than once: every listener is attached to elements
 * freshly queried from `root`, so re-running it after a fresh fragment
 * load never double-binds anything from a previous modal open.
 *
 * NOTE: custodian assignment at registration time was removed from this
 * form — it now only creates/edits the asset record. Custody is assigned
 * separately via the Custodial Tracking module,
 * which owns the Office -> Department -> Custodian cascade, the external
 * transfer auto-head-fill, and the Salary Grade check.
 */
function initAssetForm(root) {
    root = root || document;

    // ===== Acquisition date sanity check (client-side heads-up; server-side is authoritative) =====
    const acquisitionDateInput = root.querySelector('#acquisition_date');
    const dateWarning = root.querySelector('#dateWarning');
    if (acquisitionDateInput && dateWarning) {
        acquisitionDateInput.addEventListener('input', function() {
            if (!this.value) { dateWarning.textContent = ''; return; }
            const year = parseInt(this.value.split('-')[0], 10);
            const currentYear = new Date().getFullYear();
            if (year < 1990 || year > currentYear) {
                dateWarning.textContent = 'Year must be between 1990 and ' + currentYear + '.';
                dateWarning.classList.add('text-red-600');
            } else {
                dateWarning.textContent = '';
                dateWarning.classList.remove('text-red-600');
            }
        });
    }

    // Progressive-enhancement account suggestion (public/js/asset-account-suggest.js)
    if (typeof window.initAssetAccountSuggest === 'function') {
        window.initAssetAccountSuggest(root);
    }
}

// Non-AJAX fallback: the controller can still render this same fragment
// inside the full page layout (JS-disabled / direct link access).
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('assetForm')) {
        initAssetForm(document);
    }
});