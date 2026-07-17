<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow">
    <div class="card-header">
        <h4 class="mb-0 fw-bold text-success"><i class="bi bi-building me-2"></i>Assets by Office</h4>
    </div>
    <div class="card-body">
        <?php if (empty($officeData)): ?>
            <div class="alert alert-info">No offices found.</div>
        <?php else: ?>
            <?php foreach ($officeData as $office): ?>
                <h5 class="mt-3"><?= htmlspecialchars($office['office_name']) ?></h5>
                <?php if (empty($office['assets'])): ?>
                    <p class="text-muted">No assets in this office.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Asset Code</th>
                                    <th>Asset Name</th>
                                    <th>Account</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($office['assets'] as $asset): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($asset['asset_code']) ?></td>
                                        <td><?= htmlspecialchars($asset['asset_name']) ?></td>
                                        <td><?= htmlspecialchars($asset['account_code']) ?></td>
                                        <td><span class="badge bg-<?= $asset['status'] === 'active' ? 'success' : 'secondary' ?>"><?= $asset['status'] ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>