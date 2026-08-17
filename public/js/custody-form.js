/**
 * Behavior for the Custody Assign/Edit form (app/Views/custody/form.php).
 * Exposed as window.initCustodyForm() — see asset-form.js for why this
 * can't just be an inline <script> in the fetched fragment.
 */
function initCustodyForm(root) {
    root = root || document;

    const custodianSelect = root.querySelector('#custodian_id');
    const officeSelect = root.querySelector('#office_id');
    const assetSelect = root.querySelector('#asset_id');
    const sgWarning = root.querySelector('#sgWarning');
    const endDateInput = root.querySelector('#end_date');
    const statusSelect = root.querySelector('#status');

    if (!custodianSelect || !officeSelect || !assetSelect) return;

    const allCustodianOptions = Array.from(custodianSelect.options);

    // Fixed SG -> threshold table, mirrors app/Helpers/SalaryGradeHelper.php.
    // Server-side validation is authoritative; this is a client-side heads-up only.
    function sgThreshold(sg) {
        if (sg >= 1 && sg <= 7) return 70000;
        if (sg >= 8 && sg <= 10) return 500000;
        if (sg >= 11 && sg <= 17) return 1000000;
        if (sg >= 18 && sg <= 21) return 10000000;
        if (sg >= 22 && sg <= 30) return Infinity;
        return 0;
    }

    function filterCustodiansByOffice(officeId) {
        const currentValue = custodianSelect.value;
        custodianSelect.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select Custodian';
        custodianSelect.appendChild(placeholder);

        allCustodianOptions.forEach(opt => {
            if (opt.value === '') return;
            const optOfficeId = opt.getAttribute('data-office-id');
            if (officeId === '' || optOfficeId == officeId) {
                const newOpt = document.createElement('option');
                newOpt.value = opt.value;
                newOpt.textContent = opt.textContent;
                newOpt.setAttribute('data-office-id', optOfficeId);
                newOpt.setAttribute('data-salary-grade', opt.getAttribute('data-salary-grade'));
                if (opt.value === currentValue) newOpt.selected = true;
                custodianSelect.appendChild(newOpt);
            }
        });
        checkThreshold();
    }

    function checkThreshold() {
        if (!sgWarning) return;
        const costRaw = assetSelect.options[assetSelect.selectedIndex]?.getAttribute('data-cost');
        const sgRaw = custodianSelect.options[custodianSelect.selectedIndex]?.getAttribute('data-salary-grade');
        if (!costRaw || !sgRaw) {
            sgWarning.textContent = '';
            return;
        }
        const cost = parseFloat(costRaw);
        const sg = parseInt(sgRaw, 10);
        const threshold = sgThreshold(sg);
        if (cost > threshold) {
            sgWarning.textContent = 'Warning: this asset (₱' + cost.toLocaleString(undefined, {minimumFractionDigits: 2}) +
                ') exceeds SG ' + sg + '\'s threshold' + (isFinite(threshold) ? ' of ₱' + threshold.toLocaleString() : '') + '. This assignment will be rejected on save.';
            sgWarning.classList.add('text-red-600');
            sgWarning.classList.remove('text-gray-500');
        } else {
            sgWarning.textContent = '';
            sgWarning.classList.remove('text-red-600');
        }
    }

    // Keep Status in sync with the return date so the two fields can't
    // silently disagree: entering a return date means custody has ended.
    if (endDateInput && statusSelect) {
        endDateInput.addEventListener('change', function() {
            if (this.value) {
                statusSelect.value = 'inactive';
            }
        });
        statusSelect.addEventListener('change', function() {
            if (this.value === 'active') {
                endDateInput.value = '';
            }
        });
    }

    officeSelect.addEventListener('change', function() {
        filterCustodiansByOffice(this.value);
    });

    custodianSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected && selected.value) {
            const officeId = selected.getAttribute('data-office-id');
            if (officeId) {
                officeSelect.value = officeId;
            }
        }
        checkThreshold();
    });

    assetSelect.addEventListener('change', checkThreshold);

    if (officeSelect.value) {
        filterCustodiansByOffice(officeSelect.value);
    }
    checkThreshold();
}

// Non-AJAX fallback (JS-disabled / direct link access to the full page).
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('custodyForm')) {
        initCustodyForm(document);
    }
});