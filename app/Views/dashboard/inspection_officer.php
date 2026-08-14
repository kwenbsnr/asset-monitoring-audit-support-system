<?php if (!defined('APP_START')) exit; ?>

<!-- Dashboard Header -->
<div class="flex flex-wrap items-center justify-between gap-4 mb-8 pb-5 border-b border-gray-200">
    <div class="flex items-center gap-4">
        <span class="flex items-center justify-center w-12 h-12 rounded-xl bg-linear-to-br from-[#182919] to-[#345635] text-white text-xl shadow-md">
            <i class="bi bi-speedometer2"></i>
        </span>
        <div>
            <h1 class="text-2xl font-bold text-gray-800 leading-tight">Dashboard</h1>
            <div class="text-sm text-gray-500">Asset verification overview</div>
        </div>
    </div>
    <div class="flex items-center gap-3 text-sm text-gray-600 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5">
        <div class="flex items-center gap-1.5"><i class="bi bi-calendar3 text-gray-400"></i> <?= date('F d, Y') ?></div>
        <span class="w-px h-4 bg-gray-300"></span>
        <div class="flex items-center gap-1.5"><i class="bi bi-clock text-gray-400"></i> <?= date('h:i A') ?></div>
        <span class="w-px h-4 bg-gray-300"></span>
        <span class="badge-app badge-app-neutral">Inspection Officer</span>
    </div>
</div>

<!-- Primary action — this is the core of the role -->
<a href="index.php?page=assets&sub=verify" class="card-panel p-6 mb-6 flex items-center justify-between gap-4 hover:shadow-md transition-all duration-200 bg-linear-to-r! from-[#182919] to-[#345635] text-white!">
    <div class="flex items-center gap-4">
        <span class="flex items-center justify-center w-14 h-14 rounded-xl bg-white/15 text-2xl">
            <i class="bi bi-qr-code-scan"></i>
        </span>
        <div>
            <div class="text-lg font-bold">Verify Asset</div>
            <div class="text-sm text-white/80">Scan a QR code to look up and verify an asset</div>
        </div>
    </div>
    <i class="bi bi-arrow-right text-2xl text-white/70"></i>
</a>

<!-- Assets flagged for inspection follow-up -->
<div class="mb-6">
    <h6 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Assets Flagged by Condition</h6>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <a href="index.php?page=assets&sub=list_all&condition=poor" class="card-panel p-5 flex items-center gap-4 hover:shadow-md transition-all duration-200">
            <span class="flex items-center justify-center w-12 h-12 shrink-0 rounded-xl bg-yellow-100 text-yellow-600 text-xl"><i class="bi bi-exclamation-circle"></i></span>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Poor Condition</div>
                <div class="text-2xl font-extrabold text-gray-800"><?= number_format($flaggedConditions['poor'] ?? 0) ?></div>
            </div>
        </a>
        <a href="index.php?page=assets&sub=list_all&condition=damaged" class="card-panel p-5 flex items-center gap-4 hover:shadow-md transition-all duration-200">
            <span class="flex items-center justify-center w-12 h-12 shrink-0 rounded-xl bg-red-100 text-red-600 text-xl"><i class="bi bi-tools"></i></span>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Damaged</div>
                <div class="text-2xl font-extrabold text-gray-800"><?= number_format($flaggedConditions['damaged'] ?? 0) ?></div>
            </div>
        </a>
        <a href="index.php?page=assets&sub=list_all&condition=obsolete" class="card-panel p-5 flex items-center gap-4 hover:shadow-md transition-all duration-200">
            <span class="flex items-center justify-center w-12 h-12 shrink-0 rounded-xl bg-gray-100 text-gray-500 text-xl"><i class="bi bi-archive"></i></span>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Obsolete</div>
                <div class="text-2xl font-extrabold text-gray-800"><?= number_format($flaggedConditions['obsolete'] ?? 0) ?></div>
            </div>
        </a>
    </div>
</div>

<!-- Recently Added Assets (light pulse, not the main focus) -->
<div class="card-panel p-5">
    <div class="flex flex-wrap items-center justify-between border-b border-gray-200 pb-3 mb-4">
        <h6 class="font-semibold text-gray-700 flex items-center gap-2">
            <i class="bi bi-table text-gray-400"></i> Recently Added Assets
        </h6>
        <a href="index.php?page=assets&sub=browse" class="btn-app btn-app-sm btn-app-primary">Asset Records</a>
    </div>
    <div class="table-app-wrap rounded-xl border border-gray-200 overflow-x-auto">
        <table class="table-app w-full text-sm table-fixed min-w-[600px]">
            <colgroup>
                <col style="width:18%">
                <col style="width:32%">
                <col style="width:16%">
                <col style="width:16%">
                <col style="width:18%">
            </colgroup>
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                    <th class="px-4 py-3 text-left font-semibold">Asset Code</th>
                    <th class="px-4 py-3 text-left font-semibold">Asset Name</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                    <th class="px-4 py-3 text-left font-semibold">Condition</th>
                    <th class="px-4 py-3 text-left font-semibold">Date Added</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($recentAssets)): ?>
                    <tr><td colspan="5" class="px-4 py-8"><div class="table-empty text-center text-gray-400">No assets found.</div></td></tr>
                <?php else: ?>
                    <?php foreach ($recentAssets as $asset): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-800 truncate" title="<?= htmlspecialchars($asset['asset_code']) ?>"><?= htmlspecialchars($asset['asset_code']) ?></td>
                            <td class="px-4 py-3 truncate" title="<?= htmlspecialchars($asset['asset_name']) ?>"><?= htmlspecialchars($asset['asset_name']) ?></td>
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