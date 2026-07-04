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
     * Get all custody records (including asset, personnel, office details).
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
                ac.accountability_document,
                ac.accountability_reference
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
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM asset_custodies WHERE asset_custodies_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Get the active custody record for a given asset.
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
     * Insert a new custody record.
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO asset_custodies (
                asset_id, custodian_id, office_id, accountability_document,
                accountability_reference, effectivity_date, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'iiissss',
            $data['asset_id'],
            $data['custodian_id'],
            $data['office_id'],
            $data['accountability_document'],
            $data['accountability_reference'],
            $data['effectivity_date'],
            $data['status']
        );
        return $stmt->execute();
    }

    /**
     * Update an existing custody record (e.g., end_date, status).
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE asset_custodies SET
                custodian_id = ?,
                office_id = ?,
                accountability_document = ?,
                accountability_reference = ?,
                effectivity_date = ?,
                end_date = ?,
                status = ?
            WHERE asset_custodies_id = ?
        ");
        $stmt->bind_param(
            'iisssssi',
            $data['custodian_id'],
            $data['office_id'],
            $data['accountability_document'],
            $data['accountability_reference'],
            $data['effectivity_date'],
            $data['end_date'],
            $data['status'],
            $id
        );
        return $stmt->execute();
    }

    /**
     * Get all personnel for dropdown.
     */
    public function getPersonnel() {
        $result = $this->db->query("SELECT personnel_id, full_name, position FROM personnel WHERE is_active = 1 ORDER BY full_name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all offices for dropdown.
     */
    public function getOffices() {
        $result = $this->db->query("SELECT office_id, name FROM offices ORDER BY name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all assets for dropdown.
     */
    public function getAssets() {
        $result = $this->db->query("SELECT asset_id, asset_code, description FROM assets WHERE status != 'inactive' ORDER BY asset_code");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}