<?php if (!defined('APP_START')) exit; ?>
<?php 
$flashType = $_SESSION['flash_type'] ?? 'success';
$alertClass = $flashType === 'success' ? 'alert-app-success' : 'alert-app-danger';
?>
<div class="card-panel">
    <div class="card-panel-header">
        <div class="flex items-center gap-3">
            <span class="page-icon"><i class="bi bi-folder2-open"></i></span>
            <span class="page-title"><?= $pageTitle ?? 'Asset Categories' ?></span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button class="btn-app btn-app-outline" type="button" onclick="document.getElementById('advancedSearch').classList.toggle('hidden')">
                <i class="bi bi-sliders2"></i> Advanced
            </button>
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

    <div class="hidden" id="advancedSearch">
        <div class="bg-gray-50 p-4 border-b border-gray-200">
            <form method="GET" action="index.php">
                <input type="hidden" name="page" value="assets">
                <input type="hidden" name="sub" value="list_all">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Search Field</label>
                        <select class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm" name="field">
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
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Keyword</label>
                        <input type="text" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Enter keyword">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                        <select class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm" name="status">
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="disposed">Disposed</option>
                            <option value="missing">Missing</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Condition</label>
                        <select class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm" name="condition">
                            <option value="">All</option>
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                            <option value="damaged">Damaged</option>
                            <option value="obsolete">Obsolete</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date From</label>
                        <input type="date" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date To</label>
                        <input type="date" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Min Cost</label>
                        <input type="number" step="0.01" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm" name="cost_from" value="<?= htmlspecialchars($_GET['cost_from'] ?? '') ?>" placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Max Cost</label>
                        <input type="number" step="0.01" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm" name="cost_to" value="<?= htmlspecialchars($_GET['cost_to'] ?? '') ?>" placeholder="0.00">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn-app btn-app-primary"><i class="bi bi-funnel"></i> Apply Filters</button>
                        <a href="index.php?page=assets&sub=list_all" class="btn-app btn-app-outline"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                    </div>
                </div>
            </form>
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

        <?php if (isset($currentCategory) && $currentCategory['parent_category_id'] !== null): ?>
            <div class="mb-3">
                <a href="index.php?page=assets&sub=browse&cat_id=<?= $currentCategory['parent_category_id'] ?>" class="btn-app btn-app-outline">
                    <i class="bi bi-arrow-left"></i> Back to Parent Category
                </a>
            </div>
        <?php elseif (isset($currentCategory)): ?>
            <div class="mb-3">
                <a href="index.php?page=assets&sub=browse" class="btn-app btn-app-outline">
                    <i class="bi bi-arrow-left"></i> Back to Root Categories
                </a>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4" id="categoriesGrid">
            <?php if (empty($categories)): ?>
                <div class="col-span-full">
                    <div class="empty-state">No categories found. Please add categories first.</div>
                </div>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                    <div class="tile-card">
                        <div class="tile-card-title"><?= htmlspecialchars($cat['name']) ?></div>
                        <div class="tile-card-meta"><?= htmlspecialchars($cat['code']) ?></div>
                        <?php if (!empty($cat['description'])): ?>
                            <div class="text-xs text-gray-600 mt-1"><?= htmlspecialchars($cat['description']) ?></div>
                        <?php endif; ?>
                        <div class="mt-3">
                            <a href="index.php?page=assets&sub=browse&cat_id=<?= $cat['asset_category_id'] ?>" class="tile-card-link">
                                <?= !empty($cat['children']) ? 'Browse Sub‑categories' : 'View Assets' ?>
                                <i class="bi bi-chevron-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="searchResultsContainer" style="display: none;"></div>
        <div id="noResultsMessage" style="display: none;" class="empty-state mt-3">No assets found.</div>
    </div>
</div>
<script src="public/js/asset-search.js"></script>