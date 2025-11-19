<?php
/**
 * Migration: Remove address column from organizations table
 *
 * Since SQLite doesn't support DROP COLUMN directly, we need to:
 * 1. Create a new table without the address column
 * 2. Copy data from old table to new table
 * 3. Drop old table
 * 4. Rename new table to original name
 * 5. Recreate indexes
 */

require_once __DIR__ . '/../src/includes/autoload.php';

use App\Config\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getPdo();

    echo "Starting migration to remove address column from organizations table...\n";

    // Start transaction
    $pdo->beginTransaction();

    // Step 1: Create new table without address column
    echo "Creating new table structure...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS organizations_new (
            id TEXT PRIMARY KEY,
            short_name TEXT NOT NULL,
            legal_structure TEXT,
            subdomain TEXT UNIQUE NOT NULL,
            description TEXT,
            email TEXT,
            phone TEXT,
            website TEXT,
            logo_url TEXT,
            is_active INTEGER DEFAULT 1,
            created_by TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_by TEXT,
            updated_at TEXT,
            deleted_by TEXT,
            deleted_at TEXT,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    // Step 2: Copy data from old table to new table (excluding address)
    echo "Copying data to new table...\n";
    $pdo->exec("
        INSERT INTO organizations_new (
            id, short_name, legal_structure, subdomain, description,
            email, phone, website, logo_url, is_active,
            created_by, created_at, updated_by, updated_at, deleted_by, deleted_at
        )
        SELECT
            id, short_name, legal_structure, subdomain, description,
            email, phone, website, logo_url, is_active,
            created_by, created_at, updated_by, updated_at, deleted_by, deleted_at
        FROM organizations
    ");

    // Step 3: Drop old table
    echo "Dropping old table...\n";
    $pdo->exec("DROP TABLE organizations");

    // Step 4: Rename new table to original name
    echo "Renaming new table...\n";
    $pdo->exec("ALTER TABLE organizations_new RENAME TO organizations");

    // Step 5: Recreate indexes
    echo "Recreating indexes...\n";
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_organizations_subdomain ON organizations(subdomain)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_organizations_created_by ON organizations(created_by)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_organizations_deleted_at ON organizations(deleted_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_organizations_short_name ON organizations(short_name)");

    // Commit transaction
    $pdo->commit();

    echo "\n✅ Migration completed successfully!\n";
    echo "The 'address' column has been removed from the organizations table.\n";
    echo "Addresses will now be managed at the Building level.\n";

} catch (Exception $e) {
    // Rollback on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
