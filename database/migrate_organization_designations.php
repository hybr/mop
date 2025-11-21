<?php
require_once __DIR__ . '/../src/includes/autoload.php';

use App\Config\Database;

$db = Database::getInstance();

if ($db->getDriver() !== 'sqlite') {
    die("This migration script is designed for SQLite only.\n");
}

$pdo = $db->getPdo();

try {
    // Create organization_designations table
    $sql = "CREATE TABLE IF NOT EXISTS organization_designations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        code TEXT NOT NULL UNIQUE,
        description TEXT,
        level INTEGER,
        organization_id INTEGER,
        organization_department_id INTEGER,
        is_active INTEGER DEFAULT 1,
        sort_order INTEGER DEFAULT 0,
        created_by TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_by TEXT,
        updated_at DATETIME,
        deleted_by TEXT,
        deleted_at DATETIME,
        FOREIGN KEY (organization_id) REFERENCES organizations(id),
        FOREIGN KEY (organization_department_id) REFERENCES organization_departments(id)
    )";

    $pdo->exec($sql);
    echo "✅ Table 'organization_designations' created successfully!\n";

    // Create indexes
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_org_designations_code ON organization_designations(code)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_org_designations_level ON organization_designations(level)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_org_designations_org_id ON organization_designations(organization_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_org_designations_dept_id ON organization_designations(organization_department_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_org_designations_deleted ON organization_designations(deleted_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_org_designations_active ON organization_designations(is_active)");

    echo "✅ Indexes created successfully!\n";
    echo "\n✨ Migration completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
