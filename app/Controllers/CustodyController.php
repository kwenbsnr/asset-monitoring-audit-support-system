<?php
namespace App\Controllers;

use App\Models\CustodyModel;
use App\Models\EmployeeModel;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class CustodyController {
    /** @var CustodyModel */
    private $custodyModel;

    public function __construct() {
        // Allow asset_manager and admin to access custody (view, add, edit)
        // Delete is restricted to admin in the delete method itself.
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['asset_manager', 'admin'])) {
            header('Location: index.php');
            exit;
        }
        $this->custodyModel = new CustodyModel();
    }

    public function index() {
        $offices = $this->custodyModel->getOfficesWithCustody();
        $pageTitle = 'Custodial Tracking - Offices';
        $currentPage = 'custody';
        $viewFile = __DIR__ . '/../Views/custody/offices.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function office() {
        $officeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$officeId) {
            header('Location: index.php?page=custody');
            exit;
        }
        $custodians = $this->custodyModel->getCustodiansByOffice($officeId);
        $office = $this->custodyModel->getOfficeById($officeId);
        $pageTitle = 'Custodians - ' . ($office ? $office['name'] : '');
        $currentPage = 'custody';
        $viewFile = __DIR__ . '/../Views/custody/custodians.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function custodian() {
        $custodianId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$custodianId) {
            header('Location: index.php?page=custody');
            exit;
        }
        $page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;
        $assets = $this->custodyModel->getAssetsByCustodian($custodianId, $limit, $offset);
        $total = $this->custodyModel->countAssetsByCustodian($custodianId);
        $totalPages = ceil($total / $limit);
        $custodian = $this->custodyModel->getPersonnelById($custodianId);
        $pageTitle = 'Assets under ' . ($custodian ? $custodian['full_name'] : '');
        $currentPage = 'custody';
        $viewFile = __DIR__ . '/../Views/custody/assets.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function searchCustodians() {
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        if (strlen($search) < 2) {
            header('Location: index.php?page=custody');
            exit;
        }
        $custodians = $this->custodyModel->searchCustodians($search);
        $pageTitle = 'Search Results';
        $currentPage = 'custody';
        $viewFile = __DIR__ . '/../Views/custody/search.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    // ===== Add / Edit / Save (allowed for asset_manager and admin) =====
    public function add() {
        $assetId = isset($_GET['asset_id']) ? (int)$_GET['asset_id'] : 0;
        $personnel = $this->custodyModel->getPersonnel();
        $offices = $this->custodyModel->getOffices();
        $assets = $this->custodyModel->getAssets();
        $pageTitle = 'Assign Custody';
        $currentPage = 'custody';
        $viewFile = __DIR__ . '/../Views/custody/form.php';
        $isEdit = false;
        $preSelectedAsset = $assetId ? $assetId : 0;
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $assetId = isset($_GET['asset_id']) ? (int)$_GET['asset_id'] : 0;

        // If asset_id is provided but no custody_id, find the active custody
        if ($assetId && !$id) {
            $activeCustody = $this->custodyModel->getActiveCustody($assetId);
            if ($activeCustody) {
                header('Location: index.php?page=custody&sub=edit&id=' . $activeCustody['asset_custodies_id']);
                exit;
            } else {
                // No active custody – redirect to add
                header('Location: index.php?page=custody&sub=add&asset_id=' . $assetId);
                exit;
            }
        }

        if (!$id) {
            header('Location: index.php?page=custody');
            exit;
        }
        $record = $this->custodyModel->getById($id);
        if (!$record) {
            header('Location: index.php?page=custody');
            exit;
        }
        $personnel = $this->custodyModel->getPersonnel();
        $offices = $this->custodyModel->getOffices();
        $assets = $this->custodyModel->getAssets();
        $pageTitle = 'Edit Custody';
        $currentPage = 'custody';
        $viewFile = __DIR__ . '/../Views/custody/form.php';
        $isEdit = true;
        $preSelectedAsset = 0;
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=custody');
            exit;
        }

        $id = isset($_POST['custody_id']) ? (int)$_POST['custody_id'] : 0;
        $data = [
            'asset_id' => (int)$_POST['asset_id'],
            'custodian_id' => (int)$_POST['custodian_id'],
            'office_id' => (int)$_POST['office_id'],
            'property_number' => trim($_POST['property_number'] ?? ''),
            'effectivity_date' => $_POST['effectivity_date'],
            'status' => $_POST['status'],
        ];

        if ($id) {
            $data['end_date'] = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        }

        $errors = [];
        if (empty($data['asset_id'])) $errors[] = 'Asset is required.';
        if (empty($data['custodian_id'])) $errors[] = 'Custodian is required.';
        if (empty($data['office_id'])) $errors[] = 'Office is required.';
        if (empty($data['effectivity_date'])) $errors[] = 'Effectivity date is required.';
        if ($data['property_number'] === '') $errors[] = 'Property number is required.';

        // ===== Salary Grade vs. asset value validation =====
        // Only meaningful when this record is actively assigning a custodian.
        if (empty($errors) && $data['status'] === 'active' && $data['asset_id'] && $data['custodian_id']) {
            $asset = $this->custodyModel->getAssetById($data['asset_id']);
            if ($asset) {
                $employeeModel = new EmployeeModel();
                $sgCheck = $employeeModel->validateAssetAssignment($data['custodian_id'], $asset['acquisition_cost']);
                if ($sgCheck !== true) {
                    $errors[] = $sgCheck;
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $data;
            header('Location: index.php?page=custody&sub=' . ($id ? 'edit&id=' . $id : 'add'));
            exit;
        }

        if ($id) {
            $success = $this->custodyModel->update($id, $data);
        } else {
            $success = $this->custodyModel->create($data);
        }

        if ($success) {
            unset($_SESSION['form_errors'], $_SESSION['form_data']);
            $_SESSION['flash'] = 'Custody record saved successfully.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash'] = 'Failed to save custody record.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: index.php?page=custody&sub=' . ($id ? 'edit&id=' . $id : 'add'));
            exit;
        }
        header('Location: index.php?page=custody');
        exit;
    }

    /**
     * Delete (end custody) – only admin.
     */
    public function delete() {
        if ($_SESSION['role'] !== 'admin') {
            header('Location: index.php');
            exit;
        }
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id) {
            $record = $this->custodyModel->getById($id);
            if ($record) {
                $data = [
                    'custodian_id' => $record['custodian_id'],
                    'office_id' => $record['office_id'],
                    'property_number' => $record['property_number'],
                    'effectivity_date' => $record['effectivity_date'],
                    'end_date' => date('Y-m-d'),
                    'status' => 'inactive'
                ];
                $this->custodyModel->update($id, $data);
                $_SESSION['flash'] = 'Custody record ended.';
                $_SESSION['flash_type'] = 'warning';
            }
        }
        header('Location: index.php?page=custody');
        exit;
    }
}