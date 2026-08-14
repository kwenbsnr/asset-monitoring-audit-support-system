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
        switch ($_SESSION['role']) {
            case 'admin':
                $this->adminDashboard();
                break;
            case 'inspection_officer':
                $this->inspectionOfficerDashboard();
                break;
            case 'asset_manager':
            default:
                $this->assetManagerDashboard();
                break;
        }
    }

    /**
     * Admin: system oversight — users, audit trail, flagged assets, reports.
     * No asset-registration or field-verification data here; that's not their job.
     */
    private function adminDashboard() {
        $totalAssets = $this->dashboardModel->getTotalAssets();
        $totalOffices = $this->dashboardModel->getTotalOffices();
        $totalAccounts = $this->dashboardModel->getTotalAccounts();

        $userCounts = $this->dashboardModel->getUserCounts();
        $totalUsers = 0;
        $activeUsers = 0;
        foreach ($userCounts as $row) {
            $totalUsers += (int)$row['total'];
            $activeUsers += (int)$row['active'];
        }

        $missingAssets = $this->dashboardModel->getMissingAssets();
        $damagedAssets = $this->dashboardModel->getDamagedAssetsCount();
        $disposedAssets = $this->dashboardModel->getAssetsForDisposal();

        $reportStatusCounts = $this->dashboardModel->getReportStatusCounts();
        $draftReports = 0;
        $submittedReports = 0;
        foreach ($reportStatusCounts as $row) {
            if ($row['status'] === 'draft') $draftReports = (int)$row['count'];
            if ($row['status'] === 'submitted') $submittedReports = (int)$row['count'];
        }

        $recentAudit = $this->dashboardModel->getRecentAudit(12);

        $pageTitle = 'Dashboard';
        $currentPage = 'dashboard';
        $viewFile = __DIR__ . '/../Views/dashboard/admin.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    /**
     * Asset Manager: registration & inventory operations.
     * No user management, audit trail, or reports data here.
     */
    private function assetManagerDashboard() {
        $totalAssets = $this->dashboardModel->getTotalAssets();
        $activeInactive = $this->dashboardModel->getActiveInactiveCounts();
        $activeAssets = $activeInactive['active'] ?? 0;

        $conditionCounts = $this->dashboardModel->getConditionCounts();
        $conditionLabels = [];
        $conditionData = [];
        foreach ($conditionCounts as $row) {
            $conditionLabels[] = ucfirst($row['condition']);
            $conditionData[] = (int)$row['count'];
        }

        $recentAssets = $this->dashboardModel->getRecentAssets(10);

        $pageTitle = 'Dashboard';
        $currentPage = 'dashboard';
        $viewFile = __DIR__ . '/../Views/dashboard/asset_manager.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    /**
     * Inspection Officer: verification workflow.
     * Kept deliberately light — no charts, no financials, no user/report data.
     */
    private function inspectionOfficerDashboard() {
        $conditionCounts = $this->dashboardModel->getConditionCounts();
        $flaggedConditions = ['poor' => 0, 'damaged' => 0, 'obsolete' => 0];
        foreach ($conditionCounts as $row) {
            if (isset($flaggedConditions[$row['condition']])) {
                $flaggedConditions[$row['condition']] = (int)$row['count'];
            }
        }

        $recentAssets = $this->dashboardModel->getRecentAssets(5);

        $pageTitle = 'Dashboard';
        $currentPage = 'dashboard';
        $viewFile = __DIR__ . '/../Views/dashboard/inspection_officer.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }
}