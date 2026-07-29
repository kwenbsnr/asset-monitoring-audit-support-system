<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow">
    <div class="card-header">
        <h4><i class="bi bi-clock-history"></i> Audit Trail</h4>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" action="index.php" class="row g-2 mb-3">
            <input type="hidden" name="page" value="audit">
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="action_type">
                    <option value="">Action Type</option>
                    <?php foreach ($actionTypes as $at): ?>
                        <option value="<?= $at['action_type'] ?>" <?= isset($_GET['action_type']) && $_GET['action_type'] == $at['action_type'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($at['action_type']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="module">
                    <option value="">Module</option>
                    <?php foreach ($modules as $m): ?>
                        <option value="<?= $m['module'] ?>" <?= isset($_GET['module']) && $_GET['module'] == $m['module'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['module']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="user_id">
                    <option value="">User</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['users_id'] ?>" <?= isset($_GET['user_id']) && $_GET['user_id'] == $u['users_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="asset_id">
                    <option value="">Asset</option>
                    <?php foreach ($assets as $a): ?>
                        <option value="<?= $a['asset_id'] ?>" <?= isset($_GET['asset_id']) && $_GET['asset_id'] == $a['asset_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['asset_code']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>" placeholder="From">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>" placeholder="To">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel"></i> Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
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
                        <tr><td colspan="6" class="text-center">No audit logs found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['performed_at']) ?></td>
                                <td><?= htmlspecialchars($log['performed_by_username']) ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($log['action_type']) ?></span></td>
                                <td><?= htmlspecialchars($log['module']) ?></td>
                                <td><?= htmlspecialchars($log['asset_code'] ?? 'N/A') ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="alert('Previous: <?= htmlspecialchars($log['previous_values'] ?? '') ?>\nNew: <?= htmlspecialchars($log['new_values'] ?? '') ?>')">View</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>