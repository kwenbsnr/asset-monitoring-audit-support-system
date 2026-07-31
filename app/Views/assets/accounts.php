<?php if (!defined('APP_START')) exit; ?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
        <h4 class="text-xl font-bold text-green-700 flex items-center gap-2">
            <i class="bi bi-collection"></i> <?= $pageTitle ?? 'Asset Accounts' ?>
        </h4>
        <div class="flex flex-wrap items-center gap-2">
            <button class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50" type="button" data-bs-toggle="collapse" data-bs-target="#advancedSearch" aria-expanded="false">
                <i class="bi bi-sliders2"></i> Advanced
            </button>
            <form method="GET" action="index.php" class="flex gap-1">
                <input type="hidden" name="page" value="assets">
                <input type="hidden" name="sub" value="list_all">
                <div class="flex">
                    <input type="text" class="form-control form-control-sm border border-gray-300 rounded-l px-3 py-1 text-sm focus:ring-1 focus:ring-green-500 focus:border-green-500" name="search" placeholder="Search all assets..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button class="px-3 py-1 bg-green-600 text-white text-sm rounded-r hover:bg-green-700" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>
            <a href="index.php?page=assets&sub=list_all" class="px-3 py-1.5 text-sm border border-blue-600 text-blue-600 rounded hover:bg-blue-50"><i class="bi bi-list-ul"></i> All Assets</a>
            <a href="index.php?page=assets&sub=add" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700"><i class="bi bi-plus-circle"></i> Add Asset</a>
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

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4" id="accountsGrid">
            <?php if (empty($accounts)): ?>
                <div class="col-span-full">
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 p-4 rounded">No asset accounts found. Please add accounts first.</div>
                </div>
            <?php else: ?>
                <?php foreach ($accounts as $acc): ?>
                    <div class="bg-white border border-gray-200 rounded-lg hover:border-green-600 hover:shadow-md hover:-translate-y-1 transition-all duration-200 flex flex-col p-4">
                        <h6 class="font-semibold text-gray-800 text-sm mb-0"><?= htmlspecialchars($acc['account_code']) ?></h6>
                        <div class="text-xs text-gray-500 mt-0.5"><?= htmlspecialchars($acc['account_name']) ?></div>
                        <div class="mt-2">
                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded"><?= $acc['asset_count'] ?> Assets</span>
                        </div>
                        <div class="mt-auto pt-2">
                            <a href="index.php?page=assets&sub=browse&account_id=<?= $acc['asset_accounts_id'] ?>" 
                               class="block w-full text-center text-sm bg-green-50 text-green-700 border border-green-300 rounded py-1.5 hover:bg-green-100 transition">
                                View Assets <i class="bi bi-chevron-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>