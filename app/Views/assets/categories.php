<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap py-3">
        <h4 class="mb-0 fw-bold text-success"><i class="bi bi-folder2-open me-2"></i><?= $pageTitle ?? 'Asset Categories' ?></h4>
        <div class="d-flex gap-2 flex-wrap">
            <!-- Advanced search toggle -->
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#advancedSearch" aria-expanded="false">
                <i class="bi bi-sliders2"></i> Advanced
            </button>
            <!-- Basic search form -->
            <form method="GET" action="index.php" class="d-flex gap-2" id="basicSearchForm">
                <input type="hidden" name="page" value="assets">
                <input type="hidden" name="sub" value="list_all">
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm" name="search" placeholder="Search all assets..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button class="btn btn-outline-success btn-sm" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>
            <a href="index.php?page=assets&sub=list_all" class="btn btn-outline-primary btn-sm"><i class="bi bi-list-ul"></i> All Assets</a>
            <a href="index.php?page=assets&sub=add" class="btn btn-success btn-sm"><i class="bi bi-plus-circle"></i> Add Asset</a>
        </div>
    </div>

    <!-- Advanced Search Collapse -->
    <div class="collapse" id="advancedSearch">
        <div class="card card-body bg-light mb-3">
            <form method="GET" action="index.php">
                <input type="hidden" name="page" value="assets">
                <input type="hidden" name="sub" value="list_all">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label small">Search Field</label>
                        <select class="form-select form-select-sm" name="field">
                            <option value="all">All Fields</option>
                            <option value="asset_code">Asset Code</option>
                            <option value="description">Description</option>
                            <option value="brand">Brand</option>
                            <option value="model">Model</option>
                            <option value="serial_number">Serial Number</option>
                            <option value="account_code">Account Code</option>
                            <option value="custodian">Custodian</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Keyword</label>
                        <input type="text" class="form-control form-control-sm" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Enter keyword">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Status</label>
                        <select class="form-select form-select-sm" name="status">
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="disposed">Disposed</option>
                            <option value="missing">Missing</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Condition</label>
                        <select class="form-select form-select-sm" name="condition">
                            <option value="">All</option>
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                            <option value="damaged">Damaged</option>
                            <option value="obsolete">Obsolete</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Date From</label>
                        <input type="date" class="form-control form-control-sm" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Date To</label>
                        <input type="date" class="form-control form-control-sm" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Min Cost</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" name="cost_from" value="<?= htmlspecialchars($_GET['cost_from'] ?? '') ?>" placeholder="0.00">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Max Cost</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" name="cost_to" value="<?= htmlspecialchars($_GET['cost_to'] ?? '') ?>" placeholder="0.00">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel"></i> Apply Filters</button>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="index.php?page=assets&sub=list_all" class="btn btn-secondary btn-sm w-100"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                    </div>
                </div>
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

        <!-- Back buttons -->
        <?php if (isset($currentCategory) && $currentCategory['parent_category_id'] !== null): ?>
            <div class="mb-3">
                <a href="index.php?page=assets&sub=browse&cat_id=<?= $currentCategory['parent_category_id'] ?>" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Parent Category
                </a>
            </div>
        <?php elseif (isset($currentCategory)): ?>
            <div class="mb-3">
                <a href="index.php?page=assets&sub=browse" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Root Categories
                </a>
            </div>
        <?php endif; ?>

        <!-- Categories Grid – 5 per row on XL -->
        <div class="row g-3" id="categoriesGrid">
            <?php if (empty($categories)): ?>
                <div class="col-12">
                    <div class="alert alert-info">No categories found. Please add categories first.</div>
                </div>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                    <div class="col-5-xl col-lg-3 col-md-4 col-sm-6 col-12">
                        <div class="card h-100 category-card border border-secondary-subtle rounded-3">
                            <div class="card-body p-3 d-flex flex-column">
                                <h6 class="card-title fw-semibold mb-1"><?= htmlspecialchars($cat['name']) ?></h6>
                                <div class="small text-muted mb-1"><?= htmlspecialchars($cat['code']) ?></div>
                                <?php if (!empty($cat['description'])): ?>
                                    <div class="small text-secondary"><?= htmlspecialchars($cat['description']) ?></div>
                                <?php endif; ?>
                                <div class="mt-auto pt-2">
                                    <a href="index.php?page=assets&sub=browse&cat_id=<?= $cat['asset_category_id'] ?>" 
                                       class="btn btn-outline-success btn-sm w-100">
                                        <?= !empty($cat['children']) ? 'Browse Sub‑categories' : 'View Assets' ?>
                                        <i class="bi bi-chevron-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Search Results (hidden by default) -->
        <div id="searchResultsContainer" style="display: none;"></div>
        <div id="noResultsMessage" style="display: none;" class="alert alert-info mt-3">No assets found.</div>
    </div>
</div>

<style>
/* Custom 5-column breakpoint for XL screens */
@media (min-width: 1200px) {
    .col-5-xl {
        flex: 0 0 20%;
        max-width: 20%;
    }
}

/* Category cards – moderate, clean, readable */
.category-card {
    transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
    border-width: 1px !important;
    border-color: #dee2e6 !important;
}
.category-card:hover {
    border-color: #198754 !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    transform: translateY(-2px);
}
.category-card .card-body {
    padding: 1rem !important;
}
.category-card .card-title {
    font-size: 0.95rem;
    margin-bottom: 0.1rem;
    line-height: 1.3;
}
.category-card .text-muted {
    font-size: 0.75rem;
}
.category-card .text-secondary {
    font-size: 0.8rem;
    line-height: 1.2;
}
.category-card .btn {
    font-size: 0.75rem;
    padding: 0.3rem 0.5rem;
}
/* Grid spacing */
#categoriesGrid {
    --bs-gutter-x: 1.25rem;
    --bs-gutter-y: 1.25rem;
}
</style>

<script src="public/js/asset-search.js"></script>