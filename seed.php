<?php
/**
 * Complete Seed Script – Populates all tables with realistic sample data.
 * Run this once (from browser or CLI) to set up your demo environment.
 */

define('APP_START', true);

require_once __DIR__ . '/app/Config/database.php';

$db = \App\Config\Database::getInstance()->getConnection();

$db->begin_transaction();

try {
    $db->query("SET FOREIGN_KEY_CHECKS = 0");

    $tables = [
        'audit_trail',
        'qr_scans',
        'asset_report_items',
        'asset_reports',
        'asset_transfers',
        'asset_locations',
        'asset_custodies',
        'assets',
        'asset_accounts',
        'asset_categories',
        'users',
        'personnel',
        'offices'
    ];
    foreach ($tables as $table) {
        $db->query("TRUNCATE TABLE $table");
    }

    $db->query("SET FOREIGN_KEY_CHECKS = 1");

    // ---------- 1. Offices ----------
    $offices = [
        ['name' => 'NIA Regional Office IX', 'office_code' => 'NIA-IX', 'location' => 'Zamboanga City', 'contact_person' => 'Regional Director'],
        ['name' => 'Zamboanga City Irrigation Office', 'office_code' => 'ZCIO', 'location' => 'Zamboanga City', 'contact_person' => 'Division Chief'],
        ['name' => 'Pagadian City Irrigation Office', 'office_code' => 'PCIO', 'location' => 'Pagadian City', 'contact_person' => 'Division Chief'],
    ];
    $officeIds = [];
    $stmt = $db->prepare("INSERT INTO offices (name, office_code, location, contact_person) VALUES (?, ?, ?, ?)");
    foreach ($offices as $off) {
        $stmt->bind_param('ssss', $off['name'], $off['office_code'], $off['location'], $off['contact_person']);
        $stmt->execute();
        $officeIds[] = $db->insert_id;
    }
    echo "✅ Offices inserted.\n";

    // ---------- 2. Personnel ----------
    $personnel = [
        ['employee_id' => 'EMP-001', 'full_name' => 'Juan Dela Cruz', 'position' => 'IT Head', 'designation' => 'System Administrator', 'office_id' => $officeIds[0]],
        ['employee_id' => 'EMP-002', 'full_name' => 'Maria Santos', 'position' => 'Supply Officer', 'designation' => 'Property Custodian', 'office_id' => $officeIds[0]],
        ['employee_id' => 'EMP-003', 'full_name' => 'Pedro Reyes', 'position' => 'Administrative Officer', 'designation' => 'Asset Custodian', 'office_id' => $officeIds[0]],
        ['employee_id' => 'EMP-004', 'full_name' => 'Ana Gonzales', 'position' => 'Records Officer', 'designation' => 'Document Controller', 'office_id' => $officeIds[1]],
        ['employee_id' => 'EMP-005', 'full_name' => 'Carlos Mendoza', 'position' => 'Engineer', 'designation' => 'Project Engineer', 'office_id' => $officeIds[2]],
    ];
    $personnelIds = [];
    $stmt = $db->prepare("INSERT INTO personnel (employee_id, full_name, position, designation, office_id, is_active) VALUES (?, ?, ?, ?, ?, 1)");
    foreach ($personnel as $p) {
        $stmt->bind_param('ssssi', $p['employee_id'], $p['full_name'], $p['position'], $p['designation'], $p['office_id']);
        $stmt->execute();
        $personnelIds[] = $db->insert_id;
    }
    echo "✅ Personnel inserted.\n";

    // ---------- 3. Users ----------
    $users = [
        ['personnel_id' => $personnelIds[0], 'username' => 'admin', 'password' => 'admin123', 'role' => 'admin'],
        ['personnel_id' => $personnelIds[1], 'username' => 'supply_officer', 'password' => 'supply123', 'role' => 'supply_officer'],
        ['personnel_id' => $personnelIds[2], 'username' => 'pedro_reyes', 'password' => 'pedro123', 'role' => 'supply_officer'],
    ];
    $userIds = [];
    $stmt = $db->prepare("INSERT INTO users (personnel_id, username, password_hash, role, is_active) VALUES (?, ?, ?, ?, 1)");
    foreach ($users as $u) {
        $hash = password_hash($u['password'], PASSWORD_DEFAULT);
        $stmt->bind_param('isss', $u['personnel_id'], $u['username'], $hash, $u['role']);
        $stmt->execute();
        $userIds[] = $db->insert_id;
    }
    echo "✅ Users inserted.\n";

    // ---------- 4. Asset Categories ----------
    $topLevel = [
        ['name' => 'Land', 'code' => 'LAND', 'description' => 'Land and related accounts'],
        ['name' => 'Land Improvements', 'code' => 'LAND-IMP', 'description' => 'Improvements to land'],
        ['name' => 'Infrastructure Assets', 'code' => 'INFRA', 'description' => 'Roads, water systems, etc.'],
        ['name' => 'Buildings and Other Structures', 'code' => 'BUILD', 'description' => 'Buildings, schools, hospitals'],
        ['name' => 'Machinery and Equipment', 'code' => 'MACH-EQ', 'description' => 'All types of machinery and equipment'],
        ['name' => 'Transportation Equipment', 'code' => 'TRANS-EQ', 'description' => 'Vehicles, aircraft, watercraft'],
        ['name' => 'Furniture, Fixtures and Books', 'code' => 'FURN', 'description' => 'Furniture, fixtures, books'],
        ['name' => 'Leased Assets', 'code' => 'LEASED', 'description' => 'Assets under lease'],
        ['name' => 'Leased Assets Improvements', 'code' => 'LEASE-IMP', 'description' => 'Improvements to leased assets'],
        ['name' => 'Heritage Assets', 'code' => 'HERITAGE', 'description' => 'Historical buildings, artworks'],
        ['name' => 'Service Concession Tangible Assets', 'code' => 'SCA', 'description' => 'Service concession assets'],
        ['name' => 'Other Property, Plant and Equipment', 'code' => 'OTHER-PPE', 'description' => 'Other PPE like work animals'],
        ['name' => 'Construction in Progress', 'code' => 'CIP', 'description' => 'Assets under construction'],
    ];
    $catIds = [];
    $stmt = $db->prepare("INSERT INTO asset_categories (name, code, description, parent_category_id) VALUES (?, ?, ?, NULL)");
    foreach ($topLevel as $cat) {
        $stmt->bind_param('sss', $cat['name'], $cat['code'], $cat['description']);
        $stmt->execute();
        $catIds[$cat['name']] = $db->insert_id;
    }
    echo "✅ Top-level categories inserted.\n";

    $subCats = [
        ['name' => 'Road Networks', 'code' => 'INFRA-01', 'description' => 'Roads and highways', 'parent' => 'Infrastructure Assets'],
        ['name' => 'Water Supply Systems', 'code' => 'INFRA-02', 'description' => 'Water distribution systems', 'parent' => 'Infrastructure Assets'],
        ['name' => 'Power Supply Systems', 'code' => 'INFRA-03', 'description' => 'Power distribution networks', 'parent' => 'Infrastructure Assets'],
        ['name' => 'Buildings', 'code' => 'BUILD-01', 'description' => 'Office and administrative buildings', 'parent' => 'Buildings and Other Structures'],
        ['name' => 'School Buildings', 'code' => 'BUILD-02', 'description' => 'Educational facilities', 'parent' => 'Buildings and Other Structures'],
        ['name' => 'Office Equipment', 'code' => 'MACH-01', 'description' => 'Computers, printers, etc.', 'parent' => 'Machinery and Equipment'],
        ['name' => 'ICT Equipment', 'code' => 'MACH-02', 'description' => 'Servers, network devices', 'parent' => 'Machinery and Equipment'],
        ['name' => 'Agricultural Equipment', 'code' => 'MACH-03', 'description' => 'Tractors, harvesters', 'parent' => 'Machinery and Equipment'],
        ['name' => 'Motor Vehicles', 'code' => 'TRANS-01', 'description' => 'Cars, trucks, vans', 'parent' => 'Transportation Equipment'],
        ['name' => 'Watercrafts', 'code' => 'TRANS-02', 'description' => 'Boats, ferries', 'parent' => 'Transportation Equipment'],
        ['name' => 'Furniture and Fixtures', 'code' => 'FURN-01', 'description' => 'Desks, chairs, cabinets', 'parent' => 'Furniture, Fixtures and Books'],
        ['name' => 'Books', 'code' => 'FURN-02', 'description' => 'Reference books, manuals', 'parent' => 'Furniture, Fixtures and Books'],
    ];
    foreach ($subCats as $sub) {
        $parentId = $catIds[$sub['parent']];
        $stmt = $db->prepare("INSERT INTO asset_categories (name, code, description, parent_category_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sssi', $sub['name'], $sub['code'], $sub['description'], $parentId);
        $stmt->execute();
        $catIds[$sub['name']] = $db->insert_id;
    }
    echo "✅ Sub-categories inserted.\n";

    // ---------- 5. Asset Accounts ----------
    $accounts = [
        ['account_code' => '1060501000', 'account_name' => 'Office Equipment - Computers', 'category' => 'Office Equipment'],
        ['account_code' => '1060502000', 'account_name' => 'Office Equipment - Printers', 'category' => 'Office Equipment'],
        ['account_code' => '1060503000', 'account_name' => 'ICT Equipment - Servers', 'category' => 'ICT Equipment'],
        ['account_code' => '1060504000', 'account_name' => 'Agricultural Equipment - Tractors', 'category' => 'Agricultural Equipment'],
        ['account_code' => '1060601000', 'account_name' => 'Motor Vehicles - Pickup Trucks', 'category' => 'Motor Vehicles'],
        ['account_code' => '1060401000', 'account_name' => 'Buildings - Office Buildings', 'category' => 'Buildings'],
        ['account_code' => '1060301000', 'account_name' => 'Road Networks - Paved Roads', 'category' => 'Road Networks'],
        ['account_code' => '1060701000', 'account_name' => 'Furniture and Fixtures - Office Desks', 'category' => 'Furniture and Fixtures'],
        ['account_code' => '1060702000', 'account_name' => 'Books - Technical References', 'category' => 'Books'],
        ['account_code' => '1060604000', 'account_name' => 'Watercrafts - Patrol Boats', 'category' => 'Watercrafts'],
    ];
    $accountIds = [];
    $stmt = $db->prepare("INSERT INTO asset_accounts (account_code, account_name, asset_category_id) VALUES (?, ?, (SELECT asset_category_id FROM asset_categories WHERE name = ? LIMIT 1))");
    foreach ($accounts as $acc) {
        $stmt->bind_param('sss', $acc['account_code'], $acc['account_name'], $acc['category']);
        $stmt->execute();
        $accountIds[] = $db->insert_id;
    }
    echo "✅ Asset accounts inserted.\n";

    // ---------- 6. Assets ----------
    $assets = [
        ['asset_code' => 'AST-001', 'description' => 'Dell OptiPlex 7080 Desktop', 'brand' => 'Dell', 'model' => 'OptiPlex 7080', 'serial_number' => 'SN-001-ABC', 'acquisition_cost' => 45000.00, 'acquisition_date' => '2023-06-15', 'account_index' => 0, 'status' => 'active', 'condition' => 'good', 'remarks' => 'Used for admin work'],
        ['asset_code' => 'AST-002', 'description' => 'HP LaserJet Pro MFP', 'brand' => 'HP', 'model' => 'LaserJet Pro MFP M428fdw', 'serial_number' => 'SN-002-XYZ', 'acquisition_cost' => 28000.00, 'acquisition_date' => '2023-07-20', 'account_index' => 1, 'status' => 'active', 'condition' => 'good', 'remarks' => 'Shared printer for office'],
        ['asset_code' => 'AST-003', 'description' => 'Dell PowerEdge R740 Server', 'brand' => 'Dell', 'model' => 'PowerEdge R740', 'serial_number' => 'SN-003-456', 'acquisition_cost' => 250000.00, 'acquisition_date' => '2023-08-10', 'account_index' => 2, 'status' => 'active', 'condition' => 'fair', 'remarks' => 'Main application server'],
        ['asset_code' => 'AST-004', 'description' => 'John Deere 5055E Tractor', 'brand' => 'John Deere', 'model' => '5055E', 'serial_number' => 'SN-004-789', 'acquisition_cost' => 850000.00, 'acquisition_date' => '2023-09-05', 'account_index' => 3, 'status' => 'active', 'condition' => 'good', 'remarks' => 'Used for farm operations'],
        ['asset_code' => 'AST-005', 'description' => 'Toyota Hilux Pickup', 'brand' => 'Toyota', 'model' => 'Hilux 4x4', 'serial_number' => 'SN-005-321', 'acquisition_cost' => 1200000.00, 'acquisition_date' => '2023-10-01', 'account_index' => 4, 'status' => 'active', 'condition' => 'good', 'remarks' => 'Field service vehicle'],
        ['asset_code' => 'AST-006', 'description' => 'NIA Regional Office Building', 'brand' => 'N/A', 'model' => 'N/A', 'serial_number' => null, 'acquisition_cost' => 5000000.00, 'acquisition_date' => '2020-01-15', 'account_index' => 5, 'status' => 'active', 'condition' => 'good', 'remarks' => 'Main office building'],
        ['asset_code' => 'AST-007', 'description' => 'Concrete Road – Barangay San Jose', 'brand' => 'N/A', 'model' => 'N/A', 'serial_number' => null, 'acquisition_cost' => 3500000.00, 'acquisition_date' => '2022-11-20', 'account_index' => 6, 'status' => 'active', 'condition' => 'good', 'remarks' => '2 km concrete road'],
        ['asset_code' => 'AST-008', 'description' => 'Office Desk – 120x60cm', 'brand' => 'Furniture Inc.', 'model' => 'D-120', 'serial_number' => 'SN-008-654', 'acquisition_cost' => 5500.00, 'acquisition_date' => '2023-05-10', 'account_index' => 7, 'status' => 'active', 'condition' => 'good', 'remarks' => 'Workstation desk'],
        ['asset_code' => 'AST-009', 'description' => 'National Building Code of the Philippines', 'brand' => 'N/A', 'model' => 'N/A', 'serial_number' => null, 'acquisition_cost' => 1200.00, 'acquisition_date' => '2023-03-25', 'account_index' => 8, 'status' => 'active', 'condition' => 'good', 'remarks' => 'Reference book'],
        ['asset_code' => 'AST-010', 'description' => 'Patrol Boat Mark III', 'brand' => 'Marine Tech', 'model' => 'PB-3', 'serial_number' => 'SN-010-987', 'acquisition_cost' => 2500000.00, 'acquisition_date' => '2023-12-01', 'account_index' => 9, 'status' => 'active', 'condition' => 'good', 'remarks' => 'For river patrol'],
        ['asset_code' => 'AST-011', 'description' => 'HP Laptop ProBook 450 G8', 'brand' => 'HP', 'model' => 'ProBook 450 G8', 'serial_number' => 'SN-011-345', 'acquisition_cost' => 65000.00, 'acquisition_date' => '2023-11-15', 'account_index' => 0, 'status' => 'active', 'condition' => 'good', 'remarks' => 'Field staff laptop'],
        ['asset_code' => 'AST-012', 'description' => 'Printer – Epson L3110', 'brand' => 'Epson', 'model' => 'L3110', 'serial_number' => 'SN-012-456', 'acquisition_cost' => 12000.00, 'acquisition_date' => '2023-09-20', 'account_index' => 1, 'status' => 'inactive', 'condition' => 'poor', 'remarks' => 'Broken – to be disposed'],
    ];
    $assetIds = [];
    $stmt = $db->prepare("
        INSERT INTO assets (
            asset_code, qr_code_ref, description, brand, model, serial_number,
            acquisition_cost, acquisition_date, asset_accounts_id, status, `condition`, remarks
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    foreach ($assets as $a) {
        $qr = 'QR-' . strtoupper(uniqid());
        $accId = $accountIds[$a['account_index']];
        $stmt->bind_param('ssssssdsisss', $a['asset_code'], $qr, $a['description'], $a['brand'], $a['model'], $a['serial_number'], $a['acquisition_cost'], $a['acquisition_date'], $accId, $a['status'], $a['condition'], $a['remarks']);
        $stmt->execute();
        $assetIds[] = $db->insert_id;
    }
    echo "✅ Assets inserted.\n";

    // ---------- 7. Custody Records ----------
    $custodies = [
        ['asset_id' => $assetIds[0], 'custodian_id' => $personnelIds[2], 'office_id' => $officeIds[0], 'effectivity_date' => '2023-06-15', 'status' => 'active'],
        ['asset_id' => $assetIds[1], 'custodian_id' => $personnelIds[1], 'office_id' => $officeIds[0], 'effectivity_date' => '2023-07-20', 'status' => 'active'],
        ['asset_id' => $assetIds[2], 'custodian_id' => $personnelIds[0], 'office_id' => $officeIds[0], 'effectivity_date' => '2023-08-10', 'status' => 'active'],
        ['asset_id' => $assetIds[3], 'custodian_id' => $personnelIds[4], 'office_id' => $officeIds[2], 'effectivity_date' => '2023-09-05', 'status' => 'active'],
        ['asset_id' => $assetIds[4], 'custodian_id' => $personnelIds[3], 'office_id' => $officeIds[1], 'effectivity_date' => '2023-10-01', 'status' => 'active'],
        ['asset_id' => $assetIds[5], 'custodian_id' => $personnelIds[2], 'office_id' => $officeIds[0], 'effectivity_date' => '2020-01-15', 'status' => 'active'],
        ['asset_id' => $assetIds[6], 'custodian_id' => $personnelIds[4], 'office_id' => $officeIds[2], 'effectivity_date' => '2022-11-20', 'status' => 'active'],
        ['asset_id' => $assetIds[7], 'custodian_id' => $personnelIds[2], 'office_id' => $officeIds[0], 'effectivity_date' => '2023-05-10', 'status' => 'active'],
        ['asset_id' => $assetIds[8], 'custodian_id' => $personnelIds[1], 'office_id' => $officeIds[0], 'effectivity_date' => '2023-03-25', 'status' => 'active'],
        ['asset_id' => $assetIds[9], 'custodian_id' => $personnelIds[3], 'office_id' => $officeIds[1], 'effectivity_date' => '2023-12-01', 'status' => 'active'],
        ['asset_id' => $assetIds[10], 'custodian_id' => $personnelIds[2], 'office_id' => $officeIds[0], 'effectivity_date' => '2023-11-15', 'status' => 'active'],
        ['asset_id' => $assetIds[11], 'custodian_id' => $personnelIds[1], 'office_id' => $officeIds[0], 'effectivity_date' => '2023-09-20', 'status' => 'inactive'],
    ];
    $stmt = $db->prepare("INSERT INTO asset_custodies (asset_id, custodian_id, office_id, effectivity_date, status, accountability_document, accountability_reference) VALUES (?, ?, ?, ?, ?, 'ICO', 'REF-001')");
    foreach ($custodies as $c) {
        $stmt->bind_param('iiiss', $c['asset_id'], $c['custodian_id'], $c['office_id'], $c['effectivity_date'], $c['status']);
        $stmt->execute();
    }
    echo "✅ Custody records inserted.\n";

    // ---------- 8. Asset Locations ----------
    $locations = [
        ['asset_id' => $assetIds[0], 'location_name' => 'Admin Building Room 101', 'site_type' => 'indoor', 'description' => 'IT Office', 'recorded_by' => $userIds[0]],
        ['asset_id' => $assetIds[1], 'location_name' => 'Admin Building Room 102', 'site_type' => 'indoor', 'description' => 'Supply Office', 'recorded_by' => $userIds[1]],
        ['asset_id' => $assetIds[2], 'location_name' => 'IT Server Room', 'site_type' => 'indoor', 'description' => 'Main server rack', 'recorded_by' => $userIds[0]],
        ['asset_id' => $assetIds[3], 'location_name' => 'Farm Site – Barangay San Jose', 'site_type' => 'outdoor', 'description' => 'Agricultural area', 'recorded_by' => $userIds[2]],
        ['asset_id' => $assetIds[4], 'location_name' => 'Motor Pool', 'site_type' => 'indoor', 'description' => 'Vehicle garage', 'recorded_by' => $userIds[1]],
        ['asset_id' => $assetIds[5], 'location_name' => 'NIA Regional Office IX', 'site_type' => 'indoor', 'description' => 'Main building', 'recorded_by' => $userIds[0]],
        ['asset_id' => $assetIds[6], 'location_name' => 'Barangay San Jose Road', 'site_type' => 'outdoor', 'description' => '2 km stretch', 'recorded_by' => $userIds[2]],
        ['asset_id' => $assetIds[7], 'location_name' => 'Admin Building Room 201', 'site_type' => 'indoor', 'description' => 'Engineering office', 'recorded_by' => $userIds[1]],
        ['asset_id' => $assetIds[8], 'location_name' => 'Library', 'site_type' => 'indoor', 'description' => 'Reference section', 'recorded_by' => $userIds[0]],
        ['asset_id' => $assetIds[9], 'location_name' => 'River Patrol Base', 'site_type' => 'outdoor', 'description' => 'Docking area', 'recorded_by' => $userIds[2]],
    ];
    $stmt = $db->prepare("INSERT INTO asset_locations (asset_id, location_name, site_type, description, recorded_by) VALUES (?, ?, ?, ?, ?)");
    foreach ($locations as $loc) {
        $stmt->bind_param('isssi', $loc['asset_id'], $loc['location_name'], $loc['site_type'], $loc['description'], $loc['recorded_by']);
        $stmt->execute();
    }
    echo "✅ Asset locations inserted.\n";

    // ---------- 9. Audit Trail ----------
    $auditEntries = [
        ['asset_id' => $assetIds[0], 'performed_by' => $userIds[0], 'action_type' => 'CREATE', 'module' => 'ASSET', 'previous_values' => null, 'new_values' => '{"asset_code":"AST-001","description":"Dell OptiPlex 7080 Desktop"}'],
        ['asset_id' => $assetIds[1], 'performed_by' => $userIds[1], 'action_type' => 'CREATE', 'module' => 'ASSET', 'previous_values' => null, 'new_values' => '{"asset_code":"AST-002","description":"HP LaserJet Pro MFP"}'],
        ['asset_id' => $assetIds[0], 'performed_by' => $userIds[1], 'action_type' => 'UPDATE', 'module' => 'ASSET', 'previous_values' => '{"status":"active"}', 'new_values' => '{"status":"inactive"}'],
        ['asset_id' => $assetIds[2], 'performed_by' => $userIds[0], 'action_type' => 'CREATE', 'module' => 'ASSET', 'previous_values' => null, 'new_values' => '{"asset_code":"AST-003","description":"Dell PowerEdge R740 Server"}'],
        ['asset_id' => $assetIds[3], 'performed_by' => $userIds[2], 'action_type' => 'CREATE', 'module' => 'ASSET', 'previous_values' => null, 'new_values' => '{"asset_code":"AST-004","description":"John Deere 5055E Tractor"}'],
        ['asset_id' => $assetIds[4], 'performed_by' => $userIds[1], 'action_type' => 'CREATE', 'module' => 'ASSET', 'previous_values' => null, 'new_values' => '{"asset_code":"AST-005","description":"Toyota Hilux Pickup"}'],
    ];
    $stmt = $db->prepare("INSERT INTO audit_trail (asset_id, performed_by, action_type, module, previous_values, new_values) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($auditEntries as $aud) {
        $stmt->bind_param('iissss', $aud['asset_id'], $aud['performed_by'], $aud['action_type'], $aud['module'], $aud['previous_values'], $aud['new_values']);
        $stmt->execute();
    }
    echo "✅ Audit trail inserted.\n";

    // ---------- 10. QR Scans ----------
    $qrScans = [
        ['asset_id' => $assetIds[0], 'scanned_by' => $userIds[0]],
        ['asset_id' => $assetIds[1], 'scanned_by' => $userIds[1]],
        ['asset_id' => $assetIds[2], 'scanned_by' => $userIds[0]],
        ['asset_id' => $assetIds[4], 'scanned_by' => $userIds[2]],
        ['asset_id' => $assetIds[5], 'scanned_by' => $userIds[1]],
    ];
    $stmt = $db->prepare("INSERT INTO qr_scans (asset_id, scanned_by) VALUES (?, ?)");
    foreach ($qrScans as $scan) {
        $stmt->bind_param('ii', $scan['asset_id'], $scan['scanned_by']);
        $stmt->execute();
    }
    echo "✅ QR scans inserted.\n";

    // ---------- 11. Asset Reports ----------
    $reportNumber = 'RPT-2026-001';
    $reportDate = '2026-07-04';
    $preparedBy = $userIds[0];
    $officeId = $officeIds[0];
    $remarks = 'Sample draft report';
    $stmt = $db->prepare("INSERT INTO asset_reports (report_number, report_date, office_id, prepared_by, status, remarks) VALUES (?, ?, ?, ?, 'draft', ?)");
    $stmt->bind_param('ssiis', $reportNumber, $reportDate, $officeId, $preparedBy, $remarks);
    $stmt->execute();
    $reportId = $db->insert_id;

    $reportItems = [
        ['asset_id' => $assetIds[0], 'verification_status' => 'pending', 'asset_condition' => 'good', 'verified_by' => $userIds[0], 'remarks' => 'To verify'],
        ['asset_id' => $assetIds[1], 'verification_status' => 'verified', 'asset_condition' => 'good', 'verified_by' => $userIds[1], 'remarks' => 'Verified'],
        ['asset_id' => $assetIds[2], 'verification_status' => 'pending', 'asset_condition' => 'fair', 'verified_by' => $userIds[0], 'remarks' => 'Check server status'],
    ];
    $stmt = $db->prepare("INSERT INTO asset_report_items (asset_report_id, asset_id, verification_status, asset_condition, verified_by, remarks) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($reportItems as $item) {
        $stmt->bind_param('iissss', $reportId, $item['asset_id'], $item['verification_status'], $item['asset_condition'], $item['verified_by'], $item['remarks']);
        $stmt->execute();
    }
    echo "✅ Sample report inserted.\n";

    $db->commit();
    echo "\n🎉 All sample data inserted successfully!\n";
    echo "You can now log in with:\n";
    echo "  admin        / admin123\n";
    echo "  supply_officer / supply123\n";
    echo "  pedro_reyes  / pedro123\n";

} catch (Exception $e) {
    $db->rollback();
    die("❌ Error: " . $e->getMessage());
}