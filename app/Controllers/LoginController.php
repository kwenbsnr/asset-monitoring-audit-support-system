<?php
namespace App\Controllers;

use App\Models\UserModel;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class LoginController {
    /** @var UserModel */
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function showLoginForm() {
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php');
            exit;
        }
        require_once __DIR__ . '/../Views/login.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];
        if (empty($username)) $errors[] = 'Username is required.';
        if (empty($password)) $errors[] = 'Password is required.';

        if (empty($errors)) {
            $user = $this->userModel->findByUsername($username);
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['users_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'] ?? $user['username'];
                $_SESSION['office'] = $user['office_name'] ?? 'N/A';
                $this->userModel->updateLastLogin($user['users_id']);
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Invalid username or password.';
            }
        }
        require_once __DIR__ . '/../Views/login.php';
    }

    public function logout() {
        $_SESSION = [];
        session_destroy();
        header('Location: index.php');
        exit;
    }
}