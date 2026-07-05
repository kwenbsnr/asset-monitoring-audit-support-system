let html5QrCode = null;
let isScanning = false;
let currentCamera = 'environment';
let isStarting = false;
let isStopping = false;
let scannerInitialized = false;
let isPaused = false;

const readerContainer = document.getElementById('reader');
const startBtn = document.getElementById('startScannerBtn');
const stopBtn = document.getElementById('stopScannerBtn');
const switchBtn = document.getElementById('switchCameraBtn');
const scanAnotherBtn = document.getElementById('scanAnotherBtn');
const profilePlaceholder = document.getElementById('profilePlaceholder');
const profileContent = document.getElementById('profileContent');
const profileFooter = document.getElementById('profileFooter');
const actionContainer = document.getElementById('actionButtonContainer');
const scannerFrame = document.getElementById('scanner-frame');
const scannerLine = document.getElementById('scanner-line');
const scannerCheckmark = document.getElementById('scanner-checkmark');
const scanSuccessMsg = document.getElementById('scanSuccessMsg');

let lastScannedCode = '';

// Detect mobile
function isMobile() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobi/i.test(navigator.userAgent) 
        || window.innerWidth < 768;
}

// UI setup
if (startBtn) startBtn.addEventListener('click', startScanner);
if (stopBtn) stopBtn.addEventListener('click', function() { stopScanner(); });
if (switchBtn) {
    switchBtn.addEventListener('click', switchCamera);
    if (!isMobile()) switchBtn.style.display = 'none';
}
if (scanAnotherBtn) scanAnotherBtn.addEventListener('click', resetAndScanAgain);

function switchCamera() {
    if (!isScanning || isStarting || isStopping || isPaused) return;
    currentCamera = (currentCamera === 'environment') ? 'user' : 'environment';
    stopScanner().then(() => setTimeout(startScanner, 400));
}

function startScanner() {
    if (isScanning || isStarting) return;
    isStarting = true;
    startBtn.disabled = true;
    startBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Starting...';
    setScannerFrameState('idle');

    const isSecure = window.isSecureContext || window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    if (!isSecure) {
        alert('Camera access requires HTTPS. Please use ngrok or a secure URL.');
        resetButton();
        isStarting = false;
        return;
    }

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert('Your browser does not support camera access. Please use Chrome, Safari, or Firefox.');
        resetButton();
        isStarting = false;
        return;
    }

    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            stream.getTracks().forEach(track => track.stop());
            initializeScanner();
        })
        .catch(err => {
            if (err.name === 'NotAllowedError') {
                alert('Camera permission denied. Please allow camera access in your browser settings and reload.');
            } else if (err.name === 'NotFoundError') {
                alert('No camera found on this device. Please connect a camera.');
            } else {
                alert('Camera error: ' + err.message + ' (Please try again)');
            }
            resetButton();
            isStarting = false;
        });
}

function initializeScanner() {
    recreateReaderContainer();
    html5QrCode = new Html5Qrcode("reader");

    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ],
        aspectRatio: 1.0
    };

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
            alert('Unable to access camera after all attempts. Please ensure you have a working camera and granted permission.');
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
                if (isMobile() && switchBtn) switchBtn.style.display = 'block';
                lastScannedCode = '';
                setScannerFrameState('idle');
            })
            .catch(err => {
                console.warn(`Camera mode ${attempts} failed:`, err);
                if (!started) tryNextMode();
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
                .catch((err) => {
                    console.warn('Stop error:', err);
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
    isPaused = false;
    if (html5QrCode) {
        html5QrCode.clear(); // Release camera stream
        html5QrCode = null;
    }
    // Reset UI
    startBtn.style.display = 'block';
    startBtn.disabled = false;
    startBtn.innerHTML = '<i class="bi bi-camera"></i> Tap to scan QR code';
    stopBtn.style.display = 'none';
    if (switchBtn) switchBtn.style.display = 'none';
    scanAnotherBtn.style.display = 'none';
    recreateReaderContainer();
    setScannerFrameState('idle');
    lastScannedCode = '';
    resetProfileUI();
}

function resetProfileUI() {
    profilePlaceholder.style.display = 'block';
    profileContent.style.display = 'none';
    profileFooter.style.display = 'none';
    scanAnotherBtn.style.display = 'none';
    actionContainer.innerHTML = '';
    if (scanSuccessMsg) scanSuccessMsg.style.display = 'none';
}

function recreateReaderContainer() {
    const container = document.getElementById('reader');
    if (container) {
        while (container.firstChild) container.removeChild(container.firstChild);
    }
}

function resetButton() {
    startBtn.disabled = false;
    startBtn.innerHTML = '<i class="bi bi-camera"></i> Tap to scan QR code';
    isStarting = false;
}

function setScannerFrameState(state) {
    if (state === 'idle') {
        scannerFrame.className = 'position-absolute top-0 start-0 w-100 h-100 pointer-events-none scanner-frame-idle';
        scannerLine.style.display = 'block';
        scannerCheckmark.classList.add('d-none');
    } else if (state === 'detected') {
        scannerFrame.className = 'position-absolute top-0 start-0 w-100 h-100 pointer-events-none scanner-frame-detected';
        scannerLine.style.display = 'none';
        scannerCheckmark.classList.remove('d-none');
        scannerFrame.classList.add('pulse');
        setTimeout(() => scannerFrame.classList.remove('pulse'), 1000);
        setTimeout(() => scannerCheckmark.classList.add('d-none'), 1200);
    } else if (state === 'paused') {
        scannerFrame.className = 'position-absolute top-0 start-0 w-100 h-100 pointer-events-none scanner-frame-detected';
        scannerLine.style.display = 'none';
        scannerCheckmark.classList.add('d-none');
    }
}

function onScanSuccess(decodedText, decodedResult) {
    if (lastScannedCode === decodedText || isPaused) return;
    lastScannedCode = decodedText;
    isPaused = true;

    setScannerFrameState('detected');

    if (html5QrCode && isScanning) {
        html5QrCode.stop().catch(() => {});
        isScanning = false;
    }

    fetch(`index.php?page=assets&sub=details&qr=${encodeURIComponent(decodedText)}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.error) {
                alert(data.error);
                resetScannerAndProfile();
                return;
            }
            showAssetProfile(data);
            scanAnotherBtn.style.display = 'inline-block';
            if (scanSuccessMsg) scanSuccessMsg.style.display = 'inline';
        })
        .catch(error => {
            alert('Failed to fetch asset details: ' + error.message);
            resetScannerAndProfile();
        });
}

function onScanError(error) {
    // Ignore
}

function showAssetProfile(data) {
    const asset = data.asset;
    const custody = data.custody || [];
    const activeCustody = custody.find(c => c.custody_status === 'active');
    const hasActiveCustody = !!activeCustody;

    let actionButton = '';
    if (hasActiveCustody) {
        const transferUrl = `index.php?page=custody&sub=edit&id=${activeCustody.asset_custodies_id}`;
        actionButton = `<a href="${transferUrl}" class="btn btn-warning btn-sm">
                            <i class="bi bi-arrow-left-right"></i> Transfer Custodian
                        </a>`;
    } else {
        const assignUrl = `index.php?page=custody&sub=add&asset_id=${asset.asset_id}`;
        actionButton = `<a href="${assignUrl}" class="btn btn-primary btn-sm">
                            <i class="bi bi-person-plus"></i> Assign Custodian
                        </a>`;
    }

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
            <div class="col-6"><strong>Status:</strong> <span class="badge bg-${asset.status === 'active' ? 'success' : 'secondary'}">${asset.status}</span></div>
            <div class="col-6"><strong>Condition:</strong> <span class="badge bg-${asset.condition === 'good' ? 'success' : 'warning'}">${asset.condition}</span></div>
            <div class="col-12"><strong>Remarks:</strong> ${escapeHtml(asset.remarks || 'N/A')}</div>
            <div class="col-6"><strong>Date Created:</strong> ${asset.created_at || 'N/A'}</div>
            <div class="col-6"><strong>Last Updated:</strong> ${asset.updated_at || 'N/A'}</div>
        </div>
    `;

    if (hasActiveCustody) {
        html += `
            <h6 class="border-bottom pb-2 mt-3">Current Custodian</h6>
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
        html += `<div class="alert alert-secondary mt-3"><i class="bi bi-info-circle"></i> No active custodian assigned.</div>`;
    }

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

    profilePlaceholder.style.display = 'none';
    profileContent.style.display = 'block';
    profileContent.innerHTML = html;
    profileFooter.style.display = 'flex';
    actionContainer.innerHTML = actionButton;
    scanAnotherBtn.style.display = 'inline-block';
    if (scanSuccessMsg) scanSuccessMsg.style.display = 'inline';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function resetScannerAndProfile() {
    resetProfileUI();
    setScannerFrameState('idle');
    isPaused = false;
    if (!isScanning && scannerInitialized) {
        startScanner();
    } else if (!scannerInitialized) {
        startBtn.style.display = 'block';
    }
}

function resetAndScanAgain() {
    if (isScanning) {
        stopScanner().then(() => {
            resetScannerAndProfile();
            setTimeout(startScanner, 500);
        });
    } else {
        resetScannerAndProfile();
        setTimeout(startScanner, 500);
    }
}

// Expose functions globally for the view
window.startScanner = startScanner;
window.showAssetProfile = showAssetProfile;
window.resetAndScanAgain = resetAndScanAgain;

window.addEventListener('beforeunload', function() {
    if (html5QrCode && isScanning) {
        html5QrCode.stop().catch(() => {});
    }
});