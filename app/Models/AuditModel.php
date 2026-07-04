<?php
namespace App\Models;

use App\Config\Database;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class AuditModel {
    /** @var \mysqli */
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get audit logs with optional filters.
     */
    public function getLogs($filters = []) {
        $sql = "
            SELECT 
                at.audit_trail_id,
                at.asset_id,
                a.asset_code,
                at.performed_by,
                u.username AS performed_by_username,
                at.action_type,
                at.module,
                at.previous_values,
                at.new_values,
                at.performed_at
            FROM audit_trail at
            LEFT JOIN assets a ON at.asset_id = a.asset_id
            LEFT JOIN users u ON at.performed_by = u.users_id
            WHERE 1=1
        ";
        $params = [];
        $types = '';

        if (!empty($filters['asset_id'])) {
            $sql .= " AND at.asset_id = ?";
            $params[] = $filters['asset_id'];
            $types .= 'i';
        }
        if (!empty($filters['user_id'])) {
            $sql .= " AND at.performed_by = ?";
            $params[] = $filters['user_id'];
            $types .= 'i';
        }
        if (!empty($filters['action_type'])) {
            $sql .= " AND at.action_type = ?";
            $params[] = $filters['action_type'];
            $types .= 's';
        }
        if (!empty($filters['module'])) {
            $sql .= " AND at.module = ?";
            $params[] = $filters['module'];
            $types .= 's';
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(at.performed_at) >= ?";
            $params[] = $filters['date_from'];
            $types .= 's';
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(at.performed_at) <= ?";
            $params[] = $filters['date_to'];
            $types .= 's';
        }

        $sql .= " ORDER BY at.performed_at DESC";

        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get distinct action types for filter dropdown.
     */
    public function getActionTypes() {
        $result = $this->db->query("SELECT DISTINCT action_type FROM audit_trail ORDER BY action_type");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get distinct modules for filter dropdown.
     */
    public function getModules() {
        $result = $this->db->query("SELECT DISTINCT module FROM audit_trail ORDER BY module");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get users for filter dropdown.
     */
    public function getUsers() {
        $result = $this->db->query("SELECT users_id, username FROM users WHERE is_active = 1 ORDER BY username");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get assets for filter dropdown.
     */
    public function getAssets() {
        $result = $this->db->query("SELECT asset_id, asset_code FROM assets WHERE status != 'inactive' ORDER BY asset_code");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}