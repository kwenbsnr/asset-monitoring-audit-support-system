<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 fw-bold text-success"><i class="bi bi-people me-2"></i><?= $pageTitle ?? 'Custodians' ?></h4>
        <a href="index.php?page=assets&sub=by_office" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Offices
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'success' ?> alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['flash']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <div class="row g-3">
            <?php if (empty($custodians)): ?>
                <div class="col-12"><div class="alert alert-info">No custodians found in this office.</div></div>
            <?php else: ?>
                <?php foreach ($custodians as $c): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="card h-100 custodian-card border-secondary-subtle">
                            <div class="card-body text-center">
                                <i class="bi bi-person-circle fs-1 text-secondary"></i>
                                <h6 class="card-title mt-2"><?= htmlspecialchars($c['full_name']) ?></h6>
                                <div class="small text-muted"><?= htmlspecialchars($c['position']) ?></div>
                                <div class="mt-2">
                                    <span class="badge bg-success"><?= $c['asset_count'] ?> Assets</span>
                                </div>
                                <!-- Hover popover trigger -->
                                <button class="btn btn-outline-primary btn-sm mt-3 view-assets-btn" 
                                        data-custodian-id="<?= $c['personnel_id'] ?>"
                                        data-custodian-name="<?= htmlspecialchars($c['full_name']) ?>"
                                        data-bs-toggle="popover" 
                                        data-bs-placement="top"
                                        data-bs-html="true"
                                        title="Assets of <?= htmlspecialchars($c['full_name']) ?>"
                                        data-bs-content="Loading assets...">
                                    <i class="bi bi-eye"></i> View Assets
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(el => new bootstrap.Popover(el, {
        trigger: 'hover focus',
        delay: { show: 500, hide: 100 },
        sanitize: false // allow HTML
    }));

    // Fetch assets when popover is shown (dynamic content)
    document.querySelectorAll('.view-assets-btn').forEach(btn => {
        btn.addEventListener('shown.bs.popover', function(e) {
            const popover = bootstrap.Popover.getInstance(this);
            const contentDiv = popover.tip.querySelector('.popover-body');
            if (contentDiv.textContent.trim() === 'Loading assets...') {
                const custodianId = this.dataset.custodianId;
                const custodianName = this.dataset.custodianName;
                fetch(`index.php?page=assets&sub=custodian_assets_json&id=${custodianId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            contentDiv.innerHTML = `<span class="text-danger">${data.error}</span>`;
                            return;
                        }
                        if (data.length === 0) {
                            contentDiv.innerHTML = '<span class="text-muted">No assets assigned.</span>';
                            return;
                        }
                        let html = '<ul class="list-unstyled mb-0">';
                        data.forEach(asset => {
                            html += `<li><strong>${asset.asset_code}</strong> - ${asset.asset_name} <span class="badge bg-${asset.status === 'active' ? 'success' : 'secondary'}">${asset.status}</span></li>`;
                        });
                        html += '</ul>';
                        contentDiv.innerHTML = html;
                    })
                    .catch(error => {
                        contentDiv.innerHTML = `<span class="text-danger">Failed to load assets.</span>`;
                    });
            }
        });
    });
});
</script>

<style>
.custodian-card {
    transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
    border-width: 1px !important;
    border-color: #dee2e6 !important;
}
.custodian-card:hover {
    border-color: #198754 !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    transform: translateY(-2px);
}
.popover {
    max-width: 400px;
}
.popover-body {
    max-height: 300px;
    overflow-y: auto;
}
</style>