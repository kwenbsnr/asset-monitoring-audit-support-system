<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="bi bi-geo-alt"></i> Location & Condition Monitoring</h4>
        <a href="index.php?page=monitoring&sub=add" class="btn btn-success btn-sm">
            <i class="bi bi-plus-circle"></i> Add Location Update
        </a>
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
                        <th>Asset</th>
                        <th>Location</th>
                        <th>Site Type</th>
                        <th>Description</th>
                        <th>Recorded At</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($locations)): ?>
                        <tr><td colspan="6" class="text-center">No location updates found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($locations as $loc): ?>
                            <tr>
                                <td><?= htmlspecialchars($loc['asset_code'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($loc['location_name']) ?></td>
                                <td><?= htmlspecialchars($loc['site_type'] ?? '') ?></td>
                                <td><?= htmlspecialchars($loc['description'] ?? '') ?></td>
                                <td><?= htmlspecialchars($loc['recorded_at']) ?></td>
                                <td><?= htmlspecialchars($loc['recorded_by']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>