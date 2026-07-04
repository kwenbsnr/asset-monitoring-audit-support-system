<?php if (!defined('APP_START')) exit;

$data = $_SESSION['form_data'] ?? ($asset ?? []);
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);

$isEdit = $isEdit ?? false;
$title = $isEdit ? 'Edit Asset' : 'Add New Asset';
$assetId = $asset['asset_id'] ?? 0;
?>
<div class="row">
    <!-- Left column: Form -->
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header">
                <h4><?= $title ?></h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0"><?php foreach ($errors as $err) echo '<li>'.htmlspecialchars($err).'</li>'; ?></ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?page=assets&sub=save">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="asset_id" value="<?= $asset['asset_id'] ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="asset_code" class="form-label">Asset Code *</label>
                            <input type="text" class="form-control" id="asset_code" name="asset_code"
                                   value="<?= htmlspecialchars($data['asset_code'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="asset_accounts_id" class="form-label">Account *</label>
                            <select class="form-select" id="asset_accounts_id" name="asset_accounts_id" required>
                                <option value="">Select Account</option>
                                <?php foreach ($accounts as $acc): ?>
                                    <option value="<?= $acc['asset_accounts_id'] ?>"
                                        <?= (isset($data['asset_accounts_id']) && $data['asset_accounts_id'] == $acc['asset_accounts_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($acc['account_code'] . ' - ' . $acc['account_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <input type="text" class="form-control" id="description" name="description"
                               value="<?= htmlspecialchars($data['description'] ?? '') ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="brand" class="form-label">Brand</label>
                            <input type="text" class="form-control" id="brand" name="brand"
                                   value="<?= htmlspecialchars($data['brand'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="model" class="form-label">Model</label>
                            <input type="text" class="form-control" id="model" name="model"
                                   value="<?= htmlspecialchars($data['model'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="serial_number" class="form-label">Serial Number</label>
                            <input type="text" class="form-control" id="serial_number" name="serial_number"
                                   value="<?= htmlspecialchars($data['serial_number'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="acquisition_cost" class="form-label">Acquisition Cost</label>
                            <input type="number" step="0.01" class="form-control" id="acquisition_cost" name="acquisition_cost"
                                   value="<?= htmlspecialchars($data['acquisition_cost'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="acquisition_date" class="form-label">Acquisition Date</label>
                            <input type="date" class="form-control" id="acquisition_date" name="acquisition_date"
                                   value="<?= htmlspecialchars($data['acquisition_date'] ?? '') ?>">
                        </div>
                    </div>

                    <?php if ($isEdit): ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <?php foreach ($statusOptions as $opt): ?>
                                    <option value="<?= $opt ?>"
                                        <?= (isset($data['status']) && $data['status'] == $opt) ? 'selected' : '' ?>>
                                        <?= ucfirst($opt) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="condition" class="form-label">Condition</label>
                            <select class="form-select" id="condition" name="condition">
                                <?php foreach ($conditionOptions as $opt): ?>
                                    <option value="<?= $opt ?>"
                                        <?= (isset($data['condition']) && $data['condition'] == $opt) ? 'selected' : '' ?>>
                                        <?= ucfirst($opt) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="2"><?= htmlspecialchars($data['remarks'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="index.php?page=assets&sub=browse" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?> Asset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right column: QR Preview (only when editing) -->
    <div class="col-md-4">
        <?php if ($isEdit && $assetId): ?>
            <div class="card shadow">
                <div class="card-header">
                    <h5>QR Code</h5>
                </div>
                <div class="card-body text-center">
                    <img src="index.php?page=assets&sub=qr&id=<?= $assetId ?>" 
                         alt="QR Code" class="img-fluid" style="max-width:200px;">
                    <p class="text-muted small mt-2">
                        <i class="bi bi-info-circle"></i> 
                        The QR code is linked to this asset record.<br>
                        Print and affix it to the physical asset.
                    </p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="window.open('index.php?page=assets&sub=qr&id=<?= $assetId ?>&download=1')">
                            <i class="bi bi-download"></i> Download PNG
                        </button>
                        <button class="btn btn-success" onclick="printQR(<?= $assetId ?>)">
                            <i class="bi bi-printer"></i> Print QR Label
                        </button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow bg-light">
                <div class="card-body text-center text-muted">
                    <i class="bi bi-qr-code" style="font-size: 3rem;"></i>
                    <p class="mt-2">QR code will appear here<br>after saving the asset.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function printQR(assetId) {
        var win = window.open('', '_blank');
        win.document.write('<!DOCTYPE html><html><head><title>QR Label</title>');
        win.document.write('<style>body { text-align: center; margin-top: 50px; } img { max-width: 300px; }</style>');
        win.document.write('</head><body>');
        win.document.write('<h3>NIA Regional Office IX</h3>');
        win.document.write('<p>Asset QR Code</p>');
        win.document.write('<img src="index.php?page=assets&sub=qr&id=' + assetId + '">');
        win.document.write('</body></html>');
        win.document.close();
        win.onload = function() { win.print(); };
    }
</script>