<?php if (!defined('APP_START')) exit;
$flashType = $_SESSION['flash_type'] ?? 'success';
$alertClass = $flashType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
        <h4 class="text-xl font-bold text-green-700 flex items-center gap-2">
            <i class="bi bi-buildings"></i> Offices with Custody
        </h4>
        <form method="GET" action="index.php" class="flex gap-1">
            <input type="hidden" name="page" value="custody">
            <input type="hidden" name="sub" value="search_custodians">
            <input type="text" class="border border-gray-300 rounded-l px-3 py-1.5 text-sm focus:ring-1 focus:ring-green-500 focus:border-green-500" name="q" placeholder="Search custodian name..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            <button class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-r hover:bg-blue-700" type="submit"><i class="bi bi-search"></i></button>
        </form>
    </div>
    <div class="p-6">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="mb-4 p-3 rounded border <?= $alertClass ?> flex justify-between items-center">
                <span><?= htmlspecialchars($_SESSION['flash']) ?></span>
                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="this.parentElement.remove()">&times;</button>
            </div>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
        <?php endif; ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <?php if (empty($offices)): ?>
                <div class="col-span-full">
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 p-4 rounded">No offices with active custody assignments.</div>
                </div>
            <?php else: ?>
                <?php foreach ($offices as $office): ?>
                    <div class="bg-white border border-gray-200 rounded-lg hover:border-green-600 hover:shadow-md hover:-translate-y-1 transition-all duration-200 p-4 text-center">
                        <i class="bi bi-building text-4xl text-gray-400"></i>
                        <h6 class="font-semibold text-gray-800 mt-2"><?= htmlspecialchars($office['name']) ?></h6>
                        <div class="text-sm text-gray-500"><?= htmlspecialchars($office['location'] ?? '') ?></div>
                        <div class="mt-2 flex flex-wrap justify-center gap-1">
                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded"><?= $office['custodian_count'] ?> Custodians</span>
                            <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded"><?= $office['asset_count'] ?> Assets</span>
                        </div>
                        <a href="index.php?page=custody&sub=office&id=<?= $office['office_id'] ?>" class="inline-block mt-3 px-4 py-1.5 text-sm border border-blue-600 text-blue-600 rounded hover:bg-blue-50">View Custodians</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>