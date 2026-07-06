<?php if (!defined('APP_START')) exit; ?>
<div class="row">
    <!-- Left: Generate Report (optional, keep as is) -->
    <div class="col-lg-4">
        <div class="card shadow">
            <div class="card-header">
                <h5><i class="bi bi-file-earmark-arrow-up"></i> Generate Report</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="index.php?page=reports&sub=generate">
                    <div class="mb-3">
                        <label class="form-label">Report Type *</label>
                        <select class="form-select" name="report_type" required>
                            <?php foreach ($reportTypes as $rt): ?>
                                <option value="<?= $rt['value'] ?>"><?= $rt['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="categoryDiv" style="display:none;">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category_id">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['asset_category_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="officeDiv" style="display:none;">
                        <label class="form-label">Office</label>
                        <select class="form-select" name="office_id">
                            <option value="">Select Office</option>
                            <?php foreach ($offices as $o): ?>
                                <option value="<?= $o['office_id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
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
                    <div class="d-grid gap-2">
                        <button type="submit" name="format" value="preview" class="btn btn-primary">
                            <i class="bi bi-eye"></i> Preview
                        </button>
                        <button type="submit" name="format" value="pdf" class="btn btn-success">
                            <i class="bi bi-file-earmark-pdf"></i> Generate PDF
                        </button>
                        <button type="submit" name="format" value="excel" class="btn btn-info">
                            <i class="bi bi-file-earmark-excel"></i> Export Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right: Saved Reports (with filters & preview) -->
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header">
                <h5><i class="bi bi-list-ul"></i> Saved Reports</h5>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <div class="row g-2 mb-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">Report Type</label>
                        <select class="form-select form-select-sm" id="filterReportType">
                            <?php foreach ($reportTypes as $rt): ?>
                                <option value="<?= $rt['value'] ?>"><?= $rt['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2" id="filterCategoryDiv" style="display:none;">
                        <label class="form-label small">Category</label>
                        <select class="form-select form-select-sm" id="filterCategory">
                            <option value="">Select</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['asset_category_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2" id="filterOfficeDiv" style="display:none;">
                        <label class="form-label small">Office</label>
                        <select class="form-select form-select-sm" id="filterOffice">
                            <option value="">Select</option>
                            <?php foreach ($offices as $o): ?>
                                <option value="<?= $o['office_id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Date From</label>
                        <input type="date" class="form-control form-control-sm" id="filterDateFrom">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Date To</label>
                        <input type="date" class="form-control form-control-sm" id="filterDateTo">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Status</label>
                        <select class="form-select form-select-sm" id="filterStatus">
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="disposed">Disposed</option>
                            <option value="missing">Missing</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary btn-sm w-100" id="previewBtn"><i class="bi bi-eye"></i> Preview</button>
                    </div>
                </div>

                <!-- Preview Area -->
                <div id="previewContainer" style="display:none;">
                    <div class="alert alert-info" id="previewTitle"></div>
                    <div id="previewContent" class="table-responsive"></div>
                    <div class="mt-2">
                        <button class="btn btn-success btn-sm" id="exportPdfBtn"><i class="bi bi-file-earmark-pdf"></i> PDF</button>
                        <button class="btn btn-info btn-sm" id="exportExcelBtn"><i class="bi bi-file-earmark-excel"></i> Excel</button>
                        <button class="btn btn-secondary btn-sm" id="exportDocxBtn"><i class="bi bi-file-earmark-word"></i> DOCX</button>
                    </div>
                    <hr>
                </div>

                <!-- Saved Reports List -->
                <h6 class="border-bottom pb-2 mt-3">Saved Reports</h6>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Report #</th>
                                <th>Date</th>
                                <th>Office</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($savedReports)): ?>
                                <tr><td colspan="5" class="text-center">No saved reports.</td></tr>
                            <?php else: ?>
                                <?php foreach ($savedReports as $r): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($r['report_number']) ?></strong></td>
                                        <td><?= htmlspecialchars($r['report_date']) ?></td>
                                        <td><?= htmlspecialchars($r['office_name']) ?></td>
                                        <td><span class="badge bg-<?= $r['status'] === 'draft' ? 'secondary' : 'success' ?>"><?= $r['status'] ?></span></td>
                                        <td>
                                            <a href="index.php?page=reports&sub=view&id=<?= $r['asset_report_id'] ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                                            <?php if ($r['status'] === 'draft'): ?>
                                                <a href="index.php?page=reports&sub=delete&id=<?= $r['asset_report_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this draft?')"><i class="bi bi-trash"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <a href="index.php?page=reports&sub=add" class="btn btn-success btn-sm"><i class="bi bi-plus-circle"></i> Create New Saved Report</a>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for filters and preview -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle category/office fields based on report type
        const reportTypeSelect = document.getElementById('filterReportType');
        const categoryDiv = document.getElementById('filterCategoryDiv');
        const officeDiv = document.getElementById('filterOfficeDiv');

        reportTypeSelect.addEventListener('change', function() {
            const type = this.value;
            categoryDiv.style.display = type === 'by_category' ? 'block' : 'none';
            officeDiv.style.display = type === 'by_office' ? 'block' : 'none';
        });
        // Trigger initial state
        reportTypeSelect.dispatchEvent(new Event('change'));

        // Preview button
        document.getElementById('previewBtn').addEventListener('click', function() {
            const formData = new FormData();
            formData.append('report_type', document.getElementById('filterReportType').value);
            formData.append('category_id', document.getElementById('filterCategory').value);
            formData.append('office_id', document.getElementById('filterOffice').value);
            formData.append('date_from', document.getElementById('filterDateFrom').value);
            formData.append('date_to', document.getElementById('filterDateTo').value);
            formData.append('status', document.getElementById('filterStatus').value);

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
                // Update export buttons to use session data
                document.getElementById('exportPdfBtn').onclick = function() {
                    window.open('index.php?page=reports&sub=generate&format=pdf', '_blank');
                };
                document.getElementById('exportExcelBtn').onclick = function() {
                    window.open('index.php?page=reports&sub=generate&format=excel', '_blank');
                };
                document.getElementById('exportDocxBtn').onclick = function() {
                    window.open('index.php?page=reports&sub=export_docx', '_blank');
                };
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        });
    });
</script>

<!-- Left panel script: toggle category/office fields -->
<script>
    document.querySelector('select[name="report_type"]').addEventListener('change', function() {
        const type = this.value;
        document.getElementById('categoryDiv').style.display = type === 'by_category' ? 'block' : 'none';
        document.getElementById('officeDiv').style.display = type === 'by_office' ? 'block' : 'none';
    });
</script>