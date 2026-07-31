<?php if (!defined('APP_START')) exit; ?>
<div class="grid grid-cols-1 md:grid-cols-12 gap-6">
    <!-- LEFT: Scanner / Search -->
    <div class="md:col-span-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 h-full p-4">
            <h5 class="font-bold text-gray-800 border-b border-gray-200 pb-2 mb-4 flex items-center gap-2">
                <i class="bi bi-qr-code-scan"></i> Find Asset
            </h5>
            <div class="flex flex-col items-center">
                <div class="relative w-full max-w-[350px] aspect-square bg-gray-100 rounded-lg overflow-hidden shadow-inner" id="reader-wrapper">
                    <div id="reader" class="w-full h-full"></div>
                    <div id="scanner-frame" class="absolute inset-0 pointer-events-none scanner-frame-idle">
                        <div id="scanner-line" class="scanner-line"></div>
                        <div id="scanner-checkmark" class="scanner-checkmark hidden">✓</div>
                    </div>
                </div>
                <div class="mt-4 w-full max-w-[350px] space-y-2">
                    <button id="startScannerBtn" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 hidden">
                        <i class="bi bi-camera"></i> Tap to scan QR code
                    </button>
                    <button id="stopScannerBtn" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 hidden">
                        <i class="bi bi-stop-circle"></i> Stop Camera
                    </button>
                    <button id="switchCameraBtn" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 hidden">
                        <i class="bi bi-arrow-repeat"></i> Switch Camera
                    </button>
                    <p class="text-xs text-gray-500 text-center"><i class="bi bi-info-circle"></i> Point the camera at an asset QR label.</p>
                </div>
                <hr class="my-4 w-full max-w-[350px] border-gray-300">
                <div class="w-full max-w-[350px]">
                    <div class="text-xs text-gray-500 text-center mb-1">— or search manually —</div>
                    <div class="flex">
                        <input type="text" id="manualSearchInput" class="flex-1 border border-gray-300 rounded-l px-3 py-2 text-sm focus:ring-1 focus:ring-green-500 focus:border-green-500" placeholder="Asset code, serial number, or description...">
                        <button id="manualSearchBtn" class="px-4 py-2 bg-blue-600 text-white rounded-r hover:bg-blue-700">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                    <div id="manualSearchError" class="text-red-600 text-xs mt-1 hidden"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT: Asset Profile + Actions -->
    <div class="md:col-span-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 h-full flex flex-col">
            <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                <h5 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="bi bi-file-earmark-text"></i> Asset Verification
                </h5>
                <button id="scanAnotherBtn" class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50 hidden">
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
                    <form id="verifyForm" method="POST" action="index.php?page=assets&sub=verify">
                        <input type="hidden" name="asset_id" id="assetIdField" value="">

                        <h6 class="font-semibold text-gray-800 border-b pb-2 mb-3">Asset Information (View‑only)</h6>
                        <div id="assetInfo" class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm mb-4"></div>

                        <div id="currentCustodianInfo" class="grid grid-cols-2 gap-2 text-sm mb-4" style="display: none;">
                            <div><strong>Custodian:</strong> <span id="currentCustodianName"></span></div>
                            <div><strong>Office:</strong> <span id="currentOfficeName"></span></div>
                        </div>

                        <div id="custodyHistoryContainer" class="hidden">
                            <h6 class="font-semibold text-gray-800 border-b pb-2 mt-4">Custody History</h6>
                            <div id="custodyHistoryTable" class="overflow-x-auto"></div>
                        </div>

                        <div id="transferHistoryContainer" class="hidden">
                            <h6 class="font-semibold text-gray-800 border-b pb-2 mt-4">Transfer History</h6>
                            <div id="transferHistoryTable" class="overflow-x-auto"></div>
                        </div>

                        <div id="actionButtons" class="flex gap-2 mb-4">
                            <button type="submit" name="mark_verified" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                <i class="bi bi-check-circle"></i> Mark as Verified
                            </button>
                            <button type="button" id="showUpdateBtn" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                <i class="bi bi-pencil"></i> Update Asset Details
                            </button>
                        </div>

                        <div id="editableFields" class="hidden">
                            <h6 class="font-semibold text-gray-800 border-b pb-2 mt-4">Inspection / Operational Data</h6>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                <div>
                                    <label for="condition" class="block text-sm font-medium text-gray-700">Condition</label>
                                    <select class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="condition" name="condition">
                                        <option value="good">Good</option>
                                        <option value="fair">Fair</option>
                                        <option value="poor">Poor</option>
                                        <option value="damaged">Damaged</option>
                                        <option value="obsolete">Obsolete</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                    <select class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="status" name="status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="missing">Missing</option>
                                        <option value="disposed">Disposed</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="verification_status" class="block text-sm font-medium text-gray-700">Verification Status</label>
                                    <select class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="verification_status" name="verification_status">
                                        <option value="pending">Pending</option>
                                        <option value="verified">Verified</option>
                                        <option value="discrepancy">Discrepancy</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="custodian_id" class="block text-sm font-medium text-gray-700">Accountable Custodian</label>
                                    <select class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="custodian_id" name="custodian_id">
                                        <option value="">Select Custodian</option>
                                        <?php foreach ($personnel as $p): ?>
                                            <option value="<?= $p['personnel_id'] ?>" data-office-id="<?= $p['office_id'] ?>">
                                                <?= htmlspecialchars($p['full_name'] . ' (' . $p['position'] . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="office_id" id="office_id" value="">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="inspection_remarks" class="block text-sm font-medium text-gray-700">Inspection Remarks</label>
                                    <textarea class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="inspection_remarks" name="inspection_remarks" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="flex gap-2 mt-4">
                                <button type="submit" name="update_asset" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    <i class="bi bi-save"></i> Save Updates
                                </button>
                                <button type="button" id="cancelUpdateBtn" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Cancel</button>
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
<script src="/asset-monitoring-audit-support-system/public/js/scanner.js"></script>
<script>
// Override showAssetProfile to populate the form and display full history
const originalShowAssetProfile = window.showAssetProfile;
window.showAssetProfile = function(data) {
    const asset = data.asset;
    const custody = data.custody || [];
    const transfers = data.transfers || [];
    const activeCustody = custody.find(c => c.custody_status === 'active');

    document.getElementById('assetIdField').value = asset.asset_id;

    const infoDiv = document.getElementById('assetInfo');
    infoDiv.innerHTML = `
        <div class="md:col-span-1"><strong>Asset Code:</strong> ${escapeHtml(asset.asset_code)}</div>
        <div class="md:col-span-1"><strong>Property Number:</strong> ${escapeHtml(asset.asset_code)}</div>
        <div class="md:col-span-1"><strong>Asset Name:</strong> ${escapeHtml(asset.asset_name)}</div>
        <div class="md:col-span-1"><strong>Description:</strong> ${escapeHtml(asset.description || 'N/A')}</div>
        <div class="md:col-span-1"><strong>Classification:</strong> ${escapeHtml(asset.account_code || 'N/A')}</div>
        <div class="md:col-span-1"><strong>Account Code:</strong> ${escapeHtml(asset.account_code || 'N/A')}</div>
        <div class="md:col-span-1"><strong>Brand:</strong> ${escapeHtml(asset.brand || 'N/A')}</div>
        <div class="md:col-span-1"><strong>Model:</strong> ${escapeHtml(asset.model || 'N/A')}</div>
        <div class="md:col-span-1"><strong>Serial Number:</strong> ${escapeHtml(asset.serial_number || 'N/A')}</div>
        <div class="md:col-span-1"><strong>Acquisition Date:</strong> ${asset.acquisition_date || 'N/A'}</div>
        <div class="md:col-span-1"><strong>Acquisition Cost:</strong> ${asset.acquisition_cost ? '₱' + Number(asset.acquisition_cost).toFixed(2) : 'N/A'}</div>
        <div class="md:col-span-1"><strong>Supplier:</strong> N/A</div>
        <div class="md:col-span-1"><strong>Funding Source:</strong> N/A</div>
        <div class="md:col-span-1"><strong>Status:</strong> <span class="px-2 py-0.5 rounded-full text-xs font-medium ${asset.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">${asset.status}</span></div>
        <div class="md:col-span-1"><strong>Condition:</strong> <span class="px-2 py-0.5 rounded-full text-xs font-medium ${asset.condition === 'good' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">${asset.condition}</span></div>
        <div class="md:col-span-1"><strong>Created:</strong> ${asset.created_at || 'N/A'}</div>
        <div class="md:col-span-1"><strong>Updated:</strong> ${asset.updated_at || 'N/A'}</div>
        <div class="md:col-span-1"><strong>Verification Status:</strong> <span class="px-2 py-0.5 rounded-full text-xs font-medium ${asset.verification_status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">${asset.verification_status || 'pending'}</span></div>
        <div class="md:col-span-1"><strong>Last Verified:</strong> ${asset.verified_at ? new (window.Date)(asset.verified_at).toLocaleString() : 'Never'}</div>
        <div class="md:col-span-1"><strong>Verified By:</strong> ${asset.verified_by_username || 'N/A'}</div>
    `;

    const custodianInfo = document.getElementById('currentCustodianInfo');
    if (activeCustody) {
        document.getElementById('currentCustodianName').textContent = activeCustody.custodian_name + ' (' + (activeCustody.position || '') + ')';
        document.getElementById('currentOfficeName').textContent = activeCustody.office_name;
        custodianInfo.style.display = 'grid';
    } else {
        document.getElementById('currentCustodianName').textContent = 'Not assigned';
        document.getElementById('currentOfficeName').textContent = 'N/A';
        custodianInfo.style.display = 'grid';
    }

    const custodyContainer = document.getElementById('custodyHistoryContainer');
    const custodyTable = document.getElementById('custodyHistoryTable');
    if (custody.length === 0) {
        custodyTable.innerHTML = '<p class="text-gray-500 text-sm">No custody records found.</p>';
    } else {
        let tableHtml = '<table class="w-full text-sm border border-gray-200"><thead class="bg-gray-100"><tr><th class="px-2 py-1 border">From</th><th class="px-2 py-1 border">To</th><th class="px-2 py-1 border">Custodian</th><th class="px-2 py-1 border">Office</th><th class="px-2 py-1 border">Status</th><th class="px-2 py-1 border">Document</th></tr></thead><tbody>';
        custody.forEach(c => {
            tableHtml += `<tr>
                <td class="px-2 py-1 border">${c.effectivity_date || 'N/A'}</td>
                <td class="px-2 py-1 border">${c.end_date || 'Current'}</td>
                <td class="px-2 py-1 border">${escapeHtml(c.custodian_name)} <br><span class="text-xs text-gray-500">${escapeHtml(c.position || '')}</span></td>
                <td class="px-2 py-1 border">${escapeHtml(c.office_name)}</td>
                <td class="px-2 py-1 border"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${c.custody_status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">${c.custody_status}</span></td>
                <td class="px-2 py-1 border">${escapeHtml(c.accountability_document || '')} ${c.accountability_reference ? '<br><span class="text-xs text-gray-500">Ref: ' + escapeHtml(c.accountability_reference) + '</span>' : ''}</td>
            </tr>`;
        });
        tableHtml += '</tbody></table>';
        custodyTable.innerHTML = tableHtml;
    }
    custodyContainer.style.display = 'block';

    const transferContainer = document.getElementById('transferHistoryContainer');
    const transferTable = document.getElementById('transferHistoryTable');
    if (transfers.length === 0) {
        transferTable.innerHTML = '<p class="text-gray-500 text-sm">No transfer records found.</p>';
    } else {
        let tableHtml = '<table class="w-full text-sm border border-gray-200"><thead class="bg-gray-100"><tr><th class="px-2 py-1 border">Transfer #</th><th class="px-2 py-1 border">Date</th><th class="px-2 py-1 border">From</th><th class="px-2 py-1 border">To</th><th class="px-2 py-1 border">Status</th><th class="px-2 py-1 border">Remarks</th></tr></thead><tbody>';
        transfers.forEach(t => {
            tableHtml += `<tr>
                <td class="px-2 py-1 border">${escapeHtml(t.transfer_number)}</td>
                <td class="px-2 py-1 border">${escapeHtml(t.transfer_date)}</td>
                <td class="px-2 py-1 border">${escapeHtml(t.from_custodian)} (${escapeHtml(t.from_office || '')})</td>
                <td class="px-2 py-1 border">${escapeHtml(t.to_custodian)} (${escapeHtml(t.to_office || '')})</td>
                <td class="px-2 py-1 border"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${t.status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">${escapeHtml(t.status)}</span></td>
                <td class="px-2 py-1 border">${escapeHtml(t.remarks || '')}</td>
            </tr>`;
        });
        tableHtml += '</tbody></table>';
        transferTable.innerHTML = tableHtml;
    }
    transferContainer.style.display = 'block';

    document.getElementById('condition').value = asset.condition || 'good';
    document.getElementById('status').value = asset.status || 'active';
    document.getElementById('verification_status').value = asset.verification_status || 'pending';
    document.getElementById('inspection_remarks').value = asset.inspection_remarks || '';

    const custodianSelect = document.getElementById('custodian_id');
    const officeHidden = document.getElementById('office_id');
    if (activeCustody) {
        custodianSelect.value = activeCustody.custodian_id;
        officeHidden.value = activeCustody.office_id;
    } else {
        custodianSelect.value = '';
        officeHidden.value = '';
    }

    custodianSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const officeId = selectedOption ? selectedOption.getAttribute('data-office-id') : '';
        officeHidden.value = officeId || '';
    });

    document.getElementById('profilePlaceholder').style.display = 'none';
    document.getElementById('profileContent').style.display = 'block';
    document.getElementById('profileFooter').style.display = 'flex';
    document.getElementById('scanAnotherBtn').style.display = 'inline-block';
    document.getElementById('scanSuccessMsg').style.display = 'inline';
    document.getElementById('editableFields').style.display = 'none';
    document.getElementById('actionButtons').style.display = 'flex';
};

document.getElementById('showUpdateBtn').addEventListener('click', function() {
    document.getElementById('editableFields').style.display = 'block';
    document.getElementById('actionButtons').style.display = 'none';
});

document.getElementById('cancelUpdateBtn').addEventListener('click', function() {
    document.getElementById('editableFields').style.display = 'none';
    document.getElementById('actionButtons').style.display = 'flex';
});

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

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        if (typeof startScanner === 'function') {
            startScanner();
        }
    }, 500);
});

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>