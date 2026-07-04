<?php if (!defined('APP_START')) exit; ?>

<div class="row">
    <!-- Stats Cards -->
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-box-seam"></i> Total Assets</h5>
                <h2 class="display-4"><?= number_format($totalAssets) ?></h2>
                <p class="card-text">Active assets in the system</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-check-circle"></i> Active</h5>
                <h2 class="display-4"><?= number_format($activeInactive['active'] ?? 0) ?></h2>
                <p class="card-text">Assets currently active</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-warning h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-exclamation-triangle"></i> Other Status</h5>
                <h2 class="display-4"><?= number_format($activeInactive['other'] ?? 0) ?></h2>
                <p class="card-text">Disposed, missing, etc.</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-secondary h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-archive"></i> Inactive</h5>
                <h2 class="display-4"><?= number_format($activeInactive['inactive'] ?? 0) ?></h2>
                <p class="card-text">Retired or decommissioned</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Assets by Category</h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Assets by Status</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people"></i> Recent Custody Assignments</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Asset</th>
                                <th>Custodian</th>
                                <th>Office</th>
                                <th>Since</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentCustody)): ?>
                                <tr><td colspan="4" class="text-center">No custody records found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentCustody as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['asset_code'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($row['custodian']) ?></td>
                                        <td><?= htmlspecialchars($row['office']) ?></td>
                                        <td><?= date('M d, Y', strtotime($row['effectivity_date'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Audit Logs</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Module</th>
                                <th>User</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentAudit)): ?>
                                <tr><td colspan="4" class="text-center">No audit logs found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentAudit as $row): ?>
                                    <tr>
                                        <td><span class="badge bg-info"><?= htmlspecialchars($row['action_type']) ?></span></td>
                                        <td><?= htmlspecialchars($row['module']) ?></td>
                                        <td><?= htmlspecialchars($row['performed_by']) ?></td>
                                        <td><?= date('M d, Y H:i', strtotime($row['performed_at'])) ?></td>
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

<!-- Chart.js and chart initialization -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Category Chart (Pie)
        const categoryData = <?= json_encode($chartCategoryLabels) ?>;
        const categoryCounts = <?= json_encode($chartCategoryData) ?>;
        if (categoryData.length) {
            new Chart(document.getElementById('categoryChart'), {
                type: 'pie',
                data: {
                    labels: categoryData,
                    datasets: [{
                        data: categoryCounts,
                        backgroundColor: ['#345735', '#4a7a4a', '#6b9a6b', '#8cba8c', '#addbad', '#cef0ce'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        // Status Chart (Bar)
        const statusLabels = <?= json_encode($chartStatusLabels) ?>;
        const statusCounts = <?= json_encode($chartStatusData) ?>;
        if (statusLabels.length) {
            new Chart(document.getElementById('statusChart'), {
                type: 'bar',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        label: 'Assets',
                        data: statusCounts,
                        backgroundColor: '#345735',
                        borderColor: '#182919',
                        borderWidth: 1
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
    });
</script>