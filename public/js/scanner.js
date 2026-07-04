/**
 * QR Scanner – handles camera, decoding, and fetching asset details.
 */

let html5QrCode;
let isScanning = false;
const readerElement = document.getElementById('reader');
const startBtn = document.getElementById('startScannerBtn');
const stopBtn = document.getElementById('stopScannerBtn');
const assetResult = document.getElementById('assetResult');
const assetDetails = document.getElementById('assetDetails');
const loadingPlaceholder = document.getElementById('loadingPlaceholder');
const errorPlaceholder = document.getElementById('errorPlaceholder');
let lastScannedCode = '';

// Start scanner on button click (required for mobile browsers)
if (startBtn) {
    startBtn.addEventListener('click', function() {
        startScanner();
    });
}

if (stopBtn) {
    stopBtn.addEventListener('click', function() {
        stopScanner();
    });
}

function startScanner() {
    if (isScanning) return;
    
    startBtn.disabled = true;
    startBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Starting...';
    
    html5QrCode = new Html5Qrcode("reader");
    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ]
    };

    // Try environment (back) camera first, fallback to user (front)
    html5QrCode.start(
        { facingMode: "environment" },
        config,
        onScanSuccess,
        onScanError
    ).then(() => {
        isScanning = true;
        startBtn.style.display = 'none';
        stopBtn.style.display = 'block';
        errorPlaceholder.style.display = 'none';
    }).catch(err => {
        console.warn('Environment camera failed, trying user camera', err);
        html5QrCode.start(
            { facingMode: "user" },
            config,
            onScanSuccess,
            onScanError
        ).then(() => {
            isScanning = true;
            startBtn.style.display = 'none';
            stopBtn.style.display = 'block';
            errorPlaceholder.style.display = 'none';
        }).catch(err2 => {
            startBtn.disabled = false;
            startBtn.innerHTML = '<i class="bi bi-camera"></i> Start Camera';
            errorPlaceholder.innerText = 'Cannot access camera. Please allow camera permissions and try again.';
            errorPlaceholder.style.display = 'block';
            console.error('Camera error:', err2);
        });
    });
}

function stopScanner() {
    if (html5QrCode && isScanning) {
        html5QrCode.stop().then(() => {
            isScanning = false;
            startBtn.style.display = 'block';
            startBtn.disabled = false;
            startBtn.innerHTML = '<i class="bi bi-camera"></i> Start Camera';
            stopBtn.style.display = 'none';
            readerElement.innerHTML = '';
            const newReader = document.createElement('div');
            newReader.id = 'reader';
            readerElement.parentNode.replaceChild(newReader, readerElement);
        }).catch(err => {
            console.error('Failed to stop scanner', err);
        });
    }
}

function onScanSuccess(decodedText, decodedResult) {
    if (lastScannedCode === decodedText) return;
    lastScannedCode = decodedText;

    assetResult.style.display = 'none';
    errorPlaceholder.style.display = 'none';
    loadingPlaceholder.style.display = 'block';

    fetch(`index.php?page=assets&sub=details&qr=${encodeURIComponent(decodedText)}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            loadingPlaceholder.style.display = 'none';
            if (data.error) {
                errorPlaceholder.innerText = data.error;
                errorPlaceholder.style.display = 'block';
                return;
            }
            assetDetails.innerHTML = buildAssetDetailsHTML(data);
            document.getElementById('viewFullDetails').href = 
                `index.php?page=assets&sub=edit&id=${data.asset.asset_id}`;
            assetResult.style.display = 'block';
        })
        .catch(error => {
            loadingPlaceholder.style.display = 'none';
            errorPlaceholder.innerText = 'Failed to fetch asset details: ' + error.message;
            errorPlaceholder.style.display = 'block';
        });
}

function onScanError(error) {
    // Ignore – keep scanning
}

function resetScanner() {
    lastScannedCode = '';
    assetResult.style.display = 'none';
    errorPlaceholder.style.display = 'none';
    if (isScanning) {
        stopScanner();
        setTimeout(() => startScanner(), 500);
    } else {
        startScanner();
    }
}

function buildAssetDetailsHTML(data) {
    const asset = data.asset;
    const custody = data.custody || [];
    const audit = data.audit || [];

    let html = `
        <h6 class="border-bottom pb-2">Asset Information</h6>
        <div class="row mb-2">
            <div class="col-6"><strong>Asset Code:</strong> ${escapeHtml(asset.asset_code)}</div>
            <div class="col-6"><strong>QR Ref:</strong> ${escapeHtml(asset.qr_code_ref)}</div>
            <div class="col-12"><strong>Description:</strong> ${escapeHtml(asset.description)}</div>
            <div class="col-6"><strong>Brand:</strong> ${escapeHtml(asset.brand || 'N/A')}</div>
            <div class="col-6"><strong>Model:</strong> ${escapeHtml(asset.model || 'N/A')}</div>
            <div class="col-6"><strong>Serial #:</strong> ${escapeHtml(asset.serial_number || 'N/A')}</div>
            <div class="col-6"><strong>Account:</strong> ${escapeHtml(asset.account_code)}</div>
            <div class="col-6"><strong>Status:</strong> <span class="badge bg-${asset.status === 'active' ? 'success' : 'secondary'}">${asset.status}</span></div>
            <div class="col-6"><strong>Condition:</strong> <span class="badge bg-${asset.condition === 'good' ? 'success' : 'warning'}">${asset.condition}</span></div>
        </div>
    `;

    if (custody.length > 0) {
        html += `<h6 class="border-bottom pb-2 mt-3">Current Custodian</h6>`;
        const current = custody.find(c => c.custody_status === 'active');
        if (current) {
            html += `<p><strong>${escapeHtml(current.custodian_name)}</strong><br>
                     ${escapeHtml(current.position || '')}<br>
                     ${escapeHtml(current.office_name)}</p>`;
        } else {
            html += `<p class="text-muted">No active custodian.</p>`;
        }
    }

    if (audit.length > 0) {
        html += `<h6 class="border-bottom pb-2 mt-3">Recent Activity</h6>`;
        const recent = audit.slice(0, 3);
        recent.forEach(a => {
            html += `<div class="small mb-1">
                        <strong>${escapeHtml(a.action_type)}</strong> 
                        by ${escapeHtml(a.performed_by)} 
                        <span class="text-muted">${a.performed_at}</span>
                    </div>`;
        });
        if (audit.length > 3) {
            html += `<div class="text-muted small">+${audit.length - 3} more</div>`;
        }
    }

    return html;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Clean up when leaving the page
window.addEventListener('beforeunload', function() {
    if (html5QrCode && isScanning) {
        html5QrCode.stop().catch(() => {});
    }
});