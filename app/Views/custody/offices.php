<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="bi bi-buildings"></i> Offices with Custody</h4>
        <div>
            <form method="GET" action="index.php" class="d-flex gap-2">
                <input type="hidden" name="page" value="custody">
                <input type="hidden" name="sub" value="search_custodians">
                <input type="text" class="form-control form-control-sm" name="q" placeholder="Search custodian name..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-search"></i></button>
            </form>
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
        <div class="row g-3">
            <?php if (empty($offices)): ?>
                <div class="col-12"><div class="alert alert-info">No offices with active custody assignments.</div></div>
            <?php else: ?>
                <?php foreach ($offices as $office): ?>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <div class="card h-100 border-secondary-subtle">
                            <div class="card-body text-center">
                                <i class="bi bi-building fs-1 text-secondary"></i>
                                <h6 class="card-title mt-2"><?= htmlspecialchars($office['name']) ?></h6>
                                <div class="small text-muted"><?= htmlspecialchars($office['location'] ?? '') ?></div>
                                <div class="mt-2">
                                    <span class="badge bg-primary"><?= $office['custodian_count'] ?> Custodians</span>
                                    <span class="badge bg-success"><?= $office['asset_count'] ?> Assets</span>
                                </div>
                                <a href="index.php?page=custody&sub=office&id=<?= $office['office_id'] ?>" class="btn btn-outline-primary btn-sm mt-3">View Custodians</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>