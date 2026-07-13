<?php
namespace App\Models;

use App\Config\Database;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class DisposalModel {
    /** @var \mysqli */
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a disposal request.
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO disposal_requests (
                asset_id, requested_by, reason, supporting_document, status
            ) VALUES (?, ?, ?, ?, 'pending')
        ");
        $stmt->bind_param(
            'iiss',
            $data['asset_id'],
            $data['requested_by'],
            $data['reason'],
            $data['supporting_document']
        );
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    /**
     * Update asset status to pending_disposal.
     * @param int $assetId
     * @return bool
     */
    public function setAssetPendingDisposal($assetId) {
        $stmt = $this->db->prepare("UPDATE assets SET status = 'pending_disposal' WHERE asset_id = ?");
        $stmt->bind_param('i', $assetId);
        return $stmt->execute();
    }

    /**
     * Get all disposal requests (with filters).
     * @param array $filters (status, user_id, asset_id)
     * @return array
     */
    public function getAll($filters = []) {
        $sql = "
            SELECT 
                dr.*,
                a.asset_code,
                a.asset_name,
                u.username AS requested_by_username,
                ru.username AS reviewed_by_username
            FROM disposal_requests dr
            LEFT JOIN assets a ON dr.asset_id = a.asset_id
            LEFT JOIN users u ON dr.requested_by = u.users_id
            LEFT JOIN users ru ON dr.reviewed_by = ru.users_id
            WHERE 1=1
        ";
        $params = [];
        $types = '';

        if (!empty($filters['status'])) {
            $sql .= " AND dr.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }
        if (!empty($filters['user_id'])) {
            $sql .= " AND dr.requested_by = ?";
            $params[] = $filters['user_id'];
            $types .= 'i';
        }
        if (!empty($filters['asset_id'])) {
            $sql .= " AND dr.asset_id = ?";
            $params[] = $filters['asset_id'];
            $types .= 'i';
        }

        $sql .= " ORDER BY dr.created_at DESC";

        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get a single disposal request by ID.
     * @param int $id
     * @return array|null
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT 
                dr.*,
                a.asset_code,
                a.asset_name,
                u.username AS requested_by_username,
                ru.username AS reviewed_by_username
            FROM disposal_requests dr
            LEFT JOIN assets a ON dr.asset_id = a.asset_id
            LEFT JOIN users u ON dr.requested_by = u.users_id
            LEFT JOIN users ru ON dr.reviewed_by = ru.users_id
            WHERE dr.id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Update disposal request status (approve/reject).
     * @param int   $id
     * @param string $status
     * @param int   $reviewedBy
     * @param string $remarks
     * @return bool
     */
    public function updateStatus($id, $status, $reviewedBy, $remarks = null) {
        $stmt = $this->db->prepare("
            UPDATE disposal_requests SET
                status = ?,
                reviewed_by = ?,
                review_remarks = ?,
                reviewed_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param('sisi', $status, $reviewedBy, $remarks, $id);
        return $stmt->execute();
    }

    /**
     * Update asset status after disposal decision.
     * @param int    $assetId
     * @param string $newStatus (disposed or active)
     * @return bool
     */
    public function updateAssetStatus($assetId, $newStatus) {
        $stmt = $this->db->prepare("UPDATE assets SET status = ? WHERE asset_id = ?");
        $stmt->bind_param('si', $newStatus, $assetId);
        return $stmt->execute();
    }

    /**
     * Get counts of pending requests (for dashboard alerts).
     * @return int
     */
    public function getPendingCount() {
        $result = $this->db->query("SELECT COUNT(*) AS count FROM disposal_requests WHERE status = 'pending'");
        return $result->fetch_assoc()['count'] ?? 0;
    }
}