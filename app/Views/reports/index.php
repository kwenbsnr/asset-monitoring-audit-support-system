<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> Reports</h4>
        <a href="index.php?page=reports&sub=add" class="btn btn-success btn-sm">
            <i class="bi bi-plus-circle"></i> Create Report
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'success' ?> alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['flash']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Report #</th>
                        <th>Date</th>
                        <th>Office</th>
                        <th>Prepared By</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)): ?>
                        <tr><td colspan="7" class="text-center">No reports found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($reports as $r): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['report_number']) ?></strong></td>
                                <td><?= htmlspecialchars($r['report_date']) ?></td>
                                <td><?= htmlspecialchars($r['office_name']) ?></td>
                                <td><?= htmlspecialchars($r['prepared_by_username']) ?></td>
                                <td><span class="badge bg-<?= $r['status'] === 'draft' ? 'secondary' : 'success' ?>"><?= $r['status'] ?></span></td>
                                <td><?= htmlspecialchars($r['remarks'] ?? '') ?></td>
                                <td>
                                    <a href="index.php?page=reports&sub=view&id=<?= $r['asset_report_id'] ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                                    <?php if ($r['status'] === 'draft'): ?>
                                        <a href="index.php?page=reports&sub=delete&id=<?= $r['asset_report_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this draft report?')"><i class="bi bi-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>