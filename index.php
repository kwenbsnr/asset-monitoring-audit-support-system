<?php
use App\Controllers\LoginController;
use App\Controllers\DashboardController;
use App\Controllers\AssetController;

define('APP_START', true);
require_once __DIR__ . '/app/bootstrap.php';

// Start session if not already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- Logout ----
$action = isset($_GET['action']) ? $_GET['action'] : '';
if ($action === 'logout') {
    $controller = new LoginController();
    $controller->logout();
    exit;
}

// ---- Check login ----
if (!isset($_SESSION['user_id'])) {
    // Not logged in – show login
    $controller = new LoginController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $controller->login();
    } else {
        $controller->showLoginForm();
    }
    exit;
}

// ---- Logged in – route ----
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

switch ($page) {
    case 'assets':
        $controller = new AssetController();
        $sub = isset($_GET['sub']) ? $_GET['sub'] : 'browse';
        switch ($sub) {
            case 'browse':
                $controller->browse();
                break;
            case 'add':
                $controller->add();
                break;
            case 'edit':
                $controller->edit();
                break;
            case 'delete':
                $controller->delete();
                break;
            case 'save':
                $controller->save();
                break;
            case 'details':
                $controller->details();
                break;
            case 'list_all':
                $controller->listAll();
                break;
            case 'search_json':
                $controller->searchJson();
                break;
            case 'qr':
                $controller->qr();
                break;
            case 'scan':
                $controller->scan();
                break;
            default:
                $controller->browse();
        }
        break;
    default:
        // Dashboard
        $controller = new DashboardController();
        $controller->index();
}