<?php
namespace App\Controllers;

use App\Models\AssetModel;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class AssetController {
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
     * List all assets
     */
    public function list() {
        $assets = $this->assetModel->getAll();
        require_once __DIR__ . '/../Views/assets/list.php';
    }

    /**
     * Show add form
     */
    public function add() {
        $accounts = $this->assetModel->getAssetAccounts();
        // Define enum options for status and condition (from schema)
        $statusOptions = ['active', 'inactive', 'disposed', 'missing']; // adjust based on your enum
        $conditionOptions = ['good', 'fair', 'poor', 'damaged', 'obsolete'];
        require_once __DIR__ . '/../Views/assets/form.php';
    }

    /**
     * Show edit form
     */
    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            header('Location: index.php?page=assets&sub=list');
            exit;
        }
        $asset = $this->assetModel->getById($id);
        if (!$asset) {
            header('Location: index.php?page=assets&sub=list');
            exit;
        }
        $accounts = $this->assetModel->getAssetAccounts();
        $statusOptions = ['active', 'inactive', 'disposed', 'missing'];
        $conditionOptions = ['good', 'fair', 'poor', 'damaged', 'obsolete'];
        require_once __DIR__ . '/../Views/assets/form.php';
    }

    /**
     * Save (create or update)
     */
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=assets&sub=list');
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
            // Pass errors back to form
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $data;
            header('Location: index.php?page=assets&sub=' . ($id ? 'edit&id=' . $id : 'add'));
            exit;
        }

        if ($id) {
            // Update
            $success = $this->assetModel->update($id, $data);
        } else {
            // Create
            $success = $this->assetModel->create($data);
        }

        if ($success) {
            unset($_SESSION['form_errors'], $_SESSION['form_data']);
            $_SESSION['flash'] = 'Asset saved successfully.';
        } else {
            $_SESSION['form_errors'] = ['Failed to save asset. Please try again.'];
            header('Location: index.php?page=assets&sub=' . ($id ? 'edit&id=' . $id : 'add'));
            exit;
        }

        header('Location: index.php?page=assets&sub=list');
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
        }
        header('Location: index.php?page=assets&sub=list');
        exit;
    }
}