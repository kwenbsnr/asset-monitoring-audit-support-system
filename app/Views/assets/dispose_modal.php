<?php if (!defined('APP_START')) exit; ?>
<div class="modal fade" id="disposeModal" tabindex="-1" aria-labelledby="disposeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="disposeForm" method="POST" action="index.php?page=assets&sub=dispose">
                <input type="hidden" name="asset_id" id="disposeAssetId">
                <div class="modal-header">
                    <h5 class="modal-title" id="disposeModalLabel">Dispose Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Warning:</strong> This action will mark the asset as <strong>disposed</strong>. 
                        This cannot be undone, but the asset record will remain in the system for audit purposes.
                    </div>
                    <div class="mb-3">
                        <label for="disposal_reason" class="form-label">Reason for Disposal *</label>
                        <textarea class="form-control" id="disposal_reason" name="disposal_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Disposal</button>
                </div>
            </form>
        </div>
    </div>
</div>