<?php
namespace App\Controllers;

use App\Models\UserModel;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class UserController {
    /** @var UserModel */
    private $userModel;

    public function __construct() {
        // Only admin can access
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: index.php');
            exit;
        }
        $this->userModel = new UserModel();
    }

    public function index() {
        $users = $this->userModel->getAllUsers();
        $pageTitle = 'User Management';
        $currentPage = 'users';
        $viewFile = __DIR__ . '/../Views/users/index.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function add() {
        $personnel = $this->userModel->getPersonnelList();
        $offices = $this->userModel->getOfficeList();
        $isEdit = false;

        if ($this->isAjaxRequest()) {
            require __DIR__ . '/../Views/users/form.php';
            return;
        }

        $pageTitle = 'Add User';
        $currentPage = 'users';
        $viewFile = __DIR__ . '/../Views/users/form.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            header('Location: index.php?page=users');
            exit;
        }
        $user = $this->userModel->getUserById($id);
        if (!$user) {
            header('Location: index.php?page=users');
            exit;
        }
        $personnel = $this->userModel->getPersonnelList();
        $offices = $this->userModel->getOfficeList();
        $isEdit = true;

        if ($this->isAjaxRequest()) {
            require __DIR__ . '/../Views/users/form.php';
            return;
        }

        $pageTitle = 'Edit User';
        $currentPage = 'users';
        $viewFile = __DIR__ . '/../Views/users/form.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=users');
            exit;
        }
        $isAjax = $this->isAjaxRequest();

        $id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        $data = [
            'username' => trim($_POST['username']),
            'role' => $_POST['role'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'personnel_id' => (int)$_POST['personnel_id'],
        ];

        $creatingNewPersonnel = isset($_POST['new_personnel']) && $_POST['new_personnel'] == 1;

        // If new personnel selected, get details
        if ($creatingNewPersonnel) {
            $data['full_name'] = trim($_POST['full_name']);
            $data['position'] = trim($_POST['position']);
            $data['designation'] = trim($_POST['designation']);
            $data['office_id'] = (int)$_POST['office_id'];
            // Personnel created here writes to the same table as Employee
            // Management, so Salary Grade is required here too.
            $data['salary_grade'] = (int)($_POST['salary_grade'] ?? 0);
        } else {
            // Use existing personnel
            $personnel = $this->userModel->getPersonnelById($data['personnel_id']); // we need this method
            if ($personnel) {
                $data['full_name'] = $personnel['full_name'];
                $data['position'] = $personnel['position'];
                $data['designation'] = $personnel['designation'];
                $data['office_id'] = $personnel['office_id'];
                $data['salary_grade'] = $personnel['salary_grade'];
            }
        }

        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }

        // Validation
        $errors = [];
        if (empty($data['username'])) $errors[] = 'Username is required.';
        if (empty($data['role'])) $errors[] = 'Role is required.';
        if (!$id && empty($data['password'])) $errors[] = 'Password is required for new users.';
        if ($id === 0 && empty($data['personnel_id']) && empty($data['full_name'])) $errors[] = 'Personnel is required.';
        if ($creatingNewPersonnel && (empty($data['salary_grade']) || $data['salary_grade'] < 1 || $data['salary_grade'] > 30)) {
            $errors[] = 'A valid Salary Grade (1–30) is required for new personnel.';
        }

        if (!empty($errors)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => $errors]);
                return;
            }
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $data;
            header('Location: index.php?page=users&sub=' . ($id ? 'edit&id=' . $id : 'add'));
            exit;
        }

        if ($id) {
            $success = $this->userModel->updateUser($id, $data);
        } else {
            $success = $this->userModel->createUser($data);
        }

        if (!$success) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => ['Failed to save user.']]);
                return;
            }
            $_SESSION['flash'] = 'Failed to save user.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: index.php?page=users&sub=' . ($id ? 'edit&id=' . $id : 'add'));
            exit;
        }

        unset($_SESSION['form_errors'], $_SESSION['form_data']);
        $_SESSION['flash'] = 'User saved successfully.';
        $_SESSION['flash_type'] = 'success';

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'User saved successfully.']);
            return;
        }
        header('Location: index.php?page=users');
        exit;
    }

    public function delete() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id) {
            $this->userModel->deleteUser($id);
            $_SESSION['flash'] = 'User deactivated.';
            $_SESSION['flash_type'] = 'warning';
        }
        header('Location: index.php?page=users');
        exit;
    }

    /**
     * True if the current request was sent via fetch()/XHR (not a plain form submit).
     * @return bool
     */
    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}