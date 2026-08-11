<?php if (!defined('APP_START')) exit; ?>
<div class="card-panel">
    <div class="card-panel-header">
        <div class="flex items-center gap-3">
            <span class="page-icon"><i class="bi bi-clock-history"></i></span>
            <span class="page-title">Audit Trail</span>
        </div>
    </div>
    <div class="card-panel-body">
        <!-- Filters -->
        <form method="GET" action="index.php" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 mb-4">
            <input type="hidden" name="page" value="audit">
            <div>
                <select class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="action_type">
                    <option value="">Action Type</option>
                    <?php foreach ($actionTypes as $at): ?>
                        <option value="<?= $at['action_type'] ?>" <?= isset($_GET['action_type']) && $_GET['action_type'] == $at['action_type'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($at['action_type']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="module">
                    <option value="">Module</option>
                    <?php foreach ($modules as $m): ?>
                        <option value="<?= $m['module'] ?>" <?= isset($_GET['module']) && $_GET['module'] == $m['module'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['module']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="user_id">
                    <option value="">User</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['users_id'] ?>" <?= isset($_GET['user_id']) && $_GET['user_id'] == $u['users_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="asset_id">
                    <option value="">Asset</option>
                    <?php foreach ($assets as $a): ?>
                        <option value="<?= $a['asset_id'] ?>" <?= isset($_GET['asset_id']) && $_GET['asset_id'] == $a['asset_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['asset_code']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <input type="date" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>" placeholder="From">
            </div>
            <div>
                <input type="date" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>" placeholder="To">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full btn-app btn-app-primary"><i class="bi bi-funnel"></i> Filter</button>
            </div>
        </form>

        <div class="table-app-wrap">
            <table class="table-app">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Asset</th>
                        <th>Changes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="6"><div class="table-empty">No audit logs found.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['performed_at']) ?></td>
                                <td><?= htmlspecialchars($log['performed_by_username']) ?></td>
                                <td><span class="badge-app badge-app-info"><?= htmlspecialchars($log['action_type']) ?></span></td>
                                <td><?= htmlspecialchars($log['module']) ?></td>
                                <td><?= htmlspecialchars($log['asset_code'] ?? 'N/A') ?></td>
                                <td>
                                    <button class="btn-app btn-app-sm btn-app-outline" onclick="alert('Previous: <?= htmlspecialchars($log['previous_values'] ?? '') ?>\nNew: <?= htmlspecialchars($log['new_values'] ?? '') ?>')">View</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>