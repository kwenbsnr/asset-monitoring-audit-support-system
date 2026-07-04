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
     * Create a new report.
     * @param array $data
     * @return int|false
     */
    public function create($data) {
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
     * Add an item to a report.
     * @param array $data
     * @return bool
     */
    public function addItem($data) {
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
}