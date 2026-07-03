<?php
// This is the main layout – use it by including it at the top of every authenticated view
// The content of the page must be passed in the variable $content (we'll include it later)
// We'll use a capture technique: we'll start output buffering in the controller, 
// but to keep it simple we'll just include the layout and then include the specific view inside.

// Instead of complex output buffering, we'll just include the sidebar and header in each view.
// We'll create a partial for sidebar and header.
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
    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <img src="public/images/nia-logo.png" alt="NIA" class="sidebar-logo" onerror="this.style.display='none'">
            <h5>NIA RO IX</h5>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= ($currentPage === 'dashboard') ? 'active' : '' ?>" href="index.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <?php if (in_array($_SESSION['role'], ['supply_officer', 'admin'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'assets') ? 'active' : '' ?>" href="index.php?page=assets&sub=list">
                        <i class="bi bi-box-seam"></i> Asset Registry
                    </a>
                </li>
                <!-- More links can be added later -->
            <?php endif; ?>
            <!-- Add other role-based links here -->
            <li class="nav-item mt-4">
                <a class="nav-link text-danger" href="index.php?action=logout">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <!-- Page content -->
    <div id="content">
        <!-- Top navbar (optional) -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
            <div class="container-fluid">
                <button class="btn btn-sm btn-outline-secondary" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <span class="navbar-brand mb-0 h5"><?= $pageTitle ?? 'Dashboard' ?></span>
                <div class="d-flex align-items-center">
                    <span class="me-3 text-muted">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['full_name']) ?>
                        <small class="text-secondary">(<?= $_SESSION['role'] ?>)</small>
                    </span>
                </div>
            </div>
        </nav>

        <div class="container-fluid mt-3">
            <!-- The main content of each page will be included here -->
            <?php require_once $viewFile; ?>
        </div>
    </div>
</div>

<!-- Bootstrap & custom scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="public/js/script.js"></script>
<script>
    // Toggle sidebar on small screens
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
    });
</script>
</body>
</html>