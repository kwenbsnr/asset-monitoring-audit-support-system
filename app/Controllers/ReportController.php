<?php
namespace App\Controllers;

use App\Models\ReportModel;
use App\Models\AssetModel;
use App\Models\CustodyModel;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

// Conditional loading for PDF & Excel
$pdfLoaded = false;
$excelLoaded = false;
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    $pdfLoaded = class_exists('Dompdf\Dompdf');
    $excelLoaded = class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet');
}

class ReportController {
    /** @var ReportModel */
    private $reportModel;
    /** @var AssetModel */
    private $assetModel;
    /** @var CustodyModel */
    private $custodyModel;

    public function __construct() {
        // Only admin can access reports
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: index.php');
            exit;
        }
        $this->reportModel = new ReportModel();
        $this->assetModel = new AssetModel();
        $this->custodyModel = new CustodyModel();
    }

    /**
     * Main page: generate report form + list of saved reports.
     */
    public function index() {
        $accounts = $this->reportModel->getAccounts();
        $offices = $this->custodyModel->getOffices();
        $reportTypes = $this->getReportTypes();
        $savedReports = $this->reportModel->getAll();

        $pageTitle = 'Reports';
        $currentPage = 'reports';
        $viewFile = __DIR__ . '/../Views/reports/index.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    /**
     * Generate report – fetch data based on filters.
     */
    public function generate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=reports');
            exit;
        }

        $reportType = $_POST['report_type'] ?? '';
        $accountId = isset($_POST['account_id']) ? (int)$_POST['account_id'] : 0;
        $officeId = isset($_POST['office_id']) ? (int)$_POST['office_id'] : 0;
        $status = $_POST['status'] ?? '';
        $condition = $_POST['condition'] ?? '';
        $dateFrom = $_POST['date_from'] ?? '';
        $dateTo = $_POST['date_to'] ?? '';

        $data = [];
        $title = '';

        switch ($reportType) {
            case 'by_account':
                if (!$accountId) {
                    $_SESSION['flash'] = 'Please select an account.';
                    $_SESSION['flash_type'] = 'danger';
                    header('Location: index.php?page=reports');
                    exit;
                }
                $data = $this->reportModel->getAssetsByAccount($accountId);
                $title = 'Assets by Account';
                break;
            case 'by_office':
                if (!$officeId) {
                    $_SESSION['flash'] = 'Please select an office.';
                    $_SESSION['flash_type'] = 'danger';
                    header('Location: index.php?page=reports');
                    exit;
                }
                $data = $this->reportModel->getAssetsByOffice($officeId);
                $title = 'Assets by Office';
                break;
            case 'for_disposal':
                $data = $this->assetModel->searchAssets(null, ['status' => 'disposed']);
                $title = 'Assets for Disposal';
                break;
            case 'unverified':
                $data = $this->reportModel->getUnverifiedAssets();
                $title = 'Unverified Assets';
                break;
            case 'missing':
                $data = $this->assetModel->searchAssets(null, ['status' => 'missing']);
                $title = 'Missing Assets';
                break;
            case 'transfer_history':
                $data = $this->reportModel->getTransferHistory($dateFrom, $dateTo);
                $title = 'Transfer History';
                break;
            case 'custodian_assignment':
                $data = $this->custodyModel->getAll();
                $title = 'Custodian Assignment';
                break;
            case 'complete':
                $data = $this->assetModel->getAll();
                $title = 'Complete Asset List';
                break;
            default:
                $_SESSION['flash'] = 'Invalid report type.';
                $_SESSION['flash_type'] = 'danger';
                header('Location: index.php?page=reports');
                exit;
        }

        $format = $_POST['format'] ?? '';
        if ($format === 'pdf') {
            $this->exportPdf($data, $reportType, $title);
        } elseif ($format === 'excel') {
            $this->exportExcel($data, $reportType, $title);
        } elseif ($format === 'docx') {
            $_SESSION['report_data'] = $data;
            $_SESSION['report_type'] = $reportType;
            $_SESSION['report_title'] = $title;
            $this->exportDocx();
        } else {
            $_SESSION['report_data'] = $data;
            $_SESSION['report_type'] = $reportType;
            $_SESSION['report_title'] = $title;
            $_SESSION['flash'] = 'Report generated successfully. Click "Preview" to see it.';
            $_SESSION['flash_type'] = 'success';
            header('Location: index.php?page=reports');
            exit;
        }
    }

    /**
     * Preview the generated report (HTML).
     */
    public function preview() {
        $data = $_SESSION['report_data'] ?? [];
        $reportType = $_SESSION['report_type'] ?? 'complete';
        $title = $_SESSION['report_title'] ?? 'Report';

        if (empty($data)) {
            $_SESSION['flash'] = 'No report data. Generate a report first.';
            $_SESSION['flash_type'] = 'warning';
            header('Location: index.php?page=reports');
            exit;
        }

        echo $this->buildReportHtml($data, $reportType, $title);
        exit;
    }

    /**
     * Get report types for dropdown.
     * @return array
     */
    public function getReportTypes() {
        return [
            ['value' => 'complete', 'label' => 'Complete Asset List'],
            ['value' => 'by_account', 'label' => 'Assets by Account'],
            ['value' => 'by_office', 'label' => 'Assets by Office/Location'],
            ['value' => 'unverified', 'label' => 'Unverified Assets'],
            ['value' => 'missing', 'label' => 'Missing Assets'],
            ['value' => 'for_disposal', 'label' => 'Assets for Disposal'],
            ['value' => 'transfer_history', 'label' => 'Transfer History'],
            ['value' => 'custodian_assignment', 'label' => 'Custodian Assignment'],
        ];
    }

    /**
     * Build just the meta + table markup shared by every HTML rendering
     * of a report (standalone document for PDF, and the inline AJAX
     * preview fragment). Keeping this in one place means the two can
     * never drift out of sync on headers/rows again.
     * @param array  $data
     * @param string $reportType
     * @param string $metaClass   CSS class for the "Generated:" line
     * @param string $wrapClass   CSS class for the div wrapping <table>
     * @param string $tableClass  CSS class(es) for the <table> element
     * @param string $emptyClass  CSS class for the "no records" cell
     * @return string
     */
    private function buildReportTableFragment($data, $reportType, $metaClass, $wrapClass, $tableClass, $emptyClass) {
        $headers = $this->getReportHeaders($reportType);
        $html = '<p class="' . $metaClass . '"><strong>Generated:</strong> ' . date('Y-m-d H:i') . '</p>';
        $html .= '<div class="' . $wrapClass . '"><table class="' . $tableClass . '"><thead><tr>';
        foreach ($headers as $h) {
            $html .= '<th>' . htmlspecialchars($h) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        if (empty($data)) {
            $html .= '<tr><td colspan="' . count($headers) . '" class="' . $emptyClass . '">No records found.</td></tr>';
        } else {
            foreach ($data as $row) {
                $html .= '<tr>';
                foreach (array_keys($headers) as $key) {
                    $value = $row[$key] ?? '';
                    $html .= '<td>' . htmlspecialchars($value) . '</td>';
                }
                $html .= '</tr>';
            }
        }
        $html .= '</tbody></table></div>';
        return $html;
    }

    /**
     * Build a full standalone HTML document for PDF rendering (Dompdf
     * needs its own inline <style> — it does not load external
     * stylesheets), and for the plain preview()/print route.
     * @param array  $data
     * @param string $reportType
     * @param string $title
     * @return string
     */
    public function buildReportHtml($data, $reportType, $title) {
        $fragment = '<h2>' . htmlspecialchars($title) . '</h2>'
            . $this->buildReportTableFragment($data, $reportType, 'report-meta', 'report-table-wrap', 'report-table', 'report-empty');
        $html = '<!DOCTYPE html><html><head><title>' . htmlspecialchars($title) . '</title>';
        $html .= '<style>
            body { font-family: Arial, Helvetica, sans-serif; color: #1f2937; margin: 20px; }
            h2 { color: #15803d; margin-bottom: 4px; }
            .report-meta { color: #4b5563; font-size: 13px; margin-bottom: 16px; }
            table.report-table { width: 100%; border-collapse: collapse; font-size: 13px; }
            table.report-table th, table.report-table td { border: 1px solid #d1d5db; padding: 6px 10px; text-align: left; }
            table.report-table thead th { background-color: #f3f4f6; color: #374151; font-weight: 600; }
            table.report-table tbody tr:nth-child(even) { background-color: #f9fafb; }
            .report-empty { text-align: center; color: #6b7280; padding: 16px; }
        </style>';
        $html .= '</head><body>';
        $html .= $fragment;
        $html .= '</body></html>';
        return $html;
    }

    /**
     * Build the report fragment for the inline AJAX preview panel in
     * Views/reports/index.php. This is injected via .innerHTML, so it
     * must NOT be a full document (no <!DOCTYPE>/<html>/<head>/<style> —
     * browsers won't parse those correctly from an innerHTML assignment).
     * It reuses the app's own .table-app design-system classes instead,
     * which are already loaded globally via public/css/style.css, so the
     * preview matches every other table in the app automatically.
     * @param array  $data
     * @param string $reportType
     * @param string $title
     * @return string
     */
    public function buildReportPreviewHtml($data, $reportType, $title) {
        // $title is unused here — the preview panel already shows the
        // title in its own banner (see #previewTitle in reports/index.php);
        // repeating it inside the fragment would duplicate it.
        return $this->buildReportTableFragment($data, $reportType, 'report-preview-meta', 'table-app-wrap', 'table-app', 'table-empty');
    }

    /**
     * Get column headers for each report type.
     * @param string $reportType
     * @return array
     */
    public function getReportHeaders($reportType) {
        switch ($reportType) {
            case 'complete':
            case 'by_account':
                return [
                    'asset_code' => 'Asset Code',
                    'asset_name' => 'Asset Name',
                    'brand' => 'Brand',
                    'model' => 'Model',
                    'serial_number' => 'Serial #',
                    'acquisition_cost' => 'Cost',
                    'acquisition_date' => 'Acq. Date',
                    'status' => 'Status',
                    'condition' => 'Condition'
                ];
            case 'by_office':
                return [
                    'asset_code' => 'Asset Code',
                    'asset_name' => 'Asset Name',
                    'custodian' => 'Custodian',
                    'office_name' => 'Office',
                    'status' => 'Status'
                ];
            case 'unverified':
                return [
                    'asset_code' => 'Asset Code',
                    'asset_name' => 'Asset Name',
                    'verification_status' => 'Verification',
                    'remarks' => 'Remarks'
                ];
            case 'missing':
                return [
                    'asset_code' => 'Asset Code',
                    'asset_name' => 'Asset Name',
                    'status' => 'Status'
                ];
            case 'for_disposal':
                return [
                    'asset_code' => 'Asset Code',
                    'asset_name' => 'Asset Name',
                    'status' => 'Status',
                    'remarks' => 'Remarks'
                ];
            case 'transfer_history':
                return [
                    'transfer_number' => 'Transfer #',
                    'asset_code' => 'Asset Code',
                    'from_custodian' => 'From',
                    'to_custodian' => 'To',
                    'transfer_date' => 'Transfer Date',
                    'status' => 'Status'
                ];
            case 'custodian_assignment':
                return [
                    'asset_code' => 'Asset Code',
                    'asset_name' => 'Asset Name',
                    'custodian_name' => 'Custodian',
                    'office_name' => 'Office',
                    'effectivity_date' => 'Effectivity'
                ];
            default:
                return ['asset_code' => 'Asset Code', 'asset_name' => 'Asset Name', 'status' => 'Status'];
        }
    }

    /**
     * Export as PDF (using Dompdf if installed).
     * @param array  $data
     * @param string $reportType
     * @param string $title
     */
    public function exportPdf($data, $reportType, $title) {
        $html = $this->buildReportHtml($data, $reportType, $title);

        if (class_exists('Dompdf\Dompdf')) {
            $options = new \Dompdf\Options();
            $options->set('defaultFont', 'Courier');
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream('report.pdf', ['Attachment' => true]);
        } else {
            header('Content-Type: text/html');
            echo $html;
            echo '<p style="text-align:center"><strong>PDF library not installed.</strong> Please run: <code>composer require dompdf/dompdf</code></p>';
        }
        exit;
    }

    /**
     * Export as Excel (using PhpSpreadsheet if installed).
     * @param array  $data
     * @param string $reportType
     * @param string $title
     */
    public function exportExcel($data, $reportType, $title) {
        if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = $this->getReportHeaders($reportType);
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }

            $row = 2;
            foreach ($data as $item) {
                $col = 'A';
                foreach (array_keys($headers) as $key) {
                    $value = $item[$key] ?? '';
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }
                $row++;
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="report.xlsx"');
            $writer->save('php://output');
        } else {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="report.csv"');
            $output = fopen('php://output', 'w');
            $headers = $this->getReportHeaders($reportType);
            fputcsv($output, array_values($headers));
            foreach ($data as $row) {
                $line = [];
                foreach (array_keys($headers) as $key) {
                    $line[] = $row[$key] ?? '';
                }
                fputcsv($output, $line);
            }
            fclose($output);
        }
        exit;
    }

    /**
     * AJAX preview – returns report HTML for inline display.
     */
    public function previewAjax() {
        $reportType = $_POST['report_type'] ?? $_GET['report_type'] ?? '';
        $accountId = isset($_POST['account_id']) ? (int)$_POST['account_id'] : (isset($_GET['account_id']) ? (int)$_GET['account_id'] : 0);
        $officeId = isset($_POST['office_id']) ? (int)$_POST['office_id'] : (isset($_GET['office_id']) ? (int)$_GET['office_id'] : 0);
        $status = $_POST['status'] ?? $_GET['status'] ?? '';
        $condition = $_POST['condition'] ?? $_GET['condition'] ?? '';
        $dateFrom = $_POST['date_from'] ?? $_GET['date_from'] ?? '';
        $dateTo = $_POST['date_to'] ?? $_GET['date_to'] ?? '';

        $data = [];
        $title = '';
        switch ($reportType) {
            case 'by_account':
                if (!$accountId) { echo json_encode(['error' => 'Account required']); return; }
                $data = $this->reportModel->getAssetsByAccount($accountId);
                $title = 'Assets by Account';
                break;
            case 'by_office':
                if (!$officeId) { echo json_encode(['error' => 'Office required']); return; }
                $data = $this->reportModel->getAssetsByOffice($officeId);
                $title = 'Assets by Office';
                break;
            case 'for_disposal':
                $data = $this->assetModel->searchAssets(null, ['status' => 'disposed']);
                $title = 'Assets for Disposal';
                break;
            case 'unverified':
                $data = $this->reportModel->getUnverifiedAssets();
                $title = 'Unverified Assets';
                break;
            case 'missing':
                $data = $this->assetModel->searchAssets(null, ['status' => 'missing']);
                $title = 'Missing Assets';
                break;
            case 'transfer_history':
                $data = $this->reportModel->getTransferHistory($dateFrom, $dateTo);
                $title = 'Transfer History';
                break;
            case 'custodian_assignment':
                $data = $this->custodyModel->getAll();
                $title = 'Custodian Assignment';
                break;
            case 'complete':
            default:
                $data = $this->assetModel->getAll();
                $title = 'Complete Asset List';
                break;
        }

        $_SESSION['report_data'] = $data;
        $_SESSION['report_type'] = $reportType;
        $_SESSION['report_title'] = $title;

        $html = $this->buildReportPreviewHtml($data, $reportType, $title);
        echo json_encode(['html' => $html, 'title' => $title]);
        exit;
    }

    /**
     * Export as DOCX (using PhpWord).
     */
    public function exportDocx() {
        $data = $_SESSION['report_data'] ?? [];
        $reportType = $_SESSION['report_type'] ?? 'complete';
        $title = $_SESSION['report_title'] ?? 'Report';

        if (empty($data)) {
            $_SESSION['flash'] = 'No report data. Generate a preview first.';
            $_SESSION['flash_type'] = 'warning';
            header('Location: index.php?page=reports');
            exit;
        }

        if (!class_exists('PhpOffice\PhpWord\IOFactory')) {
            header('Content-Type: text/html');
            echo $this->buildReportHtml($data, $reportType, $title);
            echo '<p><strong>PhpWord not installed.</strong> Please run: composer require phpoffice/phpword</p>';
            exit;
        }

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        $section->addTitle($title, 1);
        $section->addText('Generated: ' . date('Y-m-d H:i'));

        $headers = $this->getReportHeaders($reportType);
        $table = $section->addTable(['borderSize' => 6, 'cellMargin' => 80]);
        $table->addRow();
        foreach ($headers as $header) {
            $table->addCell()->addText($header, ['bold' => true]);
        }
        foreach ($data as $row) {
            $table->addRow();
            foreach (array_keys($headers) as $key) {
                $value = $row[$key] ?? '';
                $table->addCell()->addText($value);
            }
        }

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="report.docx"');
        $writer->save('php://output');
        exit;
    }

    // ========== Existing methods (add, save, view, delete) ==========

    public function add() {
        $offices = $this->reportModel->getOffices();
        $users = $this->reportModel->getUsers();
        $assets = $this->reportModel->getAssets();
        $pageTitle = 'Create Report';
        $currentPage = 'reports';
        $viewFile = __DIR__ . '/../Views/reports/form.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=reports');
            exit;
        }

        $data = [
            'report_number' => trim($_POST['report_number']),
            'report_date' => $_POST['report_date'],
            'office_id' => (int)$_POST['office_id'],
            'prepared_by' => (int)$_POST['prepared_by'],
            'status' => $_POST['status'],
            'remarks' => trim($_POST['remarks'] ?? ''),
        ];

        $errors = [];
        if (empty($data['report_number'])) $errors[] = 'Report number is required.';
        if (empty($data['report_date'])) $errors[] = 'Report date is required.';
        if (empty($data['office_id'])) $errors[] = 'Office is required.';

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $data;
            header('Location: index.php?page=reports&sub=add');
            exit;
        }

        $reportId = $this->reportModel->create($data);
        if ($reportId) {
            if (isset($_POST['items']) && is_array($_POST['items'])) {
                foreach ($_POST['items'] as $item) {
                    if (!empty($item['asset_id'])) {
                        $itemData = [
                            'asset_report_id' => $reportId,
                            'asset_id' => (int)$item['asset_id'],
                            'verification_status' => $item['verification_status'] ?? 'pending',
                            'asset_condition' => $item['asset_condition'] ?? 'good',
                            'verified_by' => (int)$item['verified_by'] ?? 0,
                            'remarks' => trim($item['remarks'] ?? ''),
                        ];
                        $this->reportModel->addItem($itemData);
                    }
                }
            }
            $_SESSION['flash'] = 'Report created successfully.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash'] = 'Failed to create report.';
            $_SESSION['flash_type'] = 'danger';
        }
        header('Location: index.php?page=reports');
        exit;
    }

    public function view() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            header('Location: index.php?page=reports');
            exit;
        }
        $report = $this->reportModel->getById($id);
        if (!$report) {
            header('Location: index.php?page=reports');
            exit;
        }
        $pageTitle = 'View Report';
        $currentPage = 'reports';
        $viewFile = __DIR__ . '/../Views/reports/view.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function delete() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id) {
            $this->reportModel->delete($id);
            $_SESSION['flash'] = 'Report deleted.';
            $_SESSION['flash_type'] = 'warning';
        }
        header('Location: index.php?page=reports');
        exit;
    }
}