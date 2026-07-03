<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h4 class="mb-0"><?= htmlspecialchars($pageTitle ?? 'Assets') ?></h4>
        <div class="d-flex gap-2">
            <!-- Search Form -->
            <form method="GET" action="index.php" class="d-flex gap-2">
                <input type="hidden" name="page" value="assets">
                <input type="hidden" name="sub" value="<?= isset($_GET['cat_id']) ? 'browse' : 'list_all' ?>">
                <?php if (isset($_GET['cat_id'])): ?>
                    <input type="hidden" name="cat_id" value="<?= (int)$_GET['cat_id'] ?>">
                <?php endif; ?>
                <div class="input-group">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Search by code, name, serial, custodian..." 
                           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button class="btn btn-outline-success" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                    <?php if (!empty($_GET['search'])): ?>
                        <a href="?page=assets&sub=<?= isset($_GET['cat_id']) ? 'browse&cat_id=' . (int)$_GET['cat_id'] : 'list_all' ?>" 
                           class="btn btn-outline-secondary" title="Clear search">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
            <a href="index.php?page=assets&sub=browse<?= isset($_GET['cat_id']) ? '&cat_id=' . (int)$_GET['cat_id'] : '' ?>" 
               class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <a href="index.php?page=assets&sub=add" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Add New
            </a>
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

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Asset Code</th>
                        <th>Description</th>
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
                            <td colspan="8" class="text-center">
                                <?php if (!empty($_GET['search'])): ?>
                                    No assets found matching "<strong><?= htmlspecialchars($_GET['search']) ?></strong>".
                                <?php else: ?>
                                    No assets found in this category.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($assets as $asset): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($asset['asset_code']) ?></strong></td>
                                <td><?= htmlspecialchars($asset['description']) ?></td>
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
                                <td><span class="badge bg-<?= $asset['status'] === 'active' ? 'success' : 'secondary' ?>"><?= $asset['status'] ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-info view-details" 
                                            data-id="<?= $asset['asset_id'] ?>" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#assetDetailsModal">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="index.php?page=assets&sub=edit&id=<?= $asset['asset_id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                    <a href="index.php?page=assets&sub=delete&id=<?= $asset['asset_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this asset?')"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal (same as before) -->
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

        let html = `
            <h6 class="border-bottom pb-2">Asset Information</h6>
            <div class="row mb-3">
                <div class="col-md-6"><strong>Asset Code:</strong> ${escapeHtml(asset.asset_code)}</div>
                <div class="col-md-6"><strong>QR Code:</strong> ${escapeHtml(asset.qr_code_ref)}</div>
                <div class="col-md-12"><strong>Description:</strong> ${escapeHtml(asset.description)}</div>
                <div class="col-md-4"><strong>Brand:</strong> ${escapeHtml(asset.brand || 'N/A')}</div>
                <div class="col-md-4"><strong>Model:</strong> ${escapeHtml(asset.model || 'N/A')}</div>
                <div class="col-md-4"><strong>Serial #:</strong> ${escapeHtml(asset.serial_number || 'N/A')}</div>
                <div class="col-md-6"><strong>Acquisition Cost:</strong> ${asset.acquisition_cost ? '₱' + Number(asset.acquisition_cost).toFixed(2) : 'N/A'}</div>
                <div class="col-md-6"><strong>Acquisition Date:</strong> ${asset.acquisition_date || 'N/A'}</div>
                <div class="col-md-6"><strong>Account:</strong> ${escapeHtml(asset.account_code + ' - ' + asset.account_name)}</div>
                <div class="col-md-6"><strong>Category:</strong> ${escapeHtml(asset.category_name)}</div>
                <div class="col-md-4"><strong>Status:</strong> <span class="badge bg-${asset.status === 'active' ? 'success' : 'secondary'}">${asset.status}</span></div>
                <div class="col-md-4"><strong>Condition:</strong> <span class="badge bg-${asset.condition === 'good' ? 'success' : 'warning'}">${asset.condition}</span></div>
                <div class="col-md-12"><strong>Remarks:</strong> ${escapeHtml(asset.remarks || 'N/A')}</div>
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