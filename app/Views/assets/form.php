<?php if (!defined('APP_START')) exit;

$data = $_SESSION['form_data'] ?? ($asset ?? []);
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);

$isEdit = $isEdit ?? false;
$assetId = $asset['asset_id'] ?? 0;
?>
<!--
    Modal fragment: this file is fetched via AJAX by public/js/modal-forms.js
    and injected into #formModal .modal-body — no card-panel/page chrome
    here, the shared modal shell in main.php already provides the header
    and Close button. Non-AJAX (JS-disabled / direct link) requests still
    reach AssetController::add()/edit(), which wraps this same file in the
    full layout instead — this fragment renders identically either way.
-->
<?php if (!empty($errors)): ?>
    <div class="alert-app alert-app-danger alert-app-top">
        <ul class="list-disc list-inside"><?php foreach ($errors as $err) echo '<li>'.htmlspecialchars($err).'</li>'; ?></ul>
    </div>
<?php endif; ?>

<div>
    <form method="POST" action="index.php?page=assets&sub=save" id="assetForm">
            <?php if ($isEdit): ?>
                <input type="hidden" name="asset_id" value="<?= $asset['asset_id'] ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="asset_code" class="block text-sm font-medium text-gray-700">Asset Code *</label>
                    <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="asset_code" name="asset_code"
                           value="<?= htmlspecialchars($data['asset_code'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="asset_accounts_id" class="block text-sm font-medium text-gray-700">Account *</label>
                    <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="asset_accounts_id" name="asset_accounts_id" required>
                        <option value="">Select Account</option>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc['asset_accounts_id'] ?>"
                                data-code="<?= htmlspecialchars($acc['account_code']) ?>"
                                <?= (isset($data['asset_accounts_id']) && $data['asset_accounts_id'] == $acc['asset_accounts_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($acc['account_code'] . ' - ' . $acc['account_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label for="asset_name" class="block text-sm font-medium text-gray-700">Asset Name *</label>
                <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="asset_name" name="asset_name"
                       value="<?= htmlspecialchars($data['asset_name'] ?? '') ?>" required>
                <p class="mt-1 text-xs text-gray-500" id="accountSuggestionHint"></p>
            </div>

            <div class="mt-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Additional Description</label>
                <textarea class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="description" name="description" rows="2"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div>
                    <label for="brand" class="block text-sm font-medium text-gray-700">Brand</label>
                    <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="brand" name="brand"
                           value="<?= htmlspecialchars($data['brand'] ?? '') ?>">
                </div>
                <div>
                    <label for="model" class="block text-sm font-medium text-gray-700">Model</label>
                    <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="model" name="model"
                           value="<?= htmlspecialchars($data['model'] ?? '') ?>">
                </div>
                <div>
                    <label for="serial_number" class="block text-sm font-medium text-gray-700">Serial Number</label>
                    <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="serial_number" name="serial_number"
                           value="<?= htmlspecialchars($data['serial_number'] ?? '') ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label for="acquisition_cost" class="block text-sm font-medium text-gray-700">Acquisition Cost (₱) *</label>
                    <input type="number" step="0.01" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="acquisition_cost" name="acquisition_cost"
                        value="<?= htmlspecialchars($data['acquisition_cost'] ?? '') ?>"
                        min="50000" required
                        placeholder="Minimum ₱50,000.00">
                    <p class="text-xs text-gray-500 mt-1">For PPE registration, acquisition cost must be at least ₱50,000.00.</p>
                </div>
                <div>
                    <label for="acquisition_date" class="block text-sm font-medium text-gray-700">Acquisition Date</label>
                    <input type="date" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="acquisition_date" name="acquisition_date"
                           value="<?= htmlspecialchars($data['acquisition_date'] ?? '') ?>"
                           min="1990-01-01" max="<?= \date('Y-m-d') ?>">
                    <p class="text-xs text-gray-500 mt-1" id="dateWarning"></p>
                </div>
            </div>

            <?php if ($isEdit): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="status" name="status">
                        <?php foreach ($statusOptions as $opt): ?>
                            <option value="<?= $opt ?>"
                                <?= (isset($data['status']) && $data['status'] == $opt) ? 'selected' : '' ?>>
                                <?= ucfirst($opt) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="condition" class="block text-sm font-medium text-gray-700">Condition</label>
                    <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="condition" name="condition">
                        <?php foreach ($conditionOptions as $opt): ?>
                            <option value="<?= $opt ?>"
                                <?= (isset($data['condition']) && $data['condition'] == $opt) ? 'selected' : '' ?>>
                                <?= ucfirst($opt) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php endif; ?>

            <div class="mt-4">
                <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                <textarea class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="remarks" name="remarks" rows="2"><?= htmlspecialchars($data['remarks'] ?? '') ?></textarea>
            </div>

            <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
                <button type="button" class="btn-app btn-app-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn-app btn-app-primary"><?= $isEdit ? 'Update' : 'Create' ?> Asset</button>
            </div>
        </form>
</div>

<!--
 <div class="mt-4">
                <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                <textarea class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="remarks" name="remarks" rows="2"><?= htmlspecialchars($data['remarks'] ?? '') ?></textarea>
            </div>

            <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
                <button type="button" class="btn-app btn-app-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn-app btn-app-primary"><?= $isEdit ? 'Update' : 'Create' ?> Asset</button>
            </div>
        </form>
</div>
                    