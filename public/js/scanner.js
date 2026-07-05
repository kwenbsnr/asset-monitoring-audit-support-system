let html5QrCode = null;
let isScanning = false;
let currentCamera = 'environment';
let isStarting = false;
let isStopping = false;
let scannerInitialized = false;

const readerContainer = document.getElementById('reader');
const startBtn = document.getElementById('startScannerBtn');
const stopBtn = document.getElementById('stopScannerBtn');
const switchBtn = document.getElementById('switchCameraBtn');
const assetResult = document.getElementById('assetResult');
const assetDetails = document.getElementById('assetDetails');
const loadingPlaceholder = document.getElementById('loadingPlaceholder');
const errorPlaceholder = document.getElementById('errorPlaceholder');
let lastScannedCode = '';

// Detect mobile
function isMobile() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobi/i.test(navigator.userAgent) 
        || window.innerWidth < 768;
}

// UI setup
if (startBtn) startBtn.addEventListener('click', startScanner);
if (stopBtn) stopBtn.addEventListener('click', stopScanner);
if (switchBtn) {
    switchBtn.addEventListener('click', switchCamera);
    if (!isMobile()) switchBtn.style.display = 'none';
}

function switchCamera() {
    if (!isScanning || isStarting || isStopping) return;
    currentCamera = (currentCamera === 'environment') ? 'user' : 'environment';
    // Stop and restart
    stopScanner().then(() => {
        // Wait for cleanup
        setTimeout(startScanner, 400);
    });
}

function startScanner() {
    if (isScanning || isStarting) return;
    isStarting = true;
    startBtn.disabled = true;
    startBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Starting...';
    errorPlaceholder.style.display = 'none';

    // Check secure context
    const isSecure = window.isSecureContext || window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    if (!isSecure) {
        showError('Camera access requires HTTPS. Please use ngrok or a secure URL.');
        resetButton();
        isStarting = false;
        return;
    }

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        showError('Your browser does not support camera access. Please use Chrome, Safari, or Firefox.');
        resetButton();
        isStarting = false;
        return;
    }

    // Test permissions
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            stream.getTracks().forEach(track => track.stop());
            // Now initialize the scanner
            initializeScanner();
        })
        .catch(err => {
            if (err.name === 'NotAllowedError') {
                showError('Camera permission denied. Please allow camera access in your browser settings and reload.');
            } else if (err.name === 'NotFoundError') {
                showError('No camera found on this device. Please connect a camera.');
            } else {
                showError('Camera error: ' + err.message + ' (Please try again)');
            }
            resetButton();
            isStarting = false;
        });
}

function initializeScanner() {
    // Ensure the reader container is clean
    recreateReaderContainer();

    // Create new instance
    html5QrCode = new Html5Qrcode("reader");
    const config = {
        fps: 6,
        qrbox: { width: 170, height: 170 },
        formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ],
        aspectRatio: 1.0
    };

    // Build camera modes based on currentCamera
    let modes;
    if (currentCamera === 'environment') {
        modes = [
            { facingMode: "environment" },
            { facingMode: "user" },
            {}
        ];
    } else {
        modes = [
            { facingMode: "user" },
            { facingMode: "environment" },
            {}
        ];
    }

    let attempts = 0;
    let started = false;

    function tryNextMode() {
        if (attempts >= modes.length) {
            showError('Unable to access camera after all attempts. Please ensure you have a working camera and granted permission.');
            resetButton();
            isStarting = false;
            return;
        }
        const modeConfig = modes[attempts];
        attempts++;
        console.log(`Trying camera mode ${attempts}:`, modeConfig);

        html5QrCode.start(modeConfig, config, onScanSuccess, onScanError)
            .then(() => {
                started = true;
                isScanning = true;
                isStarting = false;
                scannerInitialized = true;
                startBtn.style.display = 'none';
                stopBtn.style.display = 'block';
                if (isMobile() && switchBtn) {
                    switchBtn.style.display = 'block';
                    updateSwitchButton();
                }
                lastScannedCode = '';
                errorPlaceholder.style.display = 'none';
            })
            .catch(err => {
                console.warn(`Camera mode ${attempts} failed:`, err);
                if (!started) {
                    tryNextMode();
                }
            });
    }
    tryNextMode();
}

function stopScanner() {
    return new Promise((resolve) => {
        if (isStopping) { resolve(); return; }
        isStopping = true;

        if (html5QrCode && (isScanning || scannerInitialized)) {
            html5QrCode.stop()
                .then(() => {
                    cleanupScanner();
                    resolve();
                })
                .catch(() => {
                    // Force cleanup even if stop fails
                    cleanupScanner();
                    resolve();
                });
        } else {
            cleanupScanner();
            resolve();
        }
    });
}

function cleanupScanner() {
    isScanning = false;
    scannerInitialized = false;
    isStopping = false;
    if (html5QrCode) {
        html5QrCode = null;
    }
    // Reset UI
    startBtn.style.display = 'block';
    startBtn.disabled = false;
    startBtn.innerHTML = '<i class="bi bi-camera"></i> Start Camera';
    stopBtn.style.display = 'none';
    if (switchBtn) switchBtn.style.display = 'none';
    // Recreate reader container to remove any leftover video elements
    recreateReaderContainer();
}

function recreateReaderContainer() {
    const old = document.getElementById('reader');
    if (old) {
        // Remove all child nodes (video, canvas, etc.)
        while (old.firstChild) {
            old.removeChild(old.firstChild);
        }
        // Replace with a fresh div to reset any lingering state
        const newDiv = document.createElement('div');
        newDiv.id = 'reader';
        old.parentNode.replaceChild(newDiv, old);
        // Update the global reference (optional)
        // We'll keep using the new one
    }
}

function resetButton() {
    startBtn.disabled = false;
    startBtn.innerHTML = '<i class="bi bi-camera"></i> Start Camera';
    isStarting = false;
}

function showError(msg) {
    errorPlaceholder.innerText = msg;
    errorPlaceholder.style.display = 'block';
}

function updateSwitchButton() {
    if (!switchBtn) return;
    const label = currentCamera === 'environment' ? 'Switch to Front Camera' : 'Switch to Back Camera';
    switchBtn.innerHTML = '<i class="bi bi-arrow-repeat"></i> ' + label;
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
                showError(data.error);
                return;
            }
            assetDetails.innerHTML = buildAssetDetailsHTML(data);
            assetResult.style.display = 'block';
        })
        .catch(error => {
            loadingPlaceholder.style.display = 'none';
            showError('Failed to fetch asset details: ' + error.message);
        });
}

function onScanError(error) {
    // Ignore – scanning errors are normal
}

function resetScanner() {
    lastScannedCode = '';
    assetResult.style.display = 'none';
    errorPlaceholder.style.display = 'none';
    // Clear action button
    const actionContainer = document.getElementById('actionButtonContainer');
    if (actionContainer) actionContainer.innerHTML = '';
    if (isScanning) {
        stopScanner().then(() => setTimeout(startScanner, 500));
    } else {
        startScanner();
    }
}

function buildAssetDetailsHTML(data) {
    const asset = data.asset;
    const custody = data.custody || [];
    const audit = data.audit || [];

    // Find active custody (status = 'active')
    const activeCustody = custody.find(c => c.custody_status === 'active');
    const hasActiveCustody = !!activeCustody;

    // Build action button based on active custody
    let actionButton = '';
    if (hasActiveCustody) {
        // Transfer button – link to custody edit with asset pre-filled
        const transferUrl = `index.php?page=custody&sub=edit&id=${activeCustody.asset_custodies_id}`;
        actionButton = `<a href="${transferUrl}" class="btn btn-warning btn-sm">
                            <i class="bi bi-arrow-left-right"></i> Transfer Custodian
                        </a>`;
    } else {
        // Assign button – link to custody add with asset pre-filled
        const assignUrl = `index.php?page=custody&sub=add&asset_id=${asset.asset_id}`;
        actionButton = `<a href="${assignUrl}" class="btn btn-primary btn-sm">
                            <i class="bi bi-person-plus"></i> Assign Custodian
                        </a>`;
    }

    // -------- Asset Information --------
    let html = `
        <h6 class="border-bottom pb-2">Asset Information</h6>
        <div class="row mb-2">
            <div class="col-6"><strong>Asset Code:</strong> ${escapeHtml(asset.asset_code)}</div>
            <div class="col-6"><strong>QR Ref:</strong> ${escapeHtml(asset.qr_code_ref)}</div>
            <div class="col-12"><strong>Description:</strong> ${escapeHtml(asset.description)}</div>
            <div class="col-6"><strong>Category:</strong> ${escapeHtml(asset.category_name || 'N/A')}</div>
            <div class="col-6"><strong>Brand:</strong> ${escapeHtml(asset.brand || 'N/A')}</div>
            <div class="col-6"><strong>Model:</strong> ${escapeHtml(asset.model || 'N/A')}</div>
            <div class="col-6"><strong>Serial #:</strong> ${escapeHtml(asset.serial_number || 'N/A')}</div>
            <div class="col-6"><strong>Acquisition Date:</strong> ${asset.acquisition_date || 'N/A'}</div>
            <div class="col-6"><strong>Acquisition Cost:</strong> ${asset.acquisition_cost ? '₱' + Number(asset.acquisition_cost).toFixed(2) : 'N/A'}</div>
            <div class="col-6"><strong>Account:</strong> ${escapeHtml(asset.account_code + ' - ' + asset.account_name)}</div>
            <div class="col-6"><strong>Fund Source:</strong> ${escapeHtml(asset.fund_source || 'N/A')}</div>
            <div class="col-6"><strong>Status:</strong> <span class="badge bg-${asset.status === 'active' ? 'success' : 'secondary'}">${asset.status}</span></div>
            <div class="col-6"><strong>Condition:</strong> <span class="badge bg-${asset.condition === 'good' ? 'success' : 'warning'}">${asset.condition}</span></div>
            <div class="col-12"><strong>Remarks:</strong> ${escapeHtml(asset.remarks || 'N/A')}</div>
            <div class="col-6"><strong>Date Created:</strong> ${asset.created_at || 'N/A'}</div>
            <div class="col-6"><strong>Last Updated:</strong> ${asset.updated_at || 'N/A'}</div>
        </div>
    `;

    // -------- Current Assignment (if active) --------
    if (hasActiveCustody) {
        html += `
            <h6 class="border-bottom pb-2 mt-3">Current Assignment</h6>
            <div class="row mb-2">
                <div class="col-6"><strong>Custodian:</strong> ${escapeHtml(activeCustody.custodian_name)}</div>
                <div class="col-6"><strong>Position:</strong> ${escapeHtml(activeCustody.position || 'N/A')}</div>
                <div class="col-6"><strong>Office:</strong> ${escapeHtml(activeCustody.office_name)}</div>
                <div class="col-6"><strong>Effectivity:</strong> ${activeCustody.effectivity_date}</div>
                <div class="col-6"><strong>Accountability Doc:</strong> ${escapeHtml(activeCustody.accountability_document || 'N/A')}</div>
                <div class="col-6"><strong>Reference:</strong> ${escapeHtml(activeCustody.accountability_reference || 'N/A')}</div>
            </div>
        `;
    } else {
        html += `
            <div class="alert alert-secondary mt-3">
                <i class="bi bi-info-circle"></i> No active custodian assigned.
            </div>
        `;
    }

    // -------- Complete Custody History --------
    html += `<h6 class="border-bottom pb-2 mt-3">Custody History</h6>`;
    if (custody.length === 0) {
        html += `<p class="text-muted">No custody records found.</p>`;
    } else {
        html += `<div class="table-responsive"><table class="table table-sm table-bordered">`;
        html += `<thead><tr><th>From</th><th>To</th><th>Custodian</th><th>Office</th><th>Status</th><th>Document</th></tr></thead><tbody>`;
        custody.forEach(c => {
            html += `<tr>
                <td>${c.effectivity_date || 'N/A'}</td>
                <td>${c.end_date || 'Current'}</td>
                <td>${escapeHtml(c.custodian_name)} <br><small>${escapeHtml(c.position || '')}</small></td>
                <td>${escapeHtml(c.office_name)}</td>
                <td><span class="badge bg-${c.custody_status === 'active' ? 'success' : 'secondary'}">${c.custody_status}</span></td>
                <td>${escapeHtml(c.accountability_document || '')} ${c.accountability_reference ? '<br><small>Ref: ' + escapeHtml(c.accountability_reference) + '</small>' : ''}</td>
            </tr>`;
        });
        html += `</tbody></table></div>`;
    }

    // -------- Inject Action Button --------
    // We'll place the button in the footer container
    const actionContainer = document.getElementById('actionButtonContainer');
    if (actionContainer) {
        actionContainer.innerHTML = actionButton;
    }

    return html;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Prevent memory leaks
window.addEventListener('beforeunload', function() {
    if (html5QrCode && isScanning) {
        html5QrCode.stop().catch(() => {});
    }
});