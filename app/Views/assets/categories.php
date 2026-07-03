<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center flex-wrap py-3">
        <h4 class="mb-0 fw-bold text-success">
            <i class="bi bi-folder2-open me-2"></i><?= htmlspecialchars($pageTitle ?? 'Asset Categories') ?>
        </h4>
        <div class="d-flex gap-2 flex-wrap">
            <!-- Live Search -->
            <div class="input-group" style="min-width: 280px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="liveSearchInput" class="form-control border-start-0" 
                       placeholder="Search all assets..." aria-label="Search assets">
            </div>
            <a href="index.php?page=assets&sub=list_all" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-list-ul"></i> All Assets
            </a>
            <a href="index.php?page=assets&sub=add" class="btn btn-success btn-sm">
                <i class="bi bi-plus-circle"></i> Add Asset
            </a>
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