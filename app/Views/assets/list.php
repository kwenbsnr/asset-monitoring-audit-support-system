<?php if (!defined('APP_START')) exit; ?>
<?php 
$flashType = $_SESSION['flash_type'] ?? 'success';
$alertClass = $flashType === 'success' ? 'alert-app-success' : 'alert-app-danger';
?>
<div class="card-panel">
    <div class="card-panel-header">
        <div class="flex items-center gap-3">
            <span class="page-icon"><i class="bi bi-box-seam"></i></span>
            <span class="page-title"><?= $pageTitle ?? 'Assets' ?></span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" action="index.php" class="flex gap-1">
                <input type="hidden" name="page" value="assets">
                <input type="hidden" name="sub" value="<?= isset($_GET['account_id']) ? 'browse' : 'list_all' ?>">
                <?php if (isset($_GET['account_id'])): ?>
                    <input type="hidden" name="account_id" value="<?= (int)$_GET['account_id'] ?>">
                <?php endif; ?>
                <div class="flex">
                    <input type="text" class="border border-gray-300 rounded-l-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="search" placeholder="Search..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button class="btn-app btn-app-primary btn-app-join-r" type="submit"><i class="bi bi-search"></i></button>
                    <?php if (!empty($_GET['search'])): ?>
                        <a href="?page=assets&sub=<?= isset($_GET['account_id']) ? 'browse&account_id=' . (int)$_GET['account_id'] : 'list_all' ?>" class="btn-app btn-app-outline ml-1"><i class="bi bi-x-circle"></i></a>
                    <?php endif; ?>
                </div>
            </form>
            <?php if (isset($account) && $account): ?>
                <a href="index.php?page=assets&sub=browse" class="btn-app btn-app-outline">
                    <i class="bi bi-arrow-left"></i> Back to Accounts
                </a>
            <?php endif; ?>
            <a href="index.php?page=assets&sub=add" class="btn-app btn-app-primary"><i class="bi bi-plus-circle"></i> Add</a>
        </div>
    </div>

    <div class="card-panel-body">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert-app <?= $alertClass ?>">
                <span><?= htmlspecialchars($_SESSION['flash']) ?></span>
                <button type="button" class="alert-app-close" onclick="this.closest('.alert-app').remove()">&times;</button>
            </div>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <?php if (!empty($_GET['search'])): ?>
            <div class="alert-app alert-app-info">
                <span>
                    <i class="bi bi-info-circle"></i>
                    Showing results for: <strong>"<?= htmlspecialchars($_GET['search']) ?>"</strong>
                    <?php if (!empty($assets)): ?>
                        (<?= count($assets) ?> found)
                    <?php endif; ?>
                </span>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=assets&sub=bulk_qr" id="bulkQrForm">
            <div class="flex flex-wrap items-center justify-between mb-3">
                <div class="flex gap-2">
                    <button type="submit" class="btn-app btn-app-outline-primary" onclick="return confirm('Print QR codes for selected assets?')">
                        <i class="bi bi-printer"></i> Print Selected QR
                    </button>
                    <button type="button" class="btn-app btn-app-outline" onclick="toggleAllCheckboxes()">Select All</button>
                </div>
                <span class="text-sm text-gray-500" id="selectedCount">0 selected</span>
            </div>

            <div class="table-app-wrap">
                <table class="table-app">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll" onclick="toggleAllCheckboxes()"></th>
                            <th>Asset Code</th>
                            <th>Asset Name</th>
                            <th>Brand / Model</th>
                            <th>Serial #</th>
                            <th>Account</th>
                            <th>Custodian</th>
                            <th>Status</th>
                            <th>Actions</th>
                            <th>Verification</th>
                            <th>Last Verified</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assets)): ?>
                            <tr>
                                <td colspan="11">
                                    <div class="table-empty">
                                    <?php if (!empty($_GET['search'])): ?>
                                        No assets found matching "<strong><?= htmlspecialchars($_GET['search']) ?></strong>".
                                    <?php else: ?>
                                        No assets found in this account.
                                    <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($assets as $asset): ?>
                                <tr>
                                    <td><input type="checkbox" name="asset_ids[]" value="<?= $asset['asset_id'] ?>" class="asset-checkbox"></td>
                                    <td class="font-medium text-gray-800"><?= htmlspecialchars($asset['asset_code']) ?></td>
                                    <td><?= htmlspecialchars($asset['asset_name']) ?></td>
                                    <td><?= htmlspecialchars($asset['brand'] ?? '') ?> <?= htmlspecialchars($asset['model'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($asset['serial_number'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($asset['account_code'] ?? '') ?></td>
                                    <td>
                                        <?php if (!empty($asset['custodians'])): ?>
                                            <?= htmlspecialchars($asset['custodians']) ?>
                                        <?php else: ?>
                                            <span class="text-gray-400">Not assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                            $statusClass = match($asset['status']) {
                                                'active' => 'badge-app-success',
                                                'pending_disposal' => 'badge-app-warning',
                                                'disposed' => 'badge-app-neutral',
                                                'missing' => 'badge-app-danger',
                                                default => 'badge-app-neutral'
                                            };
                                        ?>
                                        <span class="badge-app <?= $statusClass ?>"><?= $asset['status'] ?></span>
                                    </td>
                                    <td class="whitespace-nowrap">
                                        <button type="button" class="btn-app btn-app-sm btn-app-outline-primary view-details"
                                                data-id="<?= $asset['asset_id'] ?>" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if (in_array($_SESSION['role'], ['encoder', 'admin'])): ?>
                                            <a href="index.php?page=assets&sub=edit&id=<?= $asset['asset_id'] ?>" class="btn-app btn-app-sm btn-app-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <?php endif; ?>
                                        <?php if ($asset['status'] === 'active' && in_array($_SESSION['role'], ['encoder', 'admin'])): ?>
                                            <?php if (empty($asset['active_custody_id'])): ?>
                                                <a href="index.php?page=custody&sub=add&asset_id=<?= $asset['asset_id'] ?>" class="btn-app btn-app-sm btn-app-outline-primary" title="Assign Custodian">
                                                    <i class="bi bi-person-plus"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="index.php?page=custody&sub=edit&asset_id=<?= $asset['asset_id'] ?>" class="btn-app btn-app-sm btn-app-outline-primary" title="Transfer Custodian">
                                                    <i class="bi bi-arrow-left-right"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if ($_SESSION['role'] === 'admin' && $asset['status'] === 'active'): ?>
                                            <button class="btn-app btn-app-sm btn-app-outline-danger dispose-btn"
                                                    data-id="<?= $asset['asset_id'] ?>"
                                                    onclick="openDisposeModal(<?= $asset['asset_id'] ?>)" title="Dispose">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                            $vStatus = $asset['verification_status'] ?? 'pending';
                                            $vClass = match($vStatus) {
                                                'verified' => 'badge-app-success',
                                                'discrepancy' => 'badge-app-danger',
                                                default => 'badge-app-neutral'
                                            };
                                        ?>
                                        <span class="badge-app <?= $vClass ?>"><?= ucfirst($vStatus) ?></span>
                                    </td>
                                    <td class="text-sm"><?= $asset['verified_at'] ? date('Y-m-d H:i', strtotime($asset['verified_at'])) : 'Never' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<!-- Asset Details Modal (standardized modal system) -->
<div id="assetDetailsModal" class="modal-overlay">
    <div class="modal-panel modal-panel-xl" role="dialog" aria-modal="true" aria-labelledby="assetDetailsModalTitle">
        <div class="modal-header">
            <h5 id="assetDetailsModalTitle">Asset Details</h5>
            <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                <p class="mt-2 text-gray-500">Loading asset details...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-app btn-app-outline" data-modal-close>Close</button>
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
            NiaModal.open('assetDetailsModal');

            fetch(`index.php?page=assets&sub=details&id=${assetId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        modalBody.innerHTML = `<div class="alert-app alert-app-danger">${data.error}</div>`;
                        return;
                    }
                    modalBody.innerHTML = buildDetailsHTML(data);
                })
                .catch(error => {
                    modalBody.innerHTML = `<div class="alert-app alert-app-danger">Failed to load asset details: ${error.message}</div>`;
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
                <div><strong>Status:</strong> <span class="badge-app ${asset.status === 'active' ? 'badge-app-success' : 'badge-app-neutral'}">${asset.status}</span></div>
                <div><strong>Condition:</strong> <span class="badge-app ${asset.condition === 'good' ? 'badge-app-success' : 'badge-app-warning'}">${asset.condition}</span></div>
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
                    <td class="px-2 py-1 border"><span class="badge-app ${c.custody_status === 'active' ? 'badge-app-success' : 'badge-app-neutral'}">${c.custody_status}</span></td>
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
                    <td class="px-2 py-1 border"><span class="badge-app ${t.status === 'approved' ? 'badge-app-success' : 'badge-app-warning'}">${escapeHtml(t.status)}</span></td>
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