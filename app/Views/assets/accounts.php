<?php if (!defined('APP_START')) exit; ?>
<?php 
$flashType = $_SESSION['flash_type'] ?? 'success';
$alertClass = $flashType === 'success' ? 'alert-app-success' : 'alert-app-danger';
?>
<div class="card-panel">
    <div class="card-panel-header">
        <div class="flex items-center gap-3">
            <span class="page-icon"><i class="bi bi-collection"></i></span>
            <span class="page-title"><?= $pageTitle ?? 'Asset Accounts' ?></span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" action="index.php" class="flex gap-1">
                <input type="hidden" name="page" value="assets">
                <input type="hidden" name="sub" value="list_all">
                <div class="flex">
                    <input type="text" class="border border-gray-300 rounded-l-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="search" placeholder="Search all assets..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button class="btn-app btn-app-primary btn-app-join-r" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>
            <a href="index.php?page=assets&sub=list_all" class="btn-app btn-app-outline-primary"><i class="bi bi-list-ul"></i> All Assets</a>
            <a href="index.php?page=assets&sub=add" class="btn-app btn-app-primary"><i class="bi bi-plus-circle"></i> Add Asset</a>
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

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4" id="accountsGrid">
            <?php if (empty($accounts)): ?>
                <div class="col-span-full">
                    <div class="empty-state">No asset accounts found. Please add accounts first.</div>
                </div>
            <?php else: ?>
                <?php foreach ($accounts as $acc): ?>
                    <div class="tile-card">
                        <div class="tile-card-title"><?= htmlspecialchars($acc['account_code']) ?></div>
                        <div class="tile-card-meta"><?= htmlspecialchars($acc['account_name']) ?></div>
                        <div class="mt-2">
                            <span class="badge-app badge-app-info"><?= $acc['asset_count'] ?> Assets</span>
                        </div>
                        <div class="mt-3">
                            <a href="index.php?page=assets&sub=browse&account_id=<?= $acc['asset_accounts_id'] ?>" class="tile-card-link">
                                View Assets <i class="bi bi-chevron-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>