<?php if (!defined('APP_START')) exit; ?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
        <h4 class="text-xl font-bold text-green-700 flex items-center gap-2">
            <i class="bi bi-folder2-open"></i> <?= $pageTitle ?? 'Asset Categories' ?>
        </h4>
        <div class="flex flex-wrap items-center gap-2">
            <button class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50" type="button" data-bs-toggle="collapse" data-bs-target="#advancedSearch" aria-expanded="false">
                <i class="bi bi-sliders2"></i> Advanced
            </button>
            <form method="GET" action="index.php" class="flex gap-1">
                <input type="hidden" name="page" value="assets">
                <input type="hidden" name="sub" value="list_all">
                <div class="flex">
                    <input type="text" class="border border-gray-300 rounded-l px-3 py-1 text-sm focus:ring-1 focus:ring-green-500 focus:border-green-500" name="search" placeholder="Search all assets..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button class="px-3 py-1 bg-green-600 text-white text-sm rounded-r hover:bg-green-700" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>
            <a href="index.php?page=assets&sub=list_all" class="px-3 py-1.5 text-sm border border-blue-600 text-blue-600 rounded hover:bg-blue-50"><i class="bi bi-list-ul"></i> All Assets</a>
            <a href="index.php?page=assets&sub=add" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700"><i class="bi bi-plus-circle"></i> Add Asset</a>
        </div>
    </div>

    <!-- Advanced Search (Bootstrap collapse still works because we load Bootstrap JS) -->
    <div class="collapse" id="advancedSearch">
        <div class="bg-gray-50 p-4 border-b border-gray-200">
            <form method="GET" action="index.php">
                <input type="hidden" name="page" value="assets">
                <input type="hidden" name="sub" value="list_all">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Search Field</label>
                        <select class="w-full border border-gray-300 rounded px-2 py-1 text-sm" name="field">
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
                        <label class="block text-xs font-medium text-gray-700">Keyword</label>
                        <input type="text" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Enter keyword">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Status</label>
                        <select class="w-full border border-gray-300 rounded px-2 py-1 text-sm" name="status">
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="disposed">Disposed</option>
                            <option value="missing">Missing</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Condition</label>
                        <select class="w-full border border-gray-300 rounded px-2 py-1 text-sm" name="condition">
                            <option value="">All</option>
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                            <option value="damaged">Damaged</option>
                            <option value="obsolete">Obsolete</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Date From</label>
                        <input type="date" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Date To</label>
                        <input type="date" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Min Cost</label>
                        <input type="number" step="0.01" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" name="cost_from" value="<?= htmlspecialchars($_GET['cost_from'] ?? '') ?>" placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Max Cost</label>
                        <input type="number" step="0.01" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" name="cost_to" value="<?= htmlspecialchars($_GET['cost_to'] ?? '') ?>" placeholder="0.00">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"><i class="bi bi-funnel"></i> Apply Filters</button>
                        <a href="index.php?page=assets&sub=list_all" class="px-4 py-1.5 bg-gray-300 text-gray-800 text-sm rounded hover:bg-gray-400"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="p-6">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="mb-4 p-3 rounded border <?= ($_SESSION['flash_type'] ?? 'success') === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700' ?> flex justify-between items-center">
                <span><?= htmlspecialchars($_SESSION['flash']) ?></span>
                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="this.parentElement.remove()">&times;</button>
            </div>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <!-- Back buttons -->
        <?php if (isset($currentCategory) && $currentCategory['parent_category_id'] !== null): ?>
            <div class="mb-3">
                <a href="index.php?page=assets&sub=browse&cat_id=<?= $currentCategory['parent_category_id'] ?>" class="inline-block px-4 py-1.5 bg-gray-500 text-white text-sm rounded hover:bg-gray-600">
                    <i class="bi bi-arrow-left"></i> Back to Parent Category
                </a>
            </div>
        <?php elseif (isset($currentCategory)): ?>
            <div class="mb-3">
                <a href="index.php?page=assets&sub=browse" class="inline-block px-4 py-1.5 bg-gray-500 text-white text-sm rounded hover:bg-gray-600">
                    <i class="bi bi-arrow-left"></i> Back to Root Categories
                </a>
            </div>
        <?php endif; ?>

        <!-- Categories Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4" id="categoriesGrid">
            <?php if (empty($categories)): ?>
                <div class="col-span-full">
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 p-4 rounded">No categories found. Please add categories first.</div>
                </div>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                    <div class="bg-white border border-gray-200 rounded-lg hover:border-green-600 hover:shadow-md hover:-translate-y-1 transition-all duration-200 flex flex-col p-4">
                        <h6 class="font-semibold text-gray-800 text-sm mb-0"><?= htmlspecialchars($cat['name']) ?></h6>
                        <div class="text-xs text-gray-500"><?= htmlspecialchars($cat['code']) ?></div>
                        <?php if (!empty($cat['description'])): ?>
                            <div class="text-xs text-gray-600 mt-0.5"><?= htmlspecialchars($cat['description']) ?></div>
                        <?php endif; ?>
                        <div class="mt-auto pt-2">
                            <a href="index.php?page=assets&sub=browse&cat_id=<?= $cat['asset_category_id'] ?>" 
                               class="block w-full text-center text-sm bg-green-50 text-green-700 border border-green-300 rounded py-1.5 hover:bg-green-100 transition">
                                <?= !empty($cat['children']) ? 'Browse Sub‑categories' : 'View Assets' ?>
                                <i class="bi bi-chevron-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="searchResultsContainer" style="display: none;"></div>
        <div id="noResultsMessage" style="display: none;" class="bg-blue-50 border border-blue-200 text-blue-700 p-3 rounded mt-3">No assets found.</div>
    </div>
</div>
<script src="public/js/asset-search.js"></script>