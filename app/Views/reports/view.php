<?php if (!defined('APP_START')) exit; ?>
<div class="card-panel">
    <div class="card-panel-header">
        <div class="flex items-center gap-3">
            <span class="page-icon"><i class="bi bi-file-earmark-text"></i></span>
            <span class="page-title">Report: <?= htmlspecialchars($report['report_number']) ?></span>
        </div>
        <a href="index.php?page=reports" class="btn-app btn-app-outline"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
    <div class="card-panel-body">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div><strong>Report Date:</strong> <?= htmlspecialchars($report['report_date']) ?></div>
            <div><strong>Office:</strong> <?= htmlspecialchars($report['office_name']) ?></div>
            <div><strong>Prepared By:</strong> <?= htmlspecialchars($report['prepared_by_username']) ?></div>
            <div><strong>Status:</strong> <span class="badge-app <?= $report['status'] === 'draft' ? 'badge-app-neutral' : 'badge-app-success' ?>"><?= $report['status'] ?></span></div>
        </div>
        <div class="mb-4"><strong>Remarks:</strong> <?= htmlspecialchars($report['remarks'] ?? '') ?></div>
        <h6 class="font-semibold text-gray-800 border-b border-gray-200 pb-2 mt-6 mb-3">Items</h6>
        <div class="table-app-wrap">
            <table class="table-app">
                <thead>
                    <tr>
                        <th>Asset Code</th>
                        <th>Asset Name</th>
                        <th>Verification</th>
                        <th>Condition</th>
                        <th>Verified By</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($report['items'])): ?>
                        <tr><td colspan="6"><div class="table-empty">No items in this report.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($report['items'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['asset_code']) ?></td>
                                <td><?= htmlspecialchars($item['asset_name'] ?? $item['asset_description']) ?></td>
                                <td><span class="badge-app <?= $item['verification_status'] === 'verified' ? 'badge-app-success' : 'badge-app-warning' ?>"><?= $item['verification_status'] ?></span></td>
                                <td><?= htmlspecialchars($item['asset_condition'] ?? '') ?></td>
                                <td><?= htmlspecialchars($item['verified_by_username']) ?></td>
                                <td><?= htmlspecialchars($item['remarks'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>