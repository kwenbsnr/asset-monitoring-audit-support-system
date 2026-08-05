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

    // ===== Existing methods =====

    /**
     * Find a user by username.
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
     * Update last login timestamp.
     * @param int $userId
     * @return void
     */
    public function updateLastLogin($userId) {
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE users_id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
    }

    // ===== NEW User Management Methods =====

    /**
     * Get all users with personnel and office details.
     * @return array
     */
    public function getAllUsers() {
        $sql = "
            SELECT 
                u.users_id,
                u.username,
                u.role,
                u.is_active,
                u.last_login,
                u.created_at,
                p.personnel_id,
                p.full_name,
                p.position,
                p.designation,
                o.name AS office_name
            FROM users u
            LEFT JOIN personnel p ON u.personnel_id = p.personnel_id
            LEFT JOIN offices o ON p.office_id = o.office_id
            ORDER BY u.users_id
        ";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get a single user by ID.
     * @param int $id
     * @return array|null
     */
    public function getUserById($id) {
        $stmt = $this->db->prepare("
            SELECT 
                u.*,
                p.full_name,
                p.position,
                p.designation,
                p.office_id,
                o.name AS office_name
            FROM users u
            LEFT JOIN personnel p ON u.personnel_id = p.personnel_id
            LEFT JOIN offices o ON p.office_id = o.office_id
            WHERE u.users_id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Create a new user (and optionally personnel).
     * Personnel created inline here (via "Create new personnel" on the user
     * form) now also requires salary_grade, same as Employee Management —
     * both paths write to the same personnel table, so neither can bypass it.
     * @param array $data
     * @return int|false
     */
    public function createUser($data) {
        $personnelId = $data['personnel_id'] ?? 0;
        if (!$personnelId && !empty($data['full_name'])) {
            $stmt = $this->db->prepare("
                INSERT INTO personnel (full_name, position, designation, office_id, salary_grade, employment_status, is_active)
                VALUES (?, ?, ?, ?, ?, 'active', 1)
            ");
            $salaryGrade = (int)($data['salary_grade'] ?? 1);
            $stmt->bind_param('sssii', $data['full_name'], $data['position'], $data['designation'], $data['office_id'], $salaryGrade);
            if (!$stmt->execute()) {
                return false;
            }
            $personnelId = $this->db->insert_id;
        }

        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("
            INSERT INTO users (personnel_id, username, password_hash, role, is_active)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('isssi', $personnelId, $data['username'], $passwordHash, $data['role'], $data['is_active']);
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    /**
     * Update an existing user.
     * @param int   $id
     * @param array $data
     * @return bool
     */
    public function updateUser($id, $data) {
        $personnelId = $data['personnel_id'] ?? 0;
        if ($personnelId) {
            if (isset($data['salary_grade'])) {
                $stmt = $this->db->prepare("
                    UPDATE personnel SET full_name = ?, position = ?, designation = ?, office_id = ?, salary_grade = ?
                    WHERE personnel_id = ?
                ");
                $salaryGrade = (int)$data['salary_grade'];
                $stmt->bind_param('sssiii', $data['full_name'], $data['position'], $data['designation'], $data['office_id'], $salaryGrade, $personnelId);
            } else {
                $stmt = $this->db->prepare("
                    UPDATE personnel SET full_name = ?, position = ?, designation = ?, office_id = ?
                    WHERE personnel_id = ?
                ");
                $stmt->bind_param('sssii', $data['full_name'], $data['position'], $data['designation'], $data['office_id'], $personnelId);
            }
            $stmt->execute();
        }

        $sql = "UPDATE users SET username = ?, role = ?, is_active = ?";
        $params = ['ssi', $data['username'], $data['role'], $data['is_active']];
        if (!empty($data['password'])) {
            $sql .= ", password_hash = ?";
            $params[0] .= 's';
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $sql .= " WHERE users_id = ?";
        $params[0] .= 'i';
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(...$params);
        return $stmt->execute();
    }

    /**
     * Soft delete a user (set is_active = 0).
     * @param int $id
     * @return bool
     */
    public function deleteUser($id) {
        $stmt = $this->db->prepare("UPDATE users SET is_active = 0 WHERE users_id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    /**
     * Get all active personnel for dropdown. Employment status is kept in
     * sync with is_active by EmployeeModel, so this automatically excludes
     * retired/transferred/inactive employees without any change here.
     * @return array
     */
    public function getPersonnelList() {
        $result = $this->db->query("SELECT personnel_id, full_name, position, salary_grade FROM personnel WHERE is_active = 1 ORDER BY full_name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all offices for dropdown.
     * @return array
     */
    public function getOfficeList() {
        $result = $this->db->query("SELECT office_id, name FROM offices ORDER BY name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get a single personnel record by ID.
     * @param int $id
     * @return array|null
     */
    public function getPersonnelById($id) {
        $stmt = $this->db->prepare("SELECT * FROM personnel WHERE personnel_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
