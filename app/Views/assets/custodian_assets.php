<?php if (!defined('APP_START')) exit; ?>
<div class="card-panel">
    <div class="card-panel-header">
        <div class="flex items-center gap-3">
            <span class="page-icon"><i class="bi bi-box-seam"></i></span>
            <span class="page-title"><?= htmlspecialchars($pageTitle) ?></span>
        </div>
        <a href="index.php?page=assets&sub=by_office" class="btn-app btn-app-outline">
            <i class="bi bi-arrow-left"></i> Back to Custodians
        </a>
    </div>
    <div class="card-panel-body">
        <div class="table-app-wrap">
            <table class="table-app">
                <thead>
                    <tr>
                        <th>Asset Code</th>
                        <th>Asset Name</th>
                        <th>Account</th>
                        <th>Status</th>
                        <th>Condition</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assets)): ?>
                        <tr><td colspan="5"><div class="table-empty">No assets under this custodian.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($assets as $a): ?>
                            <tr>
                                <td class="font-medium text-gray-800"><?= htmlspecialchars($a['asset_code']) ?></td>
                                <td><?= htmlspecialchars($a['asset_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($a['account_code'] ?? 'N/A') ?></td>
                                <td><span class="badge-app <?= $a['status'] === 'active' ? 'badge-app-success' : 'badge-app-neutral' ?>"><?= htmlspecialchars($a['status']) ?></span></td>
                                <td><span class="badge-app <?= $a['condition'] === 'good' ? 'badge-app-success' : 'badge-app-warning' ?>"><?= htmlspecialchars($a['condition']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>