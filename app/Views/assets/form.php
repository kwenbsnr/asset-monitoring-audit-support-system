<?php if (!defined('APP_START')) exit;

$data = $_SESSION['form_data'] ?? ($asset ?? []);
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);

$isEdit = $isEdit ?? false;
$title = $isEdit ? 'Edit Asset' : 'Add New Asset';
$assetId = $asset['asset_id'] ?? 0;
?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Left column: Form -->
    <div class="md:col-span-2">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="border-b border-gray-200 px-6 py-4">
                <h4 class="text-xl font-bold text-gray-800"><?= $title ?></h4>
            </div>
            <div class="p-6">
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded mb-4">
                        <ul class="list-disc list-inside"><?php foreach ($errors as $err) echo '<li>'.htmlspecialchars($err).'</li>'; ?></ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?page=assets&sub=save">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="asset_id" value="<?= $asset['asset_id'] ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="asset_code" class="block text-sm font-medium text-gray-700">Asset Code *</label>
                            <input type="text" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="asset_code" name="asset_code"
                                   value="<?= htmlspecialchars($data['asset_code'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label for="asset_accounts_id" class="block text-sm font-medium text-gray-700">Account *</label>
                            <select class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="asset_accounts_id" name="asset_accounts_id" required>
                                <option value="">Select Account</option>
                                <?php foreach ($accounts as $acc): ?>
                                    <option value="<?= $acc['asset_accounts_id'] ?>"
                                        <?= (isset($data['asset_accounts_id']) && $data['asset_accounts_id'] == $acc['asset_accounts_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($acc['account_code'] . ' - ' . $acc['account_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="asset_name" class="block text-sm font-medium text-gray-700">Asset Name *</label>
                        <input type="text" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="asset_name" name="asset_name"
                               value="<?= htmlspecialchars($data['asset_name'] ?? '') ?>" required>
                    </div>

                    <div class="mt-4">
                        <label for="description" class="block text-sm font-medium text-gray-700">Additional Description</label>
                        <textarea class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="description" name="description" rows="2"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <label for="brand" class="block text-sm font-medium text-gray-700">Brand</label>
                            <input type="text" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="brand" name="brand"
                                   value="<?= htmlspecialchars($data['brand'] ?? '') ?>">
                        </div>
                        <div>
                            <label for="model" class="block text-sm font-medium text-gray-700">Model</label>
                            <input type="text" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="model" name="model"
                                   value="<?= htmlspecialchars($data['model'] ?? '') ?>">
                        </div>
                        <div>
                            <label for="serial_number" class="block text-sm font-medium text-gray-700">Serial Number</label>
                            <input type="text" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="serial_number" name="serial_number"
                                   value="<?= htmlspecialchars($data['serial_number'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="acquisition_cost" class="block text-sm font-medium text-gray-700">Acquisition Cost (₱) *</label>
                            <input type="number" step="0.01" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="acquisition_cost" name="acquisition_cost"
                                value="<?= htmlspecialchars($data['acquisition_cost'] ?? '') ?>"
                                min="50000" required
                                placeholder="Minimum ₱50,000.00">
                            <p class="text-xs text-gray-500 mt-1">For PPE registration, acquisition cost must be at least ₱50,000.00.</p>
                        </div>
                        <div>
                            <label for="acquisition_date" class="block text-sm font-medium text-gray-700">Acquisition Date</label>
                            <input type="date" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="acquisition_date" name="acquisition_date"
                                   value="<?= htmlspecialchars($data['acquisition_date'] ?? '') ?>">
                        </div>
                    </div>

                    <?php if ($isEdit): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="status" name="status">
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
                            <select class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="condition" name="condition">
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
                        <textarea class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="remarks" name="remarks" rows="2"><?= htmlspecialchars($data['remarks'] ?? '') ?></textarea>
                    </div>

                    <!-- Optional Custodian Assignment -->
                    <div class="mt-6 border-t border-gray-200 pt-4">
                        <div class="flex items-center gap-2 mb-3">
                            <input type="checkbox" id="assignCustodianToggle" name="assign_custodian" value="1" 
                                <?= (isset($data['assign_custodian']) && $data['assign_custodian'] == '1') ? 'checked' : '' ?>
                                class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                            <label for="assignCustodianToggle" class="font-semibold text-gray-700">
                                <i class="bi bi-person-check"></i> Assign Custodian (Optional)
                            </label>
                        </div>
                        <div id="custodianSection" style="<?= (isset($data['assign_custodian']) && $data['assign_custodian'] == '1') ? 'display:block;' : 'display:none;' ?>">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="custodian_id" class="block text-sm font-medium text-gray-700">Custodian</label>
                                    <select class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="custodian_id" name="custodian_id">
                                        <option value="">Select Custodian</option>
                                        <?php foreach ($personnel as $p): ?>
                                            <option value="<?= $p['personnel_id'] ?>" 
                                                    data-office-id="<?= $p['office_id'] ?>"
                                                <?= (isset($data['custodian_id']) && $data['custodian_id'] == $p['personnel_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p['full_name']) ?> (<?= htmlspecialchars($p['position']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="office_id" class="block text-sm font-medium text-gray-700">Office</label>
                                    <select class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="office_id" name="office_id">
                                        <option value="">Select Office</option>
                                        <?php foreach ($offices as $o): ?>
                                            <option value="<?= $o['office_id'] ?>" 
                                                <?= (isset($data['office_id']) && $data['office_id'] == $o['office_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($o['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="effectivity_date" class="block text-sm font-medium text-gray-700">Effectivity Date</label>
                                    <input type="date" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="effectivity_date" name="effectivity_date" 
                                        value="<?= htmlspecialchars($data['effectivity_date'] ?? date('Y-m-d')) ?>">
                                </div>
                                <div>
                                    <label for="accountability_document" class="block text-sm font-medium text-gray-700">Accountability Document</label>
                                    <input type="text" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="accountability_document" name="accountability_document" 
                                        value="<?= htmlspecialchars($data['accountability_document'] ?? '') ?>" placeholder="e.g., PAR, ICS">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="accountability_reference" class="block text-sm font-medium text-gray-700">Reference Number</label>
                                    <input type="text" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" id="accountability_reference" name="accountability_reference" 
                                        value="<?= htmlspecialchars($data['accountability_reference'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
                        <a href="index.php?page=assets&sub=browse" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Cancel</a>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"><?= $isEdit ? 'Update' : 'Create' ?> Asset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right column: QR Preview -->
    <div class="md:col-span-1">
        <?php if ($isEdit && $assetId): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                <h5 class="font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">QR Code</h5>
                <img src="index.php?page=assets&sub=qr&id=<?= $assetId ?>" alt="QR Code" class="mx-auto max-w-[200px] border border-gray-200 p-2 rounded">
                <p class="text-xs text-gray-500 mt-3">
                    <i class="bi bi-info-circle"></i> 
                    The QR code is linked to this asset record.<br>
                    Print and affix it to the physical asset.
                </p>
                <div class="mt-4 space-y-2">
                    <button class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" onclick="window.open('index.php?page=assets&sub=qr&id=<?= $assetId ?>&download=1')">
                        <i class="bi bi-download"></i> Download PNG
                    </button>
                    <button class="w-full px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700" onclick="printQR(<?= $assetId ?>)">
                        <i class="bi bi-printer"></i> Print QR Label
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-6 text-center text-gray-500">
                <i class="bi bi-qr-code text-6xl"></i>
                <p class="mt-2">QR code will appear here<br>after saving the asset.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function printQR(assetId) {
        var win = window.open('', '_blank');
        win.document.write('<!DOCTYPE html><html><head><title>QR Label</title>');
        win.document.write('<style>body { text-align: center; margin-top: 50px; } img { max-width: 300px; }</style>');
        win.document.write('</head><body>');
        win.document.write('<h3>NIA Regional Office IX</h3>');
        win.document.write('<p>Asset QR Code</p>');
        win.document.write('<img src="index.php?page=assets&sub=qr&id=' + assetId + '">');
        win.document.write('</body></html>');
        win.document.close();
        win.onload = function() { win.print(); };
    }
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('assignCustodianToggle');
    const section = document.getElementById('custodianSection');
    if (toggle) {
        toggle.addEventListener('change', function() {
            section.style.display = this.checked ? 'block' : 'none';
        });
    }

    // Office ↔ Custodian auto-fill
    const custodianSelect = document.getElementById('custodian_id');
    const officeSelect = document.getElementById('office_id');
    if (custodianSelect && officeSelect) {
        const allOptions = Array.from(custodianSelect.options);
        function filterCustodians(officeId) {
            custodianSelect.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Select Custodian';
            custodianSelect.appendChild(placeholder);
            allOptions.forEach(opt => {
                if (opt.value === '') return;
                const optOffice = opt.getAttribute('data-office-id');
                if (officeId === '' || optOffice == officeId) {
                    const newOpt = document.createElement('option');
                    newOpt.value = opt.value;
                    newOpt.textContent = opt.textContent;
                    newOpt.setAttribute('data-office-id', optOffice);
                    if (opt.selected) newOpt.selected = true;
                    custodianSelect.appendChild(newOpt);
                }
            });
        }
        officeSelect.addEventListener('change', function() {
            filterCustodians(this.value);
        });
        custodianSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (selected && selected.value) {
                const officeId = selected.getAttribute('data-office-id');
                if (officeId) {
                    officeSelect.value = officeId;
                    filterCustodians(officeId);
                }
            }
        });
        if (officeSelect.value) filterCustodians(officeSelect.value);
    }
});
</script>