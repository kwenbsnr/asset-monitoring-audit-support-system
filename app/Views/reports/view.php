<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> Report: <?= htmlspecialchars($report['report_number']) ?></h4>
        <a href="index.php?page=reports" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3"><strong>Report Date:</strong> <?= htmlspecialchars($report['report_date']) ?></div>
            <div class="col-md-3"><strong>Office:</strong> <?= htmlspecialchars($report['office_name']) ?></div>
            <div class="col-md-3"><strong>Prepared By:</strong> <?= htmlspecialchars($report['prepared_by_username']) ?></div>
            <div class="col-md-3"><strong>Status:</strong> <span class="badge bg-<?= $report['status'] === 'draft' ? 'secondary' : 'success' ?>"><?= $report['status'] ?></span></div>
        </div>
        <div class="row mb-3">
            <div class="col-12"><strong>Remarks:</strong> <?= htmlspecialchars($report['remarks'] ?? '') ?></div>
        </div>
        <h6 class="border-bottom pb-2">Items</h6>
        <div class="table-responsive">
            <table class="table table-hover table-striped">
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
                        <tr><td colspan="6" class="text-center">No items in this report.</td></tr>
                    <?php else: ?>
                        <?php foreach ($report['items'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['asset_code']) ?></td>
                                <td><?= htmlspecialchars($item['asset_name'] ?? $item['asset_description']) ?></td>
                                <td><span class="badge bg-<?= $item['verification_status'] === 'verified' ? 'success' : 'warning' ?>"><?= $item['verification_status'] ?></span></td>
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