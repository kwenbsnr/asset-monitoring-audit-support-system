<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center flex-wrap py-3">
        <h4 class="mb-0 fw-bold text-success"><i class="bi bi-box-seam me-2"></i><?= $pageTitle ?? 'Assets' ?></h4>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#advancedSearch" aria-expanded="false">
                <i class="bi bi-sliders2"></i> Advanced
            </button>
            <form method="GET" action="index.php" class="d-flex gap-2" id="basicSearchForm">
                <input type="hidden" name="page" value="assets">
                <input type="hidden" name="sub" value="<?= isset($_GET['account_id']) ? 'browse' : 'list_all' ?>">
                <?php if (isset($_GET['account_id'])): ?>
                    <input type="hidden" name="account_id" value="<?= (int)$_GET['account_id'] ?>">
                <?php endif; ?>
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm" name="search" placeholder="Search..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button class="btn btn-outline-success btn-sm" type="submit"><i class="bi bi-search"></i></button>
                    <?php if (!empty($_GET['search'])): ?>
                        <a href="?page=assets&sub=<?= isset($_GET['account_id']) ? 'browse&account_id=' . (int)$_GET['account_id'] : 'list_all' ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i></a>
                    <?php endif; ?>
                </div>
            </form>
            <?php if (isset($account) && $account): ?>
                <a href="index.php?page=assets&sub=browse" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Accounts
                </a>
            <?php endif; ?>
            <a href="index.php?page=assets&sub=add" class="btn btn-success btn-sm"><i class="bi bi-plus-circle"></i> Add</a>
        </div>
    </div>

    <div class="card-body">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'success' ?> alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['flash']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <?php if (!empty($_GET['search'])): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                Showing results for: <strong>"<?= htmlspecialchars($_GET['search']) ?>"</strong>
                <?php if (!empty($assets)): ?>
                    (<?= count($assets) ?> found)
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=assets&sub=bulk_qr" id="bulkQrForm">
            <div class="mb-2 d-flex justify-content-between align-items-center">
                <div>
                    <button type="submit" class="btn btn-outline-primary btn-sm" onclick="return confirm('Print QR codes for selected assets?')">
                        <i class="bi bi-printer"></i> Print Selected QR
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleAllCheckboxes()">Select All</button>
                </div>
                <span class="text-muted small" id="selectedCount">0 selected</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assets)): ?>
                            <tr>
                                <td colspan="9" class="text-center">
                                    <?php if (!empty($_GET['search'])): ?>
                                        No assets found matching "<strong><?= htmlspecialchars($_GET['search']) ?></strong>".
                                    <?php else: ?>
                                        No assets found in this account.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($assets as $asset): ?>
                                <tr>
                                    <td><input type="checkbox" name="asset_ids[]" value="<?= $asset['asset_id'] ?>" class="asset-checkbox"></td>
                                    <td><strong><?= htmlspecialchars($asset['asset_code']) ?></strong></td>
                                    <td><?= htmlspecialchars($asset['asset_name']) ?></td>
                                    <td><?= htmlspecialchars($asset['brand'] ?? '') ?> <?= htmlspecialchars($asset['model'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($asset['serial_number'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($asset['account_code'] ?? '') ?></td>
                                    <td>
                                        <?php if (!empty($asset['custodians'])): ?>
                                            <?= htmlspecialchars($asset['custodians']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $statusClass = match($asset['status']) {
                                                'active' => 'success',
                                                'pending_disposal' => 'warning',
                                                'disposed' => 'secondary',
                                                'missing' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>"><?= $asset['status'] ?></span>
                                    </td>
                                    <td>
                                        <!-- Eye button – always visible -->
                                        <button type="button" class="btn btn-sm btn-info view-details" 
                                                data-id="<?= $asset['asset_id'] ?>" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#assetDetailsModal">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <!-- Edit – only for encoder and admin -->
                                        <?php if (in_array($_SESSION['role'], ['encoder', 'admin'])): ?>
                                            <a href="index.php?page=assets&sub=edit&id=<?= $asset['asset_id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                        <?php endif; ?>

                                        <!-- Custody actions – only for encoder and admin -->
                                        <?php if ($asset['status'] === 'active' && in_array($_SESSION['role'], ['encoder', 'admin'])): ?>
                                            <?php if (empty($asset['active_custody_id'])): ?>
                                                <a href="index.php?page=custody&sub=add&asset_id=<?= $asset['asset_id'] ?>" class="btn btn-sm btn-primary" title="Assign Custodian">
                                                    <i class="bi bi-person-plus"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="index.php?page=custody&sub=edit&asset_id=<?= $asset['asset_id'] ?>" class="btn btn-sm btn-warning" title="Transfer Custodian">
                                                    <i class="bi bi-arrow-left-right"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <!-- Dispose – only for admin (not for asset_inspector) -->
                                        <?php if ($_SESSION['role'] === 'admin' && $asset['status'] === 'active'): ?>
                                            <button class="btn btn-sm btn-danger dispose-btn" 
                                                    data-id="<?= $asset['asset_id'] ?>" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#disposeModal"
                                                    title="Dispose Asset">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Asset Details -->
<div class="modal fade" id="assetDetailsModal" tabindex="-1" aria-labelledby="assetDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assetDetailsModalLabel">Asset Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Loading asset details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/dispose_modal.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.dispose-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('disposeAssetId').value = this.dataset.id;
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalBody = document.getElementById('modalBody');

    document.querySelectorAll('.view-details').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const assetId = this.dataset.id;
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Loading asset details...</p>
                </div>
            `;

            fetch(`index.php?page=assets&sub=details&id=${assetId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        modalBody.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                        return;
                    }
                    modalBody.innerHTML = buildDetailsHTML(data);
                })
                .catch(error => {
                    modalBody.innerHTML = `<div class="alert alert-danger">Failed to load asset details: ${error.message}</div>`;
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
            <h6 class="border-bottom pb-2">Asset Information</h6>
            <div class="row mb-3">
                <div class="col-md-6"><strong>Asset Code:</strong> ${escapeHtml(asset.asset_code)}</div>
                <div class="col-md-6"><strong>QR Code:</strong> ${escapeHtml(asset.qr_code_ref)}</div>
                <div class="col-md-12"><strong>Asset Name:</strong> ${escapeHtml(asset.asset_name)}</div>
                <div class="col-md-4"><strong>Brand:</strong> ${escapeHtml(asset.brand || 'N/A')}</div>
                <div class="col-md-4"><strong>Model:</strong> ${escapeHtml(asset.model || 'N/A')}</div>
                <div class="col-md-4"><strong>Serial #:</strong> ${escapeHtml(asset.serial_number || 'N/A')}</div>
                <div class="col-md-6"><strong>Acquisition Cost:</strong> ${asset.acquisition_cost ? '₱' + Number(asset.acquisition_cost).toFixed(2) : 'N/A'}</div>
                <div class="col-md-6"><strong>Acquisition Date:</strong> ${asset.acquisition_date || 'N/A'}</div>
                <div class="col-md-6"><strong>Account:</strong> ${escapeHtml(asset.account_code + ' - ' + asset.account_name)}</div>
                <div class="col-md-4"><strong>Status:</strong> <span class="badge bg-${asset.status === 'active' ? 'success' : 'secondary'}">${asset.status}</span></div>
                <div class="col-md-4"><strong>Condition:</strong> <span class="badge bg-${asset.condition === 'good' ? 'success' : 'warning'}">${asset.condition}</span></div>
                <div class="col-md-4"><strong>Created:</strong> ${asset.created_at || 'N/A'}</div>
                <div class="col-md-12"><strong>Remarks:</strong> ${escapeHtml(asset.remarks || 'N/A')}</div>
            </div>
        `;

        html += `
            <div class="text-center mb-3">
                <img src="${qrImg}" alt="QR Code" style="max-width:150px; border:1px solid #ddd; padding:5px; border-radius:8px;">
                <p class="small text-muted mt-1">QR Code: ${escapeHtml(asset.qr_code_ref)}</p>
            </div>
        `;

        // Custody history
        html += `<h6 class="border-bottom pb-2 mt-3">Custody History</h6>`;
        if (custody.length === 0) {
            html += `<p class="text-muted">No custody records found.</p>`;
        } else {
            html += `<div class="table-responsive"><table class="table table-sm table-bordered">
                <thead><tr><th>From</th><th>To</th><th>Custodian</th><th>Office</th><th>Status</th><th>Document</th></tr></thead><tbody>`;
            custody.forEach(c => {
                html += `<tr>
                    <td>${c.effectivity_date || 'N/A'}</td>
                    <td>${c.end_date || 'Current'}</td>
                    <td>${escapeHtml(c.custodian_name)} <br><small>${escapeHtml(c.position || '')}</small></td>
                    <td>${escapeHtml(c.office_name)}</td>
                    <td><span class="badge bg-${c.custody_status === 'active' ? 'success' : 'secondary'}">${c.custody_status}</span></td>
                    <td>${escapeHtml(c.accountability_document || '')} ${c.accountability_reference ? '<br><small>Ref: ' + escapeHtml(c.accountability_reference) + '</small>' : ''}</td>
                </tr>`;
            });
            html += `</tbody></table></div>`;
        }

        // Transfer History
        html += `<h6 class="border-bottom pb-2 mt-3">Transfer History</h6>`;
        if (transfers.length === 0) {
            html += `<p class="text-muted">No transfer records found.</p>`;
        } else {
            html += `<div class="table-responsive"><table class="table table-sm table-bordered">
                <thead><tr><th>Transfer #</th><th>Date</th><th>From</th><th>To</th><th>Status</th><th>Remarks</th></tr></thead><tbody>`;
            transfers.forEach(t => {
                html += `<tr>
                    <td>${escapeHtml(t.transfer_number)}</td>
                    <td>${escapeHtml(t.transfer_date)}</td>
                    <td>${escapeHtml(t.from_custodian)} (${escapeHtml(t.from_office || '')})</td>
                    <td>${escapeHtml(t.to_custodian)} (${escapeHtml(t.to_office || '')})</td>
                    <td><span class="badge bg-${t.status === 'approved' ? 'success' : 'warning'}">${escapeHtml(t.status)}</span></td>
                    <td>${escapeHtml(t.remarks || '')}</td>
                </tr>`;
            });
            html += `</tbody></table></div>`;
        }

        // Audit trail
        html += `<h6 class="border-bottom pb-2 mt-3">Audit Trail</h6>`;
        if (audit.length === 0) {
            html += `<p class="text-muted">No audit records found.</p>`;
        } else {
            html += `<div class="table-responsive"><table class="table table-sm table-bordered">
                <thead><tr><th>Date</th><th>User</th><th>Action</th><th>Module</th><th>Changes</th></tr></thead><tbody>`;
            audit.forEach(a => {
                html += `<tr>
                    <td>${a.performed_at}</td>
                    <td>${escapeHtml(a.performed_by)}</td>
                    <td>${escapeHtml(a.action_type)}</td>
                    <td>${escapeHtml(a.module)}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary" onclick="alert('Previous: ${escapeHtml(a.previous_values || '')}\nNew: ${escapeHtml(a.new_values || '')}')">View Changes</button>
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
</script>

<script>
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