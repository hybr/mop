<?php
/**
 * Organization Entity Permissions Seed Script
 * Populates organization_entity_permissions table with sample permissions
 * for various positions in the organization
 *
 * Permission Structure:
 * <OrganizationPosition> can <Action> the entity <Entity> with <Scope>
 *
 * Example: "Senior Software Engineer can Create OrganizationVacancy with scope 'department'"
 */

require_once __DIR__ . '/../src/includes/autoload.php';

use App\Config\Database;

echo "=== Organization Entity Permissions Seeding Script ===" . PHP_EOL . PHP_EOL;

$db = Database::getInstance();
$pdo = $db->getPdo();

// Get sample user
$userStmt = $pdo->query("SELECT id FROM users LIMIT 1");
$userId = $userStmt->fetchColumn();

if (!$userId) {
    echo "ERROR: No users found in database. Please create a user first." . PHP_EOL;
    exit(1);
}

// Get position codes and their IDs
$positionStmt = $pdo->query("SELECT id, code FROM organization_positions");
$positions = [];
while ($row = $positionStmt->fetch(PDO::FETCH_ASSOC)) {
    $positions[$row['code']] = $row['id'];
}

if (empty($positions)) {
    echo "ERROR: No positions found. Please seed organization_positions first." . PHP_EOL;
    exit(1);
}

echo "Found " . count($positions) . " positions in database." . PHP_EOL . PHP_EOL;

/**
 * Generate UUID v4
 */
function generateUUID() {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

/**
 * Sample permission templates by role type
 */
$permissionTemplates = [
    // ===== Executive/Director Level =====
    'executive' => [
        ['entity' => 'Organization', 'action' => 'read', 'scope' => 'all', 'priority' => 100],
        ['entity' => 'Organization', 'action' => 'update', 'scope' => 'organization', 'priority' => 90],
        ['entity' => 'OrganizationVacancy', 'action' => 'create', 'scope' => 'organization', 'priority' => 90],
        ['entity' => 'OrganizationVacancy', 'action' => 'read', 'scope' => 'all', 'priority' => 90],
        ['entity' => 'OrganizationVacancy', 'action' => 'update', 'scope' => 'organization', 'priority' => 90],
        ['entity' => 'OrganizationVacancy', 'action' => 'delete', 'scope' => 'organization', 'priority' => 90],
        ['entity' => 'OrganizationVacancy', 'action' => 'approve', 'scope' => 'organization', 'priority' => 100],
        ['entity' => 'OrganizationVacancy', 'action' => 'publish', 'scope' => 'organization', 'priority' => 90],
        ['entity' => 'OrganizationPosition', 'action' => 'read', 'scope' => 'all', 'priority' => 80],
        ['entity' => 'OrganizationEntityPermission', 'action' => 'create', 'scope' => 'organization', 'priority' => 100],
        ['entity' => 'OrganizationEntityPermission', 'action' => 'read', 'scope' => 'organization', 'priority' => 100],
        ['entity' => 'OrganizationEntityPermission', 'action' => 'update', 'scope' => 'organization', 'priority' => 100],
        ['entity' => 'OrganizationEntityPermission', 'action' => 'delete', 'scope' => 'organization', 'priority' => 100],
    ],

    // ===== Manager Level =====
    'manager' => [
        ['entity' => 'Organization', 'action' => 'read', 'scope' => 'organization', 'priority' => 50],
        ['entity' => 'OrganizationVacancy', 'action' => 'create', 'scope' => 'department', 'priority' => 60],
        ['entity' => 'OrganizationVacancy', 'action' => 'read', 'scope' => 'organization', 'priority' => 60],
        ['entity' => 'OrganizationVacancy', 'action' => 'update', 'scope' => 'department', 'priority' => 60],
        ['entity' => 'OrganizationVacancy', 'action' => 'approve', 'scope' => 'department', 'priority' => 70],
        ['entity' => 'OrganizationPosition', 'action' => 'read', 'scope' => 'all', 'priority' => 50],
        ['entity' => 'OrganizationEntityPermission', 'action' => 'read', 'scope' => 'department', 'priority' => 50],
    ],

    // ===== HR Department =====
    'hr' => [
        ['entity' => 'OrganizationVacancy', 'action' => 'create', 'scope' => 'organization', 'priority' => 80],
        ['entity' => 'OrganizationVacancy', 'action' => 'read', 'scope' => 'all', 'priority' => 80],
        ['entity' => 'OrganizationVacancy', 'action' => 'update', 'scope' => 'organization', 'priority' => 80],
        ['entity' => 'OrganizationVacancy', 'action' => 'delete', 'scope' => 'organization', 'priority' => 80],
        ['entity' => 'OrganizationVacancy', 'action' => 'publish', 'scope' => 'organization', 'priority' => 80],
        ['entity' => 'OrganizationPosition', 'action' => 'create', 'scope' => 'organization', 'priority' => 70],
        ['entity' => 'OrganizationPosition', 'action' => 'read', 'scope' => 'all', 'priority' => 70],
        ['entity' => 'OrganizationPosition', 'action' => 'update', 'scope' => 'organization', 'priority' => 70],
        ['entity' => 'OrganizationEntityPermission', 'action' => 'read', 'scope' => 'organization', 'priority' => 60],
    ],

    // ===== Senior Developer/Engineer =====
    'senior_technical' => [
        ['entity' => 'Organization', 'action' => 'read', 'scope' => 'organization', 'priority' => 30],
        ['entity' => 'OrganizationVacancy', 'action' => 'read', 'scope' => 'organization', 'priority' => 30],
        ['entity' => 'OrganizationPosition', 'action' => 'read', 'scope' => 'all', 'priority' => 30],
    ],

    // ===== Regular Employee =====
    'employee' => [
        ['entity' => 'Organization', 'action' => 'read', 'scope' => 'organization', 'priority' => 10],
        ['entity' => 'OrganizationVacancy', 'action' => 'read', 'scope' => 'organization', 'priority' => 10],
        ['entity' => 'OrganizationPosition', 'action' => 'read', 'scope' => 'all', 'priority' => 10],
    ]
];

/**
 * Map positions to role templates
 */
$positionRoleMap = [
    // Executive/Director Level
    'CEO' => 'executive',
    'CTO' => 'executive',
    'CFO' => 'executive',
    'TECH_DIR' => 'executive',
    'HR_DIR' => 'hr',

    // Manager Level
    'TECH_MGR' => 'manager',
    'HR_MGR' => 'hr',
    'OPS_MGR' => 'manager',
    'PROJ_MGR' => 'manager',

    // HR Positions
    'HR_SPEC' => 'hr',

    // Senior Technical
    'BACKEND_SR_DEV' => 'senior_technical',
    'FRONTEND_SR_DEV' => 'senior_technical',
    'DEVOPS_SR_ENG' => 'senior_technical',
    'DATA_SCI_SR' => 'senior_technical',

    // Regular Employees (Default)
    'BACKEND_JR_DEV' => 'employee',
    'FRONTEND_JR_DEV' => 'employee',
    'QA_ENG' => 'employee',
    'SUPPORT_ENG' => 'employee',
];

/**
 * Insert permissions
 */
$insertedCount = 0;
$skippedCount = 0;

foreach ($positionRoleMap as $positionCode => $roleTemplate) {
    if (!isset($positions[$positionCode])) {
        echo "! Position '{$positionCode}' not found, skipping..." . PHP_EOL;
        continue;
    }

    $positionId = $positions[$positionCode];
    $permissions = $permissionTemplates[$roleTemplate] ?? $permissionTemplates['employee'];

    echo "Creating permissions for position: {$positionCode} (Role: {$roleTemplate})..." . PHP_EOL;

    foreach ($permissions as $perm) {
        $id = generateUUID();
        $description = "{$positionCode} can {$perm['action']} {$perm['entity']} ({$perm['scope']})";

        // Check if permission already exists
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*) FROM organization_entity_permissions
            WHERE organization_position_id = ?
            AND entity_name = ?
            AND action = ?
            AND deleted_at IS NULL
        ");
        $checkStmt->execute([$positionId, $perm['entity'], $perm['action']]);

        if ($checkStmt->fetchColumn() > 0) {
            $skippedCount++;
            continue;
        }

        // Insert permission
        $insertStmt = $pdo->prepare("
            INSERT INTO organization_entity_permissions (
                id,
                organization_position_id,
                entity_name,
                action,
                scope,
                description,
                is_active,
                priority,
                created_by,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?)
        ");

        $result = $insertStmt->execute([
            $id,
            $positionId,
            $perm['entity'],
            $perm['action'],
            $perm['scope'],
            $description,
            $perm['priority'],
            $userId,
            date('Y-m-d H:i:s')
        ]);

        if ($result) {
            $insertedCount++;
            echo "  ✓ {$description}" . PHP_EOL;
        } else {
            echo "  ✗ Failed to create: {$description}" . PHP_EOL;
        }
    }

    echo PHP_EOL;
}

echo "=== Summary ===" . PHP_EOL;
echo "✓ Inserted: {$insertedCount} permissions" . PHP_EOL;
echo "⊘ Skipped: {$skippedCount} (already exist)" . PHP_EOL;
echo PHP_EOL;
echo "=== Seeding Complete ===" . PHP_EOL;
