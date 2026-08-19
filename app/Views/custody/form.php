<?php if (!defined('APP_START')) exit; ?>
<?php
$data = $_SESSION['form_data'] ?? ($record ?? []);
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);
$isEdit = $isEdit ?? false;
$recordMode = $recordMode ?? 'internal';
$selectedTopOffice = $selectedTopOffice ?? 0;
$selectedDepartment = $selectedDepartment ?? 0;
$departments = $departments ?? [];
$custodians = $custodians ?? [];
$topOffices = $topOffices ?? [];
$externalOffices = $externalOffices ?? [];
?>
<!-- Modal fragment: fetched via AJAX by public/js/modal-forms.js — no
     page chrome here, the shared #formModal shell provides it.

     Field order follows the actual decision flow, not an arbitrary
     grouping:
       Internal: Asset -> Office -> Department -> Custodian -> details
       External: Asset -> Destination Sub-office -> Head (auto) -> details
     "Assign" vs "Transfer" is decided server-side in
     CustodyController::save() from (a) whether the destination is
     internal or external and (b) whether the asset already has an
     active custodian — never from (b) alone. -->
<?php if (!empty($errors)): ?>
    <div class="alert-app alert-app-danger alert-app-top">
        <ul class="list-disc list-inside"><?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul>
    </div>
<?php endif; ?>
<form method="POST" action="index.php?page=custody&sub=save" id="custodyForm">
    <?php if ($isEdit): ?>
        <input type="hidden" name="custody_id" value="<?= $record['asset_custodies_id'] ?>">
    <?php endif; ?>

    <!-- Step 0: which asset. Each option carries its CURRENT custody (if
         any) so JS can show "Assign" vs "Transfer" the moment an asset
         is picked, without a round-trip. -->
    <div>
        <label class="block text-sm font-medium text-gray-700">Asset *</label>
        <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="asset_id" id="asset_id" required>
            <option value="">Select Asset</option>
            <?php foreach ($assets as $a): ?>
                <option value="<?= $a['asset_id'] ?>"
                    data-cost="<?= htmlspecialchars($a['acquisition_cost'] ?? '0') ?>"
                    data-has-custodian="<?= !empty($a['current_custodian_id']) ? '1' : '0' ?>"
                    data-current-custodian-name="<?= htmlspecialchars($a['current_custodian_name'] ?? '') ?>"
                    data-current-office-name="<?= htmlspecialchars($a['current_office_name'] ?? '') ?>"
                    <?= (isset($data['asset_id']) && $data['asset_id'] == $a['asset_id']) ? 'selected' : '' ?>
                    <?= (isset($preSelectedAsset) && $preSelectedAsset == $a['asset_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['asset_code'] . ' - ' . $a['description']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="mt-1 text-xs font-medium" id="actionTypeIndicator"></p>
    </div>

    <!-- Step 1: which kind of movement. Everything below re-shapes
         around this choice — it is not a cosmetic tab, it changes which
         fields are required and what gets submitted. -->
    <div class="mt-4">
        <label class="block text-sm font-medium text-gray-700">Movement Type *</label>
        <div class="mt-1 grid grid-cols-1 sm:grid-cols-2 gap-2">
            <label class="flex items-center gap-2 border border-gray-300 rounded-lg px-3 py-2 cursor-pointer">
                <input type="radio" name="assignment_mode" id="mode_internal" value="internal" <?= $recordMode === 'internal' ? 'checked' : '' ?>>
                <span>Within the Same Office <span class="text-xs text-gray-500">(Assign / Transfer)</span></span>
            </label>
            <label class="flex items-center gap-2 border border-gray-300 rounded-lg px-3 py-2 cursor-pointer">
                <input type="radio" name="assignment_mode" id="mode_external" value="external" <?= $recordMode === 'external' ? 'checked' : '' ?>>
                <span>To Another Sub-office <span class="text-xs text-gray-500">(Transfer)</span></span>
            </label>
        </div>
    </div>

    <!-- ===== Internal path: Office -> Department -> Custodian ===== -->
    <div id="internalSection" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Office *</label>
            <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="top_office_id">
                <?php foreach ($topOffices as $o): ?>
                    <option value="<?= $o['office_id'] ?>" <?= $selectedTopOffice == $o['office_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($o['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Department *</label>
            <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="department_id" id="department_id">
                <option value="">Select Department</option>
                <?php foreach ($departments as $d): ?>
                    <option value="<?= $d['office_id'] ?>" <?= $selectedDepartment == $d['office_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Custodian *</label>
            <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="custodian_id" id="custodian_id">
                <option value="">Select Department first</option>
                <?php foreach ($custodians as $p): ?>
                    <option value="<?= $p['personnel_id'] ?>"
                            data-salary-grade="<?= (int)($p['salary_grade'] ?? 0) ?>"
                            <?= (isset($data['custodian_id']) && $data['custodian_id'] == $p['personnel_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['full_name'] . ' (' . $p['position'] . ') — SG ' . (int)($p['salary_grade'] ?? 0)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="mt-1 text-xs text-gray-500">Choices are limited to personnel in the selected Department.</p>
            <p class="mt-1 text-xs text-gray-500" id="sgWarning"></p>
        </div>
    </div>

    <!-- ===== External path: Destination Sub-office -> Head (auto) ===== -->
    <div id="externalSection" class="mt-4" style="display:none;">
        <label class="block text-sm font-medium text-gray-700">Destination Sub-office *</label>
        <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="destination_office_id" id="destination_office_id">
            <option value="">Select Sub-office</option>
            <?php foreach ($externalOffices as $o): ?>
                <option value="<?= $o['office_id'] ?>"
                        data-head-name="<?= htmlspecialchars($o['head_name'] ?? '') ?>"
                        data-head-position="<?= htmlspecialchars($o['head_position'] ?? '') ?>"
                        data-has-head="<?= !empty($o['head_personnel_id']) ? '1' : '0' ?>"
                        <?= (!$isEdit ? false : ($record['office_id'] == $o['office_id'])) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($o['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="mt-3 border border-gray-200 rounded-lg px-3 py-2 bg-gray-50">
            <div class="text-xs text-gray-500">Accountable Officer (auto-determined)</div>
            <div class="font-medium text-gray-800" id="externalHeadDisplay">—</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
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
        <button type="submit" class="btn-app btn-app-primary" id="custodySubmitBtn"><?= $isEdit ? 'Update' : 'Save' ?></button>
    </div>
</form>