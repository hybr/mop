<?php
/**
 * Verify Entity Permissions Data
 */

require_once __DIR__ . '/../src/includes/autoload.php';

use App\Config\Database;

$db = Database::getInstance();
$pdo = $db->getPdo();

echo "=== Organization Entity Permissions Verification ===" . PHP_EOL . PHP_EOL;

// Count total permissions
$count = $pdo->query('SELECT COUNT(*) FROM organization_entity_permissions')->fetchColumn();
echo "Total permissions: {$count}" . PHP_EOL . PHP_EOL;

// Show sample permissions with position names
echo "Sample permissions:" . PHP_EOL;
$stmt = $pdo->query("
    SELECT
        oep.*,
        op.name as position_name,
        op.code as position_code
    FROM organization_entity_permissions oep
    LEFT JOIN organization_positions op ON oep.organization_position_id = op.id
    ORDER BY oep.priority DESC, oep.created_at ASC
    LIMIT 15
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  • {$row['position_name']} ({$row['position_code']}) can {$row['action']} {$row['entity_name']} [scope: {$row['scope']}, priority: {$row['priority']}]" . PHP_EOL;
}

echo PHP_EOL . "=== Verification Complete ===" . PHP_EOL;
