/**
 * Behavior for the User Add/Edit form (app/Views/users/form.php).
 * Exposed as window.initUserForm() — see asset-form.js for why this
 * can't just be an inline <script> in the fetched fragment.
 */
function initUserForm(root) {
    root = root || document;

    const radios = root.querySelectorAll('input[name="new_personnel"]');
    const existingDiv = root.querySelector('#existingPersonnelDiv');
    const newDiv = root.querySelector('#newPersonnelDiv');
    const sgSelect = root.querySelector('#salary_grade');
    if (!radios.length || !existingDiv || !newDiv) return;

    function toggle() {
        let value = '0';
        radios.forEach(r => { if (r.checked) value = r.value; });
        existingDiv.style.display = value === '0' ? 'block' : 'none';
        newDiv.style.display = value === '1' ? 'block' : 'none';
        if (sgSelect) sgSelect.required = (value === '1');
    }

    radios.forEach(r => r.addEventListener('change', toggle));
    toggle();
}

// Non-AJAX fallback (JS-disabled / direct link access to the full page).
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('userForm')) {
        initUserForm(document);
    }
});