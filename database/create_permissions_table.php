<?php
/**
 * Simple script to create organization_entity_permissions table
 */

require_once __DIR__ . '/../src/includes/autoload.php';

use App\Config\Database;

$db = Database::getInstance();
$pdo = $db->getPdo();

echo "Creating organization_entity_permissions table..." . PHP_EOL;

// Create table without CHECK constraints (SQLite may have issues with multi-line CHECK)
$sql = "
CREATE TABLE IF NOT EXISTS organization_entity_permissions (
    id TEXT PRIMARY KEY,
    organization_position_id TEXT NOT NULL,
    entity_name TEXT NOT NULL,
    action TEXT NOT NULL,
    scope TEXT DEFAULT 'own',
    conditions TEXT,
    description TEXT,
    is_active INTEGER DEFAULT 1,
    priority INTEGER DEFAULT 0,
    created_by TEXT NOT NULL,
    created_at TEXT NOT NULL,
    updated_by TEXT,
    updated_at TEXT,
    deleted_by TEXT,
    deleted_at TEXT,
    FOREIGN KEY (organization_position_id) REFERENCES organization_positions(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    FOREIGN KEY (deleted_by) REFERENCES users(id),
    UNIQUE (organization_position_id, entity_name, action)
)";

try {
    $pdo->exec($sql);
    echo "✓ Table created successfully" . PHP_EOL . PHP_EOL;
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

// Create indexes
$indexes = [
    "CREATE INDEX IF NOT EXISTS idx_entity_permissions_position ON organization_entity_permissions(organization_position_id)",
    "CREATE INDEX IF NOT EXISTS idx_entity_permissions_entity ON organization_entity_permissions(entity_name)",
    "CREATE INDEX IF NOT EXISTS idx_entity_permissions_action ON organization_entity_permissions(action)",
    "CREATE INDEX IF NOT EXISTS idx_entity_permissions_scope ON organization_entity_permissions(scope)",
    "CREATE INDEX IF NOT EXISTS idx_entity_permissions_active ON organization_entity_permissions(is_active)",
    "CREATE INDEX IF NOT EXISTS idx_entity_permissions_priority ON organization_entity_permissions(priority)",
    "CREATE INDEX IF NOT EXISTS idx_entity_permissions_deleted ON organization_entity_permissions(deleted_at)",
    "CREATE INDEX IF NOT EXISTS idx_entity_permissions_check ON organization_entity_permissions(organization_position_id, entity_name, action, is_active, deleted_at)"
];

echo "Creating indexes..." . PHP_EOL;
foreach ($indexes as $indexSql) {
    try {
        $pdo->exec($indexSql);
        echo "✓ Index created" . PHP_EOL;
    } catch (Exception $e) {
        echo "! Warning: " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "=== Complete ===" . PHP_EOL;

// Verify
$result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='organization_entity_permissions'")->fetch();
if ($result) {
    echo "✓ Table 'organization_entity_permissions' exists" . PHP_EOL;
} else {
    echo "✗ Table creation failed" . PHP_EOL;
}
