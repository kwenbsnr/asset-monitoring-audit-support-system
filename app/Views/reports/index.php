<?php if (!defined('APP_START')) exit; ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left: Generate Report -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h5 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4 flex items-center gap-2">
                <i class="bi bi-file-earmark-arrow-up"></i> Generate Report
            </h5>
            <form id="reportForm" method="POST" action="index.php?page=reports&sub=generate">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Report Type *</label>
                        <select class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" name="report_type" id="reportType" required>
                            <?php foreach ($reportTypes as $rt): ?>
                                <option value="<?= $rt['value'] ?>"><?= $rt['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Account (hidden by default) -->
                    <div id="accountDiv" style="display:none;">
                        <label class="block text-sm font-medium text-gray-700">Account</label>
                        <select class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" name="account_id">
                            <option value="">Select Account</option>
                            <?php foreach ($accounts as $a): ?>
                                <option value="<?= $a['asset_accounts_id'] ?>"><?= htmlspecialchars($a['account_code'] . ' - ' . $a['account_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Office (hidden by default) -->
                    <div id="officeDiv" style="display:none;">
                        <label class="block text-sm font-medium text-gray-700">Office</label>
                        <select class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" name="office_id">
                            <option value="">Select Office</option>
                            <?php foreach ($offices as $o): ?>
                                <option value="<?= $o['office_id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date From</label>
                            <input type="date" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" name="date_from">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date To</label>
                            <input type="date" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" name="date_to">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status (Optional)</label>
                        <select class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-green-500 focus:border-green-500" name="status">
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="disposed">Disposed</option>
                            <option value="missing">Missing</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <button type="button" class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" id="previewBtn">
                            <i class="bi bi-eye"></i> Preview
                        </button>
                        <button type="submit" name="format" value="pdf" class="w-full px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            <i class="bi bi-file-earmark-pdf"></i> Generate PDF
                        </button>
                        <button type="submit" name="format" value="excel" class="w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            <i class="bi bi-file-earmark-excel"></i> Export Excel
                        </button>
                        <button type="submit" name="format" value="docx" class="w-full px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                            <i class="bi bi-file-earmark-word"></i> Export DOCX
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Right: Preview Panel -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 h-full flex flex-col">
            <h5 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4 flex items-center gap-2">
                <i class="bi bi-file-earmark-text"></i> Report Preview
            </h5>
            <div id="previewContainer" style="display:none;" class="flex-1">
                <div class="bg-blue-50 border border-blue-200 text-blue-700 p-3 rounded mb-3" id="previewTitle"></div>
                <div id="previewContent" class="overflow-x-auto"></div>
            </div>
            <div id="previewPlaceholder" class="flex-1 flex flex-col items-center justify-center text-gray-500 py-12">
                <i class="bi bi-file-earmark text-6xl"></i>
                <p class="mt-3">Select report options and click <strong>Preview</strong> to see the report here.</p>
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