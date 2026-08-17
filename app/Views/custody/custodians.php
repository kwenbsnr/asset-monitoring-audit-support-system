<?php if (!defined('APP_START')) exit;
$flashType = $_SESSION['flash_type'] ?? 'success';
$alertClass = $flashType === 'success' ? 'alert-app-success' : 'alert-app-danger';
?>
<div class="card-panel">
    <div class="card-panel-header">
        <div class="flex items-center gap-3">
            <span class="page-icon"><i class="bi bi-people"></i></span>
            <span class="page-title"><?= htmlspecialchars($pageTitle) ?></span>
        </div>
        <a href="index.php?page=custody" class="btn-app btn-app-outline"><i class="bi bi-arrow-left"></i> Back to Offices</a>
    </div>
    <div class="card-panel-body">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert-app <?= $alertClass ?>">
                <span><?= htmlspecialchars($_SESSION['flash']) ?></span>
                <button type="button" class="alert-app-close" onclick="this.closest('.alert-app').remove()">&times;</button>
            </div>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
        <?php endif; ?>
        <div class="table-app-wrap">
            <table class="table-app">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Position</th>
                        <th>Assets Under Custody</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($custodians)): ?>
                        <tr><td colspan="5"><div class="table-empty">No custodians found in this office.</div></td></tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($custodians as $c): ?>
                            <tr>
                                <td class="text-gray-500"><?= $i++ ?></td>
                                <td class="font-medium text-gray-800"><?= htmlspecialchars($c['full_name']) ?></td>
                                <td><?= htmlspecialchars($c['position']) ?></td>
                                <td><span class="badge-app badge-app-success"><?= $c['asset_count'] ?></span></td>
                                <td class="text-center">
                                    <button type="button"
                                            class="view-custody-assets-btn btn-app btn-app-sm btn-app-outline-primary"
                                            data-id="<?= $c['personnel_id'] ?>"
                                            data-name="<?= htmlspecialchars($c['full_name'], ENT_QUOTES) ?>">
                                        <i class="bi bi-eye"></i> View Assets
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Custodian Assets Modal (standardized modal system) — mirrors the
     read-only "View Assets" modal on Views/assets/office_custodians.php,
     using the fields CustodyModel::getAssetsByCustodian() actually
     returns (asset_code, description, brand/model, serial_number,
     status, condition, effectivity_date, property_number), same as the
     full-page Views/custody/assets.php table. -->
<div id="custodyCustodianAssetsModal" class="modal-overlay">
    <div class="modal-panel modal-panel-lg" role="dialog" aria-modal="true" aria-labelledby="custodyCustodianAssetsModalTitle">
        <div class="modal-header">
            <h5 id="custodyCustodianAssetsModalTitle">Assets</h5>
            <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body" id="custodyCustodianAssetsModalBody">
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                <p class="mt-2 text-gray-500">Loading assets...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-app btn-app-outline" data-modal-close>Close</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalBody = document.getElementById('custodyCustodianAssetsModalBody');
    const modalTitle = document.getElementById('custodyCustodianAssetsModalTitle');

    document.querySelectorAll('.view-custody-assets-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const custodianId = this.dataset.id;
            const custodianName = this.dataset.name;

            modalTitle.textContent = 'Assets of ' + custodianName;
            modalBody.innerHTML = `
                <div class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                    <p class="mt-2 text-gray-500">Loading assets...</p>
                </div>
            `;
            NiaModal.open('custodyCustodianAssetsModal');

            fetch(`index.php?page=custody&sub=custodian_assets_json&id=${custodianId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        modalBody.innerHTML = `<div class="alert-app alert-app-danger">${escapeHtml(data.error)}</div>`;
                        return;
                    }
                    modalBody.innerHTML = buildAssetsHTML(data);
                })
                .catch(error => {
                    modalBody.innerHTML = `<div class="alert-app alert-app-danger">Failed to load assets: ${escapeHtml(error.message)}</div>`;
                });
        });
    });

    function buildAssetsHTML(assets) {
        if (!assets || assets.length === 0) {
            return '<div class="empty-state">No assets under this custodian.</div>';
        }
        let html = `
            <div class="table-app-wrap">
                <table class="table-app">
                    <thead>
                        <tr>
                            <th>Asset Code</th>
                            <th>Description</th>
                            <th>Brand/Model</th>
                            <th>Serial #</th>
                            <th>Status</th>
                            <th>Condition</th>
                            <th>Effectivity</th>
                            <th>Property No.</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        assets.forEach(a => {
            const statusClass = a.status === 'active' ? 'badge-app-success' : 'badge-app-neutral';
            const conditionClass = a.condition === 'good' ? 'badge-app-success' : 'badge-app-warning';
            html += `
                <tr>
                    <td class="font-medium text-gray-800">${escapeHtml(a.asset_code)}</td>
                    <td>${escapeHtml(a.description || '')}</td>
                    <td>${escapeHtml(a.brand || '')} ${escapeHtml(a.model || '')}</td>
                    <td>${escapeHtml(a.serial_number || '')}</td>
                    <td><span class="badge-app ${statusClass}">${escapeHtml(a.status)}</span></td>
                    <td><span class="badge-app ${conditionClass}">${escapeHtml(a.condition)}</span></td>
                    <td>${escapeHtml(a.effectivity_date || '')}</td>
                    <td>${escapeHtml(a.property_number || '')}</td>
                </tr>
            `;
        });
        html += '</tbody></table></div>';
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