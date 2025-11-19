<?php
/**
 * Migration: Rename facility_teams to department_teams
 * Change facility_id to organization_department_id
 *
 * Since SQLite doesn't support RENAME COLUMN directly, we need to:
 * 1. Create a new table with the new schema
 * 2. Copy data from old table to new table
 * 3. Drop old table
 * 4. Rename new table to final name
 * 5. Recreate indexes
 */

require_once __DIR__ . '/../src/includes/autoload.php';

use App\Config\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getPdo();

    echo "Starting migration to rename facility_teams to department_teams...\n";

    // Check if old table exists
    $tableCheck = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='facility_teams'")->fetch();

    if (!$tableCheck) {
        echo "ℹ️  Table 'facility_teams' does not exist. Checking if 'department_teams' already exists...\n";

        $newTableCheck = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='department_teams'")->fetch();

        if ($newTableCheck) {
            echo "✅ Table 'department_teams' already exists. Migration not needed.\n";
            exit(0);
        } else {
            echo "❌ Neither 'facility_teams' nor 'department_teams' exists. Please run the seed script first.\n";
            exit(1);
        }
    }

    // Start transaction
    $pdo->beginTransaction();

    // Step 1: Create new table with updated schema
    echo "Creating new table structure...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS department_teams (
            id TEXT PRIMARY KEY,
            name TEXT NOT NULL,
            code TEXT UNIQUE NOT NULL,
            description TEXT,
            parent_team_id TEXT,
            organization_department_id TEXT,
            organization_id TEXT,
            is_active INTEGER DEFAULT 1,
            created_by TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_by TEXT,
            updated_at TEXT,
            deleted_by TEXT,
            deleted_at TEXT,
            FOREIGN KEY (parent_team_id) REFERENCES department_teams(id) ON DELETE SET NULL,
            FOREIGN KEY (organization_department_id) REFERENCES organization_departments(id) ON DELETE CASCADE,
            FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    // Step 2: Copy data from old table to new table
    echo "Copying data to new table...\n";
    $pdo->exec("
        INSERT INTO department_teams (
            id, name, code, description, parent_team_id, organization_department_id,
            organization_id, is_active, created_by, created_at, updated_by, updated_at,
            deleted_by, deleted_at
        )
        SELECT
            id, name, code, description, parent_team_id, facility_id,
            organization_id, is_active, created_by, created_at, updated_by, updated_at,
            deleted_by, deleted_at
        FROM facility_teams
    ");

    // Step 3: Drop old table
    echo "Dropping old table...\n";
    $pdo->exec("DROP TABLE facility_teams");

    // Step 4: Recreate indexes
    echo "Recreating indexes...\n";
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_department_teams_code ON department_teams(code)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_department_teams_parent ON department_teams(parent_team_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_department_teams_dept ON department_teams(organization_department_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_department_teams_org ON department_teams(organization_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_department_teams_created_by ON department_teams(created_by)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_department_teams_deleted_at ON department_teams(deleted_at)");

    // Commit transaction
    $pdo->commit();

    echo "\n✅ Migration completed successfully!\n";
    echo "The 'facility_teams' table has been renamed to 'department_teams'.\n";
    echo "The 'facility_id' column has been renamed to 'organization_department_id'.\n";

} catch (Exception $e) {
    // Rollback on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
