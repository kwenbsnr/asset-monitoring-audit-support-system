<?php if (!defined('APP_START')) exit;
$flashType = $_SESSION['flash_type'] ?? 'success';
$alertClass = $flashType === 'success' ? 'alert-app-success' : 'alert-app-danger';
?>
<div class="card-panel">
    <div class="card-panel-header">
        <div class="flex items-center gap-3">
            <span class="page-icon"><i class="bi bi-box-seam"></i></span>
            <span class="page-title"><?= htmlspecialchars($pageTitle) ?></span>
        </div>
        <div class="flex gap-2">
            <a href="javascript:history.back()" class="btn-app btn-app-outline"><i class="bi bi-arrow-left"></i> Back</a>
            <a href="index.php?page=custody" class="btn-app btn-app-outline"><i class="bi bi-house"></i> Offices</a>
        </div>
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
                        <th>Asset Code</th>
                        <th>Description</th>
                        <th>Brand/Model</th>
                        <th>Serial #</th>
                        <th>Status</th>
                        <th>Condition</th>
                        <th>Effectivity</th>
                        <th>Property No.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assets)): ?>
                        <tr><td colspan="8"><div class="table-empty">No assets under this custodian.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($assets as $a): ?>
                            <tr>
                                <td class="font-medium text-gray-800"><?= htmlspecialchars($a['asset_code']) ?></td>
                                <td><?= htmlspecialchars($a['description']) ?></td>
                                <td><?= htmlspecialchars($a['brand'] ?? '') ?> <?= htmlspecialchars($a['model'] ?? '') ?></td>
                                <td><?= htmlspecialchars($a['serial_number'] ?? '') ?></td>
                                <td><span class="badge-app <?= $a['status'] === 'active' ? 'badge-app-success' : 'badge-app-neutral' ?>"><?= $a['status'] ?></span></td>
                                <td><span class="badge-app <?= $a['condition'] === 'good' ? 'badge-app-success' : 'badge-app-warning' ?>"><?= $a['condition'] ?></span></td>
                                <td><?= htmlspecialchars($a['effectivity_date']) ?></td>
                                <td><?= htmlspecialchars($a['property_number'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalPages > 1): ?>
            <nav class="mt-4 flex justify-center">
                <ul class="flex gap-1">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li>
                            <a class="btn-app btn-app-sm <?= $i == $page ? 'btn-app-primary' : 'btn-app-outline' ?>" href="?page=custody&sub=custodian&id=<?= $custodianId ?>&page_num=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>