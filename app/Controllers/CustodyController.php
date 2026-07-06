<?php
namespace App\Controllers;

use App\Models\CustodyModel;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class CustodyController {
    /** @var CustodyModel */
    private $custodyModel;

    public function __construct() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supply_officer') {
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

    // ===== Existing methods (add, edit, save, delete) remain unchanged =====
    public function add() {
        $personnel = $this->custodyModel->getPersonnel();
        $offices = $this->custodyModel->getOffices();
        $assets = $this->custodyModel->getAssets();
        $pageTitle = 'Assign Custody';
        $currentPage = 'custody';
        $viewFile = __DIR__ . '/../Views/custody/form.php';
        $isEdit = false;
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
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
            'accountability_document' => trim($_POST['accountability_document'] ?? ''),
            'accountability_reference' => trim($_POST['accountability_reference'] ?? ''),
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

    public function delete() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id) {
            $record = $this->custodyModel->getById($id);
            if ($record) {
                $data = [
                    'custodian_id' => $record['custodian_id'],
                    'office_id' => $record['office_id'],
                    'accountability_document' => $record['accountability_document'],
                    'accountability_reference' => $record['accountability_reference'],
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