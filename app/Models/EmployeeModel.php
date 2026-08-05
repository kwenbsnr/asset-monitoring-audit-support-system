<?php
namespace App\Models;

use App\Config\Database;
use App\Helpers\SalaryGradeHelper;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class EmployeeModel {
    /** @var \mysqli */
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all employee profiles, with optional filters.
     * @param array $filters ['status' => string, 'office_id' => int, 'search' => string]
     * @return array
     */
    public function getAll($filters = []) {
        $sql = "
            SELECT
                p.personnel_id,
                p.employee_id,
                p.full_name,
                p.position,
                p.designation,
                p.salary_grade,
                p.employment_status,
                p.is_active,
                p.office_id,
                o.name AS office_name,
                (SELECT COUNT(*) FROM asset_custodies ac
                    WHERE ac.custodian_id = p.personnel_id AND ac.status = 'active') AS active_asset_count
            FROM personnel p
            LEFT JOIN offices o ON p.office_id = o.office_id
            WHERE 1=1
        ";
        $params = [];
        $types = '';

        if (!empty($filters['status'])) {
            $sql .= " AND p.employment_status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }
        if (!empty($filters['office_id'])) {
            $sql .= " AND p.office_id = ?";
            $params[] = $filters['office_id'];
            $types .= 'i';
        }
        if (!empty($filters['search'])) {
            $like = '%' . $filters['search'] . '%';
            $sql .= " AND (p.full_name LIKE ? OR p.employee_id LIKE ? OR p.position LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $types .= 'sss';
        }

        $sql .= " ORDER BY p.full_name";

        if ($params) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($sql);
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get a single employee profile by ID.
     * @param int $id
     * @return array|null
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT p.*, o.name AS office_name
            FROM personnel p
            LEFT JOIN offices o ON p.office_id = o.office_id
            WHERE p.personnel_id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Create a new employee profile.
     * @param array $data
     * @return int|false New personnel_id, or false on failure.
     */
    public function create($data) {
        $isActive = ($data['employment_status'] === 'active') ? 1 : 0;
        $stmt = $this->db->prepare("
            INSERT INTO personnel (
                employee_id, full_name, position, designation, office_id,
                salary_grade, employment_status, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'ssssiisi',
            $data['employee_id'],
            $data['full_name'],
            $data['position'],
            $data['designation'],
            $data['office_id'],
            $data['salary_grade'],
            $data['employment_status'],
            $isActive
        );
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    /**
     * Update an existing employee profile (full profile edit, including status).
     * @param int   $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $isActive = ($data['employment_status'] === 'active') ? 1 : 0;
        $stmt = $this->db->prepare("
            UPDATE personnel SET
                employee_id = ?,
                full_name = ?,
                position = ?,
                designation = ?,
                office_id = ?,
                salary_grade = ?,
                employment_status = ?,
                is_active = ?
            WHERE personnel_id = ?
        ");
        $stmt->bind_param(
            'ssssiisii',
            $data['employee_id'],
            $data['full_name'],
            $data['position'],
            $data['designation'],
            $data['office_id'],
            $data['salary_grade'],
            $data['employment_status'],
            $isActive,
            $id
        );
        return $stmt->execute();
    }

    /**
     * Change ONLY the employment status (Retire / Transfer / Inactive / Reactivate)
     * without touching the rest of the profile. Keeps is_active in sync so every
     * existing "WHERE is_active = 1" custodian dropdown elsewhere in the app
     * automatically stops offering non-active employees — no other query needs
     * to change.
     * @param int    $id
     * @param string $status one of active|retired|transferred|inactive
     * @return bool
     */
    public function updateStatus($id, $status) {
        $isActive = ($status === 'active') ? 1 : 0;
        $stmt = $this->db->prepare("
            UPDATE personnel SET employment_status = ?, is_active = ?
            WHERE personnel_id = ?
        ");
        $stmt->bind_param('sii', $status, $isActive, $id);
        return $stmt->execute();
    }

    /**
     * Offices for dropdown.
     * @return array
     */
    public function getOffices() {
        $result = $this->db->query("SELECT office_id, name FROM offices ORDER BY name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * How many assets this employee currently has active custody of
     * (shown in the employee list; also used to warn admins before
     * they set someone Retired/Transferred/Inactive while assets are
     * still assigned to them).
     * @param int $id
     * @return int
     */
    public function getActiveAssetCount($id) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM asset_custodies WHERE custodian_id = ? AND status = 'active'
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        return (int)$count;
    }

    /**
     * The single shared validation rule: can this employee (by Salary Grade
     * and active status) be assigned an asset of this acquisition cost?
     * Checked per asset, not cumulative — matches the client's business rule.
     *
     * Every code path that creates or reassigns custody (AssetController::save(),
     * AssetController::saveInspection(), CustodyController::save()) calls this
     * exact method, so the rule only has to be correct in one place.
     *
     * @param int   $personnelId
     * @param float $assetCost
     * @return true|string  true if the assignment is allowed, otherwise a
     *                      user-facing error message.
     */
    public function validateAssetAssignment($personnelId, $assetCost) {
        $employee = $this->getById($personnelId);
        if (!$employee) {
            return 'Selected employee/custodian was not found.';
        }
        if ($employee['employment_status'] !== 'active') {
            return $employee['full_name'] . ' is currently marked "' . ucfirst($employee['employment_status']) .
                   '" and cannot be assigned assets.';
        }

        $sg = (int)$employee['salary_grade'];
        $cost = (float)$assetCost;

        if (!SalaryGradeHelper::canAssign($sg, $cost)) {
            $threshold = SalaryGradeHelper::getThreshold($sg);
            $thresholdText = ($threshold >= PHP_INT_MAX)
                ? 'no limit'
                : '₱' . number_format($threshold, 2);
            return sprintf(
                '%s (SG %d) cannot be assigned this asset: its value of ₱%s exceeds the SG %d threshold of %s.',
                $employee['full_name'],
                $sg,
                number_format($cost, 2),
                $sg,
                $thresholdText
            );
        }

        return true;
    }
}
