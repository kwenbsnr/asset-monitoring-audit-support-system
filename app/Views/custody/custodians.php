<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="bi bi-people"></i> <?= htmlspecialchars($pageTitle) ?></h4>
        <a href="index.php?page=custody" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Offices</a>
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
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Position</th>
                        <th>Assets Under Custody</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($custodians)): ?>
                        <tr><td colspan="5" class="text-center">No custodians found in this office.</td></tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($custodians as $c): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><strong><?= htmlspecialchars($c['full_name']) ?></strong></td>
                                <td><?= htmlspecialchars($c['position']) ?></td>
                                <td><span class="badge bg-success"><?= $c['asset_count'] ?></span></td>
                                <td class="text-center">
                                    <a href="index.php?page=custody&sub=custodian&id=<?= $c['personnel_id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View Assets
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