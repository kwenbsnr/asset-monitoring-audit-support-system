<?php if (!defined('APP_START')) exit;

$data = $_SESSION['form_data'] ?? ($asset ?? []);
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);

$isEdit = $isEdit ?? false;
$assetId = $asset['asset_id'] ?? 0;

// json_encode() escapes quotes for JavaScript, not for an HTML attribute —
// a literal " from json_encode() would otherwise terminate the onclick="..."
// attribute early and leave the browser trying to parse a truncated script.
// This wraps json_encode() output with htmlspecialchars() so it's safe to
// embed inside a double-quoted HTML attribute regardless of what characters
// (quotes, ampersands, etc.) the underlying asset data contains.
if (!function_exists('js_attr')) {
    function js_attr($value) {
        return htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8');
    }
}
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

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Left: form -->
    <div class="md:col-span-2">
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

            <!--
                Custodian autocomplete widget styles — scoped to .custodian-ac-*
                so this doesn't depend on (or clash with) the global stylesheet.
                A <style> tag inserted via innerHTML DOES apply (unlike <script>,
                which is why this form's *behavior* lives in asset-form.js and is
                invoked explicitly via data-form-init — see modal-forms.js).
            -->
            <style>
                .custodian-ac-dropdown {
                    position: absolute;
                    z-index: 60;
                    top: 100%;
                    left: 0;
                    right: 0;
                    margin-top: 4px;
                    background: #fff;
                    border: 1px solid #d1d5db;
                    border-radius: 0.5rem;
                    box-shadow: 0 4px 14px rgba(0,0,0,0.08);
                    max-height: 260px;
                    overflow-y: auto;
                    padding: 4px;
                    list-style: none;
                }
                .custodian-ac-dropdown[hidden] { display: none; }
                .custodian-ac-item {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 8px;
                    padding: 8px 10px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 0.85rem;
                    color: #182919;
                }
                .custodian-ac-item:hover,
                .custodian-ac-item.is-active { background: #f0fdf4; }
                .custodian-ac-item.is-selected { background: #dcfce7; font-weight: 600; }
                .custodian-ac-info {
                    position: relative;
                    display: inline-flex;
                    align-items: center;
                    color: #9ca3af;
                    flex-shrink: 0;
                }
                .custodian-ac-info .custodian-ac-tooltip {
                    position: absolute;
                    right: 0;
                    bottom: 100%;
                    margin-bottom: 6px;
                    width: 190px;
                    background: #243C25;
                    color: #fff;
                    font-size: 0.7rem;
                    line-height: 1.4;
                    padding: 6px 8px;
                    border-radius: 6px;
                    opacity: 0;
                    visibility: hidden;
                    transition: opacity .12s ease;
                    pointer-events: none;
                    z-index: 70;
                }
                .custodian-ac-info:hover .custodian-ac-tooltip,
                .custodian-ac-info:focus .custodian-ac-tooltip { opacity: 1; visibility: visible; }
                .custodian-ac-empty, .custodian-ac-more {
                    padding: 8px 10px;
                    font-size: 0.75rem;
                    color: #9ca3af;
                    text-align: center;
                }
                .custodian-ac-more { border-top: 1px solid #f3f4f6; margin-top: 2px; }
            </style>

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
                        <div class="custodian-ac-wrap" style="position: relative;">
                            <label for="custodianSearch" class="block text-sm font-medium text-gray-700">Custodian</label>
                            <?php
                                // Resolve the currently-assigned custodian (edit mode, or a
                                // failed-validation re-render of $_SESSION['form_data']) so the
                                // search box shows their name instead of a bare numeric ID.
                                $selectedCustodian = null;
                                if (!empty($data['custodian_id'])) {
                                    foreach ($personnel as $p) {
                                        if ($p['personnel_id'] == $data['custodian_id']) { $selectedCustodian = $p; break; }
                                    }
                                }
                            ?>
                            <input type="text" id="custodianSearch" autocomplete="off"
                                   class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition"
                                   placeholder="Type a name to search…"
                                   role="combobox" aria-expanded="false" aria-haspopup="listbox"
                                   aria-controls="custodianDropdown" aria-autocomplete="list"
                                   value="<?= $selectedCustodian ? htmlspecialchars($selectedCustodian['full_name'] . ' (' . $selectedCustodian['position'] . ')') : '' ?>">
                            <!--
                                This hidden input — not the visible text above — is what actually
                                gets submitted as custodian_id. It's only ever written by JS when
                                the user explicitly picks a result (click or Enter on a dropdown
                                option), so typing alone can never silently assign someone.
                            -->
                            <input type="hidden" id="custodian_id" name="custodian_id" value="<?= htmlspecialchars($data['custodian_id'] ?? '') ?>">
                            <ul id="custodianDropdown" class="custodian-ac-dropdown" role="listbox" aria-label="Matching custodians" hidden></ul>
                            <p class="mt-1 text-xs text-gray-500" id="sgWarning"></p>
                            <!--
                                Replaces the old "every person rendered as a <select> option"
                                approach: the full roster is still available to JS for filtering,
                                but it no longer bloats the visible DOM or forces the user to
                                scroll a giant list.
                            -->
                            <script type="application/json" id="custodianData"><?= json_encode(array_map(function ($p) {
                                return [
                                    'id'       => $p['personnel_id'],
                                    'name'     => $p['full_name'],
                                    'position' => $p['position'] ?? '',
                                    'office'   => $p['office_id'],
                                    'sg'       => (int) ($p['salary_grade'] ?? 0),
                                ];
                            }, $personnel), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
                        </div>
                        <div>
                            <label for="office_id" class="block text-sm font-medium text-gray-700">Office</label>
                            <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="office_id" name="office_id">
                                <option value="">Select Office</option>
                                <?php foreach ($offices as $o): ?>
                                    <option value="<?= $o['office_id'] ?>"
                                        data-office-type="<?= htmlspecialchars($o['office_type']) ?>"
                                        data-head-id="<?= htmlspecialchars($o['head_personnel_id'] ?? '') ?>"
                                        data-head-name="<?= htmlspecialchars($o['head_name'] ?? '') ?>"
                                        <?= (isset($data['office_id']) && $data['office_id'] == $o['office_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($o['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <!--
                                Shown instead of the custodian search box when the selected
                                office is external — the accountable officer is always that
                                office's head, resolved automatically, never manually picked.
                            -->
                            <p class="mt-1 text-xs text-gray-600" id="externalHeadNotice" style="display:none;">
                                Accountable Officer: <strong id="externalHeadName"></strong>
                                <span class="block text-gray-400">Auto-assigned — the receiving office's head is accountable once transferred.</span>
                            </p>
                            <p class="mt-1 text-xs text-red-600" id="externalNoHeadWarning" style="display:none;">
                                This office has no accountable officer on file. Add one before transferring here.
                            </p>
                        </div>
                        <div>
                            <label for="effectivity_date" class="block text-sm font-medium text-gray-700">Effectivity Date</label>
                            <input type="date" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="effectivity_date" name="effectivity_date"
                                value="<?= htmlspecialchars($data['effectivity_date'] ?? \date('Y-m-d')) ?>">
                        </div>
                        <div class="md:col-span-2">
                            <label for="property_number" class="block text-sm font-medium text-gray-700">Property Number *</label>
                            <input type="text" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" id="property_number" name="property_number"
                                value="<?= htmlspecialchars($data['property_number'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
                <button type="button" class="btn-app btn-app-outline" data-modal-close>Cancel</button>
                <button type="submit" class="btn-app btn-app-primary"><?= $isEdit ? 'Update' : 'Create' ?> Asset</button>
            </div>
        </form>
    </div>

    <!-- Right: QR preview -->
    <div class="md:col-span-1">
        <?php if ($isEdit && $assetId): ?>
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-6 text-center">
                <h6 class="font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">QR Code</h6>
                <img src="index.php?page=assets&sub=qr&id=<?= $assetId ?>" alt="QR Code" class="mx-auto max-w-50 border border-gray-200 p-2 rounded-lg bg-white">
                <p class="text-xs text-gray-500 mt-3">
                    <i class="bi bi-info-circle"></i>
                    The QR code is linked to this asset record.<br>
                    Print and affix it to the physical asset.
                </p>
                <div class="mt-4 space-y-2">
                    <button class="w-full btn-app btn-app-outline-primary"
                            onclick="downloadQRLabel(<?= $assetId ?>, <?= js_attr($asset['asset_name'] ?? '') ?>, <?= js_attr($asset['asset_code'] ?? '') ?>, <?= js_attr($asset['serial_number'] ?? '') ?>, <?= js_attr($asset['brand'] ?? '') ?>, <?= js_attr($asset['model'] ?? '') ?>, <?= js_attr($asset['description'] ?? '') ?>, <?= js_attr($asset['account_code'] ?? '') ?>)">
                        <i class="bi bi-download"></i> Download PNG
                    </button>
                    <button class="w-full btn-app btn-app-primary"
                            onclick="printQR(<?= $assetId ?>, <?= js_attr($asset['asset_name'] ?? '') ?>, <?= js_attr($asset['asset_code'] ?? '') ?>, <?= js_attr($asset['serial_number'] ?? '') ?>, <?= js_attr($asset['brand'] ?? '') ?>, <?= js_attr($asset['model'] ?? '') ?>, <?= js_attr($asset['description'] ?? '') ?>, <?= js_attr($asset['account_code'] ?? '') ?>)">
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