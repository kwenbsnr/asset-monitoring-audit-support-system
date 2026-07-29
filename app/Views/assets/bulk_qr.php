<?php if (!defined('APP_START')) exit; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bulk QR Codes</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .qr-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        .qr-item { text-align: center; border: 1px solid #ddd; padding: 10px; border-radius: 5px; }
        .qr-item img { max-width: 150px; height: auto; }
        .qr-item .code { font-size: 12px; margin-top: 5px; }
        @media print {
            .no-print { display: none; }
            .qr-grid { grid-template-columns: repeat(4, 1fr); }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:20px;">
        <button onclick="window.print()" class="btn btn-primary">Print</button>
        <a href="index.php?page=assets&sub=browse" class="btn btn-secondary">Back</a>
    </div>
    <h2>QR Codes for Selected Assets</h2>
    <div class="qr-grid">
        <?php foreach ($assets as $asset): ?>
            <div class="qr-item">
                <img src="index.php?page=assets&sub=qr&id=<?= $asset['asset_id'] ?>" alt="QR">
                <div class="code"><strong><?= htmlspecialchars($asset['asset_code']) ?></strong><br><?= htmlspecialchars($asset['asset_name']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>