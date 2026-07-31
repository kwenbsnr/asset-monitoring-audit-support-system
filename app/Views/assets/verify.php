<?php if (!defined('APP_START')) exit; ?>
<div class="container-fluid">
    <div class="row g-4">
        <!-- LEFT: Scanner / Search -->
        <div class="col-lg-4 col-md-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-qr-code-scan me-2"></i>Find Asset</h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center">
                    <!-- Scanner Preview -->
                    <div id="reader-wrapper" class="position-relative" style="max-width:350px; width:100%; aspect-ratio:1/1;">
                        <div id="reader" style="width:100%; height:100%;"></div>
                        <div id="scanner-frame" class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none scanner-frame-idle">
                            <div id="scanner-line" class="scanner-line"></div>
                            <div id="scanner-checkmark" class="scanner-checkmark d-none">✓</div>
                        </div>
                    </div>

                    <!-- Controls -->
                    <div class="mt-3 text-center" style="max-width:350px; width:100%;">
                        <button id="startScannerBtn" class="btn btn-success w-100" style="display:none;">
                            <i class="bi bi-camera"></i> Tap to scan QR code
                        </button>
                        <button id="stopScannerBtn" class="btn btn-danger w-100 mt-2" style="display:none;">
                            <i class="bi bi-stop-circle"></i> Stop Camera
                        </button>
                        <button id="switchCameraBtn" class="btn btn-info w-100 mt-2" style="display:none;">
                            <i class="bi bi-arrow-repeat"></i> Switch Camera
                        </button>
                        <p class="text-muted small mt-2 mb-0">
                            <i class="bi bi-info-circle"></i> Point the camera at an asset QR label.
                        </p>
                    </div>

                    <hr class="my-3" style="max-width:350px; width:100%;">

                    <!-- Manual Search -->
                    <div style="max-width:350px; width:100%;">
                        <div class="text-muted text-center small mb-2">— or search manually —</div>
                        <div class="input-group">
                            <input type="text" id="manualSearchInput" class="form-control" placeholder="Asset code, serial number, or description...">
                            <button id="manualSearchBtn" class="btn btn-primary">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                        <div id="manualSearchError" class="text-danger small mt-1" style="display:none;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Asset Profile + Actions -->
        <div class="col-lg-8 col-md-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text me-2"></i>Asset Verification</h5>
                    <button id="scanAnotherBtn" class="btn btn-outline-secondary btn-sm" style="display:none;">
                        <i class="bi bi-arrow-counterclockwise"></i> Scan Another Asset
                    </button>
                </div>
                <div class="card-body" id="profileBody">
                    <!-- Placeholder -->
                    <div id="profilePlaceholder" class="text-center text-muted py-5">
                        <i class="bi bi-box-seam" style="font-size: 4rem;"></i>
                        <p class="mt-3">No asset selected.</p>
                        <p class="small">Scan a QR code or search manually to verify an asset.</p>
                    </div>

                    <!-- Asset Profile & Form -->
                    <div id="profileContent" style="display:none;">
                        <form id="verifyForm" method="POST" action="index.php?page=assets&sub=verify">
                            <input type="hidden" name="asset_id" id="assetIdField" value="">

                            <!-- View-only fields -->
                            <h6 class="border-bottom pb-2">Asset Information (View‑only)</h6>
                            <div id="assetInfo" class="row mb-3"></div>

                            <!-- Action Buttons (always visible) -->
                            <div id="actionButtons" class="d-flex gap-2 mb-3">
                                <button type="submit" name="mark_verified" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Mark as Verified
                                </button>
                                <button type="button" id="showUpdateBtn" class="btn btn-warning">
                                    <i class="bi bi-pencil"></i> Update Asset Details
                                </button>
                            </div>

                            <!-- Editable fields (hidden by default) -->
                            <div id="editableFields" style="display:none;">
                                <h6 class="border-bottom pb-2 mt-3">Inspection / Operational Data</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="condition" class="form-label">Condition</label>
                                        <select class="form-select" id="condition" name="condition">
                                            <option value="good">Good</option>
                                            <option value="fair">Fair</option>
                                            <option value="poor">Poor</option>
                                            <option value="damaged">Damaged</option>
                                            <option value="obsolete">Obsolete</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                            <option value="missing">Missing</option>
                                            <option value="disposed">Disposed</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="verification_status" class="form-label">Verification Status</label>
                                        <select class="form-select" id="verification_status" name="verification_status">
                                            <option value="pending">Pending</option>
                                            <option value="verified">Verified</option>
                                            <option value="discrepancy">Discrepancy</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="custodian_id" class="form-label">Accountable Custodian</label>
                                        <select class="form-select" id="custodian_id" name="custodian_id">
                                            <option value="">Select Custodian</option>
                                            <?php foreach ($personnel as $p): ?>
                                                <option value="<?= $p['personnel_id'] ?>"><?= htmlspecialchars($p['full_name'] . ' (' . $p['position'] . ')') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="office_id" class="form-label">Office</label>
                                        <select class="form-select" id="office_id" name="office_id">
                                            <option value="">Select Office</option>
                                            <?php foreach ($offices as $o): ?>
                                                <option value="<?= $o['office_id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="inspection_remarks" class="form-label">Inspection Remarks</label>
                                        <textarea class="form-control" id="inspection_remarks" name="inspection_remarks" rows="2"></textarea>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" name="update_asset" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Save Updates
                                    </button>
                                    <button type="button" id="cancelUpdateBtn" class="btn btn-secondary">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div id="profileFooter" class="card-footer bg-white border-top" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span id="scanSuccessMsg" class="text-success small"><i class="bi bi-check-circle"></i> Asset loaded.</span>
                        <span id="updateMsg" class="text-success small"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script src="public/js/scanner.js"></script>
<script>
// Override showAssetProfile to populate the form and manage visibility
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
        <div class="col-md-4"><strong>Asset Code:</strong> ${escapeHtml(asset.asset_code)}</div>
        <div class="col-md-4"><strong>Property Number:</strong> ${escapeHtml(asset.asset_code)}</div>
        <div class="col-md-4"><strong>Asset Name:</strong> ${escapeHtml(asset.asset_name)}</div>
        <div class="col-md-4"><strong>Description:</strong> ${escapeHtml(asset.description || 'N/A')}</div>
        <div class="col-md-4"><strong>Classification:</strong> ${escapeHtml(asset.account_code || 'N/A')}</div>
        <div class="col-md-4"><strong>Account Code:</strong> ${escapeHtml(asset.account_code || 'N/A')}</div>
        <div class="col-md-3"><strong>Brand:</strong> ${escapeHtml(asset.brand || 'N/A')}</div>
        <div class="col-md-3"><strong>Model:</strong> ${escapeHtml(asset.model || 'N/A')}</div>
        <div class="col-md-3"><strong>Serial Number:</strong> ${escapeHtml(asset.serial_number || 'N/A')}</div>
        <div class="col-md-3"><strong>Acquisition Date:</strong> ${asset.acquisition_date || 'N/A'}</div>
        <div class="col-md-3"><strong>Acquisition Cost:</strong> ${asset.acquisition_cost ? '₱' + Number(asset.acquisition_cost).toFixed(2) : 'N/A'}</div>
        <div class="col-md-3"><strong>Supplier:</strong> N/A</div>
        <div class="col-md-3"><strong>Funding Source:</strong> N/A</div>
        <div class="col-md-3"><strong>Created:</strong> ${asset.created_at || 'N/A'}</div>
        <div class="col-md-3"><strong>Updated:</strong> ${asset.updated_at || 'N/A'}</div>
        <div class="col-md-4"><strong>Verification Status:</strong> <span class="badge bg-${asset.verification_status === 'verified' ? 'success' : 'secondary'}">${asset.verification_status || 'pending'}</span></div>
        <div class="col-md-4"><strong>Last Verified:</strong> ${asset.verified_at ? new Date(asset.verified_at).toLocaleString() : 'Never'}</div>
        <div class="col-md-4"><strong>Verified By:</strong> ${asset.verified_by_username || 'N/A'}</div>
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

    // Show content, hide editable fields, show action buttons
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

// Auto-start scanner
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        if (typeof startScanner === 'function') {
            startScanner();
        }
    }, 500);
});

// Reuse escapeHtml from scanner.js
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>