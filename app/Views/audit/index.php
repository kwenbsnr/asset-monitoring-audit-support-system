<?php if (!defined('APP_START')) exit;
// Dynamic alert class to avoid linter conflict
$flashType = $_SESSION['flash_type'] ?? 'success';
$alertClass = $flashType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 px-6 py-4">
        <h4 class="text-xl font-bold text-gray-800 flex items-center gap-2">
            <i class="bi bi-clock-history"></i> Audit Trail
        </h4>
    </div>
    <div class="p-6">
        <!-- Filters -->
        <form method="GET" action="index.php" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 mb-4">
            <input type="hidden" name="page" value="audit">
            <div>
                <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm focus:ring-1 focus:ring-green-500 focus:border-green-500" name="action_type">
                    <option value="">Action Type</option>
                    <?php foreach ($actionTypes as $at): ?>
                        <option value="<?= $at['action_type'] ?>" <?= isset($_GET['action_type']) && $_GET['action_type'] == $at['action_type'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($at['action_type']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm focus:ring-1 focus:ring-green-500 focus:border-green-500" name="module">
                    <option value="">Module</option>
                    <?php foreach ($modules as $m): ?>
                        <option value="<?= $m['module'] ?>" <?= isset($_GET['module']) && $_GET['module'] == $m['module'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['module']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm focus:ring-1 focus:ring-green-500 focus:border-green-500" name="user_id">
                    <option value="">User</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['users_id'] ?>" <?= isset($_GET['user_id']) && $_GET['user_id'] == $u['users_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm focus:ring-1 focus:ring-green-500 focus:border-green-500" name="asset_id">
                    <option value="">Asset</option>
                    <?php foreach ($assets as $a): ?>
                        <option value="<?= $a['asset_id'] ?>" <?= isset($_GET['asset_id']) && $_GET['asset_id'] == $a['asset_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['asset_code']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <input type="date" class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm focus:ring-1 focus:ring-green-500 focus:border-green-500" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>" placeholder="From">
            </div>
            <div>
                <input type="date" class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm focus:ring-1 focus:ring-green-500 focus:border-green-500" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>" placeholder="To">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-3 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"><i class="bi bi-funnel"></i> Filter</button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border border-gray-200">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-2 border-b text-left font-medium">Date</th>
                        <th class="px-4 py-2 border-b text-left font-medium">User</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Action</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Module</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Asset</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Changes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-gray-500">No audit logs found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2"><?= htmlspecialchars($log['performed_at']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($log['performed_by_username']) ?></td>
                                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><?= htmlspecialchars($log['action_type']) ?></span></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($log['module']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($log['asset_code'] ?? 'N/A') ?></td>
                                <td class="px-4 py-2">
                                    <button class="px-2 py-0.5 text-xs border border-gray-300 rounded hover:bg-gray-100" onclick="alert('Previous: <?= htmlspecialchars($log['previous_values'] ?? '') ?>\nNew: <?= htmlspecialchars($log['new_values'] ?? '') ?>')">View</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>