<?php
namespace App\Controllers;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class DashboardController {
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php');
            exit;
        }
        $pageTitle = 'Dashboard';
        $currentPage = 'dashboard';
        $viewFile = __DIR__ . '/../Views/dashboard.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }
}