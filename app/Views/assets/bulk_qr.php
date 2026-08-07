<?php
if (!defined('APP_START')) exit;
// json_encode() escapes quotes for JavaScript, not for an HTML attribute —
// a literal " from json_encode() would otherwise terminate the onclick="..."
// attribute early and leave the browser trying to parse a truncated script.
if (!function_exists('js_attr')) {
    function js_attr($value) {
        return htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bulk QR Codes</title>
    <link href="/asset-monitoring-audit-support-system/public/css/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f7f4; }
        .qr-toolbar {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 20px;
        }
        .qr-toolbar h2 {
            margin: 0 0 20px;
            font-size: 1.15rem;
            font-weight: 700;
            color: #243C25;
        }
        .qr-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: background-color .15s ease, border-color .15s ease;
        }
        .qr-btn-primary { background: #15803d; border-color: #15803d; color: #fff; }
        .qr-btn-primary:hover { background: #146534; }
        .qr-btn-outline { background: #fff; border-color: #d1d5db; color: #1f2937; }
        .qr-btn-outline:hover { background: #f9fafb; }
        .qr-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .qr-item {
            display: flex;
            align-items: stretch;
            gap: 10px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px;
            box-shadow: 0 1px 3px rgba(16,24,24,0.06);
        }
        .qr-info {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            font-size: 11px;
            line-height: 1.4;
        }
        .qr-info .asset-name {
            font-size: 13px;
            font-weight: bold;
            word-break: break-word;
            margin-bottom: 3px;
            color: #182919;
        }
        .qr-info .field-label {
            color: #666;
            font-weight: bold;
        }
        .qr-info .fallback-note {
            margin-top: 4px;
            font-size: 9.5px;
            font-style: italic;
            color: #888;
        }
        .qr-code-wrap {
            flex-shrink: 0;
            width: 110px;
            text-align: center;
        }
        .qr-code-wrap img {
            width: 100px;
            height: 100px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 4px;
            background: #fff;
        }
        .qr-code-wrap .code {
            font-size: 10px;
            font-weight: bold;
            margin-top: 4px;
            word-break: break-word;
        }
        .qr-download-btn {
            margin-top: 6px;
            font-size: 9.5px;
            background: #15803d;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 4px 6px;
            cursor: pointer;
            width: 100%;
            transition: background-color .15s ease;
        }
        .qr-download-btn:hover {
            background: #146534;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none; }
            .qr-grid { grid-template-columns: repeat(3, 1fr); }
            .qr-item { break-inside: avoid; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="no-print qr-toolbar">
        <button onclick="window.print()" class="qr-btn qr-btn-primary"><i class="bi bi-printer"></i> Print</button>
        <a href="index.php?page=assets&sub=browse" class="qr-btn qr-btn-outline"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
    <h2>QR Codes for Selected Assets</h2>
    <div class="qr-grid">
        <?php foreach ($assets as $asset): ?>
            <div class="qr-item">
                <div class="qr-info">
                    <div class="asset-name"><?= htmlspecialchars($asset['asset_name'] ?? 'N/A') ?></div>
                    <div><span class="field-label">Code:</span> <?= htmlspecialchars($asset['asset_code']) ?></div>
                    <?php if (!empty($asset['serial_number'])): ?>
                        <div><span class="field-label">Serial No:</span> <?= htmlspecialchars($asset['serial_number']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($asset['brand']) || !empty($asset['model'])): ?>
                        <div><span class="field-label">Brand/Model:</span> <?= htmlspecialchars(trim(($asset['brand'] ?? '') . ' ' . ($asset['model'] ?? ''))) ?></div>
                    <?php endif; ?>
                    <div class="fallback-note">If QR unreadable, search by Code or Serial No. in the system.</div>
                </div>
                <div class="qr-code-wrap">
                    <img src="index.php?page=assets&sub=qr&id=<?= $asset['asset_id'] ?>" alt="QR">
                    <div class="code"><?= htmlspecialchars($asset['asset_code']) ?></div>
                    <button type="button" class="qr-download-btn no-print"
                            onclick="downloadQRLabel(<?= $asset['asset_id'] ?>, <?= js_attr($asset['asset_name'] ?? '') ?>, <?= js_attr($asset['asset_code'] ?? '') ?>, <?= js_attr($asset['serial_number'] ?? '') ?>, <?= js_attr($asset['brand'] ?? '') ?>, <?= js_attr($asset['model'] ?? '') ?>, <?= js_attr($asset['description'] ?? '') ?>, <?= js_attr($asset['account_code'] ?? '') ?>)">
                        <i class="bi bi-download"></i> PNG
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <script src="public/js/qr-label.js"></script>
    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>