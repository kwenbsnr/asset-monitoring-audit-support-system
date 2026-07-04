<?php
namespace App\Models;

use App\Config\Database;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class ReportModel {
    /** @var \mysqli */
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all reports.
     * @return array
     */
    public function getAll() {
        $sql = "
            SELECT 
                ar.asset_report_id,
                ar.report_number,
                ar.report_date,
                ar.office_id,
                o.name AS office_name,
                ar.prepared_by,
                u.username AS prepared_by_username,
                ar.status,
                ar.remarks,
                ar.created_at
            FROM asset_reports ar
            LEFT JOIN offices o ON ar.office_id = o.office_id
            LEFT JOIN users u ON ar.prepared_by = u.users_id
            ORDER BY ar.report_date DESC
        ";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get a single report with items.
     * @param int $id
     * @return array|null
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT 
                ar.*,
                o.name AS office_name,
                u.username AS prepared_by_username
            FROM asset_reports ar
            LEFT JOIN offices o ON ar.office_id = o.office_id
            LEFT JOIN users u ON ar.prepared_by = u.users_id
            WHERE ar.asset_report_id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $report = $result->fetch_assoc();
        if ($report) {
            $report['items'] = $this->getItems($id);
        }
        return $report;
    }

    /**
     * Get items for a report.
     * @param int $reportId
     * @return array
     */
    public function getItems($reportId) {
        $stmt = $this->db->prepare("
            SELECT 
                ari.*,
                a.asset_code,
                a.description AS asset_description,
                u.username AS verified_by_username
            FROM asset_report_items ari
            LEFT JOIN assets a ON ari.asset_id = a.asset_id
            LEFT JOIN users u ON ari.verified_by = u.users_id
            WHERE ari.asset_report_id = ?
        ");
        $stmt->bind_param('i', $reportId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Create a new report, ensuring a unique report_number.
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        // Check if the supplied report number already exists
        $check = $this->db->prepare("SELECT COUNT(*) FROM asset_reports WHERE report_number = ?");
        $check->bind_param('s', $data['report_number']);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0) {
            // Generate a unique number by appending a counter
            $original = $data['report_number'];
            $counter = 1;
            do {
                $new_number = $original . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);
                $chk = $this->db->prepare("SELECT COUNT(*) FROM asset_reports WHERE report_number = ?");
                $chk->bind_param('s', $new_number);
                $chk->execute();
                $chk->bind_result($cnt);
                $chk->fetch();
                $chk->close();
                if ($cnt == 0) {
                    $data['report_number'] = $new_number;
                    break;
                }
                $counter++;
            } while (true);
        }

        $stmt = $this->db->prepare("
            INSERT INTO asset_reports (
                report_number, report_date, office_id, prepared_by, status, remarks
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'ssiiss',
            $data['report_number'],
            $data['report_date'],
            $data['office_id'],
            $data['prepared_by'],
            $data['status'],
            $data['remarks']
        );
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    /**
     * Add an item to a report, checking for duplicates.
     * @param array $data
     * @return bool
     */
    public function addItem($data) {
        // Check if this asset is already in the report
        $check = $this->db->prepare("
            SELECT COUNT(*) FROM asset_report_items 
            WHERE asset_report_id = ? AND asset_id = ?
        ");
        $check->bind_param('ii', $data['asset_report_id'], $data['asset_id']);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0) {
            // Duplicate – skip or update? We'll skip.
            return true;
        }

        $stmt = $this->db->prepare("
            INSERT INTO asset_report_items (
                asset_report_id, asset_id, verification_status,
                asset_condition, verified_by, remarks
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'iissis',
            $data['asset_report_id'],
            $data['asset_id'],
            $data['verification_status'],
            $data['asset_condition'],
            $data['verified_by'],
            $data['remarks']
        );
        return $stmt->execute();
    }

    /**
     * Delete a report (if draft).
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM asset_report_items WHERE asset_report_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt = $this->db->prepare("DELETE FROM asset_reports WHERE asset_report_id = ? AND status = 'draft'");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
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
        $result = $this->db->query("SELECT asset_id, asset_code, description FROM assets WHERE status != 'inactive' ORDER BY asset_code");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get users for dropdown.
     * @return array
     */
    public function getUsers() {
        $result = $this->db->query("SELECT users_id, username FROM users WHERE is_active = 1 ORDER BY username");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Update report status.
     * @param int    $id
     * @param string $status
     * @return bool
     */
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE asset_reports SET status = ? WHERE asset_report_id = ?");
        $stmt->bind_param('si', $status, $id);
        return $stmt->execute();
    }

    /**
     * Get assets by category (for report generation).
     * @param int $categoryId
     * @return array
     */
    public function getAssetsByCategory($categoryId) {
        $sql = "
            SELECT a.asset_id, a.asset_code, a.description, a.brand, a.model, a.serial_number,
                a.acquisition_cost, a.acquisition_date, a.status, a.condition,
                aa.account_code, aa.account_name
            FROM assets a
            LEFT JOIN asset_accounts aa ON a.asset_accounts_id = aa.asset_accounts_id
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
     * Get assets by office (from custody).
     * @param int $officeId
     * @return array
     */
    public function getAssetsByOffice($officeId) {
        $sql = "
            SELECT a.asset_id, a.asset_code, a.description, a.brand, a.model,
                a.serial_number, a.acquisition_cost, a.acquisition_date,
                a.status, a.condition,
                p.full_name AS custodian, o.name AS office_name
            FROM assets a
            LEFT JOIN asset_custodies ac ON a.asset_id = ac.asset_id AND ac.status = 'active'
            LEFT JOIN personnel p ON ac.custodian_id = p.personnel_id
            LEFT JOIN offices o ON ac.office_id = o.office_id
            WHERE ac.office_id = ? AND a.status != 'inactive'
            ORDER BY a.asset_code
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $officeId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all categories for dropdown.
     * @return array
     */
    public function getCategories() {
        $result = $this->db->query("SELECT asset_category_id, name FROM asset_categories ORDER BY name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get unverified assets (from asset_report_items with status 'pending').
     * @return array
     */
    public function getUnverifiedAssets() {
        $sql = "
            SELECT 
                a.asset_code,
                a.description,
                ari.verification_status,
                ari.remarks
            FROM assets a
            INNER JOIN asset_report_items ari ON a.asset_id = ari.asset_id
            WHERE ari.verification_status = 'pending' AND a.status != 'inactive'
            ORDER BY a.asset_code
        ";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get transfer history.
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    public function getTransferHistory($dateFrom = null, $dateTo = null) {
        $sql = "
            SELECT 
                atr.transfer_number,
                a.asset_code,
                p1.full_name AS from_custodian,
                p2.full_name AS to_custodian,
                atr.transfer_date,
                atr.status
            FROM asset_transfers atr
            LEFT JOIN assets a ON atr.asset_id = a.asset_id
            LEFT JOIN personnel p1 ON atr.from_custodian_id = p1.personnel_id
            LEFT JOIN personnel p2 ON atr.to_custodian_id = p2.personnel_id
            WHERE 1=1
        ";
        $params = [];
        $types = '';
        if ($dateFrom) {
            $sql .= " AND atr.transfer_date >= ?";
            $params[] = $dateFrom;
            $types .= 's';
        }
        if ($dateTo) {
            $sql .= " AND atr.transfer_date <= ?";
            $params[] = $dateTo;
            $types .= 's';
        }
        $sql .= " ORDER BY atr.transfer_date DESC";
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}