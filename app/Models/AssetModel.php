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
        if ($stmt->execute()) {
            return $this->db->insert_id;
            }
            return false;
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

    // ========== Methods for hierarchical browsing ==========

    /**
     * Recursively get the category tree starting from a parent ID.
     * @param int|null $parentId
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
        foreach ($categories as &$cat) {
            $cat['children'] = $this->getCategoryTree($cat['asset_category_id']);
        }
        return $categories;
    }

    /**
     * Check if a category has any child categories.
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
     * Get a single category by ID.
     * @param int $id
     * @return array|null
     */
    public function getCategory($id) {
        $stmt = $this->db->prepare("SELECT * FROM asset_categories WHERE asset_category_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // ========== Advanced search ==========

    /**
     * Search assets with advanced filters.
     * @param string|null $searchTerm
     * @param array       $filters (category_id, field, status, condition, date_from, date_to, cost_from, cost_to)
     * @return array
     */
    public function searchAssets($searchTerm = null, $filters = []) {
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
                ac.name AS category_name,
                GROUP_CONCAT(DISTINCT p.full_name SEPARATOR ', ') AS custodians
            FROM assets a
            LEFT JOIN asset_accounts aa ON a.asset_accounts_id = aa.asset_accounts_id
            LEFT JOIN asset_categories ac ON aa.asset_category_id = ac.asset_category_id
            LEFT JOIN asset_custodies acust ON a.asset_id = acust.asset_id AND acust.status = 'active'
            LEFT JOIN personnel p ON acust.custodian_id = p.personnel_id
            WHERE a.status != 'inactive'
        ";

        $params = [];
        $types = '';

        // Category filter
        if (!empty($filters['category_id'])) {
            $sql .= " AND aa.asset_category_id = ?";
            $params[] = $filters['category_id'];
            $types .= 'i';
        }

        // Search term
        if (!empty($searchTerm)) {
            $field = $filters['field'] ?? 'all';
            $like = '%' . $searchTerm . '%';
            if ($field === 'all') {
                $sql .= " AND (
                    a.asset_code LIKE ? OR a.description LIKE ? OR a.brand LIKE ? OR a.model LIKE ? 
                    OR a.serial_number LIKE ? OR aa.account_code LIKE ? OR aa.account_name LIKE ? 
                    OR p.full_name LIKE ?
                )";
                for ($i = 0; $i < 8; $i++) {
                    $params[] = $like;
                    $types .= 's';
                }
            } else {
                $fieldMap = [
                    'asset_code'   => 'a.asset_code',
                    'description'  => 'a.description',
                    'brand'        => 'a.brand',
                    'model'        => 'a.model',
                    'serial_number'=> 'a.serial_number',
                    'account_code' => 'aa.account_code',
                    'account_name' => 'aa.account_name',
                    'custodian'    => 'p.full_name'
                ];
                if (isset($fieldMap[$field])) {
                    $sql .= " AND " . $fieldMap[$field] . " LIKE ?";
                    $params[] = $like;
                    $types .= 's';
                }
            }
        }

        // Status
        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }

        // Condition
        if (!empty($filters['condition'])) {
            $sql .= " AND a.condition = ?";
            $params[] = $filters['condition'];
            $types .= 's';
        }

        // Date range
        if (!empty($filters['date_from'])) {
            $sql .= " AND a.acquisition_date >= ?";
            $params[] = $filters['date_from'];
            $types .= 's';
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND a.acquisition_date <= ?";
            $params[] = $filters['date_to'];
            $types .= 's';
        }

        // Cost range
        if (!empty($filters['cost_from']) && $filters['cost_from'] !== '') {
            $sql .= " AND a.acquisition_cost >= ?";
            $params[] = (float)$filters['cost_from'];
            $types .= 'd';
        }
        if (!empty($filters['cost_to']) && $filters['cost_to'] !== '') {
            $sql .= " AND a.acquisition_cost <= ?";
            $params[] = (float)$filters['cost_to'];
            $types .= 'd';
        }

        $sql .= " GROUP BY a.asset_id ORDER BY a.asset_code";

        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get assets by category (with optional search/filters).
     * @param int         $categoryId
     * @param string|null $search
     * @param array       $filters
     * @return array
     */
    public function getAssetsByCategory($categoryId, $search = null, $filters = []) {
        $filters['category_id'] = $categoryId;
        return $this->searchAssets($search, $filters);
    }

    /**
     * Get all assets (with optional search/filters).
     * @param string|null $search
     * @param array       $filters
     * @return array
     */
    public function getAllAssets($search = null, $filters = []) {
        return $this->searchAssets($search, $filters);
    }

    // ========== Details, custody, audit ==========

    /**
     * Get full asset details including custody and audit.
     * @param int $assetId
     * @return array|null
     */
    public function getFullDetails($assetId) {
        $asset = $this->getById($assetId);
        if (!$asset) {
            return null;
        }
        return [
            'asset'   => $asset,
            'custody' => $this->getCustodyHistory($assetId),
            'audit'   => $this->getAuditTrail($assetId),
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