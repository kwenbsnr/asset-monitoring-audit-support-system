<?php
/**
 * ONE‑TIME SEED SCRIPT – Run this once to populate initial data.
 * After running, you can delete or move this file for security.
 */

// Database credentials (same as in app/Config/database.php)
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'nia_schema_v1';

// Connect
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "✅ Connected to database.\n";

// Start transaction
$conn->begin_transaction();

try {
    // ---------- 1. Insert an office ----------
    $office_name = 'NIA Regional Office IX';
    $office_code = 'NIA-IX';
    $location = 'Zamboanga City';
    $contact_person = 'Regional Director';

    $stmt = $conn->prepare("INSERT IGNORE INTO offices (name, office_code, location, contact_person) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $office_name, $office_code, $location, $contact_person);
    $stmt->execute();
    $office_id = $conn->insert_id ?: 1; // fallback if already exists
    echo "✅ Office inserted (ID: $office_id)\n";

    // ---------- 2. Insert two personnel ----------
    // Admin (IT personnel)
    $admin_employee_id = 'EMP-ADMIN';
    $admin_full_name = 'Juan Dela Cruz';
    $admin_position = 'IT Head';
    $admin_designation = 'System Administrator';

    $stmt = $conn->prepare("INSERT IGNORE INTO personnel (employee_id, full_name, position, designation, office_id, is_active) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param('ssssi', $admin_employee_id, $admin_full_name, $admin_position, $admin_designation, $office_id);
    $stmt->execute();
    $admin_personnel_id = $conn->insert_id ?: 1;

    // Supply Officer
    $supply_employee_id = 'EMP-SUPPLY';
    $supply_full_name = 'Maria Santos';
    $supply_position = 'Supply Officer';
    $supply_designation = 'Property Custodian';

    $stmt = $conn->prepare("INSERT IGNORE INTO personnel (employee_id, full_name, position, designation, office_id, is_active) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param('ssssi', $supply_employee_id, $supply_full_name, $supply_position, $supply_designation, $office_id);
    $stmt->execute();
    $supply_personnel_id = $conn->insert_id ?: 2;

    echo "✅ Personnel inserted (Admin ID: $admin_personnel_id, Supply ID: $supply_personnel_id)\n";

    // ---------- 3. Insert two users with bcrypt hashes ----------
    $admin_username = 'admin';
    $admin_password = 'admin123';
    $admin_hash = password_hash($admin_password, PASSWORD_DEFAULT);
    $admin_role = 'admin';

    $stmt = $conn->prepare("INSERT IGNORE INTO users (personnel_id, username, password_hash, role, is_active) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param('isss', $admin_personnel_id, $admin_username, $admin_hash, $admin_role);
    $stmt->execute();

    $supply_username = 'supply_officer';
    $supply_password = 'supply123';
    $supply_hash = password_hash($supply_password, PASSWORD_DEFAULT);
    $supply_role = 'supply_officer';

    $stmt = $conn->prepare("INSERT IGNORE INTO users (personnel_id, username, password_hash, role, is_active) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param('isss', $supply_personnel_id, $supply_username, $supply_hash, $supply_role);
    $stmt->execute();

    echo "✅ Users inserted (admin / supply_officer)\n";

    // ---------- Commit ----------
    $conn->commit();
    echo "\n🎉 Dummy data seeded successfully!\n";
    echo "You can now log in with:\n";
    echo "  admin        / admin123\n";
    echo "  supply_officer / supply123\n";

} catch (Exception $e) {
    $conn->rollback();
    echo "❌ Error: " . $e->getMessage() . "\n";
}

$conn->close();