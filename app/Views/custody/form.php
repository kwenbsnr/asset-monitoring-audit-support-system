<?php if (!defined('APP_START')) exit; ?>
<?php
$data = $_SESSION['form_data'] ?? ($record ?? []);
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);
$isEdit = $isEdit ?? false;
?>
<!-- Modal fragment: fetched via AJAX by public/js/modal-forms.js — no
     page chrome here, the shared #formModal shell provides it. -->
<?php if (!empty($errors)): ?>
    <div class="alert-app alert-app-danger alert-app-top">
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
            <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="asset_id" id="asset_id" required>
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
            <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="custodian_id" id="custodian_id" required>
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
            <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="office_id" id="office_id" required>
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
            <input type="date" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="effectivity_date" value="<?= htmlspecialchars($data['effectivity_date'] ?? date('Y-m-d')) ?>" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Date Returned / Relieved of Accountability</label>
            <input type="date" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="end_date" id="end_date" value="<?= htmlspecialchars($data['end_date'] ?? '') ?>">
            <p class="mt-1 text-xs text-gray-500">Leave blank while the custodian still has this asset. Fill this in only when the item has actually been returned or the custodian has been relieved of accountability — this is not a due date.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="status" id="status">
                <option value="active" <?= (isset($data['status']) && $data['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= (isset($data['status']) && $data['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
            </select>
            <p class="mt-1 text-xs text-gray-500">Automatically set to Inactive when a return date is entered above.</p>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Property Number *</label>
            <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="property_number" value="<?= htmlspecialchars($data['property_number'] ?? '') ?>" required>
        </div>
    </div>
    <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
        <button type="button" class="btn-app btn-app-outline" data-modal-close>Cancel</button>
        <button type="submit" class="btn-app btn-app-primary"><?= $isEdit ? 'Update' : 'Save' ?></button>
    </div>
</form>