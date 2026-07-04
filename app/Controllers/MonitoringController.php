<?php
namespace App\Controllers;

use App\Models\LocationModel;
use App\Models\AssetModel;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class MonitoringController {
    /** @var LocationModel */
    private $locationModel;
    /** @var AssetModel */
    private $assetModel;

    public function __construct() {
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['supply_officer', 'admin'])) {
            header('Location: index.php');
            exit;
        }
        $this->locationModel = new LocationModel();
        $this->assetModel = new AssetModel();
    }

    public function index() {
        $locations = $this->locationModel->getAll();
        $pageTitle = 'Location & Condition Monitoring';
        $currentPage = 'monitoring';
        $viewFile = __DIR__ . '/../Views/monitoring/index.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function add() {
        $assets = $this->locationModel->getAssets();
        $users = $this->locationModel->getUsers();
        $pageTitle = 'Add Location Update';
        $currentPage = 'monitoring';
        $viewFile = __DIR__ . '/../Views/monitoring/form.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=monitoring');
            exit;
        }

        $data = [
            'asset_id' => (int)$_POST['asset_id'],
            'location_name' => trim($_POST['location_name']),
            'site_type' => $_POST['site_type'],
            'description' => trim($_POST['description']),
            'recorded_by' => (int)$_POST['recorded_by'],
        ];

        // Also update asset condition if provided
        if (isset($_POST['condition']) && !empty($_POST['condition'])) {
            $assetData = ['condition' => $_POST['condition']];
            // We need to update the asset – but we'll do it after location insert.
            // Or we can use the AssetModel update method. Let's do it here.
            $assetId = $data['asset_id'];
            $stmt = $this->assetModel->updateCondition($assetId, $_POST['condition']);
        }

        $errors = [];
        if (empty($data['asset_id'])) $errors[] = 'Asset is required.';
        if (empty($data['location_name'])) $errors[] = 'Location name is required.';
        if (empty($data['recorded_by'])) $errors[] = 'Recorded by is required.';

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $data;
            header('Location: index.php?page=monitoring&sub=add');
            exit;
        }

        $success = $this->locationModel->create($data);

        if ($success) {
            unset($_SESSION['form_errors'], $_SESSION['form_data']);
            $_SESSION['flash'] = 'Location updated successfully.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash'] = 'Failed to update location.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: index.php?page=monitoring&sub=add');
            exit;
        }
        header('Location: index.php?page=monitoring');
        exit;
    }
}