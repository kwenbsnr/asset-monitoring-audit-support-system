<?php if (!defined('APP_START')) exit;
$flashType = $_SESSION['flash_type'] ?? 'success';
$alertClass = $flashType === 'success' ? 'alert-app-success' : 'alert-app-danger';
?>
<div class="card-panel">
    <div class="card-panel-header">
        <div class="flex items-center gap-3">
            <span class="page-icon"><i class="bi bi-people"></i></span>
            <span class="page-title"><?= htmlspecialchars($pageTitle) ?></span>
        </div>
        <a href="index.php?page=custody" class="btn-app btn-app-outline"><i class="bi bi-arrow-left"></i> Back to Offices</a>
    </div>
    <div class="card-panel-body">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert-app <?= $alertClass ?>">
                <span><?= htmlspecialchars($_SESSION['flash']) ?></span>
                <button type="button" class="alert-app-close" onclick="this.closest('.alert-app').remove()">&times;</button>
            </div>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
        <?php endif; ?>
        <div class="table-app-wrap">
            <table class="table-app">
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
                        <tr><td colspan="5"><div class="table-empty">No custodians found in this office.</div></td></tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($custodians as $c): ?>
                            <tr>
                                <td class="text-gray-500"><?= $i++ ?></td>
                                <td class="font-medium text-gray-800"><?= htmlspecialchars($c['full_name']) ?></td>
                                <td><?= htmlspecialchars($c['position']) ?></td>
                                <td><span class="badge-app badge-app-success"><?= $c['asset_count'] ?></span></td>
                                <td class="text-center">
                                    <a href="index.php?page=custody&sub=custodian&id=<?= $c['personnel_id'] ?>" class="btn-app btn-app-sm btn-app-outline-primary"><i class="bi bi-eye"></i> View Assets</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>