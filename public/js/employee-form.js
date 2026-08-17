/**
 * Behavior for the Employee Add/Edit form (app/Views/employees/form.php).
 * Exposed as window.initEmployeeForm() — see asset-form.js for why this
 * can't just be an inline <script> in the fetched fragment.
 */
function initEmployeeForm(root) {
    root = root || document;

    const sgSelect = root.querySelector('#salary_grade');
    const hint = root.querySelector('#sgThresholdHint');
    if (!sgSelect || !hint) return;

    function updateHint() {
        const opt = sgSelect.options[sgSelect.selectedIndex];
        hint.textContent = (opt && opt.dataset.thresholdLabel) ? opt.dataset.thresholdLabel : '—';
    }

    sgSelect.addEventListener('change', updateHint);
    updateHint();
}

// Non-AJAX fallback (JS-disabled / direct link access to the full page).
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('employeeForm')) {
        initEmployeeForm(document);
    }
});