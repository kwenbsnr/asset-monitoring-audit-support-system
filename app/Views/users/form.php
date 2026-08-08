<?php if (!defined('APP_START')) exit;

$data = $_SESSION['form_data'] ?? ($user ?? []);
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);

$isEdit = $isEdit ?? false;
$title = $isEdit ? 'Edit User' : 'Add User';
?>
<div class="card-panel">
    <div class="card-panel-header" style="justify-content:flex-start;">
        <span class="page-icon"><i class="bi bi-<?= $isEdit ? 'pencil-square' : 'person-plus' ?>"></i></span>
        <span class="page-title"><?= $title ?></span>
    </div>
    <div class="card-panel-body">
        <?php if (!empty($errors)): ?>
            <div class="alert-app alert-app-danger" style="align-items:flex-start;">
                <ul class="list-disc list-inside"><?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul>
            </div>
        <?php endif; ?>
        <form method="POST" action="index.php?page=users&sub=save">
            <?php if ($isEdit): ?>
                <input type="hidden" name="user_id" value="<?= $user['users_id'] ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username *</label>
                    <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="username" name="username" value="<?= htmlspecialchars($data['username'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700"><?= $isEdit ? 'New Password (leave blank to keep current)' : 'Password *' ?></label>
                    <input type="password" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="password" name="password" <?= $isEdit ? '' : 'required' ?>>
                </div>
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Role *</label>
                    <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="role" name="role" required>
                        <option value="supply_officer" <?= (isset($data['role']) && $data['role'] == 'supply_officer') ? 'selected' : '' ?>>Supply Officer</option>
                        <option value="admin" <?= (isset($data['role']) && $data['role'] == 'admin') ? 'selected' : '' ?>>Administrator (IT Personnel)</option>
                    </select>
                </div>
            </div>

            <!-- Personnel selection -->
            <div class="mt-4 border-t border-gray-200 pt-4">
                <p class="font-medium text-gray-700 mb-2">Personnel</p>
                <p class="text-xs text-gray-500 mb-2">
                    Prefer selecting an existing employee profile from
                    <a href="index.php?page=employees" class="text-green-700 hover:underline">Employee Management</a>
                    — it's the single source of truth for Salary Grade and employment status used by asset assignment rules.
                </p>
                <div class="flex gap-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="new_personnel" value="0" checked class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500" onchange="togglePersonnelFields()">
                        <span class="ml-2 text-sm text-gray-700">Select existing personnel</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="new_personnel" value="1" class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500" onchange="togglePersonnelFields()">
                        <span class="ml-2 text-sm text-gray-700">Create new personnel</span>
                    </label>
                </div>
            </div>

            <!-- Existing personnel dropdown -->
            <div class="mt-3" id="existingPersonnelDiv">
                <label for="personnel_id" class="block text-sm font-medium text-gray-700">Personnel</label>
                <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="personnel_id" name="personnel_id">
                    <option value="">Select Personnel</option>
                    <?php foreach ($personnel as $p): ?>
                        <option value="<?= $p['personnel_id'] ?>" <?= (isset($data['personnel_id']) && $data['personnel_id'] == $p['personnel_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['full_name']) ?> (<?= htmlspecialchars($p['position']) ?> — SG <?= (int)($p['salary_grade'] ?? 0) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- New personnel fields -->
            <div id="newPersonnelDiv" style="display:none;" class="mt-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="full_name" name="full_name" value="<?= htmlspecialchars($data['full_name'] ?? '') ?>">
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
                        <label for="office_id" class="block text-sm font-medium text-gray-700">Office</label>
                        <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="office_id" name="office_id">
                            <option value="">Select Office</option>
                            <?php foreach ($offices as $o): ?>
                                <option value="<?= $o['office_id'] ?>" <?= (isset($data['office_id']) && $data['office_id'] == $o['office_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($o['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="salary_grade" class="block text-sm font-medium text-gray-700">Salary Grade *</label>
                        <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="salary_grade" name="salary_grade">
                            <option value="">Select Salary Grade</option>
                            <?php for ($sg = 1; $sg <= 30; $sg++): ?>
                                <option value="<?= $sg ?>" <?= (isset($data['salary_grade']) && (int)$data['salary_grade'] === $sg) ? 'selected' : '' ?>>SG <?= $sg ?></option>
                            <?php endfor; ?>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Determines the maximum asset value this employee can be assigned.</p>
                    </div>
                </div>
            </div>

            <!-- Active status -->
            <div class="mt-4 flex items-center">
                <input type="checkbox" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500" id="is_active" name="is_active" value="1" <?= (!isset($data['is_active']) || $data['is_active'] == 1) ? 'checked' : '' ?>>
                <label class="ml-2 text-sm text-gray-700" for="is_active">Active</label>
            </div>

            <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
                <a href="index.php?page=users" class="btn-app btn-app-outline">Cancel</a>
                <button type="submit" class="btn-app btn-app-primary"><?= $isEdit ? 'Update' : 'Create' ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePersonnelFields() {
    const radios = document.querySelectorAll('input[name="new_personnel"]');
    let value = '0';
    radios.forEach(r => { if (r.checked) value = r.value; });
    document.getElementById('existingPersonnelDiv').style.display = value === '0' ? 'block' : 'none';
    document.getElementById('newPersonnelDiv').style.display = value === '1' ? 'block' : 'none';
    document.getElementById('salary_grade').required = (value === '1');
}
document.addEventListener('DOMContentLoaded', togglePersonnelFields);
</script>