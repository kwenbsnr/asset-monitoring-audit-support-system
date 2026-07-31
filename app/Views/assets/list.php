<?php if (!defined('APP_START')) exit; ?>
<?php 
$flashType = $_SESSION['flash_type'] ?? 'success';
$alertClass = $flashType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="border-b border-gray-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
        <h4 class="text-xl font-bold text-green-700 flex items-center gap-2">
            <i class="bi bi-box-seam"></i> <?= $pageTitle ?? 'Assets' ?>
        </h4>
        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" action="index.php" class="flex gap-1">
                <input type="hidden" name="page" value="assets">
                <input type="hidden" name="sub" value="<?= isset($_GET['account_id']) ? 'browse' : 'list_all' ?>">
                <?php if (isset($_GET['account_id'])): ?>
                    <input type="hidden" name="account_id" value="<?= (int)$_GET['account_id'] ?>">
                <?php endif; ?>
                <div class="flex">
                    <input type="text" class="border border-gray-300 rounded-l px-3 py-1 text-sm focus:ring-1 focus:ring-green-500 focus:border-green-500" name="search" placeholder="Search..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button class="px-3 py-1 bg-green-600 text-white text-sm rounded-r hover:bg-green-700" type="submit"><i class="bi bi-search"></i></button>
                    <?php if (!empty($_GET['search'])): ?>
                        <a href="?page=assets&sub=<?= isset($_GET['account_id']) ? 'browse&account_id=' . (int)$_GET['account_id'] : 'list_all' ?>" class="px-3 py-1 bg-gray-300 text-gray-700 text-sm rounded-r hover:bg-gray-400"><i class="bi bi-x-circle"></i></a>
                    <?php endif; ?>
                </div>
            </form>
            <?php if (isset($account) && $account): ?>
                <a href="index.php?page=assets&sub=browse" class="px-3 py-1.5 text-sm bg-gray-500 text-white rounded hover:bg-gray-600">
                    <i class="bi bi-arrow-left"></i> Back to Accounts
                </a>
            <?php endif; ?>
            <a href="index.php?page=assets&sub=add" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700"><i class="bi bi-plus-circle"></i> Add</a>
        </div>
    </div>

    <div class="p-6">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="mb-4 p-3 rounded border <?= $alertClass ?> flex justify-between items-center">
                <span><?= htmlspecialchars($_SESSION['flash']) ?></span>
                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="this.parentElement.remove()">&times;</button>
            </div>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <?php if (!empty($_GET['search'])): ?>
            <div class="bg-blue-50 border border-blue-200 text-blue-700 p-3 rounded mb-4 flex items-center gap-2">
                <i class="bi bi-info-circle"></i> 
                Showing results for: <strong>"<?= htmlspecialchars($_GET['search']) ?>"</strong>
                <?php if (!empty($assets)): ?>
                    (<?= count($assets) ?> found)
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=assets&sub=bulk_qr" id="bulkQrForm">
            <div class="flex flex-wrap items-center justify-between mb-3">
                <div class="flex gap-2">
                    <button type="submit" class="px-3 py-1.5 text-sm border border-blue-600 text-blue-600 rounded hover:bg-blue-50" onclick="return confirm('Print QR codes for selected assets?')">
                        <i class="bi bi-printer"></i> Print Selected QR
                    </button>
                    <button type="button" class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50" onclick="toggleAllCheckboxes()">Select All</button>
                </div>
                <span class="text-sm text-gray-500" id="selectedCount">0 selected</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm border border-gray-200">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-3 py-2 border-b"><input type="checkbox" id="selectAll" onclick="toggleAllCheckboxes()"></th>
                            <th class="px-3 py-2 border-b font-medium">Asset Code</th>
                            <th class="px-3 py-2 border-b font-medium">Asset Name</th>
                            <th class="px-3 py-2 border-b font-medium">Brand / Model</th>
                            <th class="px-3 py-2 border-b font-medium">Serial #</th>
                            <th class="px-3 py-2 border-b font-medium">Account</th>
                            <th class="px-3 py-2 border-b font-medium">Custodian</th>
                            <th class="px-3 py-2 border-b font-medium">Status</th>
                            <th class="px-3 py-2 border-b font-medium">Actions</th>
                            <th class="px-3 py-2 border-b font-medium">Verification</th>
                            <th class="px-3 py-2 border-b font-medium">Last Verified</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assets)): ?>
                            <tr>
                                <td colspan="11" class="text-center py-4 text-gray-500">
                                    <?php if (!empty($_GET['search'])): ?>
                                        No assets found matching "<strong><?= htmlspecialchars($_GET['search']) ?></strong>".
                                    <?php else: ?>
                                        No assets found in this account.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($assets as $asset): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-3 py-2"><input type="checkbox" name="asset_ids[]" value="<?= $asset['asset_id'] ?>" class="asset-checkbox"></td>
                                    <td class="px-3 py-2 font-medium text-gray-800"><?= htmlspecialchars($asset['asset_code']) ?></td>
                                    <td class="px-3 py-2"><?= htmlspecialchars($asset['asset_name']) ?></td>
                                    <td class="px-3 py-2"><?= htmlspecialchars($asset['brand'] ?? '') ?> <?= htmlspecialchars($asset['model'] ?? '') ?></td>
                                    <td class="px-3 py-2"><?= htmlspecialchars($asset['serial_number'] ?? '') ?></td>
                                    <td class="px-3 py-2"><?= htmlspecialchars($asset['account_code'] ?? '') ?></td>
                                    <td class="px-3 py-2">
                                        <?php if (!empty($asset['custodians'])): ?>
                                            <?= htmlspecialchars($asset['custodians']) ?>
                                        <?php else: ?>
                                            <span class="text-gray-400">Not assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2">
                                        <?php 
                                            $statusClass = match($asset['status']) {
                                                'active' => 'bg-green-100 text-green-800',
                                                'pending_disposal' => 'bg-yellow-100 text-yellow-800',
                                                'disposed' => 'bg-gray-100 text-gray-800',
                                                'missing' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        ?>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>"><?= $asset['status'] ?></span>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <button type="button" class="px-2 py-1 text-blue-600 border border-blue-300 rounded hover:bg-blue-50 text-xs view-details" 
                                                data-id="<?= $asset['asset_id'] ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if (in_array($_SESSION['role'], ['encoder', 'admin'])): ?>
                                            <a href="index.php?page=assets&sub=edit&id=<?= $asset['asset_id'] ?>" class="px-2 py-1 text-yellow-600 border border-yellow-300 rounded hover:bg-yellow-50 text-xs"><i class="bi bi-pencil"></i></a>
                                        <?php endif; ?>
                                        <?php if ($asset['status'] === 'active' && in_array($_SESSION['role'], ['encoder', 'admin'])): ?>
                                            <?php if (empty($asset['active_custody_id'])): ?>
                                                <a href="index.php?page=custody&sub=add&asset_id=<?= $asset['asset_id'] ?>" class="px-2 py-1 text-blue-600 border border-blue-300 rounded hover:bg-blue-50 text-xs" title="Assign Custodian">
                                                    <i class="bi bi-person-plus"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="index.php?page=custody&sub=edit&asset_id=<?= $asset['asset_id'] ?>" class="px-2 py-1 text-blue-600 border border-blue-300 rounded hover:bg-blue-50 text-xs" title="Transfer Custodian">
                                                    <i class="bi bi-arrow-left-right"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if ($_SESSION['role'] === 'admin' && $asset['status'] === 'active'): ?>
                                            <button class="px-2 py-1 text-red-600 border border-red-300 rounded hover:bg-red-50 text-xs dispose-btn" 
                                                    data-id="<?= $asset['asset_id'] ?>"
                                                    onclick="openDisposeModal(<?= $asset['asset_id'] ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2">
                                        <?php 
                                            $vStatus = $asset['verification_status'] ?? 'pending';
                                            $vClass = match($vStatus) {
                                                'verified' => 'bg-green-100 text-green-800',
                                                'discrepancy' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        ?>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $vClass ?>"><?= ucfirst($vStatus) ?></span>
                                    </td>
                                    <td class="px-3 py-2 text-sm"><?= $asset['verified_at'] ? date('Y-m-d H:i', strtotime($asset['verified_at'])) : 'Never' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<!-- Asset Details Modal (Tailwind) – using style="display:none" to avoid conflict -->
<div id="assetDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center" style="display:none; z-index:1050;">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h5 class="text-lg font-semibold text-gray-800">Asset Details</h5>
            <button type="button" class="text-gray-400 hover:text-gray-600" onclick="document.getElementById('assetDetailsModal').style.display='none'">&times;</button>
        </div>
        <div class="p-6 overflow-y-auto flex-1" id="modalBody">
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                <p class="mt-2 text-gray-500">Loading asset details...</p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
            <button type="button" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400" onclick="document.getElementById('assetDetailsModal').style.display='none'">Close</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/dispose_modal.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalBody = document.getElementById('modalBody');

    document.querySelectorAll('.view-details').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const assetId = this.dataset.id;
            modalBody.innerHTML = `
                <div class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                    <p class="mt-2 text-gray-500">Loading asset details...</p>
                </div>
            `;
            document.getElementById('assetDetailsModal').style.display = 'flex';

            fetch(`index.php?page=assets&sub=details&id=${assetId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        modalBody.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded">${data.error}</div>`;
                        return;
                    }
                    modalBody.innerHTML = buildDetailsHTML(data);
                })
                .catch(error => {
                    modalBody.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded">Failed to load asset details: ${error.message}</div>`;
                });
        });
    });

    function buildDetailsHTML(data) {
        const asset = data.asset;
        const custody = data.custody || [];
        const audit = data.audit || [];
        const transfers = data.transfers || [];
        const qrImg = `index.php?page=assets&sub=qr&id=${asset.asset_id}`;

        let html = `
            <h6 class="font-semibold text-gray-800 border-b pb-2 mb-3">Asset Information</h6>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm mb-4">
                <div><strong>Asset Code:</strong> ${escapeHtml(asset.asset_code)}</div>
                <div><strong>QR Code:</strong> ${escapeHtml(asset.qr_code_ref)}</div>
                <div class="col-span-2"><strong>Asset Name:</strong> ${escapeHtml(asset.asset_name)}</div>
                <div><strong>Brand:</strong> ${escapeHtml(asset.brand || 'N/A')}</div>
                <div><strong>Model:</strong> ${escapeHtml(asset.model || 'N/A')}</div>
                <div><strong>Serial #:</strong> ${escapeHtml(asset.serial_number || 'N/A')}</div>
                <div><strong>Acquisition Cost:</strong> ${asset.acquisition_cost ? '₱' + Number(asset.acquisition_cost).toFixed(2) : 'N/A'}</div>
                <div><strong>Acquisition Date:</strong> ${asset.acquisition_date || 'N/A'}</div>
                <div><strong>Account:</strong> ${escapeHtml(asset.account_code + ' - ' + asset.account_name)}</div>
                <div><strong>Status:</strong> <span class="px-2 py-0.5 rounded-full text-xs font-medium ${asset.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">${asset.status}</span></div>
                <div><strong>Condition:</strong> <span class="px-2 py-0.5 rounded-full text-xs font-medium ${asset.condition === 'good' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">${asset.condition}</span></div>
                <div><strong>Created:</strong> ${asset.created_at || 'N/A'}</div>
                <div class="col-span-3"><strong>Remarks:</strong> ${escapeHtml(asset.remarks || 'N/A')}</div>
            </div>
            <div class="text-center mb-4">
                <img src="${qrImg}" alt="QR Code" class="inline-block max-w-[150px] border border-gray-200 p-2 rounded">
                <p class="text-xs text-gray-500 mt-1">QR Code: ${escapeHtml(asset.qr_code_ref)}</p>
            </div>
        `;

        html += `<h6 class="font-semibold text-gray-800 border-b pb-2 mt-4">Custody History</h6>`;
        if (custody.length === 0) {
            html += `<p class="text-gray-500 text-sm">No custody records found.</p>`;
        } else {
            html += `<div class="overflow-x-auto"><table class="w-full text-sm border border-gray-200 mb-3">
                <thead class="bg-gray-100"><tr><th class="px-2 py-1 border">From</th><th class="px-2 py-1 border">To</th><th class="px-2 py-1 border">Custodian</th><th class="px-2 py-1 border">Office</th><th class="px-2 py-1 border">Status</th><th class="px-2 py-1 border">Document</th></tr></thead><tbody>`;
            custody.forEach(c => {
                html += `<tr>
                    <td class="px-2 py-1 border">${c.effectivity_date || 'N/A'}</td>
                    <td class="px-2 py-1 border">${c.end_date || 'Current'}</td>
                    <td class="px-2 py-1 border">${escapeHtml(c.custodian_name)} <br><span class="text-xs text-gray-500">${escapeHtml(c.position || '')}</span></td>
                    <td class="px-2 py-1 border">${escapeHtml(c.office_name)}</td>
                    <td class="px-2 py-1 border"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${c.custody_status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">${c.custody_status}</span></td>
                    <td class="px-2 py-1 border">${escapeHtml(c.accountability_document || '')} ${c.accountability_reference ? '<br><span class="text-xs text-gray-500">Ref: ' + escapeHtml(c.accountability_reference) + '</span>' : ''}</td>
                </tr>`;
            });
            html += `</tbody></table></div>`;
        }

        html += `<h6 class="font-semibold text-gray-800 border-b pb-2 mt-4">Transfer History</h6>`;
        if (transfers.length === 0) {
            html += `<p class="text-gray-500 text-sm">No transfer records found.</p>`;
        } else {
            html += `<div class="overflow-x-auto"><table class="w-full text-sm border border-gray-200">
                <thead class="bg-gray-100"><tr><th class="px-2 py-1 border">Transfer #</th><th class="px-2 py-1 border">Date</th><th class="px-2 py-1 border">From</th><th class="px-2 py-1 border">To</th><th class="px-2 py-1 border">Status</th><th class="px-2 py-1 border">Remarks</th></tr></thead><tbody>`;
            transfers.forEach(t => {
                html += `<tr>
                    <td class="px-2 py-1 border">${escapeHtml(t.transfer_number)}</td>
                    <td class="px-2 py-1 border">${escapeHtml(t.transfer_date)}</td>
                    <td class="px-2 py-1 border">${escapeHtml(t.from_custodian)} (${escapeHtml(t.from_office || '')})</td>
                    <td class="px-2 py-1 border">${escapeHtml(t.to_custodian)} (${escapeHtml(t.to_office || '')})</td>
                    <td class="px-2 py-1 border"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${t.status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">${escapeHtml(t.status)}</span></td>
                    <td class="px-2 py-1 border">${escapeHtml(t.remarks || '')}</td>
                </tr>`;
            });
            html += `</tbody></table></div>`;
        }

        html += `<h6 class="font-semibold text-gray-800 border-b pb-2 mt-4">Audit Trail</h6>`;
        if (audit.length === 0) {
            html += `<p class="text-gray-500 text-sm">No audit records found.</p>`;
        } else {
            html += `<div class="overflow-x-auto"><table class="w-full text-sm border border-gray-200">
                <thead class="bg-gray-100"><tr><th class="px-2 py-1 border">Date</th><th class="px-2 py-1 border">User</th><th class="px-2 py-1 border">Action</th><th class="px-2 py-1 border">Module</th><th class="px-2 py-1 border">Changes</th></tr></thead><tbody>`;
            audit.forEach(a => {
                html += `<tr>
                    <td class="px-2 py-1 border">${a.performed_at}</td>
                    <td class="px-2 py-1 border">${escapeHtml(a.performed_by)}</td>
                    <td class="px-2 py-1 border">${escapeHtml(a.action_type)}</td>
                    <td class="px-2 py-1 border">${escapeHtml(a.module)}</td>
                    <td class="px-2 py-1 border">
                        <button class="px-2 py-0.5 text-xs border border-gray-300 rounded hover:bg-gray-100" onclick="alert('Previous: ${escapeHtml(a.previous_values || '')}\\nNew: ${escapeHtml(a.new_values || '')}')">View Changes</button>
                    </td>
                </tr>`;
            });
            html += `</tbody></table></div>`;
        }

        return html;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    document.getElementById('assetDetailsModal').addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
});

function toggleAllCheckboxes() {
    const checkboxes = document.querySelectorAll('.asset-checkbox');
    const selectAll = document.getElementById('selectAll');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('.asset-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = checked + ' selected';
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.asset-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
    updateSelectedCount();
});
</script>