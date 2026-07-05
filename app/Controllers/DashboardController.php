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
        $activeInactive = $this->dashboardModel->getActiveInactiveCounts();
        $statusCounts = $this->dashboardModel->getAssetStatusCounts();
        $categoryCounts = $this->dashboardModel->getAssetCategoryCounts();
        $conditionCounts = $this->dashboardModel->getConditionCounts();
        $assetsByOffice = $this->dashboardModel->getAssetsByOffice();
        $recentAssets = $this->dashboardModel->getRecentAssets();
        $recentActivity = $this->dashboardModel->getRecentActivity(10);
        $alerts = $this->dashboardModel->getAlerts();

        // KPI summary
        $totalCategories = $this->dashboardModel->getTotalCategories();
        $totalOffices = $this->dashboardModel->getTotalOffices();
        $assetsUnderCustody = $this->dashboardModel->getAssetsUnderCustody();
        $missingAssets = $this->dashboardModel->getMissingAssets();
        $assetsForDisposal = $this->dashboardModel->getAssetsForDisposal();
        $recentTransfers = $this->dashboardModel->getRecentTransfersCount();

        // Prepare chart data
        $statusLabels = [];
        $statusData = [];
        foreach ($statusCounts as $row) {
            $statusLabels[] = ucfirst($row['status']);
            $statusData[] = (int)$row['count'];
        }

        $categoryLabels = [];
        $categoryData = [];
        foreach ($categoryCounts as $row) {
            if ($row['category'] === null) continue;
            $categoryLabels[] = $row['category'];
            $categoryData[] = (int)$row['count'];
        }

        $conditionLabels = [];
        $conditionData = [];
        foreach ($conditionCounts as $row) {
            $conditionLabels[] = ucfirst($row['condition']);
            $conditionData[] = (int)$row['count'];
        }

        $officeLabels = [];
        $officeData = [];
        foreach ($assetsByOffice as $row) {
            $officeLabels[] = $row['office'];
            $officeData[] = (int)$row['count'];
        }

        $pageTitle = 'Dashboard';
        $currentPage = 'dashboard';
        $viewFile = __DIR__ . '/../Views/dashboard.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }
}