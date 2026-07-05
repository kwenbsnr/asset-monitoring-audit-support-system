<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow">
    <div class="card-header">
        <h4><i class="bi bi-qr-code-scan"></i> Scan Asset QR Code</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Scanner Column (Left) -->
            <div class="col-md-6">
                <!-- Scanner Preview -->
                <div id="reader" style="width:100%; max-width:336px; margin:0 auto;"></div>
                <!-- Scanner Buttons -->
                <div class="mt-3" style="max-width:336px; margin:0 auto;">
                    <button id="startScannerBtn" class="btn btn-success w-100">
                        <i class="bi bi-camera"></i> Start Camera
                    </button>
                    <button id="stopScannerBtn" class="btn btn-danger w-100 mt-2" style="display:none;">
                        <i class="bi bi-stop-circle"></i> Stop Camera
                    </button>
                    <button id="switchCameraBtn" class="btn btn-info w-100 mt-2" style="display:none;">
                        <i class="bi bi-arrow-repeat"></i> Switch Camera
                    </button>
                </div>

                <!-- Divider -->
                <div class="text-muted text-center my-3" style="max-width:336px; margin:0 auto;">
                    <span>— or search manually —</span>
                </div>

                <!-- Manual Search -->
                <div style="max-width:336px; margin:0 auto;">
                    <div class="input-group">
                        <input type="text" id="manualSearchInput" class="form-control" placeholder="Asset code, serial number, or description...">
                        <button id="manualSearchBtn" class="btn btn-primary">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                    <div id="manualSearchError" class="text-danger small mt-1" style="display:none;"></div>
                </div>

                <p class="text-muted mt-3 text-center">
                    <i class="bi bi-info-circle"></i> 
                    Point the camera or enter a search term to retrieve asset details.
                </p>
            </div>

            <!-- Results Column (Right) -->
            <div class="col-md-6">
                <div id="assetResult" style="display:none;">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-check-circle"></i> Asset Profile</h5>
                        </div>
                        <div class="card-body" id="assetDetails" style="max-height: 500px; overflow-y: auto;"></div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <div id="actionButtonContainer">
                                <!-- Dynamic button injected by JavaScript -->
                            </div>
                            <button class="btn btn-secondary btn-sm" onclick="resetScanner()">
                                <i class="bi bi-arrow-counterclockwise"></i> Scan Again
                            </button>
                        </div>
                    </div>
                </div>
                <div id="loadingPlaceholder" style="display:none;" class="text-center py-4">
                    <div class="spinner-border text-success" role="status"></div>
                    <p>Fetching asset details...</p>
                </div>
                <div id="errorPlaceholder" style="display:none;" class="alert alert-danger"></div>
            </div>
        </div>
    </div>
</div>

<!-- Include the scanner library and our custom JS -->
<script src="https://unpkg.com/html5-qrcode"></script>
<script src="public/js/scanner.js"></script>

<!-- Manual search script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('manualSearchInput');
    const searchBtn = document.getElementById('manualSearchBtn');
    const errorContainer = document.getElementById('manualSearchError');

    function performSearch() {
        const query = searchInput.value.trim();
        if (query.length < 2) {
            errorContainer.textContent = 'Please enter at least 2 characters.';
            errorContainer.style.display = 'block';
            return;
        }
        errorContainer.style.display = 'none';

        // Show loading
        document.getElementById('loadingPlaceholder').style.display = 'block';
        document.getElementById('assetResult').style.display = 'none';
        document.getElementById('errorPlaceholder').style.display = 'none';

        fetch(`index.php?page=assets&sub=details&q=${encodeURIComponent(query)}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                document.getElementById('loadingPlaceholder').style.display = 'none';
                if (data.error) {
                    document.getElementById('errorPlaceholder').innerHTML = data.error;
                    document.getElementById('errorPlaceholder').style.display = 'block';
                    return;
                }
                // Display the same way as QR scan
                document.getElementById('assetDetails').innerHTML = buildAssetDetailsHTML(data);
                document.getElementById('assetResult').style.display = 'block';
            })
            .catch(error => {
                document.getElementById('loadingPlaceholder').style.display = 'none';
                document.getElementById('errorPlaceholder').innerHTML = 'Failed to fetch asset details: ' + error.message;
                document.getElementById('errorPlaceholder').style.display = 'block';
            });
    }

    searchBtn.addEventListener('click', performSearch);
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') performSearch();
    });
});
</script>