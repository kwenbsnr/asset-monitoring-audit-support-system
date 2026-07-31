<?php if (!defined('APP_START')) exit;
$flashType = $_SESSION['flash_type'] ?? 'success';
$alertClass = $flashType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
        <h4 class="text-xl font-bold text-green-700 flex items-center gap-2">
            <i class="bi bi-people"></i> <?= htmlspecialchars($pageTitle) ?>
        </h4>
        <a href="index.php?page=custody" class="px-3 py-1.5 text-sm bg-gray-500 text-white rounded hover:bg-gray-600"><i class="bi bi-arrow-left"></i> Back to Offices</a>
    </div>
    <div class="p-6">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="mb-4 p-3 rounded border <?= $alertClass ?> flex justify-between items-center">
                <span><?= htmlspecialchars($_SESSION['flash']) ?></span>
                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="this.parentElement.remove()">&times;</button>
            </div>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
        <?php endif; ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border border-gray-200">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-2 border-b text-left font-medium">#</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Full Name</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Position</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Assets Under Custody</th>
                        <th class="px-4 py-2 border-b text-center font-medium">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($custodians)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-gray-500">No custodians found in this office.</td></tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($custodians as $c): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2"><?= $i++ ?></td>
                                <td class="px-4 py-2 font-medium text-gray-800"><?= htmlspecialchars($c['full_name']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($c['position']) ?></td>
                                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><?= $c['asset_count'] ?></span></td>
                                <td class="px-4 py-2 text-center">
                                    <a href="index.php?page=custody&sub=custodian&id=<?= $c['personnel_id'] ?>" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"><i class="bi bi-eye"></i> View Assets</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>