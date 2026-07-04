<?php if (!defined('APP_START')) exit;
$data = $_SESSION['form_data'] ?? [];
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);
?>
<div class="card shadow">
    <div class="card-header">
        <h4><i class="bi bi-geo-alt"></i> Add Location Update</h4>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div>
        <?php endif; ?>
        <form method="POST" action="index.php?page=monitoring&sub=save">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Asset *</label>
                    <select class="form-select" name="asset_id" required>
                        <option value="">Select Asset</option>
                        <?php foreach ($assets as $a): ?>
                            <option value="<?= $a['asset_id'] ?>" <?= (isset($data['asset_id']) && $data['asset_id'] == $a['asset_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a['asset_code'] . ' - ' . $a['description']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Location Name *</label>
                    <input type="text" class="form-control" name="location_name" value="<?= htmlspecialchars($data['location_name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Site Type</label>
                    <select class="form-select" name="site_type">
                        <option value="">Select</option>
                        <option value="indoor" <?= (isset($data['site_type']) && $data['site_type'] == 'indoor') ? 'selected' : '' ?>>Indoor</option>
                        <option value="outdoor" <?= (isset($data['site_type']) && $data['site_type'] == 'outdoor') ? 'selected' : '' ?>>Outdoor</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Condition (Update)</label>
                    <select class="form-select" name="condition">
                        <option value="">No Change</option>
                        <option value="good" <?= (isset($data['condition']) && $data['condition'] == 'good') ? 'selected' : '' ?>>Good</option>
                        <option value="fair" <?= (isset($data['condition']) && $data['condition'] == 'fair') ? 'selected' : '' ?>>Fair</option>
                        <option value="poor" <?= (isset($data['condition']) && $data['condition'] == 'poor') ? 'selected' : '' ?>>Poor</option>
                        <option value="damaged" <?= (isset($data['condition']) && $data['condition'] == 'damaged') ? 'selected' : '' ?>>Damaged</option>
                        <option value="obsolete" <?= (isset($data['condition']) && $data['condition'] == 'obsolete') ? 'selected' : '' ?>>Obsolete</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Recorded By *</label>
                    <select class="form-select" name="recorded_by" required>
                        <option value="">Select User</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['users_id'] ?>" <?= (isset($data['recorded_by']) && $data['recorded_by'] == $u['users_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <a href="index.php?page=monitoring" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>