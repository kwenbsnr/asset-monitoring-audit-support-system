<?php
namespace App\Models;

use App\Config\Database;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class UserModel {
    /** @var \mysqli */
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * @param string $username
     * @return array|null
     */
    public function findByUsername($username) {
        $stmt = $this->db->prepare("
            SELECT 
                u.users_id,
                u.personnel_id,
                u.username,
                u.password_hash,
                u.role,
                u.is_active,
                p.full_name,
                p.position,
                p.designation,
                o.name AS office_name
            FROM users u
            LEFT JOIN personnel p ON u.personnel_id = p.personnel_id
            LEFT JOIN offices o ON p.office_id = o.office_id
            WHERE u.username = ? AND u.is_active = 1
            LIMIT 1
        ");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * @param int $userId
     */
    public function updateLastLogin($userId) {
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE users_id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
    }
}