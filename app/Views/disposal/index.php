<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="bi bi-trash"></i> Disposal Requests</h4>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <div>
                <a href="?page=disposal&status=pending" class="btn btn-sm btn-warning <?= (!isset($_GET['status']) || $_GET['status'] === 'pending') ? 'active' : '' ?>">Pending</a>
                <a href="?page=disposal&status=approved" class="btn btn-sm btn-success <?= (isset($_GET['status']) && $_GET['status'] === 'approved') ? 'active' : '' ?>">Approved</a>
                <a href="?page=disposal&status=rejected" class="btn btn-sm btn-danger <?= (isset($_GET['status']) && $_GET['status'] === 'rejected') ? 'active' : '' ?>">Rejected</a>
                <a href="?page=disposal&status=all" class="btn btn-sm btn-secondary <?= (isset($_GET['status']) && $_GET['status'] === 'all') ? 'active' : '' ?>">All</a>
            </div>
        <?php endif; ?>
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
                        <th>ID</th>
                        <th>Asset</th>
                        <th>Reason</th>
                        <th>Requested By</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr><td colspan="7" class="text-center">No disposal requests found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td><?= $r['id'] ?></td>
                                <td><strong><?= htmlspecialchars($r['asset_code']) ?></strong><br><small><?= htmlspecialchars($r['asset_name']) ?></small></td>
                                <td><?= htmlspecialchars(substr($r['reason'], 0, 50)) ?>...</td>
                                <td><?= htmlspecialchars($r['requested_by_username']) ?></td>
                                <td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                                <td><span class="badge bg-<?= $r['status'] === 'pending' ? 'warning' : ($r['status'] === 'approved' ? 'success' : 'danger') ?>"><?= $r['status'] ?></span></td>
                                <td>
                                    <?php if ($_SESSION['role'] === 'admin' && $r['status'] === 'pending'): ?>
                                        <a href="index.php?page=disposal&sub=review&id=<?= $r['id'] ?>" class="btn btn-sm btn-primary">Review</a>
                                    <?php elseif ($_SESSION['role'] === 'admin' && $r['status'] !== 'pending'): ?>
                                        <a href="index.php?page=disposal&sub=review&id=<?= $r['id'] ?>" class="btn btn-sm btn-info">View</a>
                                    <?php else: ?>
                                        <span class="text-muted"><?= $r['status'] ?></span>
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