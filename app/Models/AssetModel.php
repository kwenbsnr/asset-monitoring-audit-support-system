<?php
namespace App\Models;

use App\Config\Database;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class AssetModel {
    /** @var \mysqli */
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Fetch all assets (excluding soft‑deleted ones) with account & category info
     */
    public function getAll() {
        $sql = "
            SELECT 
                a.asset_id,
                a.asset_code,
                a.qr_code_ref,
                a.description,
                a.brand,
                a.model,
                a.serial_number,
                a.acquisition_cost,
                a.acquisition_date,
                a.status,
                a.condition,
                a.remarks,
                aa.account_code,
                aa.account_name,
                ac.name AS category_name
            FROM assets a
            LEFT JOIN asset_accounts aa ON a.asset_accounts_id = aa.asset_accounts_id
            LEFT JOIN asset_categories ac ON aa.asset_category_id = ac.asset_category_id
            WHERE a.status != 'inactive'
            ORDER BY a.asset_code
        ";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get a single asset by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT 
                a.*,
                aa.asset_accounts_id,
                aa.account_code,
                aa.account_name,
                ac.asset_category_id,
                ac.name AS category_name
            FROM assets a
            LEFT JOIN asset_accounts aa ON a.asset_accounts_id = aa.asset_accounts_id
            LEFT JOIN asset_categories ac ON aa.asset_category_id = ac.asset_category_id
            WHERE a.asset_id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Insert a new asset
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO assets (
                asset_code, qr_code_ref, description, brand, model, serial_number,
                acquisition_cost, acquisition_date, asset_accounts_id, status, `condition`, remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        // Generate a placeholder QR code reference (you can replace with real QR later)
        $qr_code_ref = 'QR-' . strtoupper(uniqid());
        $stmt->bind_param(
            'ssssssdsiss',
            $data['asset_code'],
            $qr_code_ref,
            $data['description'],
            $data['brand'],
            $data['model'],
            $data['serial_number'],
            $data['acquisition_cost'],
            $data['acquisition_date'],
            $data['asset_accounts_id'],
            $data['status'],
            $data['condition'],
            $data['remarks']
        );
        return $stmt->execute();
    }

    /**
     * Update an existing asset
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE assets SET
                asset_code = ?,
                description = ?,
                brand = ?,
                model = ?,
                serial_number = ?,
                acquisition_cost = ?,
                acquisition_date = ?,
                asset_accounts_id = ?,
                status = ?,
                `condition` = ?,
                remarks = ?
            WHERE asset_id = ?
        ");
        $stmt->bind_param(
            'sssssdsissi',
            $data['asset_code'],
            $data['description'],
            $data['brand'],
            $data['model'],
            $data['serial_number'],
            $data['acquisition_cost'],
            $data['acquisition_date'],
            $data['asset_accounts_id'],
            $data['status'],
            $data['condition'],
            $data['remarks'],
            $id
        );
        return $stmt->execute();
    }

    /**
     * Soft delete (set status = 'inactive')
     */
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE assets SET status = 'inactive' WHERE asset_id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    /**
     * Get all asset accounts for dropdown
     */
    public function getAssetAccounts() {
        $result = $this->db->query("
            SELECT aa.asset_accounts_id, aa.account_code, aa.account_name, ac.name AS category_name
            FROM asset_accounts aa
            LEFT JOIN asset_categories ac ON aa.asset_category_id = ac.asset_category_id
            ORDER BY aa.account_code
        ");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}