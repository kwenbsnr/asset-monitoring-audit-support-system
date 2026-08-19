<?php if (!defined('APP_START')) exit;
$flashType = $_SESSION['flash_type'] ?? 'success';
$alertClass = $flashType === 'success' ? 'alert-app-success' : 'alert-app-danger';
?>
<div class="card-panel">
    <div class="card-panel-header">
        <div class="flex items-center gap-3">
            <span class="page-icon"><i class="bi bi-people"></i></span>
            <span class="page-title">Custody Records</span>
        </div>
        <button type="button" class="btn-app btn-app-primary" data-form-modal
                data-form-url="index.php?page=custody&sub=add"
                data-form-title="Assign / Transfer Custody"
                data-form-init="initCustodyForm">
            <i class="bi bi-plus-circle"></i> Assign / Transfer Custody
        </button>
    </div>
    <div class="card-panel-body">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert-app <?= $alertClass ?>">
                <span><?= htmlspecialchars($_SESSION['flash']) ?></span>
                <button type="button" class="alert-app-close" onclick="this.closest('.alert-app').remove()">&times;</button>
            </div>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <div class="flex flex-wrap items-center gap-3 mb-4">
            <div class="flex-1 min-w-50">
                <form method="GET" action="index.php" class="flex gap-1">
                    <input type="hidden" name="page" value="custody">
                    <input type="hidden" name="sub" value="index">
                    <div class="flex flex-1">
                        <input type="text" class="flex-1 border border-gray-300 rounded-l-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="search"
                               placeholder="Search by custodian, asset code, description, office, or property number..."
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        <button class="btn-app btn-app-primary btn-app-join-mid" type="submit">
                            <i class="bi bi-search"></i> Search
                        </button>
                        <?php if (!empty($_GET['search'])): ?>
                            <a href="index.php?page=custody" class="btn-app btn-app-outline btn-app-join-r">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-app-wrap">
            <table class="table-app">
                <thead>
                    <tr>
                        <th>Asset</th>
                        <th>Custodian</th>
                        <th>Office</th>
                        <th>Effectivity</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Property No.</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr><td colspan="8"><div class="table-empty">No custody records found.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($records as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['asset_code'] ?? 'N/A') ?></td>
                                <td class="font-medium"><?= htmlspecialchars($r['custodian_name']) ?></td>
                                <td><?= htmlspecialchars($r['office_name']) ?></td>
                                <td><?= htmlspecialchars($r['effectivity_date']) ?></td>
                                <td><?= htmlspecialchars($r['end_date'] ?? '—') ?></td>
                                <td><span class="badge-app <?= $r['status'] === 'active' ? 'badge-app-success' : 'badge-app-neutral' ?>"><?= $r['status'] ?></span></td>
                                <td><?= htmlspecialchars($r['property_number'] ?? '') ?></td>
                                <td class="text-center whitespace-nowrap">
                                    <button type="button" class="btn-app btn-app-sm btn-app-outline-warning" title="Edit" data-form-modal
                                            data-form-url="index.php?page=custody&sub=edit&id=<?= $r['asset_custodies_id'] ?>"
                                            data-form-title="Edit Custody Record"
                                            data-form-init="initCustodyForm">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="index.php?page=custody&sub=delete&id=<?= $r['asset_custodies_id'] ?>" class="btn-app btn-app-sm btn-app-outline-danger" title="End Custody" onclick="return confirm('End this custody record?')"><i class="bi bi-x-circle"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>