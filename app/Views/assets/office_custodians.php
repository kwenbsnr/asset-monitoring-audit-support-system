<?php if (!defined('APP_START')) exit; ?>
<?php
$flashType = $_SESSION['flash_type'] ?? 'success';
$alertClass = $flashType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
        <h4 class="text-xl font-bold text-green-700 flex items-center gap-2">
            <i class="bi bi-people"></i> <?= $pageTitle ?? 'Custodians' ?>
        </h4>
        <a href="index.php?page=assets&sub=by_office" class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50">
            <i class="bi bi-arrow-left"></i> Back to Offices
        </a>
    </div>

    <div class="p-6">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="mb-4 p-3 rounded border <?= $alertClass ?> flex justify-between items-center">
                <span><?= htmlspecialchars($_SESSION['flash']) ?></span>
                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="this.parentElement.remove()">&times;</button>
            </div>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php if (empty($custodians)): ?>
                <div class="col-span-full">
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 p-4 rounded">No custodians found in this office.</div>
                </div>
            <?php else: ?>
                <?php foreach ($custodians as $c): ?>
                    <div class="relative">
                        <div class="bg-white border border-gray-200 rounded-lg hover:border-green-600 hover:shadow-md hover:-translate-y-1 transition-all duration-200 p-4 text-center">
                            <i class="bi bi-person-circle text-4xl text-gray-400"></i>
                            <h6 class="font-semibold text-gray-800 text-sm mt-2"><?= htmlspecialchars($c['full_name']) ?></h6>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars($c['position']) ?></div>
                            <div class="mt-2">
                                <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded"><?= $c['asset_count'] ?> Assets</span>
                            </div>
                            <button type="button"
                                    class="view-assets-btn mt-3 w-full text-sm border border-blue-600 text-blue-600 rounded py-1.5 hover:bg-blue-50 transition"
                                    data-custodian-id="<?= $c['personnel_id'] ?>"
                                    data-custodian-name="<?= htmlspecialchars($c['full_name'], ENT_QUOTES) ?>">
                                <i class="bi bi-eye"></i> View Assets
                            </button>
                        </div>

                        <!-- Assets panel (toggled by JS, replaces Bootstrap popover) -->
                        <div class="asset-panel hidden absolute z-20 left-0 right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg p-3 text-left max-h-[300px] overflow-y-auto">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-semibold text-gray-800">Assets</span>
                                <button type="button" class="close-panel-btn text-gray-400 hover:text-gray-600">&times;</button>
                            </div>
                            <div class="panel-body text-sm text-gray-600">Loading assets...</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function closeAllPanels(except) {
        document.querySelectorAll('.asset-panel').forEach(p => {
            if (p !== except) p.classList.add('hidden');
        });
    }

    document.querySelectorAll('.view-assets-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const wrapper = this.closest('.relative');
            const panel = wrapper.querySelector('.asset-panel');
            const alreadyOpen = !panel.classList.contains('hidden');
            closeAllPanels();
            if (alreadyOpen) return;

            panel.classList.remove('hidden');
            const body = panel.querySelector('.panel-body');

            if (body.dataset.loaded === '1') return;

            const custodianId = this.dataset.custodianId;
            fetch(`index.php?page=assets&sub=custodian_assets_json&id=${custodianId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        body.innerHTML = `<span class="text-red-600">${escapeHtml(data.error)}</span>`;
                        return;
                    }
                    if (data.length === 0) {
                        body.innerHTML = '<span class="text-gray-400">No assets assigned.</span>';
                        body.dataset.loaded = '1';
                        return;
                    }
                    let html = '<ul class="space-y-1">';
                    data.forEach(asset => {
                        const statusClass = asset.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                        html += `<li><strong>${escapeHtml(asset.asset_code)}</strong> - ${escapeHtml(asset.asset_name)} <span class="px-2 py-0.5 rounded-full text-xs font-medium ${statusClass}">${escapeHtml(asset.status)}</span></li>`;
                    });
                    html += '</ul>';
                    body.innerHTML = html;
                    body.dataset.loaded = '1';
                })
                .catch(() => {
                    body.innerHTML = '<span class="text-red-600">Failed to load assets.</span>';
                });
        });
    });

    document.querySelectorAll('.close-panel-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            this.closest('.asset-panel').classList.add('hidden');
        });
    });

    document.addEventListener('click', function() {
        closeAllPanels();
    });

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>