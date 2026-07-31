<?php
// Main layout
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'NIA Asset Monitoring' ?></title>
    
    <!-- 🚀 NEW: Tailwind Production CSS (Built locally, stable) -->
    <link href="/public/css/output.css" rel="stylesheet">

    <!-- 🛡️ KEPT: Bootstrap Icons (they are just fonts, no conflicts) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>
<body>

<!-- ✅ REWRITTEN: Toast container using ONLY Tailwind (no Bootstrap CSS needed) -->
<div class="fixed top-4 right-4 z-[1100] flex flex-col gap-2" id="toastContainer">
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

<!-- ✅ REWRITTEN: Main wrapper using Tailwind flex -->
<div class="flex h-screen overflow-hidden">

    <!-- SIDEBAR: Using EXISTING CSS classes (sidebar, sidebar-admin) 
         Plus a Tailwind helper to hide/show on mobile -->
    <nav id="sidebar" class="sidebar <?= ($_SESSION['role'] === 'admin') ? 'sidebar-admin' : 'sidebar-supply' ?> 
                               translate-x-[-100%] md:translate-x-0 transition-transform duration-300 ease-in-out">
        <div class="sidebar-header">
            <img src="/public/images/nia-logo.png" alt="NIA" class="sidebar-logo" onerror="this.style.display='none'">
            <h5>NIA RO IX</h5>
        </div>
        <ul class="nav flex-column">
            <!-- Dashboard -->
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

            <!-- Asset Inspector -->
            <?php if ($_SESSION['role'] === 'asset_inspector'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'assets') ? 'active' : '' ?>" href="index.php?page=assets&sub=browse">
                        <i class="bi bi-box-seam"></i> Asset Records
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'verify') ? 'active' : '' ?>" href="index.php?page=assets&sub=verify">
                        <i class="bi bi-qr-code-scan"></i> Verify Asset
                    </a>
                </li>
            <?php endif; ?>

            <!-- Admin -->
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

    <!-- ✅ REWRITTEN: Main content area using Tailwind -->
    <div class="flex-1 flex flex-col overflow-y-auto bg-gray-50">
        <!-- 🆕 ADDED: Mobile Hamburger Button (so you can open sidebar on phones) -->
        <div class="md:hidden p-4 bg-white shadow-sm flex items-center gap-3">
            <button id="sidebarToggle" class="text-2xl text-gray-700 hover:text-blue-600">
                <i class="bi bi-list"></i>
            </button>
            <span class="font-semibold text-gray-800">NIA Asset System</span>
        </div>

        <!-- Page Content -->
        <div class="p-4 md:p-6 flex-1">
            <?php require_once $viewFile; ?>
        </div>
    </div>
</div>

<!-- 
    🛡️ KEPT: Bootstrap JS Bundle (still handles dropdowns/modals if you use them)
    🛡️ KEPT: custom scripts
-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/public/js/script.js"></script>

<!-- 
    ✅ UPDATED TOGGLE: Works with Tailwind's translate classes 
    Toggles the 'translate-x-[-100%]' class to show/hide sidebar on mobile
-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('translate-x-[-100%]');
            });
        }

        // Auto-close toasts after 4 seconds
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