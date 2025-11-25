<?php
/**
 * Migrate Entity Permissions Table
 * Creates organization_entity_permissions table
 */

require_once __DIR__ . '/../src/includes/autoload.php';

use App\Config\Database;

echo "=== Creating organization_entity_permissions Table ===" . PHP_EOL . PHP_EOL;

$db = Database::getInstance();
$pdo = $db->getPdo();

try {
    $sql = file_get_contents(__DIR__ . '/seed/0120_organization_entity_permissions.sql');

    // Split into statements
    $statements = explode(';', $sql);

    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }

        try {
            $pdo->exec($statement);
            echo "✓ Statement executed successfully" . PHP_EOL;
        } catch (Exception $e) {
            // Show error but continue
            echo "! Error: " . $e->getMessage() . PHP_EOL;
        }
    }

    echo PHP_EOL . "=== Migration Complete ===" . PHP_EOL;

    // Check if table exists
    $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='organization_entity_permissions'")->fetch();
    if ($result) {
        echo "✓ Table 'organization_entity_permissions' exists" . PHP_EOL;
        $count = $pdo->query("SELECT COUNT(*) FROM organization_entity_permissions")->fetchColumn();
        echo "  Current rows: $count" . PHP_EOL;
    } else {
        echo "✗ Table 'organization_entity_permissions' does not exist" . PHP_EOL;
    }

} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
