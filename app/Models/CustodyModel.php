<?php
namespace App\Models;

use App\Config\Database;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class CustodyModel {
    /** @var \mysqli */
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all custody records.
     * @return array
     */
    public function getAll() {
        $sql = "
            SELECT 
                ac.asset_custodies_id,
                ac.asset_id,
                a.asset_code,
                a.description AS asset_description,
                ac.custodian_id,
                p.full_name AS custodian_name,
                ac.office_id,
                o.name AS office_name,
                ac.effectivity_date,
                ac.end_date,
                ac.status,
                ac.property_number
            FROM asset_custodies ac
            LEFT JOIN assets a ON ac.asset_id = a.asset_id
            LEFT JOIN personnel p ON ac.custodian_id = p.personnel_id
            LEFT JOIN offices o ON ac.office_id = o.office_id
            ORDER BY ac.effectivity_date DESC
        ";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get a single custody record by ID.
     * @param int $id
     * @return array|null
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM asset_custodies WHERE asset_custodies_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Get active custody for an asset.
     * @param int $assetId
     * @return array|null
     */
    public function getActiveCustody($assetId) {
        $stmt = $this->db->prepare("
            SELECT * FROM asset_custodies 
            WHERE asset_id = ? AND status = 'active' 
            ORDER BY effectivity_date DESC LIMIT 1
        ");
        $stmt->bind_param('i', $assetId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Get a single asset's id/code/cost — used by the Salary Grade
     * assignment-value validation in CustodyController::save().
     * @param int $assetId
     * @return array|null
     */
    public function getAssetById($assetId) {
        $stmt = $this->db->prepare("SELECT asset_id, asset_code, acquisition_cost FROM assets WHERE asset_id = ?");
        $stmt->bind_param('i', $assetId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Insert a new custody record.
     * @param array $data
     * @return bool
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO asset_custodies (
                asset_id, custodian_id, office_id, property_number,
                effectivity_date, status
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'iiisss',
            $data['asset_id'],
            $data['custodian_id'],
            $data['office_id'],
            $data['property_number'],
            $data['effectivity_date'],
            $data['status']
        );
        return $stmt->execute();
    }

    /**
     * Update a custody record.
     * @param int   $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE asset_custodies SET
                custodian_id = ?,
                office_id = ?,
                property_number = ?,
                effectivity_date = ?,
                end_date = ?,
                status = ?
            WHERE asset_custodies_id = ?
        ");
        $stmt->bind_param(
            'iissssi',
            $data['custodian_id'],
            $data['office_id'],
            $data['property_number'],
            $data['effectivity_date'],
            $data['end_date'],
            $data['status'],
            $id
        );
        return $stmt->execute();
    }

    /**
     * Get personnel for dropdown.
     * @return array
     */
    public function getPersonnel() {
        $result = $this->db->query("SELECT personnel_id, full_name, position, office_id, salary_grade FROM personnel WHERE is_active = 1 ORDER BY full_name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get offices for dropdown.
     * @return array
     */
    public function getOffices() {
        $result = $this->db->query("SELECT office_id, name FROM offices ORDER BY name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get assets for dropdown.
     * @return array
     */
    public function getAssets() {
        $result = $this->db->query("SELECT asset_id, asset_code, description, acquisition_cost FROM assets WHERE status != 'inactive' ORDER BY asset_code");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Search custody records by custodian name, asset code, description, office, or property number.
     * @param string $searchTerm
     * @return array
     */
    public function search($searchTerm) {
        $like = '%' . $searchTerm . '%';
        $sql = "
            SELECT 
                ac.asset_custodies_id,
                ac.asset_id,
                a.asset_code,
                a.description AS asset_description,
                ac.custodian_id,
                p.full_name AS custodian_name,
                ac.office_id,
                o.name AS office_name,
                ac.effectivity_date,
                ac.end_date,
                ac.status,
                ac.property_number
            FROM asset_custodies ac
            LEFT JOIN assets a ON ac.asset_id = a.asset_id
            LEFT JOIN personnel p ON ac.custodian_id = p.personnel_id
            LEFT JOIN offices o ON ac.office_id = o.office_id
            WHERE 
                p.full_name LIKE ? OR
                a.asset_code LIKE ? OR
                a.description LIKE ? OR
                o.name LIKE ? OR
                ac.property_number LIKE ?
            ORDER BY ac.effectivity_date DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('sssss', $like, $like, $like, $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    
    /**
     * Get offices with active custody assignments.
     * @return array
     */
    public function getOfficesWithCustody() {
        $sql = "
            SELECT DISTINCT o.office_id, o.name, o.location,
                (SELECT COUNT(DISTINCT ac.custodian_id) FROM asset_custodies ac WHERE ac.office_id = o.office_id AND ac.status = 'active') AS custodian_count,
                (SELECT COUNT(DISTINCT ac.asset_id) FROM asset_custodies ac WHERE ac.office_id = o.office_id AND ac.status = 'active') AS asset_count
            FROM offices o
            INNER JOIN asset_custodies ac ON o.office_id = ac.office_id
            WHERE ac.status = 'active'
            ORDER BY o.name
        ";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get custodians (personnel) for a specific office.
     * @param int $officeId
     * @return array
     */
    public function getCustodiansByOffice($officeId) {
        $sql = "
            SELECT DISTINCT p.personnel_id, p.full_name, p.position,
                (SELECT COUNT(DISTINCT ac.asset_id) FROM asset_custodies ac WHERE ac.custodian_id = p.personnel_id AND ac.status = 'active') AS asset_count
            FROM personnel p
            INNER JOIN asset_custodies ac ON p.personnel_id = ac.custodian_id
            WHERE ac.office_id = ? AND ac.status = 'active'
            ORDER BY p.full_name
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $officeId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get assets under a custodian with pagination.
     * @param int $custodianId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAssetsByCustodian($custodianId, $limit, $offset) {
        $sql = "
            SELECT 
                a.asset_id, a.asset_code, a.description, a.brand, a.model,
                a.serial_number, a.status, a.condition,
                ac.effectivity_date, ac.property_number
            FROM assets a
            INNER JOIN asset_custodies ac ON a.asset_id = ac.asset_id
            WHERE ac.custodian_id = ? AND ac.status = 'active'
            ORDER BY a.asset_code
            LIMIT ? OFFSET ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iii', $custodianId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Count assets under a custodian.
     * @param int $custodianId
     * @return int
     */
    public function countAssetsByCustodian($custodianId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM asset_custodies ac
            WHERE ac.custodian_id = ? AND ac.status = 'active'
        ");
        $stmt->bind_param('i', $custodianId);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        return $count;
    }

    /**
     * Search custodians by name across all offices.
     * @param string $searchTerm
     * @return array
     */
    public function searchCustodians($searchTerm) {
        $like = '%' . $searchTerm . '%';
        $sql = "
            SELECT DISTINCT p.personnel_id, p.full_name, p.position,
                (SELECT COUNT(DISTINCT ac.asset_id) FROM asset_custodies ac WHERE ac.custodian_id = p.personnel_id AND ac.status = 'active') AS asset_count,
                (SELECT o.name FROM offices o INNER JOIN asset_custodies ac2 ON o.office_id = ac2.office_id WHERE ac2.custodian_id = p.personnel_id AND ac2.status = 'active' LIMIT 1) AS office_name
            FROM personnel p
            INNER JOIN asset_custodies ac ON p.personnel_id = ac.custodian_id
            WHERE ac.status = 'active' AND (p.full_name LIKE ? OR p.position LIKE ?)
            ORDER BY p.full_name
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ss', $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get office by ID.
     * @param int $id
     * @return array|null
     */
    public function getOfficeById($id) {
        $stmt = $this->db->prepare("SELECT * FROM offices WHERE office_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Get personnel by ID.
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