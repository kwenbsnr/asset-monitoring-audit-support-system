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
        // Only supply_officer or admin can access
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['supply_officer', 'admin'])) {
            header('Location: index.php');
            exit;
        }
        $this->assetModel = new AssetModel();
    }

    /**
     * Browse categories – shows root categories or sub‑categories.
     */
    public function browse() {
        $catId = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : null;
        if ($catId) {
            // If this category has children, show them; otherwise show assets
            if ($this->assetModel->hasChildren($catId)) {
                $categories = $this->assetModel->getCategoryTree($catId);
                $pageTitle = 'Sub‑Categories';
                $currentPage = 'assets';
                $viewFile = __DIR__ . '/../Views/assets/categories.php';
                require_once __DIR__ . '/../Views/layouts/main.php';
            } else {
                // Leaf category – show assets
                $assets = $this->assetModel->getAssetsByCategory($catId);
                $pageTitle = 'Assets';
                $currentPage = 'assets';
                $viewFile = __DIR__ . '/../Views/assets/list.php';
                require_once __DIR__ . '/../Views/layouts/main.php';
            }
        } else {
            // No category selected – show top‑level categories
            $categories = $this->assetModel->getCategoryTree(null);
            $pageTitle = 'Asset Categories';
            $currentPage = 'assets';
            $viewFile = __DIR__ . '/../Views/assets/categories.php';
            require_once __DIR__ . '/../Views/layouts/main.php';
        }
    }

    /**
     * Show add form
     */
    public function add() {
        $accounts = $this->assetModel->getAssetAccounts();
        $statusOptions = ['active', 'inactive', 'disposed', 'missing'];
        $conditionOptions = ['good', 'fair', 'poor', 'damaged', 'obsolete'];
        $pageTitle = 'Add Asset';
        $currentPage = 'assets';
        $viewFile = __DIR__ . '/../Views/assets/form.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    /**
     * Show edit form
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
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    /**
     * Save (create or update)
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
            'status' => $_POST['status'],
            'condition' => $_POST['condition'],
            'remarks' => trim($_POST['remarks'] ?? ''),
        ];

        // Basic validation
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
            $success = $this->assetModel->create($data);
        }

        if ($success) {
            unset($_SESSION['form_errors'], $_SESSION['form_data']);
            $_SESSION['flash'] = 'Asset saved successfully.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash'] = 'Failed to save asset. Please try again.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: index.php?page=assets&sub=' . ($id ? 'edit&id=' . $id : 'add'));
            exit;
        }

        header('Location: index.php?page=assets&sub=browse');
        exit;
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
}