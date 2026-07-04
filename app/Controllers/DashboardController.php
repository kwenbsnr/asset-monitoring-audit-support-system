<?php
namespace App\Controllers;

use App\Models\DashboardModel;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class DashboardController {
    /** @var DashboardModel */
    private $dashboardModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php');
            exit;
        }
        $this->dashboardModel = new DashboardModel();
    }

    public function index() {
        // Fetch all data
        $totalAssets = $this->dashboardModel->getTotalAssets();
        $statusCounts = $this->dashboardModel->getAssetStatusCounts();
        $categoryCounts = $this->dashboardModel->getAssetCategoryCounts();
        $activeInactive = $this->dashboardModel->getActiveInactiveCounts();
        $recentCustody = $this->dashboardModel->getRecentCustody();
        $recentAudit = $this->dashboardModel->getRecentAudit();

        // Prepare data for charts
        $chartStatusLabels = [];
        $chartStatusData = [];
        foreach ($statusCounts as $row) {
            $chartStatusLabels[] = ucfirst($row['status']);
            $chartStatusData[] = (int)$row['count'];
        }

        $chartCategoryLabels = [];
        $chartCategoryData = [];
        foreach ($categoryCounts as $row) {
            if ($row['category'] === null) continue;
            $chartCategoryLabels[] = $row['category'];
            $chartCategoryData[] = (int)$row['count'];
        }

        $pageTitle = 'Dashboard';
        $currentPage = 'dashboard';
        $viewFile = __DIR__ . '/../Views/dashboard.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }
}