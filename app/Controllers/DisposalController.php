<?php
namespace App\Controllers;

use App\Models\DisposalModel;
use App\Models\AssetModel;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class DisposalController {
    /** @var DisposalModel */
    private $disposalModel;
    /** @var AssetModel */
    private $assetModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php');
            exit;
        }
        $this->disposalModel = new DisposalModel();
        $this->assetModel = new AssetModel();
    }

    /**
     * Show list of disposal requests.
     * Supply officer sees their own; admin sees all pending.
     */
    public function index() {
        $filters = [];
        if ($_SESSION['role'] === 'supply_officer') {
            $filters['user_id'] = $_SESSION['user_id'];
        } elseif ($_SESSION['role'] === 'admin') {
            // Admin sees all pending by default, but can filter
            if (isset($_GET['status']) && !empty($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            } else {
                $filters['status'] = 'pending';
            }
        } else {
            header('Location: index.php');
            exit;
        }
        $requests = $this->disposalModel->getAll($filters);
        $pageTitle = 'Disposal Requests';
        $currentPage = 'disposal';
        $viewFile = __DIR__ . '/../Views/disposal/index.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    /**
     * Show form to request disposal for a specific asset.
     */
    public function request() {
        $assetId = isset($_GET['asset_id']) ? (int)$_GET['asset_id'] : 0;
        if (!$assetId) {
            header('Location: index.php?page=assets&sub=browse');
            exit;
        }
        $asset = $this->assetModel->getById($assetId);
        if (!$asset || $asset['status'] !== 'active') {
            $_SESSION['flash'] = 'Asset is not available for disposal request.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: index.php?page=assets&sub=browse');
            exit;
        }
        $pageTitle = 'Request Disposal';
        $currentPage = 'disposal';
        $viewFile = __DIR__ . '/../Views/disposal/request.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    /**
     * Store a new disposal request.
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=disposal');
            exit;
        }

        $assetId = (int)$_POST['asset_id'];
        $reason = trim($_POST['reason']);
        $asset = $this->assetModel->getById($assetId);
        if (!$asset || $asset['status'] !== 'active') {
            $_SESSION['flash'] = 'Asset is not eligible for disposal.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: index.php?page=assets&sub=browse');
            exit;
        }

        $errors = [];
        if (empty($reason)) $errors[] = 'Reason is required.';

        // File upload
        $document = null;
        if (isset($_FILES['supporting_document']) && $_FILES['supporting_document']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/disposal/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $filename = time() . '_' . basename($_FILES['supporting_document']['name']);
            $target = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['supporting_document']['tmp_name'], $target)) {
                $document = 'uploads/disposal/' . $filename;
            } else {
                $errors[] = 'Failed to upload document.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = ['asset_id' => $assetId, 'reason' => $reason];
            header('Location: index.php?page=disposal&sub=request&asset_id=' . $assetId);
            exit;
        }

        $data = [
            'asset_id' => $assetId,
            'requested_by' => $_SESSION['user_id'],
            'reason' => $reason,
            'supporting_document' => $document,
        ];

        $requestId = $this->disposalModel->create($data);
        if ($requestId) {
            // Update asset status to pending_disposal
            $this->disposalModel->setAssetPendingDisposal($assetId);
            $_SESSION['flash'] = 'Disposal request submitted for review.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash'] = 'Failed to submit disposal request.';
            $_SESSION['flash_type'] = 'danger';
        }
        header('Location: index.php?page=disposal');
        exit;
    }

    /**
     * Show review page for a specific disposal request (admin only).
     */
    public function review() {
        if ($_SESSION['role'] !== 'admin') {
            header('Location: index.php');
            exit;
        }
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            header('Location: index.php?page=disposal');
            exit;
        }
        $request = $this->disposalModel->getById($id);
        if (!$request) {
            $_SESSION['flash'] = 'Request not found.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: index.php?page=disposal');
            exit;
        }
        $pageTitle = 'Review Disposal Request';
        $currentPage = 'disposal';
        $viewFile = __DIR__ . '/../Views/disposal/review.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    /**
     * Process approval or rejection (admin only).
     */
    public function process() {
        if ($_SESSION['role'] !== 'admin') {
            header('Location: index.php');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=disposal');
            exit;
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $action = $_POST['action'] ?? '';
        $remarks = trim($_POST['remarks'] ?? '');

        if (!$id || !in_array($action, ['approve', 'reject'])) {
            $_SESSION['flash'] = 'Invalid action.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: index.php?page=disposal');
            exit;
        }

        $request = $this->disposalModel->getById($id);
        if (!$request) {
            $_SESSION['flash'] = 'Request not found.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: index.php?page=disposal');
            exit;
        }

        if ($action === 'approve') {
            $status = 'approved';
            $newAssetStatus = 'disposed';
        } else {
            $status = 'rejected';
            $newAssetStatus = 'active'; // revert to active
        }

        // Update request
        $updated = $this->disposalModel->updateStatus($id, $status, $_SESSION['user_id'], $remarks);
        if ($updated) {
            // Update asset status
            $this->disposalModel->updateAssetStatus($request['asset_id'], $newAssetStatus);
            $_SESSION['flash'] = 'Disposal request ' . $status . ' successfully.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash'] = 'Failed to process request.';
            $_SESSION['flash_type'] = 'danger';
        }
        header('Location: index.php?page=disposal');
        exit;
    }
}