<?php
// Prevent direct access
if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – NIA Asset Monitoring</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="container-fluid dashboard-wrapper">
        <nav class="navbar navbar-dark bg-dark mb-4">
            <div class="container-fluid">
                <span class="navbar-brand">NIA Asset Monitoring</span>
                <div class="d-flex">
                    <span class="navbar-text text-white me-3">
                        Welcome, <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong>
                        (<?= htmlspecialchars($_SESSION['role']) ?>)
                    </span>
                    <a href="index.php?action=logout" class="btn btn-outline-light btn-sm">Logout</a>
                </div>
            </div>
        </nav>

        <div class="card shadow">
            <div class="card-body">
                <h4>Dashboard</h4>
                <p>You are logged in as <strong><?= htmlspecialchars($_SESSION['role']) ?></strong>.</p>
                <p>Office: <?= htmlspecialchars($_SESSION['office']) ?></p>
                <hr>

                <?php if ($_SESSION['role'] === 'supply_officer' || $_SESSION['role'] === 'admin'): ?>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <i class="bi bi-box-seam" style="font-size: 2.5rem;"></i>
                                    <h5 class="mt-2">Asset Registry</h5>
                                    <p class="text-muted">Manage all assets (CRUD)</p>
                                    <a href="index.php?page=assets&sub=list" class="btn btn-success">Go to Assets</a>
                                </div>
                            </div>
                        </div>
                        <!-- More cards can be added here -->
                    </div>
                <?php else: ?>
                    <p class="text-muted">You don't have access to any modules.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap Icons for dashboard -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>