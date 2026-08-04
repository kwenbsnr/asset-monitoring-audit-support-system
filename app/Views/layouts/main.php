<?php
// Main layout
$roleLabels = [
    'admin'           => 'System Administrator',
    'encoder'         => 'Data Encoder',
    'asset_inspector' => 'Asset Inspector',
];
$roleLabel = $roleLabels[$_SESSION['role']] ?? ucfirst(str_replace('_', ' ', $_SESSION['role']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'NIA Asset Monitoring' ?></title>
    
    <link href="/asset-monitoring-audit-support-system/public/css/output.css" rel="stylesheet">
    <link href="/asset-monitoring-audit-support-system/public/css/style.css" rel="stylesheet">

    <!-- Bootstrap Icons (fonts only – no conflict) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>
<body>

<!-- Toast container -->
<div class="fixed top-4 right-4 z-1100 flex flex-col gap-2" id="toastContainer">
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flex items-center p-4 rounded-lg shadow-lg text-white 
             <?= ($_SESSION['flash_type'] ?? 'success') === 'success' ? 'bg-green-600' : 'bg-red-600' ?> 
             border-0 max-w-sm">
            <div class="flex-1 text-sm font-medium">
                <?= htmlspecialchars($_SESSION['flash']) ?>
            </div>
            <button type="button" class="ml-4 text-white hover:text-gray-200 text-xl leading-none" 
                    onclick="this.parentElement.remove()">
                &times;
            </button>
        </div>
        <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
    <?php endif; ?>
</div>

<!-- Main wrapper -->
<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar <?= ($_SESSION['role'] === 'admin') ? 'sidebar-admin' : 'sidebar-supply' ?> 
                               -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
        <div class="sidebar-header">
            <img src="/asset-monitoring-audit-support-system/public/images/nia-logo.png" alt="NIA" class="sidebar-logo" onerror="this.style.display='none'">
            <h5>NIA RO IX</h5>
            <div class="sidebar-role"><?= htmlspecialchars($roleLabel) ?></div>
        </div>
        <ul class="sidebar-nav flex flex-col list-none p-0 m-0">
            <li>
                <a class="nav-link <?= ($currentPage === 'dashboard') ? 'active' : '' ?>" href="index.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            <?php if ($_SESSION['role'] === 'encoder'): ?>
                <li>
                    <a class="nav-link <?= ($currentPage === 'add_asset') ? 'active' : '' ?>" href="index.php?page=assets&sub=add">
                        <i class="bi bi-plus-circle"></i> Register Asset
                    </a>
                </li>
                <li>
                    <a class="nav-link <?= ($currentPage === 'assets') ? 'active' : '' ?>" href="index.php?page=assets&sub=browse">
                        <i class="bi bi-box-seam"></i> Asset Records
                    </a>
                </li>
                <li>
                    <a class="nav-link <?= ($currentPage === 'assets_by_office') ? 'active' : '' ?>" href="index.php?page=assets&sub=by_office">
                        <i class="bi bi-building"></i> Assets by Office
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] === 'asset_inspector'): ?>
                <li>
                    <a class="nav-link <?= ($currentPage === 'assets') ? 'active' : '' ?>" href="index.php?page=assets&sub=browse">
                        <i class="bi bi-box-seam"></i> Asset Records
                    </a>
                </li>
                <li>
                    <a class="nav-link <?= ($currentPage === 'verify') ? 'active' : '' ?>" href="index.php?page=assets&sub=verify">
                        <i class="bi bi-qr-code-scan"></i> Verify Asset
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] === 'admin'): ?>
                <li>
                    <a class="nav-link <?= ($currentPage === 'assets') ? 'active' : '' ?>" href="index.php?page=assets&sub=browse">
                        <i class="bi bi-box-seam"></i> Asset Registry
                    </a>
                </li>
                <li>
                    <a class="nav-link <?= ($currentPage === 'custody') ? 'active' : '' ?>" href="index.php?page=custody">
                        <i class="bi bi-people"></i> Custodial Tracking
                    </a>
                </li>
                <li>
                    <a class="nav-link <?= ($currentPage === 'reports') ? 'active' : '' ?>" href="index.php?page=reports">
                        <i class="bi bi-file-earmark-text"></i> Reports
                    </a>
                </li>
                <li>
                    <a class="nav-link <?= ($currentPage === 'audit') ? 'active' : '' ?>" href="index.php?page=audit">
                        <i class="bi bi-clock-history"></i> Audit Trail
                    </a>
                </li>
                <li>
                    <a class="nav-link <?= ($currentPage === 'users') ? 'active' : '' ?>" href="index.php?page=users">
                        <i class="bi bi-people"></i> User Management
                    </a>
                </li>
            <?php endif; ?>
        </ul>

        <div class="sidebar-footer">
            <a class="nav-link text-red-400! hover:text-red-300!" href="index.php?action=logout">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Main content -->
    <div class="flex-1 flex flex-col overflow-y-auto bg-gray-50">
        <div class="md:hidden p-4 bg-white shadow-sm flex items-center gap-3">
            <button id="sidebarToggle" class="text-2xl text-gray-700 hover:text-blue-600">
                <i class="bi bi-list"></i>
            </button>
            <span class="font-semibold text-gray-800">NIA Asset System</span>
        </div>

        <div class="p-4 md:p-6 flex-1">
            <?php require_once $viewFile; ?>
        </div>
    </div>
</div>

<script src="/asset-monitoring-audit-support-system/public/js/scanner.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('-translate-x-full');
            });
        }

        const toasts = document.querySelectorAll('#toastContainer > div');
        toasts.forEach(toast => {
            setTimeout(() => {
                if (toast) toast.remove();
            }, 4000);
        });
    });
</script>
</body>
</html>