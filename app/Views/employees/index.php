<?php if (!defined('APP_START')) exit;
$flashType = $_SESSION['flash_type'] ?? 'success';
$alertClass = $flashType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';

$statusBadge = [
    'active'      => 'bg-green-100 text-green-800',
    'retired'     => 'bg-blue-100 text-blue-800',
    'transferred' => 'bg-yellow-100 text-yellow-800',
    'inactive'    => 'bg-red-100 text-red-800',
];
$statusLabel = [
    'active'      => 'Active',
    'retired'     => 'Retired',
    'transferred' => 'Transferred',
    'inactive'    => 'Inactive',
];
?>
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="border-b border-gray-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
        <h4 class="text-xl font-bold text-green-700 flex items-center gap-2">
            <i class="bi bi-person-badge"></i> Employee Management
        </h4>
        <a href="index.php?page=employees&sub=add" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700">
            <i class="bi bi-plus-circle"></i> Add Employee
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

        <!-- Filters -->
        <form method="GET" action="index.php" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <input type="hidden" name="page" value="employees">
            <div class="md:col-span-2">
                <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:ring-1 focus:ring-green-500 focus:border-green-500"
                       name="search" placeholder="Search by name, employee ID, or position..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
            <div>
                <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm focus:ring-1 focus:ring-green-500 focus:border-green-500" name="status">
                    <option value="">All Statuses</option>
                    <?php foreach ($statusLabel as $val => $label): ?>
                        <option value="<?= $val ?>" <?= (($_GET['status'] ?? '') === $val) ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-2">
                <select class="flex-1 border border-gray-300 rounded-md px-2 py-1.5 text-sm focus:ring-1 focus:ring-green-500 focus:border-green-500" name="office_id">
                    <option value="">All Offices</option>
                    <?php foreach ($offices as $o): ?>
                        <option value="<?= $o['office_id'] ?>" <?= (($_GET['office_id'] ?? '') == $o['office_id']) ? 'selected' : '' ?>><?= htmlspecialchars($o['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"><i class="bi bi-funnel"></i></button>
                <?php if (!empty($_GET['search']) || !empty($_GET['status']) || !empty($_GET['office_id'])): ?>
                    <a href="index.php?page=employees" class="px-3 py-1.5 bg-gray-300 text-gray-800 text-sm rounded hover:bg-gray-400"><i class="bi bi-x-circle"></i></a>
                <?php endif; ?>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border border-gray-200">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-2 border-b text-left font-medium">Employee ID</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Full Name</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Position</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Office</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Salary Grade</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Assets Held</th>
                        <th class="px-4 py-2 border-b text-left font-medium">Status</th>
                        <th class="px-4 py-2 border-b text-center font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($employees)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-gray-500">No employee profiles found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($employees as $e): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium text-gray-800"><?= htmlspecialchars($e['employee_id']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($e['full_name']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($e['position'] ?? '') ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($e['office_name'] ?? 'N/A') ?></td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">SG <?= (int)$e['salary_grade'] ?></span>
                                </td>
                                <td class="px-4 py-2"><?= (int)$e['active_asset_count'] ?></td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $statusBadge[$e['employment_status']] ?? 'bg-gray-100 text-gray-800' ?>">
                                        <?= $statusLabel[$e['employment_status']] ?? ucfirst($e['employment_status']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-center whitespace-nowrap">
                                    <a href="index.php?page=employees&sub=edit&id=<?= $e['personnel_id'] ?>" class="px-2 py-1 text-yellow-600 border border-yellow-300 rounded hover:bg-yellow-50 text-xs"><i class="bi bi-pencil"></i></a>
                                    <button type="button"
                                            class="status-btn px-2 py-1 text-gray-600 border border-gray-300 rounded hover:bg-gray-100 text-xs"
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
