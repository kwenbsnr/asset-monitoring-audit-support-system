<?php if (!defined('APP_START')) exit;
$flashType = $_SESSION['flash_type'] ?? 'success';
$alertClass = $flashType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
        <h4 class="text-xl font-bold text-green-700 flex items-center gap-2">
            <i class="bi bi-people"></i> User Management
        </h4>
        <a href="index.php?page=users&sub=add" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700">
            <i class="bi bi-plus-circle"></i> Add User
        </a>
    </div>
    <div class="p-6">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="mb-4 p-3 rounded border <?= $alertClass ?> flex justify-between items-center">
                <span><?= htmlspecialchars($_SESSION['flash']) ?></span>
                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="this.parentElement.remove()">&times;</button>
            </div>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border border-gray-200">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-2 border-b text-left font-medium">ID</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Username</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Full Name</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Role</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Office</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Status</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Last Login</th>
                        <th class="px-4 py-2 border-b text-center font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-gray-500">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2"><?= $u['users_id'] ?></td>
                                <td class="px-4 py-2 font-medium text-gray-800"><?= htmlspecialchars($u['username']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($u['full_name']) ?></td>
                                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $u['role'] === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' ?>"><?= $u['role'] ?></span></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($u['office_name'] ?? 'N/A') ?></td>
                                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $u['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                <td class="px-4 py-2"><?= $u['last_login'] ?? 'Never' ?></td>
                                <td class="px-4 py-2 text-center whitespace-nowrap">
                                    <a href="index.php?page=users&sub=edit&id=<?= $u['users_id'] ?>" class="px-2 py-1 text-yellow-600 border border-yellow-300 rounded hover:bg-yellow-50 text-xs"><i class="bi bi-pencil"></i></a>
                                    <a href="index.php?page=users&sub=delete&id=<?= $u['users_id'] ?>" class="px-2 py-1 text-red-600 border border-red-300 rounded hover:bg-red-50 text-xs" onclick="return confirm('Deactivate this user?')"><i class="bi bi-person-slash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>