<?php
namespace App\Models;

use App\Config\Database;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class LocationModel {
    /** @var \mysqli */
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all location records.
     * @return array
     */
    public function getAll() {
        $sql = "
            SELECT 
                al.id,
                al.asset_id,
                a.asset_code,
                a.description AS asset_description,
                al.location_name,
                al.site_type,
                al.description,
                al.recorded_at,
                u.username AS recorded_by
            FROM asset_locations al
            LEFT JOIN assets a ON al.asset_id = a.asset_id
            LEFT JOIN users u ON al.recorded_by = u.users_id
            ORDER BY al.recorded_at DESC
        ";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Insert a new location record.
     * @param array $data
     * @return bool
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO asset_locations (
                asset_id, location_name, site_type, description, recorded_by
            ) VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'isssi',
            $data['asset_id'],
            $data['location_name'],
            $data['site_type'],
            $data['description'],
            $data['recorded_by']
        );
        return $stmt->execute();
    }

    /**
     * Get assets for dropdown.
     * @return array
     */
    public function getAssets() {
        $result = $this->db->query("SELECT asset_id, asset_code, description FROM assets WHERE status != 'inactive' ORDER BY asset_code");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get users for dropdown.
     * @return array
     */
    public function getUsers() {
        $result = $this->db->query("SELECT users_id, username FROM users WHERE is_active = 1 ORDER BY username");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}