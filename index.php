<?php
use App\Controllers\LoginController;
use App\Controllers\DashboardController;
use App\Controllers\AssetController;
use App\Controllers\CustodyController;
use App\Controllers\AuditController;
use App\Controllers\ReportController;
use App\Controllers\UserController;

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
            case 'custodian_assets_json':
                $controller->custodianAssetsJson();
                break;
            case 'qr':
                $controller->qr();
                break;
            case 'scan':
                $controller->scan();
                break;
            case 'by_office':
                $controller->byOffice();
                break;
            case 'bulk_qr':
                $controller->bulkQr();
                break;
            case 'verify':
                $controller->verify();
                break;
            // ===== END ADD =====
            default:
                $controller->browse();
        }
        break;
     case 'custody':
        $controller = new CustodyController();
        $sub = isset($_GET['sub']) ? $_GET['sub'] : 'index';
        switch ($sub) {
            case 'index':
            default:
                $controller->index();
                break;
            case 'add':
                $controller->add();
                break;
            case 'edit':
                $controller->edit();
                break;
            case 'save':
                $controller->save();
                break;
            case 'delete':
                $controller->delete();
                break;
            case 'office':
                $controller->office();
                break;
            case 'custodian':
                $controller->custodian();
                break;
            case 'search_custodians':
                $controller->searchCustodians();
                break;
        }
        break;
    case 'audit':
        $controller = new AuditController();
        $controller->index();
        break;
    case 'reports':
        $controller = new ReportController();
        $sub = isset($_GET['sub']) ? $_GET['sub'] : 'index';
        switch ($sub) {
            case 'index':
                $controller->index();
                break;
            case 'add':
                $controller->add();
                break;
            case 'save':
                $controller->save();
                break;
            case 'view':
                $controller->view();
                break;
            case 'delete':
                $controller->delete();
                break;
            case 'preview':
                $controller->preview();
                break;
            case 'generate':
                $controller->generate();
                break;
            case 'preview_ajax':
                $controller->previewAjax();
                break;
            case 'export_docx':
                $controller->exportDocx();
                break;
            default:
                $controller->index();
                break;
        }
        break;
    case 'users':
        $controller = new UserController();
        $sub = isset($_GET['sub']) ? $_GET['sub'] : 'index';
        switch ($sub) {
            case 'index':
            default:
                $controller->index();
                break;
            case 'add':
                $controller->add();
                break;
            case 'edit':
                $controller->edit();
                break;
            case 'save':
                $controller->save();
                break;
            case 'delete':
                $controller->delete();
                break;
        }
        break;
    default:
        // Dashboard
        $controller = new DashboardController();
        $controller->index();
}