<?php if (!defined('APP_START')) exit; ?>
<!-- Employee Status Modal -->
<div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center" style="display:none; z-index:1050;">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <form id="statusForm" method="POST" action="index.php?page=employees&sub=updateStatus">
            <input type="hidden" name="personnel_id" id="statusPersonnelId">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h5 class="text-lg font-semibold text-gray-800">Change Employee Status</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeStatusModal()">&times;</button>
            </div>
            <div class="px-6 py-4">
                <p class="text-sm text-gray-600 mb-3">Employee: <strong id="statusEmployeeName"></strong></p>

                <div id="statusAssetWarning" class="hidden bg-yellow-50 border border-yellow-300 text-yellow-800 p-3 rounded mb-4 items-start gap-2">
                    <i class="bi bi-exclamation-triangle text-yellow-600 mt-0.5"></i>
                    <div>
                        This employee currently has <strong id="statusAssetCount">0</strong> asset(s) under active custody.
                        Changing their status here does not automatically reassign those assets — please transfer
                        custody separately via Custodial Tracking.
                    </div>
                </div>

                <label for="employment_status" class="block text-sm font-medium text-gray-700 mb-1">New Status *</label>
                <select class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="employment_status" name="employment_status" required>
                    <option value="active">Active</option>
                    <option value="retired">Retired</option>
                    <option value="transferred">Transferred to Another NIA Office</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-2">
                <button type="button" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400" onclick="closeStatusModal()">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update Status</button>
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

    document.getElementById('statusModal').style.display = 'flex';
}
function closeStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
}
document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) closeStatusModal();
});
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
