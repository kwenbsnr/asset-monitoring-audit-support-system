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
        <div class="row">
            <div class="col-12">
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
                        <p class="text-muted">This is a placeholder dashboard. Build your modules here.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>