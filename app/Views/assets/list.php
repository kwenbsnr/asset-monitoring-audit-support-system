<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><?= htmlspecialchars($pageTitle ?? 'Assets') ?></h4>
        <div>
            <a href="index.php?page=assets&sub=browse<?= isset($_GET['cat_id']) ? '&cat_id=' . (int)$_GET['cat_id'] : '' ?>" 
               class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left"></i> Back to Categories
            </a>
            <a href="index.php?page=assets&sub=add" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Add New Asset
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'success' ?> alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['flash']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
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
                        <tr><td colspan="8" class="text-center">No assets found in this category.</td></tr>
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
                                    <a href="index.php?page=assets&sub=edit&id=<?= $asset['asset_id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                    <a href="index.php?page=assets&sub=delete&id=<?= $asset['asset_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this asset?')"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>