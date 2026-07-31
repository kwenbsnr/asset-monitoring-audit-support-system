<?php if (!defined('APP_START')) exit;
$flashType = $_SESSION['flash_type'] ?? 'success';
$alertClass = $flashType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
        <h4 class="text-xl font-bold text-green-700 flex items-center gap-2">
            <i class="bi bi-box-seam"></i> <?= htmlspecialchars($pageTitle) ?>
        </h4>
        <div class="flex gap-2">
            <a href="javascript:history.back()" class="px-3 py-1.5 text-sm bg-gray-500 text-white rounded hover:bg-gray-600"><i class="bi bi-arrow-left"></i> Back</a>
            <a href="index.php?page=custody" class="px-3 py-1.5 text-sm bg-gray-500 text-white rounded hover:bg-gray-600"><i class="bi bi-house"></i> Offices</a>
        </div>
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
                        <th class="px-4 py-2 border-b text-left font-medium">Asset Code</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Description</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Brand/Model</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Serial #</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Status</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Condition</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Effectivity</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Doc Ref</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assets)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-gray-500">No assets under this custodian.</td></tr>
                    <?php else: ?>
                        <?php foreach ($assets as $a): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium text-gray-800"><?= htmlspecialchars($a['asset_code']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($a['description']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($a['brand'] ?? '') ?> <?= htmlspecialchars($a['model'] ?? '') ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($a['serial_number'] ?? '') ?></td>
                                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $a['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>"><?= $a['status'] ?></span></td>
                                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $a['condition'] === 'good' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>"><?= $a['condition'] ?></span></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($a['effectivity_date']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($a['accountability_document'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalPages > 1): ?>
            <nav class="mt-4 flex justify-center">
                <ul class="flex gap-1">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li>
                            <a class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-100 <?= $i == $page ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700' ?>" href="?page=custody&sub=custodian&id=<?= $custodianId ?>&page_num=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>