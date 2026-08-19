/**
 * Custody Assign/Transfer modal.
 *
 * Two independent flows, toggled by "assignment_mode":
 *   internal: Office -> Department -> Custodian (cascading selects)
 *   external: Destination Sub-office -> Division Manager/Head (auto)
 *
 * "Assign" vs "Transfer" is a DISPLAY-ONLY indicator here (the server
 * is the source of truth and recomputes it in CustodyController::save()).
 * It is derived from:
 *   - external mode            -> always "Transfer"
 *   - internal mode + no prior custodian on the selected asset -> "Assign"
 *   - internal mode + has prior custodian on the selected asset -> "Transfer"
 *
 * Mirrors the Salary Grade brackets in app/Helpers/SalaryGradeHelper.php
 * for an instant client-side warning; the server re-validates regardless.
 */
function initCustodyForm() {
    const form = document.getElementById('custodyForm');
    if (!form) return;

    const assetSelect = document.getElementById('asset_id');
    const modeInternal = document.getElementById('mode_internal');
    const modeExternal = document.getElementById('mode_external');
    const internalSection = document.getElementById('internalSection');
    const externalSection = document.getElementById('externalSection');

    const topOfficeSelect = document.getElementById('top_office_id');
    const departmentSelect = document.getElementById('department_id');
    const custodianSelect = document.getElementById('custodian_id');

    const destinationOfficeSelect = document.getElementById('destination_office_id');
    const externalHeadDisplay = document.getElementById('externalHeadDisplay');

    const actionTypeIndicator = document.getElementById('actionTypeIndicator');
    const sgWarning = document.getElementById('sgWarning');
    const endDateInput = document.getElementById('end_date');
    const statusSelect = document.getElementById('status');

    const SG_BRACKETS = [
        { min: 1, max: 7, threshold: 70000 },
        { min: 8, max: 10, threshold: 500000 },
        { min: 11, max: 17, threshold: 1000000 },
        { min: 18, max: 21, threshold: 10000000 },
        { min: 22, max: 30, threshold: Infinity },
    ];

    function thresholdFor(sg) {
        const b = SG_BRACKETS.find(x => sg >= x.min && sg <= x.max);
        return b ? b.threshold : 0;
    }

    // ---------- Mode toggle ----------
    function applyMode() {
        const isExternal = modeExternal && modeExternal.checked;
        if (internalSection) internalSection.style.display = isExternal ? 'none' : '';
        if (externalSection) externalSection.style.display = isExternal ? '' : 'none';

        // Only the fields belonging to the active path are required, so
        // the browser doesn't block submission on a hidden field.
        if (departmentSelect) departmentSelect.required = !isExternal;
        if (custodianSelect) custodianSelect.required = !isExternal;
        if (destinationOfficeSelect) destinationOfficeSelect.required = isExternal;

        updateActionIndicator();
    }

    if (modeInternal) modeInternal.addEventListener('change', applyMode);
    if (modeExternal) modeExternal.addEventListener('change', applyMode);

    // ---------- Internal cascade: Office -> Department -> Custodian ----------
    function loadDepartments(officeId, preselectDepartmentId) {
        if (!departmentSelect) return;
        departmentSelect.innerHTML = '<option value="">Loading...</option>';
        custodianSelect.innerHTML = '<option value="">Select Department first</option>';

        if (!officeId) {
            departmentSelect.innerHTML = '<option value="">Select Department</option>';
            return;
        }

        fetch(`index.php?page=custody&sub=department_json&office_id=${officeId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(list => {
                departmentSelect.innerHTML = '<option value="">Select Department</option>';
                list.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.office_id;
                    opt.textContent = d.name;
                    if (preselectDepartmentId && String(preselectDepartmentId) === String(d.office_id)) {
                        opt.selected = true;
                    }
                    departmentSelect.appendChild(opt);
                });
                if (departmentSelect.value) {
                    loadCustodians(departmentSelect.value);
                }
            })
            .catch(() => {
                departmentSelect.innerHTML = '<option value="">Failed to load departments</option>';
            });
    }

    function loadCustodians(departmentId, preselectCustodianId) {
        if (!custodianSelect) return;
        custodianSelect.innerHTML = '<option value="">Loading...</option>';

        if (!departmentId) {
            custodianSelect.innerHTML = '<option value="">Select Department first</option>';
            return;
        }

        fetch(`index.php?page=custody&sub=custodian_json&department_id=${departmentId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(list => {
                if (!list.length) {
                    custodianSelect.innerHTML = '<option value="">No active personnel in this Department</option>';
                    return;
                }
                custodianSelect.innerHTML = '<option value="">Select Custodian</option>';
                list.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.personnel_id;
                    opt.dataset.salaryGrade = p.salary_grade || 0;
                    opt.textContent = `${p.full_name} (${p.position || ''}) — SG ${p.salary_grade || 0}`;
                    if (preselectCustodianId && String(preselectCustodianId) === String(p.personnel_id)) {
                        opt.selected = true;
                    }
                    custodianSelect.appendChild(opt);
                });
                checkSalaryGrade();
            })
            .catch(() => {
                custodianSelect.innerHTML = '<option value="">Failed to load custodians</option>';
            });
    }

    if (topOfficeSelect) {
        topOfficeSelect.addEventListener('change', () => loadDepartments(topOfficeSelect.value));
    }
    if (departmentSelect) {
        departmentSelect.addEventListener('change', () => loadCustodians(departmentSelect.value));
    }
    if (custodianSelect) {
        custodianSelect.addEventListener('change', checkSalaryGrade);
    }

    // ---------- External: Destination Sub-office -> Head (auto) ----------
    function applyExternalHead() {
        if (!destinationOfficeSelect || !externalHeadDisplay) return;
        const opt = destinationOfficeSelect.selectedOptions[0];
        if (!opt || !opt.value) {
            externalHeadDisplay.textContent = '—';
            return;
        }
        if (opt.dataset.hasHead !== '1') {
            externalHeadDisplay.textContent = 'No Division Manager/Head on file for this sub-office.';
            return;
        }
        const name = opt.dataset.headName || '';
        const position = opt.dataset.headPosition || '';
        externalHeadDisplay.textContent = position ? `${name} (${position})` : name;
    }

    if (destinationOfficeSelect) {
        destinationOfficeSelect.addEventListener('change', () => {
            applyExternalHead();
            updateActionIndicator();
        });
        applyExternalHead();
    }

    // ---------- Assign / Transfer indicator (display only) ----------
    function updateActionIndicator() {
        if (!actionTypeIndicator || !assetSelect) return;
        const isExternal = modeExternal && modeExternal.checked;
        const opt = assetSelect.selectedOptions[0];

        if (!opt || !opt.value) {
            actionTypeIndicator.textContent = '';
            return;
        }

        if (isExternal) {
            actionTypeIndicator.textContent = 'This will be recorded as a Transfer to another sub-office.';
            actionTypeIndicator.className = 'mt-1 text-xs font-medium text-amber-600';
            return;
        }

        const hasCustodian = opt.dataset.hasCustodian === '1';
        if (hasCustodian) {
            const from = opt.dataset.currentCustodianName || 'the current custodian';
            actionTypeIndicator.textContent = `This will be recorded as a Transfer (currently with ${from}).`;
            actionTypeIndicator.className = 'mt-1 text-xs font-medium text-amber-600';
        } else {
            actionTypeIndicator.textContent = 'This will be recorded as a first-time Assign (no previous custodian).';
            actionTypeIndicator.className = 'mt-1 text-xs font-medium text-green-600';
        }
    }

    if (assetSelect) {
        assetSelect.addEventListener('change', () => {
            checkSalaryGrade();
            updateActionIndicator();
        });
    }

    // ---------- Salary Grade warning (internal path only; UX hint) ----------
    function checkSalaryGrade() {
        if (!sgWarning) return;
        const isExternal = modeExternal && modeExternal.checked;
        sgWarning.textContent = '';
        if (isExternal) return;

        const assetOpt = assetSelect && assetSelect.selectedOptions[0];
        const custodianOpt = custodianSelect && custodianSelect.selectedOptions[0];
        if (!assetOpt || !assetOpt.value || !custodianOpt || !custodianOpt.value) return;

        const cost = parseFloat(assetOpt.dataset.cost || '0');
        const sg = parseInt(custodianOpt.dataset.salaryGrade || '0', 10);
        const threshold = thresholdFor(sg);

        if (cost > threshold) {
            sgWarning.textContent = `Warning: this asset's value exceeds the assignable threshold for Salary Grade ${sg}. The server will reject this on save.`;
            sgWarning.className = 'mt-1 text-xs text-red-600';
        }
    }

    // ---------- Returned/relieved date auto-sets status ----------
    if (endDateInput && statusSelect) {
        endDateInput.addEventListener('change', () => {
            if (endDateInput.value) {
                statusSelect.value = 'inactive';
            }
        });
    }

    // ---------- Initial state on open ----------
    applyMode();
    updateActionIndicator();
    checkSalaryGrade();

    // If the internal Department select already has a value pre-rendered
    // (edit mode) but no top-office change has fired yet, nothing further
    // to do — the controller pre-loaded matching Department/Custodian
    // lists server-side. Only wire the case where a Department is
    // selected but Custodians weren't pre-loaded (defensive).
    if (departmentSelect && departmentSelect.value && custodianSelect && custodianSelect.options.length <= 1) {
        loadCustodians(departmentSelect.value, form.dataset.presetCustodianId);
    }
}