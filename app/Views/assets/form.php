<?php if (!defined('APP_START')) exit;

$data = $_SESSION['form_data'] ?? ($asset ?? []);
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);

$isEdit = $isEdit ?? false;
$title = $isEdit ? 'Edit Asset' : 'Add New Asset';
$assetId = $asset['asset_id'] ?? 0;

// json_encode() escapes quotes for JavaScript, not for an HTML attribute —
// a literal " from json_encode() would otherwise terminate the onclick="..."
// attribute early and leave the browser trying to parse a truncated script.
// This wraps json_encode() output with htmlspecialchars() so it's safe to
// embed inside a double-quoted HTML attribute regardless of what characters
// (quotes, ampersands, etc.) the underlying asset data contains.
if (!function_exists('js_attr')) {
    function js_attr($value) {
        return htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8');
    }
}
?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Left column: Form -->
    <div class="md:col-span-2">
        <div class="card-panel">
            <div class="card-panel-header card-panel-header-solo">
                <span class="page-icon"><i class="bi bi-<?= $isEdit ? 'pencil-square' : 'plus-circle' ?>"></i></span>
                <span class="page-title"><?= $title ?></span>
            </div>
            <div class="card-panel-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert-app alert-app-danger alert-app-top">
                        <ul class="list-disc list-inside"><?php foreach ($errors as $err) echo '<li>'.htmlspecialchars($err).'</li>'; ?></ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?page=assets&sub=save">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="asset_id" value="<?= $asset['asset_id'] ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="asset_accounts_id" class="block text-sm font-medium text-gray-700">Account *</label>
                            <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="asset_accounts_id" name="asset_accounts_id" required>
                                <option value="">Select Account</option>
                                <?php foreach ($accounts as $acc): ?>
                                    <option value="<?= $acc['asset_accounts_id'] ?>"
                                        data-code="<?= htmlspecialchars($acc['account_code']) ?>"
                                        <?= (isset($data['asset_accounts_id']) && $data['asset_accounts_id'] == $acc['asset_accounts_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($acc['account_code'] . ' - ' . $acc['account_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Asset Code</label>
                            <?php if ($isEdit): ?>
                                <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 text-gray-600 font-mono" value="<?= htmlspecialchars($asset['asset_code'] ?? '') ?>" readonly disabled>
                                <p class="mt-1 text-xs text-gray-500">Asset codes are fixed once assigned and can't be edited.</p>
                            <?php else: ?>
                                <input type="text" id="assetCodePreview" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 text-gray-500 font-mono" value="Select an account to preview" readonly disabled>
                                <p class="mt-1 text-xs text-gray-500">Auto-generated as YEAR-ACCOUNT-#### when you save.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="asset_name" class="block text-sm font-medium text-gray-700">Asset Name *</label>
                        <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="asset_name" name="asset_name"
                               value="<?= htmlspecialchars($data['asset_name'] ?? '') ?>" required>
                        <p class="mt-1 text-xs text-gray-500" id="accountSuggestionHint"></p>
                    </div>

                    <div class="mt-4">
                        <label for="description" class="block text-sm font-medium text-gray-700">Additional Description</label>
                        <textarea class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="description" name="description" rows="2"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <label for="brand" class="block text-sm font-medium text-gray-700">Brand</label>
                            <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="brand" name="brand"
                                   value="<?= htmlspecialchars($data['brand'] ?? '') ?>">
                        </div>
                        <div>
                            <label for="model" class="block text-sm font-medium text-gray-700">Model</label>
                            <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="model" name="model"
                                   value="<?= htmlspecialchars($data['model'] ?? '') ?>">
                        </div>
                        <div>
                            <label for="serial_number" class="block text-sm font-medium text-gray-700">Serial Number</label>
                            <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="serial_number" name="serial_number"
                                   value="<?= htmlspecialchars($data['serial_number'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="acquisition_cost" class="block text-sm font-medium text-gray-700">Acquisition Cost (₱) *</label>
                            <input type="number" step="0.01" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="acquisition_cost" name="acquisition_cost"
                                value="<?= htmlspecialchars($data['acquisition_cost'] ?? '') ?>"
                                min="50000" required
                                placeholder="Minimum ₱50,000.00">
                            <p class="text-xs text-gray-500 mt-1">For PPE registration, acquisition cost must be at least ₱50,000.00.</p>
                        </div>
                        <div>
                            <label for="acquisition_date" class="block text-sm font-medium text-gray-700">Acquisition Date</label>
                            <input type="date" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="acquisition_date" name="acquisition_date"
                                   value="<?= htmlspecialchars($data['acquisition_date'] ?? '') ?>"
                                   min="1990-01-01" max="<?= \date('Y-m-d') ?>">
                            <p class="text-xs text-gray-500 mt-1" id="dateWarning"></p>
                        </div>
                    </div>

                    <?php if ($isEdit): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="status" name="status">
                                <?php foreach ($statusOptions as $opt): ?>
                                    <option value="<?= $opt ?>"
                                        <?= (isset($data['status']) && $data['status'] == $opt) ? 'selected' : '' ?>>
                                        <?= ucfirst($opt) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="condition" class="block text-sm font-medium text-gray-700">Condition</label>
                            <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="condition" name="condition">
                                <?php foreach ($conditionOptions as $opt): ?>
                                    <option value="<?= $opt ?>"
                                        <?= (isset($data['condition']) && $data['condition'] == $opt) ? 'selected' : '' ?>>
                                        <?= ucfirst($opt) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                        <textarea class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="remarks" name="remarks" rows="2"><?= htmlspecialchars($data['remarks'] ?? '') ?></textarea>
                    </div>

                    <!-- Optional Custodian Assignment -->
                    <div class="mt-6 border-t border-gray-200 pt-4">
                        <div class="flex items-center gap-2 mb-3">
                            <input type="checkbox" id="assignCustodianToggle" name="assign_custodian" value="1" 
                                <?= (isset($data['assign_custodian']) && $data['assign_custodian'] == '1') ? 'checked' : '' ?>
                                class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                            <label for="assignCustodianToggle" class="font-semibold text-gray-700">
                                <i class="bi bi-person-check"></i> Assign Custodian (Optional)
                            </label>
                        </div>
                        <div id="custodianSection" style="<?= (isset($data['assign_custodian']) && $data['assign_custodian'] == '1') ? 'display:block;' : 'display:none;' ?>">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="custodianSearch" class="block text-sm font-medium text-gray-700">Custodian</label>
                                    <input type="text" id="custodianSearch" autocomplete="off"
                                           class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition"
                                           placeholder="Type a name to filter…">
                                    <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="custodian_id" name="custodian_id" size="5">
                                        <option value="">Select Custodian</option>
                                        <?php foreach ($personnel as $p): ?>
                                            <option value="<?= $p['personnel_id'] ?>" 
                                                    data-office-id="<?= $p['office_id'] ?>"
                                                    data-salary-grade="<?= (int)($p['salary_grade'] ?? 0) ?>"
                                                <?= (isset($data['custodian_id']) && $data['custodian_id'] == $p['personnel_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p['full_name']) ?> (<?= htmlspecialchars($p['position']) ?>) — SG <?= (int)($p['salary_grade'] ?? 0) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500" id="sgWarning"></p>
                                </div>
                                <div>
                                    <label for="office_id" class="block text-sm font-medium text-gray-700">Office</label>
                                    <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="office_id" name="office_id">
                                        <option value="">Select Office</option>
                                        <?php foreach ($offices as $o): ?>
                                            <option value="<?= $o['office_id'] ?>" 
                                                <?= (isset($data['office_id']) && $data['office_id'] == $o['office_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($o['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="effectivity_date" class="block text-sm font-medium text-gray-700">Effectivity Date</label>
                                    <input type="date" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="effectivity_date" name="effectivity_date" 
                                        value="<?= htmlspecialchars($data['effectivity_date'] ?? \date('Y-m-d')) ?>">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="property_number" class="block text-sm font-medium text-gray-700">Property Number *</label>
                                    <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="property_number" name="property_number" 
                                        value="<?= htmlspecialchars($data['property_number'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
                        <a href="index.php?page=assets&sub=browse" class="btn-app btn-app-outline">Cancel</a>
                        <button type="submit" class="btn-app btn-app-primary"><?= $isEdit ? 'Update' : 'Create' ?> Asset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right column: QR Preview -->
    <div class="md:col-span-1">
        <?php if ($isEdit && $assetId): ?>
            <div class="card-panel p-6 text-center">
                <h5 class="font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">QR Code</h5>
                <img src="index.php?page=assets&sub=qr&id=<?= $assetId ?>" alt="QR Code" class="mx-auto max-w-50 border border-gray-200 p-2 rounded-lg">
                <p class="text-xs text-gray-500 mt-3">
                    <i class="bi bi-info-circle"></i> 
                    The QR code is linked to this asset record.<br>
                    Print and affix it to the physical asset.
                </p>
                <div class="mt-4 space-y-2">
                    <button class="w-full btn-app btn-app-outline-primary"
                            onclick="downloadQRLabel(<?= $assetId ?>, <?= js_attr($asset['asset_name'] ?? '') ?>, <?= js_attr($asset['asset_code'] ?? '') ?>, <?= js_attr($asset['serial_number'] ?? '') ?>, <?= js_attr($asset['brand'] ?? '') ?>, <?= js_attr($asset['model'] ?? '') ?>, <?= js_attr($asset['description'] ?? '') ?>, <?= js_attr($asset['account_code'] ?? '') ?>)">
                        <i class="bi bi-download"></i> Download PNG
                    </button>
                    <button class="w-full btn-app btn-app-primary"
                            onclick="printQR(<?= $assetId ?>, <?= js_attr($asset['asset_name'] ?? '') ?>, <?= js_attr($asset['asset_code'] ?? '') ?>, <?= js_attr($asset['serial_number'] ?? '') ?>, <?= js_attr($asset['brand'] ?? '') ?>, <?= js_attr($asset['model'] ?? '') ?>, <?= js_attr($asset['description'] ?? '') ?>, <?= js_attr($asset['account_code'] ?? '') ?>)">
                        <i class="bi bi-printer"></i> Print QR Label
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-6 text-center text-gray-500">
                <i class="bi bi-qr-code text-6xl"></i>
                <p class="mt-2">QR code will appear here<br>after saving the asset.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="public/js/qr-label.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== Asset code preview (Add mode only — actual code is generated =====
    // ===== server-side on save; this is a heads-up, not the real value.  =====
    const accountSelectForCode = document.getElementById('asset_accounts_id');
    const codePreview = document.getElementById('assetCodePreview');
    if (accountSelectForCode && codePreview) {
        const updateCodePreview = function() {
            const opt = accountSelectForCode.options[accountSelectForCode.selectedIndex];
            const accountCode = opt ? opt.getAttribute('data-code') : null;
            codePreview.value = accountCode
                ? new Date().getFullYear() + '-' + accountCode + '-####'
                : 'Select an account to preview';
        };
        accountSelectForCode.addEventListener('change', updateCodePreview);
        updateCodePreview(); // set correct state on load (e.g. after a validation error re-render)
    }

    const toggle = document.getElementById('assignCustodianToggle');
    const section = document.getElementById('custodianSection');
    const propertyNumberInput = document.getElementById('property_number');

    function syncCustodianRequirement() {
        const on = toggle.checked;
        section.style.display = on ? 'block' : 'none';
        if (propertyNumberInput) {
            propertyNumberInput.required = on;
        }
    }
    if (toggle) {
        toggle.addEventListener('change', syncCustodianRequirement);
        syncCustodianRequirement(); // set correct state on load (e.g. after a validation error re-render)
    }

    // ===== Acquisition date sanity check (client-side heads-up; server-side is authoritative) =====
    const acquisitionDateInput = document.getElementById('acquisition_date');
    const dateWarning = document.getElementById('dateWarning');
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

    // Office ↔ Custodian auto-fill
    const custodianSelect = document.getElementById('custodian_id');
    const custodianSearch = document.getElementById('custodianSearch');
    const officeSelect = document.getElementById('office_id');
    const acquisitionCostInput = document.getElementById('acquisition_cost');
    const sgWarning = document.getElementById('sgWarning');

    // Fixed SG -> threshold table, mirrors app/Helpers/SalaryGradeHelper.php.
    // Server-side validation (AssetController::save) is authoritative;
    // this is a client-side heads-up only, same as custody/form.php and verify.php.
    function sgThreshold(sg) {
        if (sg >= 1 && sg <= 7) return 70000;
        if (sg >= 8 && sg <= 10) return 500000;
        if (sg >= 11 && sg <= 17) return 1000000;
        if (sg >= 18 && sg <= 21) return 10000000;
        if (sg >= 22 && sg <= 30) return Infinity;
        return 0;
    }

    function checkSgThreshold() {
        if (!sgWarning) return;
        const selected = custodianSelect.options[custodianSelect.selectedIndex];
        const sgRaw = selected ? selected.getAttribute('data-salary-grade') : null;
        const costRaw = acquisitionCostInput ? acquisitionCostInput.value : null;
        if (!sgRaw || !costRaw) {
            sgWarning.textContent = '';
            return;
        }
        const sg = parseInt(sgRaw, 10);
        const cost = parseFloat(costRaw);
        if (!cost) {
            sgWarning.textContent = '';
            return;
        }
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

    if (custodianSelect && officeSelect) {
        const allOptions = Array.from(custodianSelect.options).filter(o => o.value !== '');

        // Renders custodian_id's <option> list from allOptions, keeping only those
        // that match BOTH the selected office (if any) and the typed search text (if any).
        function renderCustodians(officeId, searchText) {
            const currentValue = custodianSelect.value;
            custodianSelect.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Select Custodian';
            custodianSelect.appendChild(placeholder);

            const term = (searchText || '').trim().toLowerCase();
            allOptions.forEach(opt => {
                const optOffice = opt.getAttribute('data-office-id');
                const matchesOffice = (officeId === '' || optOffice == officeId);
                const matchesSearch = (term === '' || opt.textContent.toLowerCase().includes(term));
                if (matchesOffice && matchesSearch) {
                    const newOpt = document.createElement('option');
                    newOpt.value = opt.value;
                    newOpt.textContent = opt.textContent;
                    newOpt.setAttribute('data-office-id', optOffice);
                    newOpt.setAttribute('data-salary-grade', opt.getAttribute('data-salary-grade'));
                    if (opt.value === currentValue) newOpt.selected = true;
                    custodianSelect.appendChild(newOpt);
                }
            });
            checkSgThreshold();
        }

        officeSelect.addEventListener('change', function() {
            renderCustodians(this.value, custodianSearch ? custodianSearch.value : '');
        });
        if (custodianSearch) {
            custodianSearch.addEventListener('input', function() {
                renderCustodians(officeSelect.value, this.value);
            });
        }
        custodianSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (selected && selected.value) {
                const officeId = selected.getAttribute('data-office-id');
                if (officeId) {
                    officeSelect.value = officeId;
                    renderCustodians(officeId, custodianSearch ? custodianSearch.value : '');
                }
            }
            checkSgThreshold();
        });
        if (acquisitionCostInput) {
            acquisitionCostInput.addEventListener('input', checkSgThreshold);
        }
        renderCustodians(officeSelect.value, '');
    }
});
</script>
<script src="public/js/asset-account-suggest.js"></script>