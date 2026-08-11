<?php if (!defined('APP_START')) exit; ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left: Generate Report -->
    <div class="lg:col-span-1">
        <div class="card-panel p-4">
            <h5 class="text-base font-semibold text-gray-800 border-b border-gray-200 pb-3 mb-4 flex items-center gap-2">
                <span class="page-icon page-icon-sm"><i class="bi bi-file-earmark-arrow-up"></i></span> Generate Report
            </h5>
            <form id="reportForm" method="POST" action="index.php?page=reports&sub=generate">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Report Type *</label>
                        <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="report_type" id="reportType" required>
                            <?php foreach ($reportTypes as $rt): ?>
                                <option value="<?= $rt['value'] ?>"><?= $rt['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Account (hidden by default) -->
                    <div id="accountDiv" style="display:none;">
                        <label class="block text-sm font-medium text-gray-700">Account</label>
                        <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="account_id">
                            <option value="">Select Account</option>
                            <?php foreach ($accounts as $a): ?>
                                <option value="<?= $a['asset_accounts_id'] ?>"><?= htmlspecialchars($a['account_code'] . ' - ' . $a['account_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Office (hidden by default) -->
                    <div id="officeDiv" style="display:none;">
                        <label class="block text-sm font-medium text-gray-700">Office</label>
                        <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="office_id">
                            <option value="">Select Office</option>
                            <?php foreach ($offices as $o): ?>
                                <option value="<?= $o['office_id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date From</label>
                            <input type="date" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="date_from">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date To</label>
                            <input type="date" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="date_to">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status (Optional)</label>
                        <select class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" name="status">
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="disposed">Disposed</option>
                            <option value="missing">Missing</option>
                        </select>
                    </div>

                    <div class="space-y-2 pt-2">
                        <button type="button" class="w-full btn-app btn-app-outline-primary" id="previewBtn">
                            <i class="bi bi-eye"></i> Preview
                        </button>
                        <button type="submit" name="format" value="pdf" class="w-full btn-app btn-app-primary">
                            <i class="bi bi-file-earmark-pdf"></i> Generate PDF
                        </button>
                        <button type="submit" name="format" value="excel" class="w-full btn-app btn-app-info">
                            <i class="bi bi-file-earmark-excel"></i> Export Excel
                        </button>
                        <button type="submit" name="format" value="docx" class="w-full btn-app btn-app-outline">
                            <i class="bi bi-file-earmark-word"></i> Export DOCX
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Right: Preview Panel -->
    <div class="lg:col-span-2">
        <div class="card-panel p-4 h-full flex flex-col">
            <h5 class="text-base font-semibold text-gray-800 border-b border-gray-200 pb-3 mb-4 flex items-center gap-2">
                <span class="page-icon page-icon-sm"><i class="bi bi-file-earmark-text"></i></span> Report Preview
            </h5>
            <div id="previewContainer" style="display:none;" class="flex-1">
                <div class="alert-app alert-app-info" id="previewTitle"></div>
                <div id="previewContent" class="overflow-x-auto"></div>
            </div>
            <div id="previewPlaceholder" class="flex-1 flex flex-col items-center justify-center text-gray-400 py-12">
                <i class="bi bi-file-earmark text-6xl"></i>
                <p class="mt-3 text-gray-500">Select report options and click <strong>Preview</strong> to see the report here.</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reportTypeSelect = document.getElementById('reportType');
        const accountDiv = document.getElementById('accountDiv');
        const officeDiv = document.getElementById('officeDiv');

        reportTypeSelect.addEventListener('change', function() {
            const type = this.value;
            accountDiv.style.display = type === 'by_account' ? 'block' : 'none';
            officeDiv.style.display = type === 'by_office' ? 'block' : 'none';
        });
        reportTypeSelect.dispatchEvent(new Event('change'));

        document.getElementById('previewBtn').addEventListener('click', function() {
            const form = document.getElementById('reportForm');
            const formData = new FormData(form);
            formData.delete('format');

            fetch('index.php?page=reports&sub=preview_ajax', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                document.getElementById('previewTitle').textContent = data.title;
                document.getElementById('previewContent').innerHTML = data.html;
                document.getElementById('previewContainer').style.display = 'block';
                document.getElementById('previewPlaceholder').style.display = 'none';
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        });
    });
</script>