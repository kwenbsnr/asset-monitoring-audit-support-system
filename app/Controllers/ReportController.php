<?php
namespace App\Controllers;

use App\Models\ReportModel;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class ReportController {
    /** @var ReportModel */
    private $reportModel;

    public function __construct() {
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['supply_officer', 'admin'])) {
            header('Location: index.php');
            exit;
        }
        $this->reportModel = new ReportModel();
    }

    public function index() {
        $reports = $this->reportModel->getAll();
        $pageTitle = 'Reports';
        $currentPage = 'reports';
        $viewFile = __DIR__ . '/../Views/reports/index.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function add() {
        $offices = $this->reportModel->getOffices();
        $users = $this->reportModel->getUsers();
        $assets = $this->reportModel->getAssets();
        $pageTitle = 'Create Report';
        $currentPage = 'reports';
        $viewFile = __DIR__ . '/../Views/reports/form.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=reports');
            exit;
        }

        // Save report header
        $data = [
            'report_number' => trim($_POST['report_number']),
            'report_date' => $_POST['report_date'],
            'office_id' => (int)$_POST['office_id'],
            'prepared_by' => (int)$_POST['prepared_by'],
            'status' => $_POST['status'],
            'remarks' => trim($_POST['remarks'] ?? ''),
        ];

        $errors = [];
        if (empty($data['report_number'])) $errors[] = 'Report number is required.';
        if (empty($data['report_date'])) $errors[] = 'Report date is required.';
        if (empty($data['office_id'])) $errors[] = 'Office is required.';

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $data;
            header('Location: index.php?page=reports&sub=add');
            exit;
        }

        $reportId = $this->reportModel->create($data);
        if ($reportId) {
            // Add items if any
            if (isset($_POST['items']) && is_array($_POST['items'])) {
                foreach ($_POST['items'] as $item) {
                    if (!empty($item['asset_id'])) {
                        $itemData = [
                            'asset_report_id' => $reportId,
                            'asset_id' => (int)$item['asset_id'],
                            'verification_status' => $item['verification_status'] ?? 'pending',
                            'asset_condition' => $item['asset_condition'] ?? 'good',
                            'verified_by' => (int)$item['verified_by'] ?? 0,
                            'remarks' => trim($item['remarks'] ?? ''),
                        ];
                        $this->reportModel->addItem($itemData);
                    }
                }
            }
            $_SESSION['flash'] = 'Report created successfully.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash'] = 'Failed to create report.';
            $_SESSION['flash_type'] = 'danger';
        }
        header('Location: index.php?page=reports');
        exit;
    }

    public function view() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            header('Location: index.php?page=reports');
            exit;
        }
        $report = $this->reportModel->getById($id);
        if (!$report) {
            header('Location: index.php?page=reports');
            exit;
        }
        $pageTitle = 'View Report';
        $currentPage = 'reports';
        $viewFile = __DIR__ . '/../Views/reports/view.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function delete() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id) {
            $this->reportModel->delete($id);
            $_SESSION['flash'] = 'Report deleted.';
            $_SESSION['flash_type'] = 'warning';
        }
        header('Location: index.php?page=reports');
        exit;
    }
}