<?php if (!defined('APP_START')) exit;

$data = $_SESSION['form_data'] ?? ($user ?? []);
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);

$isEdit = $isEdit ?? false;
$title = $isEdit ? 'Edit User' : 'Add User';
?>
<div class="card shadow">
    <div class="card-header">
        <h4><?= $title ?></h4>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul>
            </div>
        <?php endif; ?>
        <form method="POST" action="index.php?page=users&sub=save">
            <?php if ($isEdit): ?>
                <input type="hidden" name="user_id" value="<?= $user['users_id'] ?>">
            <?php endif; ?>

            <!-- Username -->
            <div class="mb-3">
                <label for="username" class="form-label">Username *</label>
                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($data['username'] ?? '') ?>" required>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label"><?= $isEdit ? 'New Password (leave blank to keep current)' : 'Password *' ?></label>
                <input type="password" class="form-control" id="password" name="password" <?= $isEdit ? '' : 'required' ?>>
            </div>

            <!-- Role -->
            <div class="mb-3">
                <label for="role" class="form-label">Role *</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="supply_officer" <?= (isset($data['role']) && $data['role'] == 'supply_officer') ? 'selected' : '' ?>>Supply Officer</option>
                    <option value="admin" <?= (isset($data['role']) && $data['role'] == 'admin') ? 'selected' : '' ?>>Administrator (IT Personnel)</option>
                </select>
            </div>

            <!-- Personnel selection -->
            <div class="mb-3">
                <label class="form-label">Personnel</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="new_personnel" id="existingPersonnel" value="0" checked>
                    <label class="form-check-label" for="existingPersonnel">Select existing personnel</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="new_personnel" id="newPersonnel" value="1">
                    <label class="form-check-label" for="newPersonnel">Create new personnel</label>
                </div>
            </div>

            <!-- Existing personnel dropdown -->
            <div class="mb-3" id="existingPersonnelDiv">
                <label for="personnel_id" class="form-label">Personnel</label>
                <select class="form-select" id="personnel_id" name="personnel_id">
                    <option value="">Select Personnel</option>
                    <?php foreach ($personnel as $p): ?>
                        <option value="<?= $p['personnel_id'] ?>" <?= (isset($data['personnel_id']) && $data['personnel_id'] == $p['personnel_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['full_name']) ?> (<?= htmlspecialchars($p['position']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- New personnel fields -->
            <div id="newPersonnelDiv" style="display:none;">
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($data['full_name'] ?? '') ?>">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="position" class="form-label">Position</label>
                        <input type="text" class="form-control" id="position" name="position" value="<?= htmlspecialchars($data['position'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="designation" class="form-label">Designation</label>
                        <input type="text" class="form-control" id="designation" name="designation" value="<?= htmlspecialchars($data['designation'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="office_id" class="form-label">Office</label>
                    <select class="form-select" id="office_id" name="office_id">
                        <option value="">Select Office</option>
                        <?php foreach ($offices as $o): ?>
                            <option value="<?= $o['office_id'] ?>" <?= (isset($data['office_id']) && $data['office_id'] == $o['office_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($o['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Active status -->
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?= (!isset($data['is_active']) || $data['is_active'] == 1) ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_active">Active</label>
            </div>

            <div class="d-flex justify-content-between">
                <a href="index.php?page=users" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?></button>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('input[name="new_personnel"]').forEach(el => {
        el.addEventListener('change', function() {
            document.getElementById('existingPersonnelDiv').style.display = this.value == '0' ? 'block' : 'none';
            document.getElementById('newPersonnelDiv').style.display = this.value == '1' ? 'block' : 'none';
        });
    });
</script>