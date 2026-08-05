<?php
namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Helpers\SalaryGradeHelper;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class EmployeeController {
    /** @var EmployeeModel */
    private $employeeModel;

    public function __construct() {
        // Employee Management is System Administrator only.
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: index.php');
            exit;
        }
        $this->employeeModel = new EmployeeModel();
    }

    public function index() {
        $filters = [];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (!empty($_GET['office_id'])) $filters['office_id'] = (int)$_GET['office_id'];
        if (!empty($_GET['search'])) $filters['search'] = trim($_GET['search']);

        $employees = $this->employeeModel->getAll($filters);
        $offices = $this->employeeModel->getOffices();

        $pageTitle = 'Employee Management';
        $currentPage = 'employees';
        $viewFile = __DIR__ . '/../Views/employees/index.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function add() {
        $offices = $this->employeeModel->getOffices();
        $salaryGradeBrackets = SalaryGradeHelper::getBrackets();
        $pageTitle = 'Add Employee';
        $currentPage = 'employees';
        $viewFile = __DIR__ . '/../Views/employees/form.php';
        $isEdit = false;
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            header('Location: index.php?page=employees');
            exit;
        }
        $record = $this->employeeModel->getById($id);
        if (!$record) {
            header('Location: index.php?page=employees');
            exit;
        }
        $offices = $this->employeeModel->getOffices();
        $salaryGradeBrackets = SalaryGradeHelper::getBrackets();
        $activeAssetCount = $this->employeeModel->getActiveAssetCount($id);
        $pageTitle = 'Edit Employee';
        $currentPage = 'employees';
        $viewFile = __DIR__ . '/../Views/employees/form.php';
        $isEdit = true;
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=employees');
            exit;
        }

        $id = isset($_POST['personnel_id']) ? (int)$_POST['personnel_id'] : 0;
        $data = [
            'employee_id'       => trim($_POST['employee_id'] ?? ''),
            'full_name'         => trim($_POST['full_name'] ?? ''),
            'position'          => trim($_POST['position'] ?? ''),
            'designation'       => trim($_POST['designation'] ?? ''),
            'office_id'         => (int)($_POST['office_id'] ?? 0),
            'salary_grade'      => (int)($_POST['salary_grade'] ?? 0),
            'employment_status' => $_POST['employment_status'] ?? 'active',
        ];

        $errors = [];
        if (empty($data['employee_id'])) $errors[] = 'Employee ID is required.';
        if (empty($data['full_name'])) $errors[] = 'Full name is required.';
        if (empty($data['office_id'])) $errors[] = 'Office is required.';
        if ($data['salary_grade'] < SalaryGradeHelper::minGrade() || $data['salary_grade'] > SalaryGradeHelper::maxGrade()) {
            $errors[] = 'A valid Salary Grade (' . SalaryGradeHelper::minGrade() . '–' . SalaryGradeHelper::maxGrade() . ') is required.';
        }
        if (!in_array($data['employment_status'], ['active', 'retired', 'transferred', 'inactive'], true)) {
            $errors[] = 'Invalid employment status.';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $data;
            header('Location: index.php?page=employees&sub=' . ($id ? 'edit&id=' . $id : 'add'));
            exit;
        }

        if ($id) {
            $success = $this->employeeModel->update($id, $data);
        } else {
            $success = $this->employeeModel->create($data) !== false;
        }

        if ($success) {
            unset($_SESSION['form_errors'], $_SESSION['form_data']);
            $_SESSION['flash'] = 'Employee profile saved successfully.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash'] = 'Failed to save employee profile.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: index.php?page=employees&sub=' . ($id ? 'edit&id=' . $id : 'add'));
            exit;
        }
        header('Location: index.php?page=employees');
        exit;
    }

    /**
     * Change employment status only (Retire / Transfer to Another Office /
     * Inactive / Reactivate) — this is the "soft delete" the client asked
     * for, triggered from the status-change modal on the employee list.
     */
    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=employees');
            exit;
        }
        $id = isset($_POST['personnel_id']) ? (int)$_POST['personnel_id'] : 0;
        $status = $_POST['employment_status'] ?? '';

        if (!$id || !in_array($status, ['active', 'retired', 'transferred', 'inactive'], true)) {
            $_SESSION['flash'] = 'Invalid status change request.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: index.php?page=employees');
            exit;
        }

        $this->employeeModel->updateStatus($id, $status);
        $_SESSION['flash'] = 'Employee status updated to "' . ucfirst($status) . '".';
        $_SESSION['flash_type'] = 'success';
        header('Location: index.php?page=employees');
        exit;
    }
}
