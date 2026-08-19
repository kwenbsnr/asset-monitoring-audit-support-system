<?php
namespace App\Models;

use App\Config\Database;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

/**
 * Thrown when a save would violate a unique constraint (asset_code,
 * qr_code_ref, serial_number). Carries a friendly, field-specific message
 * so controllers can show it to the user instead of a fatal error.
 */
class DuplicateEntryException extends \RuntimeException {
    private string $field;
    private string $value;

    public function __construct(string $message, string $field, string $value) {
        parent::__construct($message);
        $this->field = $field;
        $this->value = $value;
    }

    public function getField(): string {
        return $this->field;
    }

    public function getValue(): string {
        return $this->value;
    }
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
    public function getAccountById(int $id) {
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

    /**
     * Friendly labels for columns that carry a unique constraint.
     * @param string $column
     * @return string
     */
    private function duplicateFieldLabel(string $column): string {
        $labels = [
            'asset_code'    => 'Asset Code',
            'qr_code_ref'   => 'QR Code Reference',
            'serial_number' => 'Serial Number',
        ];
        return $labels[$column] ?? ucwords(str_replace('_', ' ', $column));
    }

    /**
     * Convert a MySQL "Duplicate entry" exception into a
     * DuplicateEntryException carrying a friendly, field-specific message.
     * @param \mysqli_sql_exception $e
     * @return DuplicateEntryException
     */
    private function toDuplicateEntryException(\mysqli_sql_exception $e): DuplicateEntryException {
        $value = '';
        $column = '';
        if (preg_match("/Duplicate entry '(.*)' for key '([^']+)'/", $e->getMessage(), $m)) {
            $value = $m[1];
            $parts = explode('.', $m[2]);
            $column = end($parts);
        }
        $label = $this->duplicateFieldLabel($column ?: 'value');
        $message = $value !== ''
            ? sprintf("%s '%s' is already used by another asset. Please enter a unique %s.", $label, $value, $label)
            : sprintf('This %s is already used by another asset.', $label);
        return new DuplicateEntryException($message, $column, $value);
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
     * Get a single asset by ID – include verifier username.
     * @param int $id
     * @return array|null
     */
    public function getById(int $id) {
        $stmt = $this->db->prepare("
            SELECT 
                a.*,
                aa.account_code,
                aa.account_name,
                u.username AS verified_by_username
            FROM assets a
            LEFT JOIN asset_accounts aa ON a.asset_accounts_id = aa.asset_accounts_id
            LEFT JOIN users u ON a.verified_by = u.users_id
            WHERE a.asset_id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Build the next asset code for a given account/year: YEAR-ACCOUNTCODE-SEQ,
     * e.g. "2026-07-010-0001". Sequence is 4-digit, zero-padded, and resets
     * per account per year. Never trust a client-supplied asset code — this
     * is the only place asset codes are produced.
     *
     * @param int      $accountId
     * @param int|null $year Defaults to the current year.
     * @return string
     */
    public function generateAssetCode(int $accountId, ?int $year = null): string {
        $year = $year ?: (int)date('Y');
        $account = $this->getAccountById($accountId);
        if (!$account) {
            throw new \InvalidArgumentException('Invalid asset account.');
        }
        $prefix = $year . '-' . $account['account_code'] . '-';

        // Pull the highest existing sequence number under this prefix. Taking
        // the segment after the LAST dash works even though account_code
        // itself contains a dash (e.g. "07-010").
        $stmt = $this->db->prepare("
            SELECT MAX(CAST(SUBSTRING_INDEX(asset_code, '-', -1) AS UNSIGNED)) AS max_seq
            FROM assets
            WHERE asset_code LIKE ?
        ");
        $likePattern = $prefix . '%';
        $stmt->bind_param('s', $likePattern);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $nextSeq = ((int)($row['max_seq'] ?? 0)) + 1;

        return $prefix . str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Insert a new asset. The asset code is generated server-side from the
     * account + year and is never accepted from client input. If two saves
     * race for the same sequence number, this retries with a freshly
     * computed code instead of failing the request.
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data) {
        // Deliberately the CURRENT year — the code's year reflects when it was
        // registered/assigned, not the asset's acquisition_date (which can be
        // any year back to 1990). This is what makes the sequence reset on
        // Jan 1st: the bucket is tied to "today", not to user-entered data.
        $year = (int)date('Y');

        $maxAttempts = 5;
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $assetCode = $this->generateAssetCode((int)$data['asset_accounts_id'], $year);
            try {
                return $this->insertAsset($assetCode, $data);
            } catch (DuplicateEntryException $e) {
                // Only retry when the collision was the generated asset_code
                // itself (a concurrent save grabbed the same sequence number
                // between our SELECT and our INSERT). Any other unique-field
                // collision (qr_code_ref, serial_number) is a real problem
                // and should surface to the user as-is.
                if ($e->getField() !== 'asset_code' || $attempt === $maxAttempts) {
                    throw $e;
                }
            }
        }
        return false;
    }

    /**
     * Raw insert with an already-generated asset code. Split out from
     * create() so the retry loop can call it repeatedly without re-deciding
     * the code-generation policy.
     *
     * @param string $assetCode
     * @param array  $data
     * @return int|false
     */
    private function insertAsset(string $assetCode, array $data) {
        $stmt = $this->db->prepare("
            INSERT INTO assets (
                asset_code, asset_name, qr_code_ref, description, brand, model, serial_number,
                acquisition_cost, acquisition_date, asset_accounts_id, status, `condition`, remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $qr_code_ref = 'QR-' . strtoupper(uniqid());
        $stmt->bind_param(
            'sssssssdsisss',
            $assetCode,
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
        try {
            if ($stmt->execute()) {
                return $this->db->insert_id;
            }
            return false;
        } catch (\mysqli_sql_exception $e) {
            if ((int)$e->getCode() === 1062) {
                throw $this->toDuplicateEntryException($e);
            }
            throw $e;
        }
    }

    /**
     * Update an existing asset.
     * @param int   $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data) {
        // asset_code is intentionally NOT updatable — it's generated once at
        // creation (see create()/generateAssetCode()) and stays fixed for the
        // life of the asset for audit-trail integrity.
        $stmt = $this->db->prepare("
            UPDATE assets SET
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
            'sssssdsisssi',
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
        try {
            return $stmt->execute();
        } catch (\mysqli_sql_exception $e) {
            if ((int)$e->getCode() === 1062) {
                throw $this->toDuplicateEntryException($e);
            }
            throw $e;
        }
    }

    /**
     * Soft delete (set status = 'inactive').
     * @param int $id
     * @return bool
     */
    public function delete(int $id) {
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
    public function searchAssets(?string $searchTerm = null, array $filters = []) {
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
                a.verification_status,
                a.verified_at,
                aa.account_code,
                aa.account_name,
                u.username AS verified_by_username,
                GROUP_CONCAT(DISTINCT p.full_name SEPARATOR ', ') AS custodians,
                (SELECT ac.asset_custodies_id FROM asset_custodies ac 
                    WHERE ac.asset_id = a.asset_id AND ac.status = 'active' 
                    ORDER BY ac.effectivity_date DESC LIMIT 1) AS active_custody_id
            FROM assets a
            LEFT JOIN asset_accounts aa ON a.asset_accounts_id = aa.asset_accounts_id
            LEFT JOIN asset_custodies acust ON a.asset_id = acust.asset_id AND acust.status = 'active'
            LEFT JOIN personnel p ON acust.custodian_id = p.personnel_id
            LEFT JOIN users u ON a.verified_by = u.users_id
            WHERE a.status != 'inactive'
        ";

        $params = [];
        $types = '';

        if (!empty($filters['account_id'])) {
            $sql .= " AND a.asset_accounts_id = ?";
            $params[] = $filters['account_id'];
            $types .= 'i';
        }

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
    public function getAllAssets(?string $search = null, array $filters = []) {
        return $this->searchAssets($search, $filters);
    }

    /**
     * Get assets belonging to a specific account.
     * @param int         $accountId
     * @param string|null $search
     * @param array       $filters
     * @return array
     */
    public function getAssetsByAccountId(int $accountId, ?string $search = null, array $filters = []) {
        $filters['account_id'] = $accountId;
        return $this->searchAssets($search, $filters);
    }

    // ===== Personnel & Offices =====

    /**
     * Get all active personnel for dropdown.
     * @return array
     */
    public function getPersonnel() {
        $result = $this->db->query("SELECT personnel_id, full_name, position, office_id, salary_grade FROM personnel WHERE is_active = 1 ORDER BY full_name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get a single personnel by ID.
     * @param int $id
     * @return array|null
     */
    public function getPersonnelById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM personnel WHERE personnel_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Get all offices for dropdown.
     * @return array
     */
    public function getOffices() {
        $sql = "
            SELECT o.office_id, o.name, o.office_type, o.head_personnel_id,
                   p.full_name AS head_name
            FROM offices o
            LEFT JOIN personnel p ON o.head_personnel_id = p.personnel_id
            WHERE o.office_type = 'internal'
               OR (o.office_type = 'external' AND o.is_transfer_destination = 1)
            ORDER BY (o.office_type = 'internal') DESC, o.name
        ";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get a single office by ID.
     * @param int $id
     * @return array|null
     */
    public function getOfficeById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM offices WHERE office_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // ===== New methods for Assets by Office (Asset Manager) =====

    /**
     * Get offices with asset and custodian counts (for Asset Manager).
     * @return array
     */
    public function getOfficesWithData() {
        $sql = "
            SELECT 
                o.office_id,
                o.name,
                o.location,
                COUNT(DISTINCT ac.custodian_id) AS custodian_count,
                COUNT(DISTINCT a.asset_id) AS asset_count
            FROM offices o
            LEFT JOIN asset_custodies ac ON o.office_id = ac.office_id AND ac.status = 'active'
            LEFT JOIN assets a ON ac.asset_id = a.asset_id AND a.status != 'inactive'
            WHERE o.office_id IS NOT NULL
            GROUP BY o.office_id
            ORDER BY o.name
        ";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get custodians (personnel) for a specific office (for Asset Manager).
     * @param int $officeId
     * @return array
     */
    public function getCustodiansByOfficeForAssetManager(int $officeId, int $limit, int $offset) {
        $sql = "
            SELECT DISTINCT 
                p.personnel_id,
                p.full_name,
                p.position,
                (SELECT COUNT(DISTINCT ac.asset_id) FROM asset_custodies ac WHERE ac.custodian_id = p.personnel_id AND ac.status = 'active') AS asset_count
            FROM personnel p
            INNER JOIN asset_custodies ac ON p.personnel_id = ac.custodian_id
            WHERE ac.office_id = ? AND ac.status = 'active'
            ORDER BY p.full_name
            LIMIT ? OFFSET ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iii', $officeId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Count distinct custodians in an office (for Assets-by-Office pagination).
     * @param int $officeId
     * @return int
     */
    public function countCustodiansByOfficeForAssetManager(int $officeId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT p.personnel_id)
            FROM personnel p
            INNER JOIN asset_custodies ac ON p.personnel_id = ac.custodian_id
            WHERE ac.office_id = ? AND ac.status = 'active'
        ");
        $stmt->bind_param('i', $officeId);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return (int)$count;
    }

    /**
     * Get assets for a custodian (for Asset Manager).
     * @param int $custodianId
     * @return array
     */
    public function getAssetsByCustodianForAssetManager(int $custodianId) {
        $sql = "
            SELECT 
                a.asset_id,
                a.asset_code,
                a.asset_name,
                a.status,
                a.condition,
                aa.account_code
            FROM assets a
            INNER JOIN asset_custodies ac ON a.asset_id = ac.asset_id
            LEFT JOIN asset_accounts aa ON a.asset_accounts_id = aa.asset_accounts_id
            WHERE ac.custodian_id = ? AND ac.status = 'active' AND a.status != 'inactive'
            ORDER BY a.asset_code
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $custodianId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ===== Details (custody, audit, transfers) =====

    /**
     * Get full asset details including custody, audit, and transfers.
     * @param int $assetId
     * @return array|null
     */
    public function getFullDetails(int $assetId) {
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
    public function getCustodyHistory(int $assetId) {
        $sql = "
            SELECT 
                ac.asset_custodies_id,
                ac.effectivity_date,
                ac.end_date,
                ac.status AS custody_status,
                p.full_name AS custodian_name,
                p.position,
                o.name AS office_name,
                ac.property_number
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
    public function getAuditTrail(int $assetId) {
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
    public function getTransferHistory(int $assetId) {
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
    public function getByQrCode(string $qrCode) {
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
    public function updateCondition(int $id, string $condition) {
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
    public function searchAssetByText(string $query) {
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

    /**
     * Dispose an asset (update status, reason, and log audit).
     * @param int    $assetId
     * @param string $reason
     * @param int    $userId
     * @return bool
     */
    public function disposeAsset(int $assetId, string $reason, int $userId) {
        $stmt = $this->db->prepare("UPDATE assets SET status = 'disposed', disposal_reason = ?, updated_at = NOW() WHERE asset_id = ?");
        $stmt->bind_param('si', $reason, $assetId);
        $success = $stmt->execute();

        if ($success) {
            $asset = $this->getById($assetId);
            $oldValues = json_encode(['status' => $asset['status']]);
            $newValues = json_encode(['status' => 'disposed', 'disposal_reason' => $reason]);
            $this->logAudit($assetId, $userId, 'DISPOSE', 'ASSET', $oldValues, $newValues);
        }
        return $success;
    }

    /**
     * Log an audit entry.
     * @param int    $assetId
     * @param int    $userId
     * @param string $actionType
     * @param string $module
     * @param string $previousValues
     * @param string $newValues
     */
    public function logAudit(int $assetId, int $userId, string $actionType, string $module, string $previousValues, string $newValues) {
        $stmt = $this->db->prepare("
            INSERT INTO audit_trail (asset_id, performed_by, action_type, module, previous_values, new_values)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('iissss', $assetId, $userId, $actionType, $module, $previousValues, $newValues);
        $stmt->execute();
    }

    /**
     * Get assets grouped by office (for the by_office view).
     * Includes custodian name if assigned.
     * @return array
     */
    public function getAssetsByOffice() {
        $sql = "
            SELECT 
                o.office_id,
                o.name AS office_name,
                a.asset_id,
                a.asset_code,
                a.asset_name,
                a.status,
                aa.account_code,
                p.full_name AS custodian_name
            FROM offices o
            LEFT JOIN asset_custodies ac ON o.office_id = ac.office_id AND ac.status = 'active'
            LEFT JOIN assets a ON ac.asset_id = a.asset_id AND a.status != 'inactive'
            LEFT JOIN asset_accounts aa ON a.asset_accounts_id = aa.asset_accounts_id
            LEFT JOIN personnel p ON ac.custodian_id = p.personnel_id
            WHERE o.office_id IS NOT NULL
            ORDER BY o.name, a.asset_code
        ";
        $result = $this->db->query($sql);
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $grouped = [];
        foreach ($rows as $row) {
            $officeId = $row['office_id'];
            if (!isset($grouped[$officeId])) {
                $grouped[$officeId] = [
                    'office_name' => $row['office_name'],
                    'assets' => []
                ];
            }
            if ($row['asset_id']) {
                $grouped[$officeId]['assets'][] = $row;
            }
        }
        return array_values($grouped);
    }

    /**
     * Fetch multiple assets by their IDs.
     * @param array $ids
     * @return array
     */
    public function getAssetsByIds(array $ids) {
        if (empty($ids)) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT * FROM assets WHERE asset_id IN ($placeholders) AND status != 'inactive' ORDER BY asset_code";
        $stmt = $this->db->prepare($sql);
        $types = str_repeat('i', count($ids));
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Log a custodial transfer in asset_transfers.
     * @param int      $assetId
     * @param int|null $fromCustodianId
     * @param int      $toCustodianId
     * @param int|null $fromOfficeId
     * @param int      $toOfficeId
     * @param string   $transferDate (Y-m-d)
     * @param string   $status (default 'approved')
     * @return bool
     */
    public function logTransfer(int $assetId, ?int $fromCustodianId, int $toCustodianId, ?int $fromOfficeId, int $toOfficeId, string $transferDate, string $status = 'approved') {
        $transferNumber = 'TR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        $stmt = $this->db->prepare("
            INSERT INTO asset_transfers (
                asset_id, from_custodian_id, to_custodian_id, from_office_id, to_office_id,
                transfer_number, transfer_date, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param(
            'iiiiisss',
            $assetId,
            $fromCustodianId,
            $toCustodianId,
            $fromOfficeId,
            $toOfficeId,
            $transferNumber,
            $transferDate,
            $status
        );
        return $stmt->execute();
    }

    /**
     * Update operational fields for inspection – sets verified_at and verified_by when status becomes 'verified'.
     * @param int   $id
     * @param array $data
     * @param int|null $userId
     * @return bool
     */
    public function updateInspection(int $id, array $data, ?int $userId = null) {
        $verifiedAt = null;
        $verifiedBy = null;
        if ($data['verification_status'] === 'verified' && $userId) {
            $verifiedAt = date('Y-m-d H:i:s');
            $verifiedBy = $userId;
        }
        $stmt = $this->db->prepare("
            UPDATE assets SET
                `condition` = ?,
                status = ?,
                verification_status = ?,
                inspection_remarks = ?,
                verified_at = ?,
                verified_by = ?,
                updated_at = NOW()
            WHERE asset_id = ?
        ");
        $stmt->bind_param(
            'sssssii',
            $data['condition'],
            $data['status'],
            $data['verification_status'],
            $data['inspection_remarks'],
            $verifiedAt,
            $verifiedBy,
            $id
        );
        return $stmt->execute();
    }

    // ===== Verification worklist (Inspection Officer "Verify Asset" page) =====

    /**
     * Shared WHERE/JOIN builder for the verification worklist.
     * One row per asset, with its active custodian/office (if any) and
     * account (category) attached. Used by both the paginated row fetch
     * and the count query so filtering stays identical between the two.
     * @param string|null $search
     * @param array       $filters  ['account_id' => int, 'custodian' => string, 'office_id' => int]
     * @param bool        $countOnly
     * @return array [string $sql, array $params, string $types]
     */
    private function buildVerificationWorklistQuery(?string $search, array $filters, bool $countOnly) {
        $select = $countOnly
            ? "SELECT COUNT(*) "
            : "SELECT
                    a.asset_id, a.asset_code, a.asset_name, a.serial_number,
                    a.`condition`, a.status, a.verification_status, a.verified_at, a.inspection_remarks,
                    aa.asset_accounts_id, aa.account_code, aa.account_name,
                    p.personnel_id AS custodian_id, p.full_name AS custodian_name, p.position,
                    o.office_id, o.name AS office_name ";

        $sql = $select . "
            FROM assets a
            LEFT JOIN asset_accounts aa ON a.asset_accounts_id = aa.asset_accounts_id
            LEFT JOIN asset_custodies ac ON a.asset_id = ac.asset_id AND ac.status = 'active'
            LEFT JOIN personnel p ON ac.custodian_id = p.personnel_id
            LEFT JOIN offices o ON ac.office_id = o.office_id
            WHERE a.status != 'inactive'
        ";

        $params = [];
        $types = '';

        if (!empty($search)) {
            $like = '%' . $search . '%';
            $sql .= " AND (a.asset_code LIKE ? OR a.asset_name LIKE ? OR a.serial_number LIKE ? OR p.full_name LIKE ? OR o.name LIKE ?)";
            for ($i = 0; $i < 5; $i++) {
                $params[] = $like;
                $types .= 's';
            }
        }
        if (!empty($filters['account_id'])) {
            $sql .= " AND a.asset_accounts_id = ?";
            $params[] = $filters['account_id'];
            $types .= 'i';
        }
        if (!empty($filters['custodian'])) {
            $sql .= " AND p.full_name LIKE ?";
            $params[] = '%' . $filters['custodian'] . '%';
            $types .= 's';
        }
        if (!empty($filters['office_id'])) {
            $sql .= " AND o.office_id = ?";
            $params[] = $filters['office_id'];
            $types .= 'i';
        }

        return [$sql, $params, $types];
    }

    /**
     * Fetch one page of the verification worklist (one row per asset).
     * @param string|null $search
     * @param array       $filters
     * @param int         $limit
     * @param int         $offset
     * @return array
     */
    public function getVerificationWorklist(?string $search, array $filters, int $limit, int $offset) {
        [$sql, $params, $types] = $this->buildVerificationWorklistQuery($search, $filters, false);
        $sql .= " ORDER BY (p.full_name IS NULL), p.full_name, a.asset_code LIMIT ? OFFSET ?";
        $params[] = $limit;
        $types .= 'i';
        $params[] = $offset;
        $types .= 'i';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Count total rows matching the verification worklist filters (for pagination).
     * @param string|null $search
     * @param array       $filters
     * @return int
     */
    public function countVerificationWorklist(?string $search, array $filters) {
        [$sql, $params, $types] = $this->buildVerificationWorklistQuery($search, $filters, true);
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return (int)$count;
    }
}