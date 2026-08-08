<?php if (!defined('APP_START')) exit; ?>

<!-- Dashboard Header -->
<div class="flex flex-wrap items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <span class="page-icon" style="width:46px;height:46px;font-size:1.2rem;"><i class="bi bi-speedometer2"></i></span>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Dashboard</h1>
            <div class="text-xs text-gray-500">Overview of asset holdings and recent activity</div>
        </div>
    </div>
    <div class="text-sm text-gray-500 text-right">
        <div><i class="bi bi-calendar3"></i> <?= date('F d, Y') ?></div>
        <div><i class="bi bi-clock"></i> <?= date('h:i A') ?></div>
        <div class="mt-1"><span class="badge-app badge-app-neutral"><?= ucfirst($_SESSION['role']) ?></span></div>
    </div>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="card-panel p-4">
        <div class="text-sm text-gray-500"><i class="bi bi-box-seam"></i> Total Assets</div>
        <div class="text-2xl font-bold text-gray-800 mt-1"><?= number_format($totalAssets ?? 0) ?></div>
    </div>
    <div class="card-panel p-4">
        <div class="text-sm text-gray-500"><i class="bi bi-check-circle"></i> Active Assets</div>
        <div class="text-2xl font-bold text-gray-800 mt-1"><?= number_format($activeAssets ?? 0) ?></div>
    </div>
    <div class="card-panel p-4">
        <div class="text-sm text-gray-500"><i class="bi bi-collection"></i> Accounts</div>
        <div class="text-2xl font-bold text-gray-800 mt-1"><?= number_format($totalAccounts ?? 0) ?></div>
    </div>
    <div class="card-panel p-4">
        <div class="text-sm text-gray-500"><i class="bi bi-building"></i> Offices</div>
        <div class="text-2xl font-bold text-gray-800 mt-1"><?= number_format($totalOffices ?? 0) ?></div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Status Distribution (Doughnut) -->
    <div class="card-panel p-4">
        <h6 class="font-semibold text-gray-700 border-b border-gray-200 pb-2 mb-3"><i class="bi bi-pie-chart"></i> Asset Status Distribution</h6>
        <canvas id="statusDoughnutChart" height="200"></canvas>
    </div>
    <!-- Assets by Account (Bar) -->
    <div class="card-panel p-4">
        <h6 class="font-semibold text-gray-700 border-b border-gray-200 pb-2 mb-3"><i class="bi bi-bar-chart"></i> Assets by Account</h6>
        <canvas id="accountBarChart" height="200"></canvas>
    </div>
    <!-- Assets by Office (Bar) -->
    <div class="card-panel p-4">
        <h6 class="font-semibold text-gray-700 border-b border-gray-200 pb-2 mb-3"><i class="bi bi-buildings"></i> Assets by Office</h6>
        <canvas id="officeBarChart" height="200"></canvas>
    </div>
</div>

<!-- Recent Assets Table -->
<div class="card-panel p-4 mb-6">
    <div class="flex flex-wrap items-center justify-between border-b border-gray-200 pb-2 mb-3">
        <h6 class="font-semibold text-gray-700"><i class="bi bi-table"></i> Recently Added Assets</h6>
        <a href="index.php?page=assets&sub=list_all" class="btn-app btn-app-sm btn-app-primary">View All Assets</a>
    </div>
    <div class="table-app-wrap">
        <table class="table-app">
            <thead>
                <tr>
                    <th>Asset Code</th>
                    <th>Asset Name</th>
                    <th>Account</th>
                    <th>Custodian</th>
                    <th>Office</th>
                    <th>Status</th>
                    <th>Condition</th>
                    <th>Date Added</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentAssets)): ?>
                    <tr><td colspan="8"><div class="table-empty">No assets found.</div></td></tr>
                <?php else: ?>
                    <?php foreach ($recentAssets as $asset): ?>
                        <tr>
                            <td class="font-medium text-gray-800"><?= htmlspecialchars($asset['asset_code']) ?></td>
                            <td><?= htmlspecialchars($asset['asset_name']) ?></td>
                            <td><?= htmlspecialchars($asset['account_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($asset['custodian'] ?? 'Not assigned') ?></td>
                            <td><?= htmlspecialchars($asset['office_name'] ?? 'N/A') ?></td>
                            <td>
                                <span class="badge-app <?= $asset['status'] === 'active' ? 'badge-app-success' : 'badge-app-neutral' ?>">
                                    <?= $asset['status'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge-app <?= $asset['condition'] === 'good' ? 'badge-app-success' : 'badge-app-warning' ?>">
                                    <?= $asset['condition'] ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($asset['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    window.dashboardChartData = <?= json_encode([
        'statusLabels' => $statusLabels,
        'statusData' => $statusData,
        'accountLabels' => $accountLabels,
        'accountData' => $accountData,
        'officeLabels' => $officeLabels,
        'officeData' => $officeData,
    ]) ?>;
</script>
<script src="/asset-monitoring-audit-support-system/public/js/dashboard-charts.js"></script>