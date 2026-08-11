<?php if (!defined('APP_START')) exit; ?>
<!-- Employee Status Modal (standardized modal system) -->
<div id="statusModal" class="modal-overlay">
    <div class="modal-panel modal-panel-sm" role="dialog" aria-modal="true" aria-labelledby="statusModalTitle">
        <form id="statusForm" method="POST" action="index.php?page=employees&sub=updateStatus">
            <input type="hidden" name="personnel_id" id="statusPersonnelId">
            <div class="modal-header">
                <h5 id="statusModalTitle"><i class="bi bi-arrow-repeat mr-1"></i> Change Employee Status</h5>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <p class="text-sm text-gray-600 mb-3">Employee: <strong id="statusEmployeeName"></strong></p>

                <div id="statusAssetWarning" class="hidden alert-app alert-app-warning">
                    <span>
                        <i class="bi bi-exclamation-triangle"></i>
                        This employee currently has <strong id="statusAssetCount">0</strong> asset(s) under active custody.
                        Changing their status here does not automatically reassign those assets — please transfer
                        custody separately via Custodial Tracking.
                    </span>
                </div>

                <label for="employment_status" class="block text-sm font-medium text-gray-700 mb-1">New Status *</label>
                <select class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="employment_status" name="employment_status" required>
                    <option value="active">Active</option>
                    <option value="retired">Retired</option>
                    <option value="transferred">Transferred to Another NIA Office</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-app btn-app-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn-app btn-app-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

<script>
function openStatusModal(id, name, currentStatus, assetCount) {
    document.getElementById('statusPersonnelId').value = id;
    document.getElementById('statusEmployeeName').textContent = name;
    document.getElementById('employment_status').value = currentStatus;

    const warning = document.getElementById('statusAssetWarning');
    if (assetCount > 0) {
        document.getElementById('statusAssetCount').textContent = assetCount;
        warning.classList.remove('hidden');
    } else {
        warning.classList.add('hidden');
    }

    NiaModal.open('statusModal');
}
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.status-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            openStatusModal(
                this.dataset.id,
                this.dataset.name,
                this.dataset.status,
                parseInt(this.dataset.assets, 10) || 0
            );
        });
    });
});
</script>