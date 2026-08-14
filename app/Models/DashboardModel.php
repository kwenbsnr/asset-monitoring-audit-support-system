<?php
namespace App\Models;

use App\Config\Database;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class DashboardModel {
    /** @var \mysqli */
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get total number of assets (excluding inactive).
     */
    public function getTotalAssets() {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM assets WHERE status != 'inactive'");
        return $result->fetch_assoc()['total'] ?? 0;
    }

    /**
     * Get counts by asset status.
     */
    public function getAssetStatusCounts() {
        $result = $this->db->query("SELECT status, COUNT(*) AS count FROM assets GROUP BY status");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get counts by asset account (replaces category counts).
     */
    public function getAssetAccountCounts() {
        $sql = "
            SELECT aa.account_name AS account, COUNT(a.asset_id) AS count
            FROM asset_accounts aa
            LEFT JOIN assets a ON aa.asset_accounts_id = a.asset_accounts_id
            WHERE a.status != 'inactive' OR a.status IS NULL
            GROUP BY aa.asset_accounts_id
            ORDER BY count DESC
        ";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get recent custody assignments (last 5).
     */
    public function getRecentCustody() {
        $sql = "
            SELECT 
                a.asset_code,
                p.full_name AS custodian,
                ac.effectivity_date,
                o.name AS office
            FROM asset_custodies ac
            LEFT JOIN assets a ON ac.asset_id = a.asset_id
            LEFT JOIN personnel p ON ac.custodian_id = p.personnel_id
            LEFT JOIN offices o ON ac.office_id = o.office_id
            WHERE ac.status = 'active'
            ORDER BY ac.effectivity_date DESC
            LIMIT 5
        ";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get recent audit logs.
     * @param int $limit
     */
    public function getRecentAudit($limit = 5) {
        $sql = "
            SELECT 
                at.action_type,
                at.module,
                at.performed_at,
                u.username AS performed_by,
                a.asset_code
            FROM audit_trail at
            LEFT JOIN users u ON at.performed_by = u.users_id
            LEFT JOIN assets a ON at.asset_id = a.asset_id
            ORDER BY at.performed_at DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get count of active vs inactive assets.
     */
    public function getActiveInactiveCounts() {
        $result = $this->db->query("
            SELECT 
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status != 'active' AND status != 'inactive' THEN 1 ELSE 0 END) AS other,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) AS inactive
            FROM assets
        ");
        return $result->fetch_assoc();
    }

    /**
     * Get total number of asset accounts.
     */
    public function getTotalAccounts() {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM asset_accounts");
        return $result->fetch_assoc()['total'] ?? 0;
    }

    /**
     * Get total offices.
     */
    public function getTotalOffices() {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM offices");
        return $result->fetch_assoc()['total'] ?? 0;
    }

    /**
     * Get count of assets under custody (active custody assignments).
     */
    public function getAssetsUnderCustody() {
        $result = $this->db->query("SELECT COUNT(DISTINCT asset_id) AS total FROM asset_custodies WHERE status = 'active'");
        return $result->fetch_assoc()['total'] ?? 0;
    }

    /**
     * Get count of missing assets.
     */
    public function getMissingAssets() {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM assets WHERE status = 'missing'");
        return $result->fetch_assoc()['total'] ?? 0;
    }

    /**
     * Get count of assets for disposal.
     */
    public function getAssetsForDisposal() {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM assets WHERE status = 'disposed'");
        return $result->fetch_assoc()['total'] ?? 0;
    }

    /**
     * Get transfers made this month.
     */
    public function getRecentTransfersCount() {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM asset_transfers WHERE MONTH(transfer_date) = MONTH(CURDATE()) AND YEAR(transfer_date) = YEAR(CURDATE())");
        return $result->fetch_assoc()['total'] ?? 0;
    }

    /**
     * Get user counts grouped by role, with active/inactive breakdown.
     */
    public function getUserCounts() {
        $sql = "SELECT role, COUNT(*) AS total, SUM(is_active) AS active FROM users GROUP BY role";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get count of assets with damaged condition.
     */
    public function getDamagedAssetsCount() {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM assets WHERE `condition` = 'damaged'");
        return $result->fetch_assoc()['total'] ?? 0;
    }

    /**
     * Get report counts grouped by status (draft/submitted).
     */
    public function getReportStatusCounts() {
        $sql = "SELECT status, COUNT(*) AS count FROM asset_reports GROUP BY status";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get asset condition distribution.
     */
    public function getConditionCounts() {
        $result = $this->db->query("SELECT `condition`, COUNT(*) AS count FROM assets GROUP BY `condition`");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get asset counts by office.
     */
    public function getAssetsByOffice() {
        $sql = "
            SELECT o.name AS office, COUNT(a.asset_id) AS count
            FROM offices o
            LEFT JOIN asset_custodies ac ON o.office_id = ac.office_id AND ac.status = 'active'
            LEFT JOIN assets a ON ac.asset_id = a.asset_id AND a.status != 'inactive'
            GROUP BY o.office_id
            ORDER BY count DESC
            LIMIT 5
        ";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get recent assets added – uses asset_name.
     * @param int $limit
     */
    public function getRecentAssets($limit = 10) {
        $sql = "
            SELECT 
                a.asset_id,
                a.asset_code,
                a.asset_name,
                a.status,
                a.condition,
                a.created_at,
                aa.account_name AS account_name,
                p.full_name AS custodian,
                o.name AS office_name
            FROM assets a
            LEFT JOIN asset_accounts aa ON a.asset_accounts_id = aa.asset_accounts_id
            LEFT JOIN asset_custodies acust ON a.asset_id = acust.asset_id AND acust.status = 'active'
            LEFT JOIN personnel p ON acust.custodian_id = p.personnel_id
            LEFT JOIN offices o ON acust.office_id = o.office_id
            WHERE a.status != 'inactive'
            ORDER BY a.created_at DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get recent activities from various tables.
     * @param int $limit
     * @return array
     */
    public function getRecentActivity($limit = 10) {
        $sql = "
            (SELECT 
                'asset_registered' AS type,
                CONCAT('Asset ', a.asset_code, ' registered') AS description,
                'System' AS performed_by,
                a.created_at AS created_at
            FROM assets a
            WHERE a.created_at IS NOT NULL
            ORDER BY a.created_at DESC
            LIMIT 3)
            UNION
            (SELECT 
                'custody_assigned' AS type,
                CONCAT('Asset ', a.asset_code, ' assigned to ', p.full_name) AS description,
                'System' AS performed_by,
                ac.created_at AS created_at
            FROM asset_custodies ac
            LEFT JOIN assets a ON ac.asset_id = a.asset_id
            LEFT JOIN personnel p ON ac.custodian_id = p.personnel_id
            ORDER BY ac.created_at DESC
            LIMIT 3)
            UNION
            (SELECT 
                'asset_transferred' AS type,
                CONCAT('Asset ', a.asset_code, ' transferred to ', p_to.full_name) AS description,
                COALESCE(u.username, 'System') AS performed_by,
                atr.transfer_date AS created_at
            FROM asset_transfers atr
            LEFT JOIN assets a ON atr.asset_id = a.asset_id
            LEFT JOIN personnel p_to ON atr.to_custodian_id = p_to.personnel_id
            LEFT JOIN users u ON atr.approved_by = u.users_id
            WHERE atr.transfer_date IS NOT NULL
            ORDER BY atr.transfer_date DESC
            LIMIT 2)
            UNION
            (SELECT 
                'report_generated' AS type,
                CONCAT('Report ', ar.report_number, ' generated') AS description,
                COALESCE(u.username, 'System') AS performed_by,
                ar.created_at AS created_at
            FROM asset_reports ar
            LEFT JOIN users u ON ar.prepared_by = u.users_id
            ORDER BY ar.created_at DESC
            LIMIT 2)
            ORDER BY created_at DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get alerts: missing assets, damaged assets, pending reports, pending transfers.
     */
    public function getAlerts() {
        $alerts = [];

        $missing = $this->db->query("SELECT COUNT(*) AS count FROM assets WHERE status = 'missing'")->fetch_assoc();
        if ($missing['count'] > 0) $alerts[] = ['type' => 'missing', 'label' => 'Missing Assets', 'count' => $missing['count'], 'icon' => 'bi bi-exclamation-triangle', 'color' => 'danger'];

        $damaged = $this->db->query("SELECT COUNT(*) AS count FROM assets WHERE `condition` = 'damaged'")->fetch_assoc();
        if ($damaged['count'] > 0) $alerts[] = ['type' => 'damaged', 'label' => 'Assets with Damaged Condition', 'count' => $damaged['count'], 'icon' => 'bi bi-tools', 'color' => 'warning'];

        $pendingReports = $this->db->query("SELECT COUNT(*) AS count FROM asset_reports WHERE status = 'draft'")->fetch_assoc();
        if ($pendingReports['count'] > 0) $alerts[] = ['type' => 'pending_reports', 'label' => 'Pending Reports', 'count' => $pendingReports['count'], 'icon' => 'bi bi-file-earmark-text', 'color' => 'info'];

        $pendingTransfers = $this->db->query("SELECT COUNT(*) AS count FROM asset_transfers WHERE status = 'pending'")->fetch_assoc();
        if ($pendingTransfers['count'] > 0) $alerts[] = ['type' => 'pending_transfers', 'label' => 'Pending Transfers', 'count' => $pendingTransfers['count'], 'icon' => 'bi bi-arrow-left-right', 'color' => 'warning'];

        return $alerts;
    }
}