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
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .qr-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .qr-item {
            display: flex;
            align-items: stretch;
            gap: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
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
        }
        .qr-code-wrap .code {
            font-size: 10px;
            font-weight: bold;
            margin-top: 2px;
            word-break: break-word;
        }
        .qr-download-btn {
            margin-top: 6px;
            font-size: 9.5px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 3px;
            padding: 3px 6px;
            cursor: pointer;
            width: 100%;
        }
        .qr-download-btn:hover {
            background: #1d4ed8;
        }
        @media print {
            .no-print { display: none; }
            .qr-grid { grid-template-columns: repeat(3, 1fr); }
            .qr-item { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:20px;">
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Print</button>
        <a href="index.php?page=assets&sub=browse" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Back</a>
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