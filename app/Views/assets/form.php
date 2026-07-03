<?php
// Prevent direct access
if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

// Get data from session if validation failed
$data = $_SESSION['form_data'] ?? ($asset ?? []);
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);

$isEdit = isset($asset) && $asset['asset_id'] > 0;
$title = $isEdit ? 'Edit Asset' : 'Add New Asset';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> – NIA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="container-fluid dashboard-wrapper">
        <nav class="navbar navbar-dark bg-dark mb-4">
            <div class="container-fluid">
                <span class="navbar-brand">NIA Asset Monitoring</span>
                <div class="d-flex">
                    <span class="navbar-text text-white me-3">
                        Welcome, <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong>
                        (<?= htmlspecialchars($_SESSION['role']) ?>)
                    </span>
                    <a href="index.php?action=logout" class="btn btn-outline-light btn-sm">Logout</a>
                </div>
            </div>
        </nav>

        <div class="card shadow">
            <div class="card-header">
                <h4><?= $title ?></h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
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

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <input type="text" class="form-control" id="description" name="description" 
                                   value="<?= htmlspecialchars($data['description'] ?? '') ?>" required>
                        </div>
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

                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="2"><?= htmlspecialchars($data['remarks'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="index.php?page=assets&sub=list" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?> Asset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>