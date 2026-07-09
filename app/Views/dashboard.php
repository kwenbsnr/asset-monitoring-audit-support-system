<?php if (!defined('APP_START')) exit; ?>

<!-- Dashboard Header -->
<div class="d-flex justify-content-between align-items-start flex-wrap mb-4">
    <div class="text-end text-muted small">
        <div><i class="bi bi-calendar3"></i> <?= date('F d, Y') ?></div>
        <div><i class="bi bi-clock"></i> <?= date('h:i A') ?></div>
        <div><span class="badge bg-secondary"><?= ucfirst($_SESSION['role']) ?></span></div>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <!-- ... Total Assets, Active Assets, etc. (unchanged) ... -->
    <!-- Replace Categories with Accounts -->
    <div class="col-xl-2 col-lg-4 col-md-4">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="text-muted small"><i class="bi bi-collection"></i> Accounts</div>
                <div class="fs-4 fw-bold"><?= number_format($totalAccounts) ?></div>
            </div>
        </div>
    </div>
    <!-- ... other KPIs ... -->
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <!-- Status Distribution (unchanged) -->
    <div class="col-xl-4 col-lg-6">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-header bg-white border-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart"></i> Asset Status Distribution</h6>
            </div>
            <div class="card-body">
                <canvas id="statusDoughnutChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <!-- Assets by Account (new) -->
    <div class="col-xl-4 col-lg-6">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-header bg-white border-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart"></i> Assets by Account</h6>
            </div>
            <div class="card-body">
                <canvas id="accountBarChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <!-- Assets by Office (unchanged) -->
    <div class="col-xl-4 col-lg-12">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-header bg-white border-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-buildings"></i> Assets by Office</h6>
            </div>
            <div class="card-body">
                <canvas id="officeBarChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ... Alerts & Quick Actions (unchanged) ... -->

<!-- Recent Assets Table – updated to use asset_name -->
<div class="row g-3">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-table"></i> Recently Added Assets</h6>
                <a href="index.php?page=assets&sub=list_all" class="btn btn-primary btn-sm">View All Assets</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
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
                                <tr><td colspan="8" class="text-center text-muted">No assets found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentAssets as $asset): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($asset['asset_code']) ?></strong></td>
                                        <td><?= htmlspecialchars($asset['asset_name']) ?></td>
                                        <td><?= htmlspecialchars($asset['account_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($asset['custodian'] ?? 'Not assigned') ?></td>
                                        <td><?= htmlspecialchars($asset['office_name'] ?? 'N/A') ?></td>
                                        <td><span class="badge bg-<?= $asset['status'] === 'active' ? 'success' : 'secondary' ?>"><?= $asset['status'] ?></span></td>
                                        <td><span class="badge bg-<?= $asset['condition'] === 'good' ? 'success' : 'warning' ?>"><?= $asset['condition'] ?></span></td>
                                        <td><?= date('M d, Y', strtotime($asset['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Doughnut: Status Distribution (unchanged)
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

        // Bar: Assets by Account (new)
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

        // Bar: Assets by Office (unchanged)
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