<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="bi bi-box-seam"></i> <?= htmlspecialchars($pageTitle) ?></h4>
        <div>
            <a href="javascript:history.back()" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
            <a href="index.php?page=custody" class="btn btn-secondary btn-sm"><i class="bi bi-house"></i> Offices</a>
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
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Asset Code</th>
                        <th>Description</th>
                        <th>Brand/Model</th>
                        <th>Serial #</th>
                        <th>Status</th>
                        <th>Condition</th>
                        <th>Effectivity</th>
                        <th>Doc Ref</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assets)): ?>
                        <tr><td colspan="8" class="text-center">No assets under this custodian.</td></tr>
                    <?php else: ?>
                        <?php foreach ($assets as $a): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($a['asset_code']) ?></strong></td>
                                <td><?= htmlspecialchars($a['description']) ?></td>
                                <td><?= htmlspecialchars($a['brand'] ?? '') ?> <?= htmlspecialchars($a['model'] ?? '') ?></td>
                                <td><?= htmlspecialchars($a['serial_number'] ?? '') ?></td>
                                <td><span class="badge bg-<?= $a['status'] === 'active' ? 'success' : 'secondary' ?>"><?= $a['status'] ?></span></td>
                                <td><span class="badge bg-<?= $a['condition'] === 'good' ? 'success' : 'warning' ?>"><?= $a['condition'] ?></span></td>
                                <td><?= htmlspecialchars($a['effectivity_date']) ?></td>
                                <td><?= htmlspecialchars($a['accountability_document'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalPages > 1): ?>
            <nav>
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=custody&sub=custodian&id=<?= $custodianId ?>&page_num=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>