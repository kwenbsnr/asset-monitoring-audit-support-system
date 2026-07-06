<?php if (!defined('APP_START')) exit; ?>
<div class="row">
    <!-- Left: Generate Report -->
    <div class="col-lg-4">
        <div class="card shadow">
            <div class="card-header">
                <h5><i class="bi bi-file-earmark-arrow-up"></i> Generate Report</h5>
            </div>
            <div class="card-body">
                <form id="reportForm" method="POST" action="index.php?page=reports&sub=generate">
                    <!-- Report Type -->
                    <div class="mb-3">
                        <label class="form-label">Report Type *</label>
                        <select class="form-select" name="report_type" id="reportType" required>
                            <?php foreach ($reportTypes as $rt): ?>
                                <option value="<?= $rt['value'] ?>"><?= $rt['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Category (hidden by default) -->
                    <div class="mb-3" id="categoryDiv" style="display:none;">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category_id">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['asset_category_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Office (hidden by default) -->
                    <div class="mb-3" id="officeDiv" style="display:none;">
                        <label class="form-label">Office</label>
                        <select class="form-select" name="office_id">
                            <option value="">Select Office</option>
                            <?php foreach ($offices as $o): ?>
                                <option value="<?= $o['office_id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Date range -->
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" name="date_from">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" name="date_to">
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label">Status (Optional)</label>
                        <select class="form-select" name="status">
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="disposed">Disposed</option>
                            <option value="missing">Missing</option>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="d-grid gap-2">
                        <!-- Preview button – AJAX -->
                        <button type="button" class="btn btn-primary" id="previewBtn">
                            <i class="bi bi-eye"></i> Preview
                        </button>
                        <!-- PDF – submit form -->
                        <button type="submit" name="format" value="pdf" class="btn btn-success">
                            <i class="bi bi-file-earmark-pdf"></i> Generate PDF
                        </button>
                        <!-- Excel – submit form -->
                        <button type="submit" name="format" value="excel" class="btn btn-info">
                            <i class="bi bi-file-earmark-excel"></i> Export Excel
                        </button>
                        <!-- DOCX – submit form -->
                        <button type="submit" name="format" value="docx" class="btn btn-secondary">
                            <i class="bi bi-file-earmark-word"></i> Export DOCX
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right: Preview Panel -->
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header">
                <h5><i class="bi bi-file-earmark-text"></i> Report Preview</h5>
            </div>
            <div class="card-body">
                <div id="previewContainer" style="display:none;">
                    <div class="alert alert-info" id="previewTitle"></div>
                    <div id="previewContent" class="table-responsive"></div>
                    <!-- Removed duplicate export buttons – they are on the left -->
                </div>
                <div id="previewPlaceholder" class="text-center text-muted py-5">
                    <i class="bi bi-file-earmark" style="font-size: 3rem;"></i>
                    <p class="mt-3">Select report options and click <strong>Preview</strong> to see the report here.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle category/office fields
        const reportTypeSelect = document.getElementById('reportType');
        const categoryDiv = document.getElementById('categoryDiv');
        const officeDiv = document.getElementById('officeDiv');

        reportTypeSelect.addEventListener('change', function() {
            const type = this.value;
            categoryDiv.style.display = type === 'by_category' ? 'block' : 'none';
            officeDiv.style.display = type === 'by_office' ? 'block' : 'none';
        });
        reportTypeSelect.dispatchEvent(new Event('change'));

        // Preview button – AJAX
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