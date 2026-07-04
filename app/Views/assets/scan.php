<?php if (!defined('APP_START')) exit; ?>
<div class="card shadow">
    <div class="card-header">
        <h4><i class="bi bi-qr-code-scan"></i> Scan Asset QR Code</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div id="reader" style="width:100%; max-width:500px;"></div>
                <div class="mt-3">
                    <button id="startScannerBtn" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-camera"></i> Start Camera
                    </button>
                    <button id="stopScannerBtn" class="btn btn-danger btn-sm w-100 mt-2" style="display:none;">
                        <i class="bi bi-stop-circle"></i> Stop Camera
                    </button>
                </div>
                <p class="text-muted mt-2">
                    <i class="bi bi-info-circle"></i> 
                    Point the camera at the asset’s QR code. The system will automatically fetch its details.
                </p>
            </div>
            <div class="col-md-6">
                <div id="assetResult" style="display:none;">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-check-circle"></i> Asset Found</h5>
                        </div>
                        <div class="card-body" id="assetDetails"></div>
                        <div class="card-footer">
                            <a href="#" id="viewFullDetails" class="btn btn-primary btn-sm">
                                <i class="bi bi-eye"></i> View Full Details
                            </a>
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

<!-- Include QR library -->
<script src="https://unpkg.com/html5-qrcode"></script>
<!-- Include our scanner script -->
<script src="public/js/scanner.js"></script>