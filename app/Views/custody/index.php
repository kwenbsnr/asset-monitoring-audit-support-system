<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="bi bi-people"></i> Custody Records</h4>
        <a href="index.php?page=custody&sub=add" class="btn btn-success btn-sm">
            <i class="bi bi-plus-circle"></i> Assign Custody
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'success' ?> alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['flash']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <!-- Search Form – always visible -->
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="GET" action="index.php" class="d-flex gap-2">
                    <input type="hidden" name="page" value="custody">
                    <input type="hidden" name="sub" value="index">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" name="search" 
                               placeholder="Search by custodian, asset code, description, office, or document..." 
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        <button class="btn btn-outline-success btn-sm" type="submit">
                            <i class="bi bi-search"></i> Search
                        </button>
                        <?php if (!empty($_GET['search'])): ?>
                            <a href="index.php?page=custody" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <!-- The "Assign Custody" button is already in the header, but we can keep it here too -->
                <a href="index.php?page=custody&sub=add" class="btn btn-success btn-sm">
                    <i class="bi bi-plus-circle"></i> Assign Custody
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Asset</th>
                        <th>Custodian</th>
                        <th>Office</th>
                        <th>Effectivity</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Doc Ref</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr><td colspan="8" class="text-center">No custody records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($records as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['asset_code'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($r['custodian_name']) ?></td>
                                <td><?= htmlspecialchars($r['office_name']) ?></td>
                                <td><?= htmlspecialchars($r['effectivity_date']) ?></td>
                                <td><?= htmlspecialchars($r['end_date'] ?? '—') ?></td>
                                <td><span class="badge bg-<?= $r['status'] === 'active' ? 'success' : 'secondary' ?>"><?= $r['status'] ?></span></td>
                                <td><?= htmlspecialchars($r['accountability_document'] ?? '') ?></td>
                                <td>
                                    <a href="index.php?page=custody&sub=edit&id=<?= $r['asset_custodies_id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                    <a href="index.php?page=custody&sub=delete&id=<?= $r['asset_custodies_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('End this custody record?')"><i class="bi bi-x-circle"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>