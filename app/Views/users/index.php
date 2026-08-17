<?php if (!defined('APP_START')) exit;
$flashType = $_SESSION['flash_type'] ?? 'success';
$alertClass = $flashType === 'success' ? 'alert-app-success' : 'alert-app-danger';
?>
<div class="card-panel">
    <div class="card-panel-header">
        <div class="flex items-center gap-3">
            <span class="page-icon"><i class="bi bi-people"></i></span>
            <span class="page-title">User Management</span>
        </div>
        <button type="button" class="btn-app btn-app-primary" data-form-modal
                data-form-url="index.php?page=users&sub=add"
                data-form-title="Add User"
                data-form-init="initUserForm">
            <i class="bi bi-plus-circle"></i> Add User
        </button>
    </div>
    <div class="card-panel-body">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert-app <?= $alertClass ?>">
                <span><?= htmlspecialchars($_SESSION['flash']) ?></span>
                <button type="button" class="alert-app-close" onclick="this.closest('.alert-app').remove()">&times;</button>
            </div>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <div class="table-app-wrap">
            <table class="table-app">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Office</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="8"><div class="table-empty">No users found.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="text-gray-500"><?= $u['users_id'] ?></td>
                                <td class="font-medium text-gray-800"><?= htmlspecialchars($u['username']) ?></td>
                                <td><?= htmlspecialchars($u['full_name']) ?></td>
                                <td><span class="badge-app <?= $u['role'] === 'admin' ? 'badge-app-info' : 'badge-app-neutral' ?>"><?= $u['role'] ?></span></td>
                                <td><?= htmlspecialchars($u['office_name'] ?? 'N/A') ?></td>
                                <td><span class="badge-app <?= $u['is_active'] ? 'badge-app-success' : 'badge-app-danger' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                <td><?= $u['last_login'] ?? 'Never' ?></td>
                                <td class="text-center whitespace-nowrap">
                                    <button type="button" class="btn-app btn-app-sm btn-app-outline-warning" title="Edit" data-form-modal
                                            data-form-url="index.php?page=users&sub=edit&id=<?= $u['users_id'] ?>"
                                            data-form-title="Edit User"
                                            data-form-init="initUserForm">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="index.php?page=users&sub=delete&id=<?= $u['users_id'] ?>" class="btn-app btn-app-sm btn-app-outline-danger" title="Deactivate" onclick="return confirm('Deactivate this user?')"><i class="bi bi-person-slash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>