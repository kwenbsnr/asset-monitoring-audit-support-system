<?php
namespace App\Controllers;

use App\Models\AuditModel;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class AuditController {
    /** @var AuditModel */
    private $auditModel;

    public function __construct() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: index.php');
            exit;
        }
        $this->auditModel = new AuditModel();
    }

    public function index() {
        $filters = [];
        if (isset($_GET['asset_id']) && !empty($_GET['asset_id'])) $filters['asset_id'] = (int)$_GET['asset_id'];
        if (isset($_GET['user_id']) && !empty($_GET['user_id'])) $filters['user_id'] = (int)$_GET['user_id'];
        if (isset($_GET['action_type']) && !empty($_GET['action_type'])) $filters['action_type'] = $_GET['action_type'];
        if (isset($_GET['module']) && !empty($_GET['module'])) $filters['module'] = $_GET['module'];
        if (isset($_GET['date_from']) && !empty($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
        if (isset($_GET['date_to']) && !empty($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];

        $logs = $this->auditModel->getLogs($filters);
        $actionTypes = $this->auditModel->getActionTypes();
        $modules = $this->auditModel->getModules();
        $users = $this->auditModel->getUsers();
        $assets = $this->auditModel->getAssets();

        $pageTitle = 'Audit Trail';
        $currentPage = 'audit';
        $viewFile = __DIR__ . '/../Views/audit/index.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }
}