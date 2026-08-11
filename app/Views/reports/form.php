<?php if (!defined('APP_START')) exit;
$data = $_SESSION['form_data'] ?? [];
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);
?>
<div class="card-panel">
    <div class="card-panel-header card-panel-header-solo">
        <span class="page-icon"><i class="bi bi-file-earmark-plus"></i></span>
        <span class="page-title">Create Report</span>
    </div>
    <div class="card-panel-body">
        <?php if (!empty($errors)): ?>
            <div class="alert-app alert-app-danger alert-app-top">
                <ul class="list-disc list-inside"><?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul>
            </div>
        <?php endif; ?>
        <form method="POST" action="index.php?page=reports&sub=save" id="reportForm">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Report Number *</label>
                    <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="report_number" value="<?= htmlspecialchars($data['report_number'] ?? 'RPT-'.date('Ymd').'-001') ?>" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Report Date *</label>
                    <input type="date" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="report_date" value="<?= htmlspecialchars($data['report_date'] ?? date('Y-m-d')) ?>" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Office *</label>
                    <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="office_id" required>
                        <option value="">Select Office</option>
                        <?php foreach ($offices as $o): ?>
                            <option value="<?= $o['office_id'] ?>" <?= (isset($data['office_id']) && $data['office_id'] == $o['office_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($o['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Prepared By *</label>
                    <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="prepared_by" required>
                        <option value="">Select User</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['users_id'] ?>" <?= (isset($data['prepared_by']) && $data['prepared_by'] == $u['users_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="status">
                        <option value="draft" <?= (isset($data['status']) && $data['status'] == 'draft') ? 'selected' : '' ?>>Draft</option>
                        <option value="submitted" <?= (isset($data['status']) && $data['status'] == 'submitted') ? 'selected' : '' ?>>Submitted</option>
                    </select>
                </div>
                <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Remarks</label>
                    <textarea class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="remarks" rows="2"><?= htmlspecialchars($data['remarks'] ?? '') ?></textarea>
                </div>
            </div>

            <h6 class="font-semibold text-gray-800 border-b border-gray-200 pb-2 mt-6">Report Items</h6>
            <div id="itemsContainer" class="mt-3 space-y-2">
                <div class="item-row grid grid-cols-1 md:grid-cols-5 gap-2">
                    <div>
                        <select class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="items[0][asset_id]">
                            <option value="">Select Asset</option>
                            <?php foreach ($assets as $a): ?>
                                <option value="<?= $a['asset_id'] ?>">
                                    <?= htmlspecialchars($a['asset_code'] . ' - ' . ($a['asset_name'] ?? $a['description'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <select class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="items[0][verification_status]">
                            <option value="pending">Pending</option>
                            <option value="verified">Verified</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div>
                        <select class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="items[0][asset_condition]">
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                            <option value="damaged">Damaged</option>
                            <option value="obsolete">Obsolete</option>
                        </select>
                    </div>
                    <div>
                        <select class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="items[0][verified_by]">
                            <option value="">Verifier</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['users_id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <input type="text" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="items[0][remarks]" placeholder="Remarks">
                    </div>
                </div>
            </div>
            <button type="button" class="mt-3 btn-app btn-app-sm btn-app-outline" onclick="addItem()"><i class="bi bi-plus-circle"></i> Add Item</button>

            <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
                <a href="index.php?page=reports" class="btn-app btn-app-outline">Cancel</a>
                <button type="submit" class="btn-app btn-app-primary">Create Report</button>
            </div>
        </form>
    </div>
</div>

<script>
    let itemCount = 1;
    function addItem() {
        const container = document.getElementById('itemsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'item-row grid grid-cols-1 md:grid-cols-5 gap-2';
        newRow.innerHTML = `
            <div>
                <select class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="items[${itemCount}][asset_id]">
                    <option value="">Select Asset</option>
                    <?php foreach ($assets as $a): ?>
                        <option value="<?= $a['asset_id'] ?>">
                            <?= htmlspecialchars($a['asset_code'] . ' - ' . ($a['asset_name'] ?? $a['description'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="items[${itemCount}][verification_status]">
                    <option value="pending">Pending</option>
                    <option value="verified">Verified</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div>
                <select class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="items[${itemCount}][asset_condition]">
                    <option value="good">Good</option>
                    <option value="fair">Fair</option>
                    <option value="poor">Poor</option>
                    <option value="damaged">Damaged</option>
                    <option value="obsolete">Obsolete</option>
                </select>
            </div>
            <div>
                <select class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="items[${itemCount}][verified_by]">
                    <option value="">Verifier</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['users_id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <input type="text" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="items[${itemCount}][remarks]" placeholder="Remarks">
            </div>
        `;
        container.appendChild(newRow);
        itemCount++;
    }
</script>