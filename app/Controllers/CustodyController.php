<?php
namespace App\Controllers;

use App\Models\CustodyModel;
use App\Models\EmployeeModel;
use App\Models\AssetModel;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class CustodyController {
    /** @var CustodyModel */
    private $custodyModel;

    public function __construct() {
        // Allow asset_manager and admin to access custody (view, add, edit)
        // Delete is restricted to admin in the delete method itself.
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['asset_manager', 'admin'])) {
            header('Location: index.php');
            exit;
        }
        $this->custodyModel = new CustodyModel();
    }

    public function index() {
        $offices = $this->custodyModel->getOfficesWithCustody();
        $pageTitle = 'Custodial Tracking - Offices';
        $currentPage = 'custody';
        $viewFile = __DIR__ . '/../Views/custody/offices.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function office() {
        $officeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$officeId) {
            header('Location: index.php?page=custody');
            exit;
        }
        $custodians = $this->custodyModel->getCustodiansByOffice($officeId);
        $office = $this->custodyModel->getOfficeById($officeId);
        $pageTitle = 'Custodians - ' . ($office ? $office['name'] : '');
        $currentPage = 'custody';
        $viewFile = __DIR__ . '/../Views/custody/custodians.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function custodian() {
        $custodianId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$custodianId) {
            header('Location: index.php?page=custody');
            exit;
        }
        $page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;
        $assets = $this->custodyModel->getAssetsByCustodian($custodianId, $limit, $offset);
        $total = $this->custodyModel->countAssetsByCustodian($custodianId);
        $totalPages = ceil($total / $limit);
        $custodian = $this->custodyModel->getPersonnelById($custodianId);
        $pageTitle = 'Assets under ' . ($custodian ? $custodian['full_name'] : '');
        $currentPage = 'custody';
        $viewFile = __DIR__ . '/../Views/custody/assets.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function searchCustodians() {
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        if (strlen($search) < 2) {
            header('Location: index.php?page=custody');
            exit;
        }
        $custodians = $this->custodyModel->searchCustodians($search);
        $pageTitle = 'Search Results';
        $currentPage = 'custody';
        $viewFile = __DIR__ . '/../Views/custody/search.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    // ===== Add / Edit / Save (allowed for asset_manager and admin) =====

    /**
     * New Assign/Transfer action. Loads:
     *  - the asset list (each row already carries its current active
     *    custody, if any — see CustodyModel::getAssets())
     *  - the top-level internal office(s) — step 1 of the internal
     *    Office -> Department -> Custodian cascade
     *  - the external sub-offices valid as transfer destinations, each
     *    carrying its Division Manager/Head
     * The Department list for the (usually single) top-level office is
     * pre-loaded here too, so the internal cascade is usable immediately
     * without waiting on a first AJAX round-trip.
     */
    public function add() {
        $assetId = isset($_GET['asset_id']) ? (int)$_GET['asset_id'] : 0;
        $assets = $this->custodyModel->getAssets();
        $topOffices = $this->custodyModel->getTopLevelOffices();
        $externalOffices = $this->custodyModel->getExternalOffices();
        $isEdit = false;
        $preSelectedAsset = $assetId ? $assetId : 0;
        $recordMode = 'internal';

        $selectedTopOffice = !empty($topOffices) ? $topOffices[0]['office_id'] : 0;
        $selectedDepartment = 0;
        $departments = $selectedTopOffice ? $this->custodyModel->getDepartmentsByOffice($selectedTopOffice) : [];
        $custodians = [];

        if ($this->isAjaxRequest()) {
            require __DIR__ . '/../Views/custody/form.php';
            return;
        }

        $pageTitle = 'Assign / Transfer Custody';
        $currentPage = 'custody';
        $viewFile = __DIR__ . '/../Views/custody/form.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $assetId = isset($_GET['asset_id']) ? (int)$_GET['asset_id'] : 0;

        // If asset_id is provided but no custody_id, find the active custody
        if ($assetId && !$id) {
            $activeCustody = $this->custodyModel->getActiveCustody($assetId);
            if ($activeCustody) {
                header('Location: index.php?page=custody&sub=edit&id=' . $activeCustody['asset_custodies_id']);
                exit;
            } else {
                // No active custody – redirect to add
                header('Location: index.php?page=custody&sub=add&asset_id=' . $assetId);
                exit;
            }
        }

        if (!$id) {
            header('Location: index.php?page=custody');
            exit;
        }
        $record = $this->custodyModel->getById($id);
        if (!$record) {
            header('Location: index.php?page=custody');
            exit;
        }

        $assets = $this->custodyModel->getAssets();
        $topOffices = $this->custodyModel->getTopLevelOffices();
        $externalOffices = $this->custodyModel->getExternalOffices();
        $isEdit = true;
        $preSelectedAsset = 0;

        // Resolve which side of the Internal/External toggle this record
        // is currently on, and pre-load its cascade, so the edit form
        // opens already positioned on the correct step instead of blank.
        $currentOffice = $this->custodyModel->getOfficeById($record['office_id']);
        $recordMode = ($currentOffice && $currentOffice['office_type'] === 'external') ? 'external' : 'internal';

        $departments = [];
        $custodians = [];
        $selectedTopOffice = 0;
        $selectedDepartment = 0;

        if ($recordMode === 'internal' && $currentOffice) {
            $selectedTopOffice = $currentOffice['parent_office_id'] ? (int)$currentOffice['parent_office_id'] : (int)$currentOffice['office_id'];
            $selectedDepartment = (int)$currentOffice['office_id'];
            $departments = $this->custodyModel->getDepartmentsByOffice($selectedTopOffice);
            $custodians = $this->custodyModel->getPersonnelByDepartment($selectedDepartment);
        } elseif (!empty($topOffices)) {
            $selectedTopOffice = $topOffices[0]['office_id'];
            $departments = $this->custodyModel->getDepartmentsByOffice($selectedTopOffice);
        }

        if ($this->isAjaxRequest()) {
            require __DIR__ . '/../Views/custody/form.php';
            return;
        }

        $pageTitle = 'Edit Custody';
        $currentPage = 'custody';
        $viewFile = __DIR__ . '/../Views/custody/form.php';
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=custody');
            exit;
        }
        $isAjax = $this->isAjaxRequest();

        $id = isset($_POST['custody_id']) ? (int)$_POST['custody_id'] : 0;
        $assetId = (int)($_POST['asset_id'] ?? 0);
        $mode = (($_POST['assignment_mode'] ?? 'internal') === 'external') ? 'external' : 'internal';

        $errors = [];

        // Previous active custodian of THIS asset, if any — the deciding
        // factor for Assign vs. Transfer on the internal path (and
        // irrelevant to the external path, which is always "Transfer").
        // When editing a specific record directly, don't compare it
        // against itself.
        $existing = $assetId ? $this->custodyModel->getActiveCustody($assetId) : null;
        if ($existing && $id && (int)$existing['asset_custodies_id'] === $id) {
            $existing = null;
        }

        $custodianId = 0;
        $officeId = 0;

        if ($mode === 'external') {
            // ===== B. External — moving to another sub-office =====
            // Always "Transfer", regardless of whether a previous
            // custodian existed for this asset. The accountable officer
            // is always that sub-office's Division Manager/Head,
            // resolved server-side — never a manually picked custodian,
            // so a tampered/stale client value can't assign the asset to
            // the wrong person.
            $officeId = (int)($_POST['destination_office_id'] ?? 0);
            $office = $officeId ? $this->custodyModel->getOfficeById($officeId) : null;

            if (!$office || $office['office_type'] !== 'external') {
                $errors[] = 'Select a valid destination sub-office.';
            } elseif (empty($office['head_personnel_id'])) {
                $errors[] = 'This sub-office has no Division Manager/Head on file. Add one before transferring to it.';
            } else {
                $custodianId = (int)$office['head_personnel_id'];
            }
            $actionType = 'transfer';
        } else {
            // ===== A. Internal — within the same office =====
            // Office -> Department -> Custodian. The Department the user
            // picked IS the office_id actually stored against the
            // custody record; the top-level "Office" step only narrows
            // which Department list was offered.
            $officeId = (int)($_POST['department_id'] ?? 0);
            $custodianId = (int)($_POST['custodian_id'] ?? 0);
            $office = $officeId ? $this->custodyModel->getOfficeById($officeId) : null;

            if (!$office || $office['office_type'] !== 'internal') {
                $errors[] = 'Select a valid Department.';
            }
            $actionType = $existing ? 'transfer' : 'assign';
        }

        $data = [
            'asset_id' => $assetId,
            'custodian_id' => $custodianId,
            'office_id' => $officeId,
            'property_number' => trim($_POST['property_number'] ?? ''),
            'effectivity_date' => $_POST['effectivity_date'] ?? '',
            'status' => $_POST['status'] ?? 'active',
        ];

        if ($id) {
            $data['end_date'] = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        }

        if (empty($data['asset_id'])) $errors[] = 'Asset is required.';
        if (empty($data['custodian_id'])) $errors[] = 'Custodian could not be determined.';
        if (empty($data['office_id'])) $errors[] = 'Office/Department is required.';
        if (empty($data['effectivity_date'])) $errors[] = 'Effectivity date is required.';
        if ($data['property_number'] === '') $errors[] = 'Property number is required.';

        // ===== Salary Grade vs. asset value validation =====
        // Only meaningful for an internal assignment/reassignment.
        // Skipped entirely for external transfers (see above) — the
        // Head is assigned regardless of Salary Grade per business rule.
        if (empty($errors) && $mode === 'internal' && $data['status'] === 'active' && $data['asset_id'] && $data['custodian_id']) {
            $asset = $this->custodyModel->getAssetById($data['asset_id']);
            if ($asset) {
                $employeeModel = new EmployeeModel();
                $sgCheck = $employeeModel->validateAssetAssignment($data['custodian_id'], $asset['acquisition_cost']);
                if ($sgCheck !== true) {
                    $errors[] = $sgCheck;
                }
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
            header('Location: index.php?page=custody&sub=' . ($id ? 'edit&id=' . $id : 'add'));
            exit;
        }

        if ($id) {
            // Direct correction of an existing record (e.g. fixing a
            // typo'd property number) — no transfer bookkeeping, no
            // closing-out of a "previous" custody.
            $success = $this->custodyModel->update($id, $data);
        } else {
            // New Assign/Transfer action. An asset can't have two active
            // custodians at once, so if it already has one, that custody
            // must be closed out and the move logged as a transfer
            // before the new custody begins.
            if ($existing) {
                $this->custodyModel->update($existing['asset_custodies_id'], [
                    'custodian_id' => $existing['custodian_id'],
                    'office_id' => $existing['office_id'],
                    'property_number' => $existing['property_number'],
                    'effectivity_date' => $existing['effectivity_date'],
                    'end_date' => date('Y-m-d'),
                    'status' => 'inactive'
                ]);
            }

            $assetModel = new AssetModel();
            $assetModel->logTransfer(
                $data['asset_id'],
                $existing['custodian_id'] ?? null,
                $data['custodian_id'],
                $existing['office_id'] ?? null,
                $data['office_id'],
                $data['effectivity_date'],
                'approved'
            );

            $success = $this->custodyModel->create($data);
        }

        if (!$success) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'errors' => ['Failed to save custody record.']]);
                return;
            }
            $_SESSION['flash'] = 'Failed to save custody record.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: index.php?page=custody&sub=' . ($id ? 'edit&id=' . $id : 'add'));
            exit;
        }

        unset($_SESSION['form_errors'], $_SESSION['form_data']);
        $actionLabel = $id ? 'updated' : ($actionType === 'assign' ? 'assigned' : 'transferred');
        $_SESSION['flash'] = 'Custody record ' . $actionLabel . ' successfully.';
        $_SESSION['flash_type'] = 'success';

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Custody record ' . $actionLabel . ' successfully.',
                'action_type' => $id ? 'update' : $actionType,
            ]);
            return;
        }
        header('Location: index.php?page=custody');
        exit;
    }

    /**
     * AJAX: Departments (child offices) under a selected top-level
     * Office — step 2 of the internal Office -> Department -> Custodian
     * cascade.
     */
    public function departmentsJson() {
        $officeId = isset($_GET['office_id']) ? (int)$_GET['office_id'] : 0;
        header('Content-Type: application/json');
        if (!$officeId) {
            echo json_encode([]);
            return;
        }
        echo json_encode($this->custodyModel->getDepartmentsByOffice($officeId));
    }

    /**
     * AJAX: Custodians filtered/narrowed by the selected Department —
     * step 3 of the internal cascade. Never returns the unfiltered
     * personnel roster.
     */
    public function custodiansJson() {
        $departmentId = isset($_GET['department_id']) ? (int)$_GET['department_id'] : 0;
        header('Content-Type: application/json');
        if (!$departmentId) {
            echo json_encode([]);
            return;
        }
        echo json_encode($this->custodyModel->getPersonnelByDepartment($departmentId));
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
     * AJAX: full (non-paginated) asset list for one custodian, used by the
     * "View Assets" modal on the Custodians-by-office page.
     */
    public function custodianAssetsJson() {
        $custodianId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$custodianId) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => 'Missing custodian id.']);
            return;
        }
        $assets = $this->custodyModel->getAssetsByCustodian($custodianId, 1000, 0);
        header('Content-Type: application/json');
        echo json_encode($assets);
    }

    /**
     * Delete (end custody) – only admin.
     */
    public function delete() {
        if ($_SESSION['role'] !== 'admin') {
            header('Location: index.php');
            exit;
        }
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id) {
            $record = $this->custodyModel->getById($id);
            if ($record) {
                $data = [
                    'custodian_id' => $record['custodian_id'],
                    'office_id' => $record['office_id'],
                    'property_number' => $record['property_number'],
                    'effectivity_date' => $record['effectivity_date'],
                    'end_date' => date('Y-m-d'),
                    'status' => 'inactive'
                ];
                $this->custodyModel->update($id, $data);
                $_SESSION['flash'] = 'Custody record ended.';
                $_SESSION['flash_type'] = 'warning';
            }
        }
        header('Location: index.php?page=custody');
        exit;
    }
}