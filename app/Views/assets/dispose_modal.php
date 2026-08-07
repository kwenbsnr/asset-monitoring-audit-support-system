<?php if (!defined('APP_START')) exit; ?>
<!-- Dispose Modal (standardized modal system — see public/css/style.css & public/js/modal.js) -->
<div id="disposeModal" class="modal-overlay">
    <div class="modal-panel modal-panel-sm" role="dialog" aria-modal="true" aria-labelledby="disposeModalTitle">
        <form id="disposeForm" method="POST" action="index.php?page=assets&sub=dispose">
            <input type="hidden" name="asset_id" id="disposeAssetId">
            <div class="modal-header">
                <h5 id="disposeModalTitle"><i class="bi bi-trash3 text-red-500 mr-1"></i> Dispose Asset</h5>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert-app" style="background:#fffbeb;border-color:#fde68a;color:#92400e;">
                    <span>
                        <i class="bi bi-exclamation-triangle mr-1"></i>
                        <strong>Warning:</strong> This action will mark the asset as <strong>disposed</strong>.
                        This cannot be undone, but the asset record will remain in the system for audit purposes.
                    </span>
                </div>
                <div class="mt-1">
                    <label for="disposal_reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for Disposal *</label>
                    <textarea class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-200 focus:border-red-500 transition" id="disposal_reason" name="disposal_reason" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-app btn-app-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn-app btn-app-danger"><i class="bi bi-trash3"></i> Confirm Disposal</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDisposeModal(assetId) {
    document.getElementById('disposeAssetId').value = assetId;
    NiaModal.open('disposeModal');
}
function closeDisposeModal() {
    NiaModal.close('disposeModal');
}
</script>