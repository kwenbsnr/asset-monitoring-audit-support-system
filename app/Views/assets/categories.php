<?php if (!defined('APP_START')) exit; ?>
<!-- Inside the card-header -->
<div class="card-header bg-white border-0 d-flex justify-content-between align-items-center flex-wrap py-3">
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
        
        <!-- Categories Grid -->
        <div id="categoriesGrid" class="row row-cols-1 row-cols-md-3 g-4">
            <?php if (empty($categories)): ?>
                <div class="col-12">
                    <div class="alert alert-info">No categories found. Please add categories first.</div>
                </div>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                    <div class="col">
                        <div class="card h-100 category-card border-0 shadow-sm transition">
                            <div class="card-body text-center py-4">
                                <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex p-3 mb-3">
                                    <i class="bi bi-folder fs-1 text-success"></i>
                                </div>
                                <h5 class="card-title fw-semibold"><?= htmlspecialchars($cat['name']) ?></h5>
                                <p class="card-text text-muted small"><?= htmlspecialchars($cat['code']) ?></p>
                                <?php if (!empty($cat['description'])): ?>
                                    <p class="card-text small text-secondary"><?= htmlspecialchars($cat['description']) ?></p>
                                <?php endif; ?>
                                <a href="index.php?page=assets&sub=browse&cat_id=<?= $cat['asset_category_id'] ?>" 
                                   class="btn btn-outline-success btn-sm rounded-pill px-4">
                                    <?= !empty($cat['children']) ? 'Browse Sub‑categories' : 'View Assets' ?>
                                    <i class="bi bi-chevron-right ms-1"></i>
                                </a>
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
.transition {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.transition:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.08) !important;
}
.category-card .rounded-circle {
    width: 70px;
    height: 70px;
}
#liveSearchInput:focus {
    box-shadow: none;
    border-color: #198754;
}
.input-group:focus-within {
    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.15);
    border-radius: 0.375rem;
}
</style>

<!-- Include the asset search JS -->
<script src="public/js/asset-search.js"></script>