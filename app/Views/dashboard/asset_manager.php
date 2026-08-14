<?php if (!defined('APP_START')) exit; ?>

<!-- Dashboard Header -->
<div class="flex flex-wrap items-center justify-between gap-4 mb-8 pb-5 border-b border-gray-200">
    <div class="flex items-center gap-4">
        <span class="flex items-center justify-center w-12 h-12 rounded-xl bg-linear-to-br from-[#182919] to-[#345635] text-white text-xl shadow-md">
            <i class="bi bi-speedometer2"></i>
        </span>
        <div>
            <h1 class="text-2xl font-bold text-gray-800 leading-tight">Dashboard</h1>
            <div class="text-sm text-gray-500">Overview of asset holdings and recent activity</div>
        </div>
    </div>
    <div class="flex items-center gap-3 text-sm text-gray-600 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5">
        <div class="flex items-center gap-1.5"><i class="bi bi-calendar3 text-gray-400"></i> <?= date('F d, Y') ?></div>
        <span class="w-px h-4 bg-gray-300"></span>
        <div class="flex items-center gap-1.5"><i class="bi bi-clock text-gray-400"></i> <?= date('h:i A') ?></div>
        <span class="w-px h-4 bg-gray-300"></span>
        <span class="badge-app badge-app-neutral">Asset Manager</span>
    </div>
</div>

<!-- Quick Actions (front and center — these are the most-used actions for this role) -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    <a href="index.php?page=assets&sub=add" class="btn-app btn-app-primary flex items-center justify-center gap-2 py-4 text-base">
        <i class="bi bi-plus-circle"></i> Register Asset
    </a>
    <a href="index.php?page=custody&sub=add" class="btn-app btn-app-outline-primary flex items-center justify-center gap-2 py-4 text-base">
        <i class="bi bi-person-plus"></i> Assign Custody
    </a>
    <a href="index.php?page=assets&sub=by_office" class="btn-app btn-app-outline-primary flex items-center justify-center gap-2 py-4 text-base">
        <i class="bi bi-building"></i> Assets by Office
    </a>
    <a href="index.php?page=assets&sub=scan" class="btn-app btn-app-outline-primary flex items-center justify-center gap-2 py-4 text-base">
        <i class="bi bi-qr-code-scan"></i> Scan QR
    </a>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-2 gap-5 mb-6">
    <div class="card-panel p-5 text-center">
        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 text-blue-600 text-xl mb-3"><i class="bi bi-box-seam"></i></span>
        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Assets</div>
        <div class="text-3xl font-extrabold text-gray-800 mt-1"><?= number_format($totalAssets ?? 0) ?></div>
    </div>
    <div class="card-panel p-5 text-center">
        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 text-green-600 text-xl mb-3"><i class="bi bi-check-circle"></i></span>
        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Active Assets</div>
        <div class="text-3xl font-extrabold text-gray-800 mt-1"><?= number_format($activeAssets ?? 0) ?></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Condition breakdown -->
    <div class="lg:col-span-1">
        <div class="card-panel p-5 h-full">
            <h6 class="font-semibold text-gray-700 border-b border-gray-200 pb-3 mb-4 flex items-center gap-2">
                <i class="bi bi-bar-chart text-gray-400"></i> Assets by Condition
            </h6>
            <div class="relative" style="height:220px;">
                <canvas id="conditionBarChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recently Added Assets -->
    <div class="lg:col-span-2">
        <div class="card-panel p-5">
            <div class="flex flex-wrap items-center justify-between border-b border-gray-200 pb-3 mb-4">
                <h6 class="font-semibold text-gray-700 flex items-center gap-2">
                    <i class="bi bi-table text-gray-400"></i> Recently Added Assets
                </h6>
                <a href="index.php?page=assets&sub=list_all" class="btn-app btn-app-sm btn-app-primary">View All Assets</a>
            </div>
            <div class="table-app-wrap rounded-xl border border-gray-200 overflow-x-auto">
                <table class="table-app w-full text-sm table-fixed min-w-[700px]">
                    <colgroup>
                        <col style="width:16%">
                        <col style="width:26%">
                        <col style="width:20%">
                        <col style="width:14%">
                        <col style="width:12%">
                        <col style="width:12%">
                    </colgroup>
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                            <th class="px-4 py-3 text-left font-semibold">Asset Code</th>
                            <th class="px-4 py-3 text-left font-semibold">Asset Name</th>
                            <th class="px-4 py-3 text-left font-semibold">Account</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Condition</th>
                            <th class="px-4 py-3 text-left font-semibold">Date Added</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($recentAssets)): ?>
                            <tr><td colspan="6" class="px-4 py-8"><div class="table-empty text-center text-gray-400">No assets found.</div></td></tr>
                        <?php else: ?>
                            <?php foreach ($recentAssets as $asset): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-800 truncate" title="<?= htmlspecialchars($asset['asset_code']) ?>"><?= htmlspecialchars($asset['asset_code']) ?></td>
                                    <td class="px-4 py-3 truncate" title="<?= htmlspecialchars($asset['asset_name']) ?>"><?= htmlspecialchars($asset['asset_name']) ?></td>
                                    <td class="px-4 py-3 truncate" title="<?= htmlspecialchars($asset['account_name'] ?? 'N/A') ?>"><?= htmlspecialchars($asset['account_name'] ?? 'N/A') ?></td>
                                    <td class="px-4 py-3">
                                        <span class="badge-app <?= $asset['status'] === 'active' ? 'badge-app-success' : 'badge-app-neutral' ?>"><?= $asset['status'] ?></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge-app <?= $asset['condition'] === 'good' ? 'badge-app-success' : 'badge-app-warning' ?>"><?= $asset['condition'] ?></span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-500"><?= date('M d, Y', strtotime($asset['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('conditionBarChart');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($conditionLabels ?? []) ?>,
                datasets: [{
                    label: 'Assets',
                    data: <?= json_encode($conditionData ?? []) ?>,
                    backgroundColor: '#345635',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    });
</script>