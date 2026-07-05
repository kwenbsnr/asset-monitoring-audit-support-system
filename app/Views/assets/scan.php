<?php if (!defined('APP_START')) exit; ?>
<div class="container-fluid">
    <div class="row g-4">
        <!-- LEFT: QR Code Scanner (35%) -->
        <div class="col-lg-4 col-md-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-qr-code-scan me-2"></i>QR Code Scanner</h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center">
                    <!-- Scanner Preview (1:1 aspect ratio) -->
                    <div id="reader-wrapper" class="position-relative" style="max-width:350px; width:100%; aspect-ratio:1/1;">
                        <div id="reader" style="width:100%; height:100%;"></div>
                        <!-- Frame overlay -->
                        <div id="scanner-frame" class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none scanner-frame-idle">
                            <div id="scanner-line" class="scanner-line"></div>
                            <div id="scanner-checkmark" class="scanner-checkmark d-none">✓</div>
                        </div>
                    </div>

                    <!-- Controls -->
                    <div class="mt-3 text-center" style="max-width:350px; width:100%;">
                        <button id="startScannerBtn" class="btn btn-success w-100" style="display:none;">
                            <i class="bi bi-camera"></i> Tap to scan QR code
                        </button>
                        <button id="stopScannerBtn" class="btn btn-danger w-100 mt-2" style="display:none;">
                            <i class="bi bi-stop-circle"></i> Stop Camera
                        </button>
                        <button id="switchCameraBtn" class="btn btn-info w-100 mt-2" style="display:none;">
                            <i class="bi bi-arrow-repeat"></i> Switch Camera
                        </button>
                        <p class="text-muted small mt-2 mb-0">
                            <i class="bi bi-info-circle"></i> Point the camera at an asset QR label.
                        </p>
                    </div>

                    <!-- Divider -->
                    <hr class="my-3" style="max-width:350px; width:100%;">

                    <!-- Manual Search -->
                    <div style="max-width:350px; width:100%;">
                        <div class="text-muted text-center small mb-2">— or search manually —</div>
                        <div class="input-group">
                            <input type="text" id="manualSearchInput" class="form-control" placeholder="Asset code, serial number, or description...">
                            <button id="manualSearchBtn" class="btn btn-primary">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                        <div id="manualSearchError" class="text-danger small mt-1" style="display:none;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Asset Profile (65%) -->
        <div class="col-lg-8 col-md-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text me-2"></i>Retrieved Asset Profile</h5>
                    <button id="scanAnotherBtn" class="btn btn-outline-secondary btn-sm" style="display:none;">
                        <i class="bi bi-arrow-counterclockwise"></i> Scan Another Asset
                    </button>
                </div>
                <div class="card-body" id="profileBody">
                    <!-- Placeholder -->
                    <div id="profilePlaceholder" class="text-center text-muted py-5">
                        <i class="bi bi-box-seam" style="font-size: 4rem;"></i>
                        <p class="mt-3">No asset selected.</p>
                        <p class="small">Scan a QR code or search manually to display asset information.</p>
                    </div>
                    <!-- Asset Profile -->
                    <div id="profileContent" style="display:none;"></div>
                </div>
                <div id="profileFooter" class="card-footer bg-white border-top" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div id="actionButtonContainer"><!-- Dynamic button --></div>
                        <span id="scanSuccessMsg" class="text-success small"><i class="bi bi-check-circle"></i> Asset retrieved successfully.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script src="public/js/scanner.js"></script>
<script>
    // Auto‑start scanner on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Small delay to ensure DOM is ready
        setTimeout(() => {
            if (typeof startScanner === 'function') {
                startScanner();
            }
        }, 500);

        // Manual search handler
        const searchInput = document.getElementById('manualSearchInput');
        const searchBtn = document.getElementById('manualSearchBtn');
        const errorContainer = document.getElementById('manualSearchError');

        function performManualSearch() {
            const query = searchInput.value.trim();
            if (query.length < 2) {
                errorContainer.textContent = 'Please enter at least 2 characters.';
                errorContainer.style.display = 'block';
                return;
            }
            errorContainer.style.display = 'none';
            document.getElementById('profilePlaceholder').style.display = 'none';
            document.getElementById('profileContent').style.display = 'none';
            document.getElementById('profileFooter').style.display = 'none';
            fetch(`index.php?page=assets&sub=details&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    showAssetProfile(data);
                })
                .catch(err => alert('Failed to fetch asset: ' + err.message));
        }

        searchBtn.addEventListener('click', performManualSearch);
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') performManualSearch();
        });
    });
</script>