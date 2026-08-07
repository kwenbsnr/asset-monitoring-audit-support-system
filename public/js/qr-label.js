/**
 * Shared QR label rendering — used by:
 *   - assets/form.php   (single asset: Print QR Label, Download PNG)
 *   - assets/bulk_qr.php (bulk print page + per-item Download PNG)
 *
 * Keeping this in one file means the printed label and the downloaded PNG
 * can never show different fields
 */

// Builds the ordered list of fallback-search fields shown next to the QR
// code (used by both the print label and the downloaded PNG).
function buildQRLabelFields(assetCode, serialNumber, brand, model, description, accountCode) {
    const fields = [{ label: 'Code', value: assetCode || '' }];
    if (serialNumber) {
        fields.push({ label: 'Serial No', value: serialNumber });
    }
    const brandModel = [brand, model].filter(Boolean).join(' ').trim();
    if (brandModel) {
        fields.push({ label: 'Brand/Model', value: brandModel });
    }
    if (accountCode) {
        fields.push({ label: 'Account', value: accountCode });
    }
    if (description) {
        const shortDesc = description.length > 80 ? description.slice(0, 80) + '…' : description;
        fields.push({ label: 'Description', value: shortDesc });
    }
    return fields;
}

// Resolves the QR image endpoint to an absolute URL from the CURRENT page.
// A blank popup filled via document.write() (used by printQR) has no
// reliable base URL in every browser, so a bare relative src can silently
// fail to load — resolving it here, before the popup opens, avoids that.
function resolveQrUrl(assetId) {
    return new URL('index.php?page=assets&sub=qr&id=' + encodeURIComponent(assetId), window.location.href).href;
}

function escapeHtmlForLabel(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

/**
 * Opens a popup with the "info left, QR right" label and triggers print.
 */
function printQR(assetId, assetName, assetCode, serialNumber, brand, model, description, accountCode) {
    const fields = buildQRLabelFields(assetCode, serialNumber, brand, model, description, accountCode);
    let infoRows = '';
    fields.forEach(f => {
        infoRows += '<div><span class="field-label">' + escapeHtmlForLabel(f.label) + ':</span> ' + escapeHtmlForLabel(f.value) + '</div>';
    });

    const qrUrl = resolveQrUrl(assetId);

    var win = window.open('', '_blank');
    if (!win) {
        alert('Please allow pop-ups for this site to print the QR label.');
        return;
    }
    win.document.write('<!DOCTYPE html><html><head><title>QR Label</title>');
    win.document.write('<style>');
    win.document.write('body { font-family: Arial, sans-serif; padding: 30px; }');
    win.document.write('.header { text-align: center; margin-bottom: 20px; }');
    win.document.write('.qr-item { display: flex; align-items: stretch; gap: 14px; border: 1px solid #ccc; border-radius: 5px; padding: 14px; max-width: 420px; margin: 0 auto; }');
    win.document.write('.qr-info { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; font-size: 12px; line-height: 1.5; }');
    win.document.write('.qr-info .asset-name { font-size: 15px; font-weight: bold; word-break: break-word; margin-bottom: 4px; }');
    win.document.write('.qr-info .field-label { color: #666; font-weight: bold; }');
    win.document.write('.qr-info .fallback-note { margin-top: 6px; font-size: 10.5px; font-style: italic; color: #888; }');
    win.document.write('.qr-code-wrap { flex-shrink: 0; width: 130px; text-align: center; }');
    win.document.write('.qr-code-wrap img { width: 120px; height: 120px; }');
    win.document.write('.qr-code-wrap .code { font-size: 11px; font-weight: bold; margin-top: 4px; word-break: break-word; }');
    win.document.write('</style>');
    win.document.write('</head><body>');
    win.document.write('<div class="header"><h3>NIA Regional Office IX</h3><p>Asset QR Label</p></div>');
    win.document.write('<div class="qr-item">');
    win.document.write('<div class="qr-info">');
    win.document.write('<div class="asset-name">' + escapeHtmlForLabel(assetName || 'N/A') + '</div>');
    win.document.write(infoRows);
    win.document.write('<div class="fallback-note">If QR unreadable, search by Code or Serial No. in the system.</div>');
    win.document.write('</div>');
    win.document.write('<div class="qr-code-wrap">');
    win.document.write('<img src="' + escapeHtmlForLabel(qrUrl) + '" alt="QR">');
    win.document.write('<div class="code">' + escapeHtmlForLabel(assetCode) + '</div>');
    win.document.write('</div>');
    win.document.write('</div>');
    win.document.write('</body></html>');
    win.document.close();
    win.onload = function() { win.print(); };
}

// Draws word-wrapped canvas text and returns the y position after the last line.
function drawWrappedText(ctx, text, x, y, maxWidth, lineHeight, maxLines) {
    const words = String(text).split(' ');
    let line = '';
    let lines = 0;
    for (let i = 0; i < words.length; i++) {
        const testLine = line ? line + ' ' + words[i] : words[i];
        if (ctx.measureText(testLine).width > maxWidth && line) {
            ctx.fillText(line, x, y);
            line = words[i];
            y += lineHeight;
            lines++;
            if (maxLines && lines >= maxLines) {
                ctx.fillText(line + '…', x, y);
                return y + lineHeight;
            }
        } else {
            line = testLine;
        }
    }
    if (line) {
        ctx.fillText(line, x, y);
        y += lineHeight;
    }
    return y;
}

// Renders "info left, QR right" layout used by printQR() onto a
// canvas, so the downloaded PNG matches the printed label exactly.
function renderQRLabelCanvas(qrImg, assetName, fields) {
    const width = 520;
    const qrSize = 150;
    const padding = 18;
    const leftWidth = width - qrSize - padding * 3;

    const probe = document.createElement('canvas').getContext('2d');
    probe.font = 'bold 17px Arial';
    const nameLines = Math.min(2, Math.ceil(probe.measureText(assetName || 'N/A').width / leftWidth) || 1);
    const height = Math.max(
        qrSize + padding * 2 + 40,
        padding * 2 + 34 + (nameLines * 22) + (fields.length * 18) + 34
    );

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height + 46; // + header space
    const ctx = canvas.getContext('2d');

    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.fillStyle = '#1f2937';
    ctx.textAlign = 'center';
    ctx.font = 'bold 15px Arial';
    ctx.fillText('NIA Regional Office IX', canvas.width / 2, 22);
    ctx.font = '11px Arial';
    ctx.fillStyle = '#6b7280';
    ctx.fillText('Asset QR Label', canvas.width / 2, 38);
    ctx.textAlign = 'left';

    const cardY = 46;
    const cardH = height;
    ctx.strokeStyle = '#cccccc';
    ctx.lineWidth = 1;
    ctx.strokeRect(padding / 2, cardY, width - padding, cardH - padding / 2);

    let textY = cardY + padding + 12;
    const textX = padding + 10;
    ctx.fillStyle = '#1f2937';
    ctx.font = 'bold 15px Arial';
    textY = drawWrappedText(ctx, assetName || 'N/A', textX, textY, leftWidth, 20, 2);
    textY += 4;

    ctx.font = '12px Arial';
    fields.forEach(f => {
        ctx.fillStyle = '#666666';
        ctx.font = 'bold 12px Arial';
        const labelText = f.label + ': ';
        ctx.fillText(labelText, textX, textY);
        const labelWidth = ctx.measureText(labelText).width;
        ctx.fillStyle = '#1f2937';
        ctx.font = '12px Arial';
        ctx.fillText(String(f.value), textX + labelWidth, textY);
        textY += 18;
    });

    textY += 6;
    ctx.fillStyle = '#888888';
    ctx.font = 'italic 10px Arial';
    drawWrappedText(ctx, 'If QR unreadable, search by Code or Serial No. in the system.', textX, textY, leftWidth, 13, 2);

    const qrX = width - qrSize - padding;
    const qrY = cardY + (cardH - padding / 2 - qrSize) / 2;
    ctx.drawImage(qrImg, qrX, qrY, qrSize, qrSize);
    ctx.fillStyle = '#1f2937';
    ctx.font = 'bold 11px Arial';
    ctx.textAlign = 'center';
    const codeField = fields.find(f => f.label === 'Code');
    ctx.fillText(codeField ? codeField.value : '', qrX + qrSize / 2, qrY + qrSize + 16);
    ctx.textAlign = 'left';

    return canvas;
}

/**
 * Renders the label to a canvas and triggers a PNG download.
 */
function downloadQRLabel(assetId, assetName, assetCode, serialNumber, brand, model, description, accountCode) {
    const fields = buildQRLabelFields(assetCode, serialNumber, brand, model, description, accountCode);
    const qrUrl = resolveQrUrl(assetId);

    const img = new Image();
    img.onload = function() {
        const canvas = renderQRLabelCanvas(img, assetName, fields);
        const link = document.createElement('a');
        link.download = (assetCode || 'asset') + '_qr_label.png';
        link.href = canvas.toDataURL('image/png');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };
    img.onerror = function() {
        alert('Failed to load the QR code image for download.');
    };
    img.src = qrUrl;
}