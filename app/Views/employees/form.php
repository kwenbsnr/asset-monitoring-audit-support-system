<?php if (!defined('APP_START')) exit;
$data = $_SESSION['form_data'] ?? ($record ?? []);
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);

$isEdit = $isEdit ?? false;
$title = $isEdit ? 'Edit Employee' : 'Add Employee';
?>
<div class="card-panel">
    <div class="card-panel-header">
        <div class="flex items-center gap-3">
            <span class="page-icon"><i class="bi bi-<?= $isEdit ? 'pencil-square' : 'person-plus' ?>"></i></span>
            <span class="page-title"><?= $title ?></span>
        </div>
        <a href="index.php?page=employees" class="btn-app btn-app-outline"><i class="bi bi-arrow-left"></i> Back to Employees</a>
    </div>
    <div class="card-panel-body">
        <?php if (!empty($errors)): ?>
            <div class="alert-app alert-app-danger alert-app-top">
                <ul class="list-disc list-inside"><?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul>
            </div>
        <?php endif; ?>

        <?php if ($isEdit && !empty($activeAssetCount)): ?>
            <div class="alert-app alert-app-info alert-app-top">
                <span>
                    <i class="bi bi-info-circle"></i>
                    This employee currently has <strong><?= (int)$activeAssetCount ?></strong> asset(s) under active custody. Lowering their Salary Grade below what those assets require will not automatically end custody — review Custodial Tracking if needed.
                </span>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=employees&sub=save">
            <?php if ($isEdit): ?>
                <input type="hidden" name="personnel_id" value="<?= $record['personnel_id'] ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee ID *</label>
                    <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="employee_id" name="employee_id" value="<?= htmlspecialchars($data['employee_id'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                    <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="full_name" name="full_name" value="<?= htmlspecialchars($data['full_name'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="position" class="block text-sm font-medium text-gray-700">Position</label>
                    <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="position" name="position" value="<?= htmlspecialchars($data['position'] ?? '') ?>">
                </div>
                <div>
                    <label for="designation" class="block text-sm font-medium text-gray-700">Designation</label>
                    <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="designation" name="designation" value="<?= htmlspecialchars($data['designation'] ?? '') ?>">
                </div>
                <div>
                    <label for="office_id" class="block text-sm font-medium text-gray-700">Office *</label>
                    <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="office_id" name="office_id" required>
                        <option value="">Select Office</option>
                        <?php foreach ($offices as $o): ?>
                            <option value="<?= $o['office_id'] ?>" <?= (isset($data['office_id']) && $data['office_id'] == $o['office_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($o['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="employment_status" class="block text-sm font-medium text-gray-700">Employment Status *</label>
                    <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="employment_status" name="employment_status" required>
                        <?php $curStatus = $data['employment_status'] ?? 'active'; ?>
                        <option value="active" <?= $curStatus === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="retired" <?= $curStatus === 'retired' ? 'selected' : '' ?>>Retired</option>
                        <option value="transferred" <?= $curStatus === 'transferred' ? 'selected' : '' ?>>Transferred to Another NIA Office</option>
                        <option value="inactive" <?= $curStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="salary_grade" class="block text-sm font-medium text-gray-700">Salary Grade (SG) *</label>
                    <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="salary_grade" name="salary_grade" required>
                        <option value="">Select Salary Grade</option>
                        <?php for ($sg = 1; $sg <= 30; $sg++): ?>
                            <option value="<?= $sg ?>"
                                    data-threshold-label="<?= htmlspecialchars(\App\Helpers\SalaryGradeHelper::getThresholdLabel($sg)) ?>"
                                    <?= (isset($data['salary_grade']) && (int)$data['salary_grade'] === $sg) ? 'selected' : '' ?>>
                                SG <?= $sg ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        Maximum asset value this employee can be assigned: <strong id="sgThresholdHint">—</strong>
                        <br>This limit is enforced automatically whenever an asset is assigned or reassigned to this employee.
                    </p>
                </div>
            </div>

            <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
                <a href="index.php?page=employees" class="btn-app btn-app-outline">Cancel</a>
                <button type="submit" class="btn-app btn-app-primary"><?= $isEdit ? 'Update' : 'Create' ?></button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sgSelect = document.getElementById('salary_grade');
    const hint = document.getElementById('sgThresholdHint');

    function updateHint() {
        const opt = sgSelect.options[sgSelect.selectedIndex];
        hint.textContent = (opt && opt.dataset.thresholdLabel) ? opt.dataset.thresholdLabel : '—';
    }

    sgSelect.addEventListener('change', updateHint);
    updateHint();
});
</script>