<?php
// This is the main layout – use it by including it at the top of every authenticated view
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'NIA Asset Monitoring' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<!-- Toast container – fixed top-right -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="toast align-items-center text-white bg-<?= $_SESSION['flash_type'] ?? 'success' ?> border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
            <div class="d-flex">
                <div class="toast-body">
                    <?= htmlspecialchars($_SESSION['flash']) ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
    <?php endif; ?>
</div>

<div class="wrapper">
    <nav id="sidebar" class="sidebar <?= ($_SESSION['role'] === 'admin') ? 'sidebar-admin' : 'sidebar-supply' ?>">
        <div class="sidebar-header">
            <img src="public/images/nia-logo.png" alt="NIA" class="sidebar-logo" onerror="this.style.display='none'">
            <h5>NIA RO IX</h5>
        </div>
        <ul class="nav flex-column">
            <!-- Dashboard – visible to all -->
            <li class="nav-item">
                <a class="nav-link <?= ($currentPage === 'dashboard') ? 'active' : '' ?>" href="index.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            <!-- Encoder modules -->
            <?php if ($_SESSION['role'] === 'encoder'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'add_asset') ? 'active' : '' ?>" href="index.php?page=assets&sub=add">
                        <i class="bi bi-plus-circle"></i> Register Asset
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'assets') ? 'active' : '' ?>" href="index.php?page=assets&sub=browse">
                        <i class="bi bi-box-seam"></i> Asset Records
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'assets_by_office') ? 'active' : '' ?>" href="index.php?page=assets&sub=by_office">
                        <i class="bi bi-building"></i> Assets by Office
                    </a>
                </li>
            <?php endif; ?>

            <!-- Asset Inspector modules -->
            <?php if ($_SESSION['role'] === 'asset_inspector'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'assets') ? 'active' : '' ?>" href="index.php?page=assets&sub=browse">
                        <i class="bi bi-box-seam"></i> Asset Records
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'scan') ? 'active' : '' ?>" href="index.php?page=assets&sub=scan">
                        <i class="bi bi-qr-code-scan"></i> Scan QR
                    </a>
                </li>
            <?php endif; ?>

            <!-- Admin modules -->
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'assets') ? 'active' : '' ?>" href="index.php?page=assets&sub=browse">
                        <i class="bi bi-box-seam"></i> Asset Registry
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'custody') ? 'active' : '' ?>" href="index.php?page=custody">
                        <i class="bi bi-people"></i> Custodial Tracking
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'reports') ? 'active' : '' ?>" href="index.php?page=reports">
                        <i class="bi bi-file-earmark-text"></i> Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'audit') ? 'active' : '' ?>" href="index.php?page=audit">
                        <i class="bi bi-clock-history"></i> Audit Trail
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'users') ? 'active' : '' ?>" href="index.php?page=users">
                        <i class="bi bi-people"></i> User Management
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-item mt-4">
                <a class="nav-link text-danger" href="index.php?action=logout">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

        <div class="container-fluid mt-3">
            <?php require_once $viewFile; ?>
        </div>
    </div>
</div>

<!-- Bootstrap & custom scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="public/js/script.js"></script>
<script>
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
    });
</script>
</body>
</html>