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
     * Get counts by asset category.
     */
    public function getAssetCategoryCounts() {
        $sql = "
            SELECT ac.name AS category, COUNT(a.asset_id) AS count
            FROM asset_categories ac
            LEFT JOIN asset_accounts aa ON ac.asset_category_id = aa.asset_category_id
            LEFT JOIN assets a ON aa.asset_accounts_id = a.asset_accounts_id
            WHERE a.status != 'inactive' OR a.status IS NULL
            GROUP BY ac.asset_category_id
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
     * Get recent audit logs (last 5).
     */
    public function getRecentAudit() {
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
            LIMIT 5
        ";
        $result = $this->db->query($sql);
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

    
}