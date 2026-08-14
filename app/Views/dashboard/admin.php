<?php if (!defined('APP_START')) exit; ?>

<!-- Dashboard Header -->
<div class="flex flex-wrap items-center justify-between gap-4 mb-8 pb-5 border-b border-gray-200">
    <div class="flex items-center gap-4">
        <span class="flex items-center justify-center w-12 h-12 rounded-xl bg-linear-to-br from-[#182919] to-[#345635] text-white text-xl shadow-md">
            <i class="bi bi-speedometer2"></i>
        </span>
        <div>
            <h1 class="text-2xl font-bold text-gray-800 leading-tight">Dashboard</h1>
            <div class="text-sm text-gray-500">System overview — accounts, activity, and items needing attention</div>
        </div>
    </div>
    <div class="flex items-center gap-3 text-sm text-gray-600 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5">
        <div class="flex items-center gap-1.5"><i class="bi bi-calendar3 text-gray-400"></i> <?= date('F d, Y') ?></div>
        <span class="w-px h-4 bg-gray-300"></span>
        <div class="flex items-center gap-1.5"><i class="bi bi-clock text-gray-400"></i> <?= date('h:i A') ?></div>
        <span class="w-px h-4 bg-gray-300"></span>
        <span class="badge-app badge-app-neutral">System Administrator</span>
    </div>
</div>

<!-- System Totals -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-6">
    <div class="card-panel p-5 text-center">
        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 text-blue-600 text-xl mb-3"><i class="bi bi-box-seam"></i></span>
        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Assets</div>
        <div class="text-3xl font-extrabold text-gray-800 mt-1"><?= number_format($totalAssets ?? 0) ?></div>
    </div>
    <div class="card-panel p-5 text-center">
        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-purple-100 text-purple-600 text-xl mb-3"><i class="bi bi-building"></i></span>
        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Offices</div>
        <div class="text-3xl font-extrabold text-gray-800 mt-1"><?= number_format($totalOffices ?? 0) ?></div>
    </div>
    <div class="card-panel p-5 text-center">
        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-yellow-100 text-yellow-600 text-xl mb-3"><i class="bi bi-collection"></i></span>
        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Accounts</div>
        <div class="text-3xl font-extrabold text-gray-800 mt-1"><?= number_format($totalAccounts ?? 0) ?></div>
    </div>
    <div class="card-panel p-5 text-center">
        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 text-green-600 text-xl mb-3"><i class="bi bi-people"></i></span>
        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Users</div>
        <div class="text-3xl font-extrabold text-gray-800 mt-1"><?= number_format($totalUsers ?? 0) ?></div>
        <div class="text-xs text-gray-400 mt-0.5"><?= number_format($activeUsers ?? 0) ?> active</div>
    </div>
</div>

<!-- Needs Attention -->
<div class="mb-6">
    <h6 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Needs Attention</h6>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <a href="index.php?page=assets&sub=list_all&status=missing" class="card-panel p-5 flex items-center gap-4 border-l-4! border-red-400! hover:shadow-md transition-all duration-200">
            <span class="flex items-center justify-center w-12 h-12 shrink-0 rounded-xl bg-red-100 text-red-600 text-xl"><i class="bi bi-exclamation-triangle"></i></span>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Missing Assets</div>
                <div class="text-2xl font-extrabold text-gray-800"><?= number_format($missingAssets ?? 0) ?></div>
            </div>
        </a>
        <a href="index.php?page=assets&sub=list_all&condition=damaged" class="card-panel p-5 flex items-center gap-4 border-l-4! border-yellow-400! hover:shadow-md transition-all duration-200">
            <span class="flex items-center justify-center w-12 h-12 shrink-0 rounded-xl bg-yellow-100 text-yellow-600 text-xl"><i class="bi bi-tools"></i></span>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Damaged Assets</div>
                <div class="text-2xl font-extrabold text-gray-800"><?= number_format($damagedAssets ?? 0) ?></div>
            </div>
        </a>
        <a href="index.php?page=assets&sub=list_all&status=disposed" class="card-panel p-5 flex items-center gap-4 border-l-4! border-gray-400! hover:shadow-md transition-all duration-200">
            <span class="flex items-center justify-center w-12 h-12 shrink-0 rounded-xl bg-gray-100 text-gray-500 text-xl"><i class="bi bi-trash3"></i></span>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Disposed Assets</div>
                <div class="text-2xl font-extrabold text-gray-800"><?= number_format($disposedAssets ?? 0) ?></div>
            </div>
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Reports -->
    <div class="lg:col-span-1">
        <div class="card-panel p-5 h-full">
            <h6 class="font-semibold text-gray-700 border-b border-gray-200 pb-3 mb-4 flex items-center gap-2">
                <i class="bi bi-file-earmark-text text-gray-400"></i> Reports
            </h6>
            <div class="flex items-center justify-between py-2">
                <span class="text-sm text-gray-600 flex items-center gap-2"><span class="badge-app badge-app-neutral">Draft</span></span>
                <span class="text-xl font-bold text-gray-800"><?= number_format($draftReports ?? 0) ?></span>
            </div>
            <div class="flex items-center justify-between py-2 border-t border-gray-100">
                <span class="text-sm text-gray-600 flex items-center gap-2"><span class="badge-app badge-app-success">Submitted</span></span>
                <span class="text-xl font-bold text-gray-800"><?= number_format($submittedReports ?? 0) ?></span>
            </div>
            <a href="index.php?page=reports" class="mt-4 block text-center btn-app btn-app-outline-primary btn-app-sm">
                <i class="bi bi-arrow-right"></i> Go to Reports
            </a>
        </div>
    </div>

    <!-- Recent Audit Trail -->
    <div class="lg:col-span-2">
        <div class="card-panel p-5 h-full">
            <div class="flex items-center justify-between border-b border-gray-200 pb-3 mb-4">
                <h6 class="font-semibold text-gray-700 flex items-center gap-2">
                    <i class="bi bi-clock-history text-gray-400"></i> Recent Audit Trail
                </h6>
                <a href="index.php?page=audit" class="text-xs font-semibold text-green-700 hover:underline">View All</a>
            </div>
            <div class="divide-y divide-gray-100">
                <?php if (empty($recentAudit)): ?>
                    <div class="table-empty text-center text-gray-400 py-6">No recent activity.</div>
                <?php else: ?>
                    <?php foreach ($recentAudit as $log): ?>
                        <div class="flex items-center justify-between py-2.5 text-sm">
                            <div class="min-w-0">
                                <span class="font-medium text-gray-800"><?= htmlspecialchars($log['performed_by'] ?? 'System') ?></span>
                                <span class="text-gray-500"><?= htmlspecialchars($log['action_type']) ?> · <?= htmlspecialchars($log['module']) ?></span>
                                <?php if (!empty($log['asset_code'])): ?>
                                    <span class="text-gray-400">(<?= htmlspecialchars($log['asset_code']) ?>)</span>
                                <?php endif; ?>
                            </div>
                            <span class="text-xs text-gray-400 whitespace-nowrap ml-3"><?= htmlspecialchars($log['performed_at']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card-panel p-5">
    <h6 class="font-semibold text-gray-700 border-b border-gray-200 pb-3 mb-4 flex items-center gap-2">
        <i class="bi bi-lightning-charge text-gray-400"></i> Quick Actions
    </h6>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <a href="index.php?page=users&sub=add" class="btn-app btn-app-outline-primary flex items-center justify-center gap-2 py-3">
            <i class="bi bi-person-plus"></i> Add User
        </a>
        <a href="index.php?page=audit" class="btn-app btn-app-outline-primary flex items-center justify-center gap-2 py-3">
            <i class="bi bi-clock-history"></i> View Audit Trail
        </a>
        <a href="index.php?page=reports&sub=add" class="btn-app btn-app-outline-primary flex items-center justify-center gap-2 py-3">
            <i class="bi bi-file-earmark-plus"></i> Generate Report
        </a>
        <a href="index.php?page=assets&sub=scan" class="btn-app btn-app-outline-primary flex items-center justify-center gap-2 py-3">
            <i class="bi bi-qr-code-scan"></i> Scan QR
        </a>
    </div>
</div>