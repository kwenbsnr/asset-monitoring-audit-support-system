<?php if (!defined('APP_START')) exit;
$flashType = $_SESSION['flash_type'] ?? 'success';
$alertClass = $flashType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
        <h4 class="text-xl font-bold text-green-700 flex items-center gap-2">
            <i class="bi bi-people"></i> Custody Records
        </h4>
        <a href="index.php?page=custody&sub=add" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700">
            <i class="bi bi-plus-circle"></i> Assign Custody
        </a>
    </div>
    <div class="p-6">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="mb-4 p-3 rounded border <?= $alertClass ?> flex justify-between items-center">
                <span><?= htmlspecialchars($_SESSION['flash']) ?></span>
                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="this.parentElement.remove()">&times;</button>
            </div>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <div class="flex flex-wrap items-center gap-3 mb-4">
            <div class="flex-1 min-w-50">
                <form method="GET" action="index.php" class="flex gap-1">
                    <input type="hidden" name="page" value="custody">
                    <input type="hidden" name="sub" value="index">
                    <div class="flex flex-1">
                        <input type="text" class="flex-1 border border-gray-300 rounded-l px-3 py-1.5 text-sm focus:ring-1 focus:ring-green-500 focus:border-green-500" name="search" 
                               placeholder="Search by custodian, asset code, description, office, or property number..." 
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        <button class="px-3 py-1.5 bg-green-600 text-white text-sm rounded-r hover:bg-green-700" type="submit">
                            <i class="bi bi-search"></i> Search
                        </button>
                        <?php if (!empty($_GET['search'])): ?>
                            <a href="index.php?page=custody" class="px-3 py-1.5 bg-gray-300 text-gray-800 text-sm rounded-r hover:bg-gray-400">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <a href="index.php?page=custody&sub=add" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700">
                <i class="bi bi-plus-circle"></i> Assign Custody
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border border-gray-200">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-2 border-b text-left font-medium">Asset</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Custodian</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Office</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Effectivity</th>
                        <th class="px-4 py-2 border-b text-left font-medium">End Date</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Status</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Property No.</th>
                        <th class="px-4 py-2 border-b text-center font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-gray-500">No custody records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($records as $r): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2"><?= htmlspecialchars($r['asset_code'] ?? 'N/A') ?></td>
                                <td class="px-4 py-2 font-medium"><?= htmlspecialchars($r['custodian_name']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($r['office_name']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($r['effectivity_date']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($r['end_date'] ?? '—') ?></td>
                                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $r['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>"><?= $r['status'] ?></span></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($r['property_number'] ?? '') ?></td>
                                <td class="px-4 py-2 text-center whitespace-nowrap">
                                    <a href="index.php?page=custody&sub=edit&id=<?= $r['asset_custodies_id'] ?>" class="px-2 py-1 text-yellow-600 border border-yellow-300 rounded hover:bg-yellow-50 text-xs"><i class="bi bi-pencil"></i></a>
                                    <a href="index.php?page=custody&sub=delete&id=<?= $r['asset_custodies_id'] ?>" class="px-2 py-1 text-red-600 border border-red-300 rounded hover:bg-red-50 text-xs" onclick="return confirm('End this custody record?')"><i class="bi bi-x-circle"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>