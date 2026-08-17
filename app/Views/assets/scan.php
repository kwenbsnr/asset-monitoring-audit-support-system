<?php if (!defined('APP_START')) exit; ?>
<div class="grid grid-cols-1 md:grid-cols-12 gap-6">
    <!-- LEFT -->
    <div class="md:col-span-4">
        <div class="card-panel h-full p-4">
            <h5 class="font-bold text-gray-800 border-b border-gray-200 pb-3 mb-4 flex items-center gap-2">
                <span class="page-icon page-icon-sm"><i class="bi bi-qr-code-scan"></i></span> Find Asset
            </h5>
            <div class="flex flex-col items-center">
                <div class="relative w-full max-w-87.5 aspect-square bg-gray-100 rounded-lg overflow-hidden shadow-inner" id="reader-wrapper">
                    <div id="reader" class="w-full h-full"></div>
                    <div id="scanner-frame" class="absolute inset-0 pointer-events-none scanner-frame-idle">
                        <div id="scanner-line" class="scanner-line"></div>
                        <div id="scanner-checkmark" class="scanner-checkmark hidden">✓</div>
                    </div>
                </div>
                <div class="mt-4 w-full max-w-87.5 space-y-2">
                    <button id="startScannerBtn" class="w-full btn-app btn-app-primary hidden">
                        <i class="bi bi-camera"></i> Tap to scan QR code
                    </button>
                    <button id="stopScannerBtn" class="w-full btn-app btn-app-danger hidden">
                        <i class="bi bi-stop-circle"></i> Stop Camera
                    </button>
                    <button id="switchCameraBtn" class="w-full btn-app btn-app-outline hidden">
                        <i class="bi bi-arrow-repeat"></i> Switch Camera
                    </button>
                    <p class="text-xs text-gray-500 text-center"><i class="bi bi-info-circle"></i> Point the camera at an asset QR label.</p>
                </div>
                <hr class="my-4 w-full max-w-87.5 border-gray-300">
                <div class="w-full max-w-87.5">
                    <div class="text-xs text-gray-500 text-center mb-1">— or search manually —</div>
                    <div class="flex">
                        <input type="text" id="manualSearchInput" class="flex-1 border border-gray-300 rounded-l-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" placeholder="Asset code, serial number, or description...">
                        <button id="manualSearchBtn" class="btn-app btn-app-primary btn-app-join-r">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                    <div id="manualSearchError" class="text-red-600 text-xs mt-1 hidden"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="md:col-span-8">
        <div class="card-panel h-full flex flex-col">
            <div class="card-panel-header">
                <div class="flex items-center gap-3">
                    <span class="page-icon"><i class="bi bi-file-earmark-text"></i></span>
                    <span class="page-title">Asset Verification</span>
                </div>
                <button id="scanAnotherBtn" class="btn-app btn-app-sm btn-app-outline hidden">
                    <i class="bi bi-arrow-counterclockwise"></i> Scan Another Asset
                </button>
            </div>
            <div class="flex-1 p-6" id="profileBody">
                <div id="profilePlaceholder" class="text-center text-gray-500 py-12">
                    <i class="bi bi-box-seam text-6xl"></i>
                    <p class="mt-3">No asset selected.</p>
                    <p class="text-sm">Scan a QR code or search manually to verify an asset.</p>
                </div>
                <div id="profileContent" class="hidden">
                    <form id="verifyForm" method="POST" action="index.php?page=assets&sub=scan">
                        <input type="hidden" name="asset_id" id="assetIdField" value="">
                        <h6 class="font-semibold text-gray-800 border-b pb-2 mb-3">Asset Information (View‑only)</h6>
                        <div id="assetInfo" class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm mb-4"></div>
                        <div id="actionButtons" class="flex gap-2 mb-4">
                            <button type="submit" name="mark_verified" class="btn-app btn-app-primary">
                                <i class="bi bi-check-circle"></i> Mark as Verified
                            </button>
                            <button type="button" id="showUpdateBtn" class="btn-app btn-app-gold">
                                <i class="bi bi-pencil"></i> Update Asset Details
                            </button>
                        </div>
                        <div id="editableFields" class="hidden">
                            <h6 class="font-semibold text-gray-800 border-b pb-2 mt-4">Inspection / Operational Data</h6>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                <div>
                                    <label for="condition" class="block text-sm font-medium text-gray-700">Condition</label>
                                    <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="condition" name="condition">
                                        <option value="good">Good</option>
                                        <option value="fair">Fair</option>
                                        <option value="poor">Poor</option>
                                        <option value="damaged">Damaged</option>
                                        <option value="obsolete">Obsolete</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                    <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="status" name="status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="missing">Missing</option>
                                        <option value="disposed">Disposed</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="verification_status" class="block text-sm font-medium text-gray-700">Verification Status</label>
                                    <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="verification_status" name="verification_status">
                                        <option value="pending">Pending</option>
                                        <option value="verified">Verified</option>
                                        <option value="discrepancy">Discrepancy</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="custodian_id" class="block text-sm font-medium text-gray-700">Accountable Custodian</label>
                                    <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="custodian_id" name="custodian_id">
                                        <option value="">Select Custodian</option>
                                        <?php foreach ($personnel as $p): ?>
                                            <option value="<?= $p['personnel_id'] ?>"><?= htmlspecialchars($p['full_name'] . ' (' . $p['position'] . ')') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="office_id" class="block text-sm font-medium text-gray-700">Office</label>
                                    <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="office_id" name="office_id">
                                        <option value="">Select Office</option>
                                        <?php foreach ($offices as $o): ?>
                                            <option value="<?= $o['office_id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="inspection_remarks" class="block text-sm font-medium text-gray-700">Inspection Remarks</label>
                                    <textarea class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="inspection_remarks" name="inspection_remarks" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="flex gap-2 mt-4">
                                <button type="submit" name="update_asset" class="btn-app btn-app-primary">
                                    <i class="bi bi-save"></i> Save Updates
                                </button>
                                <button type="button" id="cancelUpdateBtn" class="btn-app btn-app-outline">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div id="profileFooter" class="border-t border-gray-200 px-6 py-3 bg-gray-50 hidden">
                <div class="flex justify-between items-center">
                    <span id="scanSuccessMsg" class="text-sm text-green-600 hidden"><i class="bi bi-check-circle"></i> Asset loaded.</span>
                    <span id="updateMsg" class="text-sm text-green-600"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<!--
    NOTE: public/js/scanner.js is intentionally NOT re-included here.
    app/Views/layouts/main.php already loads it once at the bottom of every
    page (including this one). Loading it a second time redeclares its
    top-level `let`/`const` bindings in the same global script scope and
    throws "Identifier has already been declared", which also silently
    breaks the showAssetProfile override below because the failed second
    load never runs its trailing `window.showAssetProfile = showAssetProfile;`.
    The override is wrapped in DOMContentLoaded so it runs after main.php's
    single scanner.js load has finished defining the default.
-->
<script>
document.addEventListener('DOMContentLoaded', function() {
// Override showAssetProfile to populate the form and show actions
const originalShowAssetProfile = window.showAssetProfile;
window.showAssetProfile = function(data) {
    const asset = data.asset;
    const custody = data.custody || [];
    const activeCustody = custody.find(c => c.custody_status === 'active');

    // Populate hidden asset_id
    document.getElementById('assetIdField').value = asset.asset_id;

    // Populate view-only fields
    const infoDiv = document.getElementById('assetInfo');
    infoDiv.innerHTML = `
        <div><strong>Asset Code:</strong> ${escapeHtml(asset.asset_code)}</div>
        <div><strong>Property Number:</strong> ${escapeHtml(asset.asset_code)}</div>
        <div><strong>Asset Name:</strong> ${escapeHtml(asset.asset_name)}</div>
        <div><strong>Description:</strong> ${escapeHtml(asset.description || 'N/A')}</div>
        <div><strong>Classification:</strong> ${escapeHtml(asset.account_code || 'N/A')}</div>
        <div><strong>Account Code:</strong> ${escapeHtml(asset.account_code || 'N/A')}</div>
        <div><strong>Brand:</strong> ${escapeHtml(asset.brand || 'N/A')}</div>
        <div><strong>Model:</strong> ${escapeHtml(asset.model || 'N/A')}</div>
        <div><strong>Serial Number:</strong> ${escapeHtml(asset.serial_number || 'N/A')}</div>
        <div><strong>Acquisition Date:</strong> ${asset.acquisition_date || 'N/A'}</div>
        <div><strong>Acquisition Cost:</strong> ${asset.acquisition_cost ? '₱' + Number(asset.acquisition_cost).toFixed(2) : 'N/A'}</div>
        <div><strong>Supplier:</strong> N/A</div>
        <div><strong>Funding Source:</strong> N/A</div>
        <div><strong>Created:</strong> ${asset.created_at || 'N/A'}</div>
        <div><strong>Updated:</strong> ${asset.updated_at || 'N/A'}</div>
    `;

    // Populate editable fields (hidden initially)
    document.getElementById('condition').value = asset.condition || 'good';
    document.getElementById('status').value = asset.status || 'active';
    document.getElementById('verification_status').value = asset.verification_status || 'pending';
    document.getElementById('inspection_remarks').value = asset.inspection_remarks || '';

    // Custodian and office
    const custodianSelect = document.getElementById('custodian_id');
    const officeSelect = document.getElementById('office_id');
    if (activeCustody) {
        custodianSelect.value = activeCustody.custodian_id;
        officeSelect.value = activeCustody.office_id;
    } else {
        custodianSelect.value = '';
        officeSelect.value = '';
    }

    // Show content, hide editable fields initially, show action buttons
    document.getElementById('profilePlaceholder').style.display = 'none';
    document.getElementById('profileContent').style.display = 'block';
    document.getElementById('profileFooter').style.display = 'flex';
    document.getElementById('scanAnotherBtn').style.display = 'inline-block';
    document.getElementById('scanSuccessMsg').style.display = 'inline';
    document.getElementById('editableFields').style.display = 'none';
    document.getElementById('actionButtons').style.display = 'flex';
};

// Toggle editable fields
document.getElementById('showUpdateBtn').addEventListener('click', function() {
    document.getElementById('editableFields').style.display = 'block';
    document.getElementById('actionButtons').style.display = 'none';
});

document.getElementById('cancelUpdateBtn').addEventListener('click', function() {
    document.getElementById('editableFields').style.display = 'none';
    document.getElementById('actionButtons').style.display = 'flex';
});

// Manual search
document.getElementById('manualSearchBtn').addEventListener('click', function() {
    const query = document.getElementById('manualSearchInput').value.trim();
    if (query.length < 2) {
        document.getElementById('manualSearchError').textContent = 'Please enter at least 2 characters.';
        document.getElementById('manualSearchError').style.display = 'block';
        return;
    }
    document.getElementById('manualSearchError').style.display = 'none';
    fetch(`index.php?page=assets&sub=details&q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            window.showAssetProfile(data);
        })
        .catch(err => alert('Failed to fetch asset: ' + err.message));
});

// Auto-start scanner (safe to run in the same DOMContentLoaded callback,
// after the override above has already replaced window.showAssetProfile)
setTimeout(() => {
    if (typeof startScanner === 'function') {
        startScanner();
    }
}, 500);

}); // end DOMContentLoaded

// Reuse escapeHtml from scanner.js
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>