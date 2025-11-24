<?php
/**
 * Migration: Create organization_department_teams table
 * Teams within organization departments (e.g., teams within Facilities department)
 * Per entity_creation_instructions.md guidelines
 */

require_once __DIR__ . '/../src/includes/autoload.php';

use App\Config\Database;

$db = Database::getInstance();
$pdo = $db->getPdo();

try {
    echo "Creating organization_department_teams table...\n";

    $sql = "CREATE TABLE IF NOT EXISTS organization_department_teams (
        id TEXT PRIMARY KEY,
        name TEXT NOT NULL,
        code TEXT NOT NULL,
        description TEXT,
        parent_team_id TEXT,
        organization_department_id TEXT NOT NULL,
        organization_id TEXT NOT NULL,

        -- Status and metadata
        is_active INTEGER DEFAULT 1,
        sort_order INTEGER DEFAULT 0,

        -- Default audit fields
        created_by TEXT NOT NULL,
        created_at TEXT NOT NULL,
        updated_by TEXT,
        updated_at TEXT,
        deleted_by TEXT,
        deleted_at TEXT,

        FOREIGN KEY (parent_team_id) REFERENCES organization_department_teams(id),
        FOREIGN KEY (organization_department_id) REFERENCES organization_departments(id),
        FOREIGN KEY (organization_id) REFERENCES organizations(id),
        FOREIGN KEY (created_by) REFERENCES users(id),
        FOREIGN KEY (updated_by) REFERENCES users(id),
        FOREIGN KEY (deleted_by) REFERENCES users(id)
    )";

    $pdo->exec($sql);

    echo "✓ organization_department_teams table created successfully\n";

    // Create indexes
    echo "Creating indexes...\n";

    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_dept_teams_department ON organization_department_teams(organization_department_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_dept_teams_organization ON organization_department_teams(organization_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_dept_teams_parent ON organization_department_teams(parent_team_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_dept_teams_deleted ON organization_department_teams(deleted_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_dept_teams_active ON organization_department_teams(is_active)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_dept_teams_code ON organization_department_teams(code)");
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_dept_teams_code_dept ON organization_department_teams(code, organization_department_id) WHERE deleted_at IS NULL");

    echo "✓ Indexes created successfully\n";

    echo "\nMigration completed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
