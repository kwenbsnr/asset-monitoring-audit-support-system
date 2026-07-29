<?php if (!defined('APP_START')) exit;
$data = $_SESSION['form_data'] ?? [];
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);
?>
<div class="card shadow">
    <div class="card-header">
        <h4><i class="bi bi-file-earmark-plus"></i> Create Report</h4>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div>
        <?php endif; ?>
        <form method="POST" action="index.php?page=reports&sub=save" id="reportForm">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Report Number *</label>
                    <input type="text" class="form-control" name="report_number" value="<?= htmlspecialchars($data['report_number'] ?? 'RPT-'.date('Ymd').'-001') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Report Date *</label>
                    <input type="date" class="form-control" name="report_date" value="<?= htmlspecialchars($data['report_date'] ?? date('Y-m-d')) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
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
                <div class="col-md-4 mb-3">
                    <label class="form-label">Prepared By *</label>
                    <select class="form-select" name="prepared_by" required>
                        <option value="">Select User</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['users_id'] ?>" <?= (isset($data['prepared_by']) && $data['prepared_by'] == $u['users_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="draft" <?= (isset($data['status']) && $data['status'] == 'draft') ? 'selected' : '' ?>>Draft</option>
                        <option value="submitted" <?= (isset($data['status']) && $data['status'] == 'submitted') ? 'selected' : '' ?>>Submitted</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" name="remarks" rows="2"><?= htmlspecialchars($data['remarks'] ?? '') ?></textarea>
                </div>
            </div>

            <h6 class="border-bottom pb-2">Report Items</h6>
            <div id="itemsContainer">
                <div class="row item-row mb-2">
                    <div class="col-md-4">
                        <select class="form-select form-select-sm" name="items[0][asset_id]">
                            <option value="">Select Asset</option>
                            <?php foreach ($assets as $a): ?>
                                <option value="<?= $a['asset_id'] ?>">
                                    <?= htmlspecialchars($a['asset_code'] . ' - ' . ($a['asset_name'] ?? $a['description'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="items[0][verification_status]">
                            <option value="pending">Pending</option>
                            <option value="verified">Verified</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="items[0][asset_condition]">
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                            <option value="damaged">Damaged</option>
                            <option value="obsolete">Obsolete</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="items[0][verified_by]">
                            <option value="">Verifier</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['users_id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control form-control-sm" name="items[0][remarks]" placeholder="Remarks">
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addItem()"><i class="bi bi-plus-circle"></i> Add Item</button>

            <div class="d-flex justify-content-between mt-3">
                <a href="index.php?page=reports" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Report</button>
            </div>
        </form>
    </div>
</div>

<script>
    let itemCount = 1;
    function addItem() {
        const container = document.getElementById('itemsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'row item-row mb-2';
        newRow.innerHTML = `
            <div class="col-md-4">
                <select class="form-select form-select-sm" name="items[${itemCount}][asset_id]">
                    <option value="">Select Asset</option>
                    <?php foreach ($assets as $a): ?>
                        <option value="<?= $a['asset_id'] ?>">
                            <?= htmlspecialchars($a['asset_code'] . ' - ' . ($a['asset_name'] ?? $a['description'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="items[${itemCount}][verification_status]">
                    <option value="pending">Pending</option>
                    <option value="verified">Verified</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="items[${itemCount}][asset_condition]">
                    <option value="good">Good</option>
                    <option value="fair">Fair</option>
                    <option value="poor">Poor</option>
                    <option value="damaged">Damaged</option>
                    <option value="obsolete">Obsolete</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="items[${itemCount}][verified_by]">
                    <option value="">Verifier</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['users_id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control form-control-sm" name="items[${itemCount}][remarks]" placeholder="Remarks">
            </div>
        `;
        container.appendChild(newRow);
        itemCount++;
    }
</script>