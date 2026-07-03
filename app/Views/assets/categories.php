<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><?= htmlspecialchars($pageTitle ?? 'Categories') ?></h4>
        <a href="index.php?page=assets&sub=add" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Add New Asset
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'success' ?> alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['flash']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php if (empty($categories)): ?>
                <div class="col-12">
                    <div class="alert alert-info">No categories found. Please add categories first.</div>
                </div>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                    <div class="col">
                        <div class="card h-100 category-card">
                            <div class="card-body text-center">
                                <i class="bi bi-folder" style="font-size: 3rem; color: #345635;"></i>
                                <h5 class="card-title mt-2"><?= htmlspecialchars($cat['name']) ?></h5>
                                <p class="card-text text-muted small"><?= htmlspecialchars($cat['code']) ?></p>
                                <?php if (!empty($cat['description'])): ?>
                                    <p class="card-text small"><?= htmlspecialchars($cat['description']) ?></p>
                                <?php endif; ?>
                                <a href="index.php?page=assets&sub=browse&cat_id=<?= $cat['asset_category_id'] ?>" 
                                   class="btn btn-outline-success btn-sm">
                                    <?= !empty($cat['children']) ? 'View Sub‑Categories' : 'View Assets' ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.category-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}
</style>