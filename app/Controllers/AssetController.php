<?php
namespace App\Controllers;
use App\Models\CustodyModel;
use App\Models\AssetModel;
use App\Models\EmployeeModel;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class AssetController {
    /** @var AssetModel */
    private $assetModel;

    public function __construct() {
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['asset_manager', 'inspection_officer', 'admin'])) {
            header('Location: index.php');
            exit;
        }
        $this->assetModel = new AssetModel();
    }

    // ===== Account list (top level) – all roles =====
    public function browse() {
        $accountId = isset($_GET['account_id']) ? (int)$_GET['account_id'] : null;
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;
        $filters = $this->getFiltersFromGet();

        if ($accountId) {
            $assets = $this->assetModel->getAssetsByAccountId($accountId, $search, $filters);
            $account = $this->assetModel->getAccountById($accountId);
            $pageTitle = 'Assets' . ($account ? ' - ' . $account['account_code'] : '');
            $currentPage = 'assets';
            $viewFile = __DIR__ . '/../Views/assets/list.php';
            require_once __DIR__ . '/../Views/layouts/main.php';
        } else {
            $accounts = $this->assetModel->getAssetAccountsList();
            $pageTitle = 'Asset Accounts';
            $currentPage = 'assets';
            $viewFile = __DIR__ . '/../Views/assets/accounts.php';
            require_once __DIR__ . '/../Views/layouts/main.php';
        }
    }

    public function listAll() {
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;
        $filters = $this->getFiltersFromGet();
        $assets = $this->assetModel->getAllAssets($search, $filters);
        $pageTitle = 'All Assets' . ($search ? ' (Search: ' . htmlspecialchars($search) . ')' : '');
        $currentPage = 'assets';
        $viewFile = __DIR__ . '/../Views/assets/list.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    private function getFiltersFromGet() {
        $filters = [];
        if (isset($_GET['field']) && !empty($_GET['field'])) $filters['field'] = $_GET['field'];
        if (isset($_GET['status']) && !empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (isset($_GET['condition']) && !empty($_GET['condition'])) $filters['condition'] = $_GET['condition'];
        if (isset($_GET['date_from']) && !empty($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
        if (isset($_GET['date_to']) && !empty($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];
        if (isset($_GET['cost_from']) && $_GET['cost_from'] !== '') $filters['cost_from'] = (float)$_GET['cost_from'];
        if (isset($_GET['cost_to']) && $_GET['cost_to'] !== '') $filters['cost_to'] = (float)$_GET['cost_to'];
        return $filters;
    }

    public function details() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $qr = isset($_GET['qr']) ? trim($_GET['qr']) : null;
        $query = isset($_GET['q']) ? trim($_GET['q']) : null;

        if ($id) {
            $data = $this->assetModel->getFullDetails($id);
        } elseif ($qr) {
            $asset = $this->assetModel->getByQrCode($qr);
            if ($asset) {
                $data = $this->assetModel->getFullDetails($asset['asset_id']);
            } else {
                $data = null;
            }
        } elseif ($query) {
            $asset = $this->assetModel->searchAssetByText($query);
            if ($asset) {
                $data = $this->assetModel->getFullDetails($asset['asset_id']);
            } else {
                $data = null;
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Asset ID, QR code, or search term required']);
            return;
        }
        if (!$data) {
            http_response_code(404);
            echo json_encode(['error' => 'Asset not found']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    // ===== Add / Edit / Save / Delete =====
    // Only asset_manager and admin can add assets
    public function add() {
        if (!in_array($_SESSION['role'], ['asset_manager', 'admin'])) {
            header('Location: index.php');
            exit;
        }
        $accounts = $this->assetModel->getAssetAccounts();
        $personnel = $this->assetModel->getPersonnel();
        $offices = $this->assetModel->getOffices();
        $statusOptions = ['active', 'inactive', 'disposed', 'missing'];
        $conditionOptions = ['good', 'fair', 'poor', 'damaged', 'obsolete'];
        $isEdit = false;

        // AJAX: return just the form fragment for the shared modal.
        if ($this->isAjaxRequest()) {
            require __DIR__ . '/../Views/assets/form.php';
            return;
        }

        // Non-AJAX fallback (direct link, JS disabled): full page.
        $pageTitle = 'Add Asset';
        $currentPage = 'add_asset';
        $viewFile = __DIR__ . '/../Views/assets/form.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    /**
     * Edit asset – accessible to asset_manager and admin.
     */
    public function edit() {
        if (!in_array($_SESSION['role'], ['asset_manager', 'admin'])) {
            header('Location: index.php');
            exit;
        }
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            header('Location: index.php?page=assets&sub=browse');
            exit;
        }
        $asset = $this->assetModel->getById($id);
        if (!$asset) {
            header('Location: index.php?page=assets&sub=browse');
            exit;
        }
        $custodyModel = new CustodyModel();
        $currentCustody = $custodyModel->getActiveCustody($id);
        $personnel = $this->assetModel->getPersonnel();
        $offices = $this->assetModel->getOffices();
        $accounts = $this->assetModel->getAssetAccounts();
        $statusOptions = ['active', 'inactive', 'disposed', 'missing'];
        $conditionOptions = ['good', 'fair', 'poor', 'damaged', 'obsolete'];
        $isEdit = true;

        if ($this->isAjaxRequest()) {
            require __DIR__ . '/../Views/assets/form.php';
            return;
        }

        $pageTitle = 'Edit Asset';
        $currentPage = 'assets';
        $viewFile = __DIR__ . '/../Views/assets/form.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function save() {
        if (!in_array($_SESSION['role'], ['asset_manager', 'admin'])) {
            header('Location: index.php');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=assets&sub=browse');
            exit;
        }
        $isAjax = $this->isAjaxRequest();

        $id = isset($_POST['asset_id']) ? (int)$_POST['asset_id'] : 0;
        $data = [
            'asset_code' => trim($_POST['asset_code']),
            'asset_name' => trim($_POST['asset_name']),
            'description' => trim($_POST['description'] ?? ''),
            'brand' => trim($_POST['brand'] ?? ''),
            'model' => trim($_POST['model'] ?? ''),
            'serial_number' => trim($_POST['serial_number'] ?? ''),
            'acquisition_cost' => $_POST['acquisition_cost'] ? (float)$_POST['acquisition_cost'] : null,
            'acquisition_date' => $_POST['acquisition_date'] ?: null,
            'asset_accounts_id' => (int)$_POST['asset_accounts_id'],
            'status' => $id ? $_POST['status'] : 'active',
            'condition' => $id ? $_POST['condition'] : 'good',
            'remarks' => trim($_POST['remarks'] ?? ''),
        ];

        $errors = [];
        if (empty($data['asset_code'])) $errors[] = 'Asset code is required.';
        if (empty($data['asset_name'])) $errors[] = 'Asset name is required.';
        if (empty($data['asset_accounts_id'])) $errors[] = 'Account is required.';
        
        // PPE cost validation
        if ($data['acquisition_cost'] === null || $data['acquisition_cost'] < 50000) {
            $errors[] = 'Acquisition cost must be at least ₱50,000.00 for PPE registration.';
        }

        // ===== Acquisition date validation =====
        // Reject malformed or nonsensical dates (e.g. a 3-digit/typo year like "004")
        // instead of silently passing them to MySQL as NULL or a garbage DATE value.
        if (!empty($_POST['acquisition_date'])) {
            $rawDate = trim($_POST['acquisition_date']);
            $d = \DateTime::createFromFormat('Y-m-d', $rawDate);
            $currentYear = (int)date('Y');
            if (!$d || $d->format('Y-m-d') !== $rawDate) {
                $errors[] = 'Acquisition date is not a valid date.';
            } elseif ((int)$d->format('Y') < 1990 || (int)$d->format('Y') > $currentYear) {
                $errors[] = 'Acquisition date year must be between 1990 and ' . $currentYear . '.';
            } elseif ($d > new \DateTime('today')) {
                $errors[] = 'Acquisition date cannot be in the future.';
            }
        }

        // ===== Salary Grade vs. asset value validation =====
        // If a custodian is being assigned/reassigned right here, the asset's
        // value must not exceed that custodian's Salary Grade threshold.
        if (isset($_POST['assign_custodian']) && $_POST['assign_custodian'] == '1') {
            $newCustodianId = (int)($_POST['custodian_id'] ?? 0);
            if ($newCustodianId && $data['acquisition_cost'] !== null) {
                $employeeModel = new EmployeeModel();
                $sgCheck = $employeeModel->validateAssetAssignment($newCustodianId, $data['acquisition_cost']);
                if ($sgCheck !== true) {
                    $errors[] = $sgCheck;
                }
            }
            if (empty(trim($_POST['property_number'] ?? ''))) {
                $errors[] = 'Property number is required.';
            }
        }

        if (!empty($errors)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => $errors]);
                return;
            }
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $data;
            header('Location: index.php?page=assets&sub=' . ($id ? 'edit&id=' . $id : 'add'));
            exit;
        }

        try {
            if ($id) {
                $success = $this->assetModel->update($id, $data);
            } else {
                $newId = $this->assetModel->create($data);
                if ($newId) {
                    $success = true;
                    $id = $newId;
                } else {
                    $success = false;
                }
            }
        } catch (\App\Models\DuplicateEntryException $e) {
            // A unique field (asset_code, qr_code_ref, or serial_number) collided
            // with an existing asset — show it the same way as a validation error.
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => [$e->getMessage()]]);
                return;
            }
            $_SESSION['form_errors'] = [$e->getMessage()];
            $_SESSION['form_data'] = $data;
            $_SESSION['flash'] = $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
            header('Location: index.php?page=assets&sub=' . ($id ? 'edit&id=' . $id : 'add'));
            exit;
        } catch (\mysqli_sql_exception $e) {
            // Any other unexpected database error — fail gracefully instead of a fatal error.
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => ['A database error occurred while saving the asset. Please try again.']]);
                return;
            }
            $_SESSION['form_data'] = $data;
            $_SESSION['flash'] = 'A database error occurred while saving the asset. Please try again.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: index.php?page=assets&sub=' . ($id ? 'edit&id=' . $id : 'add'));
            exit;
        }

        if (!$success) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => ['Failed to save asset. Please try again.']]);
                return;
            }
            $_SESSION['flash'] = 'Failed to save asset. Please try again.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: index.php?page=assets&sub=' . ($id ? 'edit&id=' . $id : 'add'));
            exit;
        }

        unset($_SESSION['form_errors'], $_SESSION['form_data']);
        $_SESSION['flash'] = 'Asset saved successfully.';
        $_SESSION['flash_type'] = 'success';

        // Handle custody assignment and transfer logging
        if (isset($_POST['assign_custodian']) && $_POST['assign_custodian'] == '1' && $id) {
            $custodyModel = new CustodyModel();
            $existing = $custodyModel->getActiveCustody($id);

            $newCustodianId = (int)$_POST['custodian_id'];
            $newOfficeId = (int)$_POST['office_id'];
            $effectivityDate = $_POST['effectivity_date'] ?? date('Y-m-d');
            $propertyNumber = trim($_POST['property_number'] ?? '');

            // If there is an existing active custody, end it and log transfer
            if ($existing) {
                // Log transfer before ending
                $this->assetModel->logTransfer(
                    $id,
                    $existing['custodian_id'],
                    $newCustodianId,
                    $existing['office_id'],
                    $newOfficeId,
                    $effectivityDate,
                    'approved'
                );

                // End old custody
                $custodyModel->update($existing['asset_custodies_id'], [
                    'custodian_id' => $existing['custodian_id'],
                    'office_id' => $existing['office_id'],
                    'property_number' => $existing['property_number'],
                    'effectivity_date' => $existing['effectivity_date'],
                    'end_date' => date('Y-m-d'),
                    'status' => 'inactive'
                ]);
            } else {
                // No previous custody – this is an initial assignment.
                // Optionally log a transfer with from_custodian_id = NULL, from_office_id = NULL
                $this->assetModel->logTransfer(
                    $id,
                    null,
                    $newCustodianId,
                    null,
                    $newOfficeId,
                    $effectivityDate,
                    'approved'
                );
            }

            // Create new custody
            $custodyData = [
                'asset_id' => $id,
                'custodian_id' => $newCustodianId,
                'office_id' => $newOfficeId,
                'property_number' => $propertyNumber,
                'effectivity_date' => $effectivityDate,
                'status' => 'active'
            ];
            $custodyModel->create($custodyData);
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Asset saved successfully.']);
            return;
        }

        if ($id) {
            header('Location: index.php?page=assets&sub=edit&id=' . $id);
        } else {
            header('Location: index.php?page=assets&sub=browse');
        }
        exit;
    }

    public function delete() {
        if ($_SESSION['role'] !== 'admin') {
            header('Location: index.php');
            exit;
        }
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id) {
            $this->assetModel->delete($id);
            $_SESSION['flash'] = 'Asset deleted (soft delete).';
            $_SESSION['flash_type'] = 'warning';
        }
        header('Location: index.php?page=assets&sub=browse');
        exit;
    }

    public function searchJson() {
        $query = isset($_GET['q']) ? trim($_GET['q']) : '';
        if (strlen($query) < 2) {
            http_response_code(400);
            echo json_encode(['error' => 'Please type at least 2 characters']);
            return;
        }
        $assets = $this->assetModel->searchAssets($query);
        header('Content-Type: application/json');
        echo json_encode($assets);
    }

    public function qr() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            http_response_code(400);
            die('Asset ID required');
        }
        $asset = $this->assetModel->getById($id);
        if (!$asset) {
            http_response_code(404);
            die('Asset not found');
        }
        $content = $asset['qr_code_ref'];
        $download = isset($_GET['download']);
        \App\Helpers\QRGenerator::output($content, $download);
    }

    /**
     * Scan Asset – QR scan + manual search + inline verify/update.
     * Available to asset_manager and admin (not inspection_officer,
     * which now uses the dynamic worklist in verify() instead).
     * GET: shows scanner/search and asset details.
     * POST: updates operational fields (shared with verify()).
     */
    public function scan() {
        if (!in_array($_SESSION['role'], ['asset_manager', 'admin'])) {
            header('Location: index.php');
            exit;
        }

        $assetId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $qr = isset($_GET['qr']) ? trim($_GET['qr']) : null;
        $asset = null;
        $personnel = $this->assetModel->getPersonnel();
        $offices = $this->assetModel->getOffices();

        if ($assetId) {
            $asset = $this->assetModel->getById($assetId);
        } elseif ($qr) {
            $asset = $this->assetModel->getByQrCode($qr);
        }

        if ($asset) {
            $custodyModel = new CustodyModel();
            $activeCustody = $custodyModel->getActiveCustody($asset['asset_id']);
            $asset['custodian_id'] = $activeCustody['custodian_id'] ?? 0;
            $asset['office_id'] = $activeCustody['office_id'] ?? 0;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->saveInspection();
            if ($this->isAjaxRequest()) {
                $this->respondInspectionJson();
                return;
            }
            if (isset($_POST['asset_id'])) {
                header('Location: index.php?page=assets&sub=scan&id=' . (int)$_POST['asset_id']);
            } else {
                header('Location: index.php?page=assets&sub=scan');
            }
            exit;
        }

        $pageTitle = 'Scan Asset';
        $currentPage = 'scan';
        $viewFile = __DIR__ . '/../Views/assets/scan.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    /**
     * Dispose an asset (mark as disposed) – only for inspection_officer and admin.
     */
    public function dispose() {
        // Only inspection_officer and admin can dispose
        if (!in_array($_SESSION['role'], ['inspection_officer', 'admin'])) {
            header('Location: index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $assetId = isset($_POST['asset_id']) ? (int)$_POST['asset_id'] : 0;
            $reason = trim($_POST['disposal_reason'] ?? '');

            if (!$assetId) {
                $_SESSION['flash'] = 'Invalid asset.';
                $_SESSION['flash_type'] = 'danger';
                header('Location: index.php?page=assets&sub=browse');
                exit;
            }

            if (empty($reason)) {
                $_SESSION['flash'] = 'Please provide a reason for disposal.';
                $_SESSION['flash_type'] = 'danger';
                header('Location: index.php?page=assets&sub=browse');
                exit;
            }

            $asset = $this->assetModel->getById($assetId);
            if (!$asset) {
                $_SESSION['flash'] = 'Asset not found.';
                $_SESSION['flash_type'] = 'danger';
                header('Location: index.php?page=assets&sub=browse');
                exit;
            }

            if ($asset['status'] !== 'active') {
                $_SESSION['flash'] = 'Only active assets can be disposed.';
                $_SESSION['flash_type'] = 'danger';
                header('Location: index.php?page=assets&sub=browse');
                exit;
            }

            // Update asset status and reason
            $updated = $this->assetModel->disposeAsset($assetId, $reason, $_SESSION['user_id']);
            if ($updated) {
                $_SESSION['flash'] = 'Asset marked as disposed successfully.';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash'] = 'Failed to dispose asset.';
                $_SESSION['flash_type'] = 'danger';
            }
            header('Location: index.php?page=assets&sub=browse');
            exit;
        }

        // GET request – redirect to browse (the modal handles the action)
        header('Location: index.php?page=assets&sub=browse');
        exit;
    }

    /**
     * View assets by office – shows office cards, then custodians, then assets.
     */
    public function byOffice() {
        if (!in_array($_SESSION['role'], ['asset_manager', 'admin'])) {
            header('Location: index.php');
            exit;
        }

        $officeId = isset($_GET['office_id']) ? (int)$_GET['office_id'] : null;
        $custodianId = isset($_GET['custodian_id']) ? (int)$_GET['custodian_id'] : null;

        if ($custodianId) {
            // Show assets for a specific custodian (popover or modal)
            $assets = $this->assetModel->getAssetsByCustodianForAssetManager($custodianId);
            $custodian = $this->assetModel->getPersonnelById($custodianId);
            $pageTitle = 'Assets of ' . ($custodian ? $custodian['full_name'] : '');
            $currentPage = 'assets_by_office';
            $viewFile = __DIR__ . '/../Views/assets/custodian_assets.php';
            require_once __DIR__ . '/../Views/layouts/main.php';
        } elseif ($officeId) {
            // Show custodians in this office, 20 per page
            $page = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;
            $limit = 20;
            $offset = ($page - 1) * $limit;
            $totalCustodians = $this->assetModel->countCustodiansByOfficeForAssetManager($officeId);
            $totalPages = (int)ceil($totalCustodians / $limit);
            $custodians = $this->assetModel->getCustodiansByOfficeForAssetManager($officeId, $limit, $offset);
            $office = $this->assetModel->getOfficeById($officeId);
            $pageTitle = 'Custodians - ' . ($office ? $office['name'] : '');
            $currentPage = 'assets_by_office';
            $viewFile = __DIR__ . '/../Views/assets/office_custodians.php';
            require_once __DIR__ . '/../Views/layouts/main.php';
        } else {
            // Show office cards
            $offices = $this->assetModel->getOfficesWithData();
            $pageTitle = 'Assets by Office';
            $currentPage = 'assets_by_office';
            $viewFile = __DIR__ . '/../Views/assets/offices.php';
            require_once __DIR__ . '/../Views/layouts/main.php';
        }
    }

    /**
     * Return assets for a custodian as JSON (used by the "View Assets" modal
     * in Views/assets/office_custodians.php).
     */
    public function custodianAssetsJson() {
        if (!in_array($_SESSION['role'], ['asset_manager', 'admin'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        $custodianId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$custodianId) {
            http_response_code(400);
            echo json_encode(['error' => 'Custodian ID required']);
            return;
        }
        $assets = $this->assetModel->getAssetsByCustodianForAssetManager($custodianId);
        header('Content-Type: application/json');
        echo json_encode($assets);
    }

    /**
     * Bulk print QR codes for selected assets.
     */
    public function bulkQr() {
        if (!in_array($_SESSION['role'], ['asset_manager', 'admin'])) {
            header('Location: index.php');
            exit;
        }

        // Get asset IDs from POST (checkbox selection)
        $assetIds = isset($_POST['asset_ids']) ? $_POST['asset_ids'] : [];
        if (empty($assetIds)) {
            $_SESSION['flash'] = 'No assets selected for QR printing.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: index.php?page=assets&sub=browse');
            exit;
        }

        // Fetch assets
        $assets = $this->assetModel->getAssetsByIds($assetIds);
        if (empty($assets)) {
            $_SESSION['flash'] = 'No valid assets found.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: index.php?page=assets&sub=browse');
            exit;
        }

        $pageTitle = 'Bulk QR Codes';
        $currentPage = 'assets';
        $viewFile = __DIR__ . '/../Views/assets/bulk_qr.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    /**
     * Verify Asset – dynamic worklist for the Inspection Officer.
     * GET: renders the filter bar / custodian-grouped worklist shell;
     *      the rows themselves are loaded via verifyWorklistJson().
     * POST: updates operational fields for one asset (from the row modal).
     */
    public function verify() {
        // Only inspection_officer can access. (asset_manager/admin now use
        // the Scan Asset page instead – see scan().)
        if (!in_array($_SESSION['role'], ['inspection_officer'])) {
            header('Location: index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->saveInspection();
            if ($this->isAjaxRequest()) {
                $this->respondInspectionJson();
                return;
            }
            header('Location: index.php?page=assets&sub=verify');
            exit;
        }

        $personnel = $this->assetModel->getPersonnel();
        $offices = $this->assetModel->getOffices();
        $accounts = $this->assetModel->getAssetAccounts();
        $pageTitle = 'Verify Asset';
        $currentPage = 'verify';
        $viewFile = __DIR__ . '/../Views/assets/verify.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    /**
     * AJAX: filtered + paginated worklist rows for the Verify Asset page.
     * GET params: search, account_id, custodian, office_id, page.
     */
    public function verifyWorklistJson() {
        if (!in_array($_SESSION['role'], ['inspection_officer'])) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $search = isset($_GET['search']) ? trim($_GET['search']) : null;
        $filters = [];
        if (!empty($_GET['account_id'])) $filters['account_id'] = (int)$_GET['account_id'];
        if (!empty($_GET['custodian'])) $filters['custodian'] = trim($_GET['custodian']);
        if (!empty($_GET['office_id'])) $filters['office_id'] = (int)$_GET['office_id'];

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $pageSize = 20;
        $offset = ($page - 1) * $pageSize;

        $total = $this->assetModel->countVerificationWorklist($search, $filters);
        $rows = $this->assetModel->getVerificationWorklist($search, $filters, $pageSize, $offset);

        header('Content-Type: application/json');
        echo json_encode([
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ]);
    }

    /**
     * True if the current request was sent via fetch()/XHR (not a plain form submit).
     * @return bool
     */
    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Echo the result of saveInspection() as JSON (used by the Verify Asset
     * worklist modal and, optionally, the Scan Asset page) instead of the
     * classic flash + redirect flow.
     */
    private function respondInspectionJson() {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => empty($_SESSION['flash_type']) || $_SESSION['flash_type'] !== 'danger',
            'message' => $_SESSION['flash'] ?? '',
        ]);
        unset($_SESSION['flash'], $_SESSION['flash_type']);
    }

    /**
     * Save inspection updates (POST handler).
     */
    private function saveInspection() {
        // inspection_officer (worklist modal) and asset_manager/admin (Scan Asset page)
        if (!in_array($_SESSION['role'], ['inspection_officer', 'asset_manager', 'admin'])) {
            http_response_code(403);
            exit('Unauthorized');
        }

        $assetId = isset($_POST['asset_id']) ? (int)$_POST['asset_id'] : 0;
        if (!$assetId) {
            $_SESSION['flash'] = 'Invalid asset.';
            $_SESSION['flash_type'] = 'danger';
            return;
        }

        // Allowed fields for inspection officer
        $data = [
            'condition' => $_POST['condition'] ?? 'good',
            'status' => $_POST['status'] ?? 'active',
            'verification_status' => $_POST['verification_status'] ?? 'pending',
            'inspection_remarks' => trim($_POST['inspection_remarks'] ?? ''),
            'custodian_id' => isset($_POST['custodian_id']) ? (int)$_POST['custodian_id'] : 0,
            'office_id' => isset($_POST['office_id']) ? (int)$_POST['office_id'] : 0,
        ];

        // If "Mark as Verified" button was clicked, force verification_status = 'verified'
        if (isset($_POST['mark_verified'])) {
            $data['verification_status'] = 'verified';
        }

        // Validate custodian/office if changed
        if ($data['custodian_id'] > 0 && $data['office_id'] == 0) {
            $_SESSION['flash'] = 'Office is required when changing custodian.';
            $_SESSION['flash_type'] = 'danger';
            return;
        }

        // ===== Salary Grade vs. asset value validation =====
        if ($data['custodian_id'] > 0) {
            $assetForCheck = $this->assetModel->getById($assetId);
            if ($assetForCheck) {
                $employeeModel = new EmployeeModel();
                $sgCheck = $employeeModel->validateAssetAssignment($data['custodian_id'], $assetForCheck['acquisition_cost']);
                if ($sgCheck !== true) {
                    $_SESSION['flash'] = $sgCheck;
                    $_SESSION['flash_type'] = 'danger';
                    return;
                }
            }
        }

        // Update asset operational fields (condition, status, verification_status, inspection_remarks)
        $updated = $this->assetModel->updateInspection($assetId, $data, $_SESSION['user_id']);
        if (!$updated) {
            $_SESSION['flash'] = 'Failed to update asset.';
            $_SESSION['flash_type'] = 'danger';
            return;
        }

        // Handle custodian change
        if ($data['custodian_id'] > 0) {
            $custodyModel = new CustodyModel();
            $existing = $custodyModel->getActiveCustody($assetId);
            if ($existing && $existing['custodian_id'] != $data['custodian_id']) {
                // End old custody and log transfer
                $this->assetModel->logTransfer(
                    $assetId,
                    $existing['custodian_id'],
                    $data['custodian_id'],
                    $existing['office_id'],
                    $data['office_id'],
                    date('Y-m-d'),
                    'approved'
                );
                $custodyModel->update($existing['asset_custodies_id'], [
                    'custodian_id' => $existing['custodian_id'],
                    'office_id' => $existing['office_id'],
                    'property_number' => $existing['property_number'],
                    'effectivity_date' => $existing['effectivity_date'],
                    'end_date' => date('Y-m-d'),
                    'status' => 'inactive'
                ]);
                // Create new custody
                $newCustody = [
                    'asset_id' => $assetId,
                    'custodian_id' => $data['custodian_id'],
                    'office_id' => $data['office_id'],
                    'property_number' => 'TRANSFER-' . date('Ymd'),
                    'effectivity_date' => date('Y-m-d'),
                    'status' => 'active'
                ];
                $custodyModel->create($newCustody);
            } elseif (!$existing) {
                // No existing custody – create one
                $newCustody = [
                    'asset_id' => $assetId,
                    'custodian_id' => $data['custodian_id'],
                    'office_id' => $data['office_id'],
                    'property_number' => 'ASSIGN-' . date('Ymd'),
                    'effectivity_date' => date('Y-m-d'),
                    'status' => 'active'
                ];
                $custodyModel->create($newCustody);
            }
        }

        // Log audit (using public logAudit method)
        $this->assetModel->logAudit($assetId, $_SESSION['user_id'], 'VERIFY', 'ASSET', '', '');

        $_SESSION['flash'] = 'Asset verification updated successfully.';
        $_SESSION['flash_type'] = 'success';
    }
}