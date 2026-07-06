<?php
namespace App\Controllers;


use App\Models\AssetModel;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class AssetController {
    /** @var AssetModel */
    private $assetModel;

    public function __construct() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supply_officer') {
            header('Location: index.php');
            exit;
        }
        $this->assetModel = new AssetModel();
    }

    public function browse() {
        $catId = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : null;
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;
        $filters = $this->getFiltersFromGet();
        $currentCategory = null;

        if ($catId) {
            $currentCategory = $this->assetModel->getCategory($catId);
            if (!$currentCategory) {
                header('Location: index.php?page=assets&sub=browse');
                exit;
            }
            if ($this->assetModel->hasChildren($catId)) {
                $categories = $this->assetModel->getCategoryTree($catId);
                $pageTitle = 'Sub‑Categories';
                $currentPage = 'assets';
                $viewFile = __DIR__ . '/../Views/assets/categories.php';
                require_once __DIR__ . '/../Views/layouts/main.php';
            } else {
                $assets = $this->assetModel->getAssetsByCategory($catId, $search, $filters);
                $pageTitle = 'Assets' . ($search ? ' (Search: ' . htmlspecialchars($search) . ')' : '');
                $currentPage = 'assets';
                $viewFile = __DIR__ . '/../Views/assets/list.php';
                require_once __DIR__ . '/../Views/layouts/main.php';
            }
        } else {
            $categories = $this->assetModel->getCategoryTree(null);
            $pageTitle = 'Asset Categories';
            $currentPage = 'assets';
            $viewFile = __DIR__ . '/../Views/assets/categories.php';
            require_once __DIR__ . '/../Views/layouts/main.php';
        }
    }

    public function listAll() {
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;
        $filters = $this->getFiltersFromGet();
        $assets = $this->assetModel->getAllAssets($search, $filters);
        $pageTitle = 'All Assets' . ($search ? ' (Search: ' . htmlspecialchars($search) . ')' : '');
        $currentPage = 'assets';
        $viewFile = __DIR__ . '/../Views/assets/list.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    private function getFiltersFromGet() {
        $filters = [];
        if (isset($_GET['field']) && !empty($_GET['field'])) $filters['field'] = $_GET['field'];
        if (isset($_GET['status']) && !empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (isset($_GET['condition']) && !empty($_GET['condition'])) $filters['condition'] = $_GET['condition'];
        if (isset($_GET['date_from']) && !empty($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
        if (isset($_GET['date_to']) && !empty($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];
        if (isset($_GET['cost_from']) && $_GET['cost_from'] !== '') $filters['cost_from'] = (float)$_GET['cost_from'];
        if (isset($_GET['cost_to']) && $_GET['cost_to'] !== '') $filters['cost_to'] = (float)$_GET['cost_to'];
        return $filters;
    }

    public function details() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $qr = isset($_GET['qr']) ? trim($_GET['qr']) : null;
        $query = isset($_GET['q']) ? trim($_GET['q']) : null;

        if ($id) {
            $data = $this->assetModel->getFullDetails($id);
        } elseif ($qr) {
            $asset = $this->assetModel->getByQrCode($qr);
            if ($asset) {
                $data = $this->assetModel->getFullDetails($asset['asset_id']);
            } else {
                $data = null;
            }
        } elseif ($query) {
            // Search by asset_code, description, or serial_number (return first match)
            $asset = $this->assetModel->searchAssetByText($query);
            if ($asset) {
                $data = $this->assetModel->getFullDetails($asset['asset_id']);
            } else {
                $data = null;
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Asset ID, QR code, or search term required']);
            return;
        }

        if (!$data) {
            http_response_code(404);
            echo json_encode(['error' => 'Asset not found']);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Show add form – status & condition are hidden (defaults applied).
     */
    public function add() {
        $accounts = $this->assetModel->getAssetAccounts();
        $statusOptions = ['active', 'inactive', 'disposed', 'missing'];
        $conditionOptions = ['good', 'fair', 'poor', 'damaged', 'obsolete'];
        $pageTitle = 'Add Asset';
        $currentPage = 'assets';
        $viewFile = __DIR__ . '/../Views/assets/form.php';
        $isEdit = false; // new asset
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    /**
     * Show edit form – status & condition are displayed.
     */
    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            header('Location: index.php?page=assets&sub=browse');
            exit;
        }
        $asset = $this->assetModel->getById($id);
        if (!$asset) {
            header('Location: index.php?page=assets&sub=browse');
            exit;
        }
        $accounts = $this->assetModel->getAssetAccounts();
        $statusOptions = ['active', 'inactive', 'disposed', 'missing'];
        $conditionOptions = ['good', 'fair', 'poor', 'damaged', 'obsolete'];
        $pageTitle = 'Edit Asset';
        $currentPage = 'assets';
        $viewFile = __DIR__ . '/../Views/assets/form.php';
        $isEdit = true; // editing existing asset
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    /**
     * Save (create or update) – defaults status/condition for new assets.
     */
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=assets&sub=browse');
            exit;
        }

        $id = isset($_POST['asset_id']) ? (int)$_POST['asset_id'] : 0;
        $data = [
            'asset_code' => trim($_POST['asset_code']),
            'description' => trim($_POST['description']),
            'brand' => trim($_POST['brand'] ?? ''),
            'model' => trim($_POST['model'] ?? ''),
            'serial_number' => trim($_POST['serial_number'] ?? ''),
            'acquisition_cost' => $_POST['acquisition_cost'] ? (float)$_POST['acquisition_cost'] : null,
            'acquisition_date' => $_POST['acquisition_date'] ?: null,
            'asset_accounts_id' => (int)$_POST['asset_accounts_id'],
            // For new assets, force default status/condition
            'status' => $id ? $_POST['status'] : 'active',
            'condition' => $id ? $_POST['condition'] : 'good',
            'remarks' => trim($_POST['remarks'] ?? ''),
        ];

        $errors = [];
        if (empty($data['asset_code'])) $errors[] = 'Asset code is required.';
        if (empty($data['description'])) $errors[] = 'Description is required.';
        if (empty($data['asset_accounts_id'])) $errors[] = 'Account is required.';

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $data;
            header('Location: index.php?page=assets&sub=' . ($id ? 'edit&id=' . $id : 'add'));
            exit;
        }

        if ($id) {
            $success = $this->assetModel->update($id, $data);
        } else {
            $newId = $this->assetModel->create($data);
            if ($newId) {
                $success = true;
                $id = $newId; // set id for redirect
            } else {
                $success = false;
            }
        }

        if ($success) {
            unset($_SESSION['form_errors'], $_SESSION['form_data']);
            $_SESSION['flash'] = 'Asset saved successfully.';
            $_SESSION['flash_type'] = 'success';
            if ($id) {
                header('Location: index.php?page=assets&sub=edit&id=' . $id);
            } else {
                header('Location: index.php?page=assets&sub=browse');
            }
            exit;
        } else {
            $_SESSION['flash'] = 'Failed to save asset. Please try again.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: index.php?page=assets&sub=' . ($id ? 'edit&id=' . $id : 'add'));
            exit;
        }
    }


    /**
     * Delete (soft delete)
     */
    public function delete() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id) {
            $this->assetModel->delete($id);
            $_SESSION['flash'] = 'Asset deleted (soft delete).';
            $_SESSION['flash_type'] = 'warning';
        }
        header('Location: index.php?page=assets&sub=browse');
        exit;
    }

    /**
     * Search assets and return JSON for AJAX live search.
     */
    public function searchJson() {
        $query = isset($_GET['q']) ? trim($_GET['q']) : '';
        if (strlen($query) < 2) {
            http_response_code(400);
            echo json_encode(['error' => 'Please type at least 2 characters']);
            return;
        }
        $assets = $this->assetModel->searchAssets($query);
        header('Content-Type: application/json');
        echo json_encode($assets);
    }

    /**
     * Generate and output QR code for an asset.
     */
    public function qr() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            http_response_code(400);
            die('Asset ID required');
        }
        $asset = $this->assetModel->getById($id);
        if (!$asset) {
            http_response_code(404);
            die('Asset not found');
        }
        // Use the QR code reference (unique per asset)
        $content = $asset['qr_code_ref'];
        $download = isset($_GET['download']);
        \App\Helpers\QRGenerator::output($content, $download);
    }

    /**
     * Show QR scanner page.
     */
    public function scan() {
        $pageTitle = 'Scan QR Code';
        $currentPage = 'assets';
        $viewFile = __DIR__ . '/../Views/assets/scan.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }
}