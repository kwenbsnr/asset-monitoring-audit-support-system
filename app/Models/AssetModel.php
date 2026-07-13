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

    // ===== Asset Account methods =====

    /**
     * Get list of asset accounts with asset count.
     * @return array
     */
    public function getAssetAccountsList() {
        $sql = "
            SELECT 
                aa.asset_accounts_id,
                aa.account_code,
                aa.account_name,
                (SELECT COUNT(*) FROM assets WHERE asset_accounts_id = aa.asset_accounts_id AND status != 'inactive') AS asset_count
            FROM asset_accounts aa
            ORDER BY aa.account_code
        ";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get a single asset account by ID.
     * @param int $id
     * @return array|null
     */
    public function getAccountById($id) {
        $stmt = $this->db->prepare("SELECT * FROM asset_accounts WHERE asset_accounts_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Get all asset accounts (for dropdowns).
     * @return array
     */
    public function getAssetAccounts() {
        $result = $this->db->query("SELECT asset_accounts_id, account_code, account_name FROM asset_accounts ORDER BY account_code");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ===== Asset methods =====

    /**
     * Fetch all active assets with account details.
     * @return array
     */
    public function getAll() {
        $sql = "
            SELECT 
                a.asset_id,
                a.asset_code,
                a.asset_name,
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
                aa.account_name
            FROM assets a
            LEFT JOIN asset_accounts aa ON a.asset_accounts_id = aa.asset_accounts_id
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
                aa.account_code,
                aa.account_name
            FROM assets a
            LEFT JOIN asset_accounts aa ON a.asset_accounts_id = aa.asset_accounts_id
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
     * @return int|false
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO assets (
                asset_code, asset_name, qr_code_ref, description, brand, model, serial_number,
                acquisition_cost, acquisition_date, asset_accounts_id, status, `condition`, remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $qr_code_ref = 'QR-' . strtoupper(uniqid());
        $stmt->bind_param(
            'sssssssdsisss',
            $data['asset_code'],
            $data['asset_name'],
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
                asset_name = ?,
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
            'ssssssdsisssi',
            $data['asset_code'],
            $data['asset_name'],
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

    // ===== Search =====

    /**
     * Search assets with advanced filters.
     * @param string|null $searchTerm
     * @param array       $filters (account_id, field, status, condition, date_from, date_to, cost_from, cost_to)
     * @return array
     */
    
    /**
     * Search assets with advanced filters.
     * @param string|null $searchTerm
     * @param array       $filters (account_id, field, status, condition, date_from, date_to, cost_from, cost_to)
     * @return array
     */
    public function searchAssets($searchTerm = null, $filters = []) {
        $sql = "
            SELECT 
                a.asset_id,
                a.asset_code,
                a.asset_name,
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
                GROUP_CONCAT(DISTINCT p.full_name SEPARATOR ', ') AS custodians,
                (SELECT ac.asset_custodies_id FROM asset_custodies ac 
                WHERE ac.asset_id = a.asset_id AND ac.status = 'active' 
                ORDER BY ac.effectivity_date DESC LIMIT 1) AS active_custody_id
            FROM assets a
            LEFT JOIN asset_accounts aa ON a.asset_accounts_id = aa.asset_accounts_id
            LEFT JOIN asset_custodies acust ON a.asset_id = acust.asset_id AND acust.status = 'active'
            LEFT JOIN personnel p ON acust.custodian_id = p.personnel_id
            WHERE a.status != 'inactive'
        ";

        $params = [];
        $types = '';

        // Account filter
        if (!empty($filters['account_id'])) {
            $sql .= " AND a.asset_accounts_id = ?";
            $params[] = $filters['account_id'];
            $types .= 'i';
        }

        // Search term
        if (!empty($searchTerm)) {
            $field = $filters['field'] ?? 'all';
            $like = '%' . $searchTerm . '%';
            if ($field === 'all') {
                $sql .= " AND (
                    a.asset_code LIKE ? OR a.asset_name LIKE ? OR a.description LIKE ? OR a.brand LIKE ? OR a.model LIKE ? 
                    OR a.serial_number LIKE ? OR aa.account_code LIKE ? OR aa.account_name LIKE ? 
                    OR p.full_name LIKE ?
                )";
                for ($i = 0; $i < 9; $i++) {
                    $params[] = $like;
                    $types .= 's';
                }
            } else {
                $fieldMap = [
                    'asset_code'   => 'a.asset_code',
                    'asset_name'   => 'a.asset_name',
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

        // Status, condition, date, cost filters
        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }
        if (!empty($filters['condition'])) {
            $sql .= " AND a.condition = ?";
            $params[] = $filters['condition'];
            $types .= 's';
        }
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
     * Get all assets (with optional search/filters).
     * @param string|null $search
     * @param array       $filters
     * @return array
     */
    public function getAllAssets($search = null, $filters = []) {
        return $this->searchAssets($search, $filters);
    }

    /**
     * Get assets belonging to a specific account.
     * @param int         $accountId
     * @param string|null $search
     * @param array       $filters
     * @return array
     */
    public function getAssetsByAccountId($accountId, $search = null, $filters = []) {
        $filters['account_id'] = $accountId;
        return $this->searchAssets($search, $filters);
    }

    // ===== Personnel & Offices =====

    /**
     * Get all active personnel for dropdown.
     * @return array
     */
    public function getPersonnel() {
        $result = $this->db->query("SELECT personnel_id, full_name, position FROM personnel WHERE is_active = 1 ORDER BY full_name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all offices for dropdown.
     * @return array
     */
    public function getOffices() {
        $result = $this->db->query("SELECT office_id, name FROM offices ORDER BY name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ===== Details (custody, audit, transfers) =====

    /**
     * Get full asset details including custody, audit, and transfers.
     * @param int $assetId
     * @return array|null
     */
    public function getFullDetails($assetId) {
        $asset = $this->getById($assetId);
        if (!$asset) {
            return null;
        }
        return [
            'asset'    => $asset,
            'custody'  => $this->getCustodyHistory($assetId),
            'audit'    => $this->getAuditTrail($assetId),
            'transfers'=> $this->getTransferHistory($assetId),
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

    /**
     * Get transfer history for an asset.
     * @param int $assetId
     * @return array
     */
    public function getTransferHistory($assetId) {
        $sql = "
            SELECT 
                atr.transfer_number,
                atr.transfer_date,
                atr.status,
                atr.remarks,
                p_from.full_name AS from_custodian,
                p_to.full_name AS to_custodian,
                o_from.name AS from_office,
                o_to.name AS to_office
            FROM asset_transfers atr
            LEFT JOIN personnel p_from ON atr.from_custodian_id = p_from.personnel_id
            LEFT JOIN personnel p_to ON atr.to_custodian_id = p_to.personnel_id
            LEFT JOIN offices o_from ON atr.from_office_id = o_from.office_id
            LEFT JOIN offices o_to ON atr.to_office_id = o_to.office_id
            WHERE atr.asset_id = ?
            ORDER BY atr.transfer_date DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $assetId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Find an asset by its QR code reference.
     * @param string $qrCode
     * @return array|null
     */
    public function getByQrCode($qrCode) {
        $stmt = $this->db->prepare("SELECT * FROM assets WHERE qr_code_ref = ? AND status != 'inactive'");
        $stmt->bind_param('s', $qrCode);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Update asset condition.
     * @param int    $id
     * @param string $condition
     * @return bool
     */
    public function updateCondition($id, $condition) {
        $stmt = $this->db->prepare("UPDATE assets SET `condition` = ? WHERE asset_id = ?");
        $stmt->bind_param('si', $condition, $id);
        return $stmt->execute();
    }

    /**
     * Find an asset by text search (asset_code, asset_name, description, or serial_number).
     * Returns the first matching asset.
     * @param string $query
     * @return array|null
     */
    public function searchAssetByText($query) {
        $like = '%' . $query . '%';
        $stmt = $this->db->prepare("
            SELECT * FROM assets 
            WHERE (asset_code LIKE ? OR asset_name LIKE ? OR description LIKE ? OR serial_number LIKE ?)
            AND status != 'inactive'
            LIMIT 1
        ");
        $stmt->bind_param('ssss', $like, $like, $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}