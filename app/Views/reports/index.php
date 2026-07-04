<?php if (!defined('APP_START')) exit; ?>
<div class="row">
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header">
                <h5><i class="bi bi-file-earmark-arrow-up"></i> Generate Report</h5>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['report_data'])): ?>
                    <div class="alert alert-success">
                        Report generated. Use the buttons below to export.
                        <a href="index.php?page=reports&sub=preview" class="btn btn-sm btn-primary mt-2 d-block" target="_blank">
                            <i class="bi bi-eye"></i> Preview
                        </a>
                    </div>
                <?php endif; ?>

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
    <div class="col-md-8">
        <!-- Saved Reports List -->
        <div class="card shadow">
            <div class="card-header">
                <h5><i class="bi bi-list-ul"></i> Saved Reports</h5>
            </div>
            <div class="card-body">
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

<script>
    document.querySelector('select[name="report_type"]').addEventListener('change', function() {
        const type = this.value;
        document.getElementById('categoryDiv').style.display = type === 'by_category' ? 'block' : 'none';
        document.getElementById('officeDiv').style.display = type === 'by_office' ? 'block' : 'none';
    });
</script>