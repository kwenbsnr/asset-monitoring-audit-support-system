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
                    <select class="form-select" name="custodian_id" id="custodian_id" required>
                        <option value="">Select Custodian</option>
                        <?php foreach ($personnel as $p): ?>
                            <option value="<?= $p['personnel_id'] ?>" 
                                    data-office-id="<?= $p['office_id'] ?>"
                                    <?= (isset($data['custodian_id']) && $data['custodian_id'] == $p['personnel_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['full_name'] . ' (' . $p['position'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Office *</label>
                    <select class="form-select" name="office_id" id="office_id" required>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const custodianSelect = document.getElementById('custodian_id');
    const officeSelect = document.getElementById('office_id');

    // Store original options (all personnel)
    const allCustodianOptions = Array.from(custodianSelect.options);

    // Function to filter custodians by office
    function filterCustodiansByOffice(officeId) {
        // Clear current options
        custodianSelect.innerHTML = '';
        // Add the "Select Custodian" placeholder
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select Custodian';
        custodianSelect.appendChild(placeholder);

        // Filter and add matching options
        allCustodianOptions.forEach(opt => {
            if (opt.value === '') return; // skip placeholder
            const optOfficeId = opt.getAttribute('data-office-id');
            if (officeId === '' || optOfficeId == officeId) {
                // Clone the option to preserve attributes
                const newOpt = document.createElement('option');
                newOpt.value = opt.value;
                newOpt.textContent = opt.textContent;
                newOpt.setAttribute('data-office-id', optOfficeId);
                // Preserve selected state if it was selected before
                if (opt.selected) {
                    newOpt.selected = true;
                }
                custodianSelect.appendChild(newOpt);
            }
        });

        // If only one option remains (besides placeholder), auto-select it?
        // We'll leave that for user to select.
    }

    // When office changes, filter custodians
    officeSelect.addEventListener('change', function() {
        const officeId = this.value;
        filterCustodiansByOffice(officeId);
    });

    // When custodian changes, auto-fill office
    custodianSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected && selected.value) {
            const officeId = selected.getAttribute('data-office-id');
            if (officeId) {
                officeSelect.value = officeId;
                // Optionally trigger filter to lock list? We can also re-filter
                // but it's already filtered if office was selected first.
                // If we auto-fill office, we could also filter again to show only that office's personnel.
                // But since the office is already set, it's fine.
            }
        }
    });

    // Initial filter: if office is pre-selected, filter custodians
    if (officeSelect.value) {
        filterCustodiansByOffice(officeSelect.value);
    }
});
</script>