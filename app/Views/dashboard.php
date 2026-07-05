<?php if (!defined('APP_START')) exit; ?>

<!-- Dashboard Header -->
<div class="d-flex justify-content-between align-items-start flex-wrap mb-4">
    <!--
    <div>
        <h1 class="fw-bold mb-1">Dashboard</h1>
        <p class="text-muted">Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>!<br>Here's an overview of the agency's asset inventory and monitoring activities.</p>
    </div>
    --> 
    <div class="text-end text-muted small">
        <div><i class="bi bi-calendar3"></i> <?= date('F d, Y') ?></div>
        <div><i class="bi bi-clock"></i> <?= date('h:i A') ?></div>
        <div><span class="badge bg-secondary"><?= ucfirst($_SESSION['role']) ?></span></div>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                    <i class="bi bi-box-seam fs-3 text-primary"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Assets</div>
                    <div class="fs-3 fw-bold"><?= number_format($totalAssets) ?></div>
                    <div class="text-success small"><i class="bi bi-arrow-up"></i> <?= $activeInactive['active'] ?? 0 ?> active</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                    <i class="bi bi-check-circle fs-3 text-success"></i>
                </div>
                <div>
                    <div class="text-muted small">Active Assets</div>
                    <div class="fs-3 fw-bold"><?= number_format($activeInactive['active'] ?? 0) ?></div>
                    <div class="text-muted small"><?= $totalAssets > 0 ? round(($activeInactive['active'] / $totalAssets) * 100) : 0 ?>% of total</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                    <i class="bi bi-person-check fs-3 text-info"></i>
                </div>
                <div>
                    <div class="text-muted small">Assets Under Custody</div>
                    <div class="fs-3 fw-bold"><?= number_format($assetsUnderCustody) ?></div>
                    <div class="text-muted small">Assigned to custodians</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle <?= ($missingAssets > 0) ? 'bg-danger' : 'bg-success' ?> bg-opacity-10 p-3 me-3">
                    <i class="bi bi-exclamation-triangle fs-3 <?= ($missingAssets > 0) ? 'text-danger' : 'text-success' ?>"></i>
                </div>
                <div>
                    <div class="text-muted small">Missing Assets</div>
                    <div class="fs-3 fw-bold"><?= number_format($missingAssets) ?></div>
                    <div class="text-muted small"><?= $missingAssets > 0 ? 'Requires attention' : 'All accounted for' ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Second row KPI -->
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-4">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="text-muted small"><i class="bi bi-tag"></i> Categories</div>
                <div class="fs-4 fw-bold"><?= number_format($totalCategories) ?></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="text-muted small"><i class="bi bi-building"></i> Offices</div>
                <div class="fs-4 fw-bold"><?= number_format($totalOffices) ?></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="text-muted small"><i class="bi bi-arrow-left-right"></i> Transfers This Month</div>
                <div class="fs-4 fw-bold"><?= number_format($recentTransfers) ?></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="text-muted small"><i class="bi bi-trash"></i> For Disposal</div>
                <div class="fs-4 fw-bold"><?= number_format($assetsForDisposal) ?></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-4 col-md-8">
        <div class="card shadow-sm h-100 border-0 bg-light">
            <div class="card-body d-flex align-items-center justify-content-between">
                <span class="text-muted"><i class="bi bi-info-circle"></i> Total Assets: <?= number_format($totalAssets) ?></span>
                <span class="text-muted"><i class="bi bi-check-circle-fill text-success"></i> Active: <?= $activeInactive['active'] ?? 0 ?></span>
                <span class="text-muted"><i class="bi bi-exclamation-triangle-fill text-warning"></i> Others: <?= $activeInactive['other'] ?? 0 ?></span>
                <span class="text-muted"><i class="bi bi-archive-fill text-secondary"></i> Inactive: <?= $activeInactive['inactive'] ?? 0 ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
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
    <div class="col-xl-4 col-lg-6">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-header bg-white border-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart"></i> Asset Condition</h6>
            </div>
            <div class="card-body">
                <canvas id="conditionBarChart" height="200"></canvas>
            </div>
        </div>
    </div>
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

<!-- Alerts & Quick Actions -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-bell"></i> Alerts</h6>
            </div>
            <div class="card-body">
                <?php if (empty($alerts)): ?>
                    <p class="text-muted text-center"><i class="bi bi-check-circle-fill text-success"></i> No alerts.</p>
                <?php else: ?>
                    <ul class="list-unstyled">
                        <?php foreach ($alerts as $alert): ?>
                            <li class="d-flex align-items-center mb-2">
                                <span class="badge bg-<?= $alert['color'] ?> me-2"><?= $alert['count'] ?></span>
                                <i class="<?= $alert['icon'] ?> me-2 text-<?= $alert['color'] ?>"></i>
                                <?= $alert['label'] ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-lightning"></i> Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6 col-md-4">
                        <a href="index.php?page=assets&sub=add" class="btn btn-outline-primary w-100 py-2">
                            <i class="bi bi-plus-circle d-block fs-4"></i>
                            <span class="small">Register Asset</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-4">
                        <a href="index.php?page=assets&sub=scan" class="btn btn-outline-success w-100 py-2">
                            <i class="bi bi-camera d-block fs-4"></i>
                            <span class="small">Scan QR</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-4">
                        <a href="index.php?page=assets&sub=browse" class="btn btn-outline-info w-100 py-2">
                            <i class="bi bi-search d-block fs-4"></i>
                            <span class="small">Search Asset</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-4">
                        <a href="index.php?page=reports&sub=generate" class="btn btn-outline-warning w-100 py-2">
                            <i class="bi bi-file-earmark-text d-block fs-4"></i>
                            <span class="small">Generate Report</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-4">
                        <a href="index.php?page=custody" class="btn btn-outline-secondary w-100 py-2">
                            <i class="bi bi-people d-block fs-4"></i>
                            <span class="small">Manage Custody</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-4">
                        <a href="index.php?page=assets&sub=add" class="btn btn-outline-danger w-100 py-2">
                            <i class="bi bi-arrow-left-right d-block fs-4"></i>
                            <span class="small">Transfer Asset</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row g-3 mb-4">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-clock-history"></i> Recent Activity</h6>
                <span class="text-muted small">Last <?= count($recentActivity) ?> actions</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if (empty($recentActivity)): ?>
                        <li class="list-group-item text-muted text-center">No recent activity.</li>
                    <?php else: ?>
                        <?php foreach ($recentActivity as $activity): ?>
                            <li class="list-group-item d-flex align-items-center">
                                <div class="me-3">
                                    <?php 
                                        $icon = 'bi bi-activity';
                                        $color = 'text-secondary';
                                        switch ($activity['type']) {
                                            case 'asset_registered': $icon = 'bi bi-box-seam'; $color = 'text-primary'; break;
                                            case 'custody_assigned': $icon = 'bi bi-person-check'; $color = 'text-success'; break;
                                            case 'asset_transferred': $icon = 'bi bi-arrow-left-right'; $color = 'text-warning'; break;
                                            case 'report_generated': $icon = 'bi bi-file-earmark-text'; $color = 'text-info'; break;
                                        }
                                    ?>
                                    <i class="<?= $icon . ' ' . $color ?> fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div><?= htmlspecialchars($activity['description']) ?></div>
                                    <div class="small text-muted">
                                        <i class="bi bi-person"></i> <?= htmlspecialchars($activity['performed_by'] ?? 'System') ?>
                                        <i class="bi bi-clock ms-2"></i> <?= date('M d, Y h:i A', strtotime($activity['created_at'])) ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Recent Assets Table -->
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
                                <th>Description</th>
                                <th>Category</th>
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
                                        <td><?= htmlspecialchars($asset['description']) ?></td>
                                        <td><?= htmlspecialchars($asset['category_name'] ?? 'N/A') ?></td>
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

        // Bar: Condition
        const conditionLabels = <?= json_encode($conditionLabels) ?>;
        const conditionData = <?= json_encode($conditionData) ?>;
        if (conditionLabels.length) {
            new Chart(document.getElementById('conditionBarChart'), {
                type: 'bar',
                data: {
                    labels: conditionLabels,
                    datasets: [{
                        label: 'Assets',
                        data: conditionData,
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

        // Bar: Assets by Office
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