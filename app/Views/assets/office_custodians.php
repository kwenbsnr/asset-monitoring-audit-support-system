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

        <div class="overflow-x-auto">
            <table class="w-full text-sm border border-gray-200">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-2 border-b text-left font-medium">#</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Full Name</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Position</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Assets Under Custody</th>
                        <th class="px-4 py-2 border-b text-center font-medium">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($custodians)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-gray-500">No custodians found in this office.</td></tr>
                    <?php else: ?>
                        <?php $i = 1 + (($page - 1) * 20); foreach ($custodians as $c): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2"><?= $i++ ?></td>
                                <td class="px-4 py-2 font-medium text-gray-800"><?= htmlspecialchars($c['full_name']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($c['position']) ?></td>
                                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><?= $c['asset_count'] ?></span></td>
                                <td class="px-4 py-2 text-center">
                                    <button type="button"
                                            class="view-assets-btn px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
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

        <?php if (!empty($totalPages) && $totalPages > 1): ?>
            <nav class="mt-4 flex justify-center">
                <ul class="flex gap-1">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li>
                            <a class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-100 <?= $p == $page ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700' ?>" href="index.php?page=assets&sub=by_office&office_id=<?= $officeId ?>&page_num=<?= $p ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Custodian Assets Modal (Tailwind) -->
<div id="custodianAssetsModal" class="fixed inset-0 z-[1050] hidden items-center justify-center bg-gray-900/25 backdrop-blur-sm opacity-0 transition-opacity duration-200 ease-out">
    <div id="custodianAssetsModalPanel" class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4 max-h-[90vh] flex flex-col transform opacity-0 scale-95 translate-y-2 transition-all duration-200 ease-out">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h5 class="text-lg font-semibold text-gray-800" id="custodianAssetsModalTitle">Assets</h5>
            <button type="button" class="text-gray-400 hover:text-gray-600" data-close-custodian-assets-modal>&times;</button>
        </div>
        <div class="p-6 overflow-y-auto flex-1" id="custodianAssetsModalBody">
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                <p class="mt-2 text-gray-500">Loading assets...</p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
            <button type="button" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400" data-close-custodian-assets-modal>Close</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('custodianAssetsModal');
    const panel = document.getElementById('custodianAssetsModalPanel');
    const modalBody = document.getElementById('custodianAssetsModalBody');
    const modalTitle = document.getElementById('custodianAssetsModalTitle');
    const ANIM_MS = 200; // keep in sync with the duration-200 classes above

    function openCustodianAssetsModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        // Force a reflow so the browser registers the "closed" state
        // before we flip to "open" — otherwise the transition is skipped.
        void modal.offsetWidth;
        modal.classList.remove('opacity-0');
        modal.classList.add('opacity-100');
        panel.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
        panel.classList.add('opacity-100', 'scale-100', 'translate-y-0');
        document.addEventListener('keydown', onKeydown);
    }

    function closeCustodianAssetsModal() {
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        panel.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
        panel.classList.add('opacity-0', 'scale-95', 'translate-y-2');
        document.removeEventListener('keydown', onKeydown);
        setTimeout(function () {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, ANIM_MS);
    }

    function onKeydown(e) {
        if (e.key === 'Escape') closeCustodianAssetsModal();
    }

    document.querySelectorAll('[data-close-custodian-assets-modal]').forEach(function (btn) {
        btn.addEventListener('click', closeCustodianAssetsModal);
    });

    modal.addEventListener('click', function(e) {
        if (e.target === this) closeCustodianAssetsModal();
    });

    document.querySelectorAll('.view-assets-btn').forEach(btn => {
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
            openCustodianAssetsModal();

            fetch(`index.php?page=assets&sub=custodian_assets_json&id=${custodianId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        modalBody.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded">${escapeHtml(data.error)}</div>`;
                        return;
                    }
                    modalBody.innerHTML = buildAssetsHTML(data);
                })
                .catch(error => {
                    modalBody.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded">Failed to load assets: ${escapeHtml(error.message)}</div>`;
                });
        });
    });

    function buildAssetsHTML(assets) {
        if (!assets || assets.length === 0) {
            return '<p class="text-gray-500 text-sm">No assets under this custodian.</p>';
        }
        let html = `
            <div class="overflow-x-auto">
                <table class="w-full text-sm border border-gray-200">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-4 py-2 border-b text-left font-medium">Asset Code</th>
                            <th class="px-4 py-2 border-b text-left font-medium">Asset Name</th>
                            <th class="px-4 py-2 border-b text-left font-medium">Account</th>
                            <th class="px-4 py-2 border-b text-left font-medium">Status</th>
                            <th class="px-4 py-2 border-b text-left font-medium">Condition</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        assets.forEach(a => {
            const statusClass = a.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
            const conditionClass = a.condition === 'good' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
            html += `
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2 font-medium text-gray-800">${escapeHtml(a.asset_code)}</td>
                    <td class="px-4 py-2">${escapeHtml(a.asset_name || '')}</td>
                    <td class="px-4 py-2">${escapeHtml(a.account_code || 'N/A')}</td>
                    <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${statusClass}">${escapeHtml(a.status)}</span></td>
                    <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${conditionClass}">${escapeHtml(a.condition)}</span></td>
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