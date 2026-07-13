<?php if (!defined('APP_START')) exit;
$r = $request;
?>
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> Review Disposal Request #<?= $r['id'] ?></h4>
        <a href="index.php?page=disposal" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4"><strong>Asset Code:</strong> <?= htmlspecialchars($r['asset_code']) ?></div>
            <div class="col-md-4"><strong>Asset Name:</strong> <?= htmlspecialchars($r['asset_name']) ?></div>
            <div class="col-md-4"><strong>Status:</strong> <span class="badge bg-<?= $r['status'] === 'pending' ? 'warning' : ($r['status'] === 'approved' ? 'success' : 'danger') ?>"><?= $r['status'] ?></span></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6"><strong>Requested By:</strong> <?= htmlspecialchars($r['requested_by_username']) ?></div>
            <div class="col-md-6"><strong>Date:</strong> <?= htmlspecialchars($r['created_at']) ?></div>
        </div>
        <div class="mb-3">
            <strong>Reason:</strong>
            <p class="border p-2 bg-light"><?= nl2br(htmlspecialchars($r['reason'])) ?></p>
        </div>
        <?php if ($r['supporting_document']): ?>
            <div class="mb-3">
                <strong>Supporting Document:</strong><br>
                <a href="<?= htmlspecialchars($r['supporting_document']) ?>" target="_blank" class="btn btn-sm btn-info"><i class="bi bi-file-earmark"></i> View Document</a>
            </div>
        <?php endif; ?>

        <?php if ($r['status'] === 'pending'): ?>
            <hr>
            <form method="POST" action="index.php?page=disposal&sub=process">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <div class="mb-3">
                    <label for="remarks" class="form-label">Review Remarks</label>
                    <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" name="action" value="approve" class="btn btn-success"><i class="bi bi-check-circle"></i> Approve</button>
                    <button type="submit" name="action" value="reject" class="btn btn-danger"><i class="bi bi-x-circle"></i> Reject</button>
                    <a href="index.php?page=disposal" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        <?php else: ?>
            <hr>
            <div class="mb-3">
                <strong>Reviewed By:</strong> <?= htmlspecialchars($r['reviewed_by_username']) ?>
            </div>
            <div class="mb-3">
                <strong>Review Remarks:</strong>
                <p class="border p-2 bg-light"><?= nl2br(htmlspecialchars($r['review_remarks'] ?? 'None')) ?></p>
            </div>
            <div class="mb-3">
                <strong>Final Decision:</strong> <span class="badge bg-<?= $r['status'] === 'approved' ? 'success' : 'danger' ?>"><?= strtoupper($r['status']) ?></span>
            </div>
            <a href="index.php?page=disposal" class="btn btn-secondary">Back</a>
        <?php endif; ?>
    </div>
</div>