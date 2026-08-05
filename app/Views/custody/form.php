<?php if (!defined('APP_START')) exit;
$data = $_SESSION['form_data'] ?? ($record ?? []);
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);
$isEdit = $isEdit ?? false;
$title = $isEdit ? 'Edit Custody Record' : 'Assign Custody';
?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 px-6 py-4">
        <h4 class="text-xl font-bold text-gray-800"><?= $title ?></h4>
    </div>
    <div class="p-6">
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded mb-4">
                <ul class="list-disc list-inside"><?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul>
            </div>
        <?php endif; ?>
        <form method="POST" action="index.php?page=custody&sub=save" id="custodyForm">
            <?php if ($isEdit): ?>
                <input type="hidden" name="custody_id" value="<?= $record['asset_custodies_id'] ?>">
            <?php endif; ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Asset *</label>
                    <select class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" name="asset_id" id="asset_id" required>
                        <option value="">Select Asset</option>
                        <?php foreach ($assets as $a): ?>
                            <option value="<?= $a['asset_id'] ?>"
                                data-cost="<?= htmlspecialchars($a['acquisition_cost'] ?? '0') ?>"
                                <?= (isset($data['asset_id']) && $data['asset_id'] == $a['asset_id']) ? 'selected' : '' ?>
                                <?= (isset($preSelectedAsset) && $preSelectedAsset == $a['asset_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a['asset_code'] . ' - ' . $a['description']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Custodian *</label>
                    <select class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" name="custodian_id" id="custodian_id" required>
                        <option value="">Select Custodian</option>
                        <?php foreach ($personnel as $p): ?>
                            <option value="<?= $p['personnel_id'] ?>" 
                                    data-office-id="<?= $p['office_id'] ?>"
                                    data-salary-grade="<?= (int)($p['salary_grade'] ?? 0) ?>"
                                    <?= (isset($data['custodian_id']) && $data['custodian_id'] == $p['personnel_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['full_name'] . ' (' . $p['position'] . ') — SG ' . (int)($p['salary_grade'] ?? 0)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-xs text-gray-500" id="sgWarning"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Office *</label>
                    <select class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" name="office_id" id="office_id" required>
                        <option value="">Select Office</option>
                        <?php foreach ($offices as $o): ?>
                            <option value="<?= $o['office_id'] ?>" <?= (isset($data['office_id']) && $data['office_id'] == $o['office_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($o['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Effectivity Date *</label>
                    <input type="date" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" name="effectivity_date" value="<?= htmlspecialchars($data['effectivity_date'] ?? date('Y-m-d')) ?>" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" name="end_date" value="<?= htmlspecialchars($data['end_date'] ?? '') ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" name="status">
                        <option value="active" <?= (isset($data['status']) && $data['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (isset($data['status']) && $data['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Accountability Document</label>
                    <input type="text" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" name="accountability_document" value="<?= htmlspecialchars($data['accountability_document'] ?? '') ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Accountability Reference</label>
                    <input type="text" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" name="accountability_reference" value="<?= htmlspecialchars($data['accountability_reference'] ?? '') ?>">
                </div>
            </div>
            <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
                <a href="index.php?page=custody" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"><?= $isEdit ? 'Update' : 'Save' ?></button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const custodianSelect = document.getElementById('custodian_id');
    const officeSelect = document.getElementById('office_id');
    const assetSelect = document.getElementById('asset_id');
    const sgWarning = document.getElementById('sgWarning');

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
                if (opt.selected) newOpt.selected = true;
                custodianSelect.appendChild(newOpt);
            }
        });
        checkThreshold();
    }

    function checkThreshold() {
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
});
</script>
