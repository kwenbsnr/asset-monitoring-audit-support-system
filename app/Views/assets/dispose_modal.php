<?php if (!defined('APP_START')) exit; ?>
<!-- Dispose Modal – uses style="display:none" initially, no class conflict -->
<div id="disposeModal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center" style="display:none; z-index:1050;">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <form id="disposeForm" method="POST" action="index.php?page=assets&sub=dispose">
            <input type="hidden" name="asset_id" id="disposeAssetId">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h5 class="text-lg font-semibold text-gray-800">Dispose Asset</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeDisposeModal()">&times;</button>
            </div>
            <div class="px-6 py-4">
                <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 p-3 rounded mb-4 flex items-start gap-2">
                    <i class="bi bi-exclamation-triangle text-yellow-600 mt-0.5"></i>
                    <div>
                        <strong>Warning:</strong> This action will mark the asset as <strong>disposed</strong>. 
                        This cannot be undone, but the asset record will remain in the system for audit purposes.
                    </div>
                </div>
                <div>
                    <label for="disposal_reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for Disposal *</label>
                    <textarea class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-red-500 focus:border-red-500" id="disposal_reason" name="disposal_reason" rows="3" required></textarea>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-2">
                <button type="button" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400" onclick="closeDisposeModal()">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Confirm Disposal</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDisposeModal(assetId) {
    document.getElementById('disposeAssetId').value = assetId;
    var modal = document.getElementById('disposeModal');
    modal.style.display = 'flex';
}
function closeDisposeModal() {
    document.getElementById('disposeModal').style.display = 'none';
}
document.getElementById('disposeModal').addEventListener('click', function(e) {
    if (e.target === this) closeDisposeModal();
});
</script>