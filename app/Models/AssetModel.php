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

    // ========== Existing methods ==========

    /**
     * Fetch all active assets with account and category details.
     * @return array
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
     * Get a single asset by ID.
     * @param int $id
     * @return array|null
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
     * Insert a new asset.
     * @param array $data
     * @return bool
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO assets (
                asset_code, qr_code_ref, description, brand, model, serial_number,
                acquisition_cost, acquisition_date, asset_accounts_id, status, `condition`, remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $qr_code_ref = 'QR-' . strtoupper(uniqid());
        $stmt->bind_param(
            'ssssssdsisss',
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
     * Update an existing asset.
     * @param int   $id
     * @param array $data
     * @return bool
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
            'sssssdsisssi',
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
     * Soft delete (set status = 'inactive').
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE assets SET status = 'inactive' WHERE asset_id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    /**
     * Get all asset accounts with category names for dropdown.
     * @return array
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

    // ========== NEW methods for hierarchical browsing ==========

    /**
     * Recursively get the category tree starting from a parent ID.
     * Returns an array of categories, each with a 'children' key.
     *
     * @param int|null $parentId  Null for root categories, else a category ID
     * @return array
     */
    public function getCategoryTree($parentId = null) {
        $sql = "SELECT asset_category_id, name, code, description, parent_category_id 
                FROM asset_categories 
                WHERE parent_category_id " . ($parentId === null ? "IS NULL" : "= ?") . "
                ORDER BY name";
        $stmt = $this->db->prepare($sql);
        if ($parentId !== null) {
            $stmt->bind_param('i', $parentId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = $result->fetch_all(MYSQLI_ASSOC);
        // Recursively fetch children for each category
        foreach ($categories as &$cat) {
            $cat['children'] = $this->getCategoryTree($cat['asset_category_id']);
        }
        return $categories;
    }

    /**
     * Check if a category has any child categories.
     *
     * @param int $categoryId
     * @return bool
     */
    public function hasChildren($categoryId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM asset_categories WHERE parent_category_id = ?");
        $stmt->bind_param('i', $categoryId);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        return $count > 0;
    }

    /**
     * Get all assets that belong to a specific category (directly, not including sub‑categories).
     *
     * @param int $categoryId
     * @return array
     */
    public function getAssetsByCategory($categoryId) {
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
            WHERE aa.asset_category_id = ? AND a.status != 'inactive'
            ORDER BY a.asset_code
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
   
    /**
     * Get full asset details including custody history and audit trail.
     * @param int $assetId
     * @return array|null  // null if asset not found
     */
    public function getFullDetails($assetId) {
        $asset = $this->getById($assetId);
        if (!$asset) {
            return null;
        }
        $custody = $this->getCustodyHistory($assetId);
        $audit   = $this->getAuditTrail($assetId);
        return [
            'asset'   => $asset,
            'custody' => $custody,
            'audit'   => $audit,
        ];
    }

    /**
     * Get custody history for an asset.
     * @param int $assetId
     * @return array
     */
    public function getCustodyHistory($assetId) {
        $sql = "
            SELECT 
                ac.asset_custodies_id,
                ac.effectivity_date,
                ac.end_date,
                ac.status AS custody_status,
                p.full_name AS custodian_name,
                p.position,
                o.name AS office_name,
                ac.accountability_document,
                ac.accountability_reference
            FROM asset_custodies ac
            LEFT JOIN personnel p ON ac.custodian_id = p.personnel_id
            LEFT JOIN offices o ON ac.office_id = o.office_id
            WHERE ac.asset_id = ?
            ORDER BY ac.effectivity_date DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $assetId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get audit trail for an asset.
     * @param int $assetId
     * @return array
     */
    public function getAuditTrail($assetId) {
        $sql = "
            SELECT 
                at.audit_trail_id,
                at.action_type,
                at.module,
                at.previous_values,
                at.new_values,
                at.performed_at,
                u.username AS performed_by
            FROM audit_trail at
            LEFT JOIN users u ON at.performed_by = u.users_id
            WHERE at.asset_id = ?
            ORDER BY at.performed_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $assetId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

}