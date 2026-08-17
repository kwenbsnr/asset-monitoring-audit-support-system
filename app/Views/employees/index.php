<?php if (!defined('APP_START')) exit;
$flashType = $_SESSION['flash_type'] ?? 'success';
$alertClass = $flashType === 'success' ? 'alert-app-success' : 'alert-app-danger';

$statusBadge = [
    'active'      => 'badge-app-success',
    'retired'     => 'badge-app-info',
    'transferred' => 'badge-app-warning',
    'inactive'    => 'badge-app-danger',
];
$statusLabel = [
    'active'      => 'Active',
    'retired'     => 'Retired',
    'transferred' => 'Transferred',
    'inactive'    => 'Inactive',
];
?>
<div class="card-panel">
    <div class="card-panel-header">
        <div class="flex items-center gap-3">
            <span class="page-icon"><i class="bi bi-person-badge"></i></span>
            <span class="page-title">Employee Management</span>
        </div>
        <button type="button" class="btn-app btn-app-primary" data-form-modal
                data-form-url="index.php?page=employees&sub=add"
                data-form-title="Add Employee"
                data-form-init="initEmployeeForm">
            <i class="bi bi-plus-circle"></i> Add Employee
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

        <!-- Filters -->
        <form method="GET" action="index.php" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <input type="hidden" name="page" value="employees">
            <div class="md:col-span-2">
                <input type="text" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition"
                       name="search" placeholder="Search by name, employee ID, or position..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
            <div>
                <select class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="status">
                    <option value="">All Statuses</option>
                    <?php foreach ($statusLabel as $val => $label): ?>
                        <option value="<?= $val ?>" <?= (($_GET['status'] ?? '') === $val) ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-2">
                <select class="flex-1 border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="office_id">
                    <option value="">All Offices</option>
                    <?php foreach ($offices as $o): ?>
                        <option value="<?= $o['office_id'] ?>" <?= (($_GET['office_id'] ?? '') == $o['office_id']) ? 'selected' : '' ?>><?= htmlspecialchars($o['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-app btn-app-primary"><i class="bi bi-funnel"></i></button>
                <?php if (!empty($_GET['search']) || !empty($_GET['status']) || !empty($_GET['office_id'])): ?>
                    <a href="index.php?page=employees" class="btn-app btn-app-outline"><i class="bi bi-x-circle"></i></a>
                <?php endif; ?>
            </div>
        </form>

        <div class="table-app-wrap">
            <table class="table-app">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Full Name</th>
                        <th>Position</th>
                        <th>Office</th>
                        <th>Salary Grade</th>
                        <th>Assets Held</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($employees)): ?>
                        <tr><td colspan="8"><div class="table-empty">No employee profiles found.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($employees as $e): ?>
                            <tr>
                                <td class="font-medium text-gray-800"><?= htmlspecialchars($e['employee_id']) ?></td>
                                <td><?= htmlspecialchars($e['full_name']) ?></td>
                                <td><?= htmlspecialchars($e['position'] ?? '') ?></td>
                                <td><?= htmlspecialchars($e['office_name'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge-app badge-app-purple">SG <?= (int)$e['salary_grade'] ?></span>
                                </td>
                                <td><?= (int)$e['active_asset_count'] ?></td>
                                <td>
                                    <span class="badge-app <?= $statusBadge[$e['employment_status']] ?? 'badge-app-neutral' ?>">
                                        <?= $statusLabel[$e['employment_status']] ?? ucfirst($e['employment_status']) ?>
                                    </span>
                                </td>
                                <td class="text-center whitespace-nowrap">
                                    <button type="button" class="btn-app btn-app-sm btn-app-outline-warning" title="Edit" data-form-modal
                                            data-form-url="index.php?page=employees&sub=edit&id=<?= $e['personnel_id'] ?>"
                                            data-form-title="Edit Employee"
                                            data-form-init="initEmployeeForm">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button"
                                            class="status-btn btn-app btn-app-sm btn-app-outline"
                                            data-id="<?= $e['personnel_id'] ?>"
                                            data-name="<?= htmlspecialchars($e['full_name'], ENT_QUOTES) ?>"
                                            data-status="<?= htmlspecialchars($e['employment_status'], ENT_QUOTES) ?>"
                                            data-assets="<?= (int)$e['active_asset_count'] ?>">
                                        <i class="bi bi-arrow-repeat"></i> Status
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/status_modal.php'; ?>