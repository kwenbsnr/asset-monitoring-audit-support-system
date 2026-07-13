<?php if (!defined('APP_START')) exit;
$data = $_SESSION['form_data'] ?? ($record ?? []);
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);
$isEdit = $isEdit ?? false;
$title = $isEdit ? 'Edit Custody Record' : 'Assign Custody';
?>
<div class="card shadow">
    <div class="card-header">
        <h4><?= $title ?></h4>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div>
        <?php endif; ?>
        <form method="POST" action="index.php?page=custody&sub=save">
            <?php if ($isEdit): ?>
                <input type="hidden" name="custody_id" value="<?= $record['asset_custodies_id'] ?>">
            <?php endif; ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Asset *</label>
                    <select class="form-select" name="asset_id" required>
                        <option value="">Select Asset</option>
                        <?php foreach ($assets as $a): ?>
                            <option value="<?= $a['asset_id'] ?>" 
                                <?= (isset($data['asset_id']) && $data['asset_id'] == $a['asset_id']) ? 'selected' : '' ?>
                                <?= (isset($preSelectedAsset) && $preSelectedAsset == $a['asset_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a['asset_code'] . ' - ' . $a['description']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Custodian *</label>
                    <select class="form-select" name="custodian_id" required>
                        <option value="">Select Custodian</option>
                        <?php foreach ($personnel as $p): ?>
                            <option value="<?= $p['personnel_id'] ?>" <?= (isset($data['custodian_id']) && $data['custodian_id'] == $p['personnel_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['full_name'] . ' (' . $p['position'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Office *</label>
                    <select class="form-select" name="office_id" required>
                        <option value="">Select Office</option>
                        <?php foreach ($offices as $o): ?>
                            <option value="<?= $o['office_id'] ?>" <?= (isset($data['office_id']) && $data['office_id'] == $o['office_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($o['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Effectivity Date *</label>
                    <input type="date" class="form-control" name="effectivity_date" value="<?= htmlspecialchars($data['effectivity_date'] ?? date('Y-m-d')) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="end_date" value="<?= htmlspecialchars($data['end_date'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="active" <?= (isset($data['status']) && $data['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (isset($data['status']) && $data['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Accountability Document</label>
                    <input type="text" class="form-control" name="accountability_document" value="<?= htmlspecialchars($data['accountability_document'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Accountability Reference</label>
                    <input type="text" class="form-control" name="accountability_reference" value="<?= htmlspecialchars($data['accountability_reference'] ?? '') ?>">
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <a href="index.php?page=custody" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Save' ?></button>
            </div>
        </form>
    </div>
</div>