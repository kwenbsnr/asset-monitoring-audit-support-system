<?php if (!defined('APP_START')) exit; ?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
        <h4 class="text-xl font-bold text-green-700 flex items-center gap-2">
            <i class="bi bi-box-seam"></i> <?= htmlspecialchars($pageTitle) ?>
        </h4>
        <a href="index.php?page=assets&sub=by_office" class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50">
            <i class="bi bi-arrow-left"></i> Back to Custodians
        </a>
    </div>
    <div class="p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm border border-gray-200">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-2 border-b text-left font-medium">Asset Code</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Asset Name</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Account</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Status</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Condition</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assets)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-gray-500">No assets under this custodian.</td></tr>
                    <?php else: ?>
                        <?php foreach ($assets as $a): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium text-gray-800"><?= htmlspecialchars($a['asset_code']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($a['asset_name'] ?? '') ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($a['account_code'] ?? 'N/A') ?></td>
                                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $a['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>"><?= htmlspecialchars($a['status']) ?></span></td>
                                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $a['condition'] === 'good' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>"><?= htmlspecialchars($a['condition']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>