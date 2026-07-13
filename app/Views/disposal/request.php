<?php if (!defined('APP_START')) exit;
$data = $_SESSION['form_data'] ?? [];
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);
$asset = $asset ?? null;
?>
<div class="card shadow">
    <div class="card-header">
        <h4><i class="bi bi-trash"></i> Request Disposal</h4>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div>
        <?php endif; ?>

        <?php if (!$asset): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> No asset selected. Please select an asset to request disposal.
            </div>
            <a href="index.php?page=assets&sub=browse" class="btn btn-secondary">Go to Asset Registry</a>
        <?php else: ?>
            <div class="row mb-3">
                <div class="col-md-6"><strong>Asset Code:</strong> <?= htmlspecialchars($asset['asset_code'] ?? '') ?></div>
                <div class="col-md-6"><strong>Asset Name:</strong> <?= htmlspecialchars($asset['asset_name'] ?? '') ?></div>
            </div>

            <form method="POST" action="index.php?page=disposal&sub=store" enctype="multipart/form-data">
                <input type="hidden" name="asset_id" value="<?= $asset['asset_id'] ?? '' ?>">

                <div class="mb-3">
                    <label for="reason" class="form-label">Reason for Disposal *</label>
                    <textarea class="form-control" id="reason" name="reason" rows="4" required><?= htmlspecialchars($data['reason'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="supporting_document" class="form-label">Supporting Document (optional)</label>
                    <input type="file" class="form-control" id="supporting_document" name="supporting_document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <div class="form-text">Accepted formats: PDF, JPG, PNG, DOC, DOCX. Max size: 5MB.</div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php?page=disposal" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-danger">Submit Disposal Request</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>