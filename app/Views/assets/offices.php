<?php if (!defined('APP_START')) exit; ?>
<?php
$flashType = $_SESSION['flash_type'] ?? 'success';
$alertClass = $flashType === 'success' ? 'alert-app-success' : 'alert-app-danger';
?>
<div class="card-panel">
    <div class="card-panel-header">
        <div class="flex items-center gap-3">
            <span class="page-icon"><i class="bi bi-building"></i></span>
            <span class="page-title"><?= $pageTitle ?? 'Offices' ?></span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" action="index.php" class="flex gap-1">
                <input type="hidden" name="page" value="assets">
                <input type="hidden" name="sub" value="by_office">
                <div class="flex">
                    <input type="text" class="border border-gray-300 rounded-l-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="search" placeholder="Search offices..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button class="btn-app btn-app-primary btn-app-join-r" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>
            <a href="index.php?page=assets&sub=browse" class="btn-app btn-app-outline"><i class="bi bi-arrow-left"></i> Back to Asset Records</a>
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

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4" id="officesGrid">
            <?php if (empty($offices)): ?>
                <div class="col-span-full">
                    <div class="empty-state">No offices found.</div>
                </div>
            <?php else: ?>
                <?php foreach ($offices as $office): ?>
                    <div class="tile-card">
                        <div class="tile-card-title"><?= htmlspecialchars($office['name']) ?></div>
                        <div class="tile-card-meta"><?= htmlspecialchars($office['location'] ?? '') ?></div>
                        <div class="mt-2 flex flex-wrap gap-1">
                            <span class="badge-app badge-app-info"><?= $office['custodian_count'] ?> Custodians</span>
                            <span class="badge-app badge-app-success"><?= $office['asset_count'] ?> Assets</span>
                        </div>
                        <div class="mt-3">
                            <a href="index.php?page=assets&sub=by_office&office_id=<?= $office['office_id'] ?>" class="tile-card-link">
                                View Custodians <i class="bi bi-chevron-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>