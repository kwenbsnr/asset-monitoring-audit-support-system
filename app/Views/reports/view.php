<?php if (!defined('APP_START')) exit; ?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
        <h4 class="text-xl font-bold text-green-700 flex items-center gap-2">
            <i class="bi bi-file-earmark-text"></i> Report: <?= htmlspecialchars($report['report_number']) ?>
        </h4>
        <a href="index.php?page=reports" class="px-3 py-1.5 text-sm bg-gray-500 text-white rounded hover:bg-gray-600"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div><strong>Report Date:</strong> <?= htmlspecialchars($report['report_date']) ?></div>
            <div><strong>Office:</strong> <?= htmlspecialchars($report['office_name']) ?></div>
            <div><strong>Prepared By:</strong> <?= htmlspecialchars($report['prepared_by_username']) ?></div>
            <div><strong>Status:</strong> <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $report['status'] === 'draft' ? 'bg-gray-200 text-gray-700' : 'bg-green-100 text-green-800' ?>"><?= $report['status'] ?></span></div>
        </div>
        <div class="mb-4"><strong>Remarks:</strong> <?= htmlspecialchars($report['remarks'] ?? '') ?></div>
        <h6 class="font-semibold text-gray-800 border-b pb-2 mt-6">Items</h6>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border border-gray-200">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-2 border-b text-left font-medium">Asset Code</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Asset Name</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Verification</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Condition</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Verified By</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($report['items'])): ?>
                        <tr><td colspan="6" class="text-center py-4 text-gray-500">No items in this report.</td></tr>
                    <?php else: ?>
                        <?php foreach ($report['items'] as $item): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2"><?= htmlspecialchars($item['asset_code']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($item['asset_name'] ?? $item['asset_description']) ?></td>
                                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $item['verification_status'] === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>"><?= $item['verification_status'] ?></span></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($item['asset_condition'] ?? '') ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($item['verified_by_username']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($item['remarks'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>