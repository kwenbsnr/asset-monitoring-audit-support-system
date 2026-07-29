<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap py-3">
        <h4 class="mb-0 fw-bold text-success"><i class="bi bi-building me-2"></i><?= $pageTitle ?? 'Offices' ?></h4>
        <div class="d-flex gap-2 flex-wrap">
            <form method="GET" action="index.php" class="d-flex gap-2">
                <input type="hidden" name="page" value="assets">
                <input type="hidden" name="sub" value="by_office">
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm" name="search" placeholder="Search offices..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button class="btn btn-outline-success btn-sm" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>
            <a href="index.php?page=assets&sub=browse" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Asset Records</a>
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

        <div class="row g-3" id="officesGrid">
            <?php if (empty($offices)): ?>
                <div class="col-12">
                    <div class="alert alert-info">No offices found.</div>
                </div>
            <?php else: ?>
                <?php foreach ($offices as $office): ?>
                    <div class="col-5-xl col-lg-3 col-md-4 col-sm-6 col-12">
                        <div class="card h-100 office-card border border-secondary-subtle rounded-3">
                            <div class="card-body p-3 d-flex flex-column">
                                <h6 class="card-title fw-semibold mb-1"><?= htmlspecialchars($office['name']) ?></h6>
                                <div class="small text-muted"><?= htmlspecialchars($office['location'] ?? '') ?></div>
                                <div class="mt-2">
                                    <span class="badge bg-primary"><?= $office['custodian_count'] ?> Custodians</span>
                                    <span class="badge bg-success"><?= $office['asset_count'] ?> Assets</span>
                                </div>
                                <div class="mt-auto pt-2">
                                    <a href="index.php?page=assets&sub=by_office&office_id=<?= $office['office_id'] ?>" 
                                       class="btn btn-outline-success btn-sm w-100">
                                        View Custodians <i class="bi bi-chevron-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.office-card {
    transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
    border-width: 1px !important;
    border-color: #dee2e6 !important;
}
.office-card:hover {
    border-color: #198754 !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    transform: translateY(-2px);
}
.office-card .card-body {
    padding: 1rem !important;
}
.office-card .card-title {
    font-size: 0.95rem;
    margin-bottom: 0.1rem;
    line-height: 1.3;
}
.office-card .text-muted {
    font-size: 0.75rem;
}
.office-card .btn {
    font-size: 0.75rem;
    padding: 0.3rem 0.5rem;
}
@media (min-width: 1200px) {
    .col-5-xl {
        flex: 0 0 20%;
        max-width: 20%;
    }
}
</style>