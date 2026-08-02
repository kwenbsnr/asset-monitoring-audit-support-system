<?php if (!defined('APP_START')) exit; ?>

<!-- Dashboard Header -->
<div class="flex flex-wrap items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
    <div class="text-sm text-gray-500 text-right">
        <div><i class="bi bi-calendar3"></i> <?= date('F d, Y') ?></div>
        <div><i class="bi bi-clock"></i> <?= date('h:i A') ?></div>
        <div><span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-700"><?= ucfirst($_SESSION['role']) ?></span></div>
    </div>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition">
        <div class="text-sm text-gray-500"><i class="bi bi-box-seam"></i> Total Assets</div>
        <div class="text-2xl font-bold text-gray-800"><?= number_format($totalAssets ?? 0) ?></div>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition">
        <div class="text-sm text-gray-500"><i class="bi bi-check-circle"></i> Active Assets</div>
        <div class="text-2xl font-bold text-gray-800"><?= number_format($activeAssets ?? 0) ?></div>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition">
        <div class="text-sm text-gray-500"><i class="bi bi-collection"></i> Accounts</div>
        <div class="text-2xl font-bold text-gray-800"><?= number_format($totalAccounts ?? 0) ?></div>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition">
        <div class="text-sm text-gray-500"><i class="bi bi-building"></i> Offices</div>
        <div class="text-2xl font-bold text-gray-800"><?= number_format($totalOffices ?? 0) ?></div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Status Distribution (Doughnut) -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <h6 class="font-semibold text-gray-700 border-b pb-2 mb-3"><i class="bi bi-pie-chart"></i> Asset Status Distribution</h6>
        <canvas id="statusDoughnutChart" height="200"></canvas>
    </div>
    <!-- Assets by Account (Bar) -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <h6 class="font-semibold text-gray-700 border-b pb-2 mb-3"><i class="bi bi-bar-chart"></i> Assets by Account</h6>
        <canvas id="accountBarChart" height="200"></canvas>
    </div>
    <!-- Assets by Office (Bar) -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <h6 class="font-semibold text-gray-700 border-b pb-2 mb-3"><i class="bi bi-buildings"></i> Assets by Office</h6>
        <canvas id="officeBarChart" height="200"></canvas>
    </div>
</div>

<!-- Recent Assets Table -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
    <div class="flex flex-wrap items-center justify-between border-b pb-2 mb-3">
        <h6 class="font-semibold text-gray-700"><i class="bi bi-table"></i> Recently Added Assets</h6>
        <a href="index.php?page=assets&sub=list_all" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition">View All Assets</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm border border-gray-200">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-4 py-2 border-b text-left font-medium">Asset Code</th>
                    <th class="px-4 py-2 border-b text-left font-medium">Asset Name</th>
                    <th class="px-4 py-2 border-b text-left font-medium">Account</th>
                    <th class="px-4 py-2 border-b text-left font-medium">Custodian</th>
                    <th class="px-4 py-2 border-b text-left font-medium">Office</th>
                    <th class="px-4 py-2 border-b text-left font-medium">Status</th>
                    <th class="px-4 py-2 border-b text-left font-medium">Condition</th>
                    <th class="px-4 py-2 border-b text-left font-medium">Date Added</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentAssets)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-gray-500">No assets found.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentAssets as $asset): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium text-gray-800"><?= htmlspecialchars($asset['asset_code']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($asset['asset_name']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($asset['account_name'] ?? 'N/A') ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($asset['custodian'] ?? 'Not assigned') ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($asset['office_name'] ?? 'N/A') ?></td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $asset['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                    <?= $asset['status'] ?>
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $asset['condition'] === 'good' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                    <?= $asset['condition'] ?>
                                </span>
                            </td>
                            <td class="px-4 py-2"><?= date('M d, Y', strtotime($asset['created_at'])) ?></td>
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
/* @noinspection PhpUndefinedClassInspection */
// Suppresses false "Class 'Chart' not imported" warnings from Intelephense

document.addEventListener('DOMContentLoaded', function() {
    // Doughnut: Status Distribution
    const statusLabels = <?= json_encode($statusLabels) ?>;
    const statusData = <?= json_encode($statusData) ?>;
    if (statusLabels.length) {
        new Chart(document.getElementById('statusDoughnutChart'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#6c757d', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { size: 10 } }
                    }
                },
                cutout: '70%'
            }
        });
    }

    // Bar: Assets by Account
    const accountLabels = <?= json_encode($accountLabels) ?>;
    const accountData = <?= json_encode($accountData) ?>;
    if (accountLabels.length) {
        new Chart(document.getElementById('accountBarChart'), {
            type: 'bar',
            data: {
                labels: accountLabels,
                datasets: [{
                    label: 'Assets',
                    data: accountData,
                    backgroundColor: '#0d6efd',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Bar: Assets by Office (horizontal)
    const officeLabels = <?= json_encode($officeLabels) ?>;
    const officeData = <?= json_encode($officeData) ?>;
    if (officeLabels.length) {
        new Chart(document.getElementById('officeBarChart'), {
            type: 'bar',
            data: {
                labels: officeLabels,
                datasets: [{
                    label: 'Assets',
                    data: officeData,
                    backgroundColor: '#198754',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                },
                indexAxis: 'y'
            }
        });
    }
});
</script>