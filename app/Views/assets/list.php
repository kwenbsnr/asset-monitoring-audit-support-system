<?php
// Prevent direct access
if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Registry – NIA</title>
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Asset Registry</h4>
                <a href="index.php?page=assets&sub=add" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Add New Asset
                </a>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['flash'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= htmlspecialchars($_SESSION['flash']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['flash']); ?>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Asset Code</th>
                                <th>Description</th>
                                <th>Brand / Model</th>
                                <th>Serial #</th>
                                <th>Account</th>
                                <th>Status</th>
                                <th>Condition</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($assets)): ?>
                                <tr><td colspan="8" class="text-center">No assets found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($assets as $asset): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($asset['asset_code']) ?></strong></td>
                                        <td><?= htmlspecialchars($asset['description']) ?></td>
                                        <td><?= htmlspecialchars($asset['brand'] ?? '') ?> <?= htmlspecialchars($asset['model'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($asset['serial_number'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($asset['account_code'] ?? '') ?></td>
                                        <td><span class="badge bg-<?= $asset['status'] === 'active' ? 'success' : 'secondary' ?>"><?= $asset['status'] ?></span></td>
                                        <td><span class="badge bg-<?= $asset['condition'] === 'good' ? 'success' : 'warning' ?>"><?= $asset['condition'] ?></span></td>
                                        <td>
                                            <a href="index.php?page=assets&sub=edit&id=<?= $asset['asset_id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="index.php?page=assets&sub=delete&id=<?= $asset['asset_id'] ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this asset?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>